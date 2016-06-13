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
            array( $this, 'display_page' )
        );		
	}	

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

            <?php if(!isset($_GET['tab'])){ ?>
	            <h2><?php _e('Welcome to the LSX Wetu Importer','lsx-tour-importer'); ?></h2>  
	            <p>Please select the type of content you want to import from the list below.</p>
	            <ul>
	            	<li><a href="<?php echo admin_url('tools.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=accommodation"><?php _e('Accommodation','lsx-tour-importer'); ?></a></li>
	            	<li><?php _e('Tours','lsx-tour-importer'); ?></li>
	            </ul>  
            <?php } else {
            	do_action('lsx_tour_importer_admin_tab_'.$_GET['tab']);
            } ?>
        </div>
        <?php
	}

	/**
	 * The header of the item list
	 */
	public function table_header() {
	?>
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
				<th style="" class="manage-column column-ssid" id="ssid" scope="col">WETU ID</th>
			</tr>
		</thead>
	<?php 
	}	

	/**
	 * The footer of the item list
	 */
	public function table_footer() {
	?>
		<tfoot>
			<tr>
				<th style="" class="manage-column column-cb check-column" scope="col"><label for="cb-select-all-2" class="screen-reader-text">Select All</label><input type="checkbox" id="cb-select-all-2"></th>
				<th style="" class="manage-column column-title sortable desc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th>
				<th style="" class="manage-column column-date sortable asc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>
				<th style="" class="manage-column column-ssid" scope="col">ID</th>
			</tr>
		</tfoot>
	<?php 
	}	
}
$lsx_tour_importer_admin = new Lsx_Tour_Importer_Admin();