<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\Native;

use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerToolSettings;

/**
 * @property bool $playerCSSClasses
 * @property bool $playerMP4Fallback
 * @property string $playerMP4Quality
 */
class NativeVideoPlayerSettings extends VideoPlayerToolSettings {
	protected $nativePlayerSettings = [
		'playerCSSClasses' => ['media-cloud-player-css-classes', null, false],
	];

	public function __construct() {
		$this->settingsMap = array_merge($this->settingsMap, $this->nativePlayerSettings);
	}
}