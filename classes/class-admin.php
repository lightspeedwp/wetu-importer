<?php
/**
 * @package   WETU_Importer_Admin
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Admin extends WETU_Importer {

	/**
	 * The previously attached images
	 *
	 * @var      array()
	 */
	public $found_attachments = array();	

	/**
	 * The gallery ids for the found attachements
	 *
	 * @var      array()
	 */
	public $gallery_meta = array();		

	/**
	 * the featured image id
	 *
	 * @var      int
	 */
	public $featured_image = false;

	/**
	 * the banner image
	 *
	 * @var      int
	 */
	public $banner_image = false;	

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array($this,'admin_scripts') ,11 );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ),20 );
	}

	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
		add_submenu_page( 'tour-operator',esc_html__( 'Importer', 'tour-operator' ), esc_html__( 'Importer', 'tour-operator' ), 'manage_options', 'wetu-importer', array( $this, 'display_page' ) );
	}

	/**
	 * Enqueue the JS needed to contact wetu and return your result.
	 */
	public function admin_scripts() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$min = '';
		} else {
			$min = '.min';
		}

		if(is_admin() && isset($_GET['page']) && $this->plugin_slug === $_GET['page']){
			wp_enqueue_script( 'wetu-importers-script', WETU_IMPORTER_URL . 'assets/js/wetu-importer' . $min . '.js', array( 'jquery' ), WETU_IMPORTER_VER, true );
			wp_localize_script( 'wetu-importers-script', 'lsx_tour_importer_params', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			) );			
		}
	}			

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

            <?php if(!isset($_GET['tab'])){ ?>
	            <h2><?php _e('Welcome to the LSX Wetu Importer','wetu-importer'); ?></h2>
	            <p>If this is the first time you are running the import, then follow the steps below.</p>
	            <ul>
                    <li>Step 1 - Import your <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=tour"><?php _e('Tours','wetu-importer'); ?></a></li>
	            	<li>Step 2 - The tour import will have created draft <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=accommodation"><?php _e('accommodation','wetu-importer'); ?></a> that will need to be imported.</li>
                    <li>Step 3 - Lastly import the <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=destination"><?php _e('destinations','wetu-importer'); ?></a> draft posts created during the previous two steps.</li>
	            </ul>

		            <h3><?php _e('Additional Tools','wetu-importer'); ?></h3>
		            <ul>
		            	<li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=connect_accommodation"><?php _e('Connect Accommodation','wetu-importer'); ?></a> <small><?php _e('If you already have accommodation, you can "connect" it with its WETU counter part, so it works with the importer.','wetu-importer'); ?></small></li>
		            	<?php if(class_exists('Lsx_Banners')){ ?>
		            		<li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=banners"><?php _e('Sync High Res Banner Images','wetu-importer'); ?></a></li>
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
	public function team_member_checkboxes($selected=array()) {
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
    							<li><input class="team" <?php $this->checked($selected,$member); ?> type="checkbox" value="<?php echo $member; ?>" /> <?php echo get_the_title($member); ?></li>
    						<?php }
    					}else{ ?>
    							<li><input class="team" type="checkbox" value="0" /> <?php _e('None','wetu-importer'); ?></li>
    					<?php }
    				?>
    		</ul>
    	<?php }		
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function taxonomy_checkboxes($taxonomy=false,$selected=array()) {
		$return = '';
		if(false !== $taxonomy){
			$return .= '<ul>';
			$terms = get_terms(array('taxonomy'=>$taxonomy,'hide_empty'=>false));

			if(!is_wp_error($terms)){
				foreach($terms as $term){
					$return .= '<li><input class="'.$taxonomy.'" '.$this->checked($selected,$term->term_id).' type="checkbox" value="'.$term->term_id.'" /> '.$term->name.'</li>';
				}
			}else{
				$return .= '<li><input type="checkbox" value="" /> '.__('None','wetu-importer').'</li>';
			}
			$return .= '</ul>';
		}
		return $return;		
	}

	/**
	 * Saves the room data
	 */
	public function save_custom_field($value=false,$meta_key,$id,$decrease=false,$unique=true) {
		if(false !== $value){
			if(false !== $decrease){
				$value = intval($value);
				$value--;
			}
			$prev = get_post_meta($id,$meta_key,true);

			if(false !== $id && '0' !== $id && false !== $prev && true === $unique){
				update_post_meta($id,$meta_key,$value,$prev);
			}else{
				add_post_meta($id,$meta_key,$value,$unique);
			}
		}
	}

	/**
	 * grabs any attachments for the current item
	 */
	public function find_attachments($id=false) {
		if(false !== $id){
			if(empty($this->found_attachments)){

		    	$attachments_args = array(
		    			'post_parent' => $id,
		    			'post_status' => 'inherit',
		    			'post_type' => 'attachment',
		    			'order' => 'ASC',
                        'nopagin' => 'true',
                        'posts_per_page' => '-1'
		    	);   	
		    	 
		    	$attachments = new WP_Query($attachments_args);
		    	if($attachments->have_posts()){
		    		foreach($attachments->posts as $attachment){
		    			$this->found_attachments[$attachment->ID] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
		    			$this->gallery_meta[] = $attachment->ID;
		    		}
		    	}
			}

			print_r($this->found_attachments);
		}
	}

	/**
	 * Checks to see if an item is selected.
     *
     * @param $haystack array|string
     * @param $needle string
     * @return string
	 */
	public function checked($haystack=false,$needle='') {
	    $return = '';
	    if(!is_array($haystack)){
			$haystack = array($haystack);
        }
        if(in_array($needle,$haystack)){
			$return = 'checked="checked"';
        }
	    echo $return;
	}
}
$wetu_importer_admin = new WETU_Importer_Admin();