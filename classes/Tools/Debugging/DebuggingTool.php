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

namespace MediaCloud\Plugin\Tools\Debugging;

use MediaCloud\Plugin\Tools\Debugging\System\SystemCompatibilityTool;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\DatabaseLogger;
use MediaCloud\Plugin\Utilities\Logging\DatabaseLogTable;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\ParagonIE\EasyRSA\EasyRSA;
use MediaCloud\Vendor\ParagonIE\EasyRSA\PublicKey;
use MediaCloud\Vendor\Probe\ProviderFactory;
use function MediaCloud\Plugin\Utilities\discoverHooks;

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

            if (current_user_can('manage_options')) {
	            add_action('wp_ajax_mcloud-debug-download-debug-log', function() {
		            check_ajax_referer('mcloud-debug-download-debug-log', 'nonce');
		            $this->actionDownloadLog();
	            });

	            add_action('wp_ajax_mcloud-debug-generate-system-report', function() {
		            check_ajax_referer('mcloud-debug-generate-system-report', 'nonce');
		            $this->actionGenerateReport();
	            });

	            add_action('wp_ajax_mcloud-debug-clear-debug-log', function() {
		            check_ajax_referer('mcloud-debug-clear-debug-log', 'nonce');
		            $this->actionClearLog();
	            });

	            add_action('wp_ajax_mcloud-get-debug-log', function() {
		            check_ajax_referer('mcloud-get-debug-log', 'nonce');
	            	$this->actionGetDebugLog();
	            });
            }

            $link = "<a href='".admin_url('admin.php?page=media-tools-top')."'>turn it off</a>";
            $message = "Media Cloud debugging is enabled.  This may affect performance.  Unless you are troubleshooting and issue, you should $link.  You can dismiss this notice and it'll be shown to you again in 24 hours.";
            NoticeManager::instance()->displayAdminNotice('warning', $message,true, 'ilab-debug-tools-warning', 1);
        }
	}


	public function hasSettings() {
		return true;
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

    private function actionGetDebugLog() {
		if (!is_admin() || !current_user_can('manage_options')) {
			die;
		}

	    $tableView = new DatabaseLogTable([
	    	'screen' => 'ajax'
	    ]);
	    $tableView->prepare_items();

	    ob_start();
	    $tableView->display();
	    $html = ob_get_clean();

	    $doc = new \DOMDocument();
	    libxml_use_internal_errors(true);
	    $doc->loadHTML($html);

	    /** @var \DOMNode $tableNavDiv */
	    $tableNavDiv = null;
	    $divs = $doc->getElementsByTagName('div');
	    /** @var \DOMNode $div */
	    foreach($divs as $div) {
	    	$classAttr = $div->attributes->getNamedItem('class');
	    	if (!empty($classAttr)) {
			    if (strpos($classAttr->nodeValue, 'tablenav-pages') !== false) {
				    $tableNavDiv = $div;
				    break;
			    }
		    }
	    }

	    /** @var \DOMNode $logTable */
	    $logTable = null;
	    $tables = $doc->getElementsByTagName('table');
	    /** @var \DOMNode $div */
	    foreach($tables as $table) {
		    $logTable = $table;
		    break;
	    }

	    if (empty($tableNavDiv) || empty($logTable)) {
	    	wp_send_json([
	    		'status' => 'empty'
		    ]);
	    } else {
	    	wp_send_json([
			    'status' => 'ok',
	    		'nav' => $doc->saveHTML($tableNavDiv),
			    'table' => $doc->saveHTML($logTable)
		    ]);
	    }
    }

    private function actionDownloadLog() {
	    $logger = new DatabaseLogger();

	    wp_send_json([
	    	'status' => 'ok',
		    'download' => [
		    	'file' => 'media-cloud-log.csv',
		    	'type' => 'application/csv',
		    	'contents' => $logger->csv(),
		    ]
	    ]);
    }

    private function actionGenerateReport() {
		add_filter('media-cloud/compat/disable-apply', '__return_true', PHP_INT_MAX);

	    $probe = ProviderFactory::create();

	    $probeData = [
	        'OS' => trim($probe->getOsType()),
            'OS Version' => trim($probe->getOsRelease()),
            'OS Kernel' => trim($probe->getOsKernelVersion()),
            'Server' => trim($probe->getServerSoftware()),
            'PHP' => trim($probe->getPhpVersion()),
            'PHP SAPI' => trim($probe->getPhpSapiName()),
            'PHP Modules' => $probe->getPhpModules(),
		    'PHP Disabled Functions' => $probe->getPhpDisabledFunctions(),
        ];

	    $active = [];
	    $activePlugins = get_option('active_plugins');
	    $plugins = get_plugins();

	    foreach($activePlugins as $activePlugin) {
	        if (in_array($activePlugin, array_keys($plugins))) {
	            $active[$activePlugin] = $plugins[$activePlugin];
            }
        }

	    $inactivePlugins = [];
	    foreach($plugins as $pluginSlug => $pluginData) {
	    	if (!in_array($pluginSlug, $activePlugins)) {
	    		$inactivePlugins[$pluginSlug] = $pluginData;
		    }
	    }

	    $probeData['php.ini'] = ini_get_all(null, false);

	    $probeData['WordPress Settings'] = [];
	    $probeData['WordPress Settings']['uploads_use_yearmonth_folders'] = get_option('uploads_use_yearmonth_folders', true);
	    $probeData['WordPress Settings']['upload_path'] = get_option('upload_path');
	    $probeData['WordPress Settings']['upload_url_path'] = get_option('upload_url_path');

	    $probeData['Image Sizes'] = ilab_get_image_sizes();


	    $probeData['Globals'] = [];
	    $probeData['Globals']['UPLOADS'] = defined('UPLOADS') ? constant('UPLOADS') : null;
	    $probeData['Globals']['DISABLE_WP_CRON'] = defined('DISABLE_WP_CRON') ? constant('DISABLE_WP_CRON') : null;

	    $probeData['Uploads'] = wp_get_upload_dir();
	    
	    $probeData['Media Cloud Settings'] = [];
	    global $wpdb;
	    $settingsResults = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'mcloud%'", ARRAY_A);
	    foreach($settingsResults as $result) {
		    $optName = $result['option_name'];
		    $opVal = $result['option_value'];
		    $probeData['Media Cloud Settings'][$optName] = $opVal;
	    }

	    $theme = wp_get_theme();
	    $probeData['theme'] = [
		    'name' => $theme->display('Name', false),
		    'author' => $theme->display('Author', false),
		    'author_uri' => $theme->display('AuthorURI', false),
		    'version' => $theme->version,
		    'uri' => $theme->display('ThemeURI', false)
	    ];

	    $probeData['Must Use Plugins'] = get_mu_plugins();
	    $probeData['Active Plugins'] = $active;
	    $probeData['In-Active Plugins'] = $inactivePlugins;

	    $foundHooks = discoverHooks(SystemCompatibilityTool::$compatibleHooks);
	    foreach($foundHooks as &$foundHook) {
	    	unset($foundHook['realCallable']);
	    }
	    $probeData['Hooks'] = $foundHooks;

	    $jsonData = json_encode($probeData, JSON_PRETTY_PRINT);
	    $pubKey = new PublicKey(file_get_contents(ILAB_TOOLS_DIR.'/keys/public.key'));

	    wp_send_json([
	    	'status' => 'ok',
		    'download' => [
		    	'file' => 'media-cloud-debug.txt',
			    'type' => 'text/plain',
			    'contents' => EasyRSA::encrypt($jsonData, $pubKey)
		    ]
	    ]);
    }

    public function actionClearLog() {
        $logger = new DatabaseLogger();
        $logger->clearLog();

        wp_send_json(['status' => 'ok']);
    }

    //endregion
}
