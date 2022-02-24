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

namespace MediaCloud\Plugin\Tasks;

use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Vendor\Monolog\Formatter\LineFormatter;
use MediaCloud\Vendor\Monolog\Handler\StreamHandler;
use MediaCloud\Vendor\Monolog\Logger as MonologLogger;

class TaskReporter implements ITaskReporter {
	/** @var null|Task  */
	private $task = null;

	private $reportCSV = null;
	private $taskFileName = null;
	private $alwaysGenerate = false;
	private $suffix = null;

	protected $headerFields = [];

	/**
	 * TaskReporter constructor.
	 *
	 * @param Task|string $taskOrFileName
	 * @param array $headerFields
	 */
	public function __construct($taskOrFileName, array $headerFields, bool $alwaysGenerate = false, $suffix = null) {
		$this->headerFields = $headerFields;
		$this->suffix = empty($suffix) ? '' : "-{$suffix}";


		if ($taskOrFileName instanceof Task) {
			$this->task = $taskOrFileName;
		} else {
			$this->taskFileName = $taskOrFileName;
		}

		$this->alwaysGenerate = $alwaysGenerate;
	}

	public static function reporterDirectory(?string $file = null) {
		$reportDir = trailingslashit(WP_CONTENT_DIR).'mcloud-reports';
		if (is_multisite() && !is_network_admin()) {
			$reportDir .= '/'.get_current_blog_id();
		}

		if (!file_exists($reportDir)) {
			@mkdir($reportDir, 0755, true);
		}

		if (!file_exists($reportDir)) {
			return null;
		}

		if (!empty($file)) {
			return trailingslashit($reportDir).$file;
		}

		return $reportDir;
	}

	public static function reporterUrl(?string $file = null) {
		if (is_multisite() && !is_network_admin()) {
			$url = content_url('/mcloud-reports/'.get_current_blog_id());
			$url = site_url(parse_url($url, PHP_URL_PATH));
		} else {
			$url = content_url('/mcloud-reports');
		}

		if (!empty($file)) {
			return trailingslashit($url).$file;
		}

		return $url;
	}

	public function open() {
		if (!$this->alwaysGenerate && empty(TaskSettings::instance()->generateReports)) {
			return false;
		}

		if (!empty($this->reportCSV)) {
			return true;
		}

		if (!empty($this->task) && empty($this->task->tuid)) {
			return false;
		}

		$reportDir = static::reporterDirectory();

		if (!empty($reportDir)) {
			if (!empty($this->taskFileName)) {
				if (strpos($this->taskFileName, '/') !== 0) {
					$this->taskFileName = trailingslashit($reportDir).$this->taskFileName;
				}

				$reportFile = $this->taskFileName;
			} else {
				$reportFile = trailingslashit($reportDir)."{$this->task::identifier()}-{$this->task->tuid}{$this->suffix}.csv";
			}

			$exists = file_exists($reportFile);
			$this->reportCSV = fopen($reportFile, 'a');
			if (!$exists) {
				$this->add($this->headerFields);
			}

			if (file_exists($reportFile)) {
				Logger::info("Report file $reportFile exists.", [], __METHOD__, __LINE__);
			} else {
				Logger::error("Report file $reportFile could not be created.", [], __METHOD__, __LINE__);
			}

			$errorLogHandler = new StreamHandler($reportFile.'.log', MonologLogger::INFO);
			$errorLogHandler->setFormatter(new LineFormatter("%channel%.%level_name%: %message% %context% %extra%\n"));
			if (!empty($this->taskFileName)) {
				Logger::instance()->addTemporaryLogger($this->taskFileName, $errorLogHandler);
			} else {
				Logger::instance()->addTemporaryLogger($this->task::identifier(), $errorLogHandler);
			}

			return true;
		} else {
			Logger::error("Could not create reporter directory: $reportDir", [], __METHOD__, __LINE__);
		}

		return false;
	}

	public function add(array $data) {
		if (empty($this->reportCSV)) {
			if (!$this->open()) {
				return;
			}
		}

		fputcsv($this->reportCSV, $data);
	}

	public function close() {
		if (empty($this->reportCSV)) {
			return;
		}

		fclose($this->reportCSV);
		$this->reportCSV = null;

		if (!empty($this->task)) {
			if (!empty($this->taskFileName)) {
				Logger::instance()->removeTemporaryLogger($this->taskFileName);
			} else {
				Logger::instance()->removeTemporaryLogger($this->task::identifier());
			}
		}
	}

	public function headerFields(): array {
		return $this->headerFields;
	}
}