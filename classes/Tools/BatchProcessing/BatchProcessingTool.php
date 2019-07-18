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

use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;

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

	//endregion
}