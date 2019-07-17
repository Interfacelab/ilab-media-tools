<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Storage\Driver\S3;

use FasterImage\FasterImage;
use ILAB\MediaCloud\Storage\FileInfo;
use ILAB\MediaCloud\Storage\InvalidStorageSettingsException;
use ILAB\MediaCloud\Storage\StorageException;
use function ILAB\MediaCloud\Utilities\arrayPath;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class OtherS3Storage extends S3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'other-s3';
	}

	public static function name() {
		return 'Other S3 Service';
	}

	public static function bucketLink($bucket) {
		$instance = new self();
		return $instance->endpoint;
	}

	public function pathLink($bucket, $key) {
		$instance = new self();
		return $instance->endpoint;
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return false;
	}

	protected function settingsErrorOptionName() {
		return 'ilab-other-s3-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	public function info( $key ) {
		if (!$this->client) {
			throw new InvalidStorageSettingsException('Storage settings are invalid');
		}

		$url = $this->url($key);
		$presignedUrl = $this->presignedUrl($key);

		$defaults = stream_context_get_default();
		stream_context_set_default(['http'=>['method'=>'HEAD']]);
		$headers = get_headers($presignedUrl, 1);
		stream_context_set_default($defaults);

		if (!empty($headers[0]) && (strpos($headers[0], '403 Forbidden') !== false)) {
			stream_context_set_default(['http'=>['method'=>'HEAD']]);
			$headers = get_headers($url, 1);
			stream_context_set_default($defaults);
		}

		$length = (arrayPath($headers, 'Content-Length', false));
		if ($length && is_array($length)) {
			$length = $length[count($length) - 1];
		}

		$type = (arrayPath($headers, 'Content-Type', false));
		if ($type && is_array($type)) {
			$type = $type[count($type) - 1];
		}

		if (empty($type) && empty($length)) {
			throw new StorageException("Unable to get Content-Type or Content-Length for '$key'");
		}

		$size = null;
		if (strpos($type, 'image/') === 0) {
			$faster = new FasterImage();
			$result = $faster->batch([$presignedUrl]);
			$result = $result[$presignedUrl];
			$size = $result['size'];
		}

		$fileInfo = new FileInfo($key, $url, $presignedUrl, $length, $type, $size);
		return $fileInfo;
	}
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-other-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-other-s3.js', [], false, true);
	}
	//endregion
}
