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

namespace ILAB\MediaCloud\Tools\MediaUpload;

use ILAB\MediaCloud\Cloud\Storage\StorageInterface;
use ILAB\MediaCloud\Cloud\Storage\StorageManager;
use ILAB\MediaCloud\Cloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\ToolBase;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\View;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

/**
 * Class ILabMediaUploadTool
 *
 * Video Tool.
 */
class UploadTool extends ToolBase {
	//region Class Variables
	/** @var StorageInterface */
	private $client;
	//endregion

    public function __construct($toolName, $toolInfo, $toolManager) {
        parent::__construct($toolName, $toolInfo, $toolManager);

        $this->testForBadPlugins();
        $this->testForUselessPlugins();
    }

    //region ToolBase Overrides
	public function setup() {
		parent::setup();

		if(!is_admin()) {
			return;
		}

		$this->client = StorageManager::storageInstance();
		if ($this->client && $this->client->enabled() && $this->client->supportsDirectUploads()) {
			$this->setupAdmin();
			$this->setupAdminAjax();
		}
	}

	public function enabled() {
		if(!parent::enabled()) {
			return false;
		}

		if (!$this->client || !$this->client->enabled()) {
			return false;
		}

		return $this->client->supportsDirectUploads();
	}
	//endregion

	//region Admin Setup
	/**
	 * Setup upload UI
	 */
	public function setupAdmin() {

		add_action('admin_enqueue_scripts', function() {
			wp_enqueue_script('wp-util');
			wp_enqueue_style('ilab-media-upload-css', ILAB_PUB_CSS_URL.'/ilab-media-upload.min.css');
			wp_enqueue_script('ilab-media-upload-js', ILAB_PUB_JS_URL.'/ilab-media-upload.js', [
				'jquery',
				'wp-util'
			], false, true);
		});

		$this->client->enqueueUploaderScripts();

		add_action('admin_menu', function() {
			if(current_user_can('upload_files')) {
				if($this->enabled()) {
					add_media_page('Cloud Upload', 'Cloud Upload', 'upload_files', 'media-cloud-upload', [
						$this,
						'renderSettings'
					]);
				}
			}
		});

		add_filter('media_upload_tabs', function($tabs) {
			if(current_user_can('upload_files')) {
				$tabs = array_merge(['ilab_cloud_upload' => 'Cloud Upload'], $tabs);
			}

			return $tabs;
		});

		add_action('media_upload_ilab_cloud_upload', function() {
			wp_iframe([$this, 'renderInsertSettings']);
		});
	}

	/**
	 * Install Ajax Endpoints
	 */
	public function setupAdminAjax() {
		add_action('wp_ajax_ilab_upload_prepare', function() {
			$this->prepareUpload();
		});

		add_action('wp_ajax_ilab_upload_import_cloud_file', function() {
			$this->importUploadedFile();
		});

		add_action('wp_ajax_ilab_upload_attachment_info', function() {
			$postId = $_POST['postId'];

			json_response(wp_prepare_attachment_for_js($postId));
		});
	}
	//endregion

	//region Ajax Endpoints
	/**
	 * Called after a file has been uploaded and needs to be imported into the system.
	 */
	private function importUploadedFile() {
		if(!current_user_can('upload_files')) {
			json_response(["status" => "error", "message" => "Current user can't upload."]);
		}

		$key = $_POST['key'];
		if(empty($key)) {
			json_response(['status' => 'error', 'message' => 'Missing key.']);
		}

		$info = $this->client->info($key);

		$unknownMimes = [
			'application/octet-stream',
			'application/binary',
			'unknown/unknown'
		];

		if(!empty($info->mimeType()) && !in_array($info->mimeType(), $unknownMimes)) {
			if(strpos($info->mimeType(), 'image/') === 0) {
				$result = apply_filters('ilab_cloud_import_from_storage', $info);
				if($result) {
					json_response(['status' => 'success', 'data' => $result]);
				} else {
					json_response(['status' => 'error', 'message' => 'Error importing S3 file into WordPress.']);
				}
			} else {
				json_response(['status' => 'error', 'message' => 'Unknown type.', 'type' => $info->mimeType()]);
			}
		} else {
			json_response(['status' => 'error', 'message' => 'Unknown type.', 'type' => $info->mimeType()]);
		}
	}

	/**
	 * Called when ready to upload to the storage service
	 */
	private function prepareUpload() {
		if(!current_user_can('upload_files')) {
			json_response(["status" => "error", "message" => "Current user can't upload."]);
		}

		if (!$this->client || !$this->client->enabled()) {
			json_response(["status" => "error", "message" => "Storage settings are invalid."]);
		}

		$filename = $_POST['filename'];
		$type = $_POST['type'];

		if (empty($filename) || empty($type)) {
			json_response(["status" => "error", "message" => "Invalid file name or missing type."]);
		}

		$prefix = StorageSettings::prefix(null);
		$parts = explode('/', $filename);
		$bucketFilename = array_pop($parts);

		$uploadInfo = $this->client->uploadUrl($prefix.$bucketFilename,StorageSettings::privacy(), $type,StorageSettings::cacheControl(), StorageSettings::expires());
		$res = $uploadInfo->toArray();
		$res['status'] = 'ready';
		json_response($res);
	}

	/**
	 * Render settings.
	 *
	 * @param bool $insertMode
	 */
	protected function doRenderSettings($insertMode) {
		$mtypes = array_values(get_allowed_mime_types(get_current_user_id()));
		$mtypes[] = 'image/psd';

		$imgixEnabled = apply_filters('ilab_imgix_enabled', false);
		$videoEnabled = apply_filters('ilab_transcoder_enabled', false);
		$altFormatsEnabled = apply_filters('ilab_imgix_alternative_formats', false);
		$docUploadsEnabled = StorageSettings::uploadDocuments();

		$result = View::render_view('upload/ilab-media-upload.php', [
			'title' => $this->toolInfo['title'],
			'group' => $this->options_group,
			'page' => $this->options_page,
			'imgixEnabled' => $imgixEnabled,
			'videoEnabled' => $videoEnabled,
			'altFormats' => ($imgixEnabled && $altFormatsEnabled),
			'docUploads' => $docUploadsEnabled,
			'insertMode' => $insertMode,
			'allowedMimes' => $mtypes
		]);

		echo $result;
	}

	/**
	 * Render settings.
	 */
	public function renderSettings() {
		$this->doRenderSettings(false);
	}

	/**
	 * Render settings.
	 */
	public function renderInsertSettings() {
		$this->doRenderSettings(true);
	}
	//endregion
}