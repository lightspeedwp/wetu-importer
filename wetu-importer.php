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

/* ======================= Below is the Plugin Class init ========================= */

require_once( WETU_IMPORTER_PATH . 'classes/class-lsx-logger.php' );
require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer.php' );
//require_once(WETU_IMPORTER_PATH.'classes/class-wetu-importer-connect-accommodation.php');
require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer-settings.php' );
