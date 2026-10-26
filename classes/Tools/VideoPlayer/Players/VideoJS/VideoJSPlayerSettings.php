<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\VideoJS;

use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerToolSettings;

/**
 * @property string $playerCSSClasses
 * @property bool $playerAllowAirPlay
 * @property bool $playerAllowQualitySelect
 * @property bool $playerOverrideNative
 * @property bool $playerFilmstrips
 * @property bool $playerMP4Fallback
 * @property bool $playerAllowDownload
 * @property bool $playerAllowOriginalDownload
 * @property string $playerMP4Quality
 * @property string $playerAnalyticsMode
 * @property bool $customizeColors
 * @property string $primaryColor
 * @property string $buttonColor
 * @property string $playbackBarColor
 * @property string $playbackBallColor
 * @property string $loadProgressBarColor
 */
class VideoJSPlayerSettings extends VideoPlayerToolSettings {
	protected $videoJSPlayerSettings = [
		'playerCSSClasses' => ['media-cloud-player-css-classes', null, false],
		'playerAllowAirPlay' => ['media-cloud-mux-player-air-play', null, true],
		'playerAllowQualitySelect' => ['media-cloud-mux-player-quality-select', null, true],
		'playerOverrideNative' => ['media-cloud-mux-player-override-native', null, true],
		'playerFilmstrips' => ['media-cloud-mux-player-filmstrips', null, true],
		'playerMP4Fallback' => ['media-cloud-mux-player-mp4-fallback', null, true],
		'playerMP4Quality' => ['media-cloud-mux-player-mp4-preferred-quality', null, "medium.mp4"],
		'playerAnalyticsMode' => ['media-cloud-mux-player-analytics-mode', null, "none"],
		'playerAllowDownload' => ['media-cloud-mux-player-allow-download', null, true],
		'playerAllowOriginalDownload' => ['media-cloud-mux-player-allow-download-original', null, true],
		'customizeColors' => ['media-cloud-mux-player-customize-colors', null, false],
		'primaryColor' => ['media-cloud-player-color-primary', null, '#000000'],
		'buttonColor' => ['media-cloud-player-color-buttons', null, '#FFFFFF'],
		'playbackBarColor' => ['media-cloud-player-color-play-position', null, '#FFFFFF'],
		'playbackBallColor' => ['media-cloud-player-color-play-position-ball', null, '#FFFFFF'],
		'loadProgressBarColor' => ['media-cloud-player-color-load-progress', null, '#545454'],
	];

	public function __construct() {
		$this->settingsMap = array_merge($this->settingsMap, $this->videoJSPlayerSettings);
	}
}