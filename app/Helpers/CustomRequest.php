<?php
namespace App\Helpers;

class CustomRequest{

    private $service_id = '';
    private $secret_token = '';
    private $base_url = 'https://api.nimbasms.com/v1';

    public static function send_post($uri, $params) {

        $query = json_encode($params);

        $headers  = array(
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($this->service_id.':'.$this->secret_token),
            'Accept: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $this->getFullUrl($uri));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }


    private function getFullUrl($uri){
        if(!$uri) return;
        return $this->base_url.''.$uri;
    }
}