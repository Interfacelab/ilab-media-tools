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

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class DigitalOceanStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'do';
	}

	public static function name() {
		return 'DigitalOcean Spaces';
	}

	public static function bucketLink($bucket) {
		return "https://cloud.digitalocean.com/spaces/$bucket";
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		return "https://cloud.digitalocean.com/spaces/{$bucket}?path={$key}";
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	protected function settingsErrorOptionName() {
		return 'ilab-do-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function enqueueUploaderScripts() {
		wp_enqueue_script('ilab-media-direct-upload-s3', ILAB_PUB_JS_URL.'/ilab-media-direct-upload-s3.js', [], false, true);
	}
	//endregion
}
