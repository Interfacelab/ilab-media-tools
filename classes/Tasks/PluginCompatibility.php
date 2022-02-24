<?php


namespace MediaCloud\Plugin\Tasks;


use MediaCloud\Plugin\Utilities\Environment;
use function MediaCloud\Plugin\Utilities\arrayPath;

class PluginCompatibility {
	public function __construct() {
		if (!defined('MCLOUD_COMPAT_PLUGIN_DIR') || !defined('DOING_AJAX')) {
			return;
		}

		if (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], ['mcloud_run_task', 'mcloud_start_task'])) {
			return;
		}

		if (empty($this->option('mcloud-tasks-disable-plugins', null, false))) {
			return;
		}

		add_action( 'admin_init', [$this, 'removeTGMPAFilter'], 1 );
		add_filter( 'option_active_plugins', [$this, 'disablePlugins']);
		add_filter( 'site_option_active_sitewide_plugins', [$this, 'disablePlugins'] );
		add_filter( 'stylesheet_directory', [$this, 'disableTheme']);
		add_filter( 'template_directory', [$this, 'disableTheme']);
	}

	private function option($optionName = null, $envVariableName = null, $default = false) {
		if (empty($optionName) && empty($envVariableName)) {
			return $default;
		}

		if (empty($optionName)) {
			if (!is_array($envVariableName)) {
				$envVariableName = [$envVariableName];
			}
		} else {
			$optionEnvName = str_replace('-','_', strtoupper($optionName));
			if (is_array($envVariableName)) {
				$envVariableName = array_merge([$optionEnvName], $envVariableName);
			} else if (!empty($envVariableName)) {
				$envVariableName = [$optionEnvName, $envVariableName];
			} else {
				$envVariableName = [$optionEnvName];
			}
		}

		foreach($envVariableName as $envVariable) {
			if (defined($envVariable)) {
				return constant($envVariable);
			}

			$envval = getenv($envVariable);
			if ($envval !== false) {
				return $envval;
			}
		}

		if (empty($optionName)) {
			return $default;
		}

		if (!empty(get_site_option('mcloud-network-mode'))) {
			$val = get_site_option($optionName, $default);
		} else {
			$val = get_option($optionName, $default);
		}

		return $val;
	}

	public function removeTGMPAFilter() {
		global $wp_filter;
		$admin_init_functions = $wp_filter['admin_init'];
		foreach ($admin_init_functions as $priority => $functions) {
			foreach ( $functions as $key => $function ) {
				if ((strpos($key, 'force_activation') !== false) || (strpos($key, 'activate_if_not') !== false)) {
					unset( $wp_filter['admin_init']->callbacks[ $priority ][ $key ] );
					return;
				}
			}
		}
	}

	public function disablePlugins($plugins) {
		if (!is_array($plugins) || empty($plugins)) {
			return $plugins;
		}

		return [
			'ilab-media-tools/ilab-media-tools.php',
			'ilab-media-tools-premium/ilab-media-tools.php',
		];
	}

	public function disableTheme($stylesheetDir) {
		$theme_root = MCLOUD_COMPAT_PLUGIN_DIR."resources/theme";
		return $theme_root;
	}
}