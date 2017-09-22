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

namespace ILAB\MediaCloud\Tools\Debugging;

use ILAB\MediaCloud\Tools\ToolBase;
use ILAB\MediaCloud\Utilities\NoticeManager;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class DebuggingTool extends ToolBase {
	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

		$paperTrailEndPoint = get_option('ilab-media-s3-debug-papertrail-endpoint', false);
		$paperTrailPort = get_option('ilab-media-s3-debug-papertrail-port', false);

		if (!empty($paperTrailEndPoint) && !empty($paperTrailPort)) {
			if (!function_exists('socket_create') && $this->enabled()) {
				NoticeManager::instance()->displayAdminNotice('error', 'You have specified papertrail endpoint and ports, but you are missing the <a href="http://php.net/manual/en/book.sockets.php" target=_blank>php socket extension</a>.  Please install this extension to use remote logging.');
			}
		}
	}
}