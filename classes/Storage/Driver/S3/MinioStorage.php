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

class MinioStorage extends OtherS3Storage {
	//region Properties

	//endregion

	//region Constructor

	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'minio';
	}

	public static function name() {
		return 'Minio';
	}

	public static function bucketLink($bucket) {
		$instance = new self();
		return $instance->endpoint.'/minio/'.$bucket;
	}

	public function pathLink($bucket, $key) {
		$keyParts = explode('/', $key);
		array_pop($keyParts);
		$key = implode('/', $keyParts).'/';

		$instance = new self();
		return $instance->endpoint.'/minio/'.$bucket.'/'.$key;
	}
	//endregion

	//region Enabled/Options
	public function supportsDirectUploads() {
		return true;
	}

	protected function settingsErrorOptionName() {
		return 'ilab-minio-settings-error';
	}
	//endregion

	//region Client Creation
	//endregion

	//region File Functions
	//endregion

	//region URLs
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
		$args = [
			'Bucket' => $this->bucket,
			'Key' => $key
		];

		if (!empty($mimeType)) {
			$args['ContentType'] = $mimeType;
		}

		if (!empty($cacheControl)) {
			$args['CacheControl'] = $cacheControl;
		}

		if (!empty($expires)) {
			$args['Expires'] = $expires;
		}


		$command = $this->client->getCommand('PutObject', $args);
		$presignedReq = $this->client->createPresignedRequest($command, '+15 minutes');

		return new OtherS3UploadInfo($key, $presignedReq->getUri()->__toString(), $acl);
	}
	//endregion
}
