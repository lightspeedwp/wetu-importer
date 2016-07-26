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

		$temp_options = get_option('_lsx_lsx-settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
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
				$cropping = 'c';
				if(isset($this->options['cropping']) && '' !== $this->options['cropping']){
					$cropping = $this->options['cropping'];
				}				
				$this->image_scaling_url = 'https://wetu.com/ImageHandler/'.$cropping.$width.'x'.$height.'/';
			}
		}			
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
				
				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','lsx-tour-importer'); ?>" /> 
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','lsx-tour-importer'); ?>" />
				</p>				

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

					<div class="row">
						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('What content to Sync from WETU'); ?></h3>
							<ul>
								<li><input class="content" type="checkbox" name="content[]" value="description" /> <?php _e('Description','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="excerpt" /> <?php _e('Excerpt','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="gallery" /> <?php _e('Main Gallery','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="category" /> <?php _e('Category','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="location" /> <?php _e('Location','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="destination" /> <?php _e('Connect Destinations','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="checkin" /> <?php _e('Check In / Check Out','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="rating" /> <?php _e('Rating','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="rooms" /> <?php _e('Rooms','lsx-tour-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="videos" /> <?php _e('Videos','lsx-tour-importer'); ?></li>
							</ul>
						</div>
						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('Assign a Team Member'); ?></h3> 
							<?php $this->team_member_checkboxes(); ?>
						</div>

						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('Assign a Safari Brand'); ?></h3> 
							<?php echo $this->taxonomy_checkboxes('accommodation-brand'); ?>	
						</div>	

						<br clear="both" />			
					</div>


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

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php _e('Completed'); ?></h3>
				<ul>
				</ul>
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

        	<h3><span class="dashicons dashicons-search"></span> <?php _e('Search','lsx-tour-importer'); ?></h3>
        	<div class="normal-search">
        		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
        	</div>
        	<div class="advanced-search hidden" style="display:none;">
        		<p><?php _e('Enter several keywords, each on a new line.','lsx-tour-importer'); ?></p>
        		<textarea rows="10" cols="40" name="bulk-keywords"></textarea>
        		<input class="button button-primary submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
        	</div>    

        	<p><a class="advanced-search-toggle" href="#"><?php _e('Bulk Search','lsx-tour-importer'); ?></a> | <a class="my-accommodation-search-toggle" href="#"><?php _e('My Accommodation','lsx-tour-importer'); ?></a></p>


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
				$keyphrases = $_POST['keyword'];
				$my_accommodation = false;

				if(!is_array($keyphrases)){
					$keyphrases = array($keyphrases);
				}
				foreach($keyphrases as &$keyword){
					$keyword = ltrim(rtrim($keyword));
				}

				
				if(in_array('my-posts',$keyphrases)){
					$my_accommodation = true;
				}

				$accommodation = json_decode($accommodation);
				if (!empty($accommodation)) {
					$current_accommodation = $this->find_current_accommodation();

					foreach($accommodation as $row_key => $row){

						//Search through each keyword.
						foreach($keyphrases as $keyphrase){

							//Make sure the keyphrase is tured into an array
							$keywords = explode(" ",$keyphrase);
							if(!is_array($keywords)){
								$keywords = array($keywords);
							}

							if(true === $my_accommodation || $this->multineedle_stripos(ltrim(rtrim($row->name)), $keywords) !== false){
								$row->post_id = 0;
								if(false !== $current_accommodation && array_key_exists($row->id, $current_accommodation)){
									$row->post_id = $current_accommodation[$row->id]->post_id;
								}

								if(false === $my_accommodation || (true === $my_accommodation && 0 !== $row->post_id)){
									$searched_items[sanitize_title($row->name)] = $this->format_row($row);
								}

								//print_r($row->name);
								//die();
							}
						}	
					}		
				}

				if(false !== $searched_items){
					ksort($searched_items);
					$return = implode($searched_items);
				}
			}
		}
		print_r($return);
		die();
	}

	/**
	 * Does a multine search
	 */	
	public function multineedle_stripos($haystack, $needles, $offset=0) {
		$found = false;
		$needle_count = count($needles);
	    foreach($needles as $needle) {
	    	if(false !== stripos($haystack, $needle, $offset)){
	        	$found[] = true;
	    	}
	    }
	    if(false !== $found && $needle_count === count($found)){ 
	    	return true;
		}else{
			return false;
		}
	}

	/**
	 * Formats the row for output on the screen.
	 */	
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

			if(isset($_POST['team_members'])){
				$team_members = $_POST['team_members'];	
			}else{
				$team_members = false;
			}

			if(isset($_POST['safari_brands'])){
				$safari_brands = $_POST['safari_brands'];	
			}else{
				$safari_brands = false;
			}			

			if(isset($_POST['content']) && is_array($_POST['content']) && !empty($_POST['content'])){
				$content = $_POST['content'];	
			}else{
				$content = false;
			}

            $jdata=file_get_contents("http://wetu.com/API/Pins/".$this->options['api_key']."/Get?ids=".$wetu_id);
            if($jdata)
            {
                $adata=json_decode($jdata,true);
                if(!empty($adata))
                {
                	$return = $this->import_row($adata,$wetu_id,$post_id,$team_members,$content,$safari_brands);
                }
            }
		}
		$this->format_completed_row($return);
		die();
	}	
	/**
	 * Formats the row for the completed list.
	 */
	public function format_completed_row($response){
		echo '<li class="post-'.$response.'"><span class="dashicons dashicons-yes"></span> <a target="_blank" href="'.get_permalink($response).'">'.get_the_title($response).'</a></li>';
	}
	/**
	 * Connect to wetu
	 */
	public function import_row($data,$wetu_id,$id=0,$team_members=false,$importable_content=false,$safari_brands=false) {

        if(trim($data[0]['type'])=='Accommodation')
        {
	        $post_name = $data_post_content = $data_post_excerpt = '';
	        $post = array(
	          'post_type'		=> 'accommodation',
	        );

	        //Set the post_content
	        if(false !== $importable_content && in_array('description',$importable_content)){
		        if(!empty($data[0]['content']['extended_description']))
		        {
		            $data_post_content = $data[0]['content']['extended_description'];
		        }
	        	$post['post_content'] = wp_strip_all_tags($data_post_content);
	        }

	        //set the post_excerpt
	        if(false !== $importable_content && in_array('excerpt',$importable_content)){
		        if(!empty($data[0]['content']['teaser_description'])){
		        	$data_post_excerpt = $data[0]['content']['teaser_description'];
		        }elseif(!empty($data[0]['content']['general_description'])){
		            $data_post_excerpt = $data[0]['content']['general_description'];
		        }	   
		        $post['post_excerpt'] = $data_post_excerpt;     	
	        }

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

	        //Set the team member if it is there
	        if(post_type_exists('team') && false !== $team_members && '' !== $team_members){
	        	$this->set_team_member($id,$team_members);

	    	}

	        //Set the safari brand
	        if(false !== $safari_brands && '' !== $safari_brands){
	        	$this->set_safari_brands($id,$safari_brands);

	    	}	    	

	        //Import the main gallery
	        if(false !== $importable_content && in_array('gallery',$importable_content)){	    	
	    		$this->create_main_gallery($data,$id);
	        }

	        if(false !== $importable_content && in_array('location',$importable_content)){
	        	$this->set_map_data($data,$id);
	        	$this->set_location_taxonomy($data,$id);
	        }

	        if(post_type_exists('destination') && false !== $importable_content && in_array('destination',$importable_content)){
	        	$this->connect_destinations($data,$id);
	        }

	        if(false !== $importable_content && in_array('category',$importable_content)){
	        	$this->set_taxonomy_style($data,$id);
	        }

	        //Set the Room Data
	        if(false !== $importable_content && in_array('rooms',$importable_content)){
	        	$this->set_room_data($data,$id);
	    	}

	    	//Set the rating
	    	if(false !== $importable_content && in_array('rating',$importable_content)){
	       		$this->set_rating($data,$id);
	    	}

	    	//Set the checkin checkout data
	    	if(false !== $importable_content && in_array('checkin',$importable_content)){
	        	$this->set_checkin_checkout($data,$id);
	        }

	        //Import the videos
	        if(false !== $importable_content && in_array('videos',$importable_content)){
	        	$this->set_video_data($data,$id);
	        }
        }
        return $id;
	}

	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member($id,$team_members) {

		delete_post_meta($id, 'team_to_'.$this->tab_slug);
		foreach($team_members as $team){
        	add_post_meta($id,'team_to_'.$this->tab_slug,$team);			
		}
	}

	/**
	 * Set the safari brand
	 */
	public function set_safari_brands($id,$safari_brands) {
		foreach($safari_brands as $safari_brand){
        	wp_set_object_terms( $id, intval($safari_brand), 'accommodation-brand',true);			
		}
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
	 * Connects the destinations post type
	 */
	public function connect_destinations($data,$id) {
		if(isset($data[0]['position'])){
		    $destinations = false;
		    if(isset($data[0]['position']['country'])){
		    	$destinations['country'] = $data[0]['position']['country'];
		    }
		    if(isset($data[0]['position']['destination'])){
		    	$destinations['destination'] = $data[0]['position']['destination'];
		    }
		    
		    if(false !== $destinations){	
		    	$prev_values = get_post_meta($id,'destination_to_accommodation',false);
		    	if(false === $prev_values || !is_array($prev_values)){
		    		$prev_values = array();
		    	}
			    foreach($destinations as $key => $value){
				    $destination = get_page_by_title(ltrim(rtrim($value)), 'OBJECT', 'destination');
	                if (null !== $destination) {
	                	if(!in_array($destination->ID,$prev_values)){
	                   		add_post_meta($id,'destination_to_accommodation',$destination->ID,false);
	                   		add_post_meta($destination->ID,'accommodation_to_destination',$id,false);
	                	}
	                } 		    	
			    }	
			}
		}
	}	

	/**
	 * Set the Travel Style
	 */
	public function set_taxonomy_style($data,$id) {
		$terms = false;
		if(isset($data[0]['category'])){
			if(!$term = term_exists(trim($data[0]['category']), 'accommodation-type'))
	        {
	            $term = wp_insert_term(trim($data[0]['category']), 'accommodation-type');
	            if ( is_wp_error($term) ){echo $term->get_error_message();}
	            else { wp_set_object_terms( $id, intval($term['term_id']), 'accommodation-type',true); }
	        }
	        else
	        {
	            wp_set_object_terms( $id, intval($term['term_id']), 'accommodation-type',true);
	        }				
		}
	}		

	/**
	 * Saves the room data
	 */
	public function set_room_data($data,$id) {
		if(!empty($data[0]['rooms']) && is_array($data[0]['rooms'])){
			$rooms = false;

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

				if(!empty($room['images']) && is_array($room['images'])){
			    	$attachments_args = array(
			    			'post_parent' => $id,
			    			'post_status' => 'inherit',
			    			'post_type' => 'attachment',
			    			'order' => 'ASC',
			    	);   	
			    	$attachments = new WP_Query($attachments_args);
			    	$found_attachments = array();

			    	if($attachments->have_posts()){
			    		foreach($attachments->posts as $attachment){
			    			$found_attachments[] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
			    		}
			    	}

					foreach($room['images'] as $image_data){
			    		$temp_room['gallery'][] = $this->attach_image($image_data,$id,$found_attachments);
			    	}
				}
				$rooms[] = $temp_room;
			}

			if(false !== $id && '0' !== $id){
				delete_post_meta($id, 'units');				
			}
			foreach($rooms as $room){
		        add_post_meta($id,'units',$room,false);			
			}

			if(isset($data[0]['features']) && isset($data[0]['features']['rooms'])){
				$room_count = $data[0]['features']['rooms'];
			}else{
				$room_count = count($data[0]['rooms']);
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
	 * Set the ratings
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
	 * Set the Check in and Check out Date
	 */
	public function set_checkin_checkout($data,$id) {

		if(!empty($data[0]['features']) && isset($data[0]['features']['check_in_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_in_time']);
			$time = date('h:ia',strtotime($time));
			$this->save_custom_field($time,'checkin_time',$id);
		}
		if(!empty($data[0]['features']) && isset($data[0]['features']['check_out_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_out_time']);
			$time = date('h:ia',strtotime($time));
			$this->save_custom_field($time,'checkout_time',$id);
		}
	}	

	/**
	 * Set the Video date
	 */
	public function set_video_data($data,$id) {
		if(!empty($data[0]['content']['youtube_videos']) && is_array($data[0]['content']['youtube_videos'])){
			$videos = false;

			foreach($data[0]['content']['youtube_videos'] as $video){
				$temp_video = '';
				if(isset($video['label'])){
					$temp_video['title'] = $video['label'];
				}
				if(isset($video['description'])){
					$temp_video['description'] = strip_tags($video['description']);
				}	
				if(isset($video['url'])){
					$temp_video['url'] = $video['url'];
				}						
				$temp_video['thumbnail'] = '';
				$videos[] = $temp_video;
			}

			if(false !== $id && '0' !== $id){
				delete_post_meta($id, 'videos');				
			}
			foreach($videos as $video){
		        add_post_meta($id,'videos',$video,false);			
			}
		}
	}	

	/**
	 * Creates the main gallery data
	 */
	public function create_main_gallery($data,$id) {

		if(!empty($data[0]['content']['images']) && is_array($data[0]['content']['images'])){

			//Finds any previous attachments with the same name and skips over them.
	    	$attachments_args = array(
	    			'post_parent' => $id,
	    			'post_status' => 'inherit',
	    			'post_type' => 'attachment',
	    			'order' => 'ASC',
	    	);   	
	    	 
	    	$attachments = new WP_Query($attachments_args);
	    	$found_attachments = array();
	    	$gallery_meta = array();

	    	if($attachments->have_posts()){
	    		foreach($attachments->posts as $attachment){
	    			$found_attachments[] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
	    			$gallery_meta[] = $attachment->ID;
	    		}
	    	}

	    	$counter = 0;
	    	foreach($data[0]['content']['images'] as $image_data){
	    		//if($counter > 8){continue;}
	    		$gallery_meta[] = $this->attach_image($image_data,$id,$found_attachments);
	    		$counter++;
	    	}

	    	if(!empty($gallery_meta)){
	    		delete_post_meta($id,'gallery');
	    		foreach($gallery_meta as $gallery_id){
	    			if(false !== $gallery_id && '' !== $gallery_id && !is_array($gallery_id)){
	    				add_post_meta($id,'gallery',$gallery_id,false);
	    			}
	    		}
	    	}
    	}
	}

	/**
	 * Attaches 1 image
	 */
	public function attach_image($v=false,$parent_id,$found_attachments = array()){
		if(false !== $v){
	   		$temp_fragment = explode('/',$v['url_fragment']);
	    	$url_filename = $temp_fragment[count($temp_fragment)-1];
	    	$url_filename = str_replace(array('.jpg','.png','.jpeg'),'',$url_filename);
	
	    	if(in_array($url_filename,$found_attachments)){
	    		return false;
	    	}
	    	               
	        $postdata=array();
	        if(empty($v['label']))
	        {
	            $v['label']='';
	        }
	        if(!empty($v['description']))
	        {
	            $desc=wp_strip_all_tags($v['description']);
	            $posdata=array('post_excerpt'=>$desc);
	        }
	        if(!empty($v['section']))
	        {
	            $desc=wp_strip_all_tags($v['section']);
	            $posdata=array('post_excerpt'=>$desc);
	        }

	        $attachID=NULL;  
	        //Resizor - add option to setting if required
	        $fragment = str_replace(' ','%20',$v['url_fragment']);
	        $url = $this->image_scaling_url.$fragment;
	
	        $attachID = $this->attach_external_image2($url,$parent_id,'',$v['label'],$postdata);

	        //echo($attachID.' add image');
	        if($attachID!=NULL)
	        {
	            return $attachID;
	        }
        }	
        return 	false;
	}
	public function attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
	
		if ( !$url || !$post_id ) { return new WP_Error('missing', "Need a valid URL and post ID..."); }

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		// Download file to temp location, returns full server path to temp file
		//$tmp = download_url( $url );

		//var_dump($tmp);
		$tmp = tempnam("/tmp", "FOO");

		$image = file_get_contents($url);
		file_put_contents($tmp, $image);
		chmod($tmp,'777');

		preg_match('/[^\?]+\.(tif|TIFF|jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|pdf|PDF|bmp|BMP)/', $url, $matches);    // fix file filename for query strings
		$url_filename = basename($matches[0]);
		$url_filename=str_replace('%20','_',$url_filename);
		// extract filename from url for title
		$url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)
		 
		// override filename if given, reconstruct server path
		if ( !empty( $filename ) && " " != $filename )
		{
			$filename = sanitize_file_name($filename);
			$tmppath = pathinfo( $tmp );      

			$extension = '';  
			if(isset($tmppath['extension'])){
				$extension = $tmppath['extension'];
			}   

			$new = $tmppath['dirname'] . "/". $filename . "." . $extension;
			rename($tmp, $new);                                                                 // renames temp file on server
			$tmp = $new;                                                                        // push new filename (in path) to be used in file array later
		}

		// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
		$file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

		if ( !empty( $filename) && " " != $filename )
		{
			$file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
		}
		else
		{
			$file_array['name'] = $url_filename;                                                // just use original URL filename
		}

		// set additional wp_posts columns
		if ( empty( $post_data['post_title'] ) )
		{

			$url_filename=str_replace('%20',' ',$url_filename);

			$post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
		}

		// make sure gets tied to parent
		if ( empty( $post_data['post_parent'] ) )
		{
			$post_data['post_parent'] = $post_id;
		}

		// required libraries for media_handle_sideload

		// do the validation and storage stuff
		$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status
		 
		// If error storing permanently, unlink
		if ( is_wp_error($att_id) )
		{
			unlink($file_array['tmp_name']);   // clean up
			return false; // output wp_error
			//return $att_id; // output wp_error
		}

		return $att_id;
	}			
}
$lsx_tour_importer_accommodation = new Lsx_Tour_Importer_Accommodation();