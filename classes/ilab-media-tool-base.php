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

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Base class for media tools
 */
abstract class ILabMediaToolBase {

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
     * @var ILabMediaToolsManager
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

	    add_action( 'admin_enqueue_scripts', function(){
		    wp_enqueue_script('ilab-dismissible-notices', ILAB_PUB_JS_URL . '/ilab-dismiss-notice.js', ['jquery', 'common'], false, true);
		    wp_localize_script('ilab-dismissible-notices', 'ilab_dismissible_notice', ['nonce' => wp_create_nonce( 'dismissible-notice' )]);
	    });

	    add_action('wp_ajax_ilab_dismiss_admin_notice', [$this, 'dismissAdminNotice']);
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

    public function displayAdminNotice($type, $message, $dismissible=false, $dismissibleIdentifier = null, $dismissibleLength = 30)
    {
        if (isset($this->adminNotices[$message]))
            return;

        if (!$this->isAdminNoticeActive($dismissibleIdentifier)) {
        	return;
        }

        $this->adminNotices[$message]=true;
        if ($dismissible) {
        	if ($dismissibleIdentifier) {
		        $dismissibleAttr = "data-dismissible='$dismissibleIdentifier' data-dismissible-length='$dismissibleLength'";
	        }

        	$class = "notice notice-$type is-dismissible";
        } else {
	        $dismissibleAttr = '';
	        $class = "notice notice-$type";
        }

        add_action('admin_notices',function() use($class,$message,$dismissibleAttr) {
            echo ILabMediaToolView::render_view('base/ilab-admin-notice.php',[
                'class'=>$class,
                'message'=>$message,
                'identifier' => $dismissibleAttr
            ]);
        });
    }

	public function dismissAdminNotice() {
		$option_name        = sanitize_text_field( $_POST['option_name'] );
		$dismissible_length = sanitize_text_field( $_POST['dismissible_length'] );
		$transient          = 0;

		if ( 'forever' != $dismissible_length ) {
			$dismissible_length = ( 0 == absint( $dismissible_length ) ) ? 1 : $dismissible_length;
			$transient          = absint( $dismissible_length ) * DAY_IN_SECONDS;
			$dismissible_length = strtotime( absint( $dismissible_length ) . ' days' );
		}

		check_ajax_referer( 'dismissible-notice', 'nonce' );
		set_site_transient( $option_name, $dismissible_length, $transient );
		wp_die();
	}

	public function isAdminNoticeActive( $arg ) {
		$array       = explode( '-', $arg );
		$option_name = implode( '-', $array );
		$db_record   = get_site_transient( $option_name );

		if ( 'forever' == $db_record ) {
			return false;
		} elseif ( absint( $db_record ) >= time() ) {
			return false;
		} else {
			return true;
		}
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
                        switch($optionInfo['type'])
                        {
                            case 'text-field':
                                $this->registerTextFieldSetting($option,$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null), (isset($optionInfo['placeholder']) ? $optionInfo['placeholder'] : null));
                                break;
                            case 'text-area':
                                $this->registerTextAreaFieldSetting($option,$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null));
                                break;
                            case 'password':
                                $this->registerPasswordFieldSetting($option,$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null));
                                break;
                            case 'checkbox':
                                $this->registerCheckboxFieldSetting($option,$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null), (isset($optionInfo['default'])) ? $optionInfo['default'] : false);
                                break;
                            case 'number':
                                $this->registerNumberFieldSetting($option,$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null));
                                break;
	                        case 'select':
		                        $this->registerSelectSetting($option,$optionInfo['options'],$optionInfo['title'],$group,(isset($optionInfo['description']) ? $optionInfo['description'] : null));
		                        break;
	                        case 'custom':
		                        $this->registerCustomFieldSetting($option,'__CUSTOMREMOVE__',$group,$optionInfo['callback'],(isset($optionInfo['description']) ? $optionInfo['description'] : null));
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
        $result = ILabMediaToolView::render_view('base/ilab-settings.php',[
            'title'=>$this->toolInfo['title'],
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
        echo $settingSection['description'];
    }

    protected function registerTextFieldSetting($option_name,$title,$settings_slug,$description=null,$placeholder=null)
    {
        add_settings_field($option_name,$title,[$this,'renderTextFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description, 'placeholder' => $placeholder]);

    }

    public function renderTextFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input size='40' type=\"text\" name=\"{$args['option']}\" value=\"$value\" placeholder=\"{$args['placeholder']}\">";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    protected function registerPasswordFieldSetting($option_name,$title,$settings_slug,$description=null)
    {
        add_settings_field($option_name,$title,[$this,'renderPasswordFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description]);

    }

    public function renderPasswordFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input size='40' type=\"password\" name=\"{$args['option']}\" value=\"$value\" autocomplete=\"off\">";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    protected function registerTextAreaFieldSetting($option_name,$title,$settings_slug,$description=null)
    {
        add_settings_field($option_name,$title,[$this,'renderTextAreaFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description]);

    }

    public function renderTextAreaFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<textarea cols='40' rows='4' name=\"{$args['option']}\">$value</textarea>";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

	protected function registerCustomFieldSetting($option_name,$title,$settings_slug,$renderCallback,$description=null) {
		add_settings_field($option_name,$title,[$this,$renderCallback],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description]);
	}

    protected function registerCheckboxFieldSetting($option_name,$title,$settings_slug,$description=null, $default=false)
    {
        add_settings_field($option_name,$title,[$this,'renderCheckboxFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description, 'default' => $default]);

    }

    public function renderCheckboxFieldSetting($args)
    {
        $value=get_option($args['option'], $args['default']);
        echo "<input type=\"checkbox\" name=\"{$args['option']}\" ".(($value) ? 'checked':'').">";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    protected function registerNumberFieldSetting($option_name,$title,$settings_slug,$description=null)
    {
        add_settings_field($option_name,$title,[$this,'renderNumberFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'description'=>$description]);

    }

    public function renderNumberFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input type=\"number\" min=\"0\" step=\"1\" name=\"{$args['option']}\" value=\"$value\">";
        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    protected function registerSelectSetting($option_name,$options,$title,$settings_slug,$description=null)
    {
        add_settings_field($option_name,$title,[$this,'renderSelectSetting'],$this->options_page,$settings_slug,['option'=>$option_name,'options'=>$options,'description'=>$description]);
    }

    public function renderSelectSetting($args)
    {
        $option = $args['option'];
        $options = $args['options'];
	    if (!is_array($options)) {
		    $options = $this->$options();
	    }

        $value=get_option($args['option']);

        echo "<select name=\"{$option}\">\n";
        foreach($options as $val => $name) {
            $opt = "\t<option value=\"{$val}\"";
            if ($val == $value)
                $opt .= " selected";
            $opt .= ">{$name}</option>\n";

            echo $opt;
        }
        echo "</select>\n";

        if ($args['description'])
            echo "<p class='description'>".$args['description']."</p>";
    }

    public function haveSettingsChanged() {
        if (get_transient("settings_changed_".$this->toolName)) {
            delete_transient("settings_changed_".$this->toolName);

            return true;
        }

        return false;
    }

    public function getOption($optionName, $envVariableName = null, $default = false) {
    	if ($envVariableName == null) {
    		$envVariableName = str_replace('-','_', strtoupper($optionName));
	    }

    	$envval = getenv($envVariableName);
    	if ($envval) {
    		return $envval;
	    }

        return get_option($optionName, $default);
    }
}
