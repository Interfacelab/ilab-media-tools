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

namespace MediaCloud\Plugin\Tools\Debugging\System;

use MediaCloud\Plugin\Tasks\TaskDatabase;
use MediaCloud\Plugin\Tools\Optimizer\Models\Data\BackgroundData;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tasks\TaskRunner;
use MediaCloud\Plugin\Tools\Imgix\ImgixTool;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\ErrorCollector;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\Carbon\CarbonInterval;
use MediaCloud\Vendor\FasterImage\FasterImage;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\discoverHooks;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class SystemCompatibilityTool extends Tool {
	public static $compatibleHooks = [
		'get_attached_file',
		'image_downsize',
		'wp_get_attachment_url',
		'wp_update_attachment_metadata',
		'wp_get_attachment_image_src',
		'wp_generate_attachment_metadata',
		'wp_update_attachment_metadata',
		'wp_handle_upload_prefilter',
		'wp_handle_upload',
		'wp_calculate_image_srcset',
		'content_save_pre',
		'wp_video_shortcode',
		'the_content',
	];


	/** @var StorageToolSettings|null  */
	private $settings = null;

	const STEP_ENVIRONMENT = 1;
	const STEP_HOOKS = 2;
	const STEP_TEST_DATABASE_INSTALLED = 3;
	const STEP_VALIDATE_CLIENT = 4;
	const STEP_TEST_UPLOADS = 5;
	const STEP_TEST_ACL = 6;
	const STEP_TEST_DELETE = 7;
	const STEP_TEST_BACKGROUND_CONNECTIVITY = 8;
	const STEP_TEST_IMGIX = 9;

    public function __construct( $toolName, $toolInfo, $toolManager ) {
        parent::__construct( $toolName, $toolInfo, $toolManager );

        $this->settings = StorageToolSettings::instance();

        if ($this->settings->useCompatibilityManager) {
	        if (is_admin()) {
		        add_action('wp_ajax_media_cloud_disable_hook', [$this, 'actionDisableHook']);
		        add_action('wp_ajax_media_cloud_enable_hook', [$this, 'actionEnableHook']);
		        add_action('wp_ajax_media_cloud_change_disabled_hook_type', [$this, 'actionChangeHookType']);
	        }

	        add_action('init', function() {
	            $this->applyCompatibility();
	        }, PHP_INT_MAX);
        }

        if (is_admin()) {
	        add_action('wp_ajax_ilab_media_cloud_start_troubleshooting', [$this, 'startTroubleshooting']);
	        TaskRunner::init();
        }
    }

    public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false, $tool_menu_slug = null) {
	    if($this->enabled() && (($networkMode && $networkAdminMenu) || (!$networkMode && !$networkAdminMenu))) {
		    add_submenu_page($top_menu_slug, 'Media Cloud System Compatibility Test', 'System Test', 'manage_options', 'media-tools-troubleshooter', [
			    $this,
			    'renderTroubleshooter'
		    ]);

		    if ($this->settings->useCompatibilityManager) {
			    add_submenu_page($top_menu_slug, 'Media Cloud Compatibility Manager', 'Compat. Manager', 'manage_options', 'media-tools-compatibility', [
				    $this,
				    'renderCompatibilityManager'
			    ]);
		    }
	    }
    }

    public function enabled() {
        return true;
    }

    private function stepInfo($step) {
    	switch($step) {
		    case self::STEP_ENVIRONMENT:
			    return [
				    'index' => $step,
				    'title' => 'System Compatibility'
			    ];
			    break;
		    case self::STEP_HOOKS:
			    return [
				    'index' => $step,
				    'title' => 'Plugin and Theme Compatibility',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_VALIDATE_CLIENT:
			    return [
				    'index' => $step,
				    'title' => 'Validate Storage Settings',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_TEST_UPLOADS:
			    return [
				    'index' => $step,
				    'title' => 'Upload Sample File',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_TEST_ACL:
			    return [
				    'index' => $step,
				    'title' => 'Verify Uploaded File Is Publicly Accessible',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_TEST_DELETE:
			    return [
				    'index' => $step,
				    'title' => 'Delete Uploaded File',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_TEST_DATABASE_INSTALLED:
			    return [
				    'index' => $step,
				    'title' => 'Database Installed',
				    'status' => 'Running tests ... This may take several minutes ...'
			    ];
			    break;
		    case self::STEP_TEST_BACKGROUND_CONNECTIVITY:
			    return [
				    'index' => $step,
				    'title' => 'Background Connectivity',
				    'status' => 'Running tests ... This may take several minutes ...'
			    ];
			    break;
		    case self::STEP_TEST_IMGIX:
			    return [
				    'index' => $step,
				    'title' => 'Verify Imgix Settings',
				    'status' => 'Running tests ...'
			    ];
			    break;
	    }
    }

    //region Trouble Shooting

    public function renderTroubleshooter() {
    	Tracker::trackView("System Test", "/system-test");

        echo View::render_view('debug/trouble-shooter.php', [
            'title' => 'Media Cloud System Compatibility Test'
        ]);
    }

    public function startTroubleshooting() {
        if (!is_admin()) {
            wp_send_json(['error' => 'Not an admin.']);
        }

        if (empty($_POST['step'])) {
            wp_send_json(['error' => 'Missing step.']);
        }

        $step = (int)$_POST['step'];

        if (($step < self::STEP_ENVIRONMENT) || ($step > self::STEP_TEST_IMGIX)) {
            wp_send_json(['error' => 'Invalid step.']);
        }

        if ($step == self::STEP_ENVIRONMENT) {
            // Step 1 - Make sure we can connect
            $this->testEnvironment();
        } else if ($step == self::STEP_HOOKS) {
	        // Step 1 - Make sure we can connect
	        $this->testHooks();
        } else if ($step == self::STEP_VALIDATE_CLIENT) {
            // Step 1 - Make sure we can connect
            $this->testValidateClient();
        } else if ($step == self::STEP_TEST_UPLOADS) {
            // Step 2 - Upload a file
            $this->testUploadClient();
        } else if ($step == self::STEP_TEST_ACL) {
            // Step 3 - File is publicly accessible
            $this->testPubliclyAccessible();
        } else if ($step == self::STEP_TEST_DELETE) {
            // Step 4 - Delete file
            $this->testDeletingFiles();
        } else if ($step == self::STEP_TEST_DATABASE_INSTALLED) {
	        // Step 5 - Verify that the bulk importer process can work
	        $this->testDatabaseInstalled();
        } else if ($step == self::STEP_TEST_BACKGROUND_CONNECTIVITY) {
	        // Step 5 - Verify that the bulk importer process can work
	        $this->testBackgroundConnectivity();
        } else if ($step == self::STEP_TEST_IMGIX) {
            // Step 6 - Test Imgix
            $this->testImgix();
        }
    }

    private function calcTimeDrift() {
    	if (function_exists('socket_create')) {
		    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		    socket_connect($sock, 'time.google.com', 123);

		    socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);

		    /* Send request */
		    $msg = "\010" . str_repeat("\0", 47);
		    if (socket_send($sock, $msg, strlen($msg), 0) !== false) {
			    /* Receive response and close socket */
			    socket_recv($sock, $recv, 48, MSG_WAITALL);
			    socket_close($sock);

			    /* Interpret response */
			    $data = unpack('N12', $recv);
			    $timestamp = sprintf('%u', $data[9]);
		    } else {
			    socket_close($sock);
			    return false;
		    }
	    } else if (function_exists('fsockopen')) {
    		$fp = fsockopen('ntp.pads.ufrj.br', 37, $err, $errstr, 5);
    		if (!empty($fp)) {
    			fputs($fp, "\n");
			    $timercvd = fread($fp, 49);
			    fclose($fp);

			    $timestamp = bin2hex($timercvd);
			    $timestamp = abs(HexDec('7fffffff') - HexDec($timestamp) - HexDec('7fffffff'));

			    if (empty($timestamp)) {
			    	return false;
			    }
		    } else {
    			return false;
		    }
	    } else {
    		return false;
	    }

	    /* NTP is number of seconds since 0000 UT on 1 January 1900
		   Unix time is seconds since 0000 UT on 1 January 1970 */
	    $timestamp -= 2208988800;

	    return abs(time() - $timestamp);
    }

    private function testEnvironment() {
	    Tracker::trackView("System Test - Environment", "/system-test/environment");

	    $errors = false;
        $warnings = false;
        $info = [];

	    $drift = $this->calcTimeDrift();
	    if ($drift === false) {
		    $warnings = true;
		    $info[] =[
			    'type' => 'warning',
			    'message' => "Unable to connect to NTP server to verify server time is correct."
		    ];
	    } else if ($drift > 90) {
		    $errors = true;
		    $warnings = true;
		    $interval = CarbonInterval::make("{$drift}s")->cascade()->forHumans();
		    $info[] =[
			    'type' => 'error',
			    'message' => "Your server's system clock is wrong by over <strong>$interval</strong>.  This will cause errors with cloud storage services.  You may need to contact your hosting provider to correct the situation."
		    ];
	    } else {
		    $info[] =[
			    'type' => 'success',
			    'message' => "Your server's system clock has the correct time."
		    ];
	    }

        $versionSystemParts = explode('+', phpversion());
        $version = $versionSystemParts[0];
        if (PHP_VERSION_ID < 70300) {
            $warnings = true;
            $info[] =[
            	'type' => 'warning',
	            'message' => "Your PHP version ($version) is compatible but should be upgraded to 7.x as soon as possible.  The version you are using is no longer updated by PHP maintainers."
            ];
        } else {
	        $info[] =[
		        'type' => 'success',
		        'message' => "Your version of PHP ($version) is compatible."
	        ];
        }

        if (!is_callable('fastcgi_finish_request')) {
	        $warnings = true;
	        $info[] =[
		        'type' => 'warning',
		        'message' => "You are not using PHP-FPM.  PHP-FPM can seriously improve the speed and responsiveness of your site.  Contact your hosting provider for more information."
	        ];
        } else {
	        $info[] =[
		        'type' => 'success',
		        'message' => "You are using PHP-FPM."
	        ];
        }

	    $maxTime = ini_get('max_execution_time');
	    if (($maxTime > 0) && ($maxTime < 90)) {
		    $warnings = true;
		    $info[] =[
			    'type' => 'warning',
			    'message' => "The <code>max_execution_time</code> is set to a value that might be too low ($maxTime).  You should set it to about 90 seconds.  Additionally, if you are using Nginx or Apache, you may need to set the respective <code>fastcgi_read_timeout</code>, <code>request_terminate_timeout</code> or <code>TimeOut</code> settings too."
		    ];
	    } else {
		    $info[] =[
			    'type' => 'success',
			    'message' => "The <code>max_execution_time</code> is set to a good value ($maxTime)."
		    ];
	    }

	    if (defined('DISABLE_WP_CRON') && !empty(constant('DISABLE_WP_CRON'))) {
		    $warnings = true;
		    $info[] =[
			    'type' => 'warning',
			    'message' => "<code>DISABLE_WP_CRON</code> is enabled, which is often a good thing so long that WordPress's Cron is being triggered by the system's crontab or something like WPEngine's <a href='https://wpengine.com/support/wp-cron-wordpress-scheduling/' target='_blank'>WP Engine Alternate Cron</a> is enabled.  If WordPress's Cron is not being triggered, running background tasks will be slow and get 'stuck' a lot.  Read more about the best way of <a href='https://kinsta.com/knowledgebase/disable-wp-cron/' target='_blank'>setting up WordPress Cron</a>."
		    ];
	    } else {
		    $warnings = true;
		    $info[] =[
			    'type' => 'warning',
			    'message' => "WordPress Cron is enabled.  For better performance, consider disabling WordPress Cron and running it from the system crontab.  Read more about the best way of <a href='https://kinsta.com/knowledgebase/disable-wp-cron/' target='_blank'>setting up WordPress Cron</a>."
		    ];
	    }

	    if (function_exists('xdebug_is_debugger_active') && xdebug_is_debugger_active()) {
		    $warnings = true;
		    $info[] =[
			    'type' => 'warning',
			    'message' => "XDebug is currently active, which may inhibit background processing from running properly."
		    ];
	    }

        $html = View::render_view('debug/system-info', [
	        'title' => 'System Compatibility',
            'description' => 'Various aspects of your system that might have compatibility issues with Media Cloud',
	        'errors' => $errors,
	        'warnings' => $warnings,
            'info' => $info
        ]);

        $data = [
            'html' => $html,
	        'next' => $this->stepInfo(self::STEP_HOOKS)
        ];

        if (!$errors) {
	        Tracker::trackView("System Test - Environment - Success", "/system-test/environment/success");
        } else {
	        Tracker::trackView("System Test - Environment - Error", "/system-test/environment/error");
        }

        wp_send_json($data);
    }

    private function testHooks() {
	    $compatible = [
		    'woocommerce',
		    'elementor',
		    'advanced-custom-fields-pro',
		    'ewww-image-optimizer',
		    'imagify',
		    'kraken-image-optimizer',
		    'shortpixel-image-optimizer',
		    'wp-smushit',
	    ];

	    $foundHooks = discoverHooks(static::$compatibleHooks);
	    $issues = [];
	    foreach($foundHooks as $hook) {
	    	if (in_array($hook['plugin'], $compatible)) {
	    		continue;
		    }

		    $issue = "The {$hook['type']} <strong>{$hook['name']}</strong> uses the hook <code>{$hook['hook']}</code> which may interfere with Media Cloud.";
		    if (!in_array($issue, $issues)) {
		    	$issues[] = $issue;
		    }
	    }

	    $html = View::render_view('debug/trouble-shooter-step.php', [
		    'success' => (count($issues) === 0) ? true : 'maybe',
		    'title' => 'Plugin and Theme Compatibility',
		    'success_message' => 'There appears to be no issues with installed plugins or your currently active theme.',
		    'error_message' => 'There may be issues with plugins and/or your currently active theme.  <strong>Important</strong>: Just because a plugin or theme appears in the list below DOES NOT mean it\'s incompatible with Media Cloud, it simply means there is the possibility of conflict.',
		    'errors' => $issues
	    ]);

	    $data = [
		    'html' => $html,
		    'next' => $this->stepInfo(self::STEP_TEST_DATABASE_INSTALLED)
	    ];

	    wp_send_json($data);
    }

    private function testValidateClient() {
	    Tracker::trackView("System Test - Validate Client", "/system-test/validate-client");

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errorCollector = new ErrorCollector();
        try {
            $isValid = $storageTool->client()->validateSettings($errorCollector);
	        Tracker::trackView("System Test - Validate Client - Success", "/system-test/validate-client/success");
        } catch (\Exception $ex) {
	        Tracker::trackView("System Test - Validate Client - Error", "/system-test/validate-client/error");
            $errorCollector->addError("Error validating client settings.  Message: ".$ex->getMessage());
        }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => $isValid,
            'title' => 'Validate Storage Settings',
            'success_message' => 'Was able to successfully connect to storage provider.',
            'error_message' => 'There was an error or errors trying to connect to the storage provider.',
            'errors' => $errorCollector->errors()
        ]);

        $data = [
            'html' => $html
        ];

        if ($isValid) {
            $data['next'] = $this->stepInfo(self::STEP_TEST_UPLOADS);
        }

        wp_send_json($data);
    }

    private function testUploadClient() {
	    Tracker::trackView("System Test - Test Uploads", "/system-test/uploads");

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

        try {
            $url = $storageTool->client()->upload('_troubleshooter/sample.txt',ILAB_TOOLS_DIR.'/public/text/sample-upload.txt', StorageToolSettings::privacy());
	        Tracker::trackView("System Test - Test Uploads - Success", "/system-test/uploads/success");
        } catch (\Exception $ex) {
	        Tracker::trackView("System Test - Test Uploads - Error", "/system-test/uploads/error");
            $errors[] = $ex->getMessage();
        }


        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => empty($errors),
            'title' => 'Upload Sample File',
            'success_message' => 'Was able to successfully upload a sample file.',
            'error_message' => 'There was an error trying to upload a sample file.',
            'errors' => $errors
        ]);

        $data = [
            'html' => $html
        ];

        if (empty($errors)) {
            $data['next'] = $this->stepInfo(self::STEP_TEST_ACL);
        }

        wp_send_json($data);
    }

    private function testPubliclyAccessible() {
	    Tracker::trackView("System Test - Test Public", "/system-test/public");

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

	    try {
		    $result = null;
		    $url = $storageTool->client()->url('_troubleshooter/sample.txt');

		    $result = ilab_file_get_contents($url);

		    if ($result != file_get_contents(ILAB_TOOLS_DIR.'/public/text/sample-upload.txt')) {
			    $errors[] = "Upload <a href='$url'>sample file</a> is not publicly viewable.";
		    }
	    } catch (\Exception $ex) {
		    $errors[] = $ex->getMessage();
	    }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => empty($errors),
            'title' => 'Verify Uploaded File Is Publicly Accessible',
            'success_message' => 'The uploaded file is publicly accessible.',
            'error_message' => 'The uploaded file is not publicly accessible.  If you are using Imgix, this may not be matter if you are using S3 or Google Cloud Storage.  For Digital Ocean and others, this is a big deal.  For Backlaze S3, this is because your bucket is set to private and if that was intentional you can ignore this message.',
            'errors' => $errors
        ]);

	    if (empty($errors)) {
		    Tracker::trackView("System Test - Test Public - Success", "/system-test/public/success");
		    $data = [
			    'html' => $html,
			    'next' => $this->stepInfo(self::STEP_TEST_DELETE)
		    ];
	    } else {
		    Tracker::trackView("System Test - Test Public - Error", "/system-test/public/error");
		    $data = [
			    'html' => $html,
		    ];

		    if (StorageToolSettings::driver() == 'backblaze-s3') {
		    	$data['next'] = $this->stepInfo(self::STEP_TEST_DELETE);
		    }
	    }

        wp_send_json($data);
    }

    private function testDeletingFiles() {
	    Tracker::trackView("System Test - Deleting", "/system-test/delete");

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

        try {
            $storageTool->client()->delete('_troubleshooter/sample.txt');
	        Tracker::trackView("System Test - Deleting - Success", "/system-test/delete/success");
        } catch (\Exception $ex) {
            $errors[] = $ex->getMessage();
	        Tracker::trackView("System Test - Deleting - Error", "/system-test/delete/error");
        }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => empty($errors),
            'title' => 'Delete Uploaded File',
            'success_message' => 'The uploaded file was successfully deleted.',
            'error_message' => 'The uploaded file was successfully could not be deleted.',
            'errors' => $errors
        ]);

	    $data = [
		    'html' => $html,
	    ];

	    $imgixEnabled = apply_filters('media-cloud/imgix/enabled', false);
	    if ($imgixEnabled) {
		    $data['next'] = $this->stepInfo(self::STEP_TEST_IMGIX);
	    } else {
	    	$data['next'] = $this->stepInfo(self::STEP_TEST_BACKGROUND_CONNECTIVITY);
	    }

        wp_send_json($data);
    }

    private function testDatabaseInstalled() {
	    Tracker::trackView("System Test - Database", "/system-test/database");


	    TaskDatabase::init(false);
	    $taskTableExists = TaskDatabase::taskTableExists();
	    if (!$taskTableExists) {
	    	TaskDatabase::init(true);
	    }

	    $taskTableExists = TaskDatabase::taskTableExists();
	    $taskDataTableExists = TaskDatabase::taskDataTableExists();
	    $taskScheduleTableExists = TaskDatabase::taskScheduleTableExists();
	    $taskTokenTableExists = TaskDatabase::taskTokenTableExists();

	    $errors = [];

	    if (!$taskTableExists) {
	    	$errors[] = 'Tasks table is not installed.';
	    }

	    if (!$taskDataTableExists) {
		    $errors[] = 'Tasks data table is not installed.';
	    }

	    if (!$taskScheduleTableExists) {
		    $errors[] = 'Tasks schedule table is not installed.';
	    }

	    if (!$taskTokenTableExists) {
		    $errors[] = 'Tasks token table is not installed.';
	    }

	    $html = View::render_view('debug/trouble-shooter-step.php', [
		    'success' => empty($errors),
		    'title' => 'Database Installed',
		    'success_message' => 'The required database tables are installed.',
		    'error_message' => 'Missing required database tables.  Please contact your hosting support for adding permissions to your database user to be able to create and modify database tables.  Things like migrating to cloud, import from cloud and other background tasks will not work until this issue is resolved.',
		    'errors' => $errors
	    ]);

	    $data = [
		    'html' => $html,
	    ];

	    if (count($errors) > 0) {
		    Tracker::trackView("System Test - Database - Error", "/system-test/database/error");
	    } else {
		    Tracker::trackView("System Test - Database - Success", "/system-test/database/success");
		    $data['next'] = $this->stepInfo(self::STEP_VALIDATE_CLIENT);
	    }

	    wp_send_json($data);
    }

    private function testBackgroundConnectivity($attempts = 0, $mode = 'ssl', $timeoutOverride = false) {
	    Tracker::trackView("System Test - Background Connectivity", "/system-test/background-connection");

	    $result = TaskRunner::testConnectivity();
        if (is_array($result)) {
	        Tracker::trackView("System Test - Background Connectivity - Error", "/system-test/background-connection/error");
        } else {
	        Tracker::trackView("System Test - Background Connectivity - Success", "/system-test/background-connection/success");
        }

        $batchSettings = admin_url('admin.php?page=media-cloud-settings&tab=batch-processing');
        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => !is_array($result),
            'title' => 'Background Connectivity',
            'success_message' => "Your WordPress server configuration supports loopback connections.",
            'error_message' => "Your WordPress server configuration does not support background processing.  The bulk importer will not work.  Try changing the <strong>Connection Timeout</strong> setting in <a href='$batchSettings'>Batch Processing Settings</a> to a higher value like 0.1 or 0.5.  Some plugins also can cause issues.",
            'errors' => (is_array($result)) ? $result : [],
	        'hints' => [
		        "Try changing the HTTP Client to Guzzle.",
		        "Try changing the <strong>Connection Timeout</strong> and <strong>Timeout</strong> settings in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
		        "Some managed host providers have a misconfigured openssl and/or curl installations.  Try disabling <strong>Verify SSL</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
		        "DNS is also sometimes a problem on managed hosting providers.  Try turning off <strong>Skip DNS</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
	        ]
        ]);

	    if (!is_array($result)) {
		    Tracker::trackView("System Test - Environment - Success", "/system-test/background-connection/success");
	    } else {
		    Tracker::trackView("System Test - Environment - Error", "/system-test/background-connection/error");
	    }

        wp_send_json([
	        'html' => $html
        ]);
    }

    private function testImgix() {
	    Tracker::trackView("System Test - Imgix", "/system-test/imgix");

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        /** @var ImgixTool $imgixTool */
        $imgixTool = ToolsManager::instance()->tools['imgix'];


        $errors = [];

        try {
        	Logger::info("Imgix test- uploading sample image with privacy:".StorageToolSettings::privacy(), [], __METHOD__, __LINE__);
            $storageTool->client()->upload('_troubleshooter/sample.jpg',ILAB_TOOLS_DIR.'/public/img/test-image.jpg', StorageToolSettings::privacy());

            $url = $storageTool->client()->url('_troubleshooter/sample.jpg');
	        $result = ilab_file_get_contents($url);

	        if ($result != file_get_contents(ILAB_TOOLS_DIR.'/public/img/test-image.jpg')) {
		        $errors[] = "Uploaded <a href='$url'>sample file</a> is not publicly viewable.";
	        }

            $imgixURL = $imgixTool->urlForKey('_troubleshooter/sample.jpg');

            $faster = new FasterImage();
            $result = $faster->batch([$imgixURL]);
            $result = $result[$imgixURL];
            $size = $result['size'];

            if (empty($size) || ($size == 'failed')) {
                $errors[] = "Unable to access <a href='$imgixURL'>Imgix sample image</a>.  Possibly wrong signing key or Imgix can't access the master image.";
            } else if (count($size) > 1) {
                list($w, $h) = $size;

                if (($w != 320) && ($h != 320)) {
                    $errors[] = "Invalid image size for sample image.  $w x $h (should be 320 x 320)";
                }
            }
        } catch (\Exception $ex) {
            $errors[] = $ex->getMessage();
        }

        if (empty($errors)) {
	        Tracker::trackView("System Test - Imgix - Success", "/system-test/imgix/success");
        } else {
	        Tracker::trackView("System Test - Imgix - Error", "/system-test/imgix/error");
        }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => empty($errors),
            'title' => 'Verify Imgix Settings',
            'success_message' => "The <a href='$imgixURL'>uploaded file</a> was delivered by Imgix successfully.",
            'error_message' => "The <a href='$imgixURL'>uploaded file</a> was not delivered by Imgix successfully.",
            'errors' => $errors
        ]);

        $data = [
            'html' => $html,
	        'next' => $this->stepInfo(self::STEP_TEST_BACKGROUND_CONNECTIVITY)
        ];

        wp_send_json($data);

    }

    //endregion

	//region Compatibility Manager
	public function renderCompatibilityManager() {
		$disabledHookOptions = Environment::Option('media-cloud-disabled-hooks', null, []);
		$foundHooks = discoverHooks(static::$compatibleHooks);

		$disabledHookHashes = array_keys($disabledHookOptions);

		$disabledHooks = array_filter($foundHooks, function($hook) use ($disabledHookHashes) {
			return in_array($hook['hash'], $disabledHookHashes);
		});

		foreach($disabledHooks as &$disabledHook) {
			$disabledHook['disableType'] = $disabledHookOptions[$disabledHook['hash']];
		}

		$availableHooks = array_filter($foundHooks, function($hook) use ($disabledHookHashes) {
			return !in_array($hook['hash'], $disabledHookHashes);
		});

		Tracker::trackView("Compatibility Manager", "/compatibility-manager");

		echo View::render_view('base.compatibility-manager', [
			'disabledHooks' => $disabledHooks,
			'availableHooks' => $availableHooks,
		]);
	}

	private function applyCompatibility() {
    	if (is_admin() && (arrayPath($_REQUEST, 'page') === 'media-tools-compatibility')) {
    		return;
	    }

    	if (!empty(apply_filters('media-cloud/compat/disable-apply', false))) {
    		return;
	    }

		$disabledHookOptions = Environment::Option('media-cloud-disabled-hooks', null, []);
		$foundHooks = discoverHooks(static::$compatibleHooks);
		$disabledHookHashes = array_keys($disabledHookOptions);
		$disabledHooks = array_filter($foundHooks, function($hook) use ($disabledHookHashes) {
			return in_array($hook['hash'], $disabledHookHashes);
		});

		foreach($disabledHooks as $disabledHook) {
			$type = $disabledHookOptions[$disabledHook['hash']];
			if (($type == 'frontend') && is_admin()) {
				continue;
			} else if (($type == 'backend') && !is_admin()) {
				continue;
			}

			remove_filter($disabledHook['hook'], $disabledHook['realCallable'], $disabledHook['priority']);
		}
	}

	public function actionDisableHook() {
		check_ajax_referer('media_cloud_disable_hook', 'nonce');
		if (!current_user_can('manage_options')) {
			return;
		}

		$hash = arrayPath($_REQUEST, 'hash');
		$type = arrayPath($_REQUEST, 'type');
		if (anyEmpty($hash, $type)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing argument'], 400);
		}

		$disabledHookOptions = Environment::Option('media-cloud-disabled-hooks', null, []);
		$disabledHookOptions[$hash] = $type;
		Environment::UpdateOption('media-cloud-disabled-hooks', $disabledHookOptions);
	}

	public function actionEnableHook() {
		check_ajax_referer('media_cloud_enable_hook', 'nonce');
		if (!current_user_can('manage_options')) {
			return;
		}

		$hash = arrayPath($_REQUEST, 'hash');
		$type = arrayPath($_REQUEST, 'type');
		if (anyEmpty($hash, $type)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing argument'], 400);
		}

		$disabledHookOptions = Environment::Option('media-cloud-disabled-hooks', null, []);
		unset($disabledHookOptions[$hash]);
		Environment::UpdateOption('media-cloud-disabled-hooks', $disabledHookOptions);
	}

	public function actionChangeHookType() {
		check_ajax_referer('media_cloud_enable_hook', 'nonce');
		if (!current_user_can('manage_options')) {
			return;
		}

		$hash = arrayPath($_REQUEST, 'hash');
		$type = arrayPath($_REQUEST, 'type');
		if (anyEmpty($hash, $type)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing argument'], 400);
		}

		$disabledHookOptions = Environment::Option('media-cloud-disabled-hooks', null, []);

		if (isset($disabledHookOptions[$hash])) {
			$disabledHookOptions[$hash] = $type;
			Environment::UpdateOption('media-cloud-disabled-hooks', $disabledHookOptions);
		}

		wp_send_json(['status' => 'ok'], 200);
	}
	//endregion
}
