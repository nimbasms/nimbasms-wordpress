<?php

namespace Inc;

use App\Pages\Admin;

use App\Base\Enqueue;
use App\Models\Document;
use App\Base\SettingsLinks;

final class Init {

    public static function get_services()
    {
        return [
            Admin::class,
            Enqueue::class
        ];
    }

    public static function register_service()
    {
        foreach (self::get_services() as $class){
             $services = self::instantiate($class);

             if(method_exists($services, 'register' )){
                 $services->register();
             }
        }
    }

    public static function instantiate($class)
    {
        $service = new $class();

        return $service;
    }

}