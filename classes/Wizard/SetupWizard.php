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

use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Plugin\Wizard\Config\Config;

class SetupWizard {
	/** @var Config[]|null  */
	private $registry = null;
	private $defaultConfig = null;

	public function __construct() {
		if (isset($_REQUEST['wizard_ajax'])) {
			$this->setupWizard();
		}
	}

	protected function setupWizard() {
		$config = include ILAB_CONFIG_DIR.'/wizard.config.php';
		if (!is_array($config)) {
			throw new \Exception("Invalid wizard configuration.");
		}

		$this->defaultConfig = new Config($config);

		$registry = include ILAB_CONFIG_DIR.'/wizard-registry.config.php';
		if (!is_array($registry)) {
			throw new \Exception("Invalid wizard registry.");
		}

		$this->registry = [];
		foreach($registry as $key => $class) {
			if (class_exists($class)) {
				$builder = call_user_func([$class, 'configureWizard']);
				$this->registry[$key] = new Config($builder->build());
			}
		}
	}

	/**
	 * Register menu pages related to this tool
	 *
	 * @param $top_menu_slug
	 */
	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false, $tool_menu_slug = null) {
		add_submenu_page('media-cloud', 'Media Cloud Setup Wizard', 'Setup Wizard', 'manage_options', 'media-cloud-wizard', [$this, 'renderSetupWizard']);
	}

	public function renderSetupWizard() {
		if ($this->defaultConfig === null) {
			$this->setupWizard();
		}

		if (isset($_REQUEST['wizard'])) {
			$which = sanitize_text_field($_REQUEST['wizard']);
			if (isset($this->registry[$which])) {
				$config = $this->registry[$which];
			} else {
				$config = $this->defaultConfig;
			}
		} else {
			$config = $this->defaultConfig;
		}

		echo View::render_view( 'wizard.wizard', ['config' => $config]);
	}
}
