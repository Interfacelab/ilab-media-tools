<?php

/*
Plugin Name: Media Cloud
Plugin URI: https://github.com/interfacelab/ilab-media-tools
Description: Automatically upload media to Amazon S3 and integrate with Imgix, a real-time image processing CDN.  Boosts site performance and simplifies workflows.
Author: interfacelab
Version: 4.4.0
Requires PHP: 7.4
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

if ( !defined( 'ABSPATH' ) ) {
    header( 'Location: /' );
    die;
}


if ( function_exists( 'media_cloud_licensing' ) ) {
    media_cloud_licensing()->set_basename( true, __FILE__ );
    return;
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( !defined( 'PHP_MAJOR_VERSION' ) || PHP_MAJOR_VERSION < 7 || PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION < 1 ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    add_action( 'admin_notices', function () {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php 
        _e( 'Media Cloud requires PHP 7.1 or higher.', 'ilab-media-tools' );
        ?></p>
        </div>
		<?php 
    } );
    return;
}

// Make sure Offload S3 isn't activated
include_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'amazon-s3-and-cloudfront/wordpress-s3.php' ) ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    add_action( 'admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php 
        _e( 'Media Cloud cannot be activated the same time as <strong>Offload S3</strong>.  Please deactive one before activating the other.', 'ilab-media-tools' );
        ?></p>
        </div>
		<?php 
    } );
    return;
}


if ( is_plugin_active( 'wp-stateless/wp-stateless-media.php' ) ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    add_action( 'admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php 
        _e( 'Media Cloud cannot be activated the same time as the <strong>WP-Stateless</strong>.  Please deactive one before activating the other.', 'ilab-media-tools' );
        ?></p>
        </div>
		<?php 
    } );
    return;
}


if ( defined( 'MEDIA_CLOUD_VERSION' ) ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    add_action( 'admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php 
        _e( 'There is another version of Media Cloud installed.  Please deactivate it before activating this one.', 'ilab-media-tools' );
        ?></p>
        </div>
		<?php 
    } );
    return;
}

// Version Defines
define( 'MEDIA_CLOUD_VERSION', '4.4.0' );
define( 'MEDIA_CLOUD_INFO_VERSION', '4.0.2' );
define( 'MCLOUD_IS_BETA', false );
// Directory defines
define( 'ILAB_TOOLS_DIR', dirname( __FILE__ ) );
define( 'ILAB_CONFIG_DIR', ILAB_TOOLS_DIR . '/config' );
define( 'ILAB_HELPERS_DIR', ILAB_TOOLS_DIR . '/helpers' );
define( 'ILAB_CLASSES_DIR', ILAB_TOOLS_DIR . '/classes' );
define( 'ILAB_VENDOR_DIR', ILAB_TOOLS_DIR . '/vendor' );
define( 'ILAB_LIB_DIR', ILAB_TOOLS_DIR . '/lib' );
define( 'ILAB_VIEW_DIR', ILAB_TOOLS_DIR . '/views' );
define( 'ILAB_PLUGIN_NAME', plugin_basename( __FILE__ ) );
define( 'ILAB_PUB_IMG_DIR', ILAB_TOOLS_DIR . '/public/img' );
define( 'ILAB_BLOCKS_DIR', ILAB_TOOLS_DIR . '/public/blocks' );
// URL defines for CSS/JS
$plug_url = plugin_dir_url( __FILE__ );
define( 'ILAB_TOOLS_URL', $plug_url );
define( 'ILAB_PUB_URL', $plug_url . 'public' );
define( 'ILAB_PUB_JS_URL', $plug_url . 'public/js' );
define( 'ILAB_PUB_CSS_URL', $plug_url . 'public/css' );
define( 'ILAB_PUB_IMG_URL', $plug_url . 'public/img' );
define( 'ILAB_BLOCKS_URL', $plug_url . 'public/blocks/' );
// Mock Ray
if ( file_exists( ILAB_HELPERS_DIR . '/ray-helper.php' ) ) {
    require_once ILAB_HELPERS_DIR . '/ray-helper.php';
}
// Composer
if ( file_exists( ILAB_LIB_DIR . '/autoload.php' ) ) {
    require_once ILAB_LIB_DIR . '/autoload.php';
}
if ( file_exists( ILAB_VENDOR_DIR . '/autoload.php' ) ) {
    require_once ILAB_VENDOR_DIR . '/autoload.php';
}
// Helper functions
require_once 'helpers/ilab-media-tool-wordpress-helpers.php';
require_once 'helpers/ilab-media-tool-geometry-helpers.php';
// Freemius

if ( function_exists( 'media_cloud_licensing' ) ) {
    media_cloud_licensing()->set_basename( false, __FILE__ );
} else {
    // Create a helper function for easy SDK access.
    /**
     * @return Freemius
     * @throws Freemius_Exception
     */
    function media_cloud_licensing()
    {
        global  $media_cloud_licensing ;
        
        if ( !isset( $media_cloud_licensing ) ) {
            require_once ILAB_TOOLS_DIR . '/external/Freemius/start.php';
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_1431_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_1431_MULTISITE', true );
            }
            /** @var Freemius $media_cloud_licensing */
            $media_cloud_licensing = fs_dynamic_init( array(
                'id'              => '1431',
                'slug'            => 'ilab-media-tools',
                'type'            => 'plugin',
                'public_key'      => 'pk_f20e8088bc078daafd3a20b6f653d',
                'is_premium'      => false,
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'has_affiliation' => 'selected',
                'trial'           => false,
                'menu'            => array(
                'slug'    => 'media-cloud',
                'contact' => false,
                'support' => false,
                'network' => true,
            ),
                'is_live'         => true,
            ) );
        }
        
        return $media_cloud_licensing;
    }
    
    // Init Freemius.
    //	media_cloud_licensing();
    media_cloud_licensing()->add_filter( 'permission_list', function ( $permissions ) {
        $permissions['feature-tracking'] = array(
            'icon-class' => 'dashicons dashicons-admin-generic',
            'label'      => media_cloud_licensing()->get_text_inline( 'Media Cloud Features', 'plugin-features' ),
            'desc'       => media_cloud_licensing()->get_text_inline( 'Anonymously track which Media Cloud features are being used to allow us to prioritize development.', 'permissions-plugin-features' ),
            'priority'   => 50,
            'optional'   => true,
        );
        return $permissions;
    } );
    media_cloud_licensing()->add_action(
        'after_account_connection',
        function ( $user, $install ) {
        \MediaCloud\Plugin\Tools\ToolsManager::AccountConnected();
    },
        1000,
        2
    );
    media_cloud_licensing()->add_action( 'after_uninstall', [ "\\MediaCloud\\Plugin\\Tools\\ToolsManager", 'uninstall' ] );
    // Signal that SDK was initiated.
    do_action( 'media_cloud_licensing_loaded' );
}

add_action( 'plugins_loaded', function () {
    \MediaCloud\Plugin\Tools\ToolsManager::Boot();
} );
register_activation_hook( __FILE__, [ "\\MediaCloud\\Plugin\\Tools\\ToolsManager", 'activate' ] );
register_deactivation_hook( __FILE__, [ "\\MediaCloud\\Plugin\\Tools\\ToolsManager", 'deactivate' ] );
add_action( 'admin_init', function () {
    if ( !wp_doing_ajax() && !media_cloud_licensing()->is_activation_mode() ) {
        
        if ( get_option( 'mcloud_show_wizard' ) ) {
            delete_option( 'mcloud_show_wizard' );
            
            if ( media_cloud_licensing()->is_network_active() ) {
                \MediaCloud\Plugin\Utilities\Environment::UpdateNetworkMode( true );
                exit( wp_redirect( network_admin_url( 'admin.php?page=media-cloud-wizard' ) ) );
            } else {
                exit( wp_redirect( admin_url( 'admin.php?page=media-cloud-wizard' ) ) );
            }
        
        }
    
    }
} );