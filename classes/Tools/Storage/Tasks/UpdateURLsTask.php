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

namespace MediaCloud\Plugin\Tools\Storage\Tasks;

use Elementor\Plugin;
use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Search\Searcher;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class UpdateURLsTask extends AttachmentTask {
	/** @var Searcher|null */
	private $searcher = null;

	/** @var array  */
	private $sizes = [];

	/** @var int  */
	private $sleep = 200;

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'update-urls';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Update URLs';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.update-urls';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Update URLs';
	}

	/**
	 * Controls if this task stops on an error.
	 *
	 * @return bool
	 */
	public static function stopOnError() {
		return false;
	}

	/**
	 * Bulk action title.
	 *
	 * @return string|null
	 */
	public static function bulkActionTitle() {
		return "Update URLs";
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return true;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/update-urls';
	}

	public static function warnOption() {
		return 'update-urls-task-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}


	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
			'dry-run' => [
				"title" => "Dry Run",
				"description" => "Will simulate the search and replace without updating the database.  It will generate a report that you view in the <a href='".(admin_url('admin.php?page=media-tools-report-viewer'))."' target='_blank'>Report Viewer</a> about what will happen when you run this command normally.",
				"type" => "checkbox",
				"default" => false
			],
			'local' => [
				"title" => "Revert to Local URLs",
				"description" => "Revert to local URLs regardless of current cloud storage settings.",
				"type" => "checkbox",
				"default" => false
			],
			'selected-items' => [
				"title" => "Selected Media",
				"description" => "If you want to process just a small subset of items, click on 'Select Media'",
				"type" => "media-select"
			],
			'imgix' => [
				"title" => "Replace Imgix URLs",
				"description" => "Replace both local and imgix URLs.  Use this if you are going back from using imgix to just normal cloud storage.  To use this switch, you should have an imgix domain and/or signing key saved in imgix settings.  Imgix must not be enabled however.",
				"type" => "checkbox",
				"default" => false
			],
			'imgix-domain' => [
				"title" => "Imgix Domain",
				"description" => "The imgix domain to use, if not using what is saved in the settings.",
				"type" => "text",
				"placeholder" => "https://yourdomain.imgix.net",
				"default" => ''
			],
			'imgix-key' => [
				"title" => "Imgix Signing Key",
				"description" => "The imgix signing key to use, if not using what is saved in the settings.",
				"type" => "text",
				"default" => ''
			],
			'cdn' => [
				"title" => "CDN URL",
				"description" => "If you are rolling back from a CDN, specify the CDN URL here, including the 'https' part.",
				"type" => "url",
				"placeholder" => "https://cdn.yourdomain.com",
				"default" => ''
			],
			'doc-cdn' => [
				"title" => "Document CDN URL",
				"description" => "If you are rolling back from a CDN and used a different CDN for documents, specify the document CDN URL here, including the 'https' part.",
				"type" => "url",
				"placeholder" => "https://docs.yourdomain.com",
				"default" => ''
			],
			'sleep' => [
				"title" => "Performance Mode",
				"description" => "This task will use a lot of CPU on your server.  Choose the appropriate performance mode for your .",
				"type" => "select",
				"options" => [
					'0' => 'Maximum speed, maximum CPU.',
					'200' => 'Default speed, moderate CPU.',
					'500' => 'Slower, less CPU.',
					'1000' => 'Slowest, significantly less CPU.',
				],
				"default" => '200',
			],
		];
	}

	public function reporter() {
		if (empty($this->reportHeaders)) {
			$this->reportHeaders = [
				'Post ID',
				'Old URL',
				'Replacement URL',
				'Changes',
			];
		}

		return parent::reporter();
	}

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		$args['meta_query'] = [
			'relation' => 'OR',
			[
				'key'     => '_wp_attachment_metadata',
				'value'   => '"s3"',
				'compare' => 'LIKE',
				'type'    => 'CHAR',
			],
			[
				'key'     => 'ilab_s3_info',
				'compare' => 'EXISTS',
			],
		];

		return $args;
	}

	public function prepare($options = [], $selectedItems = []) {
		if (!parent::prepare($options, $selectedItems)) {
			return false;
		}

		$this->addItem(['id' => -1]);
		return true;
	}

	//endregion

	//region Execution
	public function willStart() {
		$this->sizes = ilab_get_image_sizes();
		$this->sizes['full'] = [];
		$this->sleep = intval(arrayPath($this->options, 'sleep', 200));
		$cdn = arrayPath($this->options, 'cdn', null);
		$docCdn = arrayPath($this->options, 'doc-cdn', $cdn);
		$imgixDomain = arrayPath($this->options, 'imgix-domain', null);
		$imgixKey = arrayPath($this->options, 'imgix-key', null);
		$this->searcher = new Searcher(!empty(arrayPath($this->options, 'dry-run')), !empty(arrayPath($this->options, 'local')), !empty(arrayPath($this->options, 'imgix')), $imgixDomain, $imgixKey, $cdn, $docCdn);
	}

	/**
	 * Performs the actual task
	 *
	 * @param $item
	 *
	 * @return bool|void
	 * @throws \Exception
	 */
	public function performTask($item) {
		$post_id = $item['id'];

		if ($post_id == -1) {
			if (class_exists('\\Elementor\\Plugin')) {
				Plugin::$instance->files_manager->clear_cache();
			}

			return true;
		}

		if (!postIdExists($post_id)) {
			return true;
		}

		$this->updateCurrentPost($post_id);

		Logger::info("Processing $post_id", [], __METHOD__, __LINE__);

		$totalChanges = $this->searcher->replacePostId($post_id, $this->sizes, $this->reporter(), function() {});

		if ($this->sleep > 0) {
			Logger::info("Sleeping {$this->sleep}.", [], __METHOD__, __LINE__);
			usleep($this->sleep * 1000);
		}

		Logger::info("$totalChanges total changes to $post_id", [], __METHOD__, __LINE__);
		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}

	public function complete() {
		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}
	}

	//endregion
}
