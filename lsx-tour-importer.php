<?php
/*
 * Plugin Name: LSX Tour Importer 
 * Plugin URI: https://www.lsdev.biz/product/lsx-tour-operators-plugin/
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