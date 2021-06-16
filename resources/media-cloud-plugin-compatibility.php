<?php
/*
Plugin Name: Media Cloud Plugin Compatibility
Plugin URI: http://mediacloud.press/
Description: Unloads 3rd party theme and plugins when running background tasks
Author: Jon Gilkison <jon@interfacelab.com>
Version: 1.0
Author URI: http://mediacloud.press/
*/

define('MCLOUD_COMPAT_ENABLED', true);

if (defined('WP_PLUGIN_DIR')) {
	$plugins_dir = trailingslashit(WP_PLUGIN_DIR);
} else if (defined('WPMU_PLUGIN_DIR')) {
	$plugins_dir = trailingslashit(WPMU_PLUGIN_DIR);
} else if (defined('WP_CONTENT_DIR')) {
	$plugins_dir = trailingslashit(WP_CONTENT_DIR).'plugins/';
} else {
	$plugins_dir = plugin_dir_path(__FILE__).'../plugins/';
}

$classInclude = 'classes/Tasks/PluginCompatibility.php';
$className = '\MediaCloud\Plugin\Tasks\PluginCompatibility';

$proInclude = $plugins_dir.'ilab-media-tools-premium/'.$classInclude;
$include = $plugins_dir.'ilab-media-tools/'.$classInclude;

if (file_exists($proInclude)) {
	define('MCLOUD_COMPAT_PLUGIN_DIR',  $plugins_dir.'ilab-media-tools-premium/');
	include_once $proInclude;
} else if (file_exists($include)) {
	define('MCLOUD_COMPAT_PLUGIN_DIR',  $plugins_dir.'ilab-media-tools/');
	include_once $include;
}

if (class_exists($className)) {
	new $className();
}