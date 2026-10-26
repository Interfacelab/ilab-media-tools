<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Bunny;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * @property bool $useEmbeddedPlayer
 * @property bool $secureVideo
 * @property int $secureExpiration
 * @property int $tokenAuthKey
 * @property bool $excludePrivateREST
 */
class BunnyStreamToolProSettings extends BunnyStreamToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $proSettingsMap = [
		'useEmbeddedPlayer' => ['media-cloud-bunny-use-embed-player', null, true],
		'secureVideo' => ['media-cloud-bunny-secure-video', null, false],
		'secureExpiration' => ['media-cloud-bunny-secure-video-expiration', null, 0],
		'tokenAuthKey' => ['media-cloud-bunny-secure-token', null, null],
		'excludePrivateREST' => ['media-cloud-bunny-exclude-private-from-rest', null, true],
	];


	public function __construct() {
		$this->settingsMap = array_merge($this->settingsMap, $this->proSettingsMap);
	}
}