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

namespace ILAB\MediaCloud\Tools\Debugging\System;

use FasterImage\FasterImage;
use GuzzleHttp\Psr7\Response;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Debugging\System\Batch\TestBatchProcess;
use ILAB\MediaCloud\Tools\Debugging\System\Batch\TestBatchTool;
use ILAB\MediaCloud\Tools\Imgix\ImgixTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\View;
use Psr\Http\Message\ResponseInterface;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class SystemCompatibilityTool extends Tool {
	const STEP_ENVIRONMENT = 1;
	const STEP_VALIDATE_CLIENT = 2;
	const STEP_TEST_UPLOADS = 3;
	const STEP_TEST_ACL = 4;
	const STEP_TEST_DELETE = 5;
	const STEP_TEST_BACKGROUND_CONNECTIVITY = 6;
	const STEP_TEST_BACKGROUND_TASK = 7;
	const STEP_TEST_IMGIX = 8;

    public function __construct( $toolName, $toolInfo, $toolManager ) {
        parent::__construct( $toolName, $toolInfo, $toolManager );

	    new TestBatchProcess();

        if ($this->enabled()) {
	        add_action('wp_ajax_ilab_media_cloud_start_troubleshooting', [$this, 'startTroubleshooting']);
	        add_action('wp_ajax_ilab_media_cloud_wait_troubleshooting', [$this, 'waitTroubleshooting']);
        }

    }

	public function registerHelpMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
		parent::registerHelpMenu($top_menu_slug);

		if($this->enabled() && (($networkMode && $networkAdminMenu) || (!$networkMode && !$networkAdminMenu))) {
			ToolsManager::instance()->insertHelpToolSeparator();
			add_submenu_page($top_menu_slug, 'Media Cloud System Compatibility Test', 'System Test', 'manage_options', 'media-tools-troubleshooter', [
				$this,
				'renderTroubleshooter'
			]);
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
		    case self::STEP_TEST_BACKGROUND_CONNECTIVITY:
			    return [
				    'index' => $step,
				    'title' => 'Background Connectivity',
				    'status' => 'Running tests ...'
			    ];
			    break;
		    case self::STEP_TEST_BACKGROUND_TASK:
			    return [
				    'index' => $step,
				    'title' => 'Background Tasks',
				    'status' => 'Running tests ...'
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
        } else if ($step == self::STEP_TEST_BACKGROUND_CONNECTIVITY) {
	        // Step 5 - Verify that the bulk importer process can work
	        $this->testBackgroundConnectivity();
        }  else if ($step == self::STEP_TEST_BACKGROUND_TASK) {
	        // Step 5 - Verify that the bulk importer process can work
	        $this->testBackgroundTasks();
        } else if ($step == self::STEP_TEST_IMGIX) {
            // Step 6 - Test Imgix
            $this->testImgix();
        }
    }

    public function waitTroubleshooting() {
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

	    if ($step == self::STEP_TEST_BACKGROUND_TASK) {
		    $this->waitBackgroundTasks();
	    } else {
		    wp_send_json(['error' => 'Missing step.']);
	    }
    }

    private function testEnvironment() {
        $warnings = false;
        $info = [];

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

        $html = View::render_view('debug/system-info', [
	        'title' => 'System Compatibility',
            'description' => 'Various aspects of your system that might have compatibility issues with Media Cloud',
            'warnings' => $warnings,
            'info' => $info
        ]);

        $data = [
            'html' => $html,
            'next' => $this->stepInfo(self::STEP_VALIDATE_CLIENT)
        ];

        wp_send_json($data);
    }

    private function testValidateClient() {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errorCollector = new ErrorCollector();
        try {
            $isValid = $storageTool->client()->validateSettings($errorCollector);
        } catch (\Exception $ex) {
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
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

        try {
            $url = $storageTool->client()->upload('_troubleshooter/sample.txt',ILAB_TOOLS_DIR.'/public/text/sample-upload.txt', StorageSettings::privacy());
        } catch (\Exception $ex) {
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
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

	    try {
		    $result = null;
		    $url = $storageTool->client()->url('_troubleshooter/sample.txt');

		    $result = ilab_file_get_contents($url);

		    if ($result != file_get_contents(ILAB_TOOLS_DIR.'/public/text/sample-upload.txt')) {
			    $errors[] = "Upload <a href='$url'>sample file</a> is not publicly viewable.";
			    $errors[] = $result;
		    }
	    } catch (\Exception $ex) {
		    $errors[] = $ex->getMessage();
	    }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => empty($errors),
            'title' => 'Verify Uploaded File Is Publicly Accessible',
            'success_message' => 'The uploaded file is publicly accessible.',
            'error_message' => 'The uploaded file is not publicly accessible.  If you are using Imgix, this may not be matter if you are using S3 or Google Cloud Storage.  For Digital Ocean and others, this is a big deal.',
            'errors' => $errors
        ]);

	    if (empty($errors)) {
		    $data = [
			    'html' => $html,
			    'next' => $this->stepInfo(self::STEP_TEST_DELETE)
		    ];
	    } else {
		    $data = [
			    'html' => $html,
		    ];
	    }

        wp_send_json($data);
    }

    private function testDeletingFiles() {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

        try {
            $storageTool->client()->delete('_troubleshooter/sample.txt');
        } catch (\Exception $ex) {
            $errors[] = $ex->getMessage();
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

    private function testBackgroundConnectivity($attempts = 0, $mode = 'ssl', $timeoutOverride = false) {
        $errorCollector = new ErrorCollector();

        $result = BatchManager::instance()->testConnectivity($errorCollector, $timeoutOverride);
        if ($result !== true) {
        	foreach($errorCollector->errors() as $error) {
        		if (strpos($error, ' 60: SSL') !== false) {
        			if ($attempts == 0) {
				        Environment::UpdateOption('mcloud-storage-batch-verify-ssl', 'no');
				        $this->testBackgroundConnectivity($attempts + 1, 'ssl');
			        }

        			return;
		        } else if (strpos($error, ' 28: ') !== false) {
        			if (in_array($mode, ['ssl', 'timeout'])) {
        				if ($timeoutOverride === false) {
					        $timeoutOverride = floatval(Environment::Option('mcloud-storage-batch-timeout', null, 0.01));
				        }
				        if ($timeoutOverride >= 5) {
					        Environment::UpdateOption('mcloud-storage-batch-skip-dns', 0.01);
					        Environment::UpdateOption('mcloud-storage-batch-skip-dns-host', 'ip');

					        $this->testBackgroundConnectivity(0, 'dns');
				        } else {
					        $timeoutOverride += 0.1;
					        $this->testBackgroundConnectivity($attempts + 1, 'timeout', $timeoutOverride);
				        }
			        } else if ($mode == 'dns') {
				        if ($timeoutOverride === false) {
					        $timeoutOverride = floatval(Environment::Option('mcloud-storage-batch-timeout', null, 0.01));
				        }

				        if ($timeoutOverride < 5) {
					        $timeoutOverride += 0.1;
					        $this->testBackgroundConnectivity($attempts + 1, 'dns', $timeoutOverride);
				        }
			        }
		        }
	        }
        }

        $batchSettings = admin_url('admin.php?page=media-cloud-settings&tab=batch-processing');
        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => !$errorCollector->hasErrors(),
            'title' => 'Background Connectivity',
            'success_message' => "Your WordPress server configuration supports loopback connections.",
            'error_message' => "Your WordPress server configuration does not support background processing.  The bulk importer will not work.  Try changing the <strong>Connection Timeout</strong> setting in <a href='$batchSettings'>Batch Processing Settings</a> to a higher value like 0.1 or 0.5.  Some plugins also can cause issues.",
            'errors' => $errorCollector->errors(),
	        'hints' => [
		        "Try changing the <strong>Connection Timeout</strong> and <strong>Timeout</strong> settings in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
		        "Some managed host providers have a misconfigured openssl and/or curl installations.  Try disabling <strong>Verify SSL</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
		        "DNS is also sometimes a problem on managed hosting providers.  Try turning off <strong>Skip DNS</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
	        ]
        ]);

        $data = [
            'html' => $html,
	        'next' => $this->stepInfo(self::STEP_TEST_BACKGROUND_TASK)
        ];

        wp_send_json($data);
    }

    private function testBackgroundTasks() {
	    BatchManager::instance()->setShouldCancel(TestBatchTool::BatchIdentifier(), true);
	    call_user_func([TestBatchTool::BatchProcessClassName(), 'cancelAll']);


		$ajaxurl = admin_url('admin-ajax.php');
		$data = [
			'action' => 'mcloud_system_testing_start'
		];

		$result = BatchManager::postRequest($ajaxurl,[
			'cookies' => $_COOKIE,
			'blocking' => true,
			'body' => $data
		],15);

	    if (($result instanceof ResponseInterface) && ($result->getStatusCode() == 200)) {
		    wp_send_json([
			    'title' => 'Background Tasks',
		    	'status' => 'Waiting for background test task to start ...',
			    'wait' => self::STEP_TEST_BACKGROUND_TASK
		    ]);
	    } else {
		    if (is_wp_error($result)) {
			    /** @var \WP_Error $result */
			    $msg = $result->get_error_message();
		    } else if ($result instanceof \Exception) {
			    /** @var \Exception $result */
			    $msg = $result->getMessage();
		    } else if ($result instanceof ResponseInterface) {
				$msg = 'General server error.';
		    }

		    $batchSettings = admin_url('admin.php?page=media-cloud-settings&tab=batch-processing');
		    $html = View::render_view('debug/trouble-shooter-step.php', [
			    'success' => false,
			    'title' => 'Background Tasks',
			    'error_message' => "Your WordPress server configuration does not support background tasks.  The bulk importer will not work.  Try changing the <strong>Connection Timeout</strong> setting in <a href='$batchSettings'>Batch Processing Settings</a> to a higher value like 0.1 or 0.5.  Some plugins also can cause issues.",
			    'errors' => [$msg],
			    'hints' => [
				    "Try changing the <strong>Connection Timeout</strong> and <strong>Timeout</strong> settings in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
				    "Some managed host providers have a misconfigured openssl and/or curl installations.  Try disabling <strong>Verify SSL</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
				    "DNS is also sometimes a problem on managed hosting providers.  Try turning off <strong>Skip DNS</strong> in the <a href='{$batchSettings}' target='_blank'>Batch Processing Settings</a>",
			    ]
		    ]);

		    $data = [
			    'html' => $html
		    ];

		    wp_send_json($data);
	    }
    }

    private function waitBackgroundTasks() {
	    $data = BatchManager::instance()->stats(TestBatchTool::BatchIdentifier());

	    if (!empty($data['running'])) {
	    	$lastUpdate = arrayPath($data, 'lastUpdate', 0);
	    	if (!empty($lastUpdate) && ($lastUpdate > 45)) {
			    $batchSettings = admin_url('admin.php?page=media-cloud-settings&tab=batch-processing');
			    $html = View::render_view('debug/trouble-shooter-step.php', [
				    'success' => false,
				    'title' => 'Background Tasks',
				    'error_message' => "Your WordPress server configuration does not support background tasks.  The bulk importer will not work with your current configuration.  Try changing the <strong>Connection Timeout</strong> setting in <a href='$batchSettings'>Batch Processing Settings</a> to a higher value like 0.1 or 0.5.  Some plugins can also cause issues.",
			    ]);

			    wp_send_json([
			    	'html' => $html
			    ]);
		    }

		    wp_send_json([
			    'status' => "Running test background task ... processing {$data['current']} of {$data['total']} sample items.",
			    'wait' => self::STEP_TEST_BACKGROUND_TASK
		    ]);
	    }

	    $html = View::render_view('debug/trouble-shooter-step.php', [
		    'success' => true,
		    'title' => 'Test Background Tasks',
		    'success_message' => "Your WordPress server configuration supports background tasks.",
	    ]);


	    $data = [
	    	'html' => $html
	    ];

		wp_send_json($data);
    }

    private function testImgix() {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        /** @var ImgixTool $imgixTool */
        $imgixTool = ToolsManager::instance()->tools['imgix'];


        $errors = [];

        try {
            $storageTool->client()->upload('_troubleshooter/sample.jpg',ILAB_TOOLS_DIR.'/public/img/test-image.jpg', StorageSettings::privacy());
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

}