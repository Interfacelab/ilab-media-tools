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

namespace MediaCloud\Plugin\Tools\Tasks;

use MediaCloud\Plugin\Tasks\Task;
use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tasks\TaskSettings;
use MediaCloud\Plugin\Tools\Browser\Batch\ImportFromStorageBatchProcess;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\arrayPath;

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
				if ($taskClass::userTask() && $taskClass::showInMenu()) {
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