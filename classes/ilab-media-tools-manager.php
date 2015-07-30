<?php

/**
 * Class ILabMediaToolsManager
 *
 * Manages all of the tools for the ILab Media Tools plugin
 */
class ILabMediaToolsManager
{
    private static $instance;

    protected $tools;

    public function __construct()
    {
        $toolList=json_decode(file_get_contents(ILAB_TOOLS_DIR.'/tools.json'),true);

        $this->tools=[];

        foreach($toolList as $toolName => $toolInfo)
        {
            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['source']);
            $className=$toolInfo['class'];
            $this->tools[$toolName]=new $className($toolInfo,$this);
        }

        add_action('admin_menu', function(){
            add_menu_page('Settings', 'ILab Media Tools', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            add_submenu_page( 'media-tools-top', 'Settings', 'Settings', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            foreach($this->tools as $key => $tool)
            {
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
}