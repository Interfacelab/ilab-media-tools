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

if (!defined('ABSPATH')) { header('Location: /'); die; }

require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-base.php');

if (file_exists(ILAB_VENDOR_DIR.'/autoload.php')) {
	require_once(ILAB_VENDOR_DIR.'/autoload.php');
}

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class ILabMediaDebuggingTool extends ILabMediaToolBase {

}