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

namespace ILAB\MediaCloud\Tools;

use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\NoticeManager;
use function ILAB\MediaCloud\Utilities\arrayPath;


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

    /** @var BatchTool[] List of batch tools for this tool */
    protected $batchTools = [];

    protected $actions = [];

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
                    add_action('wp_ajax_'.str_replace('-', '_', $key), [$this, $action['method']]);
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

        if (isset($toolInfo['batchTools'])) {
            foreach($toolInfo['batchTools'] as $className) {
                $this->batchTools[] = new $className($this);
            }
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
                if (is_plugin_active($plugin['plugin'])) {
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
                if (is_plugin_active($plugin['plugin'])) {
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
                    <div style="display:flex; align-items: center; font-weight:bold; margin-bottom: 10px;"><?php echo $name ?> <a style="margin-left: 15px;" class="button button-small" href="<?php echo $this->generateDeactivateLink($name, $plugin['plugin'])?>">Deactivate</a></div>
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
        foreach($this->batchTools as $batchTool) {
            $batchTool->setup();
        }
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

    public function hasSettings() {
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

        $env = ($this->env_variable) ? getenv($this->env_variable) : false;
        return Environment::Option("mcloud-tool-enabled-$this->toolName", $env);
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
                            return false;
                        }
                    }
	            }
            }
        }

        return true;
    }

    //endregion

    //region Batch Tools

    /**
     * Determines if this tool has any related batch tools
     *
     * @return bool
     */
    public function hasBatchTools() {
        return (count($this->batchTools) > 0);
    }

    /**
     * Determines if this tool has any related batch tools that are enabled
     * @return bool
     */
    public function hasEnabledBatchTools() {
        /** @var BatchTool $batchTool */
        foreach($this->batchTools as $batchTool) {
            if ($batchTool->enabled() && (!empty($batchTool->toolInfo()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns information about related batch tools
     * @return array
     */
    public function batchToolInfo() {
        $results = [];

        foreach($this->batchTools as $batchTool) {
            $results[] = $batchTool->toolInfo();
        }

        return $results;
    }

    /**
     * Returns information about related batch tools that are enabled
     * @return array
     */
    public function enabledBatchToolInfo() {
        $results = [];

        foreach($this->batchTools as $batchTool) {
            if ($batchTool->enabled()) {
                $results[] = $batchTool->toolInfo();
            }
        }

        return $results;
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

        $groups=$this->toolInfo['settings']['groups'];
        foreach($groups as $group => $groupInfo)  {
            $groupWatch = !empty($groupInfo['watch']);

            $this->registerSettingsSection($group, $groupInfo['title'], arrayPath($groupInfo, 'description', null));
            if (isset($groupInfo['options']))  {
                foreach($groupInfo['options'] as $option => $optionInfo)  {
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
                                $this->registerTextFieldSetting($option,$optionInfo['title'],$group,$description,$placeholder,$conditions);
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
		                        $this->registerSelectSetting($option,$optionInfo['options'],$optionInfo['title'],$group,$description, $conditions);
		                        break;
                            case 'dynamic-select':
                                $this->registerDynamicSelectSetting($option,$optionInfo['options'],$optionInfo['title'],$group,$description, $conditions);
                                break;
	                        case 'custom':
		                        $this->registerCustomFieldSetting($option,'__CUSTOMREMOVE__',$group,$optionInfo['callback'],$description, $conditions);
		                        break;
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
	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
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
        if (!isset($this->toolInfo['settings'])) {
            return;
        }

        if ($this->only_when_enabled && (!$this->enabled())) {
            return;
        }

        if (empty($this->batchTools)) {
            return;
        }

        if ($networkMode && $networkAdminMenu) {
            return;
        }

        $hasBatchTool = false;
	    foreach($this->batchTools as $batchTool) {
		    if ($batchTool->enabled()) {
		        $hasBatchTool = true;
		        break;
		    }
	    }

	    if ($hasBatchTool) {
		    ToolsManager::instance()->insertBatchToolSeparator();

		    foreach($this->batchTools as $batchTool) {
			    if ($batchTool->enabled()) {
			        if (empty($batchTool->menuTitle())) {
			            continue;
                    }

			        ToolsManager::instance()->addMultisiteBatchTool($batchTool);
				    add_submenu_page($tool_menu_slug, $batchTool->pageTitle(), $batchTool->menuTitle(), $batchTool->capabilityRequirement(), $batchTool->menuSlug(), [
					    $batchTool,
					    'renderBatchTool'
				    ]);
			    }
		    }
        }
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
}
