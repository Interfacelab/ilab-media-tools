<?php

/**
 * Base class for media tools
 */
abstract class ILabMediaToolBase {

    protected $settingSections;

    /**
     * Tool manager that owns this tool's admin
     * @var ILabMediaToolsManager
     */
    protected $toolManager;

    /**
     * Information about this tool
     * @var array
     */
    protected $toolInfo;

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
     * Creates a new instance
     * @param $toolInfo
     * @param $toolManager
     */
    public function __construct($toolInfo, $toolManager)
    {
        $this->settingSections=[];
        $this->toolInfo=$toolInfo;
        $this->toolManager=$toolManager;

        if (isset($toolInfo['info']['settings']['options-page']))
            $this->options_page=$toolInfo['info']['settings']['options-page'];

        if (isset($toolInfo['info']['settings']['options-group']))
            $this->options_group=$toolInfo['info']['settings']['options-group'];
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
     * Register any settings
     */
    public function registerSettings()
    {
        if (!isset($this->toolInfo['info']['settings']['groups']))
            return;

        $groups=$this->toolInfo['info']['settings']['groups'];
        foreach($groups as $group => $groupInfo)
        {
            $this->registerSettingsSection($group,$groupInfo['title'],$groupInfo['description']);
            if (isset($groupInfo['options']))
            {
                foreach($groupInfo['options'] as $option => $optionInfo)
                {
                    $this->registerSetting($option);
                    if (isset($optionInfo['type']))
                    {
                        switch($optionInfo['type'])
                        {
                            case 'text-field':
                                $this->registerTextFieldSetting($option,$optionInfo['title'],$group);
                                break;
                            case 'password':
                                $this->registerPasswordFieldSetting($option,$optionInfo['title'],$group);
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
        if (!isset($this->toolInfo['info']['settings']))
            return;

        $settings=$this->toolInfo['info']['settings'];
        add_submenu_page( $top_menu_slug, $settings['title'], $settings['menu'], 'manage_options', $this->options_page, [$this,'renderSettings']);
    }


    /**
     * Render settings.  Shouldn't need to override though.
     */
    public function renderSettings()
    {
        echo render_view('base/ilab-settings.php',[
            'title'=>$this->toolInfo['info']['title'],
            'group'=>$this->options_group,
            'page'=>$this->options_page
        ]);
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

    protected function registerTextFieldSetting($option_name,$title,$settings_slug)
    {
        add_settings_field($option_name,$title,[$this,'renderTextFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name]);

    }

    public function renderTextFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input size='40' type=\"text\" name=\"{$args['option']}\" value=\"$value\">";
    }

    protected function registerPasswordFieldSetting($option_name,$title,$settings_slug)
    {
        add_settings_field($option_name,$title,[$this,'renderPasswordFieldSetting'],$this->options_page,$settings_slug,['option'=>$option_name]);

    }

    public function renderPasswordFieldSetting($args)
    {
        $value=get_option($args['option']);
        echo "<input size='40' type=\"password\" name=\"{$args['option']}\" value=\"$value\">";
    }
}