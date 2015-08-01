<?php
/*
Plugin Name: ILAB Media Tools
Plugin URI: http://interfacelab.com/media-tools
Description: Complete media management tools
Author: Jon Gilkison
Version: 0.1
Author URI: http://interfacelab.com
*/

// Directory defines
define('ILAB_TOOLS_DIR',dirname(__FILE__));
define('ILAB_CLASSES_DIR',ILAB_TOOLS_DIR.'/classes');
define('ILAB_VENDOR_DIR',ILAB_TOOLS_DIR.'/vendor');
define('ILAB_VIEW_DIR',ILAB_TOOLS_DIR.'/views');

// URL defines for CSS/JS
$plug_url = plugin_dir_url( __FILE__ );
define('ILAB_PUB_JS_URL',$plug_url.'public/js');
define('ILAB_PUB_CSS_URL',$plug_url.'public/css');

// Helper functions
require_once('helpers/ilab-media-tool-helpers.php');
require_once('helpers/ilab-media-tool-view.php');
require_once('helpers/ilab-media-tool-wordpress-helpers.php');
require_once('helpers/ilab-media-tool-geometry-helpers.php');

require_once('classes/ilab-media-tools-manager.php');
register_activation_hook(__FILE__,[ILabMediaToolsManager::instance(),'install']);
register_deactivation_hook(__FILE__,[ILabMediaToolsManager::instance(),'uninstall']);
