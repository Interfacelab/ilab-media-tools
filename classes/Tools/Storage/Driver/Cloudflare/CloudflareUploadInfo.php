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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Cloudflare;


use MediaCloud\Plugin\Tools\Storage\UploadInfo;

/**
 * Class S3UploadInfo
 * @package MediaCloud\Plugin\Tools\Storage\Driver\S3
 */
class CloudflareUploadInfo extends UploadInfo {
	private $key;
	private $url;
	private $acl;
	private $cacheControl;
	private $expires;

	/**
	 * S3UploadInfo constructor.
	 *
	 * @param string $url
	 * @param string $key
	 * @param string $acl
	 * @param string $mimeType
	 * @param string|null $cacheControl
	 * @param string|null $expires
	 */
	public function __construct($url, $key, $mimeType = null, $acl = null, $cacheControl=null, $expires=null) {
		$this->url = $url;
		$this->key = $key;
		$this->acl = $acl;
		$this->mimeType = $mimeType;
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
		return [];
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

	public function mimeType() {
		return $this->mimeType;
	}


	/**
	 * Returns the upload info as an array
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'key' => $this->key(),
			'url' => $this->url(),
			'mimeType' => $this->mimeType(),
			'formData' => $this->formData(),
			'cacheControl' => $this->cacheControl(),
			'expires' => $this->expires(),
			'acl' => $this->acl()
		];
	}
}