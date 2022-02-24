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

namespace MediaCloud\Plugin\Tools;

use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Prefixer;
use function MediaCloud\Plugin\Utilities\arrayPath;


if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Base class for media tools
 */
abstract class Tool {
	use SettingsTrait;

    //region Variables

    /** @var array Parsed settings from config */
    protected $settingSections;

    /** @var string Name of the tool */
    public  $toolName;

    /** @var bool Determines if bad plugins are installed. */
    protected $badPluginsInstalled = false;

    /** @var ToolsManager Tool manager that owns this tool's admin */
    protected $toolManager;

    /** @var array Information about this tool */
    public $toolInfo;

	/** @var string The name of the environment variable */
    protected $env_variable;

	/** @var bool Only display the settings page when the tool is enabled */
    protected $only_when_enabled;

    protected $actions = [];

    /** @var bool Determines if settings did change. */
    private $settingsDidChange = false;

    //endregion


    //region Constructor

    /**
     * Creates a new instance.  Subclasses should do any setup dependent on being enabled in setup()
     * @param $toolName
     * @param $toolInfo
     * @param $toolManager
     */
    public function __construct($toolName, $toolInfo, $toolManager) {
        $this->toolName=$toolName;
        $this->settingSections=[];
        $this->toolInfo=$toolInfo;
        $this->toolManager=$toolManager;
	    $this->only_when_enabled = false;

	    if (is_admin()) {
            $this->actions = arrayPath($toolInfo, 'actions', []);
            foreach($this->actions as $key => $action) {
                if (!method_exists($this, $action['method'])) {
                    unset($this->actions[$key]);
                } else {
                    $actionKey = str_replace('-', '_', $key);
                    add_action('wp_ajax_'.$actionKey, function() use ($actionKey, $action) {
                        check_ajax_referer($actionKey, 'nonce');
                        call_user_func([$this, $action['method']]);
                    });
                }
            }

        }

        if (isset($toolInfo['env'])) {
        	$this->env_variable = $toolInfo['env'];
        }

        if (isset($toolInfo['settings']) && !empty($toolInfo['settings'])) {
	        if (isset($toolInfo['settings']['options-page'])) {
		        $this->options_page = $toolInfo['settings']['options-page'];
	        }

	        if (isset($toolInfo['settings']['only_when_enabled'])) {
		        $this->only_when_enabled = $toolInfo['settings']['only_when_enabled'];
	        }

	        if (isset($toolInfo['settings']['options-group'])) {
		        $this->options_group = $toolInfo['settings']['options-group'];
	        }
        }

        if (isset($toolInfo['helpers'])) {
            foreach($toolInfo['helpers'] as $helper) {
                require_once(ILAB_HELPERS_DIR.'/'.$helper);
            }
        }

        if (is_admin()) {
            add_action('wp_ajax_mcloud_preview_upload_path', [$this, 'doPreviewUploadPath']);
        }
    }

    //endregion

    //region Plugin Compatibility Testing

    /**
     * Tests for any plugins that are "bad" or cause Media Cloud to misbehave.
     */
    protected function testForBadPlugins() {
        if (!$this->enabled()) {
            return;
        }

        if (isset($this->toolInfo['badPlugins'])) {
            $installedBad = [];
            foreach($this->toolInfo['badPlugins'] as $name => $plugin) {
	            $isInstalled = false;

	            if (!empty($plugin['plugin'])) {
		            $isInstalled = is_plugin_active($plugin['plugin']);
	            } else if (!empty($plugin['class'])) {
		            $isInstalled = class_exists($plugin['class']);
	            } else if (!empty($plugin['function'])) {
		            $isInstalled = function_exists($plugin['function']);
	            }

	            if (!empty($isInstalled)) {
		            $this->badPluginsInstalled = true;
		            $installedBad[$name] = $plugin;
	            }
            }

            if (count($installedBad) > 0) {
                add_action( 'admin_notices', function () use ($installedBad) {
                    ?>
                    <div class="notice notice-error" style="padding:10px;">
                        <div style="text-transform: uppercase; font-weight:bold; opacity: 0.8; margin-bottom: 0; padding-bottom: 0">Media Cloud</div>
                        <p><?php echo "The following plugins don't work with Media Cloud ".$this->toolInfo['name']." features and can cause serious issues.  Media Cloud ".$this->toolInfo['name']." features have been disabled until these plugins have been deactivated:" ?></p>
                        <?php $this->generatePluginTable($installedBad) ?>
                    </div>
                    <?php
                } );
            }
        }
    }

    /**
     * Tests for plugins whose functionality is either superseded or disabled by Media Cloud
     */
    protected function testForUselessPlugins() {
        if (!$this->enabled()) {
            return;
        }

        if (isset($this->toolInfo['incompatiblePlugins'])) {
            $installedBad = [];
            $installedBadNames = [];

            foreach($this->toolInfo['incompatiblePlugins'] as $name => $plugin) {
                $isInstalled = false;

                if (!empty($plugin['plugin'])) {
                    $isInstalled = is_plugin_active($plugin['plugin']);
                } else if (!empty($plugin['class'])) {
	                $isInstalled = class_exists($plugin['class']);
                } else if (!empty($plugin['function'])) {
	                $isInstalled = function_exists($plugin['function']);
                }

	            if (!empty($isInstalled)) {
		            $installedBad[$name] = $plugin;
		            $installedBadNames[] = sanitize_title($name);
	            }
            }

            if (count($installedBad) > 0) {
                $dismissibleID = 'useless-plugins-'.implode('-', $installedBadNames).'-7';
                if (NoticeManager::instance()->isAdminNoticeActive($dismissibleID)) {
                    add_action( 'admin_notices', function () use ($installedBad, $dismissibleID) {
                        ?>
                        <div data-dismissible="<?php echo $dismissibleID ?>" class="notice notice-warning is-dismissible" style="padding:10px;">
                            <div style="text-transform: uppercase; font-weight:bold; opacity: 0.8; margin-bottom: 0; padding-bottom: 0">Media Cloud</div>
                            <p>The following plugins don't work well with <?php echo $this->toolInfo['name'] ?> or they don't work as you might expect they should.  Consider deactivating them or finding an alternative that works better:</p>
                            <?php $this->generatePluginTable($installedBad) ?>
                        </div>
                        <?php
                    } );
                }
            }
        }
    }

    /**
     * Generates html for the list of bad plugins
     *
     * @param $installedBad
     */
    private function generatePluginTable($installedBad) {
        ?>
        <ul style="padding: 15px; background-color: #EAEAEA;">
            <?php foreach($installedBad as $name => $plugin) : ?>
                <li style="margin-bottom: 10px;">
                    <div style="display:flex; align-items: center; font-weight:bold; margin-bottom: 10px;"><?php echo $name ?> <?php if(isset($plugin['plugin'])):?><a style="margin-left: 15px;" class="button button-small" href="<?php echo $this->generateDeactivateLink($name, $plugin['plugin'])?>">Deactivate</a><?php endif; ?></div>
                    <cite><?php echo $plugin['description'] ?></cite>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Generates a deactivate link for a plugin
     *
     * @param $pluginName
     * @param $plugin
     * @return string
     */
    private function generateDeactivateLink($pluginName, $plugin) {
        $plugin = str_replace( '\/', '%2F', $plugin );

        $url = sprintf( admin_url( 'plugins.php?action=deactivate&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
        $_REQUEST['plugin'] = $plugin;
        $url = wp_nonce_url( $url,  'deactivate-plugin_' . $plugin );
        return $url;
    }


    //endregion

    //region Setup/Install

    /**
     * Perform any setup
     */
    public function setup() {
    }

    /**
     * Performs any activation tasks when the plugin is activated
     */
    public function activate() {
    }

	/**
	 * Performs any deactivation tasks when the plugin is deactivated
	 */
	public function deactivate() {
	}

    /**
     * Performs any uninstall tasks when the plugin is uninstalled
     */
    public function uninstall() {
    }

    //endregion

    //region Properties

    public function pinned() {
        return !empty(ToolsManager::instance()->pinnedTools[$this->toolName]);
    }

    /**
     * List of actions
     * @return array
     */
    public function actions() {
        return $this->actions;
    }

	/**
     * Determines if the tool has user editable settings or not
	 * @return bool
	 */
    public function hasSettings() {
        return false;
    }


	/**
	 * Determines if the tool has a setup wizard
	 * @return bool
	 */
	public function hasWizard() {
		return false;
	}


	/**
	 * The URL for the wizard
	 * @return bool
	 */
	public function wizardLink() {
		return false;
	}

	/**
     * Determines if this tool installs, or wants to install, items in the admin menu bar
	 * @return bool
	 */
    public function hasAdminBarMenu() {
        return false;
    }

	/**
	 * Determines if the plugin is always enabled
	 *
	 * @return bool
	 */
	public function alwaysEnabled() {
	    return false;
	}

    /**
     * Determines if the plugin is set to enabled, regardless if it is really enabled or not
     *
     * @return bool
     */
    public function envEnabled() {
        if ($this->badPluginsInstalled) {
            return false;
        }

        return Environment::Option("mcloud-tool-enabled-$this->toolName", $this->env_variable, false);
    }

    private function notifyMissingDependencies($shouldBeEnabled, $toolId) {
        $problemTool = $this->toolManager->tools[$toolId];
        $problemToolName = $problemTool->toolInfo['name'];
        $problemUrl = admin_url('admin.php?page=media-cloud-settings&tab='.$toolId);
	    $toolUrl = admin_url('admin.php?page=media-cloud-settings&tab='.$this->toolInfo['id']);
	    $settingsUrl = admin_url('admin.php?page=media-cloud');

        if ($shouldBeEnabled) {
            $message = "<a href='{$toolUrl}'>{$this->toolInfo['name']}</a> requires that <a href='{$problemUrl}'>$problemToolName</a> be enabled and working.  You can enable that feature <a href='{$settingsUrl}'>here</a>.";
        } else {
	        $message = "<a href='{$toolUrl}'>{$this->toolInfo['name']}</a> cannot work until <a href='{$problemUrl}'>$problemToolName</a> is disabled.  You can disable that feature <a href='{$settingsUrl}'>here</a>.";
        }

	    NoticeManager::instance()->displayAdminNotice('warning', $message, true, "media-cloud-{$this->toolInfo['id']}-bad-dep-{$toolId}", 1);
    }

    /**
     * Determines if this tool is enabled or not
     */
    public function enabled() {
        if (!$this->envEnabled()) {
            return false;
        }

        if (isset($this->toolInfo['dependencies']))  {
            foreach($this->toolInfo['dependencies'] as $dep)  {
                if (!is_array($dep) && (strpos($dep, '!') === 0)) {
                    $dep = trim($dep, '!');
                    if ($this->toolManager->toolEnvEnabled($dep)) {
                        $this->notifyMissingDependencies(false, $dep);
                        return false;
                    }
                }
            }

            foreach($this->toolInfo['dependencies'] as $dep)  {
            	if (is_array($dep)) {
            		$enabledCount = 0;
					foreach($dep as $toolDep) {
						if ($this->toolManager->toolEnabled($toolDep)) {
							$enabledCount++;
							break;
						}
					}

					if ($enabledCount == 0) {
						return false;
					}
	            } else {
            	    if (strpos($dep, '!') === 0) {
            	        continue;
                    } else {
                        if (!$this->toolManager->toolEnabled($dep)) {
	                        $this->notifyMissingDependencies(true, $dep);
                            return false;
                        }
                    }
	            }
            }
        }

        return true;
    }

    //endregion

    //region Admin Menu Bar

	/**
     * Allows a tool to add entries to the admin bar
     *
	 * @param \WP_Admin_Bar $adminBar
	 */
    public function addAdminMenuBarItems($adminBar) {

    }

    //endregion


    //region Settings

    /**
     * Register any settings defined in the config
     */
    public function registerSettings() {
        if (!isset($this->toolInfo['settings']['groups'])) {
            return;
        }

        $watch = !empty($this->toolInfo['settings']['watch']);
        if ($watch) {
            add_action("pre_update_option", function ($value, $option, $old_value) {
                if (!get_transient("settings_changed_".$this->toolName)) {
                    set_transient("settings_changed_".$this->toolName, true);
                }

                return $value;
            }, 10, 3);
        }

        global $wp_version;

        $groups=$this->toolInfo['settings']['groups'];
        foreach($groups as $group => $groupInfo)  {
            $groupWatch = !empty($groupInfo['watch']);

            $this->registerSettingsSection($group, $groupInfo['title'], arrayPath($groupInfo, 'description', null));
            if (isset($groupInfo['options']))  {
                foreach($groupInfo['options'] as $option => $optionInfo)  {
                    if (isset($optionInfo['wp_version'])) {
                        $comparison = $optionInfo['wp_version'];
                        if (!version_compare($wp_version, $comparison[1], $comparison[0])) {
                            continue;
                        }
                    }

                    if (!empty($optionInfo['multisite'])) {
                        if (!is_multisite()) {
                            continue;
                        }
                    }

                    if (!empty($optionInfo['plan'])) {
                        if (!media_cloud_licensing()->is_plan($optionInfo['plan'])) {
                            continue;
                        }
                    }

                    $optionWatch = !empty($optionInfo['watch']);

                    $this->registerSetting($option);

                    if ($groupWatch || $optionWatch) {
                        add_action("update_option_$option", function ($setting, $oldValue=null, $newValue=null) {
                            if (!get_transient("settings_changed_".$this->toolName)) {
                                set_transient("settings_changed_".$this->toolName, true);
                            }
                        }, 10, 3);
                    }

                    if (isset($optionInfo['type']))  {
                    	$description = arrayPath($optionInfo,'description',null);
                    	$conditions = arrayPath($optionInfo,'conditions',null);
                    	$placeholder = arrayPath($optionInfo,'placeholder',null);
                    	$default = arrayPath($optionInfo,'default',null);
                        $increment = arrayPath($optionInfo,'increment',null);
                        $min = arrayPath($optionInfo,'min',1);
                        $max = arrayPath($optionInfo,'max',1000);

                        switch($optionInfo['type']) {
	                        case 'text-field':
		                        $this->registerTextFieldSetting($option,$optionInfo['title'],$group, $description, $placeholder, $conditions, isset($optionInfo['default']) ? $optionInfo['default'] : null);
		                        break;
	                        case 'webhook':
	                            $this->registerWebhookSetting($option, $optionInfo['title'], $group, !empty($optionInfo['editable']), $description, $conditions, isset($optionInfo['default']) ? $optionInfo['default'] : null);
		                        break;
	                        case 'upload-path':
		                        $this->registerUploadPathFieldSetting($option,$optionInfo['title'],$group,$description,$placeholder,$conditions);
		                        break;
	                        case 'subsite-upload-paths':
		                        $this->registerSubsiteUploadPathsFieldSetting($option,$optionInfo['title'],$group,$description,$placeholder,$conditions);
		                        break;
                            case 'text-area':
                                $this->registerTextAreaFieldSetting($option,$optionInfo['title'],$group,$description, $placeholder, $conditions);
                                break;
                            case 'password':
                                $this->registerPasswordFieldSetting($option,$optionInfo['title'],$group,$description, $placeholder, $conditions);
                                break;
                            case 'checkbox':
                                $this->registerCheckboxFieldSetting($option,$optionInfo['title'],$group,$description, $default, $conditions);
                                break;
                            case 'number':
                                $this->registerNumberFieldSetting($option,$optionInfo['title'],$group,$description, $default, $conditions, $min, $max, $increment);
                                break;
	                        case 'select':
		                        $this->registerSelectSetting($option, $optionInfo['options'], $optionInfo['title'], $group, isset($optionInfo['default']) ? $optionInfo['default'] : null,  $description, $conditions);
		                        break;
                            case 'dynamic-select':
                                $this->registerDynamicSelectSetting($option,$optionInfo['options'],$optionInfo['title'],$group,$description, $conditions);
                                break;
	                        case 'custom':
		                        $this->registerCustomFieldSetting($option,'__CUSTOMREMOVE__',$group,$optionInfo['callback'],$description, $conditions);
		                        break;
	                        case 'sites':
		                        $this->registerSiteSelectSetting($option,$optionInfo['title'],$group,$description, $conditions);
		                        break;
	                        case 'advanced-presigned':
		                        $this->registerAdvancedPresignedURLs($option, $optionInfo['title'], $group, $conditions);
		                        break;
	                        case 'advanced-privacy':
		                        $this->registerAdvancedPrivacy($option, $optionInfo['title'], $group, $conditions);
		                        break;
	                        case 'image':
		                        $this->registerImageFieldSetting($option, $optionInfo['title'], $group, $description, $conditions);
		                        break;
                            default:
                                do_action('media-cloud/tools/register-setting-type', $option, $optionInfo, $group, $groupInfo, $conditions);
                        }
                    }
                }
            }
        }
    }

	/**
	 * Register menu pages related to this tool
	 *
	 * @param $top_menu_slug
	 */
	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false, $tool_menu_slug = null) {
	}

	/**
	 * Register support/help menu pages related to this tool
	 *
	 * @param $top_menu_slug
	 */
	public function registerHelpMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
	}

    /**
     * Register batch tool menu pages
     *
     * @param $top_menu_slug
     */
    public function registerBatchToolMenu($tool_menu_slug, $networkMode = false, $networkAdminMenu = false) {
    }

    /**
     * Registers a settings section
     * @param $slug
     * @param $title
     * @param $description
     */
    protected function registerSettingsSection($slug, $title, $description) {
        $this->settingSections[$slug]=[
            'title'=>$title,
            'description'=>$description,
            'fields'=>[]
        ];

        add_settings_section($slug,$title,[$this,'renderSettingsSection'],$this->options_page);
    }

    /**
     * Renders a settings section description
     * @param $section
     */
    public function renderSettingsSection($section) {
        if (!isset($this->settingSections[$section['id']])) {
            return;
        }

        $settingSection=$this->settingSections[$section['id']];

        echo "<a name='{$section['id']}'></a>";

        if (is_array($settingSection['description'])) {
			foreach($settingSection['description'] as $description) {
				echo "<p>$description</p>";
			}
        } else {
	        echo "<p>{$settingSection['description']}</p>";
        }
    }

    /**
     * Determines if settings have changed
     * @return bool
     */
    public function haveSettingsChanged() {
        if (get_transient("settings_changed_".$this->toolName)) {
            delete_transient("settings_changed_".$this->toolName);

            return true;
        }

        return false;
    }

    //endregion

    //region Upload Path Preview
    public function doPreviewUploadPath() {
        check_ajax_referer('mcloud-preview-upload-path', 'nonce');

        $prefix = sanitize_text_field($_REQUEST['prefix']);
        if (empty($prefix)) {
            wp_die();
        }

        Prefixer::nextVersion();
        Prefixer::setType('image/jpeg');

        wp_send_json([
            'path' => Prefixer::Parse($prefix),
            'prefix' => $prefix
        ]);

    }
    //endregion
}
