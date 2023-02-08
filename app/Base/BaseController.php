<?php


namespace App\Base;


class BaseController
{

    public $plugin_path;

    public function __construct()
    {
        $this->plugin_path = NSMS_PLUGIN_PATH;
    }
}