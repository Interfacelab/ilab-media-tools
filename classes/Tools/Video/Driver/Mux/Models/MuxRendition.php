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

namespace MediaCloud\Plugin\Tools\Video\Driver\Mux\Models;

use MediaCloud\Plugin\Model\Model;

/**
 * Class MuxRendition
 * @package MediaCloud\Plugin\Tools\Video\Driver\Mux\Models
 *
 * @property string $muxId
 * @property string $rendition
 * @property int $width
 * @property int $height
 * @property int $bitrate
 * @property int $filesize
 */
class MuxRendition extends Model {
	//region Fields
	/**
	 * Mux Identifier
	 * @var null
	 */
	protected $muxId = null;

	/**
	 * Rendition file name
	 * @var null
	 */
	protected $rendition = null;

	/**
	 * Width
	 * @var string
	 */
	protected $width = 0;

	/**
	 * Height
	 * @var string
	 */
	protected $height = 0;

	/**
	 * Bit rate
	 * @var string
	 */
	protected $bitrate = 0;

	/**
	 * File size
	 * @var string
	 */
	protected $filesize = 0;


	protected $modelProperties = [
		'muxId' => '%s',
		'rendition' => '%s',
		'width' => '%d',
		'height' => '%d',
		'bitrate' => '%d',
		'filesize' => '%d',
	];
	//endregion

	//region Static

	public static function table() {
		global $wpdb;
		return "{$wpdb->base_prefix}mcloud_mux_renditions";
	}

	//endregion

	//region Queries

	/**
	 * Return renditions for a given asset
	 *
	 * @param string $muxId
	 * @param string|null $rendition
	 *
	 * @return MuxRendition[]
	 * @throws \Exception
	 */
	public static function renditionsForAsset($muxId, $rendition = null) {
		global $wpdb;

		$table = static::table();
		if (!empty($rendition)) {
			$sql = $wpdb->prepare("select * from {$table} where muxId = %s and rendition = %s", $muxId, $rendition);
		} else {
			$sql = $wpdb->prepare("select * from {$table} where muxId = %s", $muxId);
		}

		$results = [];
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			$results[] = new static($row);
		}

		return $results;
	}

	/**
	 * Returns a task with the given ID
	 *
	 * @param $muxId
	 * @param $playbackId
	 *
	 * @return MuxRendition|null
	 * @throws \Exception
	 */
	public static function rendition($muxId, $rendition) {
		global $wpdb;

		$table = static::table();
		$rows = $wpdb->get_results($wpdb->prepare("select * from {$table} where muxId = %s and rendition = %s", $muxId, $rendition));

		foreach($rows as $row) {
			return new static($row);
		}

		return null;
	}


	/**
	 * Returns a rendition with the given ID, if not found, creates a new one.
	 *
	 * @param $muxId
	 * @param $rendition
	 *
	 * @return MuxRendition
	 * @throws \Exception
	 */
	public static function findOrCreate($muxId, $rendition) {
		$item = static::rendition($muxId, $rendition);
		if ($item !== null) {
			return $item;
		}

		$item = new MuxRendition();
		$item->muxId = $muxId;
		$item->rendition = $rendition;

		return $item;
	}

	//endregion
}