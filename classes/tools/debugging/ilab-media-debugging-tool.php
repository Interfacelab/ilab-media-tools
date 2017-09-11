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

	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

		$paperTrailEndPoint = get_option('ilab-media-s3-debug-papertrail-endpoint', false);
		$paperTrailPort = get_option('ilab-media-s3-debug-papertrail-port', false);

		if (!empty($paperTrailEndPoint) && !empty($paperTrailPort)) {
			if (!function_exists('socket_create') && $this->enabled()) {
				$this->displayAdminNotice('error', 'You have specified papertrail endpoint and ports, but you are missing the <a href="http://php.net/manual/en/book.sockets.php" target=_blank>php socket extension</a>.  Please install this extension to use remote logging.');
			}
		}
	}
}