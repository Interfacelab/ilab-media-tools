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

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class ILabMediaToolsManager
 *
 * Manages all of the tools for the ILab Media Tools plugin
 */
class ILabMediaToolsManager
{
    private static $instance;

    public $tools;

    public function __construct()
    {
        $toolList=ejson_decode_file(ILAB_TOOLS_DIR.'/tools.json',true);

        $this->tools=[];

        foreach($toolList as $toolName => $toolInfo)
        {
            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['source']);
            $className=$toolInfo['class'];
            $this->tools[$toolName]=new $className($toolName,$toolInfo,$this);
        }

        foreach($this->tools as $key => $tool)
        {
            $tool->setup();
        }



        add_action('admin_menu', function(){
            add_menu_page('Settings', 'ILab Media Tools', 'manage_options', 'media-tools-top', [$this,'renderSettings'],'dashicons-image-crop');
            add_submenu_page( 'media-tools-top', 'ILab Tools', 'Tools', 'manage_options', 'media-tools-top', [$this,'renderSettings']);

            add_settings_section('ilab-media-tools','Enabled Tools',[$this,'renderSettingsSection'],'media-tools-top');

            foreach($this->tools as $key => $tool)
            {
                register_setting('ilab-media-tools',"ilab-media-tool-enabled-$key");
                add_settings_field("ilab-media-tool-enabled-$key",$tool->toolInfo['title'],[$this,'renderToolSettings'],'media-tools-top','ilab-media-tools',['key'=>$key]);

                $tool->registerMenu('media-tools-top');
                $tool->registerSettings();
            }
        });
    }

    /**
     * Returns the singleton instance of the manager
     * @return mixed
     */
    public static function instance()
    {
        if (!isset(self::$instance))
        {
            $class=__CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * Determines if a tool is enabled or not
     *
     * @param $toolName
     * @return bool
     */
    public function toolEnabled($toolName)
    {
        if (isset($this->tools[$toolName]))
            return $this->tools[$toolName]->enabled();

        return false;
    }

    /**
     * Perform plugin installation
     */
    public function install()
    {
        foreach($this->tools as $key => $tool)
            $tool->install();
    }

    /**
     * Perform plugin removal
     */
    public function uninstall()
    {
        foreach($this->tools as $key => $tool)
            $tool->uninstall();
    }

    /**
     * Render the options page
     */
    public function renderSettings()
    {
        echo render_view('base/ilab-settings.php',[
            'title'=>'Enabled Tools',
            'group'=>'ilab-media-tools',
            'page'=>'media-tools-top'
        ]);
    }

    /**
     * Render the settings section
     */
    public function renderSettingsSection()
    {
        echo 'Enabled/disable tools.';
    }

    public function renderToolSettings($args)
    {
        $tool=$this->tools[$args['key']];

        echo render_view('base/ilab-tool-settings.php',[
            'name'=>$args['key'],
            'tool'=>$tool,
            'manager'=>$this
        ]);
    }
}