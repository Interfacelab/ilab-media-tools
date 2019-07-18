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
use ILABAmazon\S3\PostObjectV4;

/**
 * Class S3UploadInfo
 * @package ILAB\MediaCloud\Storage\Driver\S3
 */
class S3UploadInfo extends UploadInfo {
	private $key;
	private $url;
	private $acl;
	private $formData;
	private $cacheControl;
	private $expires;

	/**
	 * S3UploadInfo constructor.
	 *
	 * @param string $key
	 * @param PostObjectV4 $postObject
	 * @param string $acl
	 * @param string|null $cacheControl
	 * @param string|null $expires
	 */
	public function __construct($key, $postObject, $acl, $cacheControl=null, $expires=null) {
		$this->key = $key;
		$this->url = $postObject->getFormAttributes()['action'];
		$this->formData = $postObject->getFormInputs();
		$this->acl = $acl;
		$this->cacheControl = $cacheControl;
		$this->expires = $expires;
	}

	public function key() {
		return $this->key;
	}

	public function url() {
		return $this->url;
	}

	public function formData() {
		return $this->formData;
	}

	public function cacheControl() {
		return $this->cacheControl;
	}

	public function expires() {
		return $this->expires;
	}

	public function acl() {
		return $this->acl;
	}
}