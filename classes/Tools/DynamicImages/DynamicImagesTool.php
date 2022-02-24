<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tools\DynamicImages;

use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\gen_uuid;
use function MediaCloud\Plugin\Utilities\json_response;
use function MediaCloud\Plugin\Utilities\parse_req;

abstract class DynamicImagesTool extends Tool {
	protected $forcedEnabled = false;

    /** @var DynamicImagesToolSettings  */
    protected $settings = null;

    protected $paramPropsByType;
    protected $paramProps;

    protected $shouldCrop = false;

    protected $allSizes = null;
    protected $processedAttachments = [];
    protected $skipSizeParams = false;

    //region Constructor
    public function __construct($toolName, $toolInfo, $toolManager) {
        parent::__construct($toolName, $toolInfo, $toolManager);

        add_filter('media-cloud/dynamic-images/enabled', function($enabled){
            if ($this->forcedEnabled) {
                return $this->forcedEnabled;
            }

            if (!$enabled) {
                return $this->enabled();
            }

            return $enabled;
        });
    }
    //endregion

    //region Tool Overrides
    public function setup() {
        parent::setup();

        if (!$this->enabled()) {
            return;
        }

        $directUploadEnabled = ToolsManager::instance()->toolEnvEnabled('media-upload');
	    foreach($this->toolInfo['imageOptimizers'] as $key => $plugin) {
		    if (is_plugin_active($plugin)) {
			    $dismissibleID = 'dynamic-images-image-optimizers-7';
			    if (NoticeManager::instance()->isAdminNoticeActive($dismissibleID)) {
				    add_action( 'admin_notices', function () use ($directUploadEnabled, $dismissibleID) {
					    ?>
                        <div data-dismissible="<?php echo $dismissibleID ?>" class="notice notice-warning is-dismissible" style="padding:10px;">
                            <?php if($directUploadEnabled): ?>
                            <div style="text-transform: uppercase; font-weight:bold; opacity: 0.8; margin-bottom: 0; padding-bottom: 0">Direct Upload and Image Optimizers</div>
                            <p>You have an image optimizer plugin installed and activated.  Because you are using direct upload functionality, no image you upload will be processed by these plugins as these uploads go directly to your cloud storage, bypassing WordPress completely.</p>
                            <p>You should consider deactivating these image optimizer plugins.</p>
                            <?php else: ?>
                            <div style="text-transform: uppercase; font-weight:bold; opacity: 0.8; margin-bottom: 0; padding-bottom: 0">Imgix and Image Optimizers</div>
                            <p>You have an image optimizer plugin installed and activated.  Imgix functionality will not use the images that these plugins optimize.</p>
                            <p>In the case of Imgix, Imgix will optimize images automatically for you when rendering them.</p>
                            <?php endif; ?>
                        </div>
					    <?php
				    } );
			    }

		        break;
		    }
	    }

	    $this->testForBadPlugins();
	    $this->testForUselessPlugins();

	    if (!$this->enabled()) {
		    return;
	    }

        $this->paramProps = [];
        $this->paramPropsByType = [];
        if(isset($this->toolInfo['settings']['params'])) {
            foreach($this->toolInfo['settings']['params'] as $paramCategory => $paramCategoryInfo) {
                foreach($paramCategoryInfo as $paramGroup => $paramGroupInfo) {
                    foreach($paramGroupInfo as $paramKey => $paramInfo) {
                        $this->paramProps[$paramKey] = $paramInfo;

                        if(!isset($this->paramPropsByType[$paramInfo['type']])) {
                            $paramType = [];
                        } else {
                            $paramType = $this->paramPropsByType[$paramInfo['type']];
                        }

                        $paramType[$paramKey] = $paramInfo;
                        $this->paramPropsByType[$paramInfo['type']] = $paramType;
                    }
                }
            }
        }

        $this->hookupUI();

        $this->setupHooks();

        add_filter('wp_prepare_attachment_for_js', array($this, 'prepareAttachmentForJS'), 1000, 3);

        add_filter('image_get_intermediate_size', [$this, 'imageGetIntermediateSize'], 0, 3);

	    add_action('admin_enqueue_scripts', [$this, 'enqueueTheGoods']);
        add_action('wp_ajax_ilab_dynamic_images_edit_page', [$this, 'displayEditUI']);
        add_action('wp_ajax_ilab_dynamic_images_save', [$this, 'saveAdjustments']);
        add_action('wp_ajax_ilab_dynamic_images_preview', [$this, 'previewAdjustments']);



        add_action('wp_ajax_ilab_dynamic_images_new_preset', [$this, 'newPreset']);
        add_action('wp_ajax_ilab_dynamic_images_save_preset', [$this, 'savePreset']);
        add_action('wp_ajax_ilab_dynamic_images_delete_preset', [$this, 'deletePreset']);

        add_filter('clean_url', [$this, 'fixCleanedUrls'], 1000, 3);

        if (empty($this->settings->keepThumbnails)) {
            add_filter('wp_image_editors', function($editors) {
                array_unshift($editors, '\MediaCloud\Plugin\Tools\DynamicImages\DynamicImageEditor');

                return $editors;
            });
        } else {
	        add_filter('wp_image_editors', function($editors) {
		        array_unshift($editors, '\MediaCloud\Plugin\Tools\Storage\StorageImageEditor');

		        return $editors;
	        }, PHP_INT_MAX);
        }

	    add_filter('wp_get_attachment_metadata', function($metadata, $attachmentId) {
	        if (!isset($metadata['s3'])) {
	            return $metadata;
            }

	        $hasVersion = isset($metadata['s3']['v']) && ($metadata['s3']['v'] == MEDIA_CLOUD_INFO_VERSION);
	        if ($hasVersion && in_array($attachmentId, $this->processedAttachments)) {
	            return $metadata;
            }

	        if (!isset($metadata['s3']['mime-type'])) {
	            $mime = get_post_mime_type($attachmentId);
	            $metadata['s3']['mime-type'] = $mime;
            } else {
		        $mime = $metadata['s3']['mime-type'];
            }

		    if (strpos($mime, 'image/') !== 0) {
		        return $metadata;
            }

		    $metadataFile = arrayPath($metadata, 'file');
		    $width = intval(arrayPath($metadata, 'width', 0));
		    $height = intval(arrayPath($metadata, 'height', 0));

		    if (empty($metadataFile) || empty($width) || empty($height)) {
		        Logger::warning("Metadata for image $attachmentId with mimetype $mime missing info.  File: $metadataFile, width: $width, height: $height", [], __METHOD__, __LINE__);
		        return $metadata;
            }

		    if (!isset($metadata['sizes'])) {
			    Logger::warning("Metadata for image $attachmentId with mimetype $mime missing sizes.", [], __METHOD__, __LINE__);
			    return $metadata;
            }

		    if ($this->allSizes == null) {
			    $this->allSizes = ilab_get_image_sizes();
		    }

		    $filename = pathinfo($metadataFile, PATHINFO_BASENAME);

		    $didChange = false;
		    foreach($this->allSizes as $sizeKey => $sizeData) {
		        if (isset($metadata['sizes'][$sizeKey]) && $hasVersion) {
		            continue;
                }

			    $sizeFilename = arrayPath($metadata, "sizes/$sizeKey/file", $filename);
			    $sizeWidth = intval($sizeData['width']);
			    $sizeHeight = intval($sizeData['height']);
			    $sizeS3 = arrayPath($metadata, "sizes/$sizeKey/s3", $metadata['s3']);
			    $cropData = arrayPath($metadata, "sizes/$sizeKey/crop", []);

		        if (!empty($sizeData['crop'])) {
		            $newWidth = $sizeWidth;
		            $newHeight = $sizeHeight;
                } else {
		            list($newWidth, $newHeight) = sizeToFitSize($width, $height, $sizeWidth, $sizeHeight);
                }

		        $metadata['sizes'][$sizeKey] = [
		            'file' => $sizeFilename,
                    'width' => intval($newWidth),
                    'height' => intval($newHeight),
                    'mime-type' => $mime,
                    's3' => $sizeS3
                ];

		        if (!empty($cropData)) {
		            $metadata['sizes'][$sizeKey]['crop'] = $cropData;
                }

		        $didChange = true;
            }

		    if ($didChange) {
		        $metadata['s3']['v'] = MEDIA_CLOUD_INFO_VERSION;
			    update_post_meta($attachmentId, '_wp_attachment_metadata', $metadata);
            }

		    $this->processedAttachments[] = $attachmentId;

	        return $metadata;
	    }, PHP_INT_MAX, 2);

        // Fix for Foo Gallery
        add_filter('foogallery_thumbnail_resize_args', function($args, $original_image_src, $thumbnail_object) {
            $this->shouldCrop = true;
            $args['force_use_original_thumb'] = true;
            return $args;
        }, 100000, 3);
    }

    protected function setupHooks() {
	    add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 10000, 2);
	    add_filter('image_downsize', [$this, 'imageDownsize'], 1000, 3);
    }

    protected function tearDownHooks() {
	    remove_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 10000);
	    remove_filter('image_downsize', [$this, 'imageDownsize'], 1000);
    }

    public function registerSettings() {
        parent::registerSettings();

        register_setting('ilab-imgix-preset', 'ilab-imgix-presets');
        register_setting('ilab-imgix-preset', 'ilab-imgix-size-presets');
    }

	public function forceEnable($enabled) {
		$this->forcedEnabled = $enabled;
		if ($this->forcedEnabled) {
		    $this->setupHooks();
		} else {
		    $this->tearDownHooks();
		}
	}

    //endregion

    //region URL Generation
    abstract public function buildSizedImage($id, $size);
    abstract public function buildImage($id, $size, $params = null, $skipParams = false, $mergeParams = null, $newSize = null, $newMeta=null);
	abstract public function urlForStorageMedia($key);

    public function fixCleanedUrls($good_protocol_url, $original_url, $context) {
        return $good_protocol_url;
    }

    //endregion

    //region WordPress Hooks & Filters
    /**
     * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
     *
     * @param array $response
     * @param int|object $attachment
     * @param array $meta
     *
     * @return array
     */
    function prepareAttachmentForJS($response, $attachment, $meta) {
        if(!$response || empty($response) || !isset($response['sizes'])) {
            return $response;
        }

        foreach($response['sizes'] as $key => $sizeInfo) {
            $res = $this->buildImage($response['id'], $key);
            if(is_array($res)) {
                $response['sizes'][$key]['url'] = $res[0];
            }
        }

        return $response;
    }

    /**
     * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
     * @param $url
     * @param $post_id
     * @return mixed|string
     */
    public function getAttachmentURL($url, $post_id) {
	    if (!empty(apply_filters('media-cloud/dynamic-images/skip-url-generation', false))) {
		    /** @var StorageTool $storageTool */
		    $storageTool = ToolsManager::instance()->tools['storage'];

		    $result = $storageTool->getAttachmentURL($url, $post_id);
		    return $result;
	    }

        $res = $this->buildImage($post_id, 'full');
        if(!$res || !is_array($res)) {
            return $url;
        }

        $new_url = $res[0];
        if(!$new_url) {
            return $url;
        }

        return $new_url;
    }

    /**
     * Filters whether to preempt the output of image_downsize().  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L201)
     * @param $fail
     * @param $id
     * @param $size
     * @return array|bool
     */
    public function imageDownsize($fail, $id, $size) {
	    if (!empty(apply_filters('media-cloud/dynamic-images/skip-url-generation', false))) {
		    /** @var StorageTool $storageTool */
		    $storageTool = ToolsManager::instance()->tools['storage'];

		    $result = $storageTool->forcedImageDownsize($fail, $id, $size);
		    return $result;
        }

		$this->shouldCrop = apply_filters('media-cloud/dynamic-images/should-crop', $this->shouldCrop);

        $result = $this->buildImage($id, $size);
        return $result;
    }

    /**
     * Filters the image data for intermediate sizes.
     *
     * @param array $data
     * @param int $post_id
     * @param array|string $size
     *
     * @return array
     */
    public function imageGetIntermediateSize($data, $post_id, $size) {
        $result = $this->buildImage($post_id, $size);

        if (is_array($result) && !empty($result)) {
            $data['url'] = $result[0];
        } else if (!empty($data['width']) && !empty($data['height'])) {
            $result = $this->buildSizedImage($post_id, [
                $data['width'],
                $data['height']
            ]);

            if (is_array($result) && !empty($result)) {
                $data['file'] = wp_basename($result[0]);
            }
        }

        return $data;
    }
    //endregion

    //region Imgix Image Editor UI
    /**
     * Enqueue the CSS and JS needed to make the magic happen
     *
     * @param $hook
     */
    public function enqueueTheGoods($hook) {
        add_thickbox();

        if($hook == 'post.php') {
            wp_enqueue_media();
        } else if($hook == 'upload.php') {
            $mode = get_user_option('media_library_mode', get_current_user_id()) ? get_user_option('media_library_mode', get_current_user_id()) : 'grid';
            if(isset($_GET['mode']) && in_array($_GET ['mode'], ['grid', 'list'])) {
                $mode = $_GET['mode'];
                update_user_option(get_current_user_id(), 'media_library_mode', $mode);
            }

            if($mode == 'list') {
                $version = get_bloginfo('version');
                if(version_compare($version, '4.2.2') < 0) {
                    wp_dequeue_script('media');
                }

                wp_enqueue_media();
            }
        } else {
            wp_enqueue_style('media-views');
        }

        wp_enqueue_style('wp-pointer');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-pointer');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('ilab-modal-js', ILAB_PUB_JS_URL.'/ilab-modal.js', ['jquery'], MEDIA_CLOUD_VERSION, true);
        wp_enqueue_script('ilab-media-tools-js', ILAB_PUB_JS_URL.'/ilab-media-tools.js', ['ilab-modal-js'], MEDIA_CLOUD_VERSION, true);
    }

    /**
     * Hook up the "Edit Image" links/buttons in the admin ui
     */
    private function hookupUI() {
        add_filter('media_row_actions', function($actions, $post) {
            $newaction['ilab_edit_image'] = '<a class="ilab-thickbox" href="'.$this->editPageURL($post->ID).'" title="Edit Image">'.__('Edit Image').'</a>';

            return array_merge($actions, $newaction);
        }, 10, 2);

        add_action('wp_enqueue_media', function() {
            remove_action('admin_footer', 'wp_print_media_templates');

            add_action('admin_footer', function() {
                ob_start();
                wp_print_media_templates();
                $result = ob_get_clean();
                echo $result;


                ?>
                <script>
                    jQuery(document).ready(function () {

                        jQuery('input[type="button"]')
                            .filter(function () {
                                return this.id.match(/imgedit-open-btn-[0-9]+/);
                            })
                            .each(function () {
                                var image_id = this.id.match(/imgedit-open-btn-([0-9]+)/)[1];
                                var button = jQuery(this);
                                button.off('click');
                                button.attr('onclick', null);
                                button.on('click', function (e) {
                                    e.preventDefault();

                                    ILabModal.loadURL("<?php echo get_admin_url(null, 'admin-ajax.php')?>?action=ilab_dynamic_images_edit_page&image_id=" + image_id, false, null);

                                    return false;
                                });
                            });

                        jQuery(document).on('click', '.ilab-edit-attachment', function (e) {
                            var button = jQuery(this);
                            var image_id = button.data('id');
                            e.preventDefault();

                            ILabModal.loadURL("<?php echo get_admin_url(null, 'admin-ajax.php')?>?action=ilab_dynamic_images_edit_page&image_id=" + image_id, false, null);

                            return false;
                        });

                        attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            attachTemplate.text(attachTemplate.text().replace('<button type="button" class="button edit-attachment"><?php _e('Edit Image'); ?></button>', '<button type="button" data-id="{{data.id}}" class="button ilab-edit-attachment"><?php _e('Edit Image'); ?></button>'));
                        }

                        attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/, '<a href="<?php echo $this->editPageURL('{{data.id}}')?>" class="ilab-thickbox button edit-imgix"><?php echo __('Edit Image') ?></a>'));
                        }

                        attachTemplate = jQuery('#tmpl-attachment-details');
                        if (attachTemplate)
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/, '<a class="ilab-thickbox edit-imgix" href="<?php echo $this->editPageURL('{{data.id}}')?>"><?php echo __('Edit Image') ?></a>'));
                    });
                </script>
                <?php
            });
        });
    }

    /**
     * Generate the url for the crop UI
     *
     * @param int $id
     * @param string $size
     * @param bool $partial
     * @param string $preset
     *
     * @return string
     */
    public function editPageURL($id, $size = 'full', $partial = false, $preset = null) {
        $url = get_admin_url(null, 'admin-ajax.php')."?action=ilab_dynamic_images_edit_page&image_id=$id";

        $urlParts = parse_url($url);
        $url = str_replace("{$urlParts['scheme']}://{$urlParts['host']}", "", $url);

        if($size != 'full') {
            $url .= "&size=$size";
        }

        if($partial === true) {
            $url .= '&partial=1';
        }

        if($preset != null) {
            $url .= '&preset='.$preset;
        }

        return $url;
    }

    /**
     * Render the edit ui
     *
     * @param bool|int $is_partial
     */
    public function displayEditUI($is_partial = 0) {
        $image_id = esc_html(parse_req('image_id'));
        $current_preset = esc_html(parse_req('preset'));

        $partial = parse_req('partial', $is_partial);

        $size = esc_html(parse_req('size', 'full'));

        $meta = wp_get_attachment_metadata($image_id);

        $attrs = wp_get_attachment_image_src($image_id, $size);
        list($full_src, $full_width, $full_height, $full_cropped) = $attrs;

        $mode = esc_html(parse_req('mode', 'editing'));


        $imgix_settings = [];

        $presets = get_option('ilab-imgix-presets');
        $sizePresets = get_option('ilab-imgix-size-presets');


        $presetsUI = $this->buildPresetsUI($image_id, $size);


        if($current_preset && $presets && isset($presets[$current_preset])) {
            $imgix_settings = $presets[$current_preset]['settings'];
            $full_src = $this->buildImage($image_id, $size, $imgix_settings)[0];
        } else if($size == 'full') {
            if(!$imgix_settings) {
                if(isset($meta['imgix-params'])) {
                    $imgix_settings = $meta['imgix-params'];
                }
            }
        } else {
            if(isset($meta['imgix-size-params'][$size])) {
                $imgix_settings = $meta['imgix-size-params'][$size];
            } else {
                if($presets && $sizePresets && isset($sizePresets[$size]) && isset($presets[$sizePresets[$size]])) {
                    $imgix_settings = $presets[$sizePresets[$size]]['settings'];

                    if(!$current_preset) {
                        $current_preset = $sizePresets[$size];
                    }
                }
            }

            if((!$imgix_settings) && (isset($meta['imgix-params']))) {
                $imgix_settings = $meta['imgix-params'];
            }
        }

        foreach($this->paramPropsByType['media-chooser'] as $key => $info) {
            if(isset($imgix_settings[$key]) && !empty($imgix_settings[$key])) {
                $media_id = $imgix_settings[$key];
                $imgix_settings[$key.'_url'] = wp_get_attachment_url($media_id);
            }
        }

        if(current_user_can('edit_post', $image_id)) {
            if(!$partial) {
                Tracker::trackView("Image Editor", "/image/editor");

                echo View::render_view('imgix/ilab-imgix-ui', [
                    'partial' => $partial,
                    'image_id' => $image_id,
                    'modal_id' => gen_uuid(8),
                    'size' => $size,
                    'sizes' => ilab_get_image_sizes(),
                    'meta' => $meta,
                    'full_width' => $full_width,
                    'full_height' => $full_height,
                    'tool' => $this,
                    'settings' => $imgix_settings,
                    'src' => $full_src,
                    'mode' => $mode,
                    'presets' => $presetsUI,
                    'currentPreset' => $current_preset,
                    'params' => $this->toolInfo['settings']['params'],
                    'paramProps' => $this->paramProps
                ]);
            } else {
                json_response([
                    'status' => 'ok',
	                'mode' => $mode,
                    'image_id' => $image_id,
                    'size' => $size,
                    'settings' => $imgix_settings,
                    'src' => $full_src,
                    'presets' => $presetsUI,
                    'currentPreset' => $current_preset,
                    'paramProps' => $this->paramProps
                ]);
            }
        }


        die;
    }

    /**
     * Builds the presets UI
     *
     * @param int $image_id
     * @param string $size
     *
     * @return array
     */
    private function buildPresetsUI($image_id, $size) {
        $presets = get_option('ilab-imgix-presets');
        if(!$presets) {
            $presets = [];
        }

        $sizePresets = get_option('ilab-imgix-size-presets');
        if(!$sizePresets) {
            $sizePresets = [];
        }

        $presetsUI = [];
        foreach($presets as $pkey => $pinfo) {
            $default_for = '';
            foreach($sizePresets as $psize => $psizePreset) {
                if($psizePreset == $pkey) {
                    $default_for = $psize;
                    break;
                }
            }

            $psettings = $pinfo['settings'];
            foreach($this->paramPropsByType['media-chooser'] as $mkey => $minfo) {
                if(isset($psettings[$mkey])) {
                    if(!empty($psettings[$mkey])) {
                        $psettings[$mkey.'_url'] = wp_get_attachment_url($psettings[$mkey]);
                    }
                }
            }

            $presetsUI[$pkey] = [
                'title' => $pinfo['title'],
                'default_for' => $default_for,
                'settings' => $psettings
            ];
        }

        return $presetsUI;
    }
    //endregion

    //region Imgix Image Editor Ajax
    /**
     * Save The Parameters
     */
    public function saveAdjustments() {
        $image_id = esc_html($_POST['image_id']);
        $size = esc_html($_POST['size']);
        $params = (isset($_POST['settings'])) ? $_POST['settings'] : [];
	    $mode = esc_html($_POST['mode']);

        if(!current_user_can('edit_post', $image_id)) {
            json_response([
                'status' => 'error',
                'message' => 'You are not strong enough, smart enough or fast enough.'
            ]);
        }

        if ($mode == 'size') {
            $name = ucwords(preg_replace('/[-_]/',' ', $size)). ' Default';
            $key = sanitize_title($name);

            if (empty($params) || ((count($params) == 1) && isset($params['markalign']) && empty($params['markalign']))) {
                $this->doDeletePreset($key);
            } else {
	            $this->doUpdatePresets($key, $name, $params, $size, true, false);
            }

	        json_response([
		        'status' => 'ok'
	        ]);
        } else {
	        $meta = wp_get_attachment_metadata($image_id);
	        if(!$meta) {
		        json_response([
			        'status' => 'error',
			        'message' => 'Invalid image id.'
		        ]);
	        }

	        if($size == 'full') {
		        $meta['imgix-params'] = $params;
	        } else {
		        $meta['imgix-size-params'][$size] = $params;
	        }

	        wp_update_attachment_metadata($image_id, $meta);

	        Tracker::trackView("Image Editor - Save Adjustments", '/image/editor/save');
	        json_response([
		        'status' => 'ok'
	        ]);
        }
    }

    /**
     * Preview the adjustment
     */
    public function previewAdjustments() {
        $image_id = esc_html($_POST['image_id']);
        $size = esc_html($_POST['size']);

        if(!current_user_can('edit_post', $image_id)) {
            json_response([
                'status' => 'error',
                'message' => 'You are not strong enough, smart enough or fast enough.'
            ]);
        }


        $this->skipSizeParams = !empty($_POST['forceClean']);
        $params = (isset($_POST['settings'])) ? $_POST['settings'] : [];
        $result = $this->buildImage($image_id, $size, $params);

        json_response(['status' => 'ok', 'src' => $result[0]]);
    }

    /**
     * Update the presets
     *
     * @param string $key
     * @param string $name
     * @param array $settings
     * @param string $size
     * @param bool $makeDefault
     */
    private function doUpdatePresets($key, $name, $settings, $size, $makeDefault, $sendResponse = true) {
        $image_id = esc_html($_POST['image_id']);
        $presets = get_option('ilab-imgix-presets');
        if(!$presets) {
            $presets = [];
        }

        $presets[$key] = [
            'title' => $name,
            'settings' => $settings
        ];
        update_option('ilab-imgix-presets', $presets);

        $sizePresets = get_option('ilab-imgix-size-presets');
        if(!$sizePresets) {
            $sizePresets = [];
        }

        if($size && $makeDefault) {
            $sizePresets[$size] = $key;
        } else if($size && !$makeDefault) {
            foreach($sizePresets as $s => $k) {
                if($k == $key) {
                    unset($sizePresets[$s]);
                }
            }
        }

        update_option('ilab-imgix-size-presets', $sizePresets);

        if ($sendResponse) {
	        json_response([
		        'status' => 'ok',
		        'currentPreset' => $key,
		        'presets' => $this->buildPresetsUI($image_id, $size)
	        ]);
        }
    }

    /**
     * Create a new preset
     */
    public function newPreset() {
        $name = esc_html($_POST['name']);
        if(empty($name)) {
            json_response([
                'status' => 'error',
                'error' => 'Seems that you may have forgotten something.'
            ]);
        }

        $key = sanitize_title($name);
        $newKey = $key;

        $presets = get_option('ilab-imgix-presets');
        if($presets) {
            $keyIndex = 1;
            while(isset($presets[$newKey])) {
                $keyIndex ++;
                $newKey = $key.$keyIndex;
            }
        }

        $settings = $_POST['settings'];
        $size = (isset($_POST['size'])) ? esc_html($_POST['size']) : null;
        $makeDefault = (isset($_POST['make_default'])) ? ($_POST['make_default'] == 1) : false;

	    Tracker::trackView("Image Editor - New Preset", '/image/editor/preset/new');

        $this->doUpdatePresets($newKey, $name, $settings, $size, $makeDefault);

    }

    /**
     * Save an existing preset
     */
    public function savePreset() {
        $key = esc_html($_POST['key']);
        if(empty($key)) {
            json_response([
                'status' => 'error',
                'error' => 'Seems that you may have forgotten something.'
            ]);
        }

        $presets = get_option('ilab-imgix-presets');
        if(!isset($presets[$key])) {
            json_response([
                'status' => 'error',
                'error' => 'Seems that you may have forgotten something.'
            ]);
        }

        $name = $presets[$key]['title'];
        $settings = $_POST['settings'];
        $size = (isset($_POST['size'])) ? esc_html($_POST['size']) : null;
        $makeDefault = (isset($_POST['make_default'])) ? ($_POST['make_default'] == 1) : false;

        if ($makeDefault) {
	        Tracker::trackView("Image Editor - Save Default Preset", '/image/editor/preset/save-default');
        } else {
	        Tracker::trackView("Image Editor - Save Preset", '/image/editor/preset/save');
        }

        $this->doUpdatePresets($key, $name, $settings, $size, $makeDefault);
    }

    /**
     * Delete an existing preset
     */
    public function deletePreset() {
        $key = esc_html($_POST['key']);
        if(empty($key)) {
            json_response([
                'status' => 'error',
                'error' => 'Seems that you may have forgotten something.'
            ]);
        }

	    Tracker::trackView("Image Editor - Delete Preset", '/image/editor/preset/delete');

        $this->doDeletePreset($key);

        return $this->displayEditUI(1);
    }

    private function doDeletePreset($key) {
	    $presets = get_option('ilab-imgix-presets');
	    if($presets) {
		    unset($presets[$key]);
		    update_option('ilab-imgix-presets', $presets);
	    }

	    $sizePresets = get_option('ilab-imgix-size-presets');
	    if(!$sizePresets) {
		    $sizePresets = [];
	    }

	    foreach($sizePresets as $size => $preset) {
		    if($preset == $key) {
			    unset($sizePresets[$size]);
			    break;
		    }
	    }

	    update_option('ilab-imgix-size-presets', $sizePresets);
    }
    //endregion

    //region Static Methods

	/**
     * The current dynamic images tool
	 * @return DynamicImagesTool|null
	 */
    public static function currentDynamicImagesTool() {
        if (ToolsManager::instance()->toolEnabled('imgix')) {
            return ToolsManager::instance()->tools['imgix'];
        }

        return null;
    }
}
