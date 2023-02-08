<?php

namespace App\Base;


class Enqueue
{

    public function register()
    {
        add_action('admin_enqueue_scripts', array($this,'enqueue') );
    }

   public function enqueue(){
        wp_enqueue_script('media-upload' );
        wp_enqueue_media();
        wp_enqueue_style('nsmsw-style',NSMS_PLUGIN_ASSET_URL.'assets/bootstrap/css/bootstrap.css',array(),false,'all');

        wp_enqueue_script('nsmsw-jquery',NSMS_PLUGIN_ASSET_URL.'assets/js/jquery.min.js',array(),false,'all');
        wp_enqueue_script('nsmsw-bjs',NSMS_PLUGIN_ASSET_URL.'assets/bootstrap/js/bootstrap.js',array(),false,'all');
   }




}