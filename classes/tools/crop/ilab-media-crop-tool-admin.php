<?php
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-admin-base.php');

class ILabMediaCropToolAdmin extends ILAbMediaToolAdminBase
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

    public function renderSettings()
    {

    }

    public function registerSettings()
    {
        register_setting('ilab-media-crop','ilab-media-crop-quality');
        register_setting('ilab-media-crop','ilab-media-unique-filename');
        register_setting('ilab-media-crop','ilab-media-unique-filename-prefix');
    }
}