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

use Carbon\Carbon;

class StorageFile {
	/** @var string The type of file (DIR or FILE) */
	protected $type;

	/** @var string The key (or prefix) for the file */
	protected $key;

	/** @var string The of the file */
	protected $name;

	/** @var Carbon|null  Modified date, null */
	protected $date = null;

	/** @var int Size of the file, 0 for directories */
	protected $size;

	/** @var string The URL for the file */
	protected $url;


	public function __construct($type, $key, $name = null, $date = null, $size = 0, $url = null) {
		$this->type = $type;
		$this->key = $key;
		$this->name = $name;

		if (empty($this->name)) {
			if ($this->type == 'FILE') {
				$this->name = pathinfo($this->key, PATHINFO_BASENAME);
			} else {
				$nameParts = explode('/', rtrim($this->key, '/'));
				$name = array_pop($nameParts);
				if (empty($name)) {
					$this->name = '&nbsp;&nbsp;';
				} else {
					$this->name = $name;
				}
			}
		}

		if (!empty($date)) {
			if ($date instanceof \DateTime) {
				$this->date = Carbon::instance($date);
			} else {
				$this->date = new Carbon($date);
			}
		}


		$this->size = $size;
		$this->url = $url;
	}

	/**
	 * The type of file (DIR or FILE)
	 * @return string
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * The key (or prefix) for the file
	 * @return string
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * Returns the file's name
	 * @return string
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Modified date
	 * @return Carbon|null
	 */
	public function date() {
		return $this->date;
	}

	/**
	 * The date as a formatted string
	 * @return string
	 */
	public function dateString() {
		if (empty($this->date)) {
			return '';
		}

		return $this->date->format('M jS, Y g:i:s A T');
	}

	/**
	 * Size of the file, 0 for directories
	 * @return int
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Size of the file as a string
	 * @return string
	 */
	public function sizeString() {
		if ($this->type != 'FILE') {
			return '';
		}

		if ($this->size < 1024) {
			return $this->size . ' B';
		}

		if ($this->size < (1024 * 1024)) {
			return sprintf('%.1f', floatval($this->size) / 1024) . ' KB';
		}

		if ($this->size < (1024 * 1024 * 1024)) {
			return sprintf('%.1f', floatval($this->size) / (1024 * 1024)) . ' MB';
		}

		return sprintf('%.1f', floatval($this->size) / (1024 * 1024 * 1024)) . ' GB';
	}

	/**
	 * URL for the file
	 * @return null|string
	 */
	public function url() {
		return $this->url;
	}
}