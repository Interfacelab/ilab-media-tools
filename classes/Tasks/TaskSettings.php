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

namespace MediaCloud\Plugin\Tasks;


use MediaCloud\Plugin\Tools\ToolSettings;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * @property bool $generateReports
 * @property string $verifySSL
 * @property float $connectTimeout
 * @property float $timeout
 * @property bool $skipDNS
 * @property string $skipDNSHost
 * @property string $httpClientName
 * @property bool $heartbeatEnabled
 * @property int $heartbeatFrequency
 * @property int $taskLimit
 * @property bool $scheduleBulkActions
 * @property int $scheduleBulkActionsDelay
 * @property bool $useWordPressHeartbeat
 */
class TaskSettings extends ToolSettings {
	protected $settingsMap = [
		"generateReports" => ['mcloud-tasks-generate-reports', null, true],
		"verifySSL" => ['mcloud-tasks-verify-ssl', null, 'no'],
		"connectTimeout" => ['mcloud-tasks-connect-timeout', null, 0.01],
		"timeout" => ['mcloud-tasks-timeout', null, 0.01],
		"skipDNS" => ['mcloud-tasks-skip-dns', null, false],
		"skipDNSHost" => ['mcloud-tasks-skip-dns-host', null, 'ip'],
		"httpClientName" => ['mcloud-tasks-http-client', null, 'wordpress'],
		"heartbeatEnabled" => ['mcloud-tasks-heartbeat-enabled', null, true],
		"heartbeatFrequency" => ['mcloud-tasks-heartbeat-frequency', null, 15],
		"taskLimit" => ['mcloud-tasks-task-limit', null, 2],
		"scheduleBulkActions" => ['mcloud-tasks-schedule-bulk', null, false],
		"scheduleBulkActionsDelay" => ['mcloud-tasks-schedule-delay', null, 60],
		"useWordPressHeartbeat" => ['mcloud-tasks-use-wordpress-heartbeat', null, false],
	];
}