<?php
namespace App\Services;

use App\Models\Config;

class ConfigService{

    private $secret_id;

    public static function save(Config $credentials){
        var_dump($credentials); die();
    }

    public static function getCredentials(){
        
    }

}