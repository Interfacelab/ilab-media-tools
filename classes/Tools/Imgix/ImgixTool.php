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

namespace ILAB\MediaCloud\Tools\Imgix;

use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolBase;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use function ILAB\MediaCloud\Utilities\gen_uuid;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\NoticeManager;
use function ILAB\MediaCloud\Utilities\parse_req;
use ILAB\MediaCloud\Utilities\View;
use Imgix\UrlBuilder;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}


/**
 * Class ILabMediaImgixTool
 *
 * Imgix tool.
 */
class ImgixTool extends ToolBase {
	//region Class Variables
	protected $imgixDomains;
	protected $signingKey;
	protected $imageQuality;
	protected $autoFormat;
	protected $autoCompress;
	protected $enableGifs;
	protected $skipGifs;
	protected $paramPropsByType;
	protected $paramProps;
	protected $noGifSizes;
	protected $useHTTPS;
	protected $enabledAlternativeFormats;
	protected $renderPDF;
	protected $detectFaces;
	protected $keepThumbnails;

	private $shouldCrop = false;

	//endregion

    //region Constructor
    public function __construct($toolName, $toolInfo, $toolManager) {
	    parent::__construct($toolName, $toolInfo, $toolManager);

	    add_filter('ilab_imgix_enabled', function($enabled){
	        return $this->enabled();
        });

	    add_filter('ilab_imgix_alternative_formats', function($enabled){
	        return $this->enabledAlternativeFormats;
        });

	    $this->testForBadPlugins();
        $this->testForUselessPlugins();
    }
    //endregion

	//region ToolBase Overrides
	public function enabled() {
		$enabled = parent::enabled();

		if(!$this->getOption('ilab-media-imgix-domains')) {
			NoticeManager::instance()->displayAdminNotice('error', "To start using Imgix, you will need to <a href='admin.php?page=media-tools-imgix'>set it up</a>.", true, 'disable-ilab-imgix-warning');

			return false;
		}

		return $enabled;
	}

	public function setup() {
		if(!$this->enabled()) {
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

		$this->noGifSizes = [];
		$noGifSizes = $this->getOption('ilab-media-imgix-no-gif-sizes', null, '');
		$noGifSizesArray = explode("\n", $noGifSizes);
		if(count($noGifSizesArray) <= 1) {
			$noGifSizesArray = explode(',', $noGifSizes);
		}
		foreach($noGifSizesArray as $gs) {
			if(!empty($gs)) {
				$this->noGifSizes[] = trim($gs);
			}
		}

		$this->imgixDomains = [];
		$domains = $this->getOption('ilab-media-imgix-domains', null, '');
		$domain_lines = explode("\n", $domains);
		if(count($domain_lines) <= 1) {
			$domain_lines = explode(',', $domains);
		}
		foreach($domain_lines as $d) {
			if(!empty($d)) {
				$this->imgixDomains[] = trim($d);
			}
		}

		$this->useHTTPS = EnvironmentOptions::Option('ilab-media-imgix-use-https', null, true);

		$this->signingKey = EnvironmentOptions::Option('ilab-media-imgix-signing-key');

		$this->imageQuality = EnvironmentOptions::Option('ilab-media-imgix-default-quality');
		$this->autoFormat = EnvironmentOptions::Option('ilab-media-imgix-auto-format');
		$this->autoCompress = EnvironmentOptions::Option('ilab-media-imgix-auto-compress');
		$this->enableGifs = EnvironmentOptions::Option('ilab-media-imgix-enable-gifs');
		$this->skipGifs = EnvironmentOptions::Option('ilab-media-imgix-skip-gifs', null, false);
		$this->detectFaces = EnvironmentOptions::Option('ilab-media-imgix-detect-faces', null, false);

		$this->enabledAlternativeFormats = EnvironmentOptions::Option('ilab-media-imgix-enable-alt-formats');
		$this->renderPDF = EnvironmentOptions::Option('ilab-media-imgix-render-pdf-files');
        $this->keepThumbnails = EnvironmentOptions::Option('ilab-media-imgix-generate-thumbnails', null, true);

		add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 10000, 2);
		add_filter('wp_prepare_attachment_for_js', array($this, 'prepareAttachmentForJS'), 1000, 3);

		add_filter('image_downsize', [$this, 'imageDownsize'], 1000, 3);

		$this->hookupUI();

		add_action('admin_enqueue_scripts', [$this, 'enqueueTheGoods']);
		add_action('wp_ajax_ilab_imgix_edit_page', [$this, 'displayEditUI']);
		add_action('wp_ajax_ilab_imgix_save', [$this, 'saveAdjustments']);
		add_action('wp_ajax_ilab_imgix_preview', [$this, 'previewAdjustments']);


		add_action('wp_ajax_ilab_imgix_new_preset', [$this, 'newPreset']);
		add_action('wp_ajax_ilab_imgix_save_preset', [$this, 'savePreset']);
		add_action('wp_ajax_ilab_imgix_delete_preset', [$this, 'deletePreset']);

		if (!$this->keepThumbnails) {
            add_filter('wp_image_editors', function($editors) {
                array_unshift($editors, '\ILAB\MediaCloud\Tools\Imgix\ImgixImageEditor');

                return $editors;
            });
        }

		// Fix for Foo Gallery
        add_filter('foogallery_thumbnail_resize_args', function($args, $original_image_src, $thumbnail_object) {
            $this->shouldCrop = true;
            $args['force_use_original_thumb'] = true;
            return $args;
        }, 100000, 3);

		if($this->enabledAlternativeFormats) {
			add_filter('file_is_displayable_image', [$this, "fileIsDisplayableImage"], 0, 2);

			add_filter('upload_mimes', function($mime_types) {
				$mime_types['ai'] = "application/vnd.adobe.illustrator";

				return $mime_types;
			}, 1, 1);

			add_filter('wp_generate_attachment_metadata', [$this, "generateAttachmentMetadata"], 1000, 2);

			add_filter('wp_check_filetype_and_ext', function($file, $filePath, $filename, $mimes) {
				if($file['ext'] == 'psd') {
					$file['type'] = 'image/psd';
				}

				if($file['ext'] == 'ai') {
					$file['type'] = 'application/vnd.adobe.illustrator';
				}

				return $file;
			}, 0, 4);
		}

		add_filter('imgix_build_gif_mpeg4', [$this, 'buildMpeg4'], 0, 3);
		add_filter('imgix_build_gif_jpeg', [$this, 'buildGifJpeg'], 0, 3);

		add_filter('ilab_imgix_render_pdf', function() {
			return $this->renderPDF;
		});

		do_action('ilab_imgix_setup');

		add_filter('imgix_build_srcset_url', [$this, 'buildSrcSetURL'], 0, 3);
		add_filter('image_get_intermediate_size', [$this, 'imageGetIntermediateSize'], 0, 3);
		add_filter('media_send_to_editor', [$this, 'mediaSendToEditor'], 0, 3);

		if ($this->detectFaces) {
			add_filter('ilab_s3_after_upload', [$this, 'processImageMeta'], 1000, 2);
        }
	}

	public function registerSettings() {
		parent::registerSettings();

		register_setting('ilab-imgix-preset', 'ilab-imgix-presets');
		register_setting('ilab-imgix-preset', 'ilab-imgix-size-presets');
	}
	//endregion

    //region URL Generation
	/**
     * Builds the parameters for generating Imgix URLs
     *
	 * @param array $params
	 * @param string $mimetype
	 *
	 * @return array
	 */
	private function buildImgixParams($params, $mimetype = '') {
		$format = null;
		if(!isset($params['fm'])) {
			if(($mimetype == 'image/gif') && $this->enableGifs) {
				$format = 'gif';
			} else if(($mimetype == 'image/png') && !$this->autoFormat) {
				$format = 'png';
			} else if(!$this->autoFormat) {
				$format = 'pjpg';
			}
		} else {
			$format = $params['fm'];
		}

		unset($params['fm']);

		$auto = [];
		if(isset($params['auto'])) {
			$auto = explode(',', $params['auto']);
			unset($params['auto']);
		}

		if (isset($params['flip']) && (strpos($params['flip'], ',') > 0)) {
		    $params['flip'] = 'hv';
        }

		if(!$format) {
			if($this->autoCompress && $this->autoFormat) {
				$auto[] = 'compress';
				$auto[] = 'format';
			} else if($this->autoCompress) {
				$auto[] = 'compress';
			} else if($this->autoFormat) {
				$auto[] = 'format';
			}
		} else if($format) {
			$params['fm'] = $format;
			if($this->autoCompress) {
				$auto[] = 'compress';
			}
		} else {
			$params['fm'] = 'pjpg';
			if($this->autoCompress) {
				$auto[] = 'compress';
			}
		}

		if(count($auto) > 0) {
			$params['auto'] = implode(",", $auto);
		}

		unset($params['enhance']);
		unset($params['redeye']);

		if($this->imageQuality) {
			$params['q'] = $this->imageQuality;
		}

		foreach($this->paramPropsByType['media-chooser'] as $key => $info) {
			if(isset($params[$key]) && !empty($params[$key])) {
				$media_id = $params[$key];
				unset($params[$key]);
				$markMeta = wp_get_attachment_metadata($media_id);
				if (isset($markMeta['s3'])) {
					$params[$info['imgix-param']] = '/'.$markMeta['s3']['key'];
                } else {
					$params[$info['imgix-param']] = '/'.$markMeta['file'];
                }
			} else {
				unset($params[$key]);
				if(isset($info['dependents'])) {
					foreach($info['dependents'] as $depKey) {
						unset($params[$depKey]);
					}
				}
			}
		}

		if(isset($params['border-width']) && isset($params['border-color'])) {
			$params['border'] = $params['border-width'].','.str_replace('#', '', $params['border-color']);
		}

		unset($params['border-width']);
		unset($params['border-color']);

		if(isset($params['padding-width'])) {
			$params['pad'] = $params['padding-width'];

			if(isset($params['padding-color'])) {
				$params['bg'] = $params['padding-color'];
			}
		}

		unset($params['padding-width']);
		unset($params['padding-color']);

		return $params;
	}

	/**
     * Builds an Imgix URL for a dynamically sized image.
     *
	 * @param int $id
	 * @param array $size
	 *
	 * @return array|bool
	 */
	private function buildSizedImgixImage($id, $size) {
		$meta = wp_get_attachment_metadata($id);
		if(!$meta || empty($meta)) {
			return false;
		}

		if(!isset($meta['s3'])) {
			return [];
		}

		$imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

		if($this->signingKey) {
			$imgix->setSignKey($this->signingKey);
		}


		$is_crop = ((count($size) >= 3) && ($size[2] == 'crop'));
		if (!$is_crop && $this->shouldCrop) {
		    $this->shouldCrop = false;
		    $is_crop = true;
        }

        if ($is_crop && (($size[0] === 0) || ($size[1] === 0))) {
		    if ($size[0] === 0) {
		        $size[0] = 10000;
            } else {
		        $size[1] = 10000;
            }

		    $is_crop = false;
        }

		if(isset($size['width'])) {
			$size = [
				$size['width'],
				$size['height']
			];
		}

		$params = [
			'fit' => ($is_crop) ? 'crop' : 'fit',
			'w' => $size[0],
			'h' => $size[1],
			'fm' => 'jpg'
		];
		$params = apply_filters('ilab-imgix-filter-parameters', $params, $size, $id, $meta);

		$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

		$result = [
			$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), $params),
			$size[0],
			$size[1]
		];

		return $result;
	}

	/**
     * Builds an Imgix URL for an MPEG-4 video for an animated GIF source.  This is called with apply_filter('imgix_build_gif_mpeg4').
     *
	 * @param mixed $value
	 * @param int $postId
	 * @param string $size
	 *
	 * @return array
	 */
	public function buildMpeg4($value, $postId, $size) {
		return $this->buildImgixImage($postId, $size, null, false, ['fmt' => 'mp4']);
	}

	/**
	 * Builds an Imgix URL for a poster jpeg for an animated GIF source.  This is called with apply_filter('imgix_build_gif_jpeg').
	 *
	 * @param mixed $value
	 * @param int $postId
	 * @param string $size
	 *
	 * @return array
	 */
	public function buildGifJpeg($value, $postId, $size) {
		return $this->buildImgixImage($postId, $size, null, false, ['fmt' => 'pjpg']);
	}

	/**
	 * Builds the URL for a srcSet Image
	 *
	 * @param int $post_id
	 * @param array $parentSize
	 * @param array $newSize
	 *
	 * @return array|bool
	 */
	public function buildSrcSetURL($post_id, $parentSize, $newSize) {
		return $this->buildImgixImage($post_id, $parentSize, null, false, null, $newSize);
	}

	/**
     * Builds an Imgix URL
     *
	 * @param int $id
	 * @param string|array $size
	 * @param array|null $params
	 * @param bool $skipParams
	 * @param array|null $mergeParams
	 * @param array|null $newSize
     * @param array|null $newMeta
	 *
	 * @return array|bool
	 */
	private function buildImgixImage($id, $size, $params = null, $skipParams = false, $mergeParams = null, $newSize = null, $newMeta=null) {
		if(is_array($size)) {
			return $this->buildSizedImgixImage($id, $size);
		}

		$mimetype = get_post_mime_type($id);

        if (!$this->renderPDF && ($mimetype == 'application/pdf')) {
            return false;
        }

		$meta = wp_get_attachment_metadata($id);
		if(!$meta || empty($meta)) {
			if(($this->renderPDF && ($mimetype == 'application/pdf')) || ($this->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
				$meta = get_post_meta($id, 'ilab_s3_info', true);
			}

			if(!$meta || empty($meta)) {
			    if (!empty($newMeta)) {
			        $meta = $newMeta;
                } else {
				    return false;
                }
			}
		}

		if(!isset($meta['s3'])) {
			if(($this->renderPDF && ($mimetype == 'application/pdf')) || ($this->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
				$meta = get_post_meta($id, 'ilab_s3_info', true);
			} else {
				return false;
			}
		}

		$imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

		if($this->signingKey) {
			$imgix->setSignKey($this->signingKey);
		}

		if($size == 'full' && !$newSize) {
			if(!isset($meta['width']) || !isset($meta['height'])) {
				return false;
			}

			if(!$params) {
				if(isset($meta['imgix-params'])) {
					$params = $meta['imgix-params'];
				} else {
					$params = [];
				}
			}

			$params = $this->buildImgixParams($params, $mimetype);
			$params = apply_filters('ilab-imgix-filter-parameters', $params, $size, $id, $meta);

			if(!isset($meta['file'])) {
				return null;
			}

			$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

			$result = [
				$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), ($skipParams) ? [] : $params),
				$meta['width'],
				$meta['height'],
				false
			];

			return $result;
		}

		if($newSize) {
			$sizeInfo = $newSize;
		} else {
			$sizeInfo = ilab_get_image_sizes($size);
		}

		if(!$sizeInfo) {
			return false;
		}

		$metaSize = null;
		if(isset($meta['sizes'][$size])) {
			$metaSize = $meta['sizes'][$size];
		}

		$doCrop = !empty($sizeInfo['crop']);

		if(!$params) {
		    $sizeParams = (!empty($sizeInfo['imgix']) && is_array($sizeInfo['imgix'])) ? $sizeInfo['imgix'] : [];
		    $sizeCropParams = (isset($sizeParams['crop'])) ? $sizeParams['crop'] : [];
		    if (!empty($sizeCropParams)) {
                if (is_string($sizeCropParams)) {
                    $sizeCropParams = explode(',', $sizeCropParams);
                    unset($sizeParams['crop']);
                }

                $doCrop = true;
            }

            // get the settings for this image at this size
			if(isset($meta['imgix-size-params'][$size])) {
				$params = array_merge($sizeParams, $meta['imgix-size-params'][$size]);
			}


			if(!$params || (count($params) == 0)) // see if a preset has been globally assigned to a size and use that
			{
				$presets = get_option('ilab-imgix-presets');
				$sizePresets = get_option('ilab-imgix-size-presets');

				if($presets && $sizePresets && isset($sizePresets[$size]) && isset($presets[$sizePresets[$size]])) {
					$params = array_merge($sizeParams, $presets[$sizePresets[$size]]['settings']);
				}
			}

			// still no parameters?  use any that may have been assigned to the full size image
			if((!$params || (count($params) == 0)) && (isset($meta['imgix-params']))) {
				$params = array_merge($sizeParams, $meta['imgix-params']);
			} else if(!$params) // too bad so sad
			{
				$params = $sizeParams;
			}
		}

		if ($doCrop) {
			$params['w'] = $sizeInfo['width'] ?: $sizeInfo['height'];
			$params['h'] = $sizeInfo['height'] ?: $sizeInfo['width'];
			$params['fit'] = 'crop';

			if($metaSize) {
				$metaSize = $meta['sizes'][$size];
				if(isset($metaSize['crop'])) {
					$metaSize['crop']['x'] = round($metaSize['crop']['x']);
					$metaSize['crop']['y'] = round($metaSize['crop']['y']);
					$metaSize['crop']['w'] = round($metaSize['crop']['w']);
					$metaSize['crop']['h'] = round($metaSize['crop']['h']);
					$params['rect'] = implode(',', $metaSize['crop']);
				}
			}

			// we don't want to scale animated gifs AT ALL on the front end
			if(($mimetype == 'image/gif') && (!is_admin())) {
				$imageW = $meta['width'];
				$imageH = $meta['height'];

				$pw = $params['w'];
				$ph = $params['h'];

				if(($pw > $imageW) || ($ph > $imageH)) {
					$newSize = sizeToFitSize($pw, $ph, $imageW, $imageH);
					$params['w'] = $newSize[0];
					$params['h'] = $newSize[1];
				}
			}

			if (isset($params['focalpoint'])) {
			    unset($params['rect']);

			    if ($params['focalpoint'] == 'entropy') {
				    $params['crop'] = 'entropy';
				    unset($params['fp-x']);
				    unset($params['fp-y']);
				    unset($params['fp-z']);
                } else if ($params['focalpoint'] == 'edges') {
				    $params['crop'] = 'edges';
				    unset($params['fp-x']);
				    unset($params['fp-y']);
				    unset($params['fp-z']);
                } else {
				    $params['crop'] = 'focalpoint';
				    if ($params['focalpoint'] == 'usefaces') {
					    unset($params['fp-x']);
					    unset($params['fp-y']);

					    if (isset($meta['faces'])) {
						    $faceindex = arrayPath($params,'faceindex', 0);
						    if ((count($meta['faces'])>1) && ($faceindex == 0)) {
							    $left = 900000;
							    $top = 900000;
							    $right = 0;
							    $bottom = 0;

							    foreach($meta['faces'] as $face) {
								    $bb = $face['BoundingBox'];
								    $left = min($left, $bb['Left']);
								    $top = min($top, $bb['Top']);
								    $right = max($right, $bb['Left'] + $bb['Width']);
								    $bottom = max($bottom, $bb['Top'] + $bb['Height']);
							    }

							    $params['fp-x'] = $left + (($right - $left) / 2.0);
							    $params['fp-y'] = $top + (($bottom - $top) / 2.0);
						    } else {
							    if ($faceindex == 0) {
								    $faceindex = 1;
							    }

							    $faceindex = min(count($meta['faces'])+1, $faceindex);
							    $bb = $meta['faces'][$faceindex - 1]['BoundingBox'];

							    $params['fp-x'] = $bb['Left'] + ($bb['Width'] / 2.0);
							    $params['fp-y'] = $bb['Top'] + ($bb['Height'] / 2.0);
						    }

					    } else {
						    unset($params['crop']);
						    unset($params['fp-z']);
					    }
				    }
                }
            } else {
				unset($params['fp-x']);
				unset($params['fp-y']);
				unset($params['fp-z']);

                $cropParams = [];

                if (!empty($sizeInfo['crop']) && is_array($sizeInfo['crop'])) {
                    list($cropX, $cropY) = $sizeInfo['crop'];
                    if (!empty($cropX) && ($cropX != 'center')) {
                        $cropParams[] = $cropX;
                    }
                    if (!empty($cropY) && ($cropY != 'center')) {
                        $cropParams[] = $cropY;
                    }
                }

                if (!empty($sizeCropParams)) {
                    $cropParams = array_merge($cropParams, $sizeCropParams);
                }

                if (!empty($cropParams)) {
                    $params['crop'] = implode(",", $cropParams);
                }
            }
		} else {
			$mw = !empty($meta['width']) ? $meta['width'] : 10000;
			$mh = !empty($meta['height']) ? $meta['height'] : 10000;

			$w = !empty($sizeInfo['width']) ? $sizeInfo['width'] : 10000;
			$h = !empty($sizeInfo['height']) ? $sizeInfo['height'] : 10000;

			$newSize = sizeToFitSize($mw, $mh, $w, $h);
			$params['w'] = $newSize[0];
			$params['h'] = $newSize[1];
			$params['fit'] = 'scale';

			unset($params['fp-x']);
			unset($params['fp-y']);
			unset($params['fp-z']);
		}

		unset($params['focalpoint']);
		unset($params['faceindex']);

		if($mergeParams && is_array($mergeParams)) {
			$params = array_merge($params, $mergeParams);
		}

		if($size && !is_array($size)) {
			$params['wpsize'] = $size;
		}

		$params = $this->buildImgixParams($params, $mimetype);
		$params = apply_filters('ilab-imgix-filter-parameters', $params, $size, $id, $meta);

		$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

		$result = [
			$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), $params),
			$params['w'],
			$params['h'],
			true
		];

		return $result;
	}
	//endregion

    //region Face Detection
	/**
	 * Process an image through Rekognition
	 *
	 * @param array $meta
	 * @param int $postID
	 *
	 * @return array
	 */
	public function processImageMeta($meta, $postID) {
		if (!$this->enabled()) {
			return $meta;
		}

        if (apply_filters('ilab_rekognition_enabled', false)) {
		    if (apply_filters('ilab_rekognition_detects_faces', false)) {
		        return $meta;
            }
        }

		if (!isset($meta['s3'])) {
			Logger::warning( "Post $postID is  missing 's3' metadata.", $meta);
			return $meta;
		}

		$url = $this->buildImgixImage($postID, 'full',['fm' => 'json','faces'=>1],false,null,null,$meta);
		if ($url && is_array($url)) {
			$jsonString = file_get_contents($url[0]);
			if (!empty($jsonString)) {
				$data = json_decode($jsonString, true);

				$pw = arrayPath($data,'PixelWidth',0);
				$ph = arrayPath($data,'PixelHeight',0);

				if (!empty($pw) && !empty($ph)) {
				    $facesData = arrayPath($data,'Faces',[]);
					$faces = [];
				    foreach($facesData as $face) {
					    $faces[] = [
						    'BoundingBox' => [
							    'Left' => floatval($face['bounds']['x']) / floatval($pw),
							    'Top' => floatval($face['bounds']['y']) / floatval($ph),
							    'Width' => floatval($face['bounds']['width']) / floatval($pw),
							    'Height' => floatval($face['bounds']['height']) / floatval($ph),
						    ]
					    ];
                    }

					if (count($faces)>0) {
						$meta['faces'] = $faces;
					}
                }
            }
		}

		return $meta;
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

		if($this->renderPDF && isset($meta['s3'])) {
			if($attachment->post_mime_type == 'application/pdf') {
				$response['type'] = 'image';
				$response['mime'] = 'image/pdf';
				if(isset($meta['width'])) {
					$response['width'] = round($meta['width']);
				}

				if(isset($meta['height'])) {
					$response['height'] = round($meta['height']);
				}

				if(isset($response['width']) && isset($response['height'])) {
					$response['orientation'] = ($response['width'] > $response['height']) ? 'landscape' : 'portrait';
				}
			}
		}

		$generateUrls = !($this->skipGifs && ($attachment->post_mime_type == 'image/gif'));
		if ($generateUrls) {
            foreach($response['sizes'] as $key => $sizeInfo) {
                $res = $this->buildImgixImage($response['id'], $key);
                if(is_array($res)) {
                    $response['sizes'][$key]['url'] = $res[0];
                }
            }
        }

		return $response;
	}

	/**
	 * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
	 * @param string $url
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function getAttachmentURL($url, $post_id) {
	    if ($this->skipGifs) {
	        $mimeType = get_post_mime_type($post_id);
	        if ($mimeType == 'image/gif') {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];

                $gifURL = $storageTool->getAttachmentURL($url, $post_id);
                return $gifURL;
            }
        }

		$res = $this->buildImgixImage($post_id, 'full');
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
     * @throws \ILAB\MediaCloud\Cloud\Storage\StorageException
     */
	public function imageDownsize($fail, $id, $size) {
        if ($this->skipGifs) {
            $mimeType = get_post_mime_type($id);
            if ($mimeType == 'image/gif') {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];

                $result = $storageTool->forcedImageDownsize($fail, $id, $size);
                return $result;
            }
        }

		$result = $this->buildImgixImage($id, $size);

		return $result;
	}


	/**
	 * Filters the image data for intermediate sizes.
	 *
	 * @param array $data
	 * @param int $post_idid
	 * @param array|string $size
	 *
	 * @return array
	 */
	public function imageGetIntermediateSize($data, $post_id, $size) {
	    $result = $this->buildImgixImage($post_id, $size);

	    if (is_array($result) && !empty($result)) {
            $data['url'] = $result[0];
        }

		return $data;
	}

	/**
     * Filters the generated attachment meta data. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/image.php#L292)
	 *
     * @param array $metadata
	 * @param int $attachment_id
	 *
	 * @return array
	 */
	public function generateAttachmentMetadata($metadata, $attachment_id) {
		if(!$this->enabledAlternativeFormats) {
			return $metadata;
		}

		$mime = get_post_mime_type($attachment_id);
		if($mime == 'application/vnd.adobe.illustrator') {
			$file = get_attached_file($attachment_id);
			$fileName = pathinfo($file, PATHINFO_BASENAME);
			$fallback_sizes = array(
				'thumbnail',
				'medium',
				'medium_large',
				'large',
			);
			$fallback_sizes = apply_filters('fallback_intermediate_image_sizes', $fallback_sizes, $metadata);
			$additional_sizes = wp_get_additional_image_sizes();

			$metadata = [
				'sizes' => [
					'full' => [
						'file' => $fileName,
						'width' => null,
						'height' => null,
						'mime-type' => 'image/jpeg'
					]
				]
			];

			foreach($fallback_sizes as $sz) {
				$metadata['sizes'][$sz] = [
					'file' => $fileName,
					'width' => get_option("{$sz}_size_w"),
					'height' => get_option("{$sz}_size_h"),
					'mime-type' => 'image/jpeg'
				];
			}

			foreach($additional_sizes as $size => $sizeInfo) {
				if(isset($sizeInfo['crop']) && $sizeInfo['crop']) {
					$metadata['sizes'][$size] = [
						'file' => $fileName,
						'width' => $sizeInfo['width'],
						'height' => $sizeInfo['height'],
						'mime-type' => 'image/jpeg'
					];
				}
			}

			$upload_info = wp_upload_dir();
			$upload_path = $upload_info['basedir'];
			$metadata['file'] = trim(str_replace($upload_path, '', $file), '/');
		}

		return $metadata;
	}

	/**
     * Filters whether the current image is displayable in the browser. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/image.php#L558)
	 *
     * @param bool $result
	 * @param string $path
	 *
	 * @return bool
	 */
	public function fileIsDisplayableImage($result, $path) {
		$mime = wp_get_image_mime($path);
		if(!$mime) {
			$ftype = wp_check_filetype($path);
			if(!empty($ftype) && isset($ftype['type'])) {
				$mime = $ftype['type'];
			}

			$ext = pathinfo($path, PATHINFO_EXTENSION);
			if(('ai' == strtolower($ext)) && ("application/pdf" == $mime)) {
				return true;
			}
		}

		if($mime == 'application/vnd.adobe.illustrator') {
			return true;
		}

		if($mime == 'image/tiff') {
			return true;
		}

		if($mime == 'image/psd') {
			return true;
		}

		return $result;
	}

	/**
     * Filters the HTML markup for a media item sent to the editor. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/media.php#L739)
	 *
     * @param string $html
	 * @param int $id
	 * @param array $attachment
	 *
	 * @return string
	 */
	public function mediaSendToEditor($html, $id, $attachment) {
		if(!$this->renderPDF) {
			return $html;
		}

		$mime = get_post_mime_type($id);
		if($mime == 'application/pdf') {
			$align = isset($attachment['align']) ? $attachment['align'] : 'none';
			$size = isset($attachment['image-size']) ? $attachment['image-size'] : 'medium';
			$alt = isset($attachment['image_alt']) ? $attachment['image_alt'] : '';

			// No whitespace-only captions.
			$caption = isset($attachment['post_excerpt']) ? $attachment['post_excerpt'] : '';
			if('' === trim($caption)) {
				$caption = '';
			}

			$url = empty($attachment['url']) ? '' : $attachment['url'];
			$rel = (strpos($url, 'attachment_id') || get_attachment_link($id) == $url);

			$title = ''; // We no longer insert title tags into <img> tags, as they are redundant.
			$html = get_image_send_to_editor($id, $caption, $title, $align, $url, $rel, $size, $alt);

		}

		return $html;
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
		wp_enqueue_style('ilab-modal-css', ILAB_PUB_CSS_URL.'/ilab-modal.min.css');
		wp_enqueue_style('ilab-media-tools-css', ILAB_PUB_CSS_URL.'/ilab-media-tools.min.css');
		wp_enqueue_script('wp-pointer');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('ilab-modal-js', ILAB_PUB_JS_URL.'/ilab-modal.js', ['jquery'], false, true);
		wp_enqueue_script('ilab-media-tools-js', ILAB_PUB_JS_URL.'/ilab-media-tools.js', ['ilab-modal-js'], false, true);
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

                                    ILabModal.loadURL("<?php echo get_admin_url(null, 'admin-ajax.php')?>?action=ilab_imgix_edit_page&image_id=" + image_id, false, null);

                                    return false;
                                });
                            });

                        jQuery(document).on('click', '.ilab-edit-attachment', function (e) {
                            var button = jQuery(this);
                            var image_id = button.data('id');
                            e.preventDefault();

                            ILabModal.loadURL("<?php echo get_admin_url(null, 'admin-ajax.php')?>?action=ilab_imgix_edit_page&image_id=" + image_id, false, null);

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
		$url = get_admin_url(null, 'admin-ajax.php')."?action=ilab_imgix_edit_page&image_id=$id";

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


		$imgix_settings = [];

		$presets = get_option('ilab-imgix-presets');
		$sizePresets = get_option('ilab-imgix-size-presets');


		$presetsUI = $this->buildPresetsUI($image_id, $size);


		if($current_preset && $presets && isset($presets[$current_preset])) {
			$imgix_settings = $presets[$current_preset]['settings'];
			$full_src = $this->buildImgixImage($image_id, $size, $imgix_settings)[0];
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
				echo View::render_view('imgix/ilab-imgix-ui.php', [
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
					'presets' => $presetsUI,
					'currentPreset' => $current_preset,
					'params' => $this->toolInfo['settings']['params'],
					'paramProps' => $this->paramProps
				]);
			} else {
				json_response([
					              'status' => 'ok',
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

		if(!current_user_can('edit_post', $image_id)) {
			json_response([
				              'status' => 'error',
				              'message' => 'You are not strong enough, smart enough or fast enough.'
			              ]);
		}


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

		json_response([
			              'status' => 'ok'
		              ]);
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


		$params = (isset($_POST['settings'])) ? $_POST['settings'] : [];
		$result = $this->buildImgixImage($image_id, $size, $params);

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
	private function doUpdatePresets($key, $name, $settings, $size, $makeDefault) {
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

		json_response([
			              'status' => 'ok',
			              'currentPreset' => $key,
			              'presets' => $this->buildPresetsUI($image_id, $size)
		              ]);

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

		return $this->displayEditUI(1);
	}
    //endregion

    //region Testing

    public function urlForKey($key) {
        $imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

        if($this->signingKey) {
            $imgix->setSignKey($this->signingKey);
        }

        return $imgix->createURL($key, []);
    }

    //endregion
}
