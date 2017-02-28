<?php
/**
 * @package   WETU_Importer_Destination
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Destination extends WETU_Importer_Admin {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'destination';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url = false;

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
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables()
	{
		parent::set_variables();

		if(false !== $this->api_key){
		    $this->url = 'https://wetu.com/API/Pins/'.$this->api_key;
        }
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

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
								<li><input class="content" type="checkbox" name="content[]" value="location" /> <?php _e('Location','wetu-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="special_interests" /> <?php _e('Special Interests','wetu-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="spoken_languages" /> <?php _e('Spoken Languages','wetu-importer'); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="videos" /> <?php _e('Videos','wetu-importer'); ?></li>
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
	public function search_form() {
	?>
        <form class="ajax-form" id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php" data-type="<?php echo $this->tab_slug; ?>">
        	<input type="hidden" name="page" value="<?php echo $this->tab_slug; ?>" />

        	<h3><span class="dashicons dashicons-search"></span> <?php _e('Search','wetu-importer'); ?></h3>
            <div class="normal-search">
                <input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php _e('Search','wetu-importer'); ?>" />
            </div>

            
            <div class="advanced-search hidden" style="display:none;">
                <p><?php _e('Enter several keywords, each on a new line.','wetu-importer'); ?></p>
                <textarea rows="10" cols="40" name="bulk-keywords"></textarea>
                <input class="button button-primary submit" type="submit" value="<?php _e('Search','wetu-importer'); ?>" />
            </div>

            <p>
                <a class="advanced-search-toggle" href="#"><?php _e('Bulk Search','wetu-importer'); ?></a> |
                <a class="published search-toggle" href="#publish"><?php esc_attr_e('Published','wetu-importer'); ?></a> |
                <a class="pending search-toggle"  href="#pending"><?php esc_attr_e('Pending','wetu-importer'); ?></a> |
                <a class="draft search-toggle"  href="#draft"><?php esc_attr_e('Draft','wetu-importer'); ?></a>
            </p>

            <div class="ajax-loader" style="display:none;width:100%;text-align:center;">
            	<img style="width:64px;" src="<?php echo WETU_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>

            <div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
            	<img style="width:32px;" src="<?php echo WETU_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>
        </form>
	<?php
	}



	/**
	 * Grab all the current destination posts via the lsx_wetu_id field.
	 */
	public function find_current_destination($post_type='destination') {
		global $wpdb;
		$return = array();

		$current_destination = $wpdb->get_results("
					SELECT key1.post_id,key1.meta_value
					FROM {$wpdb->postmeta} key1

					INNER JOIN  {$wpdb->posts} key2 
    				ON key1.post_id = key2.ID
					
					WHERE key1.meta_key = 'lsx_wetu_id'
					AND key2.post_type = '{$post_type}'
		");
		if(null !== $current_destination && !empty($current_destination)){
			foreach($current_destination as $accom){
				$return[$accom->meta_value] = $accom;
			}
		}
		return $return;
	}	

	/**
	 * Run through the destination grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === 'destination'){
			$destination = get_option('lsx_tour_operator_destination',false);
			if ( false !== $destination && isset($_POST['keyword'] )) {
				$searched_items = false;
				$keyphrases = $_POST['keyword'];
				$my_destination = false;

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

				$destination = json_decode($destination);
				if (!empty($destination)) {
					$current_destination = $this->find_current_destination();

					foreach($destination as $row_key => $row){


						//If we are searching for
						if(false !== $post_status){

							if('import' === $post_status){

								if(0 !== $row['post_id']){
									continue;
								}else{
									$searched_items[sanitize_title($row->name).'-'.$row->id] = $this->format_row($row);
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
								$searched_items[sanitize_title($row->name).'-'.$row->id] = $this->format_row($row);

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
									$searched_items[sanitize_title($row->name).'-'.$row->id] = $this->format_row($row);
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
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === 'destination' && isset($_POST['wetu_id'])){
			
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

            $jdata=file_get_contents($this->url."/Get?ids=".$wetu_id);
            if($jdata)
            {
                $adata=json_decode($jdata,true);
                if(!empty($adata))
                {
                	$return = $this->import_row($adata,$wetu_id,$post_id,$team_members,$content,$safari_brands);
                	$this->format_completed_row($return);
                }
            }

			die();
		}

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

        if(trim($data[0]['type'])=='Destination')
        {
	        $post_name = $data_post_content = $data_post_excerpt = '';
	        $post = array(
	          'post_type'		=> 'destination',
	        );

	        $content_used_general_description = false;

	        //Set the post_content
	        if(false !== $importable_content && in_array('description',$importable_content)){
		        if(isset($data[0]['content']['extended_description']))
		        {
		            $data_post_content = $data[0]['content']['extended_description'];
		        }elseif(isset($data[0]['content']['general_description'])){
		            $data_post_content = $data[0]['content']['general_description'];
		            $content_used_general_description = true;
		        }elseif(isset($data[0]['content']['teaser_description'])){
		        	$data_post_content = $data[0]['content']['teaser_description'];
		        }
	        	$post['post_content'] = wp_strip_all_tags($data_post_content);
	        }

	        //set the post_excerpt
	        if(false !== $importable_content && in_array('excerpt',$importable_content)){
		        if(isset($data[0]['content']['teaser_description'])){
		        	$data_post_excerpt = $data[0]['content']['teaser_description'];
		        }elseif(isset($data[0]['content']['general_description']) && false === $content_used_general_description){
		            $data_post_excerpt = $data[0]['content']['general_description'];
		        }	   
		        $post['post_excerpt'] = $data_post_excerpt;     	
	        }

	        if(false !== $id && '0' !== $id){
	        	$post['ID'] = $id;
				if(isset($data[0]['name'])){
					$post['post_title'] = $data[0]['name'];
					$post['post_name'] = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'destination', 0);
				}
	        	$id = wp_update_post($post);
	        	$prev_date = get_post_meta($id,'lsx_wetu_modified_date',true);
	        	update_post_meta($id,'lsx_wetu_modified_date',strtotime($data[0]['last_modified']),$prev_date);
	        }else{

		        //Set the name
		        if(isset($data[0]['name'])){
		            $post_name = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'destination', 0);
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

	        if(false !== $importable_content && in_array('location',$importable_content)){
	        	$this->set_map_data($data,$id);
	        }

	        //Set the Room Data
	        if(false !== $importable_content && in_array('rooms',$importable_content)){
	        	$this->set_room_data($data,$id);
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
	 * Saves the room data
	 */
	public function set_room_data($data,$id) {

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
}
$wetu_importer_destination = new WETU_Importer_Destination();