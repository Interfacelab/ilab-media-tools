<?php
interface ILabMediaToolAdminInterface
{
    public function install();
    public function uninstall();
    public function registerMenu($top_menu_slug);
}