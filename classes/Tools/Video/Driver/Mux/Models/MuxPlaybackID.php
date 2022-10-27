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
 * Class MuxAsset
 * @package MediaCloud\Plugin\Tools\Video\Driver\Mux\Models
 *
 * @property string $muxId
 * @property string $playbackId
 * @property string $policy
 */
class MuxPlaybackID extends Model {
	//region Fields
	/**
	 * Mux Identifier
	 * @var null
	 */
	protected $muxId = null;

	/**
	 * Mux playback ID
	 * @var null
	 */
	protected $playbackId = null;

	/**
	 * Mux policy
	 * @var string
	 */
	protected $policy = 0;


	protected $modelProperties = [
		'muxId' => '%s',
		'playbackId' => '%s',
		'policy' => '%s',
	];
	//endregion

	//region Static

	public static function table() {
		global $wpdb;
		return "{$wpdb->base_prefix}mcloud_mux_playback";
	}

	//endregion

	//region Queries

	/**
	 * Return playback IDs for a given asset
	 *
	 * @param string $muxId
	 * @param string|null $policy
	 *
	 * @return MuxPlaybackID[]
	 * @throws \Exception
	 */
	public static function playbackIDsForAsset($muxId, $policy = null) {
		global $wpdb;

		$table = static::table();
		if (!empty($policy)) {
			$sql = $wpdb->prepare("select * from {$table} where muxId = %s and policy = %s", $muxId, $policy);
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
	 * @return MuxPlaybackID|null
	 * @throws \Exception
	 */
	public static function playbackID($muxId, $playbackId) {
		global $wpdb;

		$table = static::table();
		$rows = $wpdb->get_results($wpdb->prepare("select * from {$table} where muxId = %s and playbackId = %s", $muxId, $playbackId));

		foreach($rows as $row) {
			return new static($row);
		}

		return null;
	}


	/**
	 * Returns a task with the given ID, if not found, creates a new one.
	 *
	 * @param $muxId
	 * @param $playbackId
	 *
	 * @return MuxPlaybackID|null
	 * @throws \Exception
	 */
	public static function findOrCreate($muxId, $playbackId) {
		$item = static::playbackID($muxId, $playbackId);
		if (!empty($item)) {
			return $item;
		}

		$item = new MuxPlaybackID();
		$item->muxId = $muxId;
		$item->playbackId = $playbackId;
		return $item;
	}

	//endregion
}