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
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogger;
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogTable;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\PHPInfo;
use ILAB\MediaCloud\Utilities\View;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class DebuggingTool extends ToolBase {
	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'media-tools-debug-log') && isset($_POST['action'])) {
            if ($_POST['action'] == 'csv') {
                $this->generateCSV();
            } else if ($_POST['action'] == 'bug') {
                $this->generateBug();
            }
        }
	}

    public function registerMenu($top_menu_slug) {
        parent::registerMenu($top_menu_slug);

        if($this->enabled()) {
            add_submenu_page($top_menu_slug, 'Debug Log', 'Debug Log', 'manage_options', 'media-tools-debug-log', [
                $this,
                'renderDebugLog'
            ]);
        }
    }

    public function renderDebugLog() {
	    $table = new DatabaseLogTable();
	    $table->prepare_items();

        echo View::render_view('debug/log-viewer.php', [
            'table' => $table
        ]);
    }

    public function generateCSV() {
	    $logger = new DatabaseLogger();

        header('Content-Disposition: attachment;filename="media-cloud-log.csv";');
        header('Content-Type: application/csv; charset=UTF-8');
        echo $logger->csv();
        die;
    }

    public function generateBug() {
        $logger = new DatabaseLogger();

        ob_start();
        phpinfo();
        $contents = ob_get_contents();
        ob_clean();

        $re = '/^\<tr\>\<td\s+class=\"e\">(.*)\<\/td\>\<td\s+class=\"v\"\>(.*)\<\/td\><\/tr>/msU';
        preg_match_all($re, $contents, $matches, PREG_SET_ORDER, 0);

        vomit($matches);

        header('Content-Disposition: attachment;filename="media-cloud-log.csv";');
        header('Content-Type: application/csv; charset=UTF-8');
        echo $logger->csv();
        die;

    }

}