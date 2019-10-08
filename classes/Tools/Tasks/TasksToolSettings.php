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

namespace ILAB\MediaCloud\Tools\Tasks;


use ILAB\MediaCloud\Tools\ToolSettings;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * @property bool heartbeatEnabled
 * @property bool heartbeatFrequency
 */
class TasksToolSettings extends ToolSettings {
	protected $settingsMap = [
		"heartbeatEnabled" => ['mcloud-tasks-heartbeat-enabled', null, true],
		"heartbeatFrequency" => ['mcloud-tasks-heartbeat-frequency', null, 15],
	];
}