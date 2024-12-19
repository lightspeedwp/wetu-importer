<?php
/*
 * Plugin Name:			LSX Importer for Wetu
 * Plugin URI:			https://lsx.design/products/wetu-importer/
 * Description:			Integrate with the Wetu Tour Operator system to import destination, accommodation, and tour content into the LSX Tour Operators plugin format.
 * Author:				LightSpeed
 * Version:				1.5.0
 * Requires at least:	6.7
 * Tested up to:		6.7
 * Requires PHP:		8.0
 * Author URI:			https://lightspeedwp.agency/
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:			lsx-wetu-importer
 * Domain Path:			/languages/
 * Update URI:			https://lsx.design/products/tour-operator/wetu-importer/
 * Tags:				lsx, tour operator, wetu, importer
 * Requires Plugins:	lsx-tour-operator
 */



define( 'LSX_WETU_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_CORE', __FILE__ );
define( 'LSX_WETU_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_VER', '1.5.0' );

register_activation_hook( LSX_WETU_IMPORTER_CORE, array( 'LSX_WETU_Importer', 'register_activation_hook' ) );

/* ======================= Below is the Plugin Class init ========================= */

require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer.php';
