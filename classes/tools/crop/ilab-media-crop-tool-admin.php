<?php
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-admin-interface.php');

class ILabMediaCropToolAdmin implements ILabMediaToolAdminInterface
{
    public function install()
    {

    }

    public function uninstall()
    {

    }

    public function registerMenu($top_menu_slug)
    {
        add_submenu_page( $top_menu_slug, 'Crop Settings', 'Crop Settings', 'manage_options', 'media-tools-crop', [$this,'render_settings']);
    }

    public function render_settings()
    {

    }
}