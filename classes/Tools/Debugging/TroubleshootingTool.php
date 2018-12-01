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

use FasterImage\FasterImage;
use ILAB\MediaCloud\Cloud\Storage\StorageException;
use ILAB\MediaCloud\Cloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Imgix\ImgixTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolBase;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogger;
use ILAB\MediaCloud\Utilities\Logging\DatabaseLogTable;
use ILAB\MediaCloud\Utilities\Logging\ErrorCollector;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\View;
use Probe\ProviderFactory;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaDebuggingTool
 *
 * Debugging tool.
 */
class TroubleshootingTool extends ToolBase {
    public function __construct( $toolName, $toolInfo, $toolManager ) {
        parent::__construct( $toolName, $toolInfo, $toolManager );

        if ($this->enabled()) {
            add_action('wp_ajax_ilab_media_cloud_start_troubleshooting', [$this, 'startTroubleshooting']);
        }

    }

    public function registerMenu($top_menu_slug) {
        parent::registerMenu($top_menu_slug);

        if($this->enabled()) {
            add_submenu_page($top_menu_slug, 'Media Cloud Troubleshooting', 'Troubleshooter', 'manage_options', 'media-tools-troubleshooter', [
                $this,
                'renderTroubleshooter'
            ]);
        }
    }

    public function enabled() {
        return true;
    }

    //region Trouble Shooting

    public function renderTroubleshooter() {
        echo View::render_view('debug/trouble-shooter.php', [
            'title' => 'Media Cloud Troubleshooter'
        ]);
    }

    public function startTroubleshooting() {
        if (!is_admin()) {
            json_response(['error' => 'Not an admin.']);
        }

        if (empty($_POST['step'])) {
            json_response(['error' => 'Missing step.']);
        }

        $step = (int)$_POST['step'];

        if (($step < 1) || ($step > 7)) {
            json_response(['error' => 'Invalid step.']);
        }

        if ($step == 1) {
            // Step 1 - Make sure we can connect
            $this->testEnvironment();
        } else if ($step == 2) {
            // Step 1 - Make sure we can connect
            $this->testValidateClient();
        } else if ($step == 3) {
            // Step 2 - Upload a file
            $this->testUploadClient();
        } else if ($step == 4) {
            // Step 3 - File is publicly accessible
            $this->testPubliclyAccessible();
        } else if ($step == 5) {
            // Step 4 - Delete file
            $this->testDeletingFiles();
        } else if ($step == 6) {
            // Step 5 - Verify that the bulk importer process can work
            $this->testBulkImporter();
        } else if ($step == 7) {
            // Step 6 - Test Imgix
            $this->testImgix();
        }
    }

    private function testEnvironment() {
        $warningOnly = false;
        $errors = [];

        $versionSystemParts = explode('+', phpversion());
        $version = $versionSystemParts[0];


        if (!defined('PHP_VERSION_ID') || (PHP_VERSION_ID < 56000)) {
            $warningOnly = false;
            $errors[] = "PHP version is out of date and probably not compatible. The version you are using is no longer updated by PHP maintainers.";
        } else if (PHP_VERSION_ID < 70000) {
            $warningOnly = true;
            $errors[] = "PHP version is compatible but should be upgraded to 7.x as soon as possible.  The version you are using is no longer updated by PHP maintainers.";
        }

        $success = !(count($errors) > 0);
        if (!$success && $warningOnly) {
            $success = 3;
        }

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => $success,
            'title' => 'PHP Version Compatibility',
            'success_message' => "Your version of PHP ($version) is compatible.",
            'error_message' => "Your version of PHP ($version) is outdated.",
            'errors' => $errors
        ]);



        $data = [
            'html' => $html,
            'next' => 2
        ];

        json_response($data);
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
            $data['next'] = 3;
        }

        json_response($data);
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
            $data['next'] = 4;
        }

        json_response($data);
    }

    private function testPubliclyAccessible() {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $errors = [];

        try {
            $url = $storageTool->client()->url('_troubleshooter/sample.txt');

            if (file_get_contents($url) != file_get_contents(ILAB_TOOLS_DIR.'/public/text/sample-upload.txt')) {
                $errors[] = "Upload <a href='$url'>sample file</a> is not publicly viewable.";
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

        $data = [
            'html' => $html,
            'next' => 5
        ];

        json_response($data);
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
            'next' => 6,
        ];

        json_response($data);
    }

    private function testBulkImporter() {
        $errorCollector = new ErrorCollector();
        BatchManager::instance()->testConnectivity($errorCollector);

        $storageSettingsURL = admin_url('admin.php?page=media-tools-s3#ilab-media-s3-batch-settings');

        $html = View::render_view('debug/trouble-shooter-step.php', [
            'success' => !$errorCollector->hasErrors(),
            'title' => 'Test Bulk Importer',
            'success_message' => "Your WordPress server configuration supports background processing.",
            'error_message' => "Your WordPress server configuration does not support background processing.  The bulk importer will not work.  Try changing the <strong>Connection Timeout</strong> setting in <a href='$storageSettingsURL'>Storage Settings</a> to a higher value like 0.1 or 0.5.",
            'errors' => $errorCollector->errors()
        ]);

        $data = [
            'html' => $html
        ];

        $imgixEnabled = apply_filters('ilab_imgix_enabled', false);
        if ($imgixEnabled) {
            $data['next'] = 7;
        }

        json_response($data);
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
            'title' => 'Test Imgix Image',
            'success_message' => "The <a href='$imgixURL'>uploaded file</a> was delivered by Imgix successfully.",
            'error_message' => "The <a href='$imgixURL'>uploaded file</a> was not delivered by Imgix successfully.",
            'errors' => $errors
        ]);

        $data = [
            'html' => $html
        ];

        json_response($data);

    }

    //endregion

}