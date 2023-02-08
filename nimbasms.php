<?php

/*
Plugin Name: NIMBA SMS
Plugin URI:  https://www.nimbasms.com/
Description: Plugin worpdress permettant l'envoi de SMS à travers la plateforme NIMBA SMS
Version:     1.0.0
Author:      NIMBA SMS
Author URI:  #
Text Domain: SAAS
Domain Path: #
License:     GPL2

NIMBA SMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

NIMBA SMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NIMBA SMS. If not, see {License URI}.
*/

if ( file_exists( dirname(__FILE__) . '/vendor/autoload.php')){
    include_once ( dirname(__FILE__) . '/vendor/autoload.php');
}

define('NSMS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NSMS_PLUGIN_ASSET_URL',plugin_dir_url(__FILE__));
define('NSMS_PLUGIN_BASENAME',plugin_basename(__FILE__));

//Activation du plugin
register_activation_hook(__FILE__,'activate_nimbasms_plugin');
function activate_nimbasms_plugin(){
    \App\Base\Activate::activate();
}

//Desactivation du plugin
register_deactivation_hook(__FILE__, 'deactivate_nimbasms_plugin');
function deactivate_nimbasms_plugin(){
    \App\Base\Deactivate::deactivate();
}

if( class_exists('App\Init')){
    App\Init::register_service();
}


