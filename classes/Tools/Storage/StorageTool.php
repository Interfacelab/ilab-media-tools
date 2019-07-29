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

namespace ILAB\MediaCloud\Tools\Storage;

use ILAB\MediaCloud\Storage\FileInfo;
use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Storage\StorageInterface;
use ILAB\MediaCloud\Storage\StorageManager;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Storage\Batch\MigrateToStorageBatchProcess;
use ILAB\MediaCloud\Tools\Storage\Batch\RegenerateThumbnailBatchProcess;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\View;
use Smalot\PdfParser\Parser;
use WP_CLI\Iterators\Exception;
use function ILAB\MediaCloud\Utilities\arrayPath;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

/**
 * Class StorageTool
 *
 * Storage Tool.
 */
class StorageTool extends Tool {
	//region Properties/Class Variables

	/** @var array */
	private $uploadedDocs = [];

	/** @var array */
	private $pdfInfo = [];

	/** @var bool */
	private $skipUpdate = false;

	/** @var bool */
	private $displayBadges = true;

	/** @var bool */
	private $mediaListIntegration = true;

	/** @var StorageInterface|null */
	private $client = null;

	/** @var bool Flag if we are currently processing an optimized image */
	private $processingOptimized = false;

	/** @var bool Determines if the user is using an image optimizer */
	private $usingImageOptimizer = false;

    /** @var string The name of the image optimizer */
    private $imageOptimizer = null;

    /** @var string Controls how file paths should be preserved when updated metadata */
    private $preserveFilePaths = 'replace';

	/** @var string[] */
	private $deleteCache = [];

	/** @var callable */
	private $dieHandler = null;

	/** @var null|array */
	protected $allSizes = null;

	private $disableSrcSet = false;

	//endregion

	//region Constructor
	public function __construct($toolName, $toolInfo, $toolManager) {

	    if (!empty($toolInfo['storageDrivers'])) {
	        foreach($toolInfo['storageDrivers'] as $key => $data) {
	            if (empty($data['name']) || empty($data['class']) || empty($data['config'])) {
	                throw new \Exception("Storage Tool configuration file is malformed.  Storage drivers are missing required information.");
                }

                $configFile = ILAB_CONFIG_DIR . $data['config'];
	            if (!file_exists($configFile)) {
	                throw new \Exception("Missing driver config file '$configFile'. ");
                }

	            $config = include $configFile;
	            StorageManager::registerDriver($key, $data['name'], $data['class'], $config, arrayPath($data, 'help', null));
            }
        }
        do_action('media-cloud/storage/register-drivers');

        $driverConfigs = [];
        foreach(StorageManager::drivers() as $key => $driver) {
            $driverConfigs[$key] = $driver['config'];
        }

        $toolInfo = $this->mergeSettings($toolInfo, $driverConfigs);

		parent::__construct($toolName, $toolInfo, $toolManager);

		new MigrateToStorageBatchProcess();
		new RegenerateThumbnailBatchProcess();

		$this->displayBadges = Environment::Option('mcloud-storage-display-badge', null, true);
		$this->mediaListIntegration = Environment::Option('mcloud-storage-display-media-list', null, true);

		$this->client = StorageManager::storageInstance();

		if($this->haveSettingsChanged()) {
			$this->settingsChanged();
		}

        $this->testForBadPlugins();
        $this->testForUselessPlugins();


        // Hate doing this but some WordPress installs are just f-cked
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH.'wp-admin/includes/image.php');
        }


        if (StorageSettings::deleteOnUpload()) {
	        add_filter('wp_die_ajax_handler', [$this, 'hookDieHandler']);
	        add_filter('wp_die_json_handler', [$this, 'hookDieHandler']);
	        add_filter('wp_die_jsonp_handler', [$this, 'hookDieHandler']);
	        add_filter('wp_die_xmlrpc_handler', [$this, 'hookDieHandler']);
	        add_filter('wp_die_xml_handler', [$this, 'hookDieHandler']);
	        add_filter('wp_die_handler', [$this, 'hookDieHandler']);
        }
    }
	//endregion

	//region Tool Overrides
	public function enabled() {
		$enabled = parent::enabled();

		if($enabled) {
			$enabled = ($this->client && $this->client->enabled());
		}

		return $enabled;
	}


    public function hasSettings() {
        return true;
    }

	public function setup() {
		parent::setup();

		if($this->enabled()) {
            BatchManager::instance()->displayAnyErrors('storage');
            BatchManager::instance()->displayAnyErrors('thumbnails');

		    foreach($this->toolInfo['compatibleImageOptimizers'] as $key => $plugin) {
                if (is_plugin_active($plugin)) {
                    $this->usingImageOptimizer = true;
                    $this->imageOptimizer = $key;

                    if ($key == 'shortpixel') {
                        add_action('shortpixel_image_optimised', [$this, 'handleImageOptimizer']);
                        add_action('shortpixel_after_restore_image', [$this, 'handleImageOptimizer']);
                    } else if ($key == 'smush') {
                        add_action('wp_smush_image_optimised', [$this, 'handleSmushImageOptimizer'], 1000, 2);
                    } else if ($key == 'ewww') {
                        Environment::UpdateOption('ewww_image_optimizer_parallel_optimization', false);
                        add_action('ewww_image_optimizer_post_optimization', function($file, $type, $fullsize) {
                            $this->processingOptimized = true;
                        }, 1000, 3);
                    } else if ($key == 'imagify') {
	                    add_action('imagify_after_reoptimize_media', [$this, 'handleImagifyAfter'], 1000, 2);
	                    add_action('imagify_after_optimize_media', [$this, 'handleImagifyAfter'], 1000, 2);
                    }
                }
            }

            if ($this->usingImageOptimizer) {
		        $this->displayOptimizerAdminNotice();
            }

            $this->disableSrcSet = Environment::Option('mcloud-storage-disable-srcset', null, false);

            add_filter('wp_update_attachment_metadata', [$this, 'handleUpdateAttachmentMetadata'], 1000, 2);

            add_filter('wp_handle_upload_prefilter', function($file){
                $addFilter = true;
                if (isset($_FILES['themezip'])) {
                    $addFilter = ($file['name'] != $_FILES['themezip']['name']);
                } else if (isset($_FILES['pluginzip'])) {
                    $addFilter = ($file['name'] != $_FILES['pluginzip']['name']);
                }

                if ($addFilter) {
                    add_filter('upload_dir', [$this, 'getUploadDir'], 1000);
                }

                return $file;
            }, 1000);

			add_action('delete_attachment', [$this, 'deleteAttachment'], 1000);
			add_filter('wp_handle_upload', function ($upload, $context = 'upload') {
			    $handleUpload = true;

                if (isset($_FILES['themezip'])) {
                    $fileInfo = pathinfo($upload['file']);
                    $handleUpload = ($fileInfo['basename'] != $_FILES['themezip']['name']);
                } else if (isset($_FILES['pluginzip'])) {
                    $fileInfo = pathinfo($upload['file']);
                    $handleUpload = ($fileInfo['basename'] != $_FILES['pluginzip']['name']);
                }

                if ($this->usingImageOptimizer) {
                    if (file_is_displayable_image($upload['file'])) {
                        $handleUpload = false;
                    } else {
                        $this->processingOptimized = true;
                    }
                }

                if (!$handleUpload) {
                    return $upload;
                } else {
                    $result = $this->handleUpload($upload, $context);

                    remove_filter('upload_dir',  [$this, 'getUploadDir']);

                    return $result;
                }
            }, 10000);
			add_filter('get_attached_file', [$this, 'getAttachedFile'], 10000, 2);
			add_filter('image_downsize', [$this, 'imageDownsize'], 999, 3);
			add_action('add_attachment', [$this, 'addAttachment'], 1000);
			add_action('edit_attachment', [$this, 'editAttachment']);

            add_filter('the_content', [$this, 'filterContent'], PHP_INT_MAX, 1);

			add_filter('media-cloud/storage/process-file-name', function($filename) {
				if(!$this->client) {
					return $filename;
				}

				if(strpos($filename, '/'.$this->client->bucket()) === 0) {
					return str_replace('/'.$this->client->bucket(), '', $filename);
				}

				return $filename;
			}, 10000, 1);

            $imgixEnabled = apply_filters('media-cloud/imgix/enabled', false);
            if (!$imgixEnabled) {
                add_filter('wp_image_editors', function($editors) {
                    array_unshift($editors, '\ILAB\MediaCloud\Tools\Storage\StorageImageEditor');

                    return $editors;
                });
            }

			add_filter('wp_calculate_image_srcset', [$this, 'calculateSrcSet'], 10000, 5);
			add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJS'], 999, 3);
			add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 1000, 2);
			add_filter('theme_mod_header_image', [$this, 'getThemeOptionURL'], 1000, 1);

			add_filter('attachment_url_to_postid', [$this, 'attachmentIdFromURL'], 1000, 2);



			add_filter('image_size_names_choose', function($sizes) {
				if ($this->allSizes == null) {
					$this->allSizes = ilab_get_image_sizes();
				}

				foreach($this->allSizes as $sizeKey => $size) {
					if (!isset($sizes[$sizeKey])) {
						$sizes[$sizeKey] = ucwords(preg_replace("/[-_]/", " ", $sizeKey));
					}
				}

				return $sizes;
			});

			add_action('admin_init', function() {
				add_filter('bulk_actions-upload', function($actions) {
					$actions['mcloud_unlink_media'] = 'Unlink from Cloud Storage';
					return $actions;
				});

				add_filter('handle_bulk_actions-upload', function($redirect_to, $action_name, $post_ids) {
				    if ($action_name == 'mcloud_unlink_media') {
				        return $this->handleUnlinkAction($redirect_to, $post_ids);
                    }

				    return $redirect_to;
				}, 1000, 3);
			});

			$this->hookupUI();
		}
	}

	public function settingsChanged() {
	    $error = empty($this->client);

	    if (!$error) {
            try {
                $this->client->validateSettings();
            } catch (StorageException $ex) {
                $error = true;
            }
        }

        if ($error) {
            NoticeManager::instance()->displayAdminNotice('error', 'There is a serious issue with your storage settings.  Please check them and try again.');
        }
	}
	//endregion

    //region Client

    /**
     * The StorageInterface client for this storage tool
     *
     * @return StorageInterface|null
     */
    public function client() {
        return $this->client;
    }

    //endregion

	//region WordPress Upload/Attachment Hooks & Filters

    public function handleUpdateAttachmentMetadata($data, $id) {
	    $ignoreOptimizers = apply_filters('media-cloud/storage/ignore-optimizers', false, $id);

	    if ($this->usingImageOptimizer && !$this->processingOptimized && !$ignoreOptimizers) {
		    return $data;
	    }

	    $shouldSkip = apply_filters('media-cloud/storage/ignore-metadata-update', false, $id);
	    if ($shouldSkip) {
	        return $data;
        }

	    return $this->updateAttachmentMetadata($data, $id, $this->preserveFilePaths);
    }

    /**
     * Filter for when attachments are updated (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5013)
     *
     * @param array $data
     * @param integer $id
	 * @param string $preserveFilePaths
	 * @param bool $skipThumbnails
	 *
	 * @return array
	 */
    public function updateAttachmentMetadata($data, $id, $preserveFilePaths = 'replace', $skipThumbnails = false) {
        if($this->skipUpdate) {
            return $data;
        }

        if(!$data) {
            return $data;
        }

        $imgixEnabled = apply_filters('media-cloud/imgix/enabled', false);

        $mime = (isset($data['ilab-mime'])) ? $data['ilab-mime'] : null;
        if($mime) {
            unset($data['ilab-mime']);
        }

        if(!isset($data['file'])) {
            if(!$mime) {
                $mime = get_post_mime_type($id);
            }

            if($mime == 'application/pdf') {
                $renderPDF = apply_filters('media-cloud/imgix/render-pdf', false);

                if(!$renderPDF) {
                    unset($data['sizes']);
                }

                $s3Info = get_post_meta($id, 'ilab_s3_info', true);
                if($s3Info) {
                    $pdfInfo = $this->pdfInfo[$s3Info['file']];
                    $data['width'] = $pdfInfo['width'];
                    $data['height'] = $pdfInfo['height'];
                    $data['file'] = $s3Info['s3']['key'];
                    $data['s3'] = $s3Info['s3'];
                    if($renderPDF) {
                        $data['sizes']['full']['file'] = $s3Info['s3']['key'];
                        $data['sizes']['full']['width'] = $data['width'];
                        $data['sizes']['full']['height'] = $data['height'];
                    }
                }
            }

            return $data;
        }

        $upload_info = wp_upload_dir();
        $upload_path = $upload_info['basedir'];
        $path_base = pathinfo($data['file'])['dirname'];
        $old_path_base = $path_base;
        $old_file = $data['file'];

        if ($preserveFilePaths) {
            $upload_path .= DIRECTORY_SEPARATOR . $path_base;
            $data['prefix'] = $path_base;
            $data['file'] = trim(str_replace($path_base, '', $data['file']), DIRECTORY_SEPARATOR);

            $upload_info['path'] = str_replace($upload_info['subdir'],DIRECTORY_SEPARATOR.$path_base, $upload_info['path']);
            $upload_info['url'] = str_replace($upload_info['subdir'],DIRECTORY_SEPARATOR.$path_base, $upload_info['url']);
            $upload_info['subdir'] = '';

            $path_base = '';
        }

        if(!file_exists($upload_path.DIRECTORY_SEPARATOR.$data['file'])) {
            return $data;
        }

        if(!$mime) {
            $mime = wp_get_image_mime($upload_path.DIRECTORY_SEPARATOR.$data['file']);
        }

        if($mime && in_array($mime, StorageSettings::ignoredMimeTypes())) {
            return $data;
        }

        if($this->client && $this->client->enabled()) {
            $ignoreExistingS3 = apply_filters('media-cloud/storage/ignore-existing-s3-data', false, $id);

            if($ignoreExistingS3 || !isset($data['s3'])) {
                Logger::info("\tProcessing main file {$data['file']}");

                $doUpload = apply_filters('media-cloud/storage/upload-master', true);
                $data = $this->processFile($upload_path, $data['file'], $data, $id, $preserveFilePaths, $doUpload);

                if ($skipThumbnails && isset($data['sizes'])) {
                    unset($data['sizes']);
                } else if(isset($data['sizes'])) {
                    foreach($data['sizes'] as $key => $size) {
                        if(!is_array($size)) {
                            continue;
                        }

                        $oldSizeFile = $size['file'];

                        if ($preserveFilePaths) {
                            $size['prefix'] = $old_path_base;
                            $size['file'] = str_replace($old_path_base, '', $size['file']);
                        }

                        $file = $path_base.'/'.$size['file'];

                        if($file == $data['file']) {
                            $size['file'] = $oldSizeFile;
                            unset($size['prefix']);
                            $data['sizes'][$key]['s3'] = $data['s3'];
                        } else {

                            Logger::info("\tProcessing thumbnail {$size['file']}");
                            $sizeData = $this->processFile($upload_path, $file, $size, $id, $preserveFilePaths);

                            if ($ignoreExistingS3 || !isset($sizeData['s3'])) {
                                foreach($data['sizes'] as $lookKey => $lookData) {
                                    if (isset($lookData['s3'])) {
                                        if ($lookData['file'] == $sizeData['file']) {
                                            $sizeData['s3'] = $lookData['s3'];
                                            break;
                                        }
                                    }
                                }
                            }

                            unset($sizeData['prefix']);
                            $sizeData['file'] = $oldSizeFile;
                            $data['sizes'][$key] = $sizeData;
                        }

                        if ($imgixEnabled) {
                            if (!ilab_size_is_cropped($key)) {
                                $w = !empty($size['width']) ? $size['width'] : 10000;
                                $h = !empty($size['height']) ? $size['height'] : 10000;
                                $newSize = sizeToFitSize($data['width'], $data['height'], $w, $h);
                                $data['sizes'][$key]['height'] = $newSize[1];
                            }
                        }
                    }
                }

                if(isset($data['s3'])) {
	                $data = apply_filters_deprecated('ilab_s3_after_upload', [$data, $id], '3.0.0', 'media-cloud/storage/after-upload');
	                $data = apply_filters('media-cloud/storage/after-upload', $data, $id);
	                $data = apply_filters('media-cloud/vision/process-meta', $data, $id);
                }
            }
        }

        unset($data['prefix']);
        $data['file'] = $old_file;
        return $data;
    }

	/**
	 * Filters for when attachments are deleted
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function deleteAttachment($id) {
		if(!StorageSettings::deleteFromStorage()) {
			return $id;
		}

		$data = wp_get_attachment_metadata($id);
		if(isset($data['file']) && !isset($data['s3'])) {
			return $id;
		}

		if($this->client && $this->client->enabled()) {
			if(!isset($data['file'])) {
				$file = get_attached_file($id);
				if($file) {
					if(strpos($file, 'http') === 0) {
						$pi = parse_url($file);
						$file = trim($pi['path'], '/');
						if(0 === strpos($file, $this->client->bucket())) {
							$file = substr($file, strlen($this->client->bucket())).'';
							$file = trim($file, '/');
						}
					} else {
						$pi = pathinfo($file);
						$upload_info = wp_upload_dir();
						$upload_path = $upload_info['basedir'];

						$file = trim(str_replace($upload_path, '', $pi['dirname']), '/').'/'.$pi['basename'];
					}

					$this->deleteFile($file);
				}
			} else {
			    $deletedFiles = [];

			    $deletedFiles[] = $data['s3']['key'];
				$this->deleteFile($data['s3']['key']);

				if(isset($data['sizes'])) {
					$pathParts = explode('/', $data['s3']['key']);
					array_pop($pathParts);
					$path_base = implode('/', $pathParts);

					foreach($data['sizes'] as $key => $size) {
						$file = arrayPath($size,'s3/key', false);
						if (!$file) {
						    $file = $path_base.'/'.$size['file'];
                        }

                        if (in_array($file, $deletedFiles)) {
					        Logger::info("File '$file' has already been deleted.");
					        continue;
                        }

                        $this->deleteFile($file);

						$deletedFiles[] = $file;
					}
				}
			}
		}

		return $id;
	}

	/**
     * Filters the uploads directory data.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/functions.php#L1880)
	 *
     * @param array $uploads
	 * @return array
	 */
	public function getUploadDir($uploads) {
		global $job_manager_upload, $job_manager_uploading_file;
		if (!empty($job_manager_upload) && !empty($job_manager_uploading_file)) {
		    return $uploads;
        }

		$prefix = trim(StorageSettings::prefix(null),'/');

		if (is_multisite() && !is_main_site()) {
		    $root = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'uploads';
		    $rootUrl = rtrim(content_url(),'/').'/uploads';

		    $sitePrefix = ltrim(str_replace($root, '', $uploads['basedir']), DIRECTORY_SEPARATOR);
		    $prefix = $sitePrefix.'/'.$prefix;

			$uploads['subdir'] = '/'.$prefix;
			$uploads['path'] = $root.'/'.$prefix;
			$uploads['url'] = $rootUrl.'/'.$prefix;
			$uploads['baseurl'] = $rootUrl;
			$uploads['basedir'] = $root;
        } else {
			$uploads['subdir'] = '/'.$prefix;
			$uploads['path'] = $uploads['basedir'].'/'.$prefix;
			$uploads['url'] = $uploads['baseurl'].'/'.$prefix;
		}

		return $uploads;
    }

    private function fileIsDisplayableImage($file) {
	    if (function_exists('file_is_displayable_image')) {
	        return file_is_displayable_image($file);
        } else {
            $displayable_image_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP];

            $info = @getimagesize($file);
            if (empty($info)) {
                $result = false;
            } else if (!in_array($info[2], $displayable_image_types)) {
                $result = false;
            } else {
                $result = true;
            }

            return apply_filters('file_is_displayable_image', $result, $file);
        }
    }

	/**
	 * Filters the data after a file has been uploaded to WordPress (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/file.php#L416)
	 *
	 * @param array $upload
	 * @param string $context
	 *
	 * @return array
	 */
	public function handleUpload($upload, $context = 'upload') {
    	if(!isset($upload['file'])) {
			return $upload;
		}

		if(isset($upload['type']) && in_array($upload['type'], StorageSettings::ignoredMimeTypes())) {
			return $upload;
		}

		if($this->fileIsDisplayableImage($upload['file'])) {
			return $upload;
		}

		if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "upload-plugin")) {
			return $upload;
		}

		$shouldHandle = apply_filters_deprecated('ilab_s3_should_handle_upload', [false, $upload], '3.0.0', 'media-cloud/storage/should-handle-upload');
		$shouldHandle = apply_filters('media-cloud/storage/should-handle-upload', $shouldHandle, $upload);

		if(!$shouldHandle && !StorageSettings::uploadDocuments()) {
			return $upload;
		}

		if($this->client && $this->client->enabled()) {
			$pi = pathinfo($upload['file']);

			$upload_info = wp_upload_dir();
			$upload_path = $upload_info['basedir'];

			$file = trim(str_replace($upload_path, '', $pi['dirname']), '/').'/'.$pi['basename'];

			if(($upload['type'] == 'application/pdf') && file_exists($upload_path.'/'.$file)) {
				set_error_handler(function($errno, $errstr, $errfile, $errline) {
					throw new \Exception($errstr);
				}, E_RECOVERABLE_ERROR);

				try {
					$parser = new Parser();
					$pdf = $parser->parseFile($upload_path.'/'.$file);
					$pages = $pdf->getPages();
					if(count($pages) > 0) {
						$page = $pages[0];
						$details = $page->getDetails();
						if(isset($details['MediaBox'])) {
							$data = [];
							$data['width'] = $details['MediaBox'][2];
							$data['height'] = $details['MediaBox'][3];
							$this->pdfInfo[$upload_path.'/'.$file] = $data;
						}
					}
				}
				catch(\Exception $ex) {
					Logger::error('PDF Parsing Error', ['exception' => $ex->getMessage()]);
				}

				restore_error_handler();
			}

			$upload = $this->processFile($upload_path, $file, $upload);
			if(isset($upload['s3'])) {
				if(StorageSettings::docCdn()) {
					$upload['url'] = trim(StorageSettings::docCdn(), '/').'/'.$file;
				} else if(isset($upload['s3']['url'])) {
					$upload['url'] = $upload['s3']['url'];
				}
			}

			$this->uploadedDocs[$file] = $upload;
		}

		return $upload;
	}

	/**
	 * Filters the attached file based on the given ID (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L293)
	 *
     * @param $file
     * @param $attachment_id
     * @return null|string
     * @throws StorageException
	 */
	public function getAttachedFile($file, $attachment_id) {
	    $shouldOverride = apply_filters('media-cloud/storage/should-override-attached-file', true, $attachment_id);

		if(!file_exists($file) && $shouldOverride) {
			$meta = wp_get_attachment_metadata($attachment_id);

			$new_url = null;
			if($meta) {
				$new_url = $this->getAttachmentURLFromMeta($meta);
			}

			if(!$new_url) {
				$meta = get_post_meta($attachment_id, 'ilab_s3_info', true);
				if($meta) {
					$new_url = $this->getAttachmentURLFromMeta($meta);
				} else if(!$meta && StorageSettings::docCdn()) {
					$post = \WP_Post::get_instance($attachment_id);
					if($post && (strpos($post->guid, StorageSettings::docCdn()) === 0)) {
						$new_url = $post->guid;
					}
				}
			}

			if($new_url) {
				return $new_url;
			}
		}

		return $file;
	}

    /**
     * Filters whether to preempt the output of image_downsize().  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L201)
     * @param $fail
     * @param $id
     * @param $size
     * @return array
     * @throws StorageException
     */
	public function imageDownsize($fail, $id, $size) {
		if(apply_filters('media-cloud/imgix/enabled', false)) {
			return $fail;
		}

		return $this->forcedImageDownsize($fail, $id, $size);
	}

    /**
     * Performs the image downsize regardless if Imgix is enabled or not.
     * @param $fail
     * @param $id
     * @param $size
     * @return array
     * @throws StorageException
     */
	public function forcedImageDownsize($fail, $id, $size) {
        if(empty($size) || empty($id) || is_array($size)) {
            return $fail;
        }

        $meta = wp_get_attachment_metadata($id);

        if(empty($meta)) {
            return $fail;
        }

        if(!isset($meta['sizes'])) {
            return $fail;
        }

        if(!isset($meta['sizes'][$size])) {
            return $fail;
        }

        $isOffloadS3 = (arrayPath($meta, 's3/provider', null) == 'aws');

        $sizeMeta = $meta['sizes'][$size];
        if(!isset($sizeMeta['s3'])) {
            if ($isOffloadS3) {
                if ($this->fixOffloadS3Meta($id, $meta)) {
                    return $this->forcedImageDownsize($fail, $id, $size);
                } else {
                    return $fail;
                }
            } else {
                return $fail;
            }
        }

        $url = $this->getAttachmentURLFromMeta($sizeMeta);// $sizeMeta['s3']['url'];

        $result = [
            $url,
            $sizeMeta['width'],
            $sizeMeta['height'],
            true
        ];

        return $result;
    }

    private function fixOffloadS3Meta($postId, $meta) {
	    if (empty($meta['s3'])) {
	        return false;
        }

        $meta['s3']['provider'] = 's3';

	    $mimetype = get_post_mime_type($postId);
	    $meta['s3']['mime-type'] = $mimetype;

	    $s3Url = $meta['s3']['url'];
	    if (strpos($s3Url, '//s3-.amazonaws') !== false) {
		    $s3Url = str_replace('//s3-.amazonaws', '//s3.amazonaws', $s3Url);
	    }

	    $url = parse_url($s3Url);
	    $path = pathinfo($url['path']);

	    $baseUrl = "{$url['scheme']}://{$url['host']}{$path['dirname']}/";

	    $path = pathinfo($meta['s3']['key']);
        $baseKey = $path['dirname'].'/';

	    foreach($meta['sizes'] as $size => $sizeData) {
            $sizeS3 = $meta['s3'];
            $sizeS3['url'] = $baseUrl.$sizeData['file'];
            $sizeS3['key'] = $baseKey.$sizeData['file'];
            $sizeS3['options'] = [];
            $sizeS3['mime-type'] = $sizeData['mime-type'];
            $sizeData['s3'] = $sizeS3;

	        $meta['sizes'][$size] = $sizeData;
        }

        $shouldSkip = $this->skipUpdate;
        $this->skipUpdate = true;
	    wp_update_attachment_metadata($postId, $meta);
        $this->skipUpdate = $shouldSkip;

        return true;
    }

	/**
	 * Fires once an attachment has been added. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L3457)
	 *
	 * @param int $post_id
	 */
	public function addAttachment($post_id) {
		$file = get_post_meta($post_id, '_wp_attached_file', true);
		if(isset($this->uploadedDocs[$file])) {
			add_post_meta($post_id, 'ilab_s3_info', $this->uploadedDocs[$file]);
			do_action('media-cloud/storage/uploaded-attachment', $post_id, $file, $this->uploadedDocs[$file]);
		}
	}

	/**
	 * Fires once an existing attachment has been updated.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L3528)
	 *
	 * @param int $post_id
	 */
	public function editAttachment($post_id) {
		$meta = wp_get_attachment_metadata($post_id);
		if(!isset($meta['s3'])) {
			$meta = get_post_meta($post_id, 'ilab_s3_info', true);
			if(empty($meta) || !isset($meta['s3'])) {
				return;
			}

			$meta = $this->updateAttachmentS3Props($post_id, $meta);
			update_post_meta($post_id, 'ilab_s3_info', $meta);

			return;
		}

		$meta = $this->updateAttachmentS3Props($post_id, $meta);
		wp_update_attachment_metadata($post_id, $meta);
	}

	/**
	 * Updates the attachment's properties, as well as updates the metadata on the storage service.
	 *
	 * @param int $id
	 * @param array $meta
	 *
	 * @return mixed
	 */
	private function updateAttachmentS3Props($id, $meta) {
		if(isset($_POST['s3-access-acl']) || isset($_POST['s3-cache-control']) || isset($_POST['s3-expires'])) {
			$mime = get_post_mime_type($id);

			$acl = (isset($meta['s3']['privacy'])) ? $meta['s3']['privacy'] : StorageSettings::privacy();
			$acl = (isset($_POST['s3-access-acl'])) ? $_POST['s3-access-acl'] : $acl;
			$meta['s3']['privacy'] = $acl;

			$cacheControl = false;
			$expires = false;

			if(isset($_POST['s3-cache-control'])) {
				$cacheControl = $_POST['s3-cache-control'];
			}

			if(isset($_POST['s3-expires'])) {
				$expires = $_POST['s3-expires'];
				if(!empty($expires)) {
					if(!is_numeric($expires)) {
						$expires = strtotime($expires) - time();
						if($expires !== false) {
							$expires = round($expires / 60);
						}
					}

					if(($expires !== false) && is_numeric($expires)) {
						$expires = gmdate('D, d M Y H:i:00 \G\M\T', time() + ($expires * 60));
					}
				}
			}

			try {
				$this->client->copy($meta['s3']['key'], $meta['s3']['key'], $acl, $mime, $cacheControl, $expires);

				if(!empty($cacheControl)) {
					if(!isset($meta['s3']['options'])) {
						$meta['s3']['options'] = [];
					}

					if(!isset($meta['s3']['options']['params'])) {
						$meta['s3']['options']['params'] = [];
					}

					$meta['s3']['options']['params']['CacheControl'] = $cacheControl;
				}

				if(!empty($expires)) {
					if(!isset($meta['s3']['options'])) {
						$meta['s3']['options'] = [];
					}

					if(!isset($meta['s3']['options']['params'])) {
						$meta['s3']['options']['params'] = [];
					}

					$meta['s3']['options']['params']['Expires'] = $expires;
				}
			}
			catch(StorageException $ex) {
				Logger::error('Error Copying Object', ['exception' => $ex->getMessage()]);
			}
		}

		return $meta;
	}

	/**
	 * Filters an image’s ‘srcset’ sources.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L1203)
	 *
	 * @param array $sources
	 * @param array $size_array
	 * @param string $image_src
	 * @param array $image_meta
	 * @param int $attachment_id
	 *
	 * @return array
	 */
	public function calculateSrcSet($sources, $size_array, $image_src, $image_meta, $attachment_id) {
	    $canCalculateSrcSet = apply_filters_deprecated('ilab_s3_can_calculate_srcset', [true], '3.0.0', 'media-cloud/storage/can-calculate-srcset');
		if (!apply_filters('media-cloud/storage/can-calculate-srcset', $canCalculateSrcSet)) {
			return $sources;
		}

		if ($this->disableSrcSet) {
		    return [];
        }

		$attachment_id = apply_filters('wpml_object_id', $attachment_id, 'attachment', true);


		if ($this->allSizes == null) {
			$this->allSizes = ilab_get_image_sizes();
		}

		$allSizesNames = array_keys($this->allSizes);

		foreach($image_meta['sizes'] as $sizeName => $sizeData) {
			$width = $sizeData['width'];
			if (isset($sources[$width])) {
			    if (in_array($sizeName, $allSizesNames)) {
                    $src = wp_get_attachment_image_src($attachment_id, $sizeName);

                    if(is_array($src)) {
                        // fix for wpml
	                    $url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $src[0]);
                        $sources[$width]['url'] = $url;
                    } else {
                        unset($sources[$width]);
                    }
                } else {
                    unset($sources[$width]);
                }
			}
		}

		if(isset($image_meta['width'])) {
			$width = $image_meta['width'];
			if(isset($sources[$width])) {
				$src = wp_get_attachment_image_src($attachment_id, 'full');

				if(is_array($src)) {
					// fix for wpml
					$url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $src[0]);
					$sources[$width]['url'] = $url;
				} else {
					unset($sources[$width]);
				}
			}
		}

		return $sources;
	}

	/**
	 * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
	 *
	 * @param array $response
	 * @param int|object $attachment
	 * @param array $meta
	 *
	 * @return array
	 */
	public function prepareAttachmentForJS($response, $attachment, $meta) {
		if(empty($meta) || !isset($meta['s3'])) {
			$meta = get_post_meta($attachment->ID, 'ilab_s3_info', true);
		}

		if(isset($meta['s3'])) {
			$response['s3'] = $meta['s3'];

			if(!isset($response['s3']['privacy'])) {
				$response['s3']['privacy'] = StorageSettings::privacy();
			}
		}

		return $response;
	}


    public function getThemeOptionURL($url) {
	    if (!is_string($url) || empty($url)) {
	        return $url;
        }

	    $uploadDir = wp_get_upload_dir();

	    $escapedBase = str_replace('/', '\/', $uploadDir['baseurl']);
	    $escapedBase = str_replace('.', '\.', $escapedBase);
	    $imageRegex = "#{$escapedBase}(.*(jpg|png))#";
	    if (preg_match($imageRegex, $url, $matches)) {
		    $id = attachment_url_to_postid($matches[0]);

		    if (!empty($id)) {
                $id = apply_filters('wpml_object_id', $id, 'attachment', true);

                $newurl = image_downsize($id, 'full');
                if (is_array($newurl)) {
			        $newurl = $newurl[0];
                }

			    $newurl = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $newurl);
                if (!empty($newurl)) {
                    return $newurl;
                }
		    }
	    }

	    return $url;
    }

	/**
	 * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
	 *
	 * @param string $url
	 * @param int $post_id
	 *
	 * @return string
     * @throws StorageException
	 */
	public function getAttachmentURL($url, $post_id) {
	    if (empty($this->client)) {
	        return $url;
        }

		$meta = wp_get_attachment_metadata($post_id);

		$new_url = null;
		if($meta) {
			$new_url = $this->getAttachmentURLFromMeta($meta);
		}

		if(empty($new_url)) {
			$meta = get_post_meta($post_id, 'ilab_s3_info', true);
			if($meta) {
				$new_url = $this->getAttachmentURLFromMeta($meta);
			}

			if(!$new_url) {
				$meta = get_post_meta($post_id, 'amazonS3_info');

				if($meta) {
					$new_url = $this->getOffloadS3URL($post_id, $meta);

					$s3Data = $meta[0];
					$s3Data['url'] = $new_url;
					$s3Data['privacy'] = 'public-read';

					$this->skipUpdate = true;

					$imageMeta = wp_get_attachment_metadata($post_id);
					if($imageMeta) {
						$imageMeta['s3'] = $s3Data;
						wp_update_attachment_metadata($post_id, $imageMeta);
					} else {
						update_post_meta($post_id, 'ilab_s3_info', ['s3' => $s3Data]);
					}

					$this->skipUpdate = false;
				}
			}

			if(!$meta && StorageSettings::docCdn()) {
				$post = \WP_Post::get_instance($post_id);
				if($post && (strpos($post->guid, StorageSettings::docCdn()) === 0)) {
					$new_url = $post->guid;
				}
			}
		}

		if (!empty($new_url)) {
		    if (strpos($new_url, '//s3-.amazonaws') !== false) {
		        $new_url = str_replace('//s3-.amazonaws', '//s3.amazonaws', $new_url);
            }
        }

		return $new_url ?: $url;
	}

	/**
     * Attempts to get the url based on the S3/Storage metadata
     * @param $meta
     * @return null|string
     * @throws StorageException
     */
	private function getAttachmentURLFromMeta($meta) {
	    if (!isset($meta['s3'])) {
	        return null;
        }

	    if (empty($this->client)) {
	        return null;
        }

	    if ($this->client->usesSignedURLs()) {
            $url = $this->client->url($meta['s3']['key']);
            if (!empty(StorageSettings::cdn())) {
                $cdnScheme = parse_url(StorageSettings::cdn(), PHP_URL_SCHEME);
                $cdnHost = parse_url(StorageSettings::cdn(), PHP_URL_HOST);

                $urlScheme =  parse_url($url, PHP_URL_SCHEME);
                $urlHost = parse_url($url, PHP_URL_HOST);

                return str_replace("{$urlScheme}://{$urlHost}", "{$cdnScheme}://{$cdnHost}", $url);
            } else {
                return $url;
            }
        } else {
            if(StorageSettings::cdn()) {
                return StorageSettings::cdn().'/'.$meta['s3']['key'];
            } else if(isset($meta['s3']['url'])) {
                if(isset($meta['file']) && StorageSettings::docCdn()) {
                    $ext = strtolower(pathinfo($meta['file'], PATHINFO_EXTENSION));
                    $image_exts = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
                    if(!in_array($ext, $image_exts)) {
                        return trim(StorageSettings::docCdn(), '/').'/'.$meta['s3']['key'];
                    }
                }

                $new_url = $meta['s3']['url'];
	            if (!empty($new_url)) {
		            if (strpos($new_url, '//s3-.amazonaws') !== false) {
			            $new_url = str_replace('//s3-.amazonaws', '//s3.amazonaws', $new_url);
		            }
	            }
                return $new_url;
            }

            try {
                return $this->client->url($meta['s3']['key']);
            } catch (\Exception $ex) {
                Logger::error("Error trying to generate url for {$meta['s3']['key']}.  Message:".$ex->getMessage());
                return null;
            }
        }
	}

	/**
	 * For compatibility with Offload S3.
	 *
	 * @param int $post_id
	 * @param array $info
	 *
	 * @return null|string
	 */
	private function getOffloadS3URL($post_id, $info) {
		if(!is_array($info) && (count($info) < 1)) {
			return null;
		}

		$region = (isset($info[0]['region'])) ? $info[0]['region'] : null;
		$bucket = $info[0]['bucket'];
		$file = $info[0]['key'];

		if (empty($region)) {
			return "https://s3.amazonaws.com/$bucket/$file";
		} else {
			return "https://s3-$region.amazonaws.com/$bucket/$file";
		}
	}

    /**
     * Filter the content to replace CDN
     * @param $content
     *
     * @return mixed
     * @throws StorageException
     */
    public function filterContent($content) {
        $canFilterContent = apply_filters_deprecated('ilab_media_cloud_filter_content', [true], '3.0.0', 'media-cloud/storage/can-filter-content');
	    if (!apply_filters('media-cloud/storage/can-filter-content', $canFilterContent)) {
		    return $content;
	    }

	    if (!preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
		    return $content;
	    }

	    $uploadDir = wp_get_upload_dir();

	    $replacements = [];
	    $resizedReplacements = [];

	    foreach($matches[0] as $image) {
	        $imageFound = false;

		    if (!preg_match("#src=['\"]+([^'\"]+)['\"]+#",$image, $srcMatches)) {
		        continue;
            }

		    $src = $srcMatches[1];

	        // parse img tags with classes because these usually indicate the wordpress size
		    if (preg_match('/class\s*=\s*(?:[\"\']{1})([^\"\']+)(?:[\"\']{1})/m', $image, $matches)) {
			    $classes = explode(' ', $matches[1]);

			    $size = null;
			    $id = null;

			    foreach($classes as $class) {
				    if (strpos($class, 'wp-image-') === 0) {
					    $parts = explode('-', $class);
					    $id = array_pop($parts);
				    } else if (strpos($class, 'size-') === 0) {
					    $size = str_replace('size-', '', $class);
				    }
			    }

			    if (!empty($id) && empty($size)) {
				    if (preg_match('/sizes=[\'"]+\(max-(width|height)\:\s*([0-9]+)px/m', $image, $sizeMatches)) {
					    $which = $sizeMatches[1];
					    $px = $sizeMatches[2];

					    $meta = wp_get_attachment_metadata($id);
					    if (!empty($meta['sizes'])) {
						    foreach($meta['sizes'] as $sizeKey => $sizeData) {
							    if ($sizeData[$which] == $px) {
								    $size = $sizeKey;
								    break;
							    }
						    }
					    }
				    }

				    if (empty($size)) {
                        if (preg_match('/wpsize=([aA-zZ0-9-_]*)/m', $src, $wpSizeMatches)) {
                            $size = $wpSizeMatches[1];
                        } else {
                            if (preg_match('/(([0-9]+)x([0-9]+)\.(?:jpg|jpeg|gif|png))/', $src, $dimensionMatches)) {
                                $size = 'full';
                                $width = $dimensionMatches[2];
                                $height = $dimensionMatches[3];
                                $size = ilab_find_nearest_size($id, $width, $height);

                                if (empty($size)) {
                                    $size = 'full';
                                }
                            } else {
	                            $size = 'full';
                            }
                        }
                    }
			    }

			    if (!empty($id) && is_numeric($id)) {
                    $imageFound = true;
                    $replacements[$id] = [
                        'src' => $src,
                        'size' => $size
                    ];
			    }
		    }

		    if (!$imageFound) {
                $escapedBase = str_replace('/', '\/', $uploadDir['baseurl']);
                $escapedBase = str_replace('.', '\.', $escapedBase);
                $imageRegex = "#(data-src|src)\s*=\s*[\'\"]+({$escapedBase}[^\'\"]*(jpg|png|gif))[\'\"]+#";
                if (preg_match($imageRegex, $image, $matches)) {
                    $matchedUrl = $matches[2];

	                $textSize = null;
	                $cleanedUrl = null;
                    $size = 'full';

                    if (preg_match('/(-[0-9x]+)\.(?:jpg|gif|png)/m', $matchedUrl, $sizeMatches)) {
                        $cleanedUrl = str_replace($sizeMatches[1], '', $matchedUrl);
	                    $id = attachment_url_to_postid($cleanedUrl);
                        $textSize = trim($sizeMatches[1], '-');
                        $size = explode('x', $textSize);
                    } else {
                        $id = attachment_url_to_postid($matchedUrl);
                    }


                    if (!empty($id)) {
                        if (!empty($textSize)) {
                            $resizedReplacements[$id.'-'.$textSize] = [
                                'id' => $id,
                                'src' => $matchedUrl,
                                'size' => $size
                            ];
                        } else {
	                        $replacements[$id] = [
		                        'src' => $matchedUrl,
		                        'size' => $size
	                        ];
                        }
                    }
                }
            }
	    } // https://mediacloud.test/app/uploads

        foreach($replacements as $id => $data) {
            $content = $this->replaceImageInContent($id, $data, $content);
	    }

	    foreach($resizedReplacements as $id => $data) {
		    $content = $this->replaceImageInContent($data['id'], $data, $content);
	    }

	    return $content;
    }

    private function replaceImageInContent($id, $data, $content) {
	    $id = apply_filters('wpml_object_id', $id, 'attachment', true);
	    if (empty($data['size'])) {
		    $meta = wp_get_attachment_metadata($id);
		    $url = $this->getAttachmentURLFromMeta($meta);
	    } else {
		    $url = image_downsize($id, $data['size']);
	    }

	    if (is_array($url)) {
		    $url = $url[0];
	    }

	    $url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $url);

	    if (empty($url) || ($url == $data['src'])) {
		    return $content;
	    }

	    return str_replace($data['src'], $url, $content);
    }


	public function attachmentIdFromURL($postId, $url) {
		if (!empty($postId)) {
			return $postId;
		}

		global $wpdb;

		$parsedUrl = parse_url($url);
		$path = ltrim($parsedUrl['path'], '/');

		if (strpos($path, $this->client()->bucket()) === 0) {
			$path = ltrim(str_replace($this->client()->bucket(),'', $path),'/');
		}

		$path = apply_filters('media-cloud/glide/clean-path', $path);

		$query = $wpdb->prepare("select ID from {$wpdb->posts} where post_type='attachment' and guid like %s order by ID desc limit 1", '%'.$path);
		$postId = $wpdb->get_var($query);

		return $postId;
	}

	//endregion

    //region Bulk Actions
    protected function handleUnlinkAction($redirect_to, $post_ids) {
        foreach($post_ids as $post_id) {
	        $meta = wp_get_attachment_metadata($post_id);
	        if (isset($meta['s3'])) {
		        unset($meta['s3']);
		        if(isset($meta['sizes'])) {
			        $sizes = $meta['sizes'];
			        foreach($sizes as $size => $sizeData) {
				        if(isset($sizeData['s3'])) {
					        unset($sizeData['s3']);
				        }

				        $sizes[$size] = $sizeData;
			        }

			        $meta['sizes'] = $sizes;
		        }

		        update_post_meta($post_id, '_wp_attachment_metadata', $meta);
	        }
        }

        return $redirect_to;
    }
    //endregion

	//region Crop Tool Related
	/**
	 * Processes a file after a crop has been performed, uploading it to storage if it exists
	 *
	 * @param string $size
	 * @param string $upload_path
	 * @param string $file
	 * @param array $sizeMeta
	 *
	 * @return array
	 */
	public function processCrop($sizeMeta, $size, $upload_path, $file) {
		$upload_info = wp_upload_dir();
		$subdir = trim(str_replace($upload_info['basedir'], '', $upload_path), '/');
		$upload_path = rtrim(str_replace($subdir, '', $upload_path), '/');

		if($this->client && $this->client->enabled()) {
			$sizeMeta = $this->processFile($upload_path, $subdir.'/'.$file, $sizeMeta);
		}

		return $sizeMeta;
	}
	//endregion

	//region Storage File Processing
	/**
	 * Uploads a file to storage and updates the related metadata.
	 *
	 * @param $upload_path
	 * @param $filename
	 * @param $data
	 * @param null $id
	 * @param string $preserveFilePath
	 * @param bool $uploadFile
	 *
	 * @return mixed
	 */
	public function processFile($upload_path, $filename, $data, $id = null, $preserveFilePath = 'replace', $uploadFile = true) {
		if(!file_exists($upload_path.'/'.$filename)) {
            Logger::error("\tFile $filename is missing.");
			return $data;
		}

		if(isset($data['s3'])) {
			$key = $data['s3']['key'];

			if($key == $filename) {
				return $data;
			}

			$this->deleteFile($key);
		}

		$shouldUseCustomPrefix = apply_filters_deprecated('ilab_storage_should_use_custom_prefix', [true], '3.0.0', 'media-cloud/storage/should-use-custom-prefix');
		$shouldUseCustomPrefix = apply_filters('media-cloud/storage/should-use-custom-prefix', $shouldUseCustomPrefix);
        $shouldUseCustomPrefix = (!empty(StorageSettings::prefixFormat()) && $shouldUseCustomPrefix);

        if (($preserveFilePath == 'replace') && !isset($data['prefix']) && !$shouldUseCustomPrefix) {
            $fpath = pathinfo($data['file'],PATHINFO_DIRNAME);
            $fpath = str_replace($upload_path, '', $fpath);
            $prefix = trailingslashit(ltrim($fpath, DIRECTORY_SEPARATOR));
            if ($prefix == './') {
                $prefix = trailingslashit(pathinfo($filename, PATHINFO_DIRNAME));
            }
        } else {
            if ($preserveFilePath == 'preserve') {
	            $prefix = (isset($data['prefix'])) ? trailingslashit($data['prefix']) : StorageSettings::prefix($id);
            } else if ($preserveFilePath == 'prepend') {
	            $prefix = (isset($data['prefix'])) ? trailingslashit(StorageSettings::prefix($id)).trailingslashit($data['prefix']) : StorageSettings::prefix($id);
            } else {
	            $prefix = StorageSettings::prefix($id);
            }
        }

        $parts = explode('/', $filename);
        $bucketFilename = array_pop($parts);

		try {
		    $url = null;

		    // File may already exist on cloud storage, but we'll check that it does first
		    if (!$uploadFile) {
		        $fileExists = $this->client->exists($prefix.$bucketFilename);
		        if (!$fileExists) {
		            $uploadFile = true;
                } else {
		            $url = $this->client->url($prefix.$bucketFilename);
                }
            }

		    if ($uploadFile || empty($url)) {
			    Logger::info("\tUploading $filename to S3.");
			    $url = $this->client->upload($prefix.$bucketFilename, $upload_path.'/'.$filename, StorageSettings::privacy(), StorageSettings::cacheControl(), StorageSettings::expires());
			    Logger::info("\tFinished uploading $filename to S3.");
            } else {
			    Logger::info("\tSkipping upload of $filename to S3.  Already exists.");
		    }

			$options = [];
			$params = [];
			if(!empty(StorageSettings::cacheControl())) {
				$params['CacheControl'] = StorageSettings::cacheControl();
			}

			if(!empty(StorageSettings::expires())) {
				$params['Expires'] = StorageSettings::expires();
			}

			if(!empty($params)) {
				$options['params'] = $params;
			}

			$providerClass = get_class($this->client);
			$providerId = $providerClass::identifier();

			$data['s3'] = [
				'url' => $url,
				'bucket' => $this->client->bucket(),
				'privacy' => StorageSettings::privacy(),
				'key' => $prefix.$bucketFilename,
                'provider' =>  $providerId,
				'options' => $options
			];

			if(file_exists($upload_path.'/'.$filename)) {
				$ftype = wp_check_filetype($upload_path.'/'.$filename);
				if(!empty($ftype) && isset($ftype['type'])) {
					$data['s3']['mime-type'] = $ftype['type'];
				}
			}
		}
		catch(StorageException $ex) {
			Logger::error('Upload Error', [
				'exception' => $ex->getMessage(),
				'prefix' => $prefix,
				'bucketFilename' => $bucketFilename,
				'privacy' => StorageSettings::privacy()
			]);
		}

		if(isset($data['type']) && ($data['type'] == 'application/pdf')) {
			$renderPDF = apply_filters('media-cloud/imgix/render-pdf', false);

			if(!$renderPDF) {
				unset($data['sizes']);
			}

			if (isset($this->pdfInfo[$data['file']]) && isset($data['s3'])) {
				$pdfInfo = $this->pdfInfo[$data['file']];
				$data['width'] = $pdfInfo['width'];
				$data['height'] = $pdfInfo['height'];
				$data['file'] = $data['s3']['key'];
				if($renderPDF) {
					$data['sizes']['full']['file'] = $data['s3']['key'];
					$data['sizes']['full']['width'] = $data['width'];
					$data['sizes']['full']['height'] = $data['height'];
				}
			}
		}

		if(StorageSettings::deleteOnUpload()) {
			if(file_exists($upload_path.'/'.$filename)) {
			    $fileToDelete = $upload_path.'/'.$filename;
			    Logger::info("Deleting $fileToDelete");
			    //$this->deleteCache[] = $fileToDelete;
				unlink($fileToDelete);
			}
		}

		return $data;
	}

	public function hookDieHandler($handler) {
	    $this->dieHandler = $handler;
	    return [$this, 'cleanUploads'];
    }

	public function cleanUploads($message, $title = '', $args = array()) {
		foreach($this->deleteCache as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}

		$this->deleteCache = [];

		call_user_func( $this->dieHandler, $message, $title, $args );
    }

	/**
	 * Deletes a file from storage
	 *
	 * @param $file
	 */
	private function deleteFile($file) {
		try {
			if($this->client && $this->client->enabled()) {
				$this->client->delete($file);
			}
		}
		catch(StorageException $ex) {
			Logger::error("Error deleting file '$file'.", ['exception' => $ex->getMessage(), 'Key' => $file]);
		}
	}
	//endregion

	//region WordPress UI Hooks
	/**
	 * Hooks into the WordPress UI in various ways.
	 */
	private function hookupUI() {
		$this->hookAttachmentDetails();
		$this->hookMediaList();
		$this->hookStorageInfoMetabox();
		$this->hookMediaGrid();
	}

	/**
	 * Displays storage info in the attachment details pop up.
	 */
	private function hookAttachmentDetails() {
		add_action('wp_enqueue_media', function() {
			add_action('admin_footer', function() {
				?>
                <script>
                    jQuery(document).ready(function () {
                        var attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            var txt = attachTemplate.text();
                            var idx = txt.indexOf('<div class="compat-meta">');
                            txt = txt.slice(0, idx) + '<# if ( data.s3 ) { #><div><strong>Bucket:</strong> {{data.s3.bucket}}</div><div><strong>Path:</strong> {{data.s3.key}}</div><div><strong>Access:</strong> {{data.s3.privacy}}</div><# if ( data.s3.options && data.s3.options.params ) { #><# if (data.s3.options.params.CacheControl) { #><div><strong>S3 Cache-Control:</strong> {{data.s3.options.params.CacheControl}}</div><# } #><# if (data.s3.options.params.Expires) { #><div><strong>S3 Expires:</strong> {{data.s3.options.params.Expires}}</div><# } #><# } #><div><a href="{{data.s3.url}}" target="_blank">Original Storage URL</a></div><# } #>' + txt.slice(idx);
                            attachTemplate.text(txt);
                        }
                    });
                </script>
				<?php

			});

			wp_enqueue_script('ilab-media-grid-js', ILAB_PUB_JS_URL.'/ilab-media-grid.js', ['jquery'], false, true);
		});
	}

	/**
	 * Adds a custom column to the media list.
	 */
	private function hookMediaList() {
		if(!$this->mediaListIntegration) {
			return;
		}

		add_action('admin_init', function() {
			add_filter('manage_media_columns', function($cols) {
				$cols["cloud"] = 'Cloud';

				return $cols;
			});

			add_action('manage_media_custom_column', function($column_name, $id) {
				if($column_name == "cloud") {
					$meta = wp_get_attachment_metadata($id);
					if(!empty($meta) && isset($meta['s3'])) {
					    $mimeType = (isset($meta['s3']['mime-type'])) ? $meta['s3']['mime-type'] : '';
						$cloudIcon = ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg';
						echo "<a class='media-cloud-info-link' data-post-id='$id' data-container='list' data-mime-type='{$mimeType}' href='".$meta['s3']['url']."' target=_blank><img src='{$cloudIcon}' width='24'></a>";
					}
				}
			}, 10, 2);
		});

		add_action('wp_enqueue_media', function() {
			add_action('admin_head', function() {
				if(get_current_screen()->base == 'upload') {
					?>
                    <style>
                        th.column-cloud, td.column-cloud {
                            width: 60px !important;
                            max-width: 60px !important;
                            text-align: center;
                        }
                    </style>
					<?php
				}
			});
		});

		add_action('restrict_manage_posts', function() {
			$scr = get_current_screen();
			if ( $scr->base !== 'upload' ) {
			    return;
			}

			$selected = (isset($_REQUEST['cloud_status'])) ? $_REQUEST['cloud_status'] : '';

			?>
            <select id="cloud-status" name="cloud_status">
                <option>Any Cloud Status</option>
                <option value="uploaded" <?php echo ($selected == 'uploaded') ? 'selected' : '' ?>>Uploaded</option>
                <option value="not-uploaded" <?php echo ($selected == 'not-uploaded') ? 'selected' : '' ?>>Not Uploaded</option>
            </select>
            <?php

			$selected = (isset($_REQUEST['detected_faces'])) ? $_REQUEST['detected_faces'] : '';
			?>
            <select id="detected-faces" name="detected_faces">
                <option>Any Faces</option>
                <option value="has-faces" <?php echo ($selected == 'has-faces') ? 'selected' : '' ?>>Faces Detected</option>
                <option value="no-faces" <?php echo ($selected == 'no-faces') ? 'selected' : '' ?>>No Faces Detected</option>
            </select>
			<?php

			$selected = (isset($_REQUEST['cloud_privacy'])) ? $_REQUEST['cloud_privacy'] : '';
			?>
            <select id="cloud-privacy" name="cloud_privacy">
                <option>Any Privacy</option>
                <option value="public-read" <?php echo ($selected == 'public-read') ? 'selected' : '' ?>>Public</option>
                <option value="authenticated-read" <?php echo ($selected == 'authenticated-read') ? 'selected' : '' ?>>Private</option>
            </select>
			<?php
		});

		add_action('pre_get_posts', function($query) {
			/** @var \WP_Query $query */
		    if (!is_admin() || !isset($_REQUEST['cloud_status']) || !isset($_REQUEST['detected_faces']) || !isset($_REQUEST['cloud_privacy']) || !$query->is_main_query()) {
		        return;
		    }

		    $meta_query = [
			    'relation' => 'and',
            ];

		    if ($_REQUEST['cloud_status'] == 'uploaded') {
			    $meta_query[] = [
				    'relation' => 'OR',
				    [
					    'key'     => '_wp_attachment_metadata',
					    'value'   => '"s3"',
					    'compare' => 'LIKE',
					    'type'    => 'CHAR',
				    ],
				    [
					    'key'     => 'ilab_s3_info',
					    'compare' => 'EXISTS',
				    ],
			    ];
            } else if ($_REQUEST['cloud_status'] == 'not-uploaded') {
			    $meta_query[] = [
				    'relation' => 'AND',
				    [
					    'key'     => '_wp_attachment_metadata',
					    'value'   => '"s3"',
					    'compare' => 'NOT LIKE',
					    'type'    => 'CHAR',
				    ],
				    [
					    'key'     => 'ilab_s3_info',
					    'compare' => 'NOT EXISTS',
				    ],
			    ];
            }

			if ($_REQUEST['detected_faces'] == 'has-faces') {
				$meta_query[] = [
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"faces"',
						'compare' => 'LIKE',
						'type'    => 'CHAR',
					],
				];
			} else if ($_REQUEST['detected_faces'] == 'no-faces') {
				$meta_query[] = [
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"faces"',
						'compare' => 'NOT LIKE',
						'type'    => 'CHAR',
					],
				];
			}

			if ($_REQUEST['cloud_privacy'] == 'public-read') {
				$meta_query[] = [
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"public-read"',
						'compare' => 'LIKE',
						'type'    => 'CHAR',
					],
				];
			} else if ($_REQUEST['cloud_privacy'] == 'authenticated-read') {
				$meta_query[] = [
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"authenticated-read"',
						'compare' => 'LIKE',
						'type'    => 'CHAR',
					],
				];
			}

		    if (count($meta_query) > 1) {
			    $query->set('meta_query', $meta_query);
            }
        });
	}

	/**
	 * Displays a cloud icon on items in the media grid.
	 */
	private function hookMediaGrid() {
		if(!$this->displayBadges) {
			return;
		}

		add_action('wp_ajax_ilab_s3_get_media_info', [$this, 'getMediaInfo']);

		add_action('admin_head', function() {
			?>
            <style>
                .ilab-s3-logo {
                    display: none;
                    position: absolute;
                    right: 5px;
                    bottom: 4px;
                    z-index: 5;
                }

                .has-s3 > .ilab-s3-logo {
                    display: block;
                }
            </style>
			<?php
		});
		add_action('admin_footer', function() {
			?>
            <script>
                jQuery(document).ready(function () {
                    var attachTemplate = jQuery('#tmpl-attachment');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#>"><img data-post-id="{{data.id}}" data-container="grid" data-mime-type="{{data.type}}" src="<?php echo ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg'?>" width="29" height="18" class="ilab-s3-logo">\n';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }

                    var attachTemplate = jQuery('#tmpl-attachment-grid-view');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#>"><img data-post-id="{{data.id}}" data-container="grid" data-mime-type="{{data.type}}" src="<?php echo ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg'?>" width="29" height="18" class="ilab-s3-logo">\n';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }
                });
            </script>
			<?php

		});
	}

	/**
	 * Adds the Cloud Storage metabox on attachment edit pages.
	 */
	private function hookStorageInfoMetabox() {
		add_action('admin_init', function() {
			add_meta_box('ilab-s3-info-meta', 'Cloud Storage Info', [
				$this,
				'renderStorageInfoMeta'
			], 'attachment', 'side', 'low');
		});
	}



    public function getMediaInfo() {
	    if (!is_admin()) {
	        die;
        }

        if (!isset($_POST['id'])) {
	        die;
        }

        $this->doRenderStorageInfoMeta($_POST['id'], true);
	    die;
    }

	/**
	 * @param \WP_Post $post
	 */
	public function renderStorageInfoMeta($post) {
	    $this->doRenderStorageInfoMeta($post->ID);
	}


	/**
	 * Renders the Cloud Storage metabox
     * @param $postId
     * @param $readOnly
	 */
	private function doRenderStorageInfoMeta($postId = null, $readOnly = false) {
		global $post;

		if(empty($postId)) {
			$postId = $post->ID;
		}

		$meta = wp_get_attachment_metadata($postId);
		if(empty($meta['s3'])) {
			$meta = get_post_meta($postId, 'ilab_s3_info', true);
		}

		if(empty($meta) || empty($meta['s3'])) {
			?>
            Not uploaded.
			<?php
			die;
		}

		$type = arrayPath($meta, 'type', false);
		if (empty($type)) {
			$type = get_post_mime_type($postId);
        }

		if(strpos($type, 'image') === 0) {
			$this->doRenderStoreageInfoMetaImage($postId, $meta, $readOnly);
		} else {
			$this->doRenderStorageinfoMetaDocument($postId, $meta, $readOnly);
		}
	}

	private function doRenderStorageinfoMetaDocument($postId, $meta, $readOnly) {
		$type = arrayPath($meta, 'type', false);
		if (empty($type)) {
			$type = get_post_mime_type($postId);
		}

		$providerClass = get_class($this->client);
		$providerId = $providerClass::identifier();

		$enabled = $this->enabled() && (arrayPath($meta, 's3/provider', false)  == $providerId);

		$clientClass = get_class($this->client);
		$uploadDriverId = arrayPath($meta,'s3/provider',$clientClass::identifier());
		$uploadDriver = StorageManager::driverClass($uploadDriverId);

		$bucket = arrayPath($meta,'s3/bucket',null);
		$key = arrayPath($meta,'s3/key',null);
		$privacy = arrayPath($meta,'s3/privacy', 'public-read');
		$cacheControl = arrayPath($meta, 's3/options/params/CacheControl', null);
		$expires = arrayPath($meta, 's3/options/params/Expires', null);
		$url = arrayPath($meta,'s3/url',null);
		$publicUrl = wp_get_attachment_url($postId); //$this->getAttachmentURLFromMeta($meta);

		$data = [
			'uploaded' => 1,
            'type' => $type,
			'enabled' => $enabled,
			'postId' => $postId,
			'bucket' => $bucket,
			'key' => $key,
			'readOnly' => $readOnly,
			'privacy' => $privacy,
			'cacheControl' => $cacheControl,
			'expires' => $expires,
			'url' => $url,
			'publicUrl' => $publicUrl,
			'driverName' => $uploadDriver::name(),
			'bucketLink' => $uploadDriver::bucketLink($bucket),
			'pathLink' => $this->client->pathLink($bucket, $key)
		];

		echo View::render_view('storage/document-info-panel.php', $data);
	}

	private function doRenderStoreageInfoMetaImage($postId, $meta, $readOnly) {
        $imgixEnabled = apply_filters('media-cloud/imgix/enabled', false);

		$providerClass = get_class($this->client);
		$providerId = $providerClass::identifier();

        $enabled = $this->enabled() && (arrayPath($meta, 's3/provider', false)  == $providerId);

        $clientClass = get_class($this->client);
        $uploadDriverId = arrayPath($meta,'s3/provider',$clientClass::identifier());
        if ($uploadDriverId == 'aws') {
            $uploadDriverId = 's3';
        }

        $uploadDriver = StorageManager::driverClass($uploadDriverId);

        $bucket = arrayPath($meta,'s3/bucket',null);
        $key = arrayPath($meta,'s3/key',null);
        $privacy = arrayPath($meta,'s3/privacy', 'public-read');
        $cacheControl = arrayPath($meta, 's3/options/params/CacheControl', null);
        $expires = arrayPath($meta, 's3/options/params/Expires', null);
        $url = arrayPath($meta,'s3/url',null);
        $publicUrl = wp_get_attachment_url($postId); //$this->getAttachmentURLFromMeta($meta);

        $sizes = [];
        if($meta['sizes']) {
            foreach($meta['sizes'] as $sizeKey => $size) {
                $sizeData = [];

                $sizeData['uploaded'] = isset($size['s3']);
                $sizeData['enabled'] = $enabled;
                $sizeData['postId'] = $postId;
                $sizeData['readOnly'] = $readOnly;
                $sizeData['name'] = ucwords(str_replace('_', ' ', str_replace('-', ' ', $sizeKey)));
                $sizeData['bucket'] = arrayPath($size,'s3/bucket',null);
                $sizeData['key'] = arrayPath($size,'s3/key',null);
                $sizeData['privacy'] = arrayPath($size,'s3/privacy', 'public-read');
                $sizeData['cacheControl'] = arrayPath($size, 's3/options/params/CacheControl', null);
                $sizeData['expires'] = arrayPath($size, 's3/options/params/Expires', null);
                $sizeData['url'] = arrayPath($size, 's3/url', null);
                $sizeData['width'] = arrayPath($size,'width', 0);
                $sizeData['height'] = arrayPath($size,'height', 0);
                $sizeData['driverName'] = $uploadDriver::name();
                $sizeData['bucketLink'] = $uploadDriver::bucketLink($sizeData['bucket']);
                $sizeData['isSize'] = 1;
                $sizeData['pathLink'] = $this->client->pathLink($sizeData['bucket'], $sizeData['key']);
                $sizeData['imgixEnabled'] = $imgixEnabled;

                $result = wp_get_attachment_image_src($postId, $sizeKey);
                if ($result && is_array($result) && (count($result) > 0)) {
                    $sizeData['publicUrl'] = $result[0];
                } else {
                    $sizeData['publicUrl'] = $this->getAttachmentURLFromMeta($size);
                }

                $sizes[$sizeKey] = $sizeData;
            }
        }

        $missingSizes = [];


		if ($this->allSizes == null) {
			$this->allSizes = ilab_get_image_sizes();
		}

        foreach($this->allSizes as $wpSizeKey => $wpSize) {
            if (!isset($sizes[$wpSizeKey])) {
                $missingSizes[$wpSizeKey] =  ucwords(str_replace('_', ' ', str_replace('-', ' ', $wpSizeKey)));
            }
        }

        $data = [
            'uploaded' => 1,
            'enabled' => $enabled,
            'postId' => $postId,
            'bucket' => $bucket,
            'key' => $key,
            'readOnly' => $readOnly,
            'privacy' => $privacy,
            'cacheControl' => $cacheControl,
            'expires' => $expires,
            'url' => $url,
            'publicUrl' => $publicUrl,
            'width' => $meta['width'],
            'height' => $meta['height'],
            'driverName' => $uploadDriver::name(),
            'bucketLink' => $uploadDriver::bucketLink($bucket),
            'pathLink' => $this->client->pathLink($bucket, $key),
            'imgixEnabled' => $imgixEnabled,
            'sizes' => $sizes,
            'missingSizes' => $missingSizes
        ];

        echo View::render_view('storage/info-panel.php', $data);
	}
	//endregion

    //region Regeneration
    private function loadImageToEditPath( $attachment_id, $size = 'full' ) {
        $filepath = get_attached_file( $attachment_id );

        if ( $filepath && file_exists( $filepath ) ) {
            if ( 'full' != $size && ( $data = image_get_intermediate_size( $attachment_id, $size ) ) ) {
                $filepath = apply_filters( 'load_image_to_edit_filesystempath', path_join( dirname( $filepath ), $data['file'] ), $attachment_id, $size );
            }
        } elseif ( function_exists( 'fopen' ) && true == ini_get( 'allow_url_fopen' ) ) {
            $filepath = apply_filters( 'load_image_to_edit_attachmenturl', wp_get_attachment_url( $attachment_id ), $attachment_id, $size );
        }
        return apply_filters( 'load_image_to_edit_path', $filepath, $attachment_id, $size );
    }

	/**
     * Regenerates an image's thumbnails and re-uploads them to the storage service.
     *
	 * @param $postId
	 * @return bool|string
	 */
    public function regenerateFile($postId) {
	    @set_time_limit(120);

	    $fullsizepath = get_attached_file( $postId );
	    if (!file_exists($fullsizepath)) {
	        if (function_exists('_load_image_to_edit_path')) {
                $fullsizepath = _load_image_to_edit_path($postId);
            } else {
	            Logger::warning("The function '_load_image_to_edit_path' does not exist, using internal implementation.");
	            $fullsizepath = $this->loadImageToEditPath($postId);
                Logger::warning("$postId => $fullsizepath");
            }
	    }

	    if (!file_exists($fullsizepath)) {
		    if (strpos($fullsizepath, 'http') === 0) {
			    $path = parse_url($fullsizepath, PHP_URL_PATH);
			    $pathParts = explode('/', $path);
			    $file = array_pop($pathParts);

			    $uploadDirInfo = wp_upload_dir();

			    $filepath = $uploadDirInfo['path'].'/'.$file;
			    Logger::startTiming("Downloading fullsize '$fullsizepath' to '$filepath'");
			    file_put_contents($filepath, ilab_file_get_contents($fullsizepath));
			    Logger::endTiming("Finished downloading fullsize '$fullsizepath' to '$filepath'");

			    if (!file_exists($filepath)) {
			        return "File '$fullsizepath' could not be downloaded.";
                }

			    $fullsizepath = $filepath;
		    } else {
		        return "Local file '$fullsizepath' does not exist and is not a URL.";
		    }
	    }

	    $shouldPreserve = $this->preserveFilePaths;

	    $this->preserveFilePaths = 'preserve';
	    Logger::startTiming('Regenerating metadata ...', ['id' => $postId]);
	    $metadata = wp_generate_attachment_metadata( $postId, $fullsizepath );
	    Logger::endTiming('Regenerating metadata ...', ['id' => $postId]);

	    wp_update_attachment_metadata($postId, $metadata);

	    $this->preserveFilePaths = $shouldPreserve;

	    return true;
    }
    //endregion

	//region Importer
	/**
     * @param int $index
	 * @param int $postId
	 * @param ImportProgressDelegate|null $progressDelegate
     * @param array $options
	 */
	public function processImport($index, $postId, $progressDelegate, $options = []) {
//		if ($progressDelegate) {
//		    $progressDelegate->updateCurrentIndex($index + 1);
//        }
//
		$isDocument = false;

		$skipThumbnails = (empty($options['skip-thumbnails'])) ? false : true;
		$pathmode = (empty($options['path-handling'])) ? 'replace' : $options['path-handling'];


		$data = wp_get_attachment_metadata($postId);

		if (empty($data)) {
			$isDocument = true;
			$post_mime = get_post_mime_type($postId);
			$upload_file = get_attached_file($postId);
			$file = _wp_relative_upload_path($upload_file);

			$fileName = basename($upload_file);
			if ($progressDelegate) {
				$progressDelegate->updateCurrentFileName($fileName);
			}

			$data = [ 'file' => $file ];

			if (is_file($upload_file)) {
				$mime = null;

				$ftype = wp_check_filetype($upload_file);
				if (!empty($ftype) && isset($ftype['type'])) {
					$mime  = $ftype['type'];
				}

				if ($mime == 'image/vnd.adobe.photoshop') {
					$mime = 'application/vnd.adobe.photoshop';
				}

				$data['ilab-mime'] = $mime;
				if ($mime != $post_mime) {
					wp_update_post(['ID'=>$postId, 'post_mime_type' => $mime]);
				}

				$imagesize = getimagesize( $upload_file );
				if ($imagesize) {
					if (file_is_displayable_image($upload_file)) {
						$data['width'] = $imagesize[0];
						$data['height'] = $imagesize[1];
						$data['sizes']=[
							'full' => [
								'file' => $data['file'],
								'width' => $data['width'],
								'height' => $data['height']
							]
						];

						$isDocument = false;
					}
				}

				if ($mime == 'application/pdf') {
					$renderPDF = apply_filters('media-cloud/imgix/render-pdf', false);

					set_error_handler(function($errno, $errstr, $errfile, $errline){
						throw new \Exception($errstr);
					}, E_RECOVERABLE_ERROR);

					try {
						$parser = new Parser();
						$pdf = $parser->parseFile($upload_file);
						$pages = $pdf->getPages();
						if (count($pages)>0) {
							$page = $pages[0];
							$details = $page->getDetails();
							if (isset($details['MediaBox'])) {
								$data['width'] = $details['MediaBox'][2];
								$data['height'] = $details['MediaBox'][3];

								if ($renderPDF) {
									$data['sizes']=[
										'full' => [
											'file' => $data['file'],
											'width' => $data['width'],
											'height' => $data['height']
										]
									];

									$isDocument = false;
								}
							}
						}
					} catch (\Exception $ex) {
						Logger::error( 'PDF Exception.',  [ 'postId' => $postId, 'exception' =>$ex->getMessage()]);
					}

					restore_error_handler();
				}
			}
		} else {
            if (empty($data['file'])) {
                $attachedFile = get_attached_file($postId);
                $data['file'] = _wp_relative_upload_path($attachedFile);
            }
			$fileName = basename($data['file']);

			if ($progressDelegate) {
				$progressDelegate->updateCurrentFileName($fileName);
			}
		}


		$data = $this->updateAttachmentMetadata($data, $postId, $pathmode, $skipThumbnails);

		if ($isDocument) {
			update_post_meta($postId, 'ilab_s3_info', $data);
		} else {
			update_post_meta( $postId, '_wp_attachment_metadata', $data);
		}
	}

	//endregion

	//region Direct Upload Support
	/**
	 * Gets a pre-signed URL for uploading directly to the storage backend
	 *
	 * @param string $filename
	 *
	 * @return array|null
	 */
	public function uploadUrlForFile($filename) {
	    $prefix = StorageSettings::prefix(null);
        $parts = explode('/', $filename);
        $bucketFilename = array_pop($parts);

		if($this->client && $this->client->enabled()) {
			try {
				return $this->client->uploadUrl($prefix.$bucketFilename, StorageSettings::privacy(), StorageSettings::cacheControl(), StorageSettings::expires());
			}
			catch(StorageException $ex) {
				Logger::error('Generate File Upload URL Error', ['exception' => $ex->getMessage()]);
			}
		}

		return null;
	}

	/**
	 * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
	 *
	 * @param FileInfo $fileInfo
	 *
	 * @return array|bool
     * @throws StorageException
	 */
	public function importImageAttachmentFromStorage($fileInfo) {
		if(!$this->client || !$this->client->enabled()) {
			return null;
		}

		if(!is_array($fileInfo->size())) {
			return null;
		}

		$this->client->insureACL($fileInfo->key(), StorageSettings::privacy());

		$fileParts = explode('/', $fileInfo->key());
		$filename = array_pop($fileParts);
		$url = $this->client->url($fileInfo->key());

		$s3Info = [
			'url' => $url,
			'mime-type' => $fileInfo->mimeType(),
			'bucket' => $this->client->bucket(),
			'privacy' => StorageSettings::privacy(),
			'key' => $fileInfo->key(),
			'options' => [
				'params' => []
			]
		];

		if(!empty(StorageSettings::cacheControl())) {
			$s3Info['options']['params']['CacheControl'] = StorageSettings::cacheControl();
		}

		if(!empty(StorageSettings::expires())) {
			$s3Info['options']['params']['Expires'] = StorageSettings::expires();
		}


		$meta = [
			'width' => $fileInfo->size()[0],
			'height' =>$fileInfo->size()[1],
			'file' => $fileInfo->key(),
			'image_meta' => [],
			's3' => $s3Info,
			'sizes' => []
		];

		$builtInSizes = [];
		foreach(['thumbnail', 'medium', 'medium_large', 'large'] as $size) {
			$builtInSizes[$size] = [
				'width' => get_option("{$size}_size_w"),
				'height' => get_option("{$size}_size_h"),
				'crop' => get_option("{$size}_crop", 0),
			];
		}

		$additional_sizes = wp_get_additional_image_sizes();
		$sizes = array_merge($builtInSizes, $additional_sizes);

		foreach($sizes as $sizeKey => $size) {
			$resized = image_resize_dimensions($fileInfo->size()[0], $fileInfo->size()[1], $size['width'], $size['height'], $size['crop']);
			if($resized) {
				$meta['sizes'][$sizeKey] = [
					'file' => $filename,
					'width' => $resized[4],
					'height' => $resized[5],
					'mime-type' => 'image/jpeg',
					's3' => $s3Info
				];
			}
		}

		$post = wp_insert_post([
			                       'post_author' => get_current_user_id(),
			                       'post_title' => $filename,
			                       'post_status' => 'inherit',
			                       'post_type' => 'attachment',
			                       'guid' => $url,
			                       'post_mime_type' => $fileInfo->mimeType()
		                       ]);

		if(is_wp_error($post)) {
			return false;
		}

		$meta = apply_filters_deprecated('ilab_s3_after_upload', [$meta, $post], '3.0.0', 'media-cloud/storage/after-upload');
		$meta = apply_filters('media-cloud/storage/after-upload', $meta, $post);
		$meta = apply_filters('media-cloud/vision/process-meta', $meta, $post);

		add_post_meta($post, '_wp_attached_file', $fileInfo->key());
		add_post_meta($post, '_wp_attachment_metadata', $meta);

		$thumbUrl = image_downsize($post, ['width' => 128, 'height' => 128]);

		if(is_array($thumbUrl)) {
			$thumbUrl = $thumbUrl[0];
		}


		return [
			'id' => $post,
			'url' => $url,
			'thumb' => $thumbUrl
		];

	}

	/**
	 * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
	 *
	 * @param FileInfo $fileInfo
	 *
	 * @return array|bool
	 * @throws StorageException
	 */
	public function importAttachmentFromStorage($fileInfo) {
		if(!$this->client || !$this->client->enabled()) {
			return null;
		}

		if ($fileInfo->mimeType() && (strpos($fileInfo->mimeType(), 'image/') === 0)) {
		    return $this->importImageAttachmentFromStorage($fileInfo);
        }


		$this->client->insureACL($fileInfo->key(), StorageSettings::privacy());

		$fileParts = explode('/', $fileInfo->key());
		$filename = array_pop($fileParts);
		$url = $this->client->url($fileInfo->key());

		$s3Info = [
			'url' => $url,
			'mime-type' => $fileInfo->mimeType(),
			'bucket' => $this->client->bucket(),
			'privacy' => StorageSettings::privacy(),
			'key' => $fileInfo->key(),
			'options' => [
				'params' => []
			]
		];

		if(!empty(StorageSettings::cacheControl())) {
			$s3Info['options']['params']['CacheControl'] = StorageSettings::cacheControl();
		}

		if(!empty(StorageSettings::expires())) {
			$s3Info['options']['params']['Expires'] = StorageSettings::expires();
		}


		$meta = [
			'file' => $fileInfo->key(),
			's3' => $s3Info
		];


		$post = wp_insert_post([
			'post_author' => get_current_user_id(),
			'post_title' => $filename,
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'guid' => $url,
			'post_mime_type' => $fileInfo->mimeType()
		]);

		if(is_wp_error($post)) {
			return false;
		}

		$meta = apply_filters_deprecated('ilab_s3_after_upload', [$meta, $post], '3.0.0', 'media-cloud/storage/after-upload');
		$meta = apply_filters('media-cloud/storage/after-upload', $meta, $post);

		add_post_meta($post, '_wp_attached_file', $fileInfo->key());
		add_post_meta($post, '_wp_attachment_metadata', $meta);


		return [
			'id' => $post,
			'url' => $url,
			'thumb' => wp_mime_type_icon($post)
		];

	}
	//endregion

    //region Image Optimizer

    public function handleImageOptimizer($postId) {
	    $this->processingOptimized = true;

	    Logger::info('Handle Image Optimizer: '.$postId);


        add_filter('media-cloud/storage/ignore-existing-s3-data', function($shouldIgnore, $attachmentId) use ($postId) {
            if ($postId == $attachmentId) {
                return true;
            }

            return $shouldIgnore;
        }, 10000, 2);

        $this->processImport(1,  $postId, null);
    }

    public function handleSmushImageOptimizer($postId, $stats) {
        $this->handleImageOptimizer($postId);
    }

    public function handleImagifyImageOptimizer($postId, $data) {
        $this->handleImageOptimizer($postId);
    }

    public function handleImagifyAfter($process, $task) {
	    $attachmentId = null;

	    $data = $process->get_data();
	    if (!empty($data)) {
	        $media = $process->get_media();
	        if (!empty($media)) {
	            $attachmentId = $media->get_id();
		        if (!empty($attachmentId)) {
			        $this->handleImageOptimizer($attachmentId);
		        }
            }
        }
    }

    private function displayOptimizerAdminNotice() {
	    $message = <<<Optimizer
<p style='text-transform:uppercase; font-weight:bold; opacity: 0.8; margin-bottom:0; padding-bottom:0px;'>Image Optimizer Warning</p>
<p>Image optimizer plugins often do the optimization step in the background, not actually during the upload process.</p>
<p>Because of this, Media Cloud will not upload your images to your cloud storage provider <strong>until after the image is optimized</strong>.  This means 
your uploaded images will appear as a local images until after the optimization process happens.  This can take several minutes.</p>
Optimizer;

        NoticeManager::instance()->displayAdminNotice('warning', $message, true, 'ilab-optimizer-'.$this->imageOptimizer.'-warning-forever');
    }

    //endregion

    //region Settings
	public function providerOptions() {
		$providers = [];
		foreach(StorageManager::drivers() as $id => $driver) {
			$providers[$id] = $driver['name'];
		}

		return $providers;
	}

	public function providerHelp() {
		$help = [];
		foreach(StorageManager::drivers() as $id => $driver) {
		    $helpData = arrayPath($driver, 'help', null);
		    if (!empty($helpData)) {
			    $help[$id] = $helpData;
            }
		}

		return $help;
	}
    //endregion
}
