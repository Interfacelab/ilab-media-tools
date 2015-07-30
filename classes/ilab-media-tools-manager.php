<?php
class ILabMediaToolsManager
{
    private static $instance;

    protected $tools;
    protected $toolsAdmin;

    public function __construct()
    {
        $toolList=json_decode(file_get_contents(ILAB_TOOLS_DIR.'/tools.json'),true);

        $this->tools=[];
        $this->toolsAdmin=[];

        foreach($toolList as $toolName => $toolInfo)
        {
            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['tool']['file']);
            $className=$toolInfo['tool']['class'];
            $this->tools[$toolName]=new $className($this);

            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['admin']['file']);
            $className=$toolInfo['admin']['class'];
            $this->toolsAdmin[$toolName]=new $className($this);
        }

        add_action('admin_menu', function(){
            add_menu_page('Settings', 'ILab Media Tools', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            add_submenu_page( 'media-tools-top', 'Settings', 'Settings', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            foreach($this->toolsAdmin as $key => $admin)
            {
                $admin->registerMenu('media-tools-top');
                $admin->registerSettings();
            }
        });
    }

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
        foreach($this->toolsAdmin as $key => $tool)
            $tool->install();
    }

    /**
     * Perform plugin removal
     */
    public function uninstall()
    {
        foreach($this->toolsAdmin as $key => $tool)
            $tool->uninstall();
    }
}