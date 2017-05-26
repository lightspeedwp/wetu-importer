<?php
/**
 * @package   WETU_Importer_Destination
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Destination extends WETU_Importer
{

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
	 * The query string url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url_qs = false;

	/**
	 * Options
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;

	/**
	 * The fields you wish to import
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $destination_options = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct()
	{
		$this->set_variables();
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables()
	{
		// ** This request only works with API KEY **
		//if ( false !== $this->api_username && false !== $this->api_password ) {
		//	$this->url    = 'https://wetu.com/API/Pins/';
		//	$this->url_qs = 'username=' . $this->api_username . '&password=' . $this->api_password;
		//} elseif ( false !== $this->api_key ) {
		$this->url = 'https://wetu.com/API/Pins/' . $this->api_key;
		$this->url_qs = '';
		//}

		$temp_options = get_option('_lsx-to_settings', false);
		if (false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])) {
			$this->options = $temp_options[$this->plugin_slug];
		}

		$destination_options = get_option('wetu_importer_destination_settings', false);
		if (false !== $destination_options) {
			$this->destination_options = $destination_options;
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page()
	{
		?>
        <div class="wrap">
			<?php $this->navigation('destination'); ?>

			<?php $this->search_form(); ?>

            <form method="get" action="" id="posts-filter">
                <input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>"/>

                <p><input class="button button-primary add" type="button"
                          value="<?php _e('Add to List', 'wetu-importer'); ?>"/>
                    <input class="button button-primary clear" type="button"
                           value="<?php _e('Clear', 'wetu-importer'); ?>"/>
                </p>

                <table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

                    <tbody id="the-list">
                    <tr class="post-0 type-tour status-none" id="post-0">
                        <th class="check-column" scope="row">
                            <label for="cb-select-0"
                                   class="screen-reader-text"><?php _e('Enter a title to search for and press enter', 'wetu-importer'); ?></label>
                        </th>
                        <td class="post-title page-title column-title">
                            <strong>
								<?php _e('Enter a title to search for', 'wetu-importer'); ?>
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

                <p><input class="button button-primary add" type="button"
                          value="<?php _e('Add to List', 'wetu-importer'); ?>"/>
                    <input class="button button-primary clear" type="button"
                           value="<?php _e('Clear', 'wetu-importer'); ?>"/>
                </p>
            </form>

            <div style="display:none;" class="import-list-wrapper">
                <br/>
                <form method="get" action="" id="import-list">

                    <div class="row">
                        <div class="settings-all" style="width:30%;display:block;float:left;">
                            <h3><?php _e('What content to Sync from WETU'); ?></h3>
                            <ul>
                                <li>
                                    <input class="content select-all" <?php $this->checked($this->destination_options, 'all'); ?>
                                           type="checkbox" name="content[]"
                                           value="all"/> <?php _e('Select All', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'description'); ?>
                                           type="checkbox" name="content[]"
                                           value="description"/> <?php _e('Description', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'excerpt'); ?>
                                           type="checkbox" name="content[]"
                                           value="excerpt"/> <?php _e('Excerpt', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'gallery'); ?>
                                           type="checkbox" name="content[]"
                                           value="gallery"/> <?php _e('Main Gallery', 'wetu-importer'); ?></li>
								<?php if (class_exists('LSX_TO_Maps')) { ?>
                                    <li>
                                        <input class="content" <?php $this->checked($this->destination_options, 'location'); ?>
                                               type="checkbox" name="content[]"
                                               value="location"/> <?php _e('Location', 'wetu-importer'); ?></li>
								<?php } ?>

								<?php if (class_exists('LSX_TO_Videos')) { ?>
                                    <li>
                                        <input class="content" <?php $this->checked($this->destination_options, 'videos'); ?>
                                               type="checkbox" name="content[]"
                                               value="videos"/> <?php _e('Videos', 'wetu-importer'); ?></li>
								<?php } ?>

                            </ul>
                            <h4><?php _e('Additional Content'); ?></h4>
                            <ul>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'featured_image'); ?>
                                           type="checkbox" name="content[]"
                                           value="featured_image"/> <?php _e('Set Featured Image', 'wetu-importer'); ?>
                                </li>
								<?php if (class_exists('LSX_Banners')) { ?>
                                    <li>
                                        <input class="content" <?php $this->checked($this->destination_options, 'banner_image'); ?>
                                               type="checkbox" name="content[]"
                                               value="banner_image"/> <?php _e('Set Banner Image', 'wetu-importer'); ?>
                                    </li>
								<?php } ?>
                            </ul>
                        </div>
                        <div class="settings-all" style="width:30%;display:block;float:left;">
                            <h3><?php _e('Travel Information'); ?></h3>
                            <ul>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'electricity'); ?>
                                           type="checkbox" name="content[]"
                                           value="electricity"/> <?php _e('Electricity', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'banking'); ?>
                                           type="checkbox" name="content[]"
                                           value="banking"/> <?php _e('Banking', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'cuisine'); ?>
                                           type="checkbox" name="content[]"
                                           value="cuisine"/> <?php _e('Cuisine', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'climate'); ?>
                                           type="checkbox" name="content[]"
                                           value="climate"/> <?php _e('Climate', 'wetu-importer'); ?></li>
                                <li>
                                    <input class="content" <?php $this->checked($this->destination_options, 'transport'); ?>
                                           type="checkbox" name="content[]"
                                           value="transport"/> <?php _e('Transport', 'wetu-importer'); ?></li>
                                <li><input class="content" <?php $this->checked($this->destination_options, 'dress'); ?>
                                           type="checkbox" name="content[]"
                                           value="dress"/> <?php _e('Dress', 'wetu-importer'); ?></li>
                            </ul>
                        </div>

						<?php if (class_exists('LSX_TO_Team')) { ?>
                            <div style="width:30%;display:block;float:left;">
                                <h3><?php _e('Assign a Team Member'); ?></h3>
								<?php $this->team_member_checkboxes($this->destination_options); ?>
                            </div>
						<?php } ?>

                        <br clear="both"/>
                    </div>


                    <h3><?php _e('Your List'); ?></h3>
                    <p><input class="button button-primary" type="submit"
                              value="<?php _e('Sync', 'wetu-importer'); ?>"/></p>
                    <table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

                        <tbody>

                        </tbody>

						<?php $this->table_footer(); ?>

                    </table>

                    <p><input class="button button-primary" type="submit"
                              value="<?php _e('Sync', 'wetu-importer'); ?>"/></p>
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
	public function search_form()
	{
		?>
        <form class="ajax-form" id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php"
              data-type="<?php echo $this->tab_slug; ?>">
            <input type="hidden" name="page" value="<?php echo $this->tab_slug; ?>"/>

            <h3><span class="dashicons dashicons-search"></span> <?php _e('Search', 'wetu-importer'); ?></h3>
            <div class="normal-search">
                <input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value="">
                <input class="button button-primary submit" type="submit"
                       value="<?php _e('Search', 'wetu-importer'); ?>"/>
            </div>


            <div class="advanced-search hidden" style="display:none;">
                <p><?php _e('Enter several keywords, each on a new line.', 'wetu-importer'); ?></p>
                <textarea rows="10" cols="40" name="bulk-keywords"></textarea>
                <input class="button button-primary submit" type="submit"
                       value="<?php _e('Search', 'wetu-importer'); ?>"/>
            </div>

            <p>
                <a class="advanced-search-toggle" href="#"><?php _e('Bulk Search', 'wetu-importer'); ?></a> |
                <a class="published search-toggle"
                   href="#publish"><?php esc_attr_e('Published', 'wetu-importer'); ?></a> |
                <a class="pending search-toggle" href="#pending"><?php esc_attr_e('Pending', 'wetu-importer'); ?></a> |
                <a class="draft search-toggle" href="#draft"><?php esc_attr_e('Draft', 'wetu-importer'); ?></a>
            </p>

            <div class="ajax-loader" style="display:none;width:100%;text-align:center;">
                <img style="width:64px;" src="<?php echo WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif'; ?>"/>
            </div>

            <div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
                <img style="width:32px;" src="<?php echo WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif'; ?>"/>
            </div>
        </form>
		<?php
	}

	/**
	 * Grab all the current destination posts via the lsx_wetu_id field.
	 */
	public function find_current_destination($post_type = 'destination')
	{
		global $wpdb;
		$return = array();

		$current_destination = $wpdb->get_results("
					SELECT key1.post_id,key1.meta_value,key2.post_title as name,key2.post_date as last_modified
					FROM {$wpdb->postmeta} key1

					INNER JOIN  {$wpdb->posts} key2 
    				ON key1.post_id = key2.ID
					
					WHERE key1.meta_key = 'lsx_wetu_id'
					AND key2.post_type = '{$post_type}'

					LIMIT 0,500
		");
		if (null !== $current_destination && !empty($current_destination)) {
			foreach ($current_destination as $accom) {
				$return[$accom->meta_value] = $accom;
			}
		}
		return $return;
	}

	/**
	 * Run through the destination grabbed from the DB.
	 */
	public function process_ajax_search()
	{
		$return = false;
		if (isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === 'destination') {

			if (isset($_POST['keyword'])) {
				$searched_items = false;

				if (isset($_POST['keyword'])) {
					$keyphrases = $_POST['keyword'];
				} else {
					$keyphrases = array(0);
				}

				if (!is_array($keyphrases)) {
					$keyphrases = array($keyphrases);
				}
				foreach ($keyphrases as &$keyword) {
					$keyword = ltrim(rtrim($keyword));
				}

				$post_status = false;
				if (in_array('publish', $keyphrases)) {
					$post_status = 'publish';
				}
				if (in_array('pending', $keyphrases)) {
					$post_status = 'pending';
				}
				if (in_array('draft', $keyphrases)) {
					$post_status = 'draft';
				}

				$destination = $this->find_current_destination();

				if (!empty($destination)) {

					foreach ($destination as $row) {

						//If we are searching for
						if (false !== $post_status) {

							$current_status = get_post_status($row->post_id);
							if ($current_status !== $post_status) {
								continue;
							}
							$searched_items[sanitize_title($row->name) . '-' . $row->meta_value] = $this->format_row($row);


						} else {
							//Search through each keyword.
							foreach ($keyphrases as $keyphrase) {

								//Make sure the keyphrase is turned into an array
								$keywords = explode(" ", $keyphrase);
								if (!is_array($keywords)) {
									$keywords = array($keywords);
								}

								if ($this->multineedle_stripos(ltrim(rtrim($row->name)), $keywords) !== false) {
									$searched_items[sanitize_title($row->name) . '-' . $row->meta_value] = $this->format_row($row);
								}
							}
						}
					}
				}

				if (false !== $searched_items) {
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
	public function format_row($row = false)
	{
		if (false !== $row) {

			$status = 'import';
			if (0 !== $row->post_id) {
				$status = '<a href="' . admin_url('/post.php?post=' . $row->post_id . '&action=edit') . '" target="_blank">' . get_post_status($row->post_id) . '</a>';
			}

			$row_html = '
			<tr class="post-' . $row->post_id . ' type-tour" id="post-' . $row->post_id . '">
				<th class="check-column" scope="row">
					<label for="cb-select-' . $row->meta_value . '" class="screen-reader-text">' . $row->name . '</label>
					<input type="checkbox" data-identifier="' . $row->meta_value . '" value="' . $row->post_id . '" name="post[]" id="cb-select-' . $row->meta_value . '">
				</th>
				<td class="post-title page-title column-title">
					<strong>' . $row->name . '</strong> - ' . $status . '
				</td>
				<td class="date column-date">
					<abbr title="' . date('Y/m/d', strtotime($row->last_modified)) . '">' . date('Y/m/d', strtotime($row->last_modified)) . '</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					' . $row->meta_value . '
				</td>
			</tr>';
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import()
	{
		$return = false;
		if (isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === 'destination' && isset($_POST['wetu_id'])) {

			$wetu_id = $_POST['wetu_id'];
			if (isset($_POST['post_id'])) {
				$post_id = $_POST['post_id'];
			} else {
				$post_id = 0;
			}

			if (isset($_POST['team_members'])) {
				$team_members = $_POST['team_members'];
			} else {
				$team_members = false;
			}

			$safari_brands = false;

			if (isset($_POST['content']) && is_array($_POST['content']) && !empty($_POST['content'])) {
				$content = $_POST['content'];
				delete_option('wetu_importer_destination_settings');
				add_option('wetu_importer_destination_settings', $content);
			} else {
				delete_option('wetu_importer_destination_settings');
				$content = false;
			}

			$jdata = file_get_contents($this->url . '/Get?' . $this->url_qs . '&ids=' . $wetu_id);
			if ($jdata) {
				$adata = json_decode($jdata, true);
				if (!empty($adata)) {
					$return = $this->import_row($adata, $wetu_id, $post_id, $team_members, $content, $safari_brands);
					$this->format_completed_row($return);
				}
			}

			die();
		}

	}

	/**
	 * Connect to wetu
	 */
	public function import_row($data, $wetu_id, $id = 0, $team_members = false, $importable_content = false, $safari_brands = false)
	{

		if (trim($data[0]['type']) == 'Destination') {
			$post_name = $data_post_content = $data_post_excerpt = '';
			$post = array(
				'post_type' => 'destination',
			);

			$content_used_general_description = false;

			//Set the post_content
			if (false !== $importable_content && in_array('description', $importable_content)) {
				if (isset($data[0]['content']['extended_description'])) {
					$data_post_content = $data[0]['content']['extended_description'];
				} elseif (isset($data[0]['content']['general_description'])) {
					$data_post_content = $data[0]['content']['general_description'];
					$content_used_general_description = true;
				} elseif (isset($data[0]['content']['teaser_description'])) {
					$data_post_content = $data[0]['content']['teaser_description'];
				}
				$post['post_content'] = wp_strip_all_tags($data_post_content);
			}

			//set the post_excerpt
			if (false !== $importable_content && in_array('excerpt', $importable_content)) {
				if (isset($data[0]['content']['teaser_description'])) {
					$data_post_excerpt = $data[0]['content']['teaser_description'];
				} elseif (isset($data[0]['content']['extended_description'])) {
					$data_post_excerpt = $data[0]['content']['extended_description'];
				} elseif (isset($data[0]['content']['general_description']) && false === $content_used_general_description) {
					$data_post_excerpt = $data[0]['content']['general_description'];
				}
				$post['post_excerpt'] = $data_post_excerpt;
			}

			if (false !== $id && '0' !== $id) {
				$post['ID'] = $id;
				if (isset($data[0]['name'])) {
					$post['post_title'] = $data[0]['name'];
					$post['post_status'] = 'publish';
					$post['post_name'] = wp_unique_post_slug(sanitize_title($data[0]['name']), $id, 'draft', 'destination', 0);
				}
				$id = wp_update_post($post);
				$prev_date = get_post_meta($id, 'lsx_wetu_modified_date', true);
				update_post_meta($id, 'lsx_wetu_modified_date', strtotime($data[0]['last_modified']), $prev_date);
			} else {

				//Set the name
				if (isset($data[0]['name'])) {
					$post_name = wp_unique_post_slug(sanitize_title($data[0]['name']), $id, 'draft', 'destination', 0);
				}
				$post['post_name'] = $post_name;
				$post['post_title'] = $data[0]['name'];
				$post['post_status'] = 'publish';
				$id = wp_insert_post($post);

				//Save the WETU ID and the Last date it was modified.
				if (false !== $id) {
					add_post_meta($id, 'lsx_wetu_id', $wetu_id);
					add_post_meta($id, 'lsx_wetu_modified_date', strtotime($data[0]['last_modified']));
				}
			}

			//Set the team member if it is there
			if (post_type_exists('team') && false !== $team_members && '' !== $team_members) {
				$this->set_team_member($id, $team_members);
			}

			if (class_exists('LSX_TO_Maps')) {
				$this->set_map_data($data, $id, 4);
			}

			//Set the Room Data
			if (false !== $importable_content && in_array('videos', $importable_content)) {
				$this->set_video_data($data, $id);
			}

			//Set the Electricity
			if (false !== $importable_content && in_array('electricity', $importable_content)) {
				$this->set_travel_info($data, $id, 'electricity');
			}
			//Set the cuisine
			if (false !== $importable_content && in_array('cuisine', $importable_content)) {
				$this->set_travel_info($data, $id, 'cuisine');
			}
			//Set the banking
			if (false !== $importable_content && in_array('banking', $importable_content)) {
				$this->set_travel_info($data, $id, 'banking');
			}
			//Set the transport
			if (false !== $importable_content && in_array('transport', $importable_content)) {
				$this->set_travel_info($data, $id, 'transport');
			}
			//Set the dress
			if (false !== $importable_content && in_array('dress', $importable_content)) {
				$this->set_travel_info($data, $id, 'dress');
			}
			//Set the climate
			if (false !== $importable_content && in_array('climate', $importable_content)) {
				$this->set_travel_info($data, $id, 'climate');
			}

			//Setup some default for use in the import
			if (false !== $importable_content && (in_array('gallery', $importable_content) || in_array('banner_image', $importable_content) || in_array('featured_image', $importable_content))) {
				$this->find_attachments($id);

				//Set the featured image
				if (false !== $importable_content && in_array('featured_image', $importable_content)) {
					$this->set_featured_image($data, $id);
				}
				if (false !== $importable_content && in_array('banner_image', $importable_content)) {
					$this->set_banner_image($data, $id);
				}
				//Import the main gallery
				if (false !== $importable_content && in_array('gallery', $importable_content)) {
					$this->create_main_gallery($data, $id);
				}
			}

		}
		return $id;
	}

	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member($id, $team_members)
	{

		delete_post_meta($id, 'team_to_' . $this->tab_slug);
		foreach ($team_members as $team) {
			add_post_meta($id, 'team_to_' . $this->tab_slug, $team);
		}
	}

	/**
	 * Saves the room data
	 */
	public function set_travel_info($data, $id, $meta_key)
	{

		if (!empty($data[0]['travel_information']) && isset($data[0]['travel_information'][$meta_key])) {
			$content = $data[0]['travel_information'][$meta_key];
			$this->save_custom_field($content, $meta_key, $id);
		}
	}

}