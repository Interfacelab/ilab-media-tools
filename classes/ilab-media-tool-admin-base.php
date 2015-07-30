<?php
abstract class ILAbMediaToolAdminBase {

    protected $toolManager;

    public function __construct($toolManager)
    {
        $this->toolManager=$toolManager;
    }

    abstract public function install();
    abstract public function uninstall();
    abstract public function registerSettings();
    abstract public function registerMenu($top_menu_slug);
}