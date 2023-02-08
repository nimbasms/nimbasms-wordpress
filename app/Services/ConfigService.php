<?php
namespace App\Services;

use App\Models\Config;

class ConfigService{

    private $secret_id;
    private static $credentials_link = __DIR__.'credentials.txt';

    public static function save(Config $credentials){
        save_to_a_file(
                self::$credentials_link, 
                json_encode($credentials->service_id.':'.$credentials->secret_token), 
                'w'
            );
        
    }

    public static function getCredentials(){
        $credentials = json_decode(get_file_content(self::$credentials_link));

        if(!$credentials){
            $credentials = new Config();
        }else{
            $credentials = explode(":", $credentials);
            $credentials = new Config($credentials[0], $credentials[1]);
        }
        
        return $credentials;      
    }

}