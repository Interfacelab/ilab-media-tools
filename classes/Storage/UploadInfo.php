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

namespace ILAB\MediaCloud\Storage;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class UploadInfo
 * @package ILAB\MediaCloud\Storage
 */
abstract class UploadInfo {
	/**
	 * The upload key
	 * @return string
	 */
	public abstract function key();

	/**
	 * The upload URL
	 * @return string
	 */
	public abstract function url();

	/**
	 * The form data for posting
	 * @return array
	 */
	public abstract function formData();

	/**
	 * The Cache-Control value
	 * @return string|null
	 */
	public abstract function cacheControl();

	/**
	 * The Expiration value
	 * @return string|null
	 */
	public abstract function expires();

	/**
	 * The acl
	 * @return string|null
	 */
	public abstract function acl();

	/**
	 * Returns the upload info as an array
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'key' => $this->key(),
			'url' => $this->url(),
			'formData' => $this->formData(),
			'cacheControl' => $this->cacheControl(),
			'expires' => $this->expires(),
			'acl' => $this->acl()
		];
	}

	/**
	 * Returns the upload info as a JSON string
	 *
	 * @return string
	 */
	public function toJson() {
		return json_encode($this->toArray(), JSON_PRETTY_PRINT);
	}
}