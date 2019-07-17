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

class FileInfo {
	protected $key = null;
	protected $url = null;
	protected $signedUrl = null;
	protected $length = 0;
	protected $mimeType = null;
	protected $size = null;

	public function __construct($key, $url, $signedUrl, $length, $mimeType = null, $size = null) {
		$this->key = $key;
		$this->url = $url;
		$this->signedUrl = $signedUrl;
		$this->length = $length;
		$this->mimeType = $mimeType;
		$this->size = $size;
	}

	public function key() {
		return $this->key;
	}

	public function url() {
		return $this->url;
	}

	public function signedUrl() {
		return $this->signedUrl;
	}

	public function length() {
		return $this->length;
	}

	public function mimeType() {
		return $this->mimeType;
	}

	public function size() {
		return $this->size;
	}
}
