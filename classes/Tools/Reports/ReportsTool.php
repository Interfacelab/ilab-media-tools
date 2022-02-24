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

namespace MediaCloud\Plugin\Tools\Reports;

use MediaCloud\Plugin\Tasks\TaskReporter;
use MediaCloud\Plugin\Tools\Storage\StorageFile;
use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Browser\Tasks\ImportFromStorageTask;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\Carbon\Carbon;
use function MediaCloud\Plugin\Utilities\json_response;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ImageSizeTool
 *
 * Tool for managing image sizes
 */
class ReportsTool extends Tool {

	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );
	}

	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false, $tool_menu_slug = null) {
		parent::registerMenu($top_menu_slug);

		if ($this->enabled()) {
//			ToolsManager::instance()->addMultisiteTool($this);
//
//			if (is_multisite() && !empty($this->settings->multisiteHide)) {
//				return;
//			}

			ToolsManager::instance()->insertToolSeparator();
			$this->options_page = 'media-tools-report-viewer';
			add_submenu_page(!empty($tool_menu_slug) ? $tool_menu_slug : $top_menu_slug, 'Media Cloud Report Viewer', 'Report Viewer', 'manage_options', 'media-tools-report-viewer', [
				$this,
				'renderViewer'
			]);


		}
	}

	public function enabled() {
		return true;
	}

	public function setup() {
		if ($this->enabled()) {
			if (is_admin()) {
				add_action('admin_enqueue_scripts', function(){
					wp_enqueue_script('mcloud-reports-js', ILAB_PUB_JS_URL.'/mcloud-reports.js', null, null, true);
					wp_enqueue_style('mcloud-reports-css', ILAB_PUB_CSS_URL . '/mcloud-reports.css' );
				});
			}
		}
	}

	private function getReports() {
		$reportDir = TaskReporter::reporterDirectory();
		if (!file_exists($reportDir)) {
			return [
				'' => "No Reports"
			];
		}


		$files = list_files($reportDir);
		if (count($files) === 0) {
			return [
				'' => "No Reports"
			];
		}

		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
		}

		$unsorted = [];
		foreach($files as $file) {
			$info = pathinfo($file);
			if (empty($info) || (strtolower($info['extension']) !== 'csv')) {
				continue;
			}

			$name = $info['filename'];
			$nameParts = explode('-', $name);
			array_pop($nameParts);
			$name = ucwords(implode(' ', $nameParts));
			$reportURL = TaskReporter::reporterUrl($info['basename']);

			$time = filectime($file);
			$carbon = Carbon::createFromTimestamp($time, $tz);

			$name .= " &mdash; ".$carbon->toDateTimeString();

			$unsorted[$time] = [
				$reportURL,
				$name
			];
		}

		if (empty($unsorted)) {
			return [
				'' => "No Reports"
			];
		}

		krsort($unsorted, SORT_NUMERIC);

		$results = [
			'' => "Select Report to View"
		];

		foreach($unsorted as $key => $value) {
			$results[$value[0]] = $value[1];
		}

		if (count($results) > 51) {
			return array_slice($results, 0, 51, true);
		}

		return $results;
	}

	public function renderViewer() {
		$allReports = $this->getReports();

		Tracker::trackView('Report Viewer', '/reports');

		echo View::render_view('reports/report-viewer', [
			'title' => 'Report Viewer',
			'allReports' => $allReports,
		]);

	}
}
