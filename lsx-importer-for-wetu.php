<?php
/*
 * Plugin Name: LSX Importer for Wetu
 * Plugin URI: https://github.com/lightspeeddevelopment/lsx-wetu-importer
 * Description: By integrating with the Wetu Tour Operator system, you are able to import your content into the LSX Tour Operators plugin format
 * Author: LightSpeed
 * Version: 1.4.2
 * Author URI: https://www.lsdev.biz/
 * License: GPL3+
 * Text Domain: lsx-wetu-importer
 * Domain Path: /languages/
 */

define( 'LSX_WETU_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_CORE', __FILE__ );
define( 'LSX_WETU_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_VER', '1.4.2' );

register_activation_hook( LSX_WETU_IMPORTER_CORE, array( 'LSX_WETU_Importer', 'register_activation_hook' ) );

/* ======================= Below is the Plugin Class init ========================= */

require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer.php';
