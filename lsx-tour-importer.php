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
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $list_url = false;	

	/**
	 * The url to import items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $import_url = false;	

	/**
	 * The url to import images from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $import_scaling_url = false;		

	/**
	 * scale the images on import or not
	 *
	 * @since 0.0.1
	 *
	 * @var      boolean
	 */
	public $scale_images = false;
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( $this,'plugin_activate') );
		$temp_options = get_option('_lsx_lsx-settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
			$this->set_variables();
		}

		add_filter( 'lsx_framework_settings_tabs', array( $this, 'settings_page_array') );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ) );
		add_action( 'admin_enqueue_scripts', array($this,'admin_scripts') ,11 );

		add_action('wp_ajax_lsx_tour_importer',array($this,'process_ajax_search'));	
		add_action('wp_ajax_nopriv_lsx_tour_importer',array($this,'process_ajax_search'));
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
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		if(isset($this->options['token'])){
			$this->list_url = 'http://wetu.com/API/Pins/'.$this->options['token'].'/List';
			$this->import_url = 'http://wetu.com/API/Pins/'.$this->options['token'].'/Get?ids=';

			if(isset($this->options['image_scaling'])){
				$this->scale_images = true;
				$width = '800';
				if(isset($this->options['width']) && '' !== $this->options['width']){
					$width = $this->options['width'];
				}
				$height = '600';
				if(isset($this->options['height']) && '' !== $this->options['height']){
					$height = $this->options['height'];
				}
				$cropping = 'raw';
				if(isset($this->options['cropping']) && '' !== $this->options['cropping']){
					$cropping = $this->options['cropping'];
				}				
				$this->image_scaling_url = 'https://wetu.com/ImageHandler/'.$cropping.$width.'x'.$height.'/';
			}	
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
            <form id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php">
            	<input type="text" name="page" value="<?php echo $this->plugin_slug; ?>" />

            	<h3><?php _e('Settings','lsx-tour-importer'); ?></h3>

            	<p><label for="keyword"><?php _e('Choose what type of content you want to import.','lsx-tour-importer'); ?></label><br />

	            	<select class="content-type" name="content_type">
	            		<option selected="selected" value="accommodation"><?php _e('Accommodation','lsx-tour-importer'); ?></option>
	            		<option value="tours"><?php _e('Tours','lsx-tour-importer'); ?></option>
	            	</select>
            	</p>

            	<div class="tour-options" style="display:none;">
            		<h3><?php _e('Tour Options','lsx-tour-importer'); ?></h3>
            		<p>
            			<label for="own-itineraries"><?php _e('Search from my itineraries only','lsx-tour-importer'); ?></label><br />
            			<input type="checkbox" name="own-itineraries" value="true"> 
            		</p>
            	</div>

            	<h3><?php _e('Search','lsx-tour-importer'); ?></h3>
            	<p>
            		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
            	</p>

	            <div class="ajax-loader" style="display:none;width:100%;text-align:center;width:64px;">
	            	<img src="<?php echo LSX_TOUR_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
	            </div>
            	
            </form>




			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $post_type; ?>" />
				
				<table class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
								<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
								<input type="checkbox" id="cb-select-all-1">
							</th>
							<th style="" class="manage-column column-title sortable desc" id="title" style="width:50%;" scope="col">
								<span>Title</span><span class="sorting-indicator"></span>
							</th>
							<th style="" class="manage-column column-date sortable asc" id="date" scope="col">
									<span>Date</span><span class="sorting-indicator"></span>
							</th>
							<th style="" class="manage-column column-ssid" id="ssid" scope="col">ID</th>
						</tr>
					</thead>
				
					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php _e('Enter a title to search for and press enter','lsx-tour-importer'); ?></label>
							</th>
							<td class="post-title page-title column-title">
								<strong>
									<?php _e('Enter a title to search for','lsx-tour-importer'); ?>
								</strong>
							</td>
							<td class="date column-date">							
							</td>
							<td class="ssid column-ssid">
							</td>
						</tr>									
					</tbody>

					<tfoot>
						<tr>
							<th style="" class="manage-column column-cb check-column" scope="col"><label for="cb-select-all-2" class="screen-reader-text">Select All</label><input type="checkbox" id="cb-select-all-2"></th>
							<th style="" class="manage-column column-title sortable desc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th>
							<th style="" class="manage-column column-date sortable asc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>
							<th style="" class="manage-column column-ssid" scope="col">ID</th>
						</tr>
					</tfoot>
				</table>
			</form>          
        </div>
        <?php		
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
	 * Connect to wetu
	 */
	public function process_ajax_search() {
		if(isset($_POST['action']) && $_POST['action'] == $this->plugin_slug){

			
			$data= file_get_contents($this->list_url);
			$all_data  = json_decode($data, true);

			print_r($_GET['keyword']);

			if(isset($_GET['keyword'])){
				$searched_items = false;
				$search_keyword = urldecode($_GET['keyword']);
				if (!empty($all_data)) {
					foreach($all_data as $row_key => $row){
						if(stripos($row->name, $search_keyword) !== false){
							$searched_items[] = $row;
						}
					}		
				}	
			}
			print_r($searched_items);
		}	
		die();
	}


}
$lsx_tour_importer = Lsx_Tour_Importer::get_instance();
