<?php


namespace MediaCloud\Plugin\Tools\Debugging;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class UploadToolSettings
 *
 * @property string $debugLoggingLevel
 * @property bool $debugContentFiltering
 * @property int $maxDatabaseEntries
 * @property string $debugRemoteUrl
 * @property int $debugRemotePort
 */
class DebuggingToolSettings extends ToolSettings {
	protected $settingsMap = [
		"debugLoggingLevel" => ['mcloud-debug-logging-level', null, 'info'],
		"debugContentFiltering" => ['mcloud-debug-content-filtering', null, false],
		"maxDatabaseEntries" => ['mcloud-debug-max-database-entries', null, 1000],
		"debugRemoteUrl" => ['mcloud-debug-remote-url', null, null],
		"debugRemotePort" => ['mcloud-debug-remote-url-port', null, true],
	];

}