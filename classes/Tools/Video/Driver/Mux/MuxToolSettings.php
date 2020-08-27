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

namespace MediaCloud\Plugin\Tools\Video\Driver\Mux;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class MuxToolSettings
 * @package MediaCloud\Mux
 *
 * @property string tokenID
 * @property string tokenSecret
 * @property string webhookSecret
 * @property bool processUploads
 * @property bool deleteFromMux
 * @property bool normalizeAudio
 * @property bool perTitleEncoding
 * @property bool testMode
 * @property bool playerCSSClasses
 */
class MuxToolSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'tokenID' => ['media-cloud-mux-token-id', null, null],
		'tokenSecret' => ['media-cloud-mux-token-secret', null, null],
		'webhookSecret' => ['media-cloud-mux-webhook-secret', null, null],
		'processUploads' => ['media-cloud-mux-process-uploads', null, true],
		'deleteFromMux' => ['media-cloud-mux-delete-uploads', null, true],
		'normalizeAudio' => ['media-cloud-mux-normalize-audio', null, false],
		'perTitleEncoding' => ['media-cloud-mux-per-title-encoding', null, false],
		'testMode' => ['media-cloud-mux-test-mode', null, false],
		'playerCSSClasses' => ['media-cloud-player-css-classes', null, false],
	];
}