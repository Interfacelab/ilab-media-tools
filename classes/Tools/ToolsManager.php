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

use  ILAB\MediaCloud\Documentation\Docs ;
use  ILAB\MediaCloud\Tasks\BatchManager ;
use  ILAB\MediaCloud\Tools\Debugging\System\SystemCompatibilityTool ;
use  ILAB\MediaCloud\Tools\Network\NetworkTool ;
use  ILAB\MediaCloud\Utilities\Environment ;
use  ILAB\MediaCloud\Utilities\NoticeManager ;
use  ILAB\MediaCloud\Utilities\View ;
use function  ILAB\MediaCloud\Utilities\arrayPath ;
use function  ILAB\MediaCloud\Utilities\json_response ;

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
    /** @var BatchTool[]  */
    private  $multisiteBatchTools = array() ;
    private  $docs = null ;
    //endregion
    //region Constructor
    public function __construct()
    {
        MigrationsManager::instance()->migrate();
        $this->tools = [];
        $this->pinnedTools = Environment::Option( 'mcloud-pinned-tools', null, [] );
        foreach ( static::$registeredTools as $toolName => $toolInfo ) {
            $className = $toolInfo['class'];
            $this->tools[$toolName] = new $className( $toolName, $toolInfo, $this );
            // Register Batch Tools
            if ( !empty($toolInfo['batchTools']) ) {
                foreach ( $toolInfo['batchTools'] as $batchToolClass ) {
                    $batchID = call_user_func( [ $batchToolClass, 'BatchIdentifier' ] );
                    $processClass = call_user_func( [ $batchToolClass, 'BatchProcessClassName' ] );
                    if ( !empty($processClass) && !empty($batchID) ) {
                        BatchManager::registerBatchClass( $batchID, $processClass );
                    }
                }
            }
            // Register CLI Commands
            if ( !empty($toolInfo['CLI']) && defined( 'WP_CLI' ) && class_exists( '\\WP_CLI' ) ) {
                foreach ( $toolInfo['CLI'] as $cliClass ) {
                    call_user_func( [ $cliClass, 'Register' ] );
                }
            }
        }
        if ( empty(static::$registeredTools['troubleshooting']) ) {
            $this->tools['troubleshooting'] = new SystemCompatibilityTool( 'troubleshooting', static::$registeredTools['troubleshooting'], $this );
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
        $runTime = Environment::Option( 'ilab_media_tools_run_time', null, 0 );
        
        if ( $runTime == 0 ) {
            Environment::UpdateOption( 'ilab_media_tools_run_time', microtime( true ) );
        } else {
            if ( microtime( true ) - floatval( $runTime ) > 1209600 ) {
                NoticeManager::instance()->displayAdminNotice(
                    'info',
                    "Thanks for using Media Cloud!  If you like it, please <a href='https://wordpress.org/support/plugin/ilab-media-tools/reviews/#new-post' target=_blank>leave a review</a>.  Thank you!",
                    true,
                    'ilab-media-tools-nag-notice'
                );
            }
        }
        
        add_action( 'admin_enqueue_scripts', function () {
            wp_enqueue_script(
                'ilab-settings-js',
                ILAB_PUB_JS_URL . '/ilab-settings.js',
                [ 'jquery' ],
                null,
                true
            );
            wp_enqueue_style( 'ilab-media-cloud-css', ILAB_PUB_CSS_URL . '/ilab-media-cloud.css' );
        } );
        
        if ( is_admin() ) {
            add_action( 'wp_ajax_ilab_pin_tool', function () {
                $this->handlePinTool();
            } );
            add_action( 'wp_ajax_ilab_hide_upgrade_bug', function () {
                update_option( 'ilab_media_cloud_hide_upgrade_bug', time() );
                json_response( [
                    'status' => 'ok',
                ] );
            } );
            if ( defined( 'PHP_MAJOR_VERSION' ) && PHP_MAJOR_VERSION >= 7 ) {
                $this->docs = new Docs();
            }
        }
        
        BatchManager::boot();
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
        if ( static::$booted ) {
            return;
        }
        static::$booted = true;
        global  $media_cloud_licensing ;
        Environment::Boot();
        // Register Tools
        ToolsManager::registerTool( "storage", include ILAB_CONFIG_DIR . '/storage.config.php' );
        ToolsManager::registerTool( "imgix", include ILAB_CONFIG_DIR . '/imgix.config.php' );
        ToolsManager::registerTool( "vision", include ILAB_CONFIG_DIR . '/vision.config.php' );
        ToolsManager::registerTool( "crop", include ILAB_CONFIG_DIR . '/crop.config.php' );
        ToolsManager::registerTool( "debugging", include ILAB_CONFIG_DIR . '/debugging.config.php' );
        ToolsManager::registerTool( "troubleshooting", include ILAB_CONFIG_DIR . '/troubleshooting.config.php' );
        ToolsManager::registerTool( "batch-processing", include ILAB_CONFIG_DIR . '/batch-processing.config.php' );
        do_action( 'media-cloud/tools/register-tools' );
        // Make sure the NoticeManager is initialized
        NoticeManager::instance();
        // Get the party started
        ToolsManager::instance();
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
    public function addMenus( $networkMode, $networkAdminMenu )
    {
        global  $media_cloud_licensing ;
        $networkMode = false;
        $networkAdminMenu = false;
        $isNetworkModeAdmin = false;
        $isLocal = true;
        
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
        if ( $isLocal || $isNetworkModeAdmin ) {
            
            if ( !empty($this->pinnedTools) ) {
                $this->insertSeparator( 'Pinned Settings' );
                foreach ( $this->pinnedTools as $pinnedTool => $value ) {
                    if ( empty($this->tools[$pinnedTool]) ) {
                        continue;
                    }
                    $tool = $this->tools[$pinnedTool];
                    add_submenu_page(
                        'media-cloud',
                        $tool->toolInfo['name'] . ' Settings',
                        $tool->toolInfo['name'],
                        'manage_options',
                        'media-cloud-settings&tab=' . $pinnedTool,
                        [ $this, 'renderSettings' ]
                    );
                }
            }
        
        }
        foreach ( $this->tools as $key => $tool ) {
            register_setting( 'ilab-media-tools', "mcloud-tool-enabled-{$key}" );
            register_setting( $tool->optionsGroup(), "mcloud-tool-enabled-{$key}" );
            $tool->registerMenu( 'media-cloud', $networkMode, $networkAdminMenu );
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
        
        if ( $isLocal || $isNetworkModeAdmin ) {
            $this->insertHelpToolSeparator();
            add_submenu_page(
                'media-cloud',
                'Plugin Support',
                'Support',
                'manage_options',
                'https://talk.mediacloud.press/'
            );
            if ( defined( 'PHP_MAJOR_VERSION' ) && PHP_MAJOR_VERSION >= 7 ) {
                $this->docs->registerAdminMenu( 'media-cloud' );
            }
            foreach ( $this->tools as $key => $tool ) {
                $tool->registerHelpMenu( 'media-cloud', $networkMode, $networkAdminMenu );
            }
        }
        
        $this->insertAccountSeparator();
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
     * @param BatchTool $batchTool
     */
    public function addMultisiteBatchTool( $batchTool )
    {
        $this->multisiteBatchTools[] = $batchTool;
    }
    
    /**
     * @return Tool[]
     */
    public function multisiteTools()
    {
        return $this->multisiteTools;
    }
    
    /**
     * @return BatchTool[]
     */
    public function multisiteBatchTools()
    {
        return $this->multisiteBatchTools;
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
        global  $wp_settings_sections, $wp_settings_fields ;
        
        if ( !empty($_GET['tab']) && in_array( $_GET['tab'], array_keys( $this->tools ) ) ) {
            $tab = $_GET['tab'];
        } else {
            $tab = array_keys( $this->tools )[0];
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
                'help'        => $help,
                'description' => arrayPath( $selectedTool->toolInfo, "settings/groups/{$section['id']}/description", null ),
            ];
        }
        echo  View::render_view( 'base/settings', [
            'title'    => 'All Settings',
            'tab'      => $tab,
            'tools'    => $this->tools,
            'tool'     => $selectedTool,
            'group'    => $group,
            'page'     => $page,
            'manager'  => $this,
            'sections' => $sections,
        ] ) ;
    }
    
    public function renderSupport()
    {
        echo  View::render_view( 'base/support', [] ) ;
    }
    
    public function renderFeatureSettings()
    {
        echo  View::render_view( 'base/features', [
            'title'       => 'Media Cloud ' . MEDIA_CLOUD_VERSION,
            'group'       => 'ilab-media-features',
            'page'        => 'media-cloud',
            'networkMode' => Environment::NetworkMode(),
            'tools'       => $this->tools,
            'manager'     => $this,
        ] ) ;
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