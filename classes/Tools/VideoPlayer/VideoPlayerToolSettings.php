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

namespace MediaCloud\Plugin\Tools\VideoPlayer;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class MuxToolSettings
 * @package MediaCloud\Mux
 *
 * @property bool $alwaysIncludeJS
 * @property bool $replaceAdminPlayer
 * @property bool $playerOverrideShortcode
 * @property bool $generateStoryboards
 * @property string $player
 */
class VideoPlayerToolSettings extends ToolSettings {
	private static $registry = [];
	private static $playerInstance = null;

	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'player' => ['media-cloud-mux-player', null, 'hlsjs'],
		'alwaysIncludeJS' => ['media-cloud-mux-player-include-js', null, false],
		'playerOverrideShortcode' => ['media-cloud-override-player-shortcode', null, true],
		'replaceAdminPlayer' => ['media-cloud-replace-admin-player', null, true],
		'generateStoryboards' => ['media-cloud-generate-storyboards', null, true],
	];

	public static function registerPlayer($identifier, $name, $class, $config, $help) {
		static::$registry[$identifier] = [
			'name' => $name,
			'class' => $class,
			'config' => $config,
			'help' => $help
		];
	}

	public static function players() {
		return static::$registry;
	}

	public static function playerInstance() {
		if (static::$playerInstance) {
			return static::$playerInstance;
		}

		if (!isset(static::$registry[static::player()])) {
			return null;
		}

		$class = static::$registry[static::player()]['class'];
		static::$playerInstance = new $class();

		return static::$playerInstance;
	}

	public static function player() {
		return static::instance()->player;
	}
}