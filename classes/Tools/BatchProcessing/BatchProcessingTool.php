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

namespace MediaCloud\Plugin\Tools\BatchProcessing;

use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tasks\TaskDatabase;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\json_response;

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
