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

namespace ILAB\MediaCloud;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class ILabMediaToolsManager
 *
 * Manages all of the tools for the ILab Media Tools plugin
 */
class ILabMediaToolsManager
{
	//region Class variables
    private static $instance;
    public $tools;
    //endregion

	//region Constructor
    public function __construct()
    {
        $toolList=include ILAB_CONFIG_DIR.'/tools.config.php';

	    $this->tools=[];

        foreach($toolList as $toolName => $toolInfo) {
            $className=$toolInfo['class'];
            $this->tools[$toolName]=new $className($toolName,$toolInfo,$this);
        }

        foreach($this->tools as $key => $tool) {
            $tool->setup();
        }

        add_action('admin_menu', function() {
            add_menu_page('Settings', 'Media Cloud', 'manage_options', 'media-tools-top', [$this,'renderSettings'],'dashicons-cloud');
            add_submenu_page( 'media-tools-top', 'Media Cloud Tools', 'Enable/Disable Tools', 'manage_options', 'media-tools-top', [$this,'renderSettings']);

            add_settings_section('ilab-media-tools','Enabled Tools',[$this,'renderSettingsSection'],'media-tools-top');

            foreach($this->tools as $key => $tool)
            {
                register_setting('ilab-media-tools',"ilab-media-tool-enabled-$key");
                add_settings_field("ilab-media-tool-enabled-$key",$tool->toolInfo['title'],[$this,'renderToolSettings'],'media-tools-top','ilab-media-tools',['key'=>$key]);

                $tool->registerMenu('media-tools-top');
                $tool->registerSettings();
            }

	        add_submenu_page( 'media-tools-top', 'Plugin Support', 'Help / Support', 'manage_options', 'media-tools-support', [$this,'renderSupport']);
        });

	    add_filter('plugin_action_links_'.ILAB_PLUGIN_NAME, function($links) {
		    $links[] = "<a href='http://www2.jdrf.org/site/TR?fr_id=6912&pg=personal&px=11429802' target='_blank'><b>Donate</b></a>";
		    $links[] = "<a href='admin.php?page=media-tools-top'>Settings</a>";
		    $links[] = "<a href='https://wordpress.org/support/plugin/ilab-media-tools' target='_blank'>Support</a>";

		    return $links;
	    });
    }
    //endregion

	//region Static Methods
    /**
     * Returns the singleton instance of the manager
     * @return mixed
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            $class=__CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }
    //endregion

	//region Plugin installation
	/**
	 * Perform plugin installation
	 */
	public function install() {
		foreach($this->tools as $key => $tool)
			$tool->install();
	}

	/**
	 * Perform plugin removal
	 */
	public function uninstall() {
		foreach($this->tools as $key => $tool)
			$tool->uninstall();
	}
	//endregion

	//region Tool Settings
    /**
     * Determines if a tool is enabled or not
     *
     * @param $toolName
     * @return bool
     */
    public function toolEnabled($toolName) {
        if (isset($this->tools[$toolName]))
            return $this->tools[$toolName]->enabled();

        return false;
    }
	//endregion


	//region Settings
    /**
     * Render the options page
     */
    public function renderSettings() {
        echo ILabMediaToolView::render_view('base/ilab-settings.php',[
            'title'=>'Enabled Tools',
            'group'=>'ilab-media-tools',
            'page'=>'media-tools-top'
        ]);
    }

    /**
     * Render the settings section
     */
    public function renderSettingsSection() {
        echo 'Enabled/disable tools.';
    }

    public function renderSupport() {
        echo ILabMediaToolView::render_view('base/ilab-support.php', []);
    }

    public function renderToolSettings($args) {
        $tool=$this->tools[$args['key']];

        echo ILabMediaToolView::render_view('base/ilab-tool-settings.php',[
            'name'=>$args['key'],
            'tool'=>$tool,
            'manager'=>$this
        ]);
    }
    //endregion
}
