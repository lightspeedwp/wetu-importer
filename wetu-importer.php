<?php
/*
 * Plugin Name: Wetu Importer 
 * Plugin URI: https://www.lsdev.biz/product/wetu-importer/
 * Description: By integrating with the Wetu Tour Operator system, you are able to import your content into the LSX Tour Operators plugin format
 * Author: LightSpeed
 * Version: 1.0.0
 * Author URI: https://www.lsdev.biz/products/
 * License: GPL3+
 * Text Domain: wetu-importer
 * Domain Path: /languages/
 */

define('WETU_IMPORTER_PATH',  plugin_dir_path( __FILE__ ) );
define('WETU_IMPORTER_URL',  plugin_dir_url( __FILE__ ) );
define('WETU_IMPORTER_VER',  '1.0.0' );

require_once(WETU_IMPORTER_PATH.'classes/importer.php');
require_once(WETU_IMPORTER_PATH.'classes/admin.php');
require_once(WETU_IMPORTER_PATH.'classes/accommodation.php');
require_once(WETU_IMPORTER_PATH.'classes/connect_accommodation.php');
require_once(WETU_IMPORTER_PATH.'classes/class-settings.php');

if(class_exists('Lsx_Banners')){
	require_once(WETU_IMPORTER_PATH.'classes/lsx-banners-integration.php');
}