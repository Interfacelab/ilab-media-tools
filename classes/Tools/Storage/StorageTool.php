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

use ILAB\MediaCloud\Cloud\Storage\FileInfo;
use ILAB\MediaCloud\Cloud\Storage\StorageException;
use ILAB\MediaCloud\Cloud\Storage\StorageInterface;
use ILAB\MediaCloud\Cloud\Storage\StorageManager;
use ILAB\MediaCloud\Cloud\Storage\StorageSettings;
use ILAB\MediaCloud\Cloud\Storage\UploadInfo;
use ILAB\MediaCloud\Tasks\RegenerateThumbnailsProcess;
use ILAB\MediaCloud\Tools\ToolBase;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\Prefixer;
use ILAB\MediaCloud\Utilities\View;
use ILAB\MediaCloud\Tasks\StorageImportProcess;
use ILAB\MediaCloud\Utilities\Logger;
use Smalot\PdfParser\Parser;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

/**
 * Class StorageTool
 *
 * Storage Tool.
 */
class StorageTool extends ToolBase {
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

	//endregion

	//region Constructor
	public function __construct($toolName, $toolInfo, $toolManager) {
		parent::__construct($toolName, $toolInfo, $toolManager);

		new StorageImportProcess();
		new RegenerateThumbnailsProcess();

		$this->displayBadges = EnvironmentOptions::Option('ilab-media-s3-display-s3-badge', null, true);
		$this->mediaListIntegration = EnvironmentOptions::Option('ilab-cloud-storage-display-media-list', null, true);

		$this->client = StorageManager::storageInstance();

		if($this->haveSettingsChanged()) {
			$this->settingsChanged();
		}

		add_filter('ilab_cloud_import_from_storage', [$this, 'importImageAttachmentFromStorage'], 10, 1);

		if(is_admin()) {
			add_action('wp_ajax_ilab_s3_import_media', [$this, 'importMedia']);
			add_action('wp_ajax_ilab_s3_import_progress', [$this, 'importProgress']);
			add_action('wp_ajax_ilab_s3_cancel_import', [$this, 'cancelImportMedia']);

			add_action('wp_ajax_ilab_media_cloud_regenerate_file', [$this, 'handleRegenerateFile']);
			add_action('wp_ajax_ilab_media_cloud_regenerate_files', [$this, 'handleRegenerateFiles']);
			add_action('wp_ajax_ilab_media_cloud_regenerate_progress', [$this, 'regenerateProgress']);
			add_action('wp_ajax_ilab_media_cloud_cancel_regenerate', [$this, 'cancelRegenerateFiles']);
		}
	}
	//endregion

	//region ToolBase Overrides
	public function enabled() {
		$enabled = parent::enabled();

		if($enabled) {
			$enabled = ($this->client && $this->client->enabled());
		}

		return $enabled;
	}

	public function setup() {
		parent::setup();

		if($this->enabled()) {
			add_filter('wp_update_attachment_metadata', [$this, 'updateAttachmentMetadata'], 1000, 2);
			add_action('delete_attachment', [$this, 'deleteAttachment'], 1000);
			add_filter('wp_handle_upload', [$this, 'handleUpload'], 10000);
			add_filter('get_attached_file', [$this, 'getAttachedFile'], 10000, 2);
			add_filter('image_downsize', [$this, 'imageDownsize'], 999, 3);
			add_action('add_attachment', [$this, 'addAttachment'], 1000);
			add_action('edit_attachment', [$this, 'editAttachment']);
			add_filter('upload_dir', [$this, 'getUploadDir']);

            add_filter('the_content', [$this, 'filterContent'], 10000, 1);

			add_filter('ilab_s3_process_crop', [$this, 'processCrop'], 10000, 4);

			add_filter('ilab_s3_process_file_name', function($filename) {
				if(!$this->client) {
					return $filename;
				}

				if(strpos($filename, '/'.$this->client->bucket()) === 0) {
					return str_replace('/'.$this->client->bucket(), '', $filename);
				}

				return $filename;
			}, 10000, 1);
		}

		add_filter('wp_calculate_image_srcset', [$this, 'calculateSrcSet'], 10000, 5);
		add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJS'], 999, 3);
		add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 1000, 2);

		$this->hookupUI();
	}

	public function settingsChanged() {
	    try {
		    $this->client->validateSettings();
        } catch (StorageException $ex) {
            NoticeManager::instance()->displayAdminNotice('error', 'There is a serious issue with your storage settings.  Please check them and try again.');
        }
	}

	public function registerMenu($top_menu_slug) {
		parent::registerMenu($top_menu_slug);

		if($this->enabled()) {
			add_submenu_page($top_menu_slug, 'Storage Importer', 'Storage Importer', 'manage_options', 'media-tools-s3-importer', [
				$this,
				'renderImporter'
			]);

			$imgixEnabled = apply_filters('ilab_imgix_enabled', false);
			if (!$imgixEnabled) {
				add_submenu_page($top_menu_slug, 'Rebuild Thumbnails', 'Rebuild Thumbnails', 'manage_options', 'media-tools-cloud-regeneration', [
					$this,
					'renderRegenerator'
				]);
            }
		}
	}
	//endregion

	//region WordPress Upload/Attachment Hooks & Filters
	/**
	 * Filter for when attachments are updated (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5013)
	 *
	 * @param array $data
	 * @param integer $id
	 *
	 * @return array
	 */
	public function updateAttachmentMetadata($data, $id) {
		if($this->skipUpdate) {
			return $data;
		}

		if(!$data) {
			return $data;
		}

		$imgixEnabled = apply_filters('ilab_imgix_enabled', false);

		$mime = (isset($data['ilab-mime'])) ? $data['ilab-mime'] : null;
		if($mime) {
			unset($data['ilab-mime']);
		}

		if(!isset($data['file'])) {
			if(!$mime) {
				$mime = get_post_mime_type($id);
			}

			if($mime == 'application/pdf') {
				$renderPDF = apply_filters('ilab_imgix_render_pdf', false);

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

		if(!file_exists($upload_path.'/'.$data['file'])) {
			return $data;
		}

		if(!$mime) {
			$mime = wp_get_image_mime($upload_path.'/'.$data['file']);
		}

		if($mime && in_array($mime, StorageSettings::ignoredMimeTypes())) {
			return $data;
		}

		if($this->client && $this->client->enabled()) {
			if(!isset($data['s3'])) {
				$data = $this->processFile($upload_path, $data['file'], $data, $id);

				if(isset($data['sizes'])) {
					foreach($data['sizes'] as $key => $size) {
						if(!is_array($size)) {
							continue;
						}

						$file = $path_base.'/'.$size['file'];
						if($file == $data['file']) {
							$data['sizes'][$key]['s3'] = $data['s3'];
						} else {
						    $sizeData = $this->processFile($upload_path, $file, $size, $id);

						    if (!isset($sizeData['s3'])) {
						        foreach($data['sizes'] as $lookKey => $lookData) {
						            if (isset($lookData['s3'])) {
							            if ($lookData['file'] == $sizeData['file']) {
								            $sizeData['s3'] = $lookData['s3'];
								            break;
							            }
                                    }
                                }
                            }

							$data['sizes'][$key] = $sizeData;
						}

						if ($imgixEnabled) {
							if (!ilab_size_is_cropped($key)) {
								$newSize = sizeToFitSize($data['width'], $data['height'], $size['width'] ?: 10000, $size['height'] ?: 10000);
								$data['sizes'][$key]['height'] = $newSize[1];
							}
                        }
					}
				}

				if(isset($data['s3'])) {
					$data = apply_filters('ilab_s3_after_upload', $data, $id);
				}
			}
		}

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
		$prefix = trim(StorageSettings::prefix(null),'/');

		$uploads['subdir'] = '/'.$prefix;
		$uploads['path'] = $uploads['basedir'].'/'.$prefix;
		$uploads['url'] = $uploads['baseurl'].'/'.$prefix;

		return $uploads;
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

		if(file_is_displayable_image($upload['file'])) {
			return $upload;
		}

		if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "upload-plugin")) {
			return $upload;
		}

		$shouldHandle = apply_filters('ilab_s3_should_handle_upload', false, $upload);

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
	 * @param string $file
	 * @param int $attachment_id
	 *
	 * @return null|string
	 */
	public function getAttachedFile($file, $attachment_id) {
		if(!file_exists($file)) {
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
	 *
	 * @param bool $fail
	 * @param int $id
	 * @param array|string $size
	 *
	 * @return bool|array
	 */
	public function imageDownsize($fail, $id, $size) {
		if(apply_filters('ilab_imgix_enabled', false)) {
			return $fail;
		}

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

		$sizeMeta = $meta['sizes'][$size];
		if(!isset($sizeMeta['s3'])) {
			return $fail;
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

	/**
	 * Fires once an attachment has been added. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L3457)
	 *
	 * @param int $post_id
	 */
	public function addAttachment($post_id) {
		$file = get_post_meta($post_id, '_wp_attached_file', true);
		if(isset($this->uploadedDocs[$file])) {
			add_post_meta($post_id, 'ilab_s3_info', $this->uploadedDocs[$file]);
			do_action('ilab_s3_uploaded_attachment', $post_id, $file, $this->uploadedDocs[$file]);
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
		if(!apply_filters('ilab_s3_can_calculate_srcset', true)) {
			return $sources;
		}

		foreach($image_meta['sizes'] as $sizeName => $sizeData) {
			$width = $sizeData['width'];
			if(isset($sources[$width])) {
				$src = wp_get_attachment_image_src($attachment_id, $sizeName);

				if(is_array($src)) {
					$sources[$width]['url'] = $src[0];
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
					$sources[$width]['url'] = $src[0];
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

	/**
	 * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
	 *
	 * @param string $url
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function getAttachmentURL($url, $post_id) {
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

		return $new_url ?: $url;
	}

	/**
	 * Attempts to get the url based on the S3/Storage metadata
	 *
	 * @param array $meta
	 *
	 * @return null|string
	 */
	private function getAttachmentURLFromMeta($meta) {
		if(isset($meta['s3']) && StorageSettings::cdn()) {
			return StorageSettings::cdn().'/'.$meta['s3']['key'];
		} else if(isset($meta['s3']) && isset($meta['s3']['url'])) {
			if(isset($meta['file']) && StorageSettings::docCdn()) {
				$ext = strtolower(pathinfo($meta['file'], PATHINFO_EXTENSION));
				$image_exts = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
				if(!in_array($ext, $image_exts)) {
					return trim(StorageSettings::docCdn(), '/').'/'.$meta['s3']['key'];
				}
			}

			return $meta['s3']['url'];
		}

		return null;
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

		$region = $info[0]['region'];
		$bucket = $info[0]['bucket'];
		$file = $info[0]['key'];

		return "http://s3-$region.amazonaws.com/$bucket/$file";
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
	public function processCrop($size, $upload_path, $file, $sizeMeta) {
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
	 * @param string $upload_path
	 * @param string $filename
	 * @param array $data
	 * @param null|int $id
	 *
	 * @return array
	 */
	private function processFile($upload_path, $filename, $data, $id = null) {
		if(!file_exists($upload_path.'/'.$filename)) {
			return $data;
		}

		if(isset($data['s3'])) {
			$key = $data['s3']['key'];

			if($key == $filename) {
				return $data;
			}

			$this->deleteFile($key);
		}

        $prefix = StorageSettings::prefix($id);
        $parts = explode('/', $filename);
        $bucketFilename = array_pop($parts);

		try {
			$url = $this->client->upload($prefix.$bucketFilename, $upload_path.'/'.$filename, StorageSettings::privacy(), StorageSettings::cacheControl(), StorageSettings::expires());

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

		if(StorageSettings::deleteOnUpload()) {
			if(file_exists($upload_path.'/'.$filename)) {
				unlink($upload_path.'/'.$filename);
			}
		}

		return $data;
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
		$this->hookBulkActions();
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

			wp_enqueue_script('ilab-media-storage-js', ILAB_PUB_JS_URL.'/ilab-media-storage.js', ['jquery'], false, true);
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
						echo "<a href='".$meta['s3']['url']."' target=_blank>View</a>";
					}
				}
			}, 10, 2);
		});

		add_action('wp_enqueue_media', function() {
			add_action('admin_head', function() {
				if(get_current_screen()->base == 'upload') {
					?>
                    <style>
                        th.column-s3, td.column-s3 {
                            width: 60px !important;
                            max-width: 60px !important;
                        }
                    </style>
					<?php
				}
			});
		});
	}

	/**
	 * Adds bulk actions to the media list view.
	 */
	private function hookBulkActions() {
		if(!$this->enabled()) {
			return;
		}

		if(!$this->mediaListIntegration) {
			return;
		}


		add_action('admin_init', function() {
			add_filter('bulk_actions-upload', function($actions) {
				$imgixEnabled = apply_filters('ilab_imgix_enabled', false);

				$actions['ilab_s3_import'] = 'Import to Cloud Storage';

				if (!$imgixEnabled) {
					$actions['ilab_regenerate_thumbnails'] = 'Regenerate Thumbnails';
                }

				return $actions;
			});

			add_filter('handle_bulk_actions-upload', function($redirect_to, $action_name, $post_ids) {
				if('ilab_s3_import' === $action_name) {
					$posts_to_import = [];
					if(count($post_ids) > 0) {
						foreach($post_ids as $post_id) {
							$meta = wp_get_attachment_metadata($post_id);
							if(!empty($meta) && isset($meta['s3'])) {
								continue;
							}

							$posts_to_import[] = $post_id;
						}
					}

					if(count($posts_to_import) > 0) {
						update_option('ilab_s3_import_status', true);
						update_option('ilab_s3_import_total_count', count($posts_to_import));
						update_option('ilab_s3_import_current', 1);
						update_option('ilab_s3_import_should_cancel', false);

						$process = new StorageImportProcess();

						for($i = 0; $i < count($posts_to_import); ++ $i) {
							$process->push_to_queue(['index' => $i, 'post' => $posts_to_import[$i]]);
						}

						$process->save();
						$process->dispatch();

						return 'admin.php?page=media-tools-s3-importer';
					}
				} else if ('ilab_regenerate_thumbnails' === $action_name) {
					if(count($post_ids) > 0) {
						update_option('ilab_cloud_regenerate_status', true);
						update_option('ilab_cloud_regenerate_total_count', count($post_ids));
						update_option('ilab_cloud_regenerate_current', 1);
						update_option('ilab_cloud_regenerate_should_cancel', false);

						$process = new RegenerateThumbnailsProcess();

						for($i = 0; $i < count($post_ids); ++ $i) {
							$process->push_to_queue(['index' => $i, 'post' => $post_ids[$i]]);
						}

						$process->save();
						$process->dispatch();

						return 'admin.php?page=media-tools-cloud-regeneration';
					}
                }

				return $redirect_to;
			}, 1000, 3);
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
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#>"><img data-post-id="{{data.id}}" data-mime-type="{{data.type}}" src="<?php echo ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg'?>" width="29" height="18" class="ilab-s3-logo">\n';
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

	/**
     * Filter the content to replace CDN
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filterContent($content) {
	    if (apply_filters('ilab_imgix_enabled', false)) {
	        return $content;
        }

		if (!preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
			return $content;
		}

		$replacements = [];

		foreach($matches[0] as $image) {
		    if (preg_match("#wp-image-([0-9]+)#",$image, $idMatches)) {
			    $id = $idMatches[1];

			    if (!empty($id) && is_numeric($id)) {
				    if (preg_match("#src=['\"]+([^'\"]+)['\"]+#",$image, $srcMatches)) {
					    $replacements[$id] = $srcMatches[1];
    			    }
                }
            }
        }
        
        foreach($replacements as $id => $src) {
		    $meta = wp_get_attachment_metadata($id);
		    $url = $this->getAttachmentURLFromMeta($meta);
		    if (!empty($url) && ($url != $src)) {
		        $content = str_replace($src, $url, $content);
            }
        }

		return $content;
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
		if(empty($meta)) {
			return;
		}

		if(!isset($meta['s3'])) {
			$meta = get_post_meta($postId, 'ilab_s3_info', true);
		}

		if(empty($meta) || !isset($meta['s3'])) {
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
			'pathLink' => $uploadDriver::pathLink($bucket, $key)
		];

		echo View::render_view('storage/document-info-panel.php', $data);
	}

	private function doRenderStoreageInfoMetaImage($postId, $meta, $readOnly) {
        $imgixEnabled = apply_filters('ilab_imgix_enabled', false);

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
                $sizeData['pathLink'] = $uploadDriver::pathLink($sizeData['bucket'], $sizeData['key']);
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

        $wpSizes = ilab_get_image_sizes();
        foreach($wpSizes as $wpSizeKey => $wpSize) {
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
            'pathLink' => $uploadDriver::pathLink($bucket, $key),
            'imgixEnabled' => $imgixEnabled,
            'sizes' => $sizes,
            'missingSizes' => $missingSizes
        ];

        echo View::render_view('storage/info-panel.php', $data);
	}
	//endregion

    //region Regeneration
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
		    $fullsizepath = _load_image_to_edit_path($postId);
	    }

	    if (!file_exists($fullsizepath)) {
		    if (strpos($fullsizepath, 'http') === 0) {
			    $path = parse_url($fullsizepath, PHP_URL_PATH);
			    $pathParts = explode('/', $path);
			    $file = array_pop($pathParts);

			    $uploadDirInfo = wp_upload_dir();

			    $filepath = $uploadDirInfo['path'].'/'.$file;
			    Logger::startTiming("Downloading fullsize '$fullsizepath' to '$filepath'");
			    file_put_contents($filepath, file_get_contents($fullsizepath));
			    Logger::endTiming("Finished downloading fullsize '$fullsizepath' to '$filepath'");

			    if (!file_exists($filepath)) {
			        return "File '$fullsizepath' could not be downloaded.";
                }

			    $fullsizepath = $filepath;
		    } else {
		        return "Local file '$fullsizepath' does not exist and is not a URL.";
		    }
	    }

	    Logger::startTiming('Regenerating metadata ...', ['id' => $postId]);
	    $metadata = wp_generate_attachment_metadata( $postId, $fullsizepath );
	    Logger::endTiming('Regenerating metadata ...', ['id' => $postId]);

	    wp_update_attachment_metadata($postId, $metadata);

	    return true;
    }

	/**
	 * Ajax endpoint for regenerating a single file
	 */
    public function handleRegenerateFile() {
	    if (!is_admin()) {
	        json_response(['status' => 'error', 'message' => 'Invalid security credentials.']);
        }

	    if (!isset($_POST['post_id'])) {
		    json_response(['status' => 'error', 'message' => 'Missing post ID.']);
	    }

        $postId = intval($_POST['post_id']);
	    if (!current_user_can('edit_post',$postId)) {
		    Logger::info('User is attempting to edit a post that they do not have access to.', ['id' => $postId]);
		    json_response(['status' => 'error', 'message' => 'User is attempting to edit a post that they do not have access to.']);
        }

        $result = $this->regenerateFile($postId);
	    if ($result === true) {
	        json_response(['status'=>'success']);
        } else {
	        json_response(['status' => 'error', 'message' => $result]);
        }
	}

	/**
	 * Renders the storage importer view
	 */
	public function renderRegenerator() {
		$shouldCancel = get_option('ilab_cloud_regenerate_should_cancel', false);
		$status = get_option('ilab_cloud_regenerate_status', false);
		$total = get_option('ilab_cloud_regenerate_total_count', 0);
		$current = get_option('ilab_cloud_regenerate_current', 1);
		$currentFile = get_option('ilab_cloud_regenerate_current_file', '');

		if($total == 0) {
			$attachments = get_posts([
				                         'post_type' => 'attachment',
				                         'posts_per_page' => - 1
			                         ]);

			$total = count($attachments);
		}

		$progress = 0;

		if($total > 0) {
			$progress = ($current / $total) * 100;
		}

		echo View::render_view('storage/regenerator.php', [
			'status' => ($status) ? 'running' : 'idle',
			'total' => $total,
			'progress' => $progress,
			'current' => $current,
			'currentFile' => $currentFile,
			'enabled' => $this->enabled(),
			'shouldCancel' => $shouldCancel
		]);
	}

	/**
	 * Ajax callback for import progress.
	 */
	public function regenerateProgress() {
		$shouldCancel = get_option('ilab_cloud_regenerate_should_cancel', false);
		$status = get_option('ilab_cloud_regenerate_status', false);
		$total = get_option('ilab_cloud_regenerate_total_count', 0);
		$current = get_option('ilab_cloud_regenerate_current', 0);
		$currentFile = get_option('ilab_cloud_regenerate_current_file', '');

		header('Content-type: application/json');
		echo json_encode([
			                 'status' => ($status) ? 'running' : 'idle',
			                 'total' => (int) $total,
			                 'current' => (int) $current,
			                 'currentFile' => $currentFile,
			                 'shouldCancel' => $shouldCancel
		                 ]);
		die;
	}

	/**
	 * Ajax method to cancel the import
	 */
	public function cancelRegenerateFiles() {
		update_option('ilab_cloud_regenerate_should_cancel', 1);
		RegenerateThumbnailsProcess::cancelAll();

		json_response(['status' => 'ok']);
	}

	/**
	 * Ajax method to start the import.
	 */
	public function handleRegenerateFiles() {
		$args = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'nopaging' => true,
			'post_mime_type' => 'image',
			'fields' => 'ids',
		];

		$query = new \WP_Query($args);

		if($query->post_count > 0) {
			update_option('ilab_cloud_regenerate_status', true);
			update_option('ilab_cloud_regenerate_total_count', $query->post_count);
			update_option('ilab_cloud_regenerate_current', 1);
			update_option('ilab_cloud_regenerate_should_cancel', false);

			$process = new RegenerateThumbnailsProcess();

			for($i = 0; $i < $query->post_count; ++ $i) {
				$process->push_to_queue(['index' => $i, 'post' => $query->posts[$i]]);
			}

			$process->save();
			$process->dispatch();
		} else {
			delete_option('ilab_cloud_regenerate_status');
		}

		header('Content-type: application/json');
		echo '{"status":"running"}';
		die;
	}

    //endregion


	//region Importer
	/**
	 * Renders the storage importer view
	 */
	public function renderImporter() {
		$shouldCancel = get_option('ilab_s3_import_should_cancel', false);
		$status = get_option('ilab_s3_import_status', false);
		$total = get_option('ilab_s3_import_total_count', 0);
		$current = get_option('ilab_s3_import_current', 1);
		$currentFile = get_option('ilab_s3_import_current_file', '');

		if($total == 0) {
			$attachments = get_posts([
				                         'post_type' => 'attachment',
				                         'posts_per_page' => - 1
			                         ]);

			$total = count($attachments);
		}

		$progress = 0;

		if($total > 0) {
			$progress = ($current / $total) * 100;
		}

		echo View::render_view('storage/ilab-storage-importer.php', [
			'status' => ($status) ? 'running' : 'idle',
			'total' => $total,
			'progress' => $progress,
			'current' => $current,
			'currentFile' => $currentFile,
			'enabled' => $this->enabled(),
			'shouldCancel' => $shouldCancel
		]);
	}

	/**
	 * Ajax callback for import progress.
	 */
	public function importProgress() {
		$shouldCancel = get_option('ilab_s3_import_should_cancel', false);
		$status = get_option('ilab_s3_import_status', false);
		$total = get_option('ilab_s3_import_total_count', 0);
		$current = get_option('ilab_s3_import_current', 0);
		$currentFile = get_option('ilab_s3_import_current_file', '');

		header('Content-type: application/json');
		echo json_encode([
			                 'status' => ($status) ? 'running' : 'idle',
			                 'total' => (int) $total,
			                 'current' => (int) $current,
			                 'currentFile' => $currentFile,
			                 'shouldCancel' => $shouldCancel
		                 ]);
		die;
	}

	/**
	 * Ajax method to cancel the import
	 */
	public function cancelImportMedia() {
		update_option('ilab_s3_import_should_cancel', 1);
		StorageImportProcess::cancelAll();

		json_response(['status' => 'ok']);
	}

	/**
	 * Ajax method to start the import.
	 */
	public function importMedia() {
		$args = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'nopaging' => true,
			'fields' => 'ids',
		];

		if(!StorageSettings::uploadDocuments()) {
			$args['post_mime_type'] = 'image';
		}

		$query = new \WP_Query($args);

		if($query->post_count > 0) {
			update_option('ilab_s3_import_status', true);
			update_option('ilab_s3_import_total_count', $query->post_count);
			update_option('ilab_s3_import_current', 1);
			update_option('ilab_s3_import_should_cancel', false);

			$process = new StorageImportProcess();

			for($i = 0; $i < $query->post_count; ++ $i) {
				$process->push_to_queue(['index' => $i, 'post' => $query->posts[$i]]);
			}

			$process->save();
			$process->dispatch();
		} else {
			delete_option('ilab_s3_import_status');
		}

		header('Content-type: application/json');
		echo '{"status":"running"}';
		die;
	}

	/**
     * @param int $index
	 * @param int $postId
	 * @param ImportProgressDelegate|null $progressDelegate
	 */
	public function processImport($index, $postId, $progressDelegate) {
		if ($progressDelegate) {
		    $progressDelegate->updateCurrentIndex($index + 1);
        }

		$isDocument = false;

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

			if (file_exists($upload_file)) {
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
					$renderPDF = apply_filters('ilab_imgix_render_pdf', false);

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
			$fileName = basename($data['file']);

			if ($progressDelegate) {
				$progressDelegate->updateCurrentFileName($fileName);
			}
		}


		$data = $this->updateAttachmentMetadata($data, $postId);

		if ($isDocument) {
			update_post_meta($postId, 'ilab_s3_info', $data);
		} else {
			wp_update_attachment_metadata($postId, $data);
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
	 */
	public function importImageAttachmentFromStorage($fileInfo) {
		if(!$this->client || !$this->client->enabled()) {
			return null;
		}

		if(!is_array($fileInfo->size())) {
			return false;
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

		$meta = apply_filters('ilab_s3_after_upload', $meta, $post);

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
	//endregion

}
