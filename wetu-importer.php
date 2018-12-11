<?php
/*
 * Plugin Name: Wetu Importer
 * Plugin URI: https://www.lsdev.biz/product/wetu-importer/
 * Description: By integrating with the Wetu Tour Operator system, you are able to import your content into the LSX Tour Operators plugin format
 * Author: LightSpeed
 * Version: 1.1.2
 * Author URI: https://www.lsdev.biz/products/
 * License: GPL3+
 * Text Domain: wetu-importer
 * Domain Path: /languages/
 */

define( 'WETU_IMPORTER_PATH',  plugin_dir_path( __FILE__ ) );
define( 'WETU_IMPORTER_CORE',  __FILE__ );
define( 'WETU_IMPORTER_URL',  plugin_dir_url( __FILE__ ) );
define( 'WETU_IMPORTER_VER',  '1.1.2' );

register_activation_hook( WETU_IMPORTER_CORE, array( 'WETU_Importer', 'register_activation_hook' ) );

/* ======================= The API Classes ========================= */

if ( ! class_exists( 'LSX_API_Manager' ) ) {
	require_once( 'classes/class-lsx-api-manager.php' );
}

/**
 *	Grabs the email and api key from settings.
 */
function lsx_to_wetu_importer_options_pages_filter( $pages ) {
	$pages[] = 'lsx-to-settings';
	return $pages;
}

add_filter( 'lsx_api_manager_options_pages', 'lsx_to_wetu_importer_options_pages_filter', 10, 1 );

function lsx_to_wetu_importer_api_admin_init() {
	$options = get_option( '_lsx-to_settings', false );

	$data = array(
		'api_key' => '',
		'email' => '',
	);

	if ( false !== $options && isset( $options['api'] ) ) {
		if ( isset( $options['api']['wetu-importer_api_key'] ) && '' !== $options['api']['wetu-importer_api_key'] ) {
			$data['api_key'] = $options['api']['wetu-importer_api_key'];
		}

		if ( isset( $options['api']['wetu-importer_email'] ) && '' !== $options['api']['wetu-importer_email'] ) {
			$data['email'] = $options['api']['wetu-importer_email'];
		}
	}

	$instance = get_option( 'lsx_api_instance', false );

	if ( false === $instance ) {
		$instance = LSX_API_Manager::generatePassword();
	}

	$api_array = array(
		'product_id' => 'Wetu Importer',
		'version' => '1.1.1',
		'instance' => $instance,
		'email' => $data['email'],
		'api_key' => $data['api_key'],
		'file' => 'wetu-importer.php',
	);

	$lsx_to_wetu_importer_api_manager = new LSX_API_Manager( $api_array );
}

add_action( 'admin_init', 'lsx_to_wetu_importer_api_admin_init' );

/* ======================= Below is the Plugin Class init ========================= */

require_once( WETU_IMPORTER_PATH . 'classes/class-lsx-logger.php' );
require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer.php' );
//require_once(WETU_IMPORTER_PATH.'classes/class-wetu-importer-connect-accommodation.php');
require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer-settings.php' );
