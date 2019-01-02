<?php
/*
Plugin Name: Media Cloud
Plugin URI: https://github.com/interfacelab/ilab-media-tools
Description: Automatically upload media to Amazon S3 and integrate with Imgix, a real-time image processing CDN.  Boosts site performance and simplifies workflows.
Author: interfacelab
Version: 2.1.30
Author URI: http://interfacelab.io
*/

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

if (!defined('ABSPATH')) { header('Location: /'); die; }

if (!defined('PHP_MAJOR_VERSION') || (PHP_MAJOR_VERSION<5) || ((PHP_MAJOR_VERSION==5) && (PHP_MINOR_VERSION<5))) {
	deactivate_plugins( plugin_basename( __FILE__ ) );

	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'ILAB Media Tools required PHP 5.5 or higher.', 'ilab-media-tools' ); ?></p>
		</div>
		<?php
	} );
	return;
}

// Make sure Offload S3 isn't activated
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('amazon-s3-and-cloudfront/wordpress-s3.php')) {
	deactivate_plugins( plugin_basename( __FILE__ ) );

	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error">
			<p><?php _e( 'Media Cloud cannot be activated the same time as <strong>Offload S3</strong>.  Please deactive one before activating the other.', 'ilab-media-tools' ); ?></p>
		</div>
		<?php
	} );
	return;
}

if (is_plugin_active('wp-stateless/wp-stateless-media.php')) {
	deactivate_plugins( plugin_basename( __FILE__ ) );

	add_action( 'admin_notices', function () {
		?>
        <div class="notice notice-error">
            <p><?php _e( 'Media Cloud cannot be activated the same time as the <strong>WP-Stateless</strong>.  Please deactive one before activating the other.', 'ilab-media-tools' ); ?></p>
        </div>
		<?php
	} );
	return;
}

// Directory defines
define('ILAB_TOOLS_DIR',dirname(__FILE__));
define('ILAB_CONFIG_DIR',ILAB_TOOLS_DIR.'/config');
define('ILAB_HELPERS_DIR',ILAB_TOOLS_DIR.'/helpers');
define('ILAB_CLASSES_DIR',ILAB_TOOLS_DIR.'/classes');
define('ILAB_VENDOR_DIR',ILAB_TOOLS_DIR.'/vendor');
define('ILAB_VIEW_DIR',ILAB_TOOLS_DIR.'/views');
define('ILAB_PLUGIN_NAME', plugin_basename(__FILE__));

// URL defines for CSS/JS
$plug_url = plugin_dir_url( __FILE__ );
define('ILAB_PUB_JS_URL',$plug_url.'public/js');
define('ILAB_PUB_CSS_URL',$plug_url.'public/css');
define('ILAB_PUB_IMG_URL',$plug_url.'public/img');

// Composer
if (file_exists(ILAB_VENDOR_DIR.'/autoload.php')) {
	require_once(ILAB_VENDOR_DIR.'/autoload.php');
}

// Helper functions
require_once('helpers/ilab-media-tool-wordpress-helpers.php');
require_once('helpers/ilab-media-tool-geometry-helpers.php');

// Register storage drivers
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('s3', \ILAB\MediaCloud\Cloud\Storage\Driver\S3\S3Storage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('minio', \ILAB\MediaCloud\Cloud\Storage\Driver\S3\MinioStorage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('wasabi', \ILAB\MediaCloud\Cloud\Storage\Driver\S3\WasabiStorage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('do', \ILAB\MediaCloud\Cloud\Storage\Driver\S3\DigitalOceanStorage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('other-s3', \ILAB\MediaCloud\Cloud\Storage\Driver\S3\OtherS3Storage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('google', \ILAB\MediaCloud\Cloud\Storage\Driver\Google\GoogleStorage::class);
\ILAB\MediaCloud\Cloud\Storage\StorageManager::registerDriver('backblaze', \ILAB\MediaCloud\Cloud\Storage\Driver\Backblaze\BackblazeStorage::class);

// Make sure the NoticeManager is initialized
\ILAB\MediaCloud\Utilities\NoticeManager::instance();

//Register Batch Processes
\ILAB\MediaCloud\Tasks\BatchManager::registerBatchClass('storage', \ILAB\MediaCloud\Tasks\StorageImportProcess::class);
\ILAB\MediaCloud\Tasks\BatchManager::registerBatchClass('rekognizer', \ILAB\MediaCloud\Tasks\RekognizerProcess::class);
\ILAB\MediaCloud\Tasks\BatchManager::registerBatchClass('thumbnails', \ILAB\MediaCloud\Tasks\RegenerateThumbnailsProcess::class);

//Insure batches are run if needed
\ILAB\MediaCloud\Tasks\BatchManager::boot();

register_activation_hook(__FILE__,[ \ILAB\MediaCloud\Tools\ToolsManager::instance(), 'install']);
register_deactivation_hook(__FILE__,[ \ILAB\MediaCloud\Tools\ToolsManager::instance(), 'uninstall']);

if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
	\ILAB\MediaCloud\CLI\Storage\StorageCommands::Register();
	\ILAB\MediaCloud\CLI\Rekognition\RekognitionCommands::Register();
}
