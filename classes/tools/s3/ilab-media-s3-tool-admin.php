<?php
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-admin-base.php');

class ILabMediaS3ToolAdmin extends ILAbMediaToolAdminBase
{
    public function install()
    {

    }

    public function uninstall()
    {

    }

    public function registerMenu($top_menu_slug)
    {
        add_submenu_page( $top_menu_slug, 'S3 Settings', 'S3 Settings', 'manage_options', 'media-tools-crop', [$this,'render_settings']);
    }

    public function renderSettings()
    {

    }

    public function registerSettings()
    {
        register_setting('ilab-media-s3','ilab-media-s3-access-key');
        register_setting('ilab-media-s3','ilab-media-s3-access-secret');
        register_setting('ilab-media-s3','ilab-media-s3-access-bucket');
        register_setting('ilab-media-s3','ilab-media-s3-region');
        register_setting('ilab-media-s3','ilab-media-s3-cdn-base');
    }
}