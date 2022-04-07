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

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class MuxToolSettings
 * @package MediaCloud\Mux
 *
 * @property bool playerCSSClasses
 */
class VideoPlayerToolSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'playerCSSClasses' => ['media-cloud-player-css-classes', null, false],
	];
}