<?php
namespace App\Models;

class Config{

    public $service_id;
    public $secret_token;

    function __construct($service_id = '', $secret_token = '') {
        $this->service_id = $service_id;
        $this->secret_token = $secret_token;
    }


}