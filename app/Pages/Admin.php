<?php

namespace App\Pages;

use App\Api\SettingsApi;
use \App\Base\BaseController;
use App\DB\DBTable;

class Admin extends BaseController
{

    public $settings;

    public $subpages = [];

    public $pages    = [];

    public function __construct()
    {
        $this->settings = new SettingsApi();

        $this->pages = [
            [
                'page_title' => 'NIMBA SMS',
                'menu_title' => 'NIMBA SMS',
                'capability' => 'manage_options',
                'menu_slug'  => 'nimbasms',
                'callback'   => [$this, 'nimbasms'],
                'icon_url'   => '',
                'position'   => 400
            ]
        ];

        $this->subpages = [
            [
                'parent_slug' => 'nimbasms',
                'page_title'  => 'Message',
                'menu_title'  => 'Message',
                'capability'  => 'manage_options',
                'menu_slug'   => 'message',
                'callback'    => [$this, 'message'],
            ]
        ];
    }

    function register(){
         $this->settings->addPages( $this->pages)->addSubPages($this->subpages)->register();
    }

    function nimbasms(){
        include(NSMS_PLUGIN_PATH.'templates/nsms-admin.php');
    }

    function message(){
        include(NSMS_PLUGIN_PATH.'templates/message/index.php');
    }
}