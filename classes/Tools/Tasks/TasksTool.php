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

namespace ILAB\MediaCloud\Tools\Tasks;

use GuzzleHttp\Client;
use ILAB\MediaCloud\Storage\StorageFile;
use ILAB\MediaCloud\Tasks\Task;
use ILAB\MediaCloud\Tasks\TaskManager;
use ILAB\MediaCloud\Tasks\TaskSettings;
use ILAB\MediaCloud\Tools\Browser\Batch\ImportFromStorageBatchProcess;
use ILAB\MediaCloud\Tools\Browser\Tasks\ImportFromStorageTask;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\Tracker;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\json_response;
use function ILAB\MediaCloud\Utilities\vomit;
use Illuminate\Support\Facades\Storage;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 */
class TasksTool extends Tool {
	/** @var TaskSettings|null  */
	protected $settings = null;

	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

		$this->settings = TaskSettings::instance();
	}

	public function setup() {
		parent::setup();

		if (is_admin()) {
			if ($this->settings->heartbeatEnabled) {
				add_action('admin_enqueue_scripts', function() {
					$script = View::render_view('base.heartbeat', [ 'heartbeatFrequency' => (int)$this->settings->heartbeatFrequency * 1000]);
					wp_register_script('task-manager-heartbeat', '', ['jquery']);
					wp_enqueue_script('task-manager-heartbeat');
					wp_add_inline_script('task-manager-heartbeat', $script);
				});
			}
		}
	}

	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
		parent::registerMenu($top_menu_slug);

	}

	public function registerBatchToolMenu($tool_menu_slug, $networkMode = false, $networkAdminMenu = false) {
		ToolsManager::instance()->insertBatchToolSeparator();

		if (!is_multisite() || is_network_admin() || empty(Environment::Option('media-cloud-task-manager-hide', null, true))) {
			ToolsManager::instance()->addMultisiteTool($this);

			$this->options_page = 'media-cloud-task-manager';
			add_submenu_page($tool_menu_slug, 'Task Manager', 'Task Manager', 'manage_options', 'media-cloud-task-manager', [$this, 'renderTaskManager']);
		}


		if($networkMode && $networkAdminMenu) {
			return;
		}

		$hasBatchTool = false;
		foreach(TaskManager::registeredTasks() as $identifier => $taskClass) {
			if ($taskClass::userTask()) {
				$hasBatchTool = true;
				break;
			}
		}

		if($hasBatchTool) {
			ToolsManager::instance()->insertBatchToolSeparator();

			foreach(TaskManager::registeredTasks() as $identifier => $taskClass) {
				if ($taskClass::userTask()) {
					add_submenu_page($tool_menu_slug, $taskClass::title(), $taskClass::menuTitle(), 'manage_options', 'mcloud-task-'.$identifier, [$this, 'renderBatchTool']);
				}
			}
		}
	}

	public function enabled() {
		return true;
	}



	//region Views

	/**
	 * Render the manager page
	 */
	public function renderTaskManager() {
		echo View::render_view('tasks.task-manager', ['title' => 'Task Manager', 'manager' => TaskManager::instance()]);
	}

	/**
	 * Render the batch page
	 *
	 * @throws \Exception
	 */
	public function renderBatchTool() {
		$identifier = str_replace('mcloud-task-', '', arrayPath($_REQUEST, 'page', null));
		if (empty($identifier)) {
			wp_die("Not sure what happened here.");
		}

		$taskClass = TaskManager::registeredTasks()[$identifier];
		$runningTask = Task::currentRunningTask($identifier);

		echo View::render_view('tasks.batch', ['title' => $taskClass::title(), 'task' => $runningTask, 'taskClass' => $taskClass, 'manager' => TaskManager::instance(), 'warning' => null]);
	}

	//endregion

}