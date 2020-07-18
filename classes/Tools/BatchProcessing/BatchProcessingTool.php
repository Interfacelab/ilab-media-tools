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

namespace ILAB\MediaCloud\Tools\BatchProcessing;

use ILAB\MediaCloud\Storage\StorageToolSettings;
use ILAB\MediaCloud\Tasks\TaskDatabase;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use function ILAB\MediaCloud\Utilities\json_response;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class BatchProcessingTool
 *
 * Tool for managing batch processing settings.  This class doesn't do anything.
 */
class BatchProcessingTool extends Tool {

	//region Tool Overrides

	public function enabled() {
		return true;
	}

	public function envEnabled() {
		return true;
	}

	public function alwaysEnabled() {
		return true;
	}

	//endregion



	//region Actions

	public function clearBackgroundTokens() {
		global $wpdb;
		$wpdb->query("delete from {$wpdb->options} where option_name like '%mcloud_token_%'");

		TaskDatabase::deleteOldTokens();

		json_response(['status' => 'ok', 'message' => 'Tokens cleared.']);
	}

	//endregion
}
