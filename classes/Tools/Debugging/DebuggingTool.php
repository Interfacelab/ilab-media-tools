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

use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogger;
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogTable;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\View;
use Probe\ProviderFactory;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class DebuggingTool extends Tool {
	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

		if ($this->enabled()) {
            Logger::instance();

            if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'media-tools-debug-log') && isset($_POST['action'])) {
                if ($_POST['action'] == 'csv') {
                    $this->generateCSV();
                } else if ($_POST['action'] == 'bug') {
                    $this->generateBug();
                } else if ($_POST['action'] == 'clear-log') {
                    $this->clearLog();
                }
            }

            $link = "<a href='".admin_url('admin.php?page=media-tools-top')."'>turn it off</a>";
            $message = "Media Cloud debugging is enabled.  This may affect performance.  Unless you are troubleshooting and issue, you should $link.  You can dismiss this notice and it'll be shown to you again in 24 hours.";
            NoticeManager::instance()->displayAdminNotice('warning', $message,true, 'ilab-debug-tools-warning', 1);
        }

	}

    public function registerHelpMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
        parent::registerHelpMenu($top_menu_slug);

        if($this->enabled() && (($networkMode && $networkAdminMenu) || (!$networkMode && !$networkAdminMenu))) {
            ToolsManager::instance()->insertHelpToolSeparator();
            add_submenu_page($top_menu_slug, 'Debug Log', 'Debug Log', 'manage_options', 'media-tools-debug-log', [
                $this,
                'renderDebugLog'
            ]);
        }
    }

    //region Debug Log

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
	    $probe = ProviderFactory::create();

	    $probeData = [
	        'OS' => trim($probe->getOsType()),
            'OS Version' => trim($probe->getOsRelease()),
            'OS Kernel' => trim($probe->getOsKernelVersion()),
            'Server' => trim($probe->getServerSoftware()),
            'PHP' => trim($probe->getPhpVersion()),
            'PHP SAPI' => trim($probe->getPhpSapiName()),
            'PHP Modules' => $probe->getPhpModules(),
            'PHP Disabled Functions' => $probe->getPhpDisabledFunctions()
        ];

	    $active = [];

	    $activePlugins = get_option('active_plugins');

	    $plugins = get_plugins();

	    foreach($activePlugins as $activePlugin) {
	        if (in_array($activePlugin, array_keys($plugins))) {
	            $active[$activePlugin] = $plugins[$activePlugin];
            }
        }

        $probeData['Must Use Plugins'] = get_mu_plugins();
        $probeData['Plugins'] = $active;
        $probeData['php.ini'] = ini_get_all(null, false);

        header('Content-Disposition: attachment;filename="media-cloud-debug.json";');
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($probeData, JSON_PRETTY_PRINT);
        die;
    }

    public function clearLog() {
        $logger = new DatabaseLogger();
        $logger->clearLog();

        $location = admin_url('admin.php?page=media-tools-debug-log');
        header("Location: $location", true, 302);
        die;
    }

    //endregion

}