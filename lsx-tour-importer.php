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

/**
 * @package   Lsx_Tour_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer {
	
	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|Module_Template
	 */
	protected static $instance = null;

	/**
	 * The slug for this plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $plugin_slug = 'lsx-tour-importer';

	/**
	 * The options for the plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;	
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( $this,'plugin_activate') );
		$this->options = get_option('_lsx_lsx-tour-importer',false);

		add_filter( 'lsx_framework_settings_tabs', array( $this, 'settings_page_array') );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ) );
	}	

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object|Module_Template    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}	

	/**
	 * Run when the plugin is first activated
	 */
	public function plugin_activate() {
		//TODO  if the lsx-tour-operator plugin is not active then do not activate.
	}

	/**
	 * Returns the array of settings to the UIX Class in the lsx framework
	 */	
	public function settings_page_array($tabs){
		$tabs[$this->plugin_slug] = array(
				'page_title'        => __('Settings','lsx-tour-importer'),				
				'page_description'  => __('Enter your API key to enable your Wetu importer.','lsx-tour-importer'),                  	
				'menu_title'        => __('Importer','lsx-tour-importer'),                           						
				'template'          => LSX_TOUR_IMPORTER_PATH.'settings/'.$this->plugin_slug.'.php',  	
				'default'	 		=> false  	
		);
		return $tabs;
	}	

	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
        add_management_page(
            __('LSX Tour Importer','lsx-tour-importer'),
            __('LSX Tour Importer','lsx-tour-importer'),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_importer_page' )
        );		
	}	

	/**
	 * Display the importer administration screen
	 */
	public function display_importer_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <form method="post" action="tools.php">
            	helllo
            </form>
        </div>
        <?php		
	}		

	/**
	 * Enqueue the JS needed to contact wetu and return your result.
	 */
	public function admin_scripts() {

	}	

	/**
	 * Display the search form for the admin screen
	 */
	public function search_form() {

	}



}
$lsx_tour_importer = Lsx_Tour_Importer::get_instance();
