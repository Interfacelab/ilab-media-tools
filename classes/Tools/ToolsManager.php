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
namespace ILAB\MediaCloud\Tools;

use  ILAB\MediaCloud\Tasks\TaskManager ;
use  ILAB\MediaCloud\Tools\Network\NetworkTool ;
use  ILAB\MediaCloud\Utilities\Environment ;
use  ILAB\MediaCloud\Utilities\LicensingManager ;
use  ILAB\MediaCloud\Utilities\NoticeManager ;
use  ILAB\MediaCloud\Utilities\Tracker ;
use  ILAB\MediaCloud\Utilities\View ;
use function  ILAB\MediaCloud\Utilities\arrayPath ;
use function  ILAB\MediaCloud\Utilities\json_response ;
use  ILAB\MediaCloud\Wizard\SetupWizard ;

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
    //endregion
    //region Constructor
    public function __construct()
    {
        MigrationsManager::instance()->migrate();
        $this->tools = [];
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
            $links[] = "<a href='https://discourse.interfacelab.io' target='_blank'>Support</a>";
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
        
        if ( !empty(constant( 'MCLOUD_IS_BETA' )) ) {
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
        MigrationsManager::instance()->displayMigrationErrors();
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
            ToolsManager::registerTool( "vision", include ILAB_CONFIG_DIR . '/vision.config.php' );
            ToolsManager::registerTool( "crop", include ILAB_CONFIG_DIR . '/crop.config.php' );
            ToolsManager::registerTool( "debugging", include ILAB_CONFIG_DIR . '/debugging.config.php' );
            ToolsManager::registerTool( "troubleshooting", include ILAB_CONFIG_DIR . '/troubleshooting.config.php' );
            ToolsManager::registerTool( "batch-processing", include ILAB_CONFIG_DIR . '/batch-processing.config.php' );
            ToolsManager::registerTool( "tasks", include ILAB_CONFIG_DIR . '/tasks.config.php' );
            if ( LicensingManager::CanTrack() ) {
                ToolsManager::registerTool( "opt-in", include ILAB_CONFIG_DIR . '/opt-in.config.php' );
            }
            do_action( 'media-cloud/tools/register-tools' );
            if ( LicensingManager::ScreenSharingEnabled() ) {
                add_action( 'admin_footer', function () {
                    echo  View::render_view( 'support.screen-sharing', [] ) ;
                } );
            }
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
        $networkMode = false;
        $networkAdminMenu = false;
        $isNetworkModeAdmin = false;
        $isLocal = true;
        $showMenu = true;
        
        if ( is_multisite() ) {
            $hideMenu = Environment::Option( 'media-cloud-network-hide', null, false );
            if ( $hideMenu && !is_network_admin() ) {
                $showMenu = false;
            }
        }
        
        if ( $showMenu ) {
            
            if ( $isLocal || $isNetworkModeAdmin ) {
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
                $this->wizard->registerMenu( 'media-cloud', $networkMode, $networkAdminMenu );
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
        add_settings_section(
            'ilab-media-features',
            'Media Cloud Features',
            null,
            'media-cloud'
        );
        register_setting( 'ilab-media-features', "mcloud-network-mode" );
        
        if ( $isNetworkModeAdmin ) {
            $this->networkTool = new NetworkTool( 'network', include ILAB_CONFIG_DIR . '/network.config.php', $this );
            $this->networkTool->registerSettings();
            $this->networkTool->registerMenu( 'media-cloud', true, true );
            $this->wizard->registerMenu( 'media-cloud', $networkMode, $networkAdminMenu );
        }
        
        if ( $isLocal || $isNetworkModeAdmin ) {
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
        $this->tools['troubleshooting']->registerMenu( 'media-cloud', $networkMode, $networkAdminMenu );
        
        if ( $isLocal || $isNetworkModeAdmin ) {
            $this->insertSeparator( 'Settings' );
            $displayedSettings = [];
            if ( !empty($this->pinnedTools) ) {
                foreach ( $this->pinnedTools as $pinnedTool => $value ) {
                    if ( empty($this->tools[$pinnedTool]) ) {
                        continue;
                    }
                    $tool = $this->tools[$pinnedTool];
                    $displayedSettings[] = $pinnedTool;
                    
                    if ( $this->tools[$pinnedTool]->envEnabled() && $tool->hasSettings() ) {
                        $pinnedSlug = 'media-cloud-settings-' . $pinnedTool;
                    } else {
                        $pinnedSlug = 'media-cloud-settings-pinned-' . $pinnedTool;
                        //.'&pinned=true';
                    }
                    
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
                $tool->registerMenu( 'media-cloud', $networkMode, $networkAdminMenu );
            }
            $tool->registerSettings();
            if ( !empty($tool->toolInfo['related']) ) {
                foreach ( $tool->toolInfo['related'] as $relatedKey ) {
                    register_setting( $tool->optionsGroup(), "mcloud-tool-enabled-{$relatedKey}" );
                }
            }
        }
        $hideBatch = Environment::Option( 'mcloud-network-hide-batch', null, false );
        if ( !is_multisite() || !$hideBatch ) {
            foreach ( $this->tools as $key => $tool ) {
                $tool->registerBatchToolMenu( 'media-cloud', $networkMode, $networkAdminMenu );
            }
        }
        $this->insertHelpToolSeparator();
        add_submenu_page(
            'media-cloud',
            'Documentation',
            'Documentation',
            'manage_options',
            'https://support.mediacloud.press/'
        );
        add_submenu_page(
            'media-cloud',
            'Plugin Support',
            'Forums',
            'manage_options',
            'https://forums.mediacloud.press/'
        );
        if ( media_cloud_licensing()->is_plan( 'pro' ) ) {
            add_submenu_page(
                'media-cloud',
                'Submit Issue',
                'Submit Issue',
                'manage_options',
                'https://support.mediacloud.press/submit-issue/'
            );
        }
        foreach ( $this->tools as $key => $tool ) {
            $tool->registerHelpMenu( 'media-cloud', $networkMode, $networkAdminMenu );
        }
        if ( $isLocal || $isNetworkModeAdmin ) {
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
    
    public function insertSeparator( $title = '' )
    {
        
        if ( empty($title) ) {
            add_submenu_page(
                'media-cloud',
                '',
                '<span class="ilab-admin-separator-container"><span class="ilab-admin-separator"></span></span>',
                'manage_options',
                '#'
            );
        } else {
            $safe_title = sanitize_title( $title );
            add_submenu_page(
                'media-cloud',
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
        $this->insertSeparator( 'Tools' );
    }
    
    public function insertBatchToolSeparator()
    {
        if ( $this->hasInsertedBatchToolSeparator ) {
            return;
        }
        $this->hasInsertedBatchToolSeparator = true;
        $this->insertSeparator( 'Batch Tools' );
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
        $new_whitelist_options = apply_filters( 'whitelist_options', $new_whitelist_options );
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