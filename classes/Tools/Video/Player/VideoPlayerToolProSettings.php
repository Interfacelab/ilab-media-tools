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

namespace MediaCloud\Plugin\Tools\Video\Player;

/**
 * Class MuxToolSettings
 * @package MediaCloud\Mux
 *
 * @property string playerType
 * @property bool playerAllowAirPlay
 * @property bool playerAllowQualitySelect
 * @property bool playerOverrideNative
 * @property bool playerFilmstrips
 * @property bool playerMP4Fallback
 * @property bool playerAllowDownload
 * @property bool playerAllowOriginalDownload
 * @property string playerMP4Quality
 * @property string playerAnalyticsMode
 */
class VideoPlayerToolProSettings extends VideoPlayerToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $proSettingsMap = [
		'playerType' => ['media-cloud-mux-player', null, 'videojs'],
		'playerAllowAirPlay' => ['media-cloud-mux-player-air-play', null, true],
		'playerAllowQualitySelect' => ['media-cloud-mux-player-quality-select', null, true],
		'playerOverrideNative' => ['media-cloud-mux-player-override-native', null, true],
		'playerFilmstrips' => ['media-cloud-mux-player-filmstrips', null, true],
		'playerMP4Fallback' => ['media-cloud-mux-player-mp4-fallback', null, true],
		'playerMP4Quality' => ['media-cloud-mux-player-mp4-preferred-quality', null, "medium.mp4"],
		'playerAnalyticsMode' => ['media-cloud-mux-player-analytics-mode', null, "none"],
		'playerAllowDownload' => ['media-cloud-mux-player-allow-download', null, true],
		'playerAllowOriginalDownload' => ['media-cloud-mux-player-allow-download-original', null, true],
	];

	public function __construct() {
		$this->settingsMap = array_merge($this->settingsMap, $this->proSettingsMap);
	}
}