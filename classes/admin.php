<?php
/**
 * @package   Lsx_Tour_Importer_Admin
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer_Admin extends Lsx_Tour_Importer {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_filter( 'lsx_framework_settings_tabs', array( $this, 'settings_page_array') );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ) );
		add_action( 'admin_enqueue_scripts', array($this,'admin_scripts') ,11 );	
	}

	/**
	 * Enqueue the JS needed to contact wetu and return your result.
	 */
	public function admin_scripts() {
		if(is_admin() && isset($_GET['page']) && $this->plugin_slug === $_GET['page']){
			wp_enqueue_script( 'lsx-tour-importers-script', LSX_TOUR_IMPORTER_URL.'assets/js/lsx-tour-importer.js');
			wp_localize_script( 'lsx-tour-importers-script', 'lsx_tour_importer_params', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			) );			
		}
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

            <h2><?php _e('Welcome to the LSX Wetu Importer','lsx-tour-importer'); ?></h2>  
            <ul>
            	<li><a href="<?php echo admin_url('tools.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=accommodation"><?php _e('Accommodation','lsx-tour-importer'); ?></a></li>
            	<li><?php _e('Tours','lsx-tour-importer'); ?></li>
            </ul>   
        </div>
        <?php
	}
}
$lsx_tour_importer_admin = new Lsx_Tour_Importer_Admin();