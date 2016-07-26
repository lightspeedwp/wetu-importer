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
            __('LSX Importer','lsx-tour-importer'),
            __('LSX Importer','lsx-tour-importer'),
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
	            </ul>  

	            
		            <h3><?php _e('Additional Tools','lsx-tour-importer'); ?></h3>
		            <ul>
		            	<li><a href="<?php echo admin_url('tools.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=connect_accommodation"><?php _e('Connect Accommodation','lsx-tour-importer'); ?></a> <small><?php _e('If you already have accommodation, you can "connect" it with its WETU counter part, so it works with the importer.','lsx-tour-importer'); ?></small></li>
		            	<?php if(class_exists('Lsx_Banners')){ ?>
		            		<li><a href="<?php echo admin_url('tools.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=banners"><?php _e('Sync High Res Banner Images','lsx-tour-importer'); ?></a></li>
		            	<?php } ?>
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
				<th style="" class="manage-column column-title " id="title" style="width:50%;" scope="col">Title</th>
				<th style="" class="manage-column column-date" id="date" scope="col">Date</th>
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
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
					<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
					<input type="checkbox" id="cb-select-all-1">
				</th>
				<th style="" class="manage-column column-title" scope="col">Title</th>
				<th style="" class="manage-column column-date" scope="col">Date</th>
				<th style="" class="manage-column column-ssid" scope="col">WETU ID</th>
			</tr>
		</tfoot>
	<?php 
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function set_taxonomy($taxonomy,$terms,$id) {
        $result=array();
        if(!empty($data))
        {
            foreach($data as $k)
            {
                if($id)
                {
                    if(!$term = term_exists(trim($k), $tax))
                    {
                        $term = wp_insert_term(trim($k), $tax);
                        if ( is_wp_error($term) )
                        {
                            echo $term->get_error_message();
                        }
                        else
                        {
                            wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
                        }
                    }
                    else
                    {
                        wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
                    }
                }
                else
                {
                    $result[]=trim($k);
                }
            }
        }
        return $result;
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function team_member_checkboxes() {
		if(post_type_exists('team')) { ?>
    		<ul>
    			<?php
    				$team_args=array(
    					'post_type'	=>	'team',
    					'post_status' => 'publish',
    					'nopagin' => true,
    					'fields' => 'ids'
    				);
    				$team_members = new WP_Query($team_args);
    					if($team_members->have_posts()){
    						foreach($team_members->posts as $member){ ?>
    							<li><input class="team" type="checkbox" value="<?php echo $member; ?>" /> <?php echo get_the_title($member); ?></li>
    						<?php }
    					}else{ ?>
    							<li><input class="team" type="checkbox" value="0" /> <?php _e('None','lsx-tour-importer'); ?></li>
    					<?php }
    				?>
    		</ul>
    	<?php }		
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function taxonomy_checkboxes($taxonomy=false) {
		$return = '';
		if(false !== $taxonomy){
			$return .= '<ul>';
			$terms = get_terms($taxonomy,array('empty'=>true));
			if(!is_wp_error($terms)){
				foreach($terms as $term){
					$return .= '<li><input class="'.$taxonomy.'" type="checkbox" value="'.$term->term_id.'" /> '.$term->name.'</li>';
				}
			}else{
				$return .= '<li><input type="checkbox" value="" /> '.__('None','lsx-tour-importer').'</li>';
			}
			$return .= '</ul>';
		}
		return $return;		
	}	

	/**
	 * Saves the room data
	 */
	public function save_custom_field($value=false,$meta_key,$id,$decrease=false) {
		if(false !== $value){
			if(false !== $decrease){
				$value = intval($value);
				$value--;
			}
			if(false !== $id && '0' !== $id){
	        	$prev = get_post_meta($id,$meta_key,true);
	        	update_post_meta($id,$meta_key,$value,$prev);
	        }else{
	        	add_post_meta($id,$meta_key,$value,true);
	        }	
		}
	}		
}
$lsx_tour_importer_admin = new Lsx_Tour_Importer_Admin();