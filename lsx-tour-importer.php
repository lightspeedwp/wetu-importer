<?php
/*
 * Plugin Name: Wetu Importer 
 * Plugin URI: https://www.lsdev.biz/product/wetu-importer/
 * Description: By integrating with the Wetu Tour Operator system, you are able to import your content into the LSX Tour Operators plugin format
 * Author: LightSpeed
 * Version: 1.0.0
 * Author URI: https://www.lsdev.biz/products/
 * License: GPL2+
 * Text Domain: lsx-tour-importer
 * Domain Path: /languages/
 */

define('LSX_TOUR_IMPORTER_PATH',  plugin_dir_path( __FILE__ ) );
define('LSX_TOUR_IMPORTER_URL',  plugin_dir_url( __FILE__ ) );
define('LSX_TOUR_IMPORTER_VER',  '1.0.0' );

require_once(LSX_TOUR_IMPORTER_PATH.'classes/importer.php');
require_once(LSX_TOUR_IMPORTER_PATH.'classes/admin.php');
require_once(LSX_TOUR_IMPORTER_PATH.'classes/accommodation.php');
require_once(LSX_TOUR_IMPORTER_PATH.'classes/connect_accommodation.php');

if(class_exists('Lsx_Banners')){
	require_once(LSX_TOUR_IMPORTER_PATH.'classes/lsx-banners-integration.php');
}

//PLugin Upgrades

if(!class_exists('LSX_API_Manager')){
	require_once(LSX_TOUR_IMPORTER_PATH.'vendor/lsx-api-class/lsx-api-manager-class.php');
}

function lsx_importer_api_admin_init(){
	$data = lsx_importer_get_api_details();

	$api_array = array(
		'product_id'	=>		'Wetu Importer',
		'version'		=>		'1.0',
		'instance'		=>		get_option('lsx_to_api_instance',false),
		'email'			=>		$data['email'],
		'api_key'		=>		$data['api_key'],
		'file'			=>		'lsx-tour-importer.php'
	);
	$lsx_to_api_manager = new LSX_API_Manager($api_array);
}
add_action('admin_head','lsx_importer_api_admin_init');

/** 
 *	Grabs the email and api key from the LSX TO Settings.
 */
function lsx_importer_get_api_details(){
	$options = get_option('_lsx_lsx-settings',false);
	$data = array('api_key'=>'','email'=>'');

	if(false !== $options && isset($options['general'])){
		if(isset($options['general']['wetu-importer_api_key']) && '' !== $options['general']['wetu-importer_api_key']){
			$data['api_key'] = $options['general']['wetu-importer_api_key'];
		}
		if(isset($options['general']['wetu-importer_email']) && '' !== $options['general']['wetu-importer_email']){
			$data['email'] = $options['general']['wetu-importer_email'];
		}		
	}
	return $data;
}


/**
 * Run when the plugin is active, and generate a unique password for the site instance.
 */
function lsx_importer_activate_plugin() {
	$lsx_to_password = get_option('lsx_api_instance',false);
	if(false === $lsx_to_password){
		$lsx_to_password = get_option('lsx_to_api_instance',false);
	}
    if(false === $lsx_to_password){
    	update_option('lsx_api_instance',LSX_API_Manager::generatePassword());
    }
}
register_activation_hook( __FILE__, 'lsx_importer_activate_plugin' );