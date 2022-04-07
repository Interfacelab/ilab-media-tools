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
namespace MediaCloud\Plugin\Tools;

use  MediaCloud\Plugin\Tasks\TaskManager ;
use  MediaCloud\Plugin\Tools\Network\NetworkTool ;
use  MediaCloud\Plugin\Tools\Storage\StorageToolSettings ;
use  MediaCloud\Plugin\Tools\Tasks\TasksTool ;
use  MediaCloud\Plugin\Utilities\Environment ;
use  MediaCloud\Plugin\Utilities\LicensingManager ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\NoticeManager ;
use  MediaCloud\Plugin\Utilities\Tracker ;
use  MediaCloud\Plugin\Utilities\View ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\json_response ;
use  MediaCloud\Plugin\Wizard\SetupWizard ;
use function  MediaCloud\Plugin\Utilities\vomit ;

if ( !defined( 'ABSPATH' ) ) {
    header( 'Location: /' );
    die;
}

/**
 * Class ILabMediaToolsManager
 *
 * Manages all of the tools for the ILab Media Tools plugin
 */
final class ToolsManager
{
    //region Class variables
    /** @var bool Determines if the plugin has "booted" or not */
    private static  $booted = false ;
    /** @var array Associative array of tool classes */
    private static  $registeredTools = array() ;
    /** @var ToolsManager The current instance */
    private static  $instance ;
    /** @var Tool[] Array of current tools  */
    public  $tools ;
    /** @var array Array of pinned tools */
    public  $pinnedTools = array() ;
    /** @var bool  */
    private  $hasInsertedToolSeparator = false ;
    /** @var bool  */
    private  $hasInsertedBatchToolSeparator = false ;
    /** @var bool  */
    private  $hasInsertedHelpToolSeparator = false ;
    /** @var bool  */
    private  $hasInsertedAccountSeparator = false ;
    /** @var null|NetworkTool  */
    private  $networkTool = null ;
    /** @var Tool[]  */
    private  $multisiteTools = array() ;
    /** @var bool Determines if any media cloud settings have changed. */
    private  $settingsDidChange = false ;
    /** @var SetupWizard|null */
    private  $wizard = null ;
    private  $isNetworkModeAdmin = false ;
    private  $isLocal = false ;
    //endregion
    //region Constructor
    public function __construct()
    {
        $this->tools = [];
        if ( class_exists( '\\hyperdb' ) || class_exists( '\\LudicrousDB' ) ) {
            add_filter(
                'pre_update_option',
                function ( $value, $option, $old_value ) {
                
                if ( empty($value) && strpos( $option, 'mcloud' ) === 0 ) {
                    $type = strtolower( gettype( $value ) );
                    if ( in_array( $type, [ 'boolean', 'null' ] ) ) {
                        return (string) '';
                    }
                }
                
                return $value;
            },
                10,
                3
            );
        }
        $hasRun = get_option( 'mcloud-has-run', false );
        
        if ( empty($hasRun) ) {
            static::AccountConnected();
            $this->pinnedTools = Environment::Option( 'mcloud-pinned-tools', null, [] );
            
            if ( empty($this->pinnedTools) ) {
                $this->pinnedTools = [
                    'storage' => 1,
                ];
                update_option( 'mcloud-has-run', true );
                update_option( 'mcloud-pinned-tools', $this->pinnedTools );
            }
        
        } else {
            $this->pinnedTools = Environment::Option( 'mcloud-pinned-tools', null, [] );
        }
        
        foreach ( static::$registeredTools as $toolName => $toolInfo ) {
            $className = $toolInfo['class'];
            $this->tools[$toolName] = new $className( $toolName, $toolInfo, $this );
            // Register CLI Commands
            if ( !empty($toolInfo['CLI']) && defined( 'WP_CLI' ) && class_exists( '\\WP_CLI' ) ) {
                foreach ( $toolInfo['CLI'] as $cliClass ) {
                    call_user_func( [ $cliClass, 'Register' ] );
                }
            }
        }
        
        if ( is_multisite() ) {
            add_action( 'network_admin_menu', function () {
                
                if ( Environment::NetworkMode() ) {
                    $this->addMenus( true, true );
                } else {
                    add_menu_page(
                        'Settings',
                        'Media Cloud',
                        'manage_network_options',
                        'media-cloud',
                        [ $this, 'renderNetworkSettings' ],
                        'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjA0OCAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTk4NCAxMTUycTAgMTU5LTExMi41IDI3MS41dC0yNzEuNSAxMTIuNWgtMTA4OHEtMTg1IDAtMzE2LjUtMTMxLjV0LTEzMS41LTMxNi41cTAtMTMyIDcxLTI0MS41dDE4Ny0xNjMuNXEtMi0yOC0yLTQzIDAtMjEyIDE1MC0zNjJ0MzYyLTE1MHExNTggMCAyODYuNSA4OHQxODcuNSAyMzBxNzAtNjIgMTY2LTYyIDEwNiAwIDE4MSA3NXQ3NSAxODFxMCA3NS00MSAxMzggMTI5IDMwIDIxMyAxMzQuNXQ4NCAyMzkuNXoiLz48L3N2Zz4='
                    );
                    add_submenu_page(
                        'media-cloud',
                        'Media Cloud Tools',
                        'Features',
                        'manage_network_options',
                        'media-cloud',
                        [ $this, 'renderNetworkSettings' ]
                    );
                    add_settings_section(
                        'ilab-media-features',
                        'Media Cloud Features',
                        null,
                        'media-cloud'
                    );
                    $this->tools['tasks']->registerMenu(
                        'media-cloud',
                        false,
                        true,
                        'media-cloud'
                    );
                    register_setting( 'ilab-media-features', "mcloud-network-mode" );
                    $this->insertAccountSeparator();
                }
            
            } );
            add_action( 'network_admin_edit_update_media_cloud_network_options', [ $this, 'updateNetworkOptions' ] );
            add_action( 'admin_menu', function () {
                $this->addMenus( Environment::NetworkMode(), false );
            } );
        } else {
            add_action( 'admin_menu', function () {
                $this->addMenus( false, false );
            } );
        }
        
        add_action( 'admin_bar_menu', function ( $adminBar ) {
            $this->addAdminBarItems( $adminBar );
        }, 1000 );
        $actionLinksPrefix = ( is_multisite() ? 'network_admin_' : '' );
        add_filter( $actionLinksPrefix . 'plugin_action_links_' . ILAB_PLUGIN_NAME, function ( $links ) {
            $links[] = "<a href='" . ilab_admin_url( 'admin.php?page=media-cloud-settings' ) . "'>Settings</a>";
            global  $media_cloud_licensing ;
            $links[] = "<a href='https://users.freemius.com' target='_blank'>Billing</a>";
            return $links;
        } );
        $maxTime = ini_get( 'max_execution_time' );
        if ( $maxTime > 0 && $maxTime < 90 ) {
            NoticeManager::instance()->displayAdminNotice(
                'warning',
                "The <code>max_execution_time</code> is set to a value that might be too low ({$maxTime}).  You should set it to about 90 seconds.  Additionally, if you are using Nginx or Apache, you may need to set the respective <code>fastcgi_read_timeout</code>, <code>request_terminate_timeout</code> or <code>TimeOut</code> settings too.",
                true,
                'ilab-media-tools-extime-notice'
            );
        }
        if ( !extension_loaded( 'mbstring' ) ) {
            NoticeManager::instance()->displayAdminNotice(
                'warning',
                "Media Cloud recommends that the <code>mbstring</code> PHP extension be installed and activated.",
                true,
                'mcloud-no-mbstring'
            );
        }
        NoticeManager::instance()->displayAdminNotice(
            'info',
            "The team behind Media Cloud is launching a new product in April 2022 that's going to change the way you work with media in WordPress.  <a href='https://preflight.ju.mp/' target='_blank'>Sign up</a> to be notified when <a href='https://preflight.ju.mp/' target='_blank'><strong>Preflight for WordPress</strong></a> is released.",
            true,
            'mcloud-preflight-beta-sales-pitch',
            10 * 365
        );
        add_action( 'admin_enqueue_scripts', function () {
            wp_enqueue_script(
                'mcloud-admin-js',
                ILAB_PUB_JS_URL . '/mcloud-admin.js',
                [
                'jquery',
                'wp-util',
                'wp-blocks',
                'wp-element'
            ],
                MEDIA_CLOUD_VERSION,
                true
            );
            wp_enqueue_script(
                'ilab-settings-js',
                ILAB_PUB_JS_URL . '/ilab-settings.js',
                [ 'jquery', 'wp-util' ],
                MEDIA_CLOUD_VERSION,
                true
            );
            wp_enqueue_style(
                'ilab-media-cloud-css',
                ILAB_PUB_CSS_URL . '/ilab-media-cloud.css',
                null,
                MEDIA_CLOUD_VERSION
            );
        } );
        
        if ( is_admin() ) {
            $this->wizard = new SetupWizard();
            add_action( 'wp_ajax_ilab_pin_tool', function () {
                $this->handlePinTool();
            } );
            add_action( 'wp_ajax_ilab_hide_upgrade_bug', function () {
                update_option( 'ilab_media_cloud_hide_upgrade_bug', time() );
                json_response( [
                    'status' => 'ok',
                ] );
            } );
        }
        
        add_action(
            "updated_option",
            function ( $setting, $oldValue = null, $newValue = null ) {
            if ( $setting != '_transient_timeout_settings_errors' && isset( $_POST['option_page'] ) && strpos( $_POST['option_page'], 'ilab-media-' ) === 0 ) {
                $this->settingsDidChange = true;
            }
        },
            10,
            3
        );
        add_action( 'shutdown', function () {
            if ( $this->settingsDidChange ) {
                Tracker::trackSettings();
            }
        } );
        if ( !defined( 'PHP_MAJOR_VERSION' ) || PHP_MAJOR_VERSION < 7 ) {
            NoticeManager::instance()->displayAdminNotice(
                'warning',
                'Media Cloud will stop supporting PHP 5 in the next major release of Media Cloud.  You should contact your hosting provider about upgrading.',
                true,
                'media-cloud-php-56-warning',
                30
            );
        }
        
        if ( defined( 'MCLOUD_IS_BETA' ) && !empty(constant( 'MCLOUD_IS_BETA' )) ) {
            $message = View::render_view( 'beta.beta-notes', [] );
            NoticeManager::instance()->displayAdminNotice(
                'info',
                $message,
                true,
                'mcloud-beta-notice' . MEDIA_CLOUD_VERSION,
                'forever'
            );
        }
    
    }
    
    protected function setup()
    {
        foreach ( $this->tools as $key => $tool ) {
            $tool->setup();
        }
        do_action( 'mediacloud/tasks/register' );
        //        MigrationsManager::instance()->displayMigrationErrors();
    }
    
    //endregion
    //region Static Methods
    /**
     * Returns the singleton instance of the manager
     * @return ToolsManager
     */
    public static function instance()
    {
        
        if ( !isset( self::$instance ) ) {
            $class = __CLASS__;
            self::$instance = new $class();
            self::$instance->setup();
        }
        
        return self::$instance;
    }
    
    /**
     * Starts Media Tools
     */
    public static function Boot()
    {
        global  $media_cloud_licensing ;
        if ( static::$booted ) {
            return;
        }
        static::$booted = true;
        Environment::Boot();
        
        if ( !is_multisite() && ($media_cloud_licensing->is_plan__premium_only( 'multisite_basic', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_pro', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_unlimited', true )) ) {
            add_action( 'admin_notices', function () {
                ?>
                <div class="notice notice-error">
                    <p><?php 
                _e( "This license is only valid for multisite WordPress sites.  Please <a href='mailto:support@mediacloud.press'>contact us</a> for assistance in selecting the correct license.", 'ilab-media-tools' );
                ?></p>
                </div>
                <?php 
            } );
        } else {
            // Register Tools
            ToolsManager::registerTool( "storage", include ILAB_CONFIG_DIR . '/storage.config.php' );
            ToolsManager::registerTool( "imgix", include ILAB_CONFIG_DIR . '/imgix.config.php' );
            ToolsManager::registerTool( "video-encoding", include ILAB_CONFIG_DIR . '/video-encoding.config.php' );
            ToolsManager::registerTool( "video-player", include ILAB_CONFIG_DIR . '/video-player.config.php' );
            ToolsManager::registerTool( "vision", include ILAB_CONFIG_DIR . '/vision.config.php' );
            ToolsManager::registerTool( "crop", include ILAB_CONFIG_DIR . '/crop.config.php' );
            ToolsManager::registerTool( "debugging", include ILAB_CONFIG_DIR . '/debugging.config.php' );
            ToolsManager::registerTool( "troubleshooting", include ILAB_CONFIG_DIR . '/troubleshooting.config.php' );
            ToolsManager::registerTool( "batch-processing", include ILAB_CONFIG_DIR . '/batch-processing.config.php' );
            ToolsManager::registerTool( "tasks", include ILAB_CONFIG_DIR . '/tasks.config.php' );
            ToolsManager::registerTool( "reports", include ILAB_CONFIG_DIR . '/reports.config.php' );
            if ( LicensingManager::CanTrack() ) {
                ToolsManager::registerTool( "opt-in", include ILAB_CONFIG_DIR . '/opt-in.config.php' );
            }
            do_action( 'media-cloud/tools/register-tools' );
        }
        
        // Make sure the NoticeManager is initialized
        NoticeManager::instance();
        // Get the party started
        ToolsManager::instance();
        // Start the task manager
        TaskManager::instance();
    }
    
    public static function AccountConnected()
    {
        
        if ( LicensingManager::CanTrack() ) {
            Environment::UpdateOption( 'mcloud-opt-in-crisp', true );
            Environment::UpdateOption( 'mcloud-opt-usage-tracking', true );
        }
    
    }
    
    /**
     * Registers a tool
     *
     * @param $identifier string The identifier of the tool
     * @param $config array The configuration for the tool
     */
    public static function registerTool( $identifier, $config )
    {
        static::$registeredTools[$identifier] = $config;
    }
    
    //endregion
    //region Menu Related
    /**
     * Adds items to the WordPress Admin Bar
     *
     * @param \WP_Admin_Bar $adminBar
     */
    public function addAdminBarItems( $adminBar )
    {
        $hasAdminBar = false;
        foreach ( $this->tools as $toolId => $tool ) {
            
            if ( $tool->hasAdminBarMenu() ) {
                $hasAdminBar = true;
                break;
            }
        
        }
        
        if ( $hasAdminBar ) {
            $adminBar->add_menu( [
                'id'    => 'media-cloud-admin-bar',
                'href'  => admin_url( 'admin.php?page=media-cloud' ),
                'title' => '<span class="ab-icon"></span>Media Cloud',
            ] );
            foreach ( $this->tools as $toolId => $tool ) {
                $tool->addAdminMenuBarItems( $adminBar );
            }
        }
    
    }
    
    public function addMenus( $networkMode, $networkAdminMenu )
    {
        global  $media_cloud_licensing ;
        
        if ( is_multisite() ) {
            $showTasks = is_network_admin() || empty(Environment::Option( 'media-cloud-task-manager-hide', null, true ));
        } else {
            $showTasks = empty(StorageToolSettings::instance()->useToolMenu);
        }
        
        $networkMode = false;
        $networkAdminMenu = false;
        $this->isNetworkModeAdmin = false;
        $this->isLocal = true;
        $showMenu = true;
        
        if ( is_multisite() ) {
            $hideMenu = Environment::Option( 'media-cloud-network-hide', null, false );
            if ( $hideMenu && !is_network_admin() ) {
                $showMenu = false;
            }
        }
        
        if ( $showMenu ) {
            
            if ( $this->isLocal ) {
                add_menu_page(
                    'Settings',
                    'Media Cloud',
                    'manage_options',
                    'media-cloud',
                    [ $this, 'renderFeatureSettings' ],
                    'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjA0OCAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTk4NCAxMTUycTAgMTU5LTExMi41IDI3MS41dC0yNzEuNSAxMTIuNWgtMTA4OHEtMTg1IDAtMzE2LjUtMTMxLjV0LTEzMS41LTMxNi41cTAtMTMyIDcxLTI0MS41dDE4Ny0xNjMuNXEtMi0yOC0yLTQzIDAtMjEyIDE1MC0zNjJ0MzYyLTE1MHExNTggMCAyODYuNSA4OHQxODcuNSAyMzBxNzAtNjIgMTY2LTYyIDEwNiAwIDE4MSA3NXQ3NSAxODFxMCA3NS00MSAxMzggMTI5IDMwIDIxMyAxMzQuNXQ4NCAyMzkuNXoiLz48L3N2Zz4='
                );
                add_submenu_page(
                    'media-cloud',
                    'Media Cloud Tools',
                    'Features',
                    'manage_options',
                    'media-cloud',
                    [ $this, 'renderFeatureSettings' ]
                );
                
                if ( !empty(StorageToolSettings::instance()->useToolMenu) ) {
                    add_menu_page(
                        'Tasks',
                        'Cloud Tasks',
                        'manage_options',
                        'media-cloud-tools',
                        [ $this, 'renderMultisiteLanding' ],
                        'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAgMTkiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgICAgICAgICAgPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik04LDIuMzgwMzE4MTZlLTEzIEM5LjA5NzIyMjIyLDIuMzgwMzE4MTZlLTEzIDEwLjA5MjAxMzksMC4zMDU1NTU1NTYgMTAuOTg0Mzc1LDAuOTE2NjY2NjY3IEMxMS44NzY3MzYxLDEuNTI3Nzc3NzggMTIuNTI3Nzc3OCwyLjMyNjM4ODg5IDEyLjkzNzUsMy4zMTI1IEMxMy40MjM2MTExLDIuODgxOTQ0NDQgMTQsMi42NjY2NjY2NyAxNC42NjY2NjY3LDIuNjY2NjY2NjcgQzE1LjQwMjc3NzgsMi42NjY2NjY2NyAxNi4wMzEyNSwyLjkyNzA4MzMzIDE2LjU1MjA4MzMsMy40NDc5MTY2NyBDMTcuMDcyOTE2NywzLjk2ODc1IDE3LjMzMzMzMzMsNC41OTcyMjIyMiAxNy4zMzMzMzMzLDUuMzMzMzMzMzMgQzE3LjMzMzMzMzMsNS44NTQxNjY2NyAxNy4xOTA5NzIyLDYuMzMzMzMzMzMgMTYuOTA2MjUsNi43NzA4MzMzMyBDMTcuODAyMDgzMyw2Ljk3OTE2NjY3IDE4LjU0MTY2NjcsNy40NDYxODA1NiAxOS4xMjUsOC4xNzE4NzUgQzE5LjcwODMzMzMsOC44OTc1Njk0NCAyMCw5LjcyOTE2NjY3IDIwLDEwLjY2NjY2NjcgQzIwLDExLjc3MDgzMzMgMTkuNjA5Mzc1LDEyLjcxMzU0MTcgMTguODI4MTI1LDEzLjQ5NDc5MTcgQzE4LjA0Njg3NSwxNC4yNzYwNDE3IDE3LjEwNDE2NjcsMTQuNjY2NjY2NyAxNiwxNC42NjY2NjY3IEwxNS44OTkwODUyLDE0LjY2NzU5OTIgQzE2LjA0NTQ5NjgsMTQuNDgxMjM2NSAxNi4xNDM5NDczLDE0LjIzODE2NDUgMTYuMTg4NDMzNiwxMy45MzMwODczIEMxNi4yNTk1MjUzLDEzLjQ2NDkwNCAxNi4yNjM2NjQ2LDEzLjAzNTQ5NiAxNi4xOTkyNzc5LDEyLjU3NTIxNjEgQzE2LjEwMjQwODEsMTEuODY4MzI1MyAxNS43MjM5NTU2LDExLjQ4MTgxOTggMTUuMTEyMTcyMSwxMS4zNjE3Mzc4IEwxNC45NjY4NzEzLDExLjMzODczMTQgTDE0LjQ4MiwxMS4yNzMwMDA5IEwxNC42ODg1ODM2LDExLjAwMTE5NjEgTDE0LjgwNDc3NTgsMTAuODUyODgxIEwxNC44NTk5OTUzLDEwLjc3NzkzNjkgQzE0LjkzMzYwMDksMTAuNjczNjExNSAxNC45OTIyODY2LDEwLjU3MDAyMSAxNS4wMzgyNjM5LDEwLjQ0NDE3MzIgQzE1LjIxNzQ0MjMsOS45NTM3MzA4MiAxNS4wODQzNTEzLDkuNTQ3Nzk3NjMgMTQuNzc1OTk0NCw5LjEzOTkxNDczIEMxNC40OTUyNzI4LDguNzcwMzQ1MDkgMTQuMTg5NzQ1NCw4LjQ3MDMxNjYxIDEzLjgwNzg2MTMsOC4xODY2ODc5NCBDMTMuMzg2ODU4OSw3Ljg3NjgyNTE3IDEyLjk1MTgxODcsNy43NTY5ODI3NCAxMi40NjE4ODUzLDcuOTY4ODUzMTEgQzEyLjM3MzUyODQsOC4wMDcwNjI3NyAxMi4zMDEwODU3LDguMDQ4MjA5MDkgMTIuMjI4OTUyNiw4LjA5NzgwMDEyIEwxMi4xMTkwMzQ2LDguMTc5MjA4NzEgTDExLjcwNiw4LjQ5ODAwMDkyIEwxMS42MTg2NDc3LDcuODY5MDM1MSBDMTEuNTE3MTM2Niw3LjM1MTE2NDM5IDExLjIyNDgxMzcsNy4wMDAzOTczNSAxMC43MDg4NDc0LDYuODQ2MDE2NjggTDEwLjU2MjU1OTcsNi44MDg4MDM5NSBMMTAuNDA1MDM0Myw2Ljc4MTQ2MTM4IEwxMC4xMzE3OTMyLDYuNzUxMDIxNTEgQzkuNzcwODM1MTQsNi43MjEzODg5NiA5LjQyMDExNTcxLDYuNzM1NTQ4NSA5LjA0MTA1NTY2LDYuNzkzMjE1NTggQzguMzQzOTc4ODUsNi44OTQ4NDUzMSA3Ljk3MDY0NDkxLDcuMjgzNjAxNTQgNy44NjEyMTM1Miw3Ljg4NTM4MjM1IEw3Ljg0MDYxNzU1LDguMDI4MDAyNjEgTDcuNzgsOC40OTcwMDA5MiBMNy41MDYwMjg5Nyw4LjI5MTEwODk5IEw3LjM3ODM3NDUsOC4xOTA4NDMyNCBMNy4yMTgxODgxMyw4LjA3NTM5MTMgQzcuMTM2OTUwODEsOC4wMjI5OTE0MSA3LjA1NDA3MTI5LDcuOTgwMjc4NzYgNi45NTQxMDAxMSw3Ljk0MzQ0ODI1IEM2LjQ2MjgzNzYzLDcuNzYyNDYxNjYgNi4wNTYxOTg5MSw3Ljg5NTUzODIxIDUuNjQ3MzQzOTcsOC4yMDQ1MTA1NCBDNS4yNzg0MDE5MSw4LjQ4NDQwNjAyIDQuOTc4OTk0MzksOC43ODk2Nzk5MSA0LjY5NDU3MTc1LDkuMTcyNDgwMDQgQzQuMzg1MDYwOTIsOS41OTE0Mjk0NiA0LjI2NDUwNjEsMTAuMDI2MDM2MSA0LjQ3NDYzNTU2LDEwLjUxNTkwMjMgQzQuNTEyNDY4NDksMTAuNjA0MTAwNyA0LjU1MzE3MjE2LDEwLjY3NjMzNDEgNC42MDIzMDQ1NCwxMC43NDgzNjY0IEw0LjY4MzAxOTM4LDEwLjg1ODIzNDUgTDUuMDAzLDExLjI3MzAwMDkgTDQuNTIyNjE1NDksMTEuMzM4NzMxNCBDMy44MjU0MjA4OCwxMS40MjQyNTMyIDMuMzk0NTMwMjMsMTEuODEzOTQ5MSAzLjI4OTgyNjExLDEyLjU3Nzk4MDUgQzMuMjI1Nzc1OTEsMTMuMDM1ODI2OSAzLjIyOTg3OTIzLDEzLjQ2NTM2OTcgMy4zMDE5NjMwOCwxMy45MzkxOTQ4IEMzLjMzNTYyODM3LDE0LjE3MDEwNDUgMy40MDA3OTk4OCwxNC4zNjU0OTAxIDMuNDk0Nzg0MTQsMTQuNTI3NTI2NiBDMi43MDExNzI2NCwxNC4zMjk3MzkzIDEuOTkyMzE4MjYsMTMuOTE5NDAxNiAxLjM2OTc5MTY3LDEzLjI5Njg3NSBDMC40NTY1OTcyMjIsMTIuMzgzNjgwNiAtMS4zODM3ODE5OGUtMTIsMTEuMjg0NzIyMiAtMS4zODM3ODE5OGUtMTIsMTAgQy0xLjM4Mzc4MTk4ZS0xMiw5LjA4MzMzMzMzIDAuMjQ2NTI3Nzc4LDguMjQ0NzkxNjcgMC43Mzk1ODMzMzMsNy40ODQzNzUgQzEuMjMyNjM4ODksNi43MjM5NTgzMyAxLjg4MTk0NDQ0LDYuMTU2MjUgMi42ODc1LDUuNzgxMjUgQzIuNjczNjExMTEsNS41ODY4MDU1NiAyLjY2NjY2NjY3LDUuNDM3NSAyLjY2NjY2NjY3LDUuMzMzMzMzMzMgQzIuNjY2NjY2NjcsMy44NjExMTExMSAzLjE4NzUsMi42MDQxNjY2NyA0LjIyOTE2NjY3LDEuNTYyNSBDNS4yNzA4MzMzMywwLjUyMDgzMzMzMyA2LjUyNzc3Nzc4LDIuMzgwMzE4MTZlLTEzIDgsMi4zODAzMTgxNmUtMTMgWiBNOS44Mzk2NjY2OCw3LjczNjc1MDUxIEwxMC4wNTExMTM4LDcuNzQ3NzYzOTMgTDEwLjI2NjQ5NzMsNy43NzE4MTg2MyBDMTAuNTE2NzgsNy44MDYxMTc3OSAxMC41OTE2NTcsNy44NjcxNDM1NyAxMC42MjU2MTYxLDcuOTk3MDM3NjMgTDEwLjYzODA1Nyw4LjA1NzA0NTgxIEwxMC42Njg4NDMsOC4yOTUwMzkxNSBMMTAuODI4MjE2OSw5LjQ0MDk2NjY0IEwxMS4wNDYxMjIzLDkuNTEwMDQ5MTUgTDExLjI1ODA5MjQsOS41OTA3NjE1OCBMMTEuMjU4MDkyNCw5LjU5MDc2MTU4IEwxMS40NjM5NjY5LDkuNjgzMDQ2NjQgTDExLjY2MzU4NTMsOS43ODY4NDcwNSBMMTIuNTgwODQwNiw5LjA4OTgzMDc5IEwxMi43NDMxMzk5LDguOTYwNjIzMDcgQzEyLjc4OTM0NzcsOC45MjQ3MjgyNSAxMi44Mjk3OTMsOC44OTcyMzY3NCAxMi44NzA4NjQsOC44ODE4MzUxNSBMMTIuOTMzNzM4NCw4Ljg2ODI2MTAyIEwxMi45OTAzMDcyLDguODcyNzk3MDYgQzEzLjA0OTc4OTcsOC44ODUxMjMzNiAxMy4xMTkzOTk2LDguOTIxNjIwNDcgMTMuMjExNjE0LDguOTg5NDg4ODEgQzEzLjUxNzUyODgsOS4yMTY2OTQzNSAxMy43NTU4NTYzLDkuNDUwMTMzMDggMTMuOTc4Mjk1Myw5Ljc0Mjk3MDM1IEMxNC4xNjk0NDYxLDkuOTk1ODE3NDkgMTQuMTM2NDE3NiwxMC4wODIzNjgzIDE0LjAwNTM2MzQsMTAuMjUxODYzOCBMMTMuOTUxODY5NSwxMC4zMTk3OTk1IEwxMy4xOTY0NTg5LDExLjMxNzM5ODUgTDEzLjMwMDUzNjIsMTEuNTE2ODIzOSBMMTMuMzkzNDc2MywxMS43MjI3MjMyIEwxMy4zOTM0NzYzLDExLjcyMjcyMzIgTDEzLjQ3NDY4MzMsMTEuOTM0NzQxMSBMMTMuNTQzNTYxNSwxMi4xNTI1MjI0IEwxNC42ODU3MDAyLDEyLjMxMTQwNzQgTDE0LjkyMzY5MzUsMTIuMzQyMjQ2NCBDMTUuMDk1NjE0NCwxMi4zNzA4NDQ0IDE1LjE2OTcyMTYsMTIuNDI3NzE1NyAxNS4yMDg5MjA3LDEyLjcxMzc1MzEgQzE1LjI1OTg4NjEsMTMuMDc4MDg4NyAxNS4yNTYzNDE3LDEzLjQxMDQwMjcgMTUuMTk4ODk4NywxMy43ODg3OTM1IEMxNS4xNjM4OTk2LDE0LjAyODgwOTggMTUuMDk5Njk5MSwxNC4wOTY5MzQ0IDE0Ljk3MjE1NTcsMTQuMTI2Mzk1NCBMMTQuOTEzNTE2NSwxNC4xMzcwNDI4IEwxNC42ODUwODkxLDE0LjE2Mzg4NDMgTDEzLjU0NTM5NDgsMTQuMzE4NzM2IEwxMy40NzY1NDE0LDE0LjUzNzE4NzYgQzEzLjQ2MTM2MzgsMTQuNTgwNzM5MiAxMy40NDU0MjQ4LDE0LjYyMzk0OSAxMy40Mjg3NDY5LDE0LjY2Njc5OTkgTDExLjE4MTkyOTgsMTQuNjY3MDQ0MyBDMTEuNTQ3MzMzNiwxNC4zMDAxOTE4IDExLjc3MzIxNTksMTMuNzk0Mjc2NSAxMS43NzMyMTU5LDEzLjIzNTYyODkgQzExLjc3MzA5MzcsMTIuMTE1ODU2MyAxMC44NjQ3NjA0LDExLjIwNzUyMyA5Ljc0NDYyMTE4LDExLjIwNzUyMyBDOC42MjQ3MjYzOSwxMS4yMDc1MjMgNy43MTY1MTUzLDEyLjExNTczNDEgNy43MTY1MTUzLDEzLjIzNTYyODkgQzcuNzE2NTE1MywxMy43OTQyNzY1IDcuOTQyMzY3MjcsMTQuMzAwMTkxOCA4LjMwNzY5NTA2LDE0LjY2NzA0NDMgTDYuMDY1LDE0LjY2NiBMNi4wMTYwMTIzNCwxNC41MzcwNzUgTDUuOTQ3MjY5NzUsMTQuMzE4NzM2IEw0LjgwNDM5Nzc0LDE0LjE2Mzg4NDMgTDQuNTc1OTcwMjgsMTQuMTM3MDQyOCBDNC40MDg3MjY2MywxNC4xMTI4OTA2IDQuMzMwNTg3MTMsMTQuMDYzMDk3OSA0LjI5MDU4ODExLDEzLjc4ODc5MzUgQzQuMjMzMDIyODUsMTMuNDEwNDAyNyA0LjIyOTYwMDcxLDEzLjA3ODA4ODcgNC4yODA1NjYxMywxMi43MTM3NTMxIEM0LjMxNDg2NTI5LDEyLjQ2MzQ3MDQgNC4zNzU4OTEwNywxMi4zODg2NDI5IDQuNTA1Nzg1MTMsMTIuMzU0Njg4MyBMNC41NjU3OTMzLDEyLjM0MjI0NjQgTDUuOTQ5NzE0MTQsMTIuMTUyMDMzNiBMNi4wMTkxMDk4MywxMS45MzUzMjM2IEw2LjA5OTk5Nzk1LDExLjcyMzYyNDYgTDYuMTkyMjc1MzgsMTEuNTE4MTEzIEw2LjI5NTgzODk5LDExLjMxOTQ3NjIgTDUuNTk0OTExNzEsMTAuMzk5NjU0MyBMNS40NDE5MTMxNiwxMC4yMDM2Nzk4IEM1LjM1MDg1OTQ3LDEwLjA3NjE0NjEgNS4zMzgzMDAyMiw5Ljk4NDA0Nzc4IDUuNDk3MjU4NTUsOS43Njg4ODA4MyBDNS43MjQ0NjQxLDkuNDYzMDg4MzIgNS45NTcwNDcyOSw5LjIyNDc2MDgyIDYuMjUwMjUxMjIsOS4wMDIzMjE4MyBDNi4zNTE0MjkxOCw4LjkyNTg2MTQ4IDYuNDI1OTI0NjQsOC44ODUyNjk4MyA2LjQ4OTcwOTA0LDguODcyNDczMDUgTDYuNTM1Nzg4NTgsOC44Njc5NjA4NSBMNi41OTAwMjA2Nyw4Ljg3NTgyNDkyIEw2LjY0MzI0MDc5LDguODk3MzE0IEw2LjY0MzI0MDc5LDguODk3MzE0IEw2LjY5ODM3MzQsOC45MzA5NDk3MSBMNi45MDQ0OTA3NSw5LjA4OTk1MzAxIEw3LjgyNzI0NTkyLDkuNzg1OTkxNTIgTDguMDI1NDY2NDIsOS42ODM4ODY5IEw4LjIzMDk5NzE1LDkuNTkyMTk3NjUgTDguMjMwOTk3MTUsOS41OTIxOTc2NSBMOC40NDMxMjc3MSw5LjUxMTY2ODU1IEw4LjY2MTE0NzcyLDkuNDQzMDQ0MzYgTDguODE2MzY2MTQsOC4yOTU2NTAyNSBDOC44NjE1ODcyNiw3Ljk2Mzk0NzI2IDguODE0Mjg4NDEsNy44MzY4MzkyNiA5LjE5MTQ1Njk1LDcuNzgxODQwNjEgQzkuNDE4NDkxMzksNy43NDczMDE0NiA5LjYyODkzODI0LDcuNzMyMjUzODIgOS44Mzk2NjY2OCw3LjczNjc1MDUxIFoiPjwvcGF0aD4KICAgICAgICAgICAgPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik05LjE5MTQ1Njk1LDcuNzgxODQwMTEgQzkuNTY5ODQ3NjgsNy43MjQyNzQ4NiA5LjkwMjE2MTc3LDcuNzIwODUyNzIgMTAuMjY2NDk3Myw3Ljc3MTgxODE0IEMxMC42NTk3OTg4LDcuODI1NzE2ODIgMTAuNjE5OTU1Myw3Ljk0NTYxMzg4IDEwLjY2ODg0Myw4LjI5NTAzODY1IEwxMC42Njg4NDMsOC4yOTUwMzg2NSBMMTAuODI4MjE2OSw5LjQ0MDk2NjE0IEMxMS4xMjI2NDMsOS41MjUyOTc0MSAxMS40MDE2Njk1LDkuNjQwNzk0NTggMTEuNjYzNTg1Myw5Ljc4Njg0NjU2IEwxMS42NjM1ODUzLDkuNzg2ODQ2NTYgTDEyLjU4MDg0MDYsOS4wODk4MzAzIEMxMi44NDgwMTE4LDguODg1NjAxOTcgMTIuOTA0MjMyNyw4Ljc2MzI2MDUyIDEzLjIxMTYxNCw4Ljk4OTQ4ODMxIEMxMy41MTc1Mjg4LDkuMjE2NjkzODYgMTMuNzU1ODU2Myw5LjQ1MDEzMjU4IDEzLjk3ODI5NTMsOS43NDI5Njk4NSBDMTQuMjE3MjMzOSwxMC4wNTkwMjg4IDE0LjEwNTg5MjEsMTAuMTE1MjQ5NiAxMy44OTA2NjQxLDEwLjM5ODQzMTYgTDEzLjg5MDY2NDEsMTAuMzk4NDMxNiBMMTMuMTk2NDU4OSwxMS4zMTczOTggQzEzLjM0MjM4ODYsMTEuNTc4ODI0OSAxMy40NjAyMDc5LDExLjg1ODQ2MjUgMTMuNTQzNTYxNSwxMi4xNTI1MjE5IEwxMy41NDM1NjE1LDEyLjE1MjUyMTkgTDE0LjY4NTcwMDIsMTIuMzExNDA2OSBDMTUuMDM1MTI0OSwxMi4zNjA0MTY5IDE1LjE1NTAyMiwxMi4zMjA0NTEyIDE1LjIwODkyMDcsMTIuNzEzNzUyNiBDMTUuMjU5ODg2MSwxMy4wNzgwODgyIDE1LjI1NjM0MTcsMTMuNDEwNDAyMiAxNS4xOTg4OTg3LDEzLjc4ODc5MyBDMTUuMTQzOSwxNC4xNjU5NjE1IDE1LjAxNjc5MiwxNC4xMTg2NjI3IDE0LjY4NTA4OTEsMTQuMTYzODgzOCBMMTQuNjg1MDg5MSwxNC4xNjM4ODM4IEwxMy41NDUzOTQ4LDE0LjMxODczNTYgQzEzLjQ2MjE2MzUsMTQuNjEzNTI4MyAxMy4zNDQwOTk3LDE0Ljg5NDYzMjYgMTMuMTk3NjgxLDE1LjE1NzE1OTUgTDEzLjE5NzY4MSwxNS4xNTcxNTk1IEwxMy44OTA2NjQxLDE2LjA3NTc1OTIgQzE0LjEwNTg5MjEsMTYuMzU4MjA3OCAxNC4yMTcyMzM5LDE2LjQxMzgxNzYgMTMuOTc4Mjk1MywxNi43Mjk5OTg3IEMxMy43NTU3MzQsMTcuMDIzMjAyNiAxMy41MTc1Mjg4LDE3LjI1NTc4NTggMTMuMjExNjE0LDE3LjQ4Mjk5MTQgQzEyLjkwNDIzMjcsMTcuNzA5OTUyNSAxMi44NDgwMTE4LDE3LjU4NzEyMjIgMTIuNTgwODQwNiwxNy4zODUzMzgyIEwxMi41ODA4NDA2LDE3LjM4NTMzODIgTDExLjY2NjY0MDgsMTYuNjg4Njg4NiBDMTEuNDA0MTEzOSwxNi44MzU5NjI4IDExLjEyMjY0MywxNi45NTM0MTU1IDEwLjgyNzQ4MzUsMTcuMDM3MTM1NiBMMTAuODI3NDgzNSwxNy4wMzcxMzU2IEwxMC42Njg5NjUyLDE4LjE3NjQ2MzMgQzEwLjYxOTk1NTMsMTguNTI1ODg4MSAxMC42NTk5MjEsMTguNjQ1Nzg1MSAxMC4yNjY2MTk1LDE4LjY5OTY4MzggQzkuOTAyMjgzOTksMTguNzUwNjQ5MiA5LjU2OTk2OTksMTguNzQ3MTA0OSA5LjE5MTU3OTE3LDE4LjY4OTY2MTggQzguODE0NDEwNjMsMTguNjM0NjYzMiA4Ljg2MTcwOTQ3LDE4LjUwNzU1NTIgOC44MTY0ODgzNiwxOC4xNzU4NTIyIEw4LjgxNjQ4ODM2LDE4LjE3NTg1MjIgTDguNjYxNjM2NiwxNy4wMzYxNTc5IEM4LjM2NzMzMjcsMTYuOTUyNDM3NyA4LjA4NzgxNzMyLDE2LjgzNDQ5NjIgNy44MjU5MDE1MSwxNi42ODg2ODg2IEw3LjgyNTkwMTUxLDE2LjY4ODY4ODYgTDYuOTA0NDkwNzUsMTcuMzg0MjM4MiBDNi42MjIwNDIxMSwxNy41OTc2MzMgNi41NjYzMTAxNCwxNy43MTA1NjM2IDYuMjUwMjUxMjIsMTcuNDcwODkxNyBDNS45NTY1NTg0MSwxNy4yNDkzMDgyIDUuNzI0NDY0MSwxNy4wMTE5NTg1IDUuNDk3MjU4NTUsMTYuNzAzMzU0OSBDNS4yNzAyOTc0NCwxNi4zOTY3MDY5IDUuMzkzMTI3NzcsMTYuMzQwNDg2IDUuNTk0OTExNzEsMTYuMDczMzE0OCBMNS41OTQ5MTE3MSwxNi4wNzMzMTQ4IEw2LjI5NDQ5NDU4LDE1LjE1NTQ0ODQgQzYuMTQ4NTY0ODIsMTQuODk0MDIxNSA2LjAzMDI1NjYxLDE0LjYxMzUyODMgNS45NDcyNjk3NSwxNC4zMTg3MzU2IEw1Ljk0NzI2OTc1LDE0LjMxODczNTYgTDQuODA0Mzk3NzQsMTQuMTYzODgzOCBDNC40NzI2OTQ3NiwxNC4xMTg2NjI3IDQuMzQ1NTg2NzYsMTQuMTY1OTYxNSA0LjI5MDU4ODExLDEzLjc4ODc5MyBDNC4yMzMwMjI4NSwxMy40MTA0MDIyIDQuMjI5NjAwNzEsMTMuMDc4MDg4MiA0LjI4MDU2NjEzLDEyLjcxMzc1MjYgQzQuMzM0NDY0ODEsMTIuMzIwNDUxMiA0LjQ1NDM2MTg3LDEyLjM2MDQxNjkgNC44MDM3ODY2NSwxMi4zMTE0MDY5IEM1LjE4NjA4ODQsMTIuMjU3ODc0OSA1LjU2ODI2NzkyLDEyLjIwNDgzMTggNS45NDk3MTQxNCwxMi4xNTIwMzMxIEw1Ljk0OTcxNDE0LDEyLjE1MjAzMzEgTDYuMDE5MTA5ODMsMTEuOTM1MzIzMSBDNi4wOTQyNjg5MywxMS43MjA3NTk2IDYuMTg2NjY2NjYsMTEuNTE0NDQ1OSA2LjI5NTgzODk5LDExLjMxOTQ3NTcgTDYuMjk1ODM4OTksMTEuMzE5NDc1NyBMNS45NDU4MzM2NywxMC44NTkxMjE3IEw1Ljk0NTgzMzY3LDEwLjg1OTEyMTcgTDUuNTk0OTExNzEsMTAuMzk5NjUzOCBDNS4zOTMxMjc3NywxMC4xMzI0ODI1IDUuMjcwMTc1MjIsMTAuMDc2MjYxNyA1LjQ5NzI1ODU1LDkuNzY4ODgwMzMgQzUuNzI0NDY0MSw5LjQ2MzA4NzgyIDUuOTU3MDQ3MjksOS4yMjQ3NjAzMyA2LjI1MDI1MTIyLDkuMDAyMzIxMzMgQzYuNTY2NDMyMzYsOC43NjMzODI3NCA2LjYyMjA0MjExLDguODc0NzI0NDUgNi45MDQ0OTA3NSw5LjA4OTk1MjUyIEM3LjIxMjk3MjA4LDkuMzIxNTU3OTUgNy41MTk3NDIzNSw5LjU1MzUzMDA1IDcuODI3MjQ1OTIsOS43ODU5OTEwMiBMNy44MjcyNDU5Miw5Ljc4NTk5MTAyIEM4LjA4NjM1MDY5LDkuNjQzMjM4OTYgOC4zNjY4NDM4Miw5LjUyNjI3NTE2IDguNjYxMTQ3NzIsOS40NDMwNDM4NyBMOC42NjExNDc3Miw5LjQ0MzA0Mzg3IEM4LjcxMzQ1NzU1LDkuMDUyNTUzNDMgOC43NjQzMDA3NSw4LjY4NTc3MzUzIDguODE2MzY2MTQsOC4yOTU2NDk3NSBDOC44NjE1ODcyNiw3Ljk2Mzk0Njc2IDguODE0Mjg4NDEsNy44MzY4Mzg3NyA5LjE5MTQ1Njk1LDcuNzgxODQwMTEgWiBNOS43NDQ2MjExOCwxMS4yMDc1MjI5IEM4LjYyNDcyNjM5LDExLjIwNzUyMjkgNy43MTY1MTUzLDEyLjExNTczNCA3LjcxNjUxNTMsMTMuMjM1NjI4NyBDNy43MTY1MTUzLDE0LjM1NTg5MDIgOC42MjQ3MjYzOSwxNS4yNjQxMDEzIDkuNzQ0NjIxMTgsMTUuMjY0MTAxMyBDMTAuODY0ODgyNiwxNS4yNjQxMDEzIDExLjc3MzIxNTksMTQuMzU1ODkwMiAxMS43NzMyMTU5LDEzLjIzNTYyODcgQzExLjc3MzA5MzcsMTIuMTE1ODU2MiAxMC44NjQ3NjA0LDExLjIwNzUyMjkgOS43NDQ2MjExOCwxMS4yMDc1MjI5IFoiPjwvcGF0aD4KPC9zdmc+'
                    );
                    add_submenu_page(
                        'media-cloud-tools',
                        'Available Tasks',
                        'Available Tasks',
                        'manage_options',
                        'media-cloud-tools',
                        [ $this, 'renderMultisiteLanding' ]
                    );
                    $this->tools['tasks']->registerMenu(
                        'media-cloud-tools',
                        $networkMode,
                        $networkAdminMenu,
                        'media-cloud-tools'
                    );
                }
                
                $this->wizard->registerMenu(
                    'media-cloud',
                    $networkMode,
                    $networkAdminMenu,
                    'media-cloud'
                );
            } else {
                
                if ( $this->isNetworkModeAdmin ) {
                    add_menu_page(
                        'Settings',
                        'Media Cloud',
                        'manage_options',
                        'media-cloud',
                        [ $this, 'renderFeatureSettings' ],
                        'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjA0OCAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTk4NCAxMTUycTAgMTU5LTExMi41IDI3MS41dC0yNzEuNSAxMTIuNWgtMTA4OHEtMTg1IDAtMzE2LjUtMTMxLjV0LTEzMS41LTMxNi41cTAtMTMyIDcxLTI0MS41dDE4Ny0xNjMuNXEtMi0yOC0yLTQzIDAtMjEyIDE1MC0zNjJ0MzYyLTE1MHExNTggMCAyODYuNSA4OHQxODcuNSAyMzBxNzAtNjIgMTY2LTYyIDEwNiAwIDE4MSA3NXQ3NSAxODFxMCA3NS00MSAxMzggMTI5IDMwIDIxMyAxMzQuNXQ4NCAyMzkuNXoiLz48L3N2Zz4='
                    );
                    add_submenu_page(
                        'media-cloud',
                        'Media Cloud Tools',
                        'Features',
                        'manage_options',
                        'media-cloud',
                        [ $this, 'renderFeatureSettings' ]
                    );
                    $this->wizard->registerMenu(
                        'media-cloud',
                        $networkMode,
                        $networkAdminMenu,
                        'media-cloud'
                    );
                } else {
                    add_menu_page(
                        'Settings',
                        'Media Cloud',
                        'manage_options',
                        'media-cloud',
                        [ $this, 'renderMultisiteLanding' ],
                        'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjA0OCAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTk4NCAxMTUycTAgMTU5LTExMi41IDI3MS41dC0yNzEuNSAxMTIuNWgtMTA4OHEtMTg1IDAtMzE2LjUtMTMxLjV0LTEzMS41LTMxNi41cTAtMTMyIDcxLTI0MS41dDE4Ny0xNjMuNXEtMi0yOC0yLTQzIDAtMjEyIDE1MC0zNjJ0MzYyLTE1MHExNTggMCAyODYuNSA4OHQxODcuNSAyMzBxNzAtNjIgMTY2LTYyIDEwNiAwIDE4MSA3NXQ3NSAxODFxMCA3NS00MSAxMzggMTI5IDMwIDIxMyAxMzQuNXQ4NCAyMzkuNXoiLz48L3N2Zz4='
                    );
                }
            
            }
        
        }
        add_settings_section(
            'ilab-media-features',
            'Media Cloud Features',
            null,
            'media-cloud'
        );
        register_setting( 'ilab-media-features', "mcloud-network-mode" );
        
        if ( $this->isNetworkModeAdmin ) {
            $this->networkTool = new NetworkTool( 'network', include ILAB_CONFIG_DIR . '/network.config.php', $this );
            $this->networkTool->registerSettings();
            $this->networkTool->registerMenu(
                'media-cloud',
                true,
                true,
                'media-cloud'
            );
            $this->wizard->registerMenu(
                'media-cloud',
                $networkMode,
                $networkAdminMenu,
                'media-cloud'
            );
        }
        
        if ( $this->isLocal || $this->isNetworkModeAdmin ) {
            add_submenu_page(
                'media-cloud',
                'Media Cloud Settings',
                'Settings',
                'manage_options',
                'media-cloud-settings',
                [ $this, 'renderSettings' ]
            );
        }
        foreach ( $this->tools as $key => $tool ) {
            register_setting( 'ilab-media-features', "mcloud-tool-enabled-{$key}" );
            if ( $key != 'troubleshooting' ) {
                add_settings_field(
                    "mcloud-tool-enabled-{$key}",
                    $tool->toolInfo['name'],
                    null,
                    'media-cloud',
                    'ilab-media-features',
                    [
                    'key' => $key,
                ]
                );
            }
        }
        $this->tools['troubleshooting']->registerMenu(
            'media-cloud',
            $networkMode,
            $networkAdminMenu,
            'media-cloud'
        );
        
        if ( $this->isLocal || $this->isNetworkModeAdmin ) {
            $this->insertSeparator( 'Settings' );
            $displayedSettings = [];
            if ( !empty($this->pinnedTools) ) {
                foreach ( $this->pinnedTools as $pinnedTool => $value ) {
                    if ( empty($this->tools[$pinnedTool]) ) {
                        continue;
                    }
                    $tool = $this->tools[$pinnedTool];
                    $displayedSettings[] = $pinnedTool;
                    $pinnedSlug = 'media-cloud-settings-pinned-' . $pinnedTool;
                    add_submenu_page(
                        'media-cloud',
                        $tool->toolInfo['name'] . ' Settings',
                        $tool->toolInfo['name'],
                        'manage_options',
                        $pinnedSlug,
                        [ $this, 'renderSettings' ]
                    );
                }
            }
            foreach ( $this->tools as $toolId => $tool ) {
                
                if ( $tool->envEnabled() && $tool->hasSettings() && !in_array( $toolId, $displayedSettings ) ) {
                    $displayedSettings[] = $toolId;
                    add_submenu_page(
                        'media-cloud',
                        $tool->toolInfo['name'] . ' Settings',
                        $tool->toolInfo['name'],
                        'manage_options',
                        'media-cloud-settings-' . $toolId,
                        [ $this, 'renderSettings' ]
                    );
                } else {
                    
                    if ( $toolId == 'integrations' ) {
                        $displayedSettings[] = $toolId;
                        add_submenu_page(
                            'media-cloud',
                            $tool->toolInfo['name'] . ' Settings',
                            $tool->toolInfo['name'],
                            'manage_options',
                            'media-cloud-settings-' . $toolId,
                            [ $this, 'renderSettings' ]
                        );
                    }
                
                }
            
            }
            $hiddenMenus = [];
            foreach ( $this->tools as $toolId => $tool ) {
                
                if ( $tool->hasSettings() && !in_array( $toolId, $displayedSettings ) ) {
                    $hiddenMenus[] = 'media-cloud-settings-' . $toolId;
                    add_submenu_page(
                        'media-cloud',
                        $tool->toolInfo['name'] . ' Settings',
                        null,
                        'manage_options',
                        'media-cloud-settings-' . $toolId,
                        [ $this, 'renderSettings' ]
                    );
                }
            
            }
            add_filter( 'submenu_file', function ( $submenu_file ) use( $hiddenMenus ) {
                global  $plugin_page ;
                if ( $plugin_page && isset( $hidden_submenus[$plugin_page] ) ) {
                    $submenu_file = 'media-cloud-settings';
                }
                foreach ( $hiddenMenus as $submenu ) {
                    remove_submenu_page( 'media-cloud', $submenu );
                }
                return $submenu_file;
            } );
        }
        
        foreach ( $this->tools as $key => $tool ) {
            register_setting( 'ilab-media-tools', "mcloud-tool-enabled-{$key}" );
            register_setting( $tool->optionsGroup(), "mcloud-tool-enabled-{$key}" );
            if ( $key != 'troubleshooting' ) {
                if ( $key === 'tasks' && $showTasks || $key !== 'tasks' ) {
                    $tool->registerMenu(
                        'media-cloud',
                        $networkMode,
                        $networkAdminMenu,
                        'media-cloud'
                    );
                }
            }
            $tool->registerSettings();
            if ( !empty($tool->toolInfo['related']) ) {
                foreach ( $tool->toolInfo['related'] as $relatedKey ) {
                    register_setting( $tool->optionsGroup(), "mcloud-tool-enabled-{$relatedKey}" );
                }
            }
        }
        do_action(
            'media-cloud/tools/added-tools',
            'media-cloud',
            $networkMode,
            $networkAdminMenu
        );
        do_action(
            'media-cloud/tools/added-tools-after',
            'media-cloud',
            $networkMode,
            $networkAdminMenu
        );
        $hideBatch = Environment::Option( 'mcloud-network-hide-batch', null, false );
        if ( !is_multisite() || !$hideBatch ) {
            foreach ( $this->tools as $key => $tool ) {
                $tool->registerBatchToolMenu( ( $this->isLocal && !empty(StorageToolSettings::instance()->useToolMenu) ? 'media-cloud-tools' : 'media-cloud' ), $networkMode, $networkAdminMenu );
            }
        }
        $this->insertHelpToolSeparator();
        add_submenu_page(
            'media-cloud',
            'Documentation',
            'Documentation',
            'manage_options',
            'https://docs.mediacloud.press/'
        );
        if ( media_cloud_licensing()->is_plan( 'pro' ) ) {
            add_submenu_page(
                'media-cloud',
                'Submit Issue',
                'Submit Issue',
                'manage_options',
                'https://support.mediacloud.press/'
            );
        }
        add_submenu_page(
            'media-cloud',
            'Preflight Beta',
            'Preflight Beta',
            'manage_options',
            'https://preflight.ju.mp'
        );
        foreach ( $this->tools as $key => $tool ) {
            $tool->registerHelpMenu( 'media-cloud', $networkMode, $networkAdminMenu );
        }
        if ( $this->isLocal || $this->isNetworkModeAdmin ) {
            if ( LicensingManager::CanTrack() ) {
                add_submenu_page(
                    'media-cloud',
                    'Opt-In Settings',
                    'Opt-In Settings',
                    'manage_options',
                    'media-cloud-settings-opt-in',
                    [ $this, 'renderSettings' ]
                );
            }
        }
        if ( !is_multisite() || is_network_admin() ) {
            $this->insertAccountSeparator();
        }
    }
    
    public function insertSeparator( $title = '', $parent = 'media-cloud' )
    {
        
        if ( empty($title) ) {
            add_submenu_page(
                $parent,
                '',
                '<span class="ilab-admin-separator-container"><span class="ilab-admin-separator"></span></span>',
                'manage_options',
                '#'
            );
        } else {
            $safe_title = sanitize_title( $title );
            add_submenu_page(
                $parent,
                '',
                '<span class="ilab-admin-separator-container ilab-admin-separator-' . $safe_title . '"><span class="ilab-admin-separator-title">' . $title . '</span><span class="ilab-admin-separator"></span></span>',
                'manage_options',
                '#'
            );
        }
    
    }
    
    public function insertToolSeparator()
    {
        if ( $this->hasInsertedToolSeparator ) {
            return;
        }
        $this->hasInsertedToolSeparator = true;
        $this->insertSeparator( 'Tools', 'media-cloud' );
    }
    
    public function insertBatchToolSeparator()
    {
        if ( $this->hasInsertedBatchToolSeparator ) {
            return;
        }
        $this->hasInsertedBatchToolSeparator = true;
        $this->insertSeparator( 'Tasks', ( $this->isLocal && !empty(StorageToolSettings::instance()->useToolMenu) ? 'media-cloud-tools' : 'media-cloud' ) );
    }
    
    public function insertHelpToolSeparator()
    {
        if ( $this->hasInsertedHelpToolSeparator ) {
            return;
        }
        $this->hasInsertedHelpToolSeparator = true;
        $this->insertSeparator( 'Support' );
    }
    
    public function insertAccountSeparator()
    {
        if ( $this->hasInsertedAccountSeparator ) {
            return;
        }
        $this->hasInsertedAccountSeparator = true;
        $this->insertSeparator( 'Account' );
    }
    
    //endregion
    //region Plugin installation
    /**
     * Perform plugin activation
     */
    public static function activate()
    {
        static::boot();
        foreach ( static::instance()->tools as $key => $tool ) {
            $tool->activate();
        }
        if ( empty(Environment::Option( 'mcloud-tool-enabled-storage', null, false )) ) {
            update_option( 'mcloud_show_wizard', true );
        }
    }
    
    /**
     * Perform plugin deactivation
     */
    public static function deactivate()
    {
        static::boot();
        foreach ( static::instance()->tools as $key => $tool ) {
            $tool->deactivate();
        }
        TasksTool::RemoveCompatibilityPlugin();
    }
    
    /**
     * Perform plugin removal
     */
    public static function uninstall()
    {
        static::boot();
        foreach ( static::instance()->tools as $key => $tool ) {
            $tool->uninstall();
        }
    }
    
    //endregion
    //region Tool Settings
    /**
     * Determines if a tool is enabled or not
     *
     * @param $toolName
     *
     * @return bool
     */
    public function toolEnabled( $toolName )
    {
        if ( isset( $this->tools[$toolName] ) ) {
            return $this->tools[$toolName]->enabled();
        }
        return false;
    }
    
    /**
     * Determines if a tool is enabled or not via environment settings
     *
     * @param $toolName
     *
     * @return bool
     */
    public function toolEnvEnabled( $toolName )
    {
        if ( isset( $this->tools[$toolName] ) ) {
            return $this->tools[$toolName]->envEnabled();
        }
        return false;
    }
    
    //endregion
    //region Multisite
    /**
     * @param Tool $tool
     */
    public function addMultisiteTool( $tool )
    {
        $this->multisiteTools[] = $tool;
    }
    
    /**
     * @return Tool[]
     */
    public function multisiteTools()
    {
        return $this->multisiteTools;
    }
    
    //endregion
    //region Processing
    public function updateNetworkOptions()
    {
        if ( !current_user_can( 'manage_network_options' ) ) {
            wp_die( 'You don\\t have the privileges to do this.' );
        }
        $page_slug = $_POST['option_page'];
        check_admin_referer( $page_slug . '-options' );
        global  $new_whitelist_options ;
        global  $wp_version ;
        $whitelistFilter = ( version_compare( $wp_version, '5.5', '>=' ) ? 'allowed_options' : 'whitelist_options' );
        $new_whitelist_options = apply_filters( $whitelistFilter, $new_whitelist_options );
        $options = $new_whitelist_options[$page_slug];
        foreach ( $options as $option ) {
            
            if ( isset( $_POST[$option] ) ) {
                
                if ( $option == 'mcloud-network-mode' ) {
                    update_site_option( $option, $_POST[$option] == 'on' );
                } else {
                    Environment::UpdateOption( $option, $_POST[$option] );
                }
            
            } else {
                Environment::UpdateOption( $option, null );
            }
        
        }
        //https://nicetits.xyz/wp-admin/network/admin.php?page=media-cloud-network
        $url = add_query_arg( [
            'updated' => 'true',
        ], $_POST['_wp_http_referer'] );
        wp_redirect( $url );
        exit;
    }
    
    //endregion
    //region Render Settings
    /**
     * Render the options page
     */
    public function renderSettings()
    {
        global  $media_cloud_licensing ;
        
        if ( !is_multisite() && ($media_cloud_licensing->is_plan__premium_only( 'multisite_basic', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_pro', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_unlimited', true )) ) {
            echo  View::render_view( 'base/wrong-license', [
                'title' => 'Media Cloud ' . MEDIA_CLOUD_VERSION,
            ] ) ;
        } else {
            global  $wp_settings_sections, $wp_settings_fields ;
            
            if ( !empty($_GET['tab']) && in_array( $_GET['tab'], array_keys( $this->tools ) ) ) {
                $tab = $_GET['tab'];
            } else {
                
                if ( strpos( $_GET['page'], 'media-cloud-settings-' ) === 0 ) {
                    $tab = str_replace( 'media-cloud-settings-', '', $_GET['page'] );
                    if ( strpos( $tab, 'pinned-' ) === 0 ) {
                        $tab = str_replace( 'pinned-', '', $tab );
                    }
                } else {
                    $tab = array_keys( $this->tools )[0];
                }
            
            }
            
            $selectedTool = $this->tools[$tab];
            $page = $selectedTool->optionsPage();
            $group = $selectedTool->optionsGroup();
            $sections = [];
            foreach ( (array) $wp_settings_sections[$page] as $section ) {
                if ( !isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
                    continue;
                }
                $help = arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/help", null );
                
                if ( is_array( $help ) && !empty($help) ) {
                    
                    if ( !is_array( $help['data'] ) ) {
                        $data = $help['data'];
                        $help['data'] = $selectedTool->{$data}();
                    }
                
                } else {
                    $help = null;
                }
                
                $sections[] = [
                    'title'       => $section['title'],
                    'id'          => $section['id'],
                    'doc_link'    => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/doc_link", null ),
                    'help'        => $help,
                    'description' => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/description", null ),
                    'hide-save'   => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/hide-save", false ),
                    'custom'      => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/custom", false ),
                    'callback'    => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/callback", false ),
                ];
            }
            echo  View::render_view( 'base/settings', [
                'title'      => 'All Settings',
                'tab'        => $tab,
                'tools'      => $this->tools,
                'tool'       => $selectedTool,
                'group'      => $group,
                'page'       => $page,
                'jump_links' => arrayPath( $selectedTool->toolInfo, "settings/jump-links", true ),
                'manager'    => $this,
                'sections'   => $sections,
            ] ) ;
        }
    
    }
    
    public function renderSupport()
    {
        echo  View::render_view( 'base/support', [] ) ;
    }
    
    public function renderFeatureSettings()
    {
        global  $media_cloud_licensing ;
        
        if ( !is_multisite() && ($media_cloud_licensing->is_plan__premium_only( 'multisite_basic', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_pro', true ) || $media_cloud_licensing->is_plan__premium_only( 'multisite_unlimited', true )) ) {
            echo  View::render_view( 'base/wrong-license', [
                'title' => 'Media Cloud ' . MEDIA_CLOUD_VERSION,
            ] ) ;
        } else {
            echo  View::render_view( 'base/features', [
                'title'       => 'Media Cloud ' . MEDIA_CLOUD_VERSION,
                'group'       => 'ilab-media-features',
                'page'        => 'media-cloud',
                'networkMode' => Environment::NetworkMode(),
                'tools'       => $this->tools,
                'manager'     => $this,
            ] ) ;
        }
    
    }
    
    public function renderNetworkSettings()
    {
        echo  View::render_view( 'base/network', [
            'title'       => 'Media Cloud Network Options',
            'group'       => 'ilab-media-features',
            'page'        => 'media-cloud',
            'networkMode' => Environment::NetworkMode(),
            'manager'     => $this,
        ] ) ;
    }
    
    public function renderMultisiteLanding()
    {
        TasksTool::InstallCompatibilityPlugin();
        echo  View::render_view( 'base/multisite-landing', [
            'title'   => 'Media Cloud',
            'manager' => $this,
        ] ) ;
    }
    
    //endregion
    //region Tool Pinning
    private function handlePinTool()
    {
        if ( empty($_POST['tool']) ) {
            json_response( [
                'status'  => 'error',
                'message' => 'Missing tool parameter.',
            ] );
        }
        $tool = $_POST['tool'];
        $pinned = false;
        
        if ( empty($this->pinnedTools[$tool]) ) {
            $this->pinnedTools[$tool] = true;
            $pinned = true;
        } else {
            unset( $this->pinnedTools[$tool] );
        }
        
        Environment::UpdateOption( 'mcloud-pinned-tools', $this->pinnedTools );
        json_response( [
            'status' => ( $pinned ? 'pinned' : 'unpinned' ),
            'link'   => admin_url( "admin.php?page=media-cloud-settings&tool={$tool}" ),
        ] );
    }

}