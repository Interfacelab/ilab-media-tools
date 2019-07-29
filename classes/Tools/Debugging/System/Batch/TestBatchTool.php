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

namespace ILAB\MediaCloud\Tools\Debugging\System\Batch;

use ILAB\MediaCloud\Tools\BatchTool;
use function ILAB\MediaCloud\Utilities\json_response;

class TestBatchTool extends BatchTool {
	//region Properties
	/**
	 * Name/ID of the batch
	 * @return string
	 */
	public static function BatchIdentifier() {
		return 'system-testing';
	}

	/**
	 * Title of the batch
	 * @return string
	 */
	public function title() {
		return null;
	}

	public function menuTitle() {
		return null;
	}

	/**
	 * The prefix to use for action names
	 * @return string
	 */
	public function batchPrefix() {
		return 'mcloud_system_testing';
	}

	/**
	 * Fully qualified class name for the BatchProcess class
	 * @return string
	 */
	public static function BatchProcessClassName() {
		return TestBatchProcess::class;
	}

	/**
	 * The view to render for instructions
	 * @return string
	 */
	public function instructionView() {
		return null;
	}

	/**
	 * The menu slug for the tool
	 * @return string
	 */
	function menuSlug() {
		return null;
	}

	public function enabled() {
		return true;
	}
	//endregion

	//region Actions
	public function manualAction() {
		json_response(["status" => 'ok']);
	}
	//endregion


	/**
	 * Gets the post data to process for this batch.  Data is paged to minimize memory usage.
	 * @param $page
	 * @param bool $forceImages
	 * @param bool $allInfo
	 *
	 * @return array
	 */
	protected function getImportBatch($page, $forceImages = false, $allInfo = false) {
		return [
			'total' => 15,
			'posts' => [
				1, 1, 1, 1, 1,
				1, 1, 1, 1, 1,
				1, 1, 1, 1, 1,
			],
			'options' => []
		];
	}
}