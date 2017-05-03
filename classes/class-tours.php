<?php
/**
 * @package   WETU_Importer_Tours
 * @author    LightSpeed
 * @license   GPL-3+
 * @link      
 * @copyright 2017 LightSpeed
 **/

class WETU_Importer_Tours extends WETU_Importer_Accommodation {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'tour';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url = false;

	/**
	 * The query string url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url_qs = false;

	/**
	 * Holds a list of any current accommodation
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $current_accommodation = false;

	/**
	 * Holds a list of any current destinations
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $current_destinations = false;

	/**
	 * Options
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
	public function __construct() {
		$this->set_variables();

		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );
		add_action('wp_ajax_lsx_tour_importer',array($this,'process_ajax_search'));	
		add_action('wp_ajax_nopriv_lsx_tour_importer',array($this,'process_ajax_search'));		

		add_action('wp_ajax_lsx_import_items',array($this,'process_ajax_import'));	
		add_action('wp_ajax_nopriv_lsx_import_items',array($this,'process_ajax_import'));

		$temp_options = get_option('_lsx-to_settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
		}				
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables()
	{
		parent::set_variables();

		if ( false !== $this->api_username && false !== $this->api_password ) {
			$this->url    = 'https://wetu.com/API/Itinerary/';
			$this->url_qs = 'username=' . $this->api_username . '&password=' . $this->api_password;
		} elseif ( false !== $this->api_key ) {
			$this->url    = 'https://wetu.com/API/Itinerary/' . $this->api_key;
			$this->url_qs = '';
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
				
				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','wetu-importer'); ?>" />
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','wetu-importer'); ?>" />
				</p>				

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>
				
					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php _e('Enter a title to search for and press enter','wetu-importer'); ?></label>
							</th>
							<td class="post-title page-title column-title">
								<strong>
									<?php _e('Enter a title to search for','wetu-importer'); ?>
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

				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','wetu-importer'); ?>" />
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','wetu-importer'); ?>" />
				</p>
			</form> 

			<div style="display:none;" class="import-list-wrapper">
				<br />        
				<form method="get" action="" id="import-list">

					<div class="row">
						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('What content to Sync from WETU'); ?></h3>
							<ul>
								<li><input class="content" type="checkbox" name="content[]" value="description" /> <?php _e('Description','wetu-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="excerpt" /> <?php _e('Excerpt','wetu-importer'); ?></li>

                                <li><input class="content" type="checkbox" name="content[]" value="price" /> <?php _e('Price','wetu-importer'); ?></li>
                                <li><input class="content" type="checkbox" name="content[]" value="duration" /> <?php _e('Duration','wetu-importer'); ?></li>

								<li><input class="content" type="checkbox" name="content[]" value="category" /> <?php _e('Category','wetu-importer'); ?></li>

                                <li><input class="content" type="checkbox" name="content[]" value="itineraries" /> <?php _e('Itinerary Days','wetu-importer'); ?></li>

								<?php if(class_exists('TO_Maps')){ ?>
                                    <li><input class="content" type="checkbox" name="content[]" value="map" /> <?php _e('Map Coordinates (generates a KML file)','wetu-importer'); ?></li>
								<?php } ?>
							</ul>
						</div>
                        <div style="width:30%;display:block;float:left;">
                            <h3><?php _e('Itinerary Info'); ?></h3>
                            <ul>
                                <li><input class="content" type="checkbox" name="content[]" value="itinerary_description" /> <?php _e('Description','wetu-importer'); ?></li>
                                <li><input class="content" type="checkbox" name="content[]" value="itinerary_gallery" /> <?php _e('Gallery','wetu-importer'); ?></li>
                            </ul>

                            <h4><?php _e('Additional Content'); ?></h4>
                            <ul>
                                <li><input class="content" type="checkbox" name="content[]" value="accommodation" /> <?php _e('Sync Accommodation','wetu-importer'); ?></li>
                                <li><input class="content" type="checkbox" name="content[]" value="destination" /> <?php _e('Sync Destinations','wetu-importer'); ?></li>
                            </ul>
                        </div>
                        <?php if(class_exists('TO_Team')){ ?>
                            <div style="width:30%;display:block;float:left;">
                                <h3><?php _e('Assign a Team Member'); ?></h3>
                                <?php $this->team_member_checkboxes(); ?>
                            </div>
                        <?php } ?>

						<br clear="both" />			
					</div>


					<h3><?php _e('Your List'); ?></h3>
                    <p><input class="button button-primary" type="submit" value="<?php _e('Sync','wetu-importer'); ?>" /></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php _e('Sync','wetu-importer'); ?>" /></p>
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
	public function update_options_form() {
		$tours = get_transient('lsx_ti_tours');
		echo '<div class="wetu-status"><h3>'.__('Wetu Status','wetu-importer').' - ';
		if('' === $tours || false === $tours || isset($_GET['refresh_tours'])){
			$result = $this->update_options();

			if(true === $result){
			    echo '<span style="color:green;">'.esc_attr('Connected','wetu-importer').'</span>';
            }else{
			    echo '<span style="color:red;">'.wp_kses_post($result).'</span>';
            }
		}else{
			echo '<span style="color:green;">'.esc_attr('Connected','wetu-importer').'</span>';
        }
		echo '</h3></div>';
	}

	/**
	 * Save the list of Tours into an option
	 */
	public function update_options() {
		$data = file_get_contents( $this->url . '/V7/List?' . $this->url_qs . '&own=true&type=All' );
		$tours = json_decode($data, true);

		if(isset($tours['error'])){
		    return $tours['error'];
        }elseif (isset($tours['itineraries']) && !empty($tours['itineraries'])) {
			set_transient('lsx_ti_tours',$tours['itineraries'],60*60*2);
			return true;
		}
	}

	/**
	 * Grab all the current tour posts via the lsx_wetu_id field.
	 */
	public function find_current_tours() {
		global $wpdb;
		$return = array();

		$current_tours = $wpdb->get_results("
					SELECT key1.post_id,key1.meta_value
					FROM {$wpdb->postmeta} key1

					INNER JOIN  {$wpdb->posts} key2 
    				ON key1.post_id = key2.ID
					
					WHERE key1.meta_key = 'lsx_wetu_id'
					AND key2.post_type = 'tour'

					LIMIT 0,500
		");
		if(null !== $current_tours && !empty($current_tours)){
			foreach($current_tours as $tour){
				$return[$tour->meta_value] = $tour;
			}
		}
		return $return;
	}	

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;

		if(isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === $this->tab_slug){
			$tours = get_transient('lsx_ti_tours');
			if ( false !== $tours) {

				$searched_items = false;

				if(isset($_POST['keyword'] )) {
					$keyphrases = $_POST['keyword'];
				}else{
					$keyphrases = array(0);
                }

				if(!is_array($keyphrases)){
					$keyphrases = array($keyphrases);
				}
				foreach($keyphrases as &$keyword){
					$keyword = ltrim(rtrim($keyword));
				}

				$post_status = false;
				if(in_array('publish',$keyphrases)){
					$post_status = 'publish';
				}
				if(in_array('pending',$keyphrases)){
					$post_status = 'pending';
				}
				if(in_array('draft',$keyphrases)){
					$post_status = 'draft';
				}
				if(in_array('import',$keyphrases)){
					$post_status = 'import';
				}


				if (!empty($tours)) {
					$current_tours = $this->find_current_tours();

					foreach($tours as $row_key => $row){

					    if(isset($row['is_disabled']) && true === $row['is_disabled']){
                            continue;
                        }

                        /*if('Sample' === $row['type']){
                            continue;
                        }*/

                        //If this is a current tour, add its ID to the row.
						$row['post_id'] = 0;
						if(false !== $current_tours && array_key_exists($row['identifier'], $current_tours)){
							$row['post_id'] = $current_tours[$row['identifier']]->post_id;
						}

						//If we are searching for
						if(false !== $post_status){

                            if('import' === $post_status){

								if(0 !== $row['post_id']){
								    continue;
								}else{
									$searched_items[sanitize_title($row['name']).'-'.$row['identifier']] = $this->format_row($row);
                                }


                            }else{

								if(0 === $row['post_id']){
									continue;
								}else{
									$current_status = get_post_status($row['post_id']);
									if($current_status !== $post_status){
									    continue;
                                    }

								}
								$searched_items[sanitize_title($row['name']).'-'.$row['identifier']] = $this->format_row($row);

                            }

                        }else{
							//Search through each keyword.
							foreach($keyphrases as $keyphrase){

								//Make sure the keyphrase is turned into an array
								$keywords = explode(" ",$keyphrase);
								if(!is_array($keywords)){
									$keywords = array($keywords);
								}

								if($this->multineedle_stripos(ltrim(rtrim($row['name'])), $keywords) !== false){
									$searched_items[sanitize_title($row['name']).'-'.$row['identifier']] = $this->format_row($row);
								}
							}
                        }
					}		
				}

				if(false !== $searched_items){
					ksort($searched_items);
					$return = implode($searched_items);
				}
			}
			print_r($return);
			die();
		}
	}

	/**
	 * Formats the row for output on the screen.
	 */	
	public function format_row($row = false){
		if(false !== $row){

			$status = 'import';
			if(0 !== $row['post_id']){
				$status = '<a href="'.admin_url('/post.php?post='.$row['post_id'].'&action=edit').'" target="_blank">'.get_post_status($row['post_id']).'</a>';
			}

			$row_html = '
			<tr class="post-'.$row['post_id'].' type-tour" id="post-'.$row['post_id'].'">
				<th class="check-column" scope="row">
					<label for="cb-select-'.$row['identifier'].'" class="screen-reader-text">'.$row['name'].'</label>
					<input type="checkbox" data-identifier="'.$row['identifier'].'" value="'.$row['post_id'].'" name="post[]" id="cb-select-'.$row['identifier'].'">
				</th>
				<td class="post-title page-title column-title">
					<strong>'.$row['name'].'</strong> - '.$status.'
				</td>
				<td class="date column-date">
					<abbr title="'.date('Y/m/d',strtotime($row['last_modified'])).'">'.date('Y/m/d',strtotime($row['last_modified'])).'</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					'.$row['identifier'].'
				</td>
			</tr>';		
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import($force = false) {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === $this->tab_slug && isset($_POST['wetu_id'])){
			
			$wetu_id = $_POST['wetu_id'];
			if(isset($_POST['post_id'])){
				$post_id = $_POST['post_id'];	
			}else{
				$post_id = 0;
			}

			if(isset($_POST['content']) && is_array($_POST['content']) && !empty($_POST['content'])){
				$content = $_POST['content'];	
			}else{
				$content = false;
			}

            $jdata=file_get_contents("http://wetu.com/API/Itinerary/V7/Get?id=".$wetu_id);

            if($jdata)
            {
				$jdata=json_decode($jdata,true);
                if(!empty($jdata))
                {
                	$return = $this->import_row($jdata,$wetu_id,$post_id,$content);
                	$this->format_completed_row($return);
                }
            }
			die();
		}

	}

	/**
	 * Connect to wetu
     *
     * @param $data array
     * @param $wetu_id string
	 */
	public function import_row($data,$wetu_id,$id=0,$importable_content=false,$old1=false,$old2=false) {
        $post_name = $data_post_content = $data_post_excerpt = '';
        $post = array(
          'post_type'		=> 'tour',
        );

        //Set the post_content
		$content_used_general_description = false;
        if(false !== $importable_content && in_array('description',$importable_content)){
            $data_post_content = '';

            if(isset($data['description'])){
                $data_post_content = $data['description'];
            }elseif(isset($data['summary'])){
                $data_post_content = $data['summary'];
                $content_used_general_description = true;
            }
            $post['post_content'] = wp_strip_all_tags($data_post_content);
        }

        //set the post_excerpt
        if(false !== $importable_content && in_array('excerpt',$importable_content)){
            if(isset($data['summary']) && false === $content_used_general_description){
                $post['post_excerpt'] = $data['summary'];
            }
        }

        //Create or update the post
        if(false !== $id && '0' !== $id){
            $post['ID'] = $id;
	        $post['post_status'] = 'publish';
            $id = wp_update_post($post);
            $prev_date = get_post_meta($id,'lsx_wetu_modified_date',true);
            update_post_meta($id,'lsx_wetu_modified_date',strtotime($data['last_modified']),$prev_date);
        }else{

            //Set the name
            if(isset($data['name'])){
                $post_name = wp_unique_post_slug(sanitize_title($data['name']),$id, 'draft', 'tour', 0);
            }
            $post['post_name'] = $post_name;
            $post['post_title'] = $data['name'];
            $post['post_status'] = 'publish';
            $id = wp_insert_post($post);

            //Save the WETU ID and the Last date it was modified.
            if(false !== $id){
                add_post_meta($id,'lsx_wetu_id',$wetu_id);
                add_post_meta($id,'lsx_wetu_modified_date',strtotime($data['last_modified']));
            }
        }


		//Set the price
		if(false !== $importable_content && in_array('price',$importable_content)){
			$this->set_price($data,$id);
		}

		//Set the Duration
		if(false !== $importable_content && in_array('duration',$importable_content)){
			$this->set_duration($data,$id);
		}

        if(in_array('itineraries',$importable_content) && isset($data['legs']) && !empty($data['legs'])){
            $this->process_itineraries($data,$id,$importable_content);
        }

		if(in_array('map',$importable_content) && isset($data['routes']) && !empty($data['routes'])){
			$this->process_map_points($data,$id);
		}

		//TODO Test These
		//Setup some default for use in the import
		if(false !== $importable_content && (in_array('itinerary_gallery',$importable_content) || in_array('gallery',$importable_content) || in_array('banner_image',$importable_content) || in_array('featured_image',$importable_content))){
			$this->find_attachments($id);
		}
        //Set the featured image
        //TODO Test These
        if(false !== $importable_content && in_array('featured_image',$importable_content)){
            $this->set_featured_image($data,$id);
        }

		//TODO Test These
        if(false !== $importable_content && in_array('banner_image',$importable_content)){
            $this->set_banner_image($data,$id);
        }

		//TODO Test These
        //Import the main gallery
        if(false !== $importable_content && in_array('gallery',$importable_content)){
            $this->create_main_gallery($data,$id);
        }

        return $id;
	}

	/**
	 * A loop which runs through each leg on the tour.
	 */
	public function process_itineraries($data,$id,$importable_content) {
		$day_counter = 1;
		$leg_counter = 0;

		delete_post_meta($id,'itinerary');

		if(false !== $importable_content && in_array('accommodation',$importable_content)){
			delete_post_meta($id,'accommodation_to_tour');
		}
		if(false !== $importable_content && in_array('destination',$importable_content)){
			delete_post_meta($id,'destination_to_tour');
			delete_post_meta($id,'departs_from');
			delete_post_meta($id,'ends_in');
		}

		$departs_from = false;
		$ends_in = false;

		foreach($data['legs'] as $leg){

			if(isset($leg['days']) && !empty($leg['days'])){

				//Itinerary Accommodation
				$current_accommodation = false;
				if(false !== $importable_content && in_array('accommodation',$importable_content)){
					$current_accommodation = $this->set_accommodation($leg,$id);
				}

				//Itinerary Destination
				$current_destination = false;
				if(false !== $importable_content && in_array('destination',$importable_content)){
					$current_destination = $this->set_destination($leg,$id);
				}

				//If the Nights are the same mount of days in the array,  then it isnt "By Destination"
				if($leg['nights'] === count($leg['days']) || 0 === $leg['itinerary_leg_id']){

					foreach($leg['days'] as $day){

						$current_day = array();

						$current_day['title'] =  esc_attr('Day ','wetu-importer').$day_counter;

						//Description
						if(false !== $importable_content && in_array('itinerary_description',$importable_content) && isset($day['notes']) && '' !== $day['notes']){
							$current_day['description'] = strip_tags($day['notes']);
						}else{
							$current_day['description'] = '';
						}

						//Itinerary Gallery
						if(false !== $importable_content && in_array('itinerary_gallery',$importable_content) && isset($day['images'])){
							$current_day['featured_image'] = '';
						}else{
							$current_day['featured_image'] = '';
						}

						//Accommodation
						if(false !== $current_accommodation){
							$current_day['accommodation_to_tour'] = array($current_accommodation);
						}else{
							$current_day['accommodation_to_tour'] = array();
						}

						//Destination
						if(false !== $current_destination){
							$current_day['destination_to_tour'] = array($current_destination);
						}else{
							$current_day['destination_to_tour'] = array();
						}

						$this->set_itinerary_day($current_day,$id);
						$day_counter++;
					}

				}else{
					$day_counter = $day_counter + (int)$leg['nights'];
				}

			}

			//If we are in the first leg,  and the destination was attached then save it as the departure field.
			if( 0 === $leg_counter && false !== $current_destination){
				$departs_from = $current_destination;
			}

			//If its the last leg then save it as the ends in.
			if( $leg_counter === (count($data['legs'])-2) && false !== $current_destination){
				$ends_in = $current_destination;
			}
			$leg_counter++;
		}

		if(false !== $departs_from){
			add_post_meta($id,'departs_from',$departs_from,true);
		}
		if(false !== $ends_in){
			add_post_meta($id,'ends_in',$ends_in,true);
		}
	}

	/**
	 * Run through your routes and save the points as a KML file.
	 */
	public function process_map_points($data,$id) {

	    if(!empty($data['routes'])){

	        delete_post_meta($id,'wetu_map_points');

	        $points = array();

	        foreach($data['routes'] as $route){


	            if(isset($route['points']) && '' !== $route['points']){

	                $temp_points = explode(';',$route['points']);
	                $point_counter = count($temp_points);

					for ($x = 0; $x <= $point_counter; $x++) {
					    $y = $x+1;
						$points[] = $temp_points[$x].','.$temp_points[$y];
						$x++;
					}
				}
            }
            if(!empty($points)){
				$this->save_custom_field(implode(' ',$points),'wetu_map_points',$id,false,true);
            }
        }

	}

	/**
	 * Set the Itinerary Day
	 */
	public function set_itinerary_day($day,$id) {
        $this->save_custom_field($day,'itinerary',$id,false,false);
	}

	/**
	 * Set the price
	 */
	public function set_price($data,$id) {
		if(isset($data['price']) && ''!== $data['price']){
            $price = preg_replace("/[^0-9,.]/", "", $data['price']);
            $this->save_custom_field($price,'price',$id);
		}
	}

	/**
	 * Set the duration
	 */
	public function set_duration($data,$id) {
		if(isset($data['days']) && !empty($data['days'])){
			$price = $data['days'];
			$price = preg_replace("/[^0-9,.]/", "", $price);
			$this->save_custom_field($price,'duration',$id);
		}
	}


	/**
	 * Connects the Accommodation if its available
	 */
	public function set_accommodation($day,$id) {

	    $ac_id = false;
		$this->current_accommodation = $this->find_current_accommodation();
		
		if(isset($day['content_entity_id']) && !empty($day['content_entity_id'])){

			if(false !== $this->current_accommodation && !empty($this->current_accommodation) && array_key_exists($day['content_entity_id'],$this->current_accommodation)){
                $ac_id = $this->current_accommodation[$day['content_entity_id']];
			}else{
				$ac_id = wp_insert_post(array(
                    'post_type' => 'accommodation',
                    'post_status' => 'draft',
                    'post_title' => $day['content_entity_id']
                ));
				$this->save_custom_field($day['content_entity_id'],'lsx_wetu_id',$ac_id);
			}

			if('' !== $ac_id && false !== $ac_id){
			    $this->save_custom_field($ac_id,'accommodation_to_tour',$id,false,false);
				$this->save_custom_field($id,'tour_to_accommodation',$ac_id,false,false);
            }
		}
		return $ac_id;
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
     *
     * @param $post_type string
     * @return boolean / array
	 */
	public function find_current_accommodation($post_type='accommodation') {
		global $wpdb;
		$accommodation = parent::find_current_accommodation($post_type);

		$return = false;
		if(!empty($accommodation)){
		    foreach($accommodation as $key => $acc){
				$return[$acc->meta_value] = $acc->post_id;
            }
        }
		return $return;
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
     * @return boolean / array
	 */
	public function find_current_destinations() {
		return $this->find_current_accommodation('destination');
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $day array
	 * @param $id string
	 * @return boolean / string
	 */
	public function set_destination($day,$id) {
		$dest_id = false;
		$country_id = false;
		$this->current_destinations = $this->find_current_destinations();

		if(isset($day['destination_content_entity_id']) && !empty($day['destination_content_entity_id'])){

			if(false !== $this->current_destinations && !empty($this->current_destinations) && array_key_exists($day['destination_content_entity_id'],$this->current_destinations)){
				$dest_id = $this->current_destinations[$day['destination_content_entity_id']];

				$potential_id = wp_get_post_parent_id($dest_id);
				$country_wetu_id = get_post_meta($potential_id,'lsx_wetu_id',true);
				if(false !== $country_wetu_id){
					$this->set_country($country_wetu_id, $id);
                }

			}else {

				$destination_json = file_get_contents("http://wetu.com/API/Pins/".$this->api_key."/Get?ids=" . $day['destination_content_entity_id']);

				if ($destination_json) {
					$destination_data = json_decode($destination_json, true);

					if (!empty($destination_data) && !isset($destination_data['error'])) {

					    $destination_title = $day['destination_content_entity_id'];

					    if(isset($destination_data[0]['name'])){
							$destination_title = $destination_data[0]['name'];
                        }

					    if(isset($destination_data[0]['map_object_id']) && isset($destination_data[0]['position']['country_content_entity_id'])
                            && $destination_data[0]['map_object_id'] !== $destination_data[0]['position']['country_content_entity_id']){

							$country_id = $this->set_country($destination_data[0]['position']['country_content_entity_id'], $id);
                        }

                        $dest_post = array(
							'post_type' => 'destination',
							'post_status' => 'draft',
							'post_title' => $destination_title
						);

					    if(false !== $country_id){
							$dest_post['post_parent'] = $country_id;
                        }
						$dest_id = wp_insert_post($dest_post);

						//Make sure we register the
						$this->current_destinations[$day['destination_content_entity_id']] = $dest_id;

						$this->save_custom_field($day['destination_content_entity_id'], 'lsx_wetu_id', $dest_id);
					}
				}
			}

			if ('' !== $dest_id && false !== $dest_id) {
				$this->save_custom_field($dest_id, 'destination_to_tour', $id, false, false);
				$this->save_custom_field($id, 'tour_to_destination', $dest_id, false, false);
			}
		}
		return $dest_id;
	}
	/**
	 * Connects the destinations post type
	 *
	 * @param $dest_id string
     * @param $country_id array
	 * @param $id string
	 */
	public function set_country($country_wetu_id, $id) {
	    $country_id = false;
		$this->current_destinations = $this->find_current_destinations();

        if (false !== $this->current_destinations && !empty($this->current_destinations) && array_key_exists($country_wetu_id, $this->current_destinations)) {
            $country_id = $this->current_destinations[$country_wetu_id];
        } else {

            $country_json = file_get_contents("http://wetu.com/API/Pins/".$this->api_key."/Get?ids=" . $country_wetu_id);

            if ($country_json) {
                $country_data = json_decode($country_json, true);

                if (!empty($country_data) && !isset($country_data['error'])) {

					//Format the title of the destination if its available,  otherwise default to the WETU ID.
                    $country_title = $country_wetu_id;
                    if (isset($country_data[0]['name'])) {
						$country_title = $country_data[0]['name'];
                    }

					$country_id = wp_insert_post(array(
                        'post_type' => 'destination',
                        'post_status' => 'draft',
                        'post_title' => $country_title
                    ));
					//add the country to the current destination stack
					$this->current_destinations[$country_wetu_id] = $country_id;

					//Save the wetu field
                    $this->save_custom_field($country_wetu_id, 'lsx_wetu_id', $country_id);
                }
            }
        }

        if ('' !== $country_id && false !== $country_id) {
            $this->save_custom_field($country_id, 'destination_to_tour', $id, false, false);
            $this->save_custom_field($id, 'tour_to_destination', $country_id, false, false);

            return $country_id;
        }
	}
}
$wetu_importer_tours = new WETU_Importer_Tours();