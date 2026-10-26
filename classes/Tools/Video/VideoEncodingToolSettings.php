<?php

namespace MediaCloud\Plugin\Tools\Video;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * @property string|null $driver
 */
class VideoEncodingToolSettings extends ToolSettings {
	private static $registry = [];
	private static $encodingInstance = null;

	protected $settingsMap = [
		"driver" => ['media-cloud-video-encoding-provider','ILAB_CLOUD_VIDEO_ENCODING_PROVIDER', null],
	];

	public static function registerDriver($identifier, $name, $class, $config, $help) {
		static::$registry[$identifier] = [
			'name' => $name,
			'class' => $class,
			'config' => $config,
			'help' => $help
		];
	}

	public static function drivers() {
		return static::$registry;
	}

	/**
	 * Gets the currently configured storage interface.
	 *
	 * @return VideoEncodingInterface
	 */
	public static function encodingInstance() {
		if (static::$encodingInstance) {
			return static::$encodingInstance;
		}

		if (!static::driver()) {
			return null;
		}

		if (!isset(static::$registry[static::driver()])) {
			return null;
		}

		$class = static::$registry[static::driver()]['class'];
		static::$encodingInstance = new $class();

		return static::$encodingInstance;
	}

	public static function driver() {
		return static::instance()->driver;
	}
}