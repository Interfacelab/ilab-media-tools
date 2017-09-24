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

use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\EnvironmentOptions;
use ILAB\MediaCloud\Utilities\View;


if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Base class for media tools
 */
abstract class ToolBase {

    private $adminNotices;
    protected $settingSections;

    private $settingsChanged = false;

    /**
     * Name of the tool
     * @var string
     */
    public  $toolName;

    /**
     * Tool manager that owns this tool's admin
     * @var ToolsManager
     */
    protected $toolManager;

    /**
     * Information about this tool
     * @var array
     */
    public $toolInfo;

    /**
     * The page slug for this tool's options
     * @var string
     */
    protected $options_page;

    /**
     * The option group for this tool's options
     * @var string
     */
    protected $options_group;

	/**
	 * The name of the environment variable
	 * @var string
	 */
    protected $env_variable;

	/**
	 * Only display the settings page when the tool is enabled
	 * @var bool
	 */
    protected $only_when_enabled;

    /**
     * Creates a new instance.  Subclasses should do any setup dependent on being enabled in setup()
     * @param $toolName
     * @param $toolInfo
     * @param $toolManager
     */
    public function __construct($toolName, $toolInfo, $toolManager)
    {
        $this->adminNotices=[];
        $this->toolName=$toolName;
        $this->settingSections=[];
        $this->toolInfo=$toolInfo;
        $this->toolManager=$toolManager;
	    $this->only_when_enabled = false;

        if (isset($toolInfo['env'])) {
        	$this->env_variable = $toolInfo['env'];
        }

        if (isset($toolInfo['settings']) && !empty($toolInfo['settings'])) {
	        if (isset($toolInfo['settings']['options-page'])) {
		        $this->options_page=$toolInfo['settings']['options-page'];
	        }

	        if (isset($toolInfo['settings']['only_when_enabled'])) {
		        $this->only_when_enabled = $toolInfo['settings']['only_when_enabled'];
	        }

	        if (isset($toolInfo['settings']['options-group'])) {
		        $this->options_group=$toolInfo['settings']['options-group'];
	        }
        }

        if (isset($toolInfo['helpers']))
        {
            foreach($toolInfo['helpers'] as $helper)
                require_once(ILAB_HELPERS_DIR.'/'.$helper);
        }


	    add_action('admin_enqueue_scripts', function(){
		    wp_enqueue_style('ilab-media-settings-css', ILAB_PUB_CSS_URL . '/ilab-media-tools.settings.min.css' );
	    });
    }

    /**
     * Perform any setup
     */
    public function setup()
    {
    }

    /**
     * Performs any install tasks when the plugin is activated
     */
    public function install()
    {

    }

    /**
     * Performs any uninstall tasks when the plugin is uninstalled
     */
    public function uninstall()
    {

    }

    /**
     * Determines if this tool is enabled or not
     */
    public function enabled()
    {
    	$env = ($this->env_variable) ? getenv($this->env_variable) : false;
        $enabled=get_option("ilab-media-tool-enabled-$this->toolName", $env);

        if ($enabled && isset($this->toolInfo['dependencies']))
        {
            foreach($this->toolInfo['dependencies'] as $dep)
            {
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
		            if (!$this->toolManager->toolEnabled($dep))
			            return false;
	            }
            }
        }

        return $enabled;
    }

    /**
     * Register any settings
     */
    public function registerSettings()
    {
        if (!isset($this->toolInfo['settings']['groups']))
            return;

        $groups=$this->toolInfo['settings']['groups'];
        foreach($groups as $group => $groupInfo)
        {
            $this->registerSettingsSection($group,$groupInfo['title'],$groupInfo['description']);
            if (isset($groupInfo['options']))
            {
                foreach($groupInfo['options'] as $option => $optionInfo)
                {
                    $this->registerSetting($option);
                    if (isset($optionInfo['watch']) && $optionInfo['watch']) {
                        add_action("update_option_$option", function ($setting, $oldValue=null, $newValue=null) {
                            set_transient("settings_changed_".$this->toolName, true);
                        }, 10, 3);
                    }

                    if (isset($optionInfo['type']))
                    {
                    	$description = arrayPath($optionInfo,'description',null);
                    	$conditions = arrayPath($optionInfo,'conditions',null);
                    	$placeholder = arrayPath($optionInfo,'placeholder',null);
                    	$default = arrayPath($optionInfo,'default',null);

                        switch($optionInfo['type'])
                        {
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
                                $this->registerNumberFieldSetting($option,$optionInfo['title'],$group,$description, $conditions);
                                break;
	                        case 'select':
		                        $this->registerSelectSetting($option,$optionInfo['options'],$optionInfo['title'],$group,$description, $conditions);
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

    protected function registerSetting($option)
    {
        register_setting($this->options_group,$option);
    }

    /**
     * Register menu pages
     *
     * @param $top_menu_slug
     */
    public function registerMenu($top_menu_slug)
    {
        if (!isset($this->toolInfo['settings']))
            return;

	    if ($this->only_when_enabled && (!$this->enabled())) {
		    return;
	    }

        $settings=$this->toolInfo['settings'];
        add_submenu_page( $top_menu_slug, $settings['title'], $settings['menu'], 'manage_options', $this->options_page, [$this,'renderSettings']);
    }


    /**
     * Render settings.
     */
    public function renderSettings()
    {

        $result = View::render_view( 'base/ilab-settings.php', [
            'title'=>$this->toolInfo['settings']['title'],
            'group'=>$this->options_group,
            'page'=>$this->options_page
        ]);

        $result = str_replace('<th scope="row">__CUSTOMREMOVE__</th>', '', $result);

        echo $result;
    }

    /**
     * Registers a settings section
     * @param $slug
     * @param $title
     * @param $description
     */
    protected function registerSettingsSection($slug,$title,$description)
    {
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
    public function renderSettingsSection($section)
    {
        if (!isset($this->settingSections[$section['id']]))
            return;

        $settingSection=$this->settingSections[$section['id']];

        if (is_array($settingSection['description'])) {
			foreach($settingSection['description'] as $description) {
				echo "<p>$description</p>";
			}
        } else {
	        echo "<p>{$settingSection['description']}</p>";
        }
    }

    protected function registerTextFieldSetting($option_name, $title, $settings_slug, $description=null, $placeholder=null, $conditions=null)
    {
        add_settings_field($option_name,
                           $title,
                           [$this,'renderTextFieldSetting'],
                           $this->options_page,
                           $settings_slug,
                           ['option'=>$option_name, 'description'=>$description, 'placeholder' => $placeholder, 'conditions' => $conditions]);
    }

    public function renderTextFieldSetting($args)
    {
    	echo View::render_view('base/fields/text-field.php',[
			'value' => get_option($args['option']),
			'name' => $args['option'],
			'placeholder' => $args['placeholder'],
			'conditions' => $args['conditions'],
			'description' => (isset($args['description'])) ? $args['description'] : false
	    ]);
    }

    protected function registerPasswordFieldSetting($option_name,$title,$settings_slug, $description=null, $placeholder=null, $conditions=null)
    {
        add_settings_field($option_name,
                           $title,
                           [$this,'renderPasswordFieldSetting'],
                           $this->options_page,
                           $settings_slug,
                           ['option'=>$option_name,'description'=>$description, 'placeholder'=>$placeholder, 'conditions' => $conditions]);
    }

    public function renderPasswordFieldSetting($args)
    {
        echo View::render_view('base/fields/password.php',[
		    'value' => get_option($args['option']),
		    'name' => $args['option'],
		    'placeholder' => $args['placeholder'],
		    'conditions' => $args['conditions'],
		    'description' => (isset($args['description'])) ? $args['description'] : false
	    ]);
    }

    protected function registerTextAreaFieldSetting($option_name,$title,$settings_slug,$description=null, $placeholder=null, $conditions=null)
    {
        add_settings_field($option_name,
                           $title,
                           [$this,'renderTextAreaFieldSetting'],
                           $this->options_page,
                           $settings_slug,
                           ['option'=>$option_name,'description'=>$description, 'placeholder'=>$placeholder, 'conditions' => $conditions]);
    }

    public function renderTextAreaFieldSetting($args) {
	    echo View::render_view('base/fields/text-area.php',[
		    'value' => get_option($args['option']),
		    'name' => $args['option'],
		    'placeholder' => $args['placeholder'],
		    'conditions' => $args['conditions'],
		    'description' => (isset($args['description'])) ? $args['description'] : false
	    ]);
    }

	protected function registerCustomFieldSetting($option_name,$title,$settings_slug,$renderCallback,$description=null, $conditions=null) {
		add_settings_field($option_name,
		                   $title,
		                   [$this,$renderCallback],
		                   $this->options_page,
		                   $settings_slug,
		                   ['option'=>$option_name,'description'=>$description, 'conditions' => $conditions]);
	}

    protected function registerCheckboxFieldSetting($option_name,$title,$settings_slug,$description=null, $default=false, $conditions=null)
    {
        add_settings_field($option_name,
                           $title,
                           [$this,'renderCheckboxFieldSetting'],
                           $this->options_page,
                           $settings_slug,
                           ['option'=>$option_name,'description'=>$description, 'default' => $default, 'conditions' => $conditions]);

    }

    public function renderCheckboxFieldSetting($args)
    {
	    echo View::render_view('base/fields/checkbox.php',[
		    'value' => get_option($args['option'], $args['default']),
		    'name' => $args['option'],
		    'conditions' => $args['conditions'],
		    'description' => (isset($args['description'])) ? $args['description'] : false
	    ]);
    }

    protected function registerNumberFieldSetting($option_name,$title,$settings_slug,$description=null, $conditions=null)
    {
        add_settings_field($option_name,$title,[$this,'renderNumberFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description, 'conditions' => $conditions]);

    }

    public function renderNumberFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input type=\"number\" min=\"0\" step=\"1\" name=\"{$args['option']}\" value=\"$value\">";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    protected function registerSelectSetting($option_name, $options, $title, $settings_slug, $description=null, $conditions=null)
    {
        add_settings_field($option_name,$title,[$this,'renderSelectSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'options'=>$options,'description'=>$description, 'conditions'=>$conditions]);
    }

    public function renderSelectSetting($args)
    {
        $options = $args['options'];
	    if (!is_array($options)) {
		    $options = $this->$options();
	    }


	    echo View::render_view('base/fields/select.php',[
		    'value' => get_option($args['option']),
		    'name' => $args['option'],
		    'options' => $options,
		    'conditions' => $args['conditions'],
		    'description' => (isset($args['description'])) ? $args['description'] : false
	    ]);
    }

    public function haveSettingsChanged() {
        if (get_transient("settings_changed_".$this->toolName)) {
            delete_transient("settings_changed_".$this->toolName);

            return true;
        }

        return false;
    }

    public function getOption($optionName, $envVariableName = null, $default = false) {
    	return EnvironmentOptions::Option($optionName, $envVariableName, $default);
    }
}
