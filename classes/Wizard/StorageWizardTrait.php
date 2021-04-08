<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Wizard;

use MediaCloud\Plugin\Tasks\TaskDatabase;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\ErrorCollector;
use MediaCloud\Plugin\Utilities\Tracker;

trait StorageWizardTrait {
	/**
	 * @param WizardBuilder $builder
	 */
	public static function addTests($builder) {
		$builder->test('Validate Storage Settings', 'Running tests ...', [static::class, 'testStorageSettings']);
		$builder->test('Database Installation', 'Running tests ...', [static::class, 'testDatabaseInstall']);
		$builder->test('Upload Sample File', 'Running tests ...', [static::class, 'testUploadSampleFile']);
		$builder->test('Verify Uploaded File Is Publicly Accessible', 'Running tests ...', [static::class, 'testStorageAcl']);
		$builder->test('Delete Uploaded File', 'Running tests ...', [static::class, 'testDeleteFromStorage']);
	}


	public static function testStorageSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Environment::UpdateOption('mcloud-tool-enabled-storage', false);

		Tracker::trackView("System Test - Validate Client", "/system-test/validate-client");

		$client = new static();
		$errorCollector = new ErrorCollector();
		try {
			$isValid = $client->validateSettings($errorCollector);
			Tracker::trackView("System Test - Validate Client - Success", "/system-test/validate-client/success");
		} catch (\Exception $ex) {
			Tracker::trackView("System Test - Validate Client - Error", "/system-test/validate-client/error");
			$errorCollector->addError("Error validating client settings.  Message: ".$ex->getMessage());
		}

		if ($isValid) {
			wp_send_json([
				'status' => 'success',
				'message' => 'Was able to successfully connect to storage provider.'
			]);
		} else {
			wp_send_json([
				'status' => 'error',
				'message' => 'There was an error or errors trying to connect to the storage provider.',
				'errors' => $errorCollector->errors()
			]);
		}
	}

	public static function testDatabaseInstall() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

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

		if (empty($errors)) {
			wp_send_json([
				'status' => 'success',
				'message' => 'Required database tables are installed.'
			]);
		} else {
			wp_send_json([
				'status' => 'error',
				'message' => 'Missing required database tables.  Please contact your hosting support for adding permissions to your database user to be able to create and modify database tables.  Things like migrating to cloud, import from cloud and other background tasks will not work until this issue is resolved.',
				'errors' => $errors
			]);
		}
	}

	public static function testUploadSampleFile() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Tracker::trackView("System Test - Test Uploads", "/system-test/uploads");

		$client = new static();
		$errors = [];
		try {
			$client->upload('_troubleshooter/sample.txt',ILAB_TOOLS_DIR.'/public/text/sample-upload.txt', StorageToolSettings::privacy());
			Tracker::trackView("System Test - Test Uploads - Success", "/system-test/uploads/success");
		} catch (\Exception $ex) {
			Tracker::trackView("System Test - Test Uploads - Error", "/system-test/uploads/error");
			$errors[] = $ex->getMessage();
		}

		if (empty($errors)) {
			wp_send_json([
				'status' => 'success',
				'message' => 'Was able to successfully upload a sample file.'
			]);
		} else {
			wp_send_json([
				'status' => 'error',
				'message' => 'There was an error trying to upload a sample file.',
				'errors' => $errors
			]);
		}
	}

	public static function testStorageAcl() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Tracker::trackView("System Test - Test Public", "/system-test/public");

		$client = new static();
		$errors = [];
		try {
			$result = null;
			$url = $client->url('_troubleshooter/sample.txt');

			$result = ilab_file_get_contents($url);

			if ($result != file_get_contents(ILAB_TOOLS_DIR.'/public/text/sample-upload.txt')) {
				$errors[] = "Upload <a href='$url'>sample file</a> is not publicly viewable.";
			}
		} catch (\Exception $ex) {
			$errors[] = $ex->getMessage();
		}

		if (empty($errors)) {
			wp_send_json([
				'status' => 'success',
				'message' => 'The uploaded file is publicly accessible.',
			]);
		} else {
			wp_send_json([
				'status' => 'warning',
				'message' => 'The uploaded file is not publicly accessible.  If you are using Imgix, this may not be matter if you are using S3 or Google Cloud Storage.  For Digital Ocean and others, this is a big deal.',
				'errors' => $errors
			]);
		}
	}

	public static function testDeleteFromStorage() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Tracker::trackView("System Test - Deleting", "/system-test/delete");


		$client = new static();
		$errors = [];
		try {
			$client->delete('_troubleshooter/sample.txt');
			Tracker::trackView("System Test - Deleting - Success", "/system-test/delete/success");
		} catch (\Exception $ex) {
			$errors[] = $ex->getMessage();
			Tracker::trackView("System Test - Deleting - Error", "/system-test/delete/error");
		}

		if (empty($errors)) {
			Environment::UpdateOption('mcloud-tool-enabled-storage', true);
			wp_send_json([
				'status' => 'success',
				'message' => 'The uploaded file was successfully deleted.',
			]);
		} else {
			wp_send_json([
				'status' => 'error',
				'message' => 'The uploaded file was successfully could not be deleted.',
				'errors' => $errors
			]);
		}
	}
}
