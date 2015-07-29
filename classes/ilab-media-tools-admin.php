<?php
class ILabMediaToolsAdmin
{
    protected $tools;

    public function __construct()
    {
        $toolList=json_decode(file_get_contents(ILAB_TOOLS_DIR.'/tools.json'),true);

        $this->tools=[];

        foreach($toolList as $toolName => $toolInfo)
        {
            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['admin']['file']);
            $className=$toolInfo['admin']['class'];
            $this->tools[]=new $className();
        }

        add_action('admin_menu', function(){
            add_menu_page('Settings', 'ILab Media Tools', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            add_submenu_page( 'media-tools-top', 'Settings', 'Settings', 'manage_options', 'media-tools-top', [$this,'render_settings']);
            foreach($this->tools as $tool)
                $tool->registerMenu('media-tools-top');
        });
    }


    /**
     * Perform plugin installation
     */
    public function install()
    {
        foreach($this->tools as $tool)
            $tool->install();
    }

    /**
     * Perform plugin removal
     */
    public function uninstall()
    {
        foreach($this->tools as $tool)
            $tool->uninstall();
    }
}