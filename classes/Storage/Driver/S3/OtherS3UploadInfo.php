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

use ILAB\MediaCloud\Storage\UploadInfo;

/**
 * Class OtherS3UploadInfo
 * @package ILAB\MediaCloud\Storage\Driver\S3
 */
class OtherS3UploadInfo extends UploadInfo {
	private $key;
	private $url;
	private $acl;
	private $formData;
	private $cacheControl;
	private $expires;

	/**
	 * GoogleUploadInfo constructor.
	 *
	 * @param string $key
	 * @param string $url
	 * @param string $acl
	 */
	public function __construct($key, $url, $acl) {
		$this->key = $key;
		$this->url = $url;
		$this->acl = $acl;
	}

	public function key() {
		return $this->key;
	}

	public function url() {
		return $this->url;
	}

	public function formData() {
		return null;
	}

	public function cacheControl() {
		return null;
	}

	public function expires() {
		return null;
	}

	public function acl() {
		return $this->acl;
	}
}