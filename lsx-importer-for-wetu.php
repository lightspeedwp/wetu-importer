<?php
/*
 * Plugin Name:			Wetu Content Importer
 * Plugin URI:			https://lsx.design/products/wetu-importer/
 * Description:			Integrate with the Wetu Tour Operator system to import destination, accommodation, and tour content into the Tour Operator plugin format.
 * Author:				lightspeedwp
 * Version:				1.5.2
 * Requires at least:	6.7
 * Tested up to:		6.8.1
 * Requires PHP:		8.0
 * Author URI:			https://lightspeedwp.agency/
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:			lsx-wetu-importer
 * Domain Path:			/languages/
 * Tags:				lsx, tour operator, wetu, importer
 * Requires Plugins:	tour-operator
 */

define( 'LSX_WETU_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_CORE', __FILE__ );
define( 'LSX_WETU_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'LSX_WETU_IMPORTER_VER', '1.5.2' );

register_activation_hook( LSX_WETU_IMPORTER_CORE, array( 'LSX_WETU_Importer', 'register_activation_hook' ) );

/* ======================= Below is the Plugin Class init ========================= */

require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer.php';
