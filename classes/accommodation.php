<?php
/**
 * @package   Lsx_Tour_Importer_Accommodation
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer_Accommodation extends Lsx_Tour_Importer_Admin {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'accommodation';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url = false;	

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$temp_options = get_option('_lsx_lsx-settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
		}
		$this->url = 'http://wetu.com/API/Pins/'.$this->options['api_key'].'/List';

		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );
		add_action('wp_ajax_lsx_tour_importer',array($this,'process_ajax_search'));	
		add_action('wp_ajax_nopriv_lsx_tour_importer',array($this,'process_ajax_search'));		

		add_action('wp_ajax_lsx_import_items',array($this,'process_ajax_import'));	
		add_action('wp_ajax_nopriv_lsx_import_items',array($this,'process_ajax_import'));			
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
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

            <?php $this->update_options_form(); ?>

            <?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>" />
				
				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>
				
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

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','lsx-tour-importer'); ?>" /> 
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','lsx-tour-importer'); ?>" />
				</p>
			</form> 

			<div style="display:none;" class="import-list-wrapper">
				<br />        
				<form method="get" action="" id="import-list">
					<h3><?php _e('Options'); ?></h3> 
					<?php $this->team_member_checkboxes(); ?>

					<h3><?php _e('Your List'); ?></h3> 
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php _e('Sync','lsx-tour-importer'); ?>" /></p>
				</form>
			</div>
        </div>
        <?php
	}

	/**
	 * search_form
	 */
	public function search_form() {
	?>
        <form class="ajax-form" id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php" data-type="accommodation">
        	<input type="hidden" name="page" value="<?php echo $this->tab_slug; ?>" />

        	<h3><?php _e('Search','lsx-tour-importer'); ?></h3>
        	<p>
        		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
        	</p>

        	<p><a class="advanced-search-toggle" href="#"><?php _e('Bulk Search','lsx-tour-importer'); ?></a></p>
        	<div class="advanced-search" style="display:none;">
        		<p><?php _e('Enter several keywords, each on a new line.','lsx-tour-importer'); ?></p>
        		<textarea rows="10" cols="40" name="bulk-keywords"></textarea>
        	</div>

            <div class="ajax-loader" style="display:none;width:100%;text-align:center;">
            	<img style="width:64px;" src="<?php echo LSX_TOUR_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>

            <div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
            	<img style="width:32px;" src="<?php echo LSX_TOUR_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>            
        </form>	
	<?php 
	}	

	/**
	 * search_form
	 */
	public function update_options_form() {

		echo '<div style="display:none;" class="wetu-status"><h3>'.__('Wetu Status','lsx-tour-importer').'</h3>';
		$last_refresh_date = get_option('lsx_tour_operator_accommodation_timestamp',false);
		
		if(!isset($_GET['refresh_options'])){
			if(false === $last_refresh_date){
				echo __('Please update your accommodation list.','lsx-tour-importer');
			}else{
				echo __('Last Update - ','lsx-tour-importer').$last_refresh_date;
			}			
		?>
	        <form id="<?php echo $this->plugin_slug; ?>-update-form" method="get" action="tools.php">
	        	<input type="hidden" name="page" value="<?php echo $this->plugin_slug; ?>" />
	        	<input type="hidden" name="tab" value="<?php echo $this->tab_slug; ?>" />
	        	<input type="hidden" name="refresh_options" value="true" />
	        	<input class="submit button button-primary" type="submit" value="<?php _e('Update','lsx-tour-importer'); ?>" />
	        </form>	
		<?php 
		}elseif('true' === $_GET['refresh_options']){
			$this->update_options();
			?>
			<p><?php _e('Your accommodation list has been updated, please use the search form below to find what you want.','lsx-tour-importer'); ?></p>
			<?php
		}
		echo '</div>';
	}


	/**
	 * Save the list of Accommodation into an option
	 */
	public function update_options() {
		if(isset($_GET['page']) && $this->plugin_slug === $_GET['page']
		 && isset($_GET['refresh_options']) && 'true' === $_GET['refresh_options']
		 && isset($_GET['tab']) && $this->tab_slug === $_GET['tab']) {
			$data= file_get_contents($this->url);
			$accommodation  = json_decode($data, true);
			if (!empty($data)) {
				update_option('lsx_tour_operator_accommodation',$data);
				update_option('lsx_tour_operator_accommodation_timestamp',date("d M Y - h:ia",strtotime("+2 Hours")));
			}
		}
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 */
	public function find_current_accommodation() {
		global $wpdb;
		$return = array();

		$current_accommodation = $wpdb->get_results("
					SELECT key1.post_id,key1.meta_value
					FROM {$wpdb->postmeta} key1

					INNER JOIN  {$wpdb->posts} key2 
    				ON key1.post_id = key2.ID
					
					WHERE key1.meta_key = 'lsx_wetu_id'
					AND key2.post_type = 'accommodation'
		");
		if(null !== $current_accommodation && !empty($current_accommodation)){
			foreach($current_accommodation as $accom){
				$return[$accom->meta_value] = $accom;
			}
		}
		return $return;
	}	

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === 'accommodation'){
			$accommodation = get_option('lsx_tour_operator_accommodation',false);
			if ( false !== $accommodation && isset($_POST['keyword'] )) {
				$searched_items = false;
				$search_keyword = urldecode($_POST['keyword']);
				$accommodation = json_decode($accommodation);
				if (!empty($accommodation)) {
					$current_accommodation = $this->find_current_accommodation();

					foreach($accommodation as $row_key => $row){
						if(stripos($row->name, $search_keyword) !== false){

							$row->post_id = 0;
							if(false !== $current_accommodation && array_key_exists($row->id, $current_accommodation)){
								$row->post_id = $current_accommodation[$row->id]->post_id;
							}
							$searched_items[sanitize_title($row->name)] = $this->format_row($row);
						}
					}		
				}
				ksort($searched_items);
				$return = implode($searched_items);
			}
		}
		print_r($return);
		die();
	}

	public function format_row($row = false){
		if(false !== $row){

			$status = 'import';
			if(0 !== $row->post_id){
				$status = '<a href="'.admin_url('/post.php?post='.$row->post_id.'&action=edit').'" target="_blank">'.get_post_status($row->post_id).'</a>';
			}

			$row_html = '
			<tr class="post-'.$row->post_id.' type-tour" id="post-'.$row->post_id.'">
				<th class="check-column" scope="row">
					<label for="cb-select-'.$row->id.'" class="screen-reader-text">'.$row->name.'</label>
					<input type="checkbox" data-identifier="'.$row->id.'" value="'.$row->post_id.'" name="post[]" id="cb-select-'.$row->id.'">
				</th>
				<td class="post-title page-title column-title">
					<strong>'.$row->name.'</strong> - '.$status.'
				</td>
				<td class="date column-date">
					<abbr title="'.date('Y/m/d',strtotime($row->last_modified)).'">'.date('Y/m/d',strtotime($row->last_modified)).'</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					'.$row->id.'
				</td>
			</tr>';		
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === 'accommodation' && isset($_POST['wetu_id'])){
			
			$wetu_id = $_POST['wetu_id'];
			if(isset($_POST['post_id'])){
				$post_id = $_POST['post_id'];	
			}else{
				$post_id = 0;
			}

            $jdata=file_get_contents("http://wetu.com/API/Pins/".$this->options['api_key']."/Get?ids=".$wetu_id);

            if($jdata)
            {
                $adata=json_decode($jdata,true);
                if(!empty($adata))
                {
                	$return = $this->import_row($adata,$wetu_id,$post_id);
                }
            }
		}
		print_r($return);
		die();
	}	

	/**
	 * Connect to wetu
	 */
	public function import_row($data,$wetu_id,$id=0) {

        if(trim($data[0]['type'])=='Accommodation')
        {
	        $post_name = $data_post_content = $data_post_excerpt = '';
	        $post = array(
	          'post_type'		=> 'accommodation',
	        );

	        if(false !== $id && '0' !== $id){
	        	$post['ID'] = $id;
	        	$id = wp_update_post($post);
	        	$prev_date = get_post_meta($id,'lsx_wetu_modified_date',true);
	        	update_post_meta($id,'lsx_wetu_modified_date',strtotime($data[0]['last_modified']),$prev_date);
	        }else{

		        //Set the name
		        if(!empty($data[0]['name'])){
		            $post_name = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'accommodation', 0);
		        }
		        //Set the content
		        if(!empty($data[0]['content']['general_description']))
		        {
		            $data_post_content = $data[0]['content']['general_description'];
		        }
	    	 	//Set the excerpt
		        if(!empty($data[0]['content']['extended_description'])){
		            $data_post_excerpt = $data[0]['content']['extended_description'];
		        }elseif(!empty($data[0]['content']['teaser_description'])){
		        	$data_post_excerpt = $data[0]['content']['teaser_description'];
		        }

	        	$post['post_content'] = wp_strip_all_tags($data_post_content);
	        	$post['post_excerpt'] = $data_post_excerpt;
	        	$post['post_name'] = $post_name;

	        	$post['post_title'] = $data[0]['name'];
	        	$post['post_status'] = 'pending';
	        	$id = wp_insert_post($post);

	        	//Save the WETU ID and the Last date it was modified.
	        	if(false !== $id){
	        		add_post_meta($id,'lsx_wetu_id',$wetu_id);
	        		add_post_meta($id,'lsx_wetu_modified_date',strtotime($data[0]['last_modified']));
	        	}
	        }

	        $this->set_map_data($data,$id);

	        $this->set_location_taxonomy($data,$id);

	        $this->set_taxonomy_style($data,$id);

	        $this->set_room_data($data,$id);

	        $this->set_rating($data,$id);

	        $this->set_checkin_checkout($data,$id);


	        
        }
        return $id;
	}	
	
	/**
	 * Saves the longitude and lattitude, as well as sets the map marker.
	 */
	public function set_map_data($data,$id) {
		$longitude = $latitude = $address = false;
		$zoom = '15';	

		if(isset($data[0]['position'])){

			if(isset($data[0]['position']['driving_latitude'])){
				$latitude = $data[0]['position']['driving_latitude'];
			}elseif(isset($data[0]['position']['latitude'])){
				$latitude = $data[0]['position']['latitude'];
			}

			if(isset($data[0]['position']['driving_longitude'])){
				$longitude = $data[0]['position']['driving_longitude'];
			}elseif(isset($data[0]['position']['longitude'])){
				$longitude = $data[0]['position']['longitude'];
			}		

		}
		if(isset($data[0]['content']) && isset($data[0]['content']['contact_information'])){
			if(isset($data[0]['content']['contact_information']['address'])){
				$address = strip_tags($data[0]['content']['contact_information']['address']);

				$address = explode("\n",$address);
				foreach($address as $bitkey => $bit){
					$bit = ltrim(rtrim($bit));
					if(false === $bit || '' === $bit || null === $bit or empty($bit)){
						unset($address[$bitkey]);
					}
				}
				$address = implode(', ',$address);
				$address = str_replace(', , ', ', ', $address);
			}	
		}


		if(false !== $longitude){
			$location_data = array(
				'address'	=>	$address,
				'lat'		=>	$latitude,
				'long'		=>	$longitude,
				'zoom'		=>	$zoom,
				'elevation'	=>	'',
			);
			if(false !== $id && '0' !== $id){
	        	$prev = get_post_meta($id,'location',true);
	        	update_post_meta($id,'location',$location_data,$prev);
	        }else{
	        	add_post_meta($id,'location',$location_data,true);
	        }
		}
	}
	/**
	 * Saves the longitude and lattitude, as well as sets the map marker.
	 */
	public function set_location_taxonomy($data,$id) {
		$taxonomy = 'location';
		$terms = false;
		if(isset($data[0]['position'])){
			$country_id = 0;
			if(isset($data[0]['position']['country'])){

				if(!$term = term_exists(trim($data[0]['position']['country']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['country']), 'location');
		            if ( is_wp_error($term) ){
		            	echo $term->get_error_message();
		            }
		            else {
		            	wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		            }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }
		        $country_id = intval($term['term_id']);
		    }

			if(isset($data[0]['position']['destination'])){

				$tax_args = array('parent'=>$country_id);
				if(!$term = term_exists(trim($data[0]['position']['destination']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['destination']), 'location', $tax_args);
		            if ( is_wp_error($term) ){echo $term->get_error_message();}
		            else { wp_set_object_terms( $id, intval($term['term_id']), 'location',true); }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }				
			}		
		}
	}

	/**
	 * Saves the location
	 */
	public function set_taxonomy_style($data,$id) {
		$terms = false;
		if(isset($data[0]['category'])){
			if(!$term = term_exists(trim($data[0]['category']), 'travel-style'))
	        {
	            $term = wp_insert_term(trim($data[0]['category']), 'travel-style');
	            if ( is_wp_error($term) ){echo $term->get_error_message();}
	            else { wp_set_object_terms( $id, intval($term['term_id']), 'travel-style',true); }
	        }
	        else
	        {
	            wp_set_object_terms( $id, intval($term['term_id']), 'travel-style',true);
	        }				
		}
	}	

	/**
	 * Saves the category as the travel style
	 */
	public function set_travel_style($data,$id) {
		$taxonomy = 'travel-style';
		$terms = false;
		if(isset($data[0]['category'])){
			$country_id = 0;
			if(isset($data[0]['position']['country'])){

				if(!$term = term_exists(trim($data[0]['position']['country']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['country']), 'location');
		            if ( is_wp_error($term) ){
		            	echo $term->get_error_message();
		            }
		            else {
		            	wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		            }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }
		        $country_id = intval($term['term_id']);
		    }

			if(isset($data[0]['position']['destination'])){

				$tax_args = array('parent'=>$country_id);
				if(!$term = term_exists(trim($data[0]['position']['destination']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['destination']), 'location', $tax_args);
		            if ( is_wp_error($term) ){echo $term->get_error_message();}
		            else { wp_set_object_terms( $id, intval($term['term_id']), 'location',true); }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }				
			}		
		}
	}	

	/**
	 * Saves the room data
	 */
	public function set_room_data($data,$id) {
		if(!empty($data[0]['rooms']) && is_array($data[0]['rooms'])){
			$rooms = false;
			$room_count = count($data[0]['rooms']);

			foreach($data[0]['rooms'] as $room){
				$temp_room = '';
				if(isset($room['name'])){
					$temp_room['title'] = $room['name'];
				}
				if(isset($room['description'])){
					$temp_room['description'] = strip_tags($room['description']);
				}			
				$temp_room['price'] = 0;
				$temp_room['type'] = 'room';
				$rooms[] = $temp_room;
			}

			if(false !== $id && '0' !== $id){
				delete_post_meta($id, 'units');				
			}
			foreach($rooms as $room){
		        add_post_meta($id,'units',$room,false);			
			}

			if(false !== $id && '0' !== $id){
	        	$prev_rooms = get_post_meta($id,'number_of_rooms',true);
	        	update_post_meta($id,'number_of_rooms',$room_count,$prev_rooms);
	        }else{
	        	add_post_meta($id,'number_of_rooms',$room_count,true);
	        }
		}
	}

	/**
	 * Saves the room data
	 */
	public function set_rating($data,$id) {

		if(!empty($data[0]['features']) && isset($data[0]['features']['star_authority'])){
			$rating_type = $data[0]['features']['star_authority'];	
		}else{
			$rating_type = 'Unspecified2';
		}
		$this->save_custom_field($rating_type,'rating_type',$id);

		if(!empty($data[0]['features']) && isset($data[0]['features']['stars'])){
			$this->save_custom_field($data[0]['features']['stars'],'rating',$id,true);	
		}
	}	

	/**
	 * Saves the room data
	 */
	public function set_checkin_checkout($data,$id) {

		if(!empty($data[0]['features']) && isset($data[0]['features']['check_in_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_in_time']);
			$time = date('h:ia',strtotime());
			$this->save_custom_field($time,'checkin_time',$id);
		}
		if(!empty($data[0]['features']) && isset($data[0]['features']['check_out_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_out_time']);
			$time = date('h:ia',strtotime($time));
			$this->save_custom_field($time,'checkout_time',$id);
		}
	}			
}
$lsx_tour_importer_accommodation = new Lsx_Tour_Importer_Accommodation();