<?php
/**
 * @package   LSX_WETU_Importer_Tours
 * @author    LightSpeed
 * @license   GPL-3+
 * @link
 * @copyright 2017 LightSpeed
 **/

class LSX_WETU_Importer_Tours extends LSX_WETU_Importer {

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
	 * Holds a list of the destination and the image it needs to grab.
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $destination_images = false;

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
	public $tour_options = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->set_variables();
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		parent::set_variables();
		if ( false !== $this->api_key ) {
			$this->url    = 'https://wetu.com/API/Itinerary/' . $this->api_key;
			$this->url_qs = '';
		}
		$tour_options = get_option( 'lsx_wetu_importer_tour_settings', false );
		if ( false !== $tour_options ) {
			$this->tour_options = $tour_options;
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">

			<div class="tablenav top">
				<div class="alignleft actions">
					<?php $this->search_form(); ?>
				</div>

				<div class="alignleft actions">
					<?php $this->update_options_form(); ?>
				</div>
				<br clear="both" />
			</div>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<td class="date column-date column-ref" colspan="5">
								<strong>
									<?php esc_html_e( 'Search for tours using the search form above', 'lsx-wetu-importer' ); ?>
								</strong>
							</td>
						</tr>
					</tbody>

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php esc_html_e( 'Add to List', 'lsx-wetu-importer' ); ?>" />
					<input class="button button-primary clear" type="button" value="<?php esc_html_e( 'Clear', 'lsx-wetu-importer' ); ?>" />
				</p>
			</form>

			<div style="display:none;" class="import-list-wrapper">
				<br />
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'What content to Sync from WETU' ); ?></h3>
							<ul>
								<?php if ( isset( $this->options ) && isset( $this->options['disable_tour_descriptions'] ) && 'on' !== $this->options['disable_tour_descriptions'] ) { ?>
									<li><input class="content" checked="checked" type="checkbox" name="content[]" value="description" /> <?php esc_html_e( 'Description', 'lsx-wetu-importer' ); ?></li>
								<?php } ?>

								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="price" /> <?php esc_html_e( 'Price', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="duration" /> <?php esc_html_e( 'Duration', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="group_size" /> <?php esc_html_e( 'Group Size', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="category" /> <?php esc_html_e( 'Category', 'lsx-wetu-importer' ); ?></li>
								<?php if ( isset( $this->options ) && isset( $this->options['disable_tour_tags'] ) && 'on' !== $this->options['disable_tour_tags'] ) { ?>
									<li><input class="content" checked="checked" type="checkbox" name="content[]" value="tags" /> <?php esc_html_e( 'Tags', 'lsx-wetu-importer' ); ?></li>
								<?php } ?>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="itineraries" /> <?php esc_html_e( 'Itinerary Days', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="start_end_point" /> <?php esc_html_e( 'Departs from / Ends in', 'lsx-wetu-importer' ); ?></li>
							</ul>
						</div>
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Itinerary Info' ); ?></h3>
							<ul>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="itinerary_description" /> <?php esc_html_e( 'Description', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="itinerary_included" /> <?php esc_html_e( 'Included', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="itinerary_excluded" /> <?php esc_html_e( 'Excluded', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="room_basis" /> <?php esc_html_e( 'Room Basis', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="drinks_basis" /> <?php esc_html_e( 'Drink Bases', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" type="checkbox" name="content[]" value="replace_itinerary_images" /> <?php esc_html_e( 'Replace Custom Images', 'lsx-wetu-importer' ); ?></li>
							</ul>

							<h4><?php esc_html_e( 'Additional Content' ); ?></h4>
							<ul>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="accommodation" /> <?php esc_html_e( 'Sync Accommodation', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="destination" /> <?php esc_html_e( 'Sync Destinations', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="featured_image" /> <?php esc_html_e( 'Featured Image', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked" type="checkbox" name="content[]" value="banner_image" /> <?php esc_html_e( 'Banner Image', 'lsx-wetu-importer' ); ?></li>
							</ul>
						</div>
						<?php if ( class_exists( 'LSX_TO_Team' ) ) { ?>
							<div style="width:30%;display:block;float:left;">
								<h3><?php esc_html_e( 'Assign a Team Member' ); ?></h3>
								<?php $this->team_member_checkboxes( $this->tour_options ); ?>
							</div>
						<?php } ?>

						<br clear="both" />
					</div>

					<h3><?php esc_html_e( 'Your List' ); ?></h3>
					<p><input class="button button-primary" type="submit" value="<?php esc_html_e( 'Sync', 'lsx-wetu-importer' ); ?>" /></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php esc_html_e( 'Sync', 'lsx-wetu-importer' ); ?>" /></p>
				</form>
			</div>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php esc_html_e( 'Completed', 'lsx-wetu-importer' ); ?> - <small><?php esc_html_e( 'Import your', 'lsx-wetu-importer' ); ?> <a href="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=accommodation"><?php esc_html_e( 'accommodation' ); ?></a> <?php esc_html_e( 'next', 'lsx-wetu-importer' ); ?></small></h3>
				<ul>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Displays the options for the form.
	 *
	 * @return void
	 */
	public function update_options_form() {
		$form_options = get_option( 'lsx_ti_tours_api_options' );
		if ( false === $form_options ) {
			$form_options = array( 'sample' );
		}
		?>
		<form method="get" class="tour-refresh-form">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->plugin_slug ); ?>" />
			<input type="hidden" name="tab" value="tour" />
			<input type="hidden" name="refresh_tours" value="true" />
			<input class="content" type="hidden" name="own" value="true" />

			<select name="type">
				<option 
				<?php
				if ( in_array( 'personal', $form_options ) ) {
					echo esc_attr( 'selected="selected"' ); }
				?>
value="personal"><?php esc_html_e( 'Personal', 'lsx-wetu-importer' ); ?></option>
				<option 
				<?php
				if ( in_array( 'sample', $form_options ) ) {
					echo esc_attr( 'selected="selected"' ); }
				?>
value="sample"><?php esc_html_e( 'Sample', 'lsx-wetu-importer' ); ?></option>
			</select>
			<input class="button submit" type="submit" value="<?php esc_attr_e( 'Refresh', 'lsx-wetu-importer' ); ?>" />
		</form>
		<?php
	}

	/**
	 * Grab all the current tour posts via the lsx_wetu_id field.
	 */
	public function find_current_tours() {
		global $wpdb;
		$return = array();

		$current_tours = $wpdb->get_results(
			"
			SELECT key1.post_id,key1.meta_value,key2.post_title
			FROM {$wpdb->postmeta} key1

			INNER JOIN  {$wpdb->posts} key2
			ON key1.post_id = key2.ID

			WHERE key1.meta_key = 'lsx_wetu_id'
			AND key2.post_type = 'tour'

			LIMIT 0,500
		"
		);

		if ( null !== $current_tours && ! empty( $current_tours ) ) {
			foreach ( $current_tours as $tour ) {
				$return[ $tour->meta_value ] = $tour;
			}
		}

		return $return;
	}

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;
		check_ajax_referer( 'lsx_wetu_ajax_action', 'security' );

		if ( isset( $_POST['action'] ) && 'lsx_tour_importer' === $_POST['action'] && isset( $_POST['type'] ) && $_POST['type'] === $this->tab_slug ) {
			$tours = get_transient( 'lsx_ti_tours' );

			if ( false !== $tours ) {
				$searched_items = false;
				if ( isset( $_POST['keyword'] ) ) {
					$keyphrases = array_map( 'sanitize_text_field', wp_unslash( $_POST['keyword'] ) );
				} else {
					$keyphrases = array( 0 );
				}

				if ( ! is_array( $keyphrases ) ) {
					$keyphrases = array( $keyphrases );
				}
				foreach ( $keyphrases as &$keyword ) {
					$keyword = ltrim( rtrim( $keyword ) );
				}

				$post_status = false;
				if ( in_array( 'publish', $keyphrases ) ) {
					$post_status = 'publish';
				}
				if ( in_array( 'pending', $keyphrases ) ) {
					$post_status = 'pending';
				}
				if ( in_array( 'draft', $keyphrases ) ) {
					$post_status = 'draft';
				}
				if ( in_array( 'import', $keyphrases ) ) {
					$post_status = 'import';
				}

				if ( ! empty( $tours ) ) {
					$current_tours = $this->find_current_tours();

					foreach ( $tours as $row_key => $row ) {
						if ( isset( $row['is_disabled'] ) && true === $row['is_disabled'] ) {
							continue;
						}

						// If this is a current tour, add its ID to the row.
						$row['post_id']    = 0;
						$row['post_title'] = $row['name'];

						if ( false !== $current_tours && array_key_exists( $row['identifier'], $current_tours ) ) {
							$row['post_id']    = $current_tours[ $row['identifier'] ]->post_id;
							$row['post_title'] = $current_tours[ $row['identifier'] ]->post_title;
						}

						// If we are searching for.
						if ( false !== $post_status ) {
							if ( 'import' === $post_status ) {

								if ( 0 !== $row['post_id'] ) {
									continue;
								} else {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row, $row_key );
								}
							} else {
								if ( 0 === $row['post_id'] ) {
									continue;
								} else {
									$current_status = get_post_status( $row['post_id'] );

									if ( $current_status !== $post_status ) {
										continue;
									}
								}

								$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row, $row_key );
							}
						} else {
							// Search through each keyword.
							foreach ( $keyphrases as $keyphrase ) {

								// Make sure the keyphrase is turned into an array.
								$keywords = explode( ' ', $keyphrase );
								if ( ! is_array( $keywords ) ) {
									$keywords = array( $keywords );
								}

								if ( $this->multineedle_stripos( ltrim( rtrim( $row['name'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row, $row_key );
								} elseif ( $this->multineedle_stripos( ltrim( rtrim( $row['reference_number'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row, $row_key );
								} elseif ( $this->multineedle_stripos( ltrim( rtrim( $row['identifier_key'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row, $row_key );
								}
							}
						}
					}
				}

				if ( false !== $searched_items ) {
					$return = implode( $searched_items );
				}
			}
			print_r( $return );
			die();
		}
	}

	/**
	 * Formats the row for output on the screen.
	 */
	public function format_row( $row = false, $row_key = '' ) {
		if ( false !== $row ) {
			$status = 'import';

			if ( 0 !== $row['post_id'] ) {
				$status = '<a href="' . admin_url( '/post.php?post=' . $row['post_id'] . '&action=edit' ) . '" target="_blank">' . get_post_status( $row['post_id'] ) . '</a>';
			}

			$row_html = '
			<tr class="post-' . $row['post_id'] . ' type-tour" id="post-' . $row['post_id'] . '">
				<td class="check-column">
					<label for="cb-select-' . $row['identifier'] . '" class="screen-reader-text">' . $row['post_title'] . '</label>
					<input type="checkbox" data-identifier="' . $row['identifier'] . '" value="' . $row['post_id'] . '" name="post[]" id="cb-select-' . $row['identifier'] . '">
				</td>
				<td class="column-order">
					' . ( $row_key + 1 ) . '
				</td>
				<td class="post-title page-title column-title">
					' . $row['post_title'] . ' - ' . $status . '
				</td>
				<td class="date column-date">
					' . $row['reference_number'] . '
				</td>
				<td class="date column-date">
					<abbr title="' . date( 'Y/m/d', strtotime( $row['last_modified'] ) ) . '">' . date( 'Y/m/d', strtotime( $row['last_modified'] ) ) . '</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					' . $row['identifier'] . '
				</td>
			</tr>';
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import( $force = false ) {
		$return = false;
		check_ajax_referer( 'lsx_wetu_ajax_action', 'security' );
		if ( isset( $_POST['action'] ) && 'lsx_import_items' === $_POST['action'] && isset( $_POST['type'] ) && $_POST['type'] === $this->tab_slug && isset( $_POST['wetu_id'] ) ) {

			$wetu_id = sanitize_text_field( $_POST['wetu_id'] );
			if ( isset( $_POST['post_id'] ) ) {
				$post_id = sanitize_text_field( $_POST['post_id'] );
			} else {
				$post_id = 0;
			}

			delete_option( 'lsx_wetu_importer_tour_settings' );

			if ( isset( $_POST['team_members'] ) ) {
				$team_members = array_map( 'sanitize_text_field', wp_unslash( $_POST['team_members'] ) );
			} else {
				$team_members = false;
			}

			if ( isset( $_POST['content'] ) && is_array( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
				$content = array_map( 'sanitize_text_field', wp_unslash( $_POST['content'] ) );
				add_option( 'lsx_wetu_importer_tour_settings', $content );
			} else {
				$content = false;
			}
			$jdata = wp_remote_get( 'https://wetu.com/API/Itinerary/V8/Get?id=' . $wetu_id );

			if ( ! is_wp_error( $jdata ) && ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && 200 === $jdata['response']['code'] ) {
				$jdata  = json_decode( $jdata['body'], true );
				$return = $this->import_row( $jdata, $wetu_id, $post_id, $team_members, $content );
				$this->format_completed_row( $return );
				$this->save_queue();
				$this->cleanup_posts();
				$this->attach_destination_images( $content );
				$this->clean_attached_destinations( $return );
			} else {
				$this->format_error( esc_html__( 'There was a problem importing ', 'lsx-wetu-importer' ) . get_the_title( $post_id ) . esc_html__( ', please try again.', 'lsx-wetu-importer' ) );
			}
		}
	}

	/**
	 * Amends the tours destinations instead of replace.
	 *
	 * @param $id string
	 * @return void
	 */
	public function clean_attached_destinations( $id ) {
		$current_connections = get_post_meta( $id, 'destination_to_tour', false );
		delete_post_meta( $id, 'destination_to_tour' );
		$current_connections = array_unique( $current_connections );

		foreach ( $current_connections as $connection ) {
			add_post_meta( $id, 'destination_to_tour', $connection, false );
		}
	}

	/**
	 * Connect to wetu
	 *
	 * @param $data array
	 * @param $wetu_id string
	 */
	public function import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = array(), $old1 = false, $old2 = false ) {
		$post_name         = '';
		$data_post_content = '';
		$data_post_excerpt = '';

		$current_post = get_post( $id );

		$post = array(
			'post_type' => 'tour',
		);

		$content_used_general_description = false;

		if ( ! empty( $importable_content ) && in_array( 'description', $importable_content ) ) {
			$data_post_content = $current_post->post_content;
			if ( isset( $data['summary'] ) && ! empty( $data['summary'] ) ) {
				$data_post_content = $data['summary'];
			}
			$post['post_content'] = $data_post_content;
		}

		// Create or update the post.
		if ( false !== $id && '0' !== $id ) {
			$post['ID']          = $id;
			$post['post_status'] = 'publish';
			if ( isset( $this->options ) && 'on' !== $this->options['disable_accommodation_title'] ) {
				$post['post_title'] = $data['name'];
			}
			$id = wp_update_post( $post );
		} else {
			// Set the name.
			if ( isset( $data['name'] ) ) {
				$post_name = wp_unique_post_slug( sanitize_title( $data['name'] ), $id, 'draft', 'tour', 0 );
			}

			if ( ! isset( $post['post_content'] ) ) {
				$post['post_content'] = ' ';
			}

			$post['post_name']   = $post_name;
			$post['post_title']  = $data['name'];
			$post['post_status'] = 'publish';
			$id                  = wp_insert_post( $post );

			// Save the WETU ID and the Last date it was modified.
			if ( false !== $id ) {
				add_post_meta( $id, 'lsx_wetu_id', $wetu_id );
				add_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data['last_modified'] ) );
			}
		}

		// Set reference number.
		$this->set_reference_number( $data, $id );

		// Set the team member if it is there.
		if ( post_type_exists( 'team' ) && false !== $team_members && '' !== $team_members ) {
			$this->set_team_member( $id, $team_members );
		}

		// Set the price.
		if ( false !== $importable_content && in_array( 'price', $importable_content ) ) {
			$this->set_price( $data, $id );
		}

		// Set the Duration.
		if ( false !== $importable_content && in_array( 'duration', $importable_content ) ) {
			$this->set_duration( $data, $id );
		}

		// Set the Group Size.
		if ( false !== $importable_content && in_array( 'group_size', $importable_content ) ) {
			$this->set_group_size( $data, $id );
		}

		// Set the Group Size.
		if ( false !== $importable_content && in_array( 'category', $importable_content ) ) {
			$this->set_travel_styles( $id, $data );
		}

		// Set the Start and End Point Destinations.
		if ( false !== $importable_content && in_array( 'start_end_point', $importable_content ) ) {
			$this->set_start_end_point( $data, $id );
		}

		if ( false !== $importable_content && in_array( 'itineraries', $importable_content ) && isset( $data['legs'] ) && ! empty( $data['legs'] ) ) {
			$this->process_itineraries( $data, $id, $importable_content );
		}

		if ( in_array( 'map', $importable_content ) && isset( $data['routes'] ) && ! empty( $data['routes'] ) ) {
			$this->set_map_data( $data, $id );
		}

		return $id;
	}

	/**
	 * A loop which runs through each leg on the tour.
	 */
	public function process_itineraries( $data, $id, $importable_content ) {
		$day_counter = 1;
		$leg_counter = 0;

		// Change this to check for a parameter
		if ( true ) {
			$current_featured_images = $this->get_current_itinerary_images( $id );
		}

		delete_post_meta( $id, 'itinerary' );

		if ( false !== $importable_content && in_array( 'accommodation', $importable_content ) ) {
			delete_post_meta( $id, 'accommodation_to_tour' );
		}

		foreach ( $data['legs'] as $leg ) {

			// Itinerary Accommodation.
			$current_accommodation = false;
			$current_destination   = false;
			
			if ( false !== $importable_content && in_array( 'accommodation', $importable_content ) ) {
				$current_accommodation = $this->set_accommodation( $leg, $id );
			}

			if ( 'Mobile' !== $leg['type'] ) {
				if ( false !== $importable_content && in_array( 'destination', $importable_content ) ) {
					$current_destination = $this->set_destination( $leg, $id, $leg_counter );
				}
			}

			// If the Nights are the same mount of days in the array,  then it isnt "By Destination".
			if ( ( 1 <= (int) $leg['nights'] && isset( $leg['periods'] ) ) || 0 === $leg['itinerary_leg_id'] ) {

				foreach ( $leg['periods'] as $day_key => $day ) {
					$current_day = array();

					// If this is a moble tented solution.
					$next_day_count = $day_counter + (int) $day['days'];

					if ( ( isset( $leg['stops'] ) && 'Mobile' !== $leg['type'] ) || ( 1 < (int) $day['days'] ) ) {
						$day_count_label = ' - ' . ( $next_day_count - 1 );
					} else {
						$day_count_label = '';
					}
					$current_day['title'] = esc_attr( 'Day ', 'lsx-wetu-importer' ) . $day_counter . $day_count_label;

					// Description.
					if ( false !== $importable_content && in_array( 'itinerary_description', $importable_content ) && isset( $day['notes'] ) ) {
						$current_day['description'] = $day['notes'];
					} else {
						$current_day['description'] = '';
					}

					// Itinerary Gallery.
					if ( false !== $importable_content && in_array( 'replace_itinerary_images', $importable_content ) ) {
						$current_day['featured_image'] = '';
					} else if ( isset( $current_featured_images[ $day_counter ] ) ) {
						$current_day['featured_image'] = $current_featured_images[ $day_counter ];
					}

					// Accommodation.
					if ( false !== $current_accommodation ) {
						$current_day['accommodation_to_tour'] = array( $current_accommodation );
					} else {
						$current_day['accommodation_to_tour'] = array();
					}

					// If its a mobile safari, we need to get the destination and accommodation data from the stops.
					if ( 'Mobile' === $leg['type'] ) {
						$current_destination   = $this->get_mobile_destination( $day, $leg, $id );
					}

					// Destination.
					if ( false !== $current_destination ) {
						$current_day['destination_to_tour'] = array( $current_destination );
					} else {
						$current_day['destination_to_tour'] = array();
					}

					// Included.
					if ( false !== $importable_content && in_array( 'itinerary_included', $importable_content ) && isset( $day['included'] ) && '' !== $day['included'] ) {
						$current_day['included'] = $day['included'];
					} else {
						$current_day['included'] = '';
					}

					// Excluded.
					if ( false !== $importable_content && in_array( 'itinerary_excluded', $importable_content ) && isset( $day['excluded'] ) && '' !== $day['excluded'] ) {
						$current_day['excluded'] = $day['excluded'];
					} else {
						$current_day['excluded'] = '';
					}

					// Excluded.
					if ( false !== $importable_content && in_array( 'room_basis', $importable_content ) && isset( $day['room_basis'] ) && '' !== $day['room_basis'] ) {
						$current_day['room_basis'] = $day['room_basis'];
					} else {
						$current_day['room_basis'] = '';
					}

					// Excluded.
					if ( false !== $importable_content && in_array( 'drinks_basis', $importable_content ) && isset( $day['drinks_basis'] ) && '' !== $day['drinks_basis'] ) {
						$current_day['drinks_basis'] = $day['drinks_basis'];
					} else {
						$current_day['drinks_basis'] = '';
					}

					$this->set_itinerary_day( $current_day, $id );
					$day_counter = $next_day_count;
				}
			} else {
				// This is for the by destination.

				$current_day     = array();
				$next_day_count  = $day_counter + (int) $leg['nights'];
				$day_count_label = $next_day_count - 1;

				$current_day['title'] = esc_attr( 'Day ', 'lsx-wetu-importer' ) . $day_counter;

				if ( 0 !== (int) $leg['nights'] ) {
					$current_day['title'] .= ' - ' . $day_count_label;
				}

				// Description.
				if ( false !== $importable_content && in_array( 'itinerary_description', $importable_content ) && isset( $leg['notes'] ) ) {
					$current_day['description'] = $leg['notes'];
				} else {
					$current_day['description'] = '';
				}

				// Itinerary Gallery.
				if ( false !== $importable_content && in_array( 'itinerary_gallery', $importable_content ) && isset( $leg['images'] ) ) {
					$current_day['featured_image'] = '';
				} else {
					$current_day['featured_image'] = '';
				}

				// Accommodation.
				if ( false !== $current_accommodation ) {
					$current_day['accommodation_to_tour'] = array( $current_accommodation );
				} else {
					$current_day['accommodation_to_tour'] = array();
				}

				// If its a mobile safari, we need to get the destination and accommodation data from the stops.
				if ( 'Mobile' === $leg['type'] ) {
					$current_destination   = $this->get_mobile_destination( $day, $leg, $id );
				}

				// Destination.
				if ( false !== $current_destination ) {
					$current_day['destination_to_tour'] = array( $current_destination );
				} else {
					$current_day['destination_to_tour'] = array();
				}

				// Itinerary Gallery.
				if ( false !== $importable_content && in_array( 'replace_itinerary_images', $importable_content ) ) {
					$current_day['featured_image'] = '';
				} else if ( isset( $current_featured_images[ $day_counter ] ) ) {
					$current_day['featured_image'] = $current_featured_images[ $day_counter ];
				} 

				// Included.
				if ( false !== $importable_content && in_array( 'itinerary_included', $importable_content ) && isset( $leg['included'] ) && '' !== $leg['included'] ) {
					$current_day['included'] = $leg['included'];
				} else {
					$current_day['included'] = '';
				}

				// Excluded.
				if ( false !== $importable_content && in_array( 'itinerary_excluded', $importable_content ) && isset( $leg['excluded'] ) && '' !== $leg['excluded'] ) {
					$current_day['excluded'] = $leg['excluded'];
				} else {
					$current_day['excluded'] = '';
				}

				// Excluded.
				if ( false !== $importable_content && in_array( 'room_basis', $importable_content ) && isset( $leg['room_basis'] ) && '' !== $leg['room_basis'] ) {
					$current_day['room_basis'] = $leg['room_basis'];
				} else {
					$current_day['room_basis'] = '';
				}

				// Excluded.
				if ( false !== $importable_content && in_array( 'drinks_basis', $importable_content ) && isset( $leg['drinks_basis'] ) && '' !== $leg['drinks_basis'] ) {
					$current_day['drinks_basis'] = $leg['drinks_basis'];
				} else {
					$current_day['drinks_basis'] = '';
				}

				$this->set_itinerary_day( $current_day, $id );
				$day_counter = $next_day_count;
			}
			$leg_counter++;
		}
	}

	/**
	 * Grabs the current itinerary images set, and logs them against an entry counter.
	 *
	 * @param integer $id
	 * @return array
	 */
	public function get_current_itinerary_images( $id = 0 ) {
		$current_featured_images = array();
		if ( 0 !== $id ) {
			$itineraries = get_post_meta( $id, 'itinerary', false );
			if ( ! empty( $itineraries ) ) {
				$counter = 1;
				foreach ( $itineraries as $itinerary ) {
					if ( isset( $itinerary['featured_image'] ) && '' !== $itinerary['featured_image'] ) {
						$current_featured_images[ $counter ] = $itinerary['featured_image'];
					}
					$counter++;
				}
			}
		}
		return $current_featured_images;
	}

	/**
	 * Sets the departs from and ends in points on the tours.
	 *
	 * @param array  $data
	 * @param string $id
	 * @return void
	 */
	public function set_start_end_point( $data, $id ) {
		delete_post_meta( $id, 'departs_from' );
		delete_post_meta( $id, 'ends_in' );
		$departs_from = false;
		$ends_in      = false;

		$args = array(
			'points'      => $data['legs'],
			'start_index' => 0,
			'end_index'   => count( $data['legs'] ) - 2,
		);
		$args = apply_filters( 'lsx_wetu_start_end_args', $args, $data );

		if ( ! empty( $args['points'] ) && is_array( $args['points'] ) ) {
			$leg_counter = 0;

			foreach ( $args['points'] as $point ) {
				// If we are in the first leg,  and the destination was attached then save it as the departure field.
				if ( $leg_counter === $args['start_index'] ) {
					$departs_from_destination = $this->set_country( $point['destination_content_entity_id'], $id );
					if ( false !== $departs_from_destination ) {
						$departs_from = $departs_from_destination;
					}
				}
				// If its the last leg then save it as the ends in.
				if ( $leg_counter === $args['end_index'] ) {
					$ends_in = $point['destination_content_entity_id'];
				}
				$leg_counter++;
			}

			$departs_from = apply_filters( 'lsx_wetu_departs_from_id', $departs_from, $data, $this );
			if ( false !== $departs_from ) {
				add_post_meta( $id, 'departs_from', $departs_from, true );
			}

			if ( false !== $ends_in ) {
				$ends_in             = apply_filters( 'lsx_wetu_ends_in_id', $ends_in, $data, $this );
				$ends_in_destination = $this->set_country( $ends_in, $id );
				if ( false !== $ends_in_destination ) {
					add_post_meta( $id, 'ends_in', $ends_in_destination, true );
				}
			}
		}
	}

	/**
	 * Gets the destination for the mobile camp.
	 *
	 * @param $day
	 * @param $leg
	 * @return void
	 */
	public function get_mobile_destination( $day, $leg, $id ) {
		$current_destination = false;
		$current_day         = (int) $day['period_start_day'];
		if ( isset( $leg['stops'] ) ) {
			foreach ( $leg['stops'] as $stop ) {
				$arrival_day   = (int) $stop['arrival_day'];
				$departure_day = (int) $stop['departure_day'];
				if ( $arrival_day <= $current_day && $current_day <= $departure_day ) {
					$current_destination = $this->set_destination( $stop, $id, 0 );
				}
			}
		}
		return $current_destination;
	}

	/**
	 * Gets the accommodation for the mobile camp.
	 *
	 * @param $day
	 * @param $leg
	 * @return void
	 */
	public function get_mobile_accommodation( $day, $leg, $id ) {
		$current_accommodation = false;
		$current_day           = (int) $day['period_start_day'];
		if ( isset( $leg['stops'] ) ) {
			foreach ( $leg['stops'] as $stop ) {
				$arrival_day   = (int) $stop['arrival_day'];
				$departure_day = (int) $stop['departure_day'];
				if ( $arrival_day <= $current_day && $current_day < $departure_day ) {
					$current_accommodation = $this->set_accommodation( $stop, $id, 0 );
				}
			}
		}
		return $current_accommodation;
	}

	/**
	 * Run through your routes and save the points as a KML file.
	 */
	public function set_map_data( $data, $id, $zoom = 9 ) {
		if ( ! empty( $data['routes'] ) ) {
			delete_post_meta( $id, 'wetu_map_points' );

			$points = array();

			foreach ( $data['routes'] as $route ) {

				if ( isset( $route['points'] ) && '' !== $route['points'] ) {

					$temp_points   = explode( ';', $route['points'] );
					$point_counter = count( $temp_points );

					for ( $x = 0; $x <= $point_counter; $x++ ) {
						$y        = $x + 1;
						$points[] = $temp_points[ $x ] . ',' . $temp_points[ $y ];
						$x++;
					}
				}
			}

			if ( ! empty( $points ) ) {
				$this->save_custom_field( implode( ' ', $points ), 'wetu_map_points', $id, false, true );
			}
		}

	}

	// CLASS SPECIFIC FUNCTIONS.

	/**
	 * Set the Itinerary Day.
	 */
	public function set_itinerary_day( $day, $id ) {
		$this->save_custom_field( $day, 'itinerary', $id, false, false );
	}

	/**
	 * Set the ref number
	 */
	public function set_reference_number( $data, $id ) {
		if ( isset( $data['reference_number'] ) && '' !== $data['reference_number'] ) {
			$this->save_custom_field( $data['reference_number'], 'lsx_wetu_ref', $id );
		}
	}

	/**
	 * Set the price.
	 */
	public function set_price( $data, $id ) {
		// Price.
		if ( isset( $data['price'] ) && '' !== $data['price'] ) {
			$price = $data['price'];
			if ( false === apply_filters( 'lsx_wetu_importer_disable_tour_price_filter', false ) ) {
				$price = preg_replace( '/[^0-9,.]/', '', $price );
			}
			$meta_key = apply_filters( 'lsx_wetu_importer_price_meta_key', 'price' );
			$this->save_custom_field( $price, $meta_key, $id );
		}

		// Price includes.
		if ( isset( $data['price_includes'] ) && '' !== $data['price_includes'] ) {
			$meta_key = apply_filters( 'lsx_wetu_importer_included_meta_key', 'included' );
			$this->save_custom_field( $data['price_includes'], $meta_key, $id );
		}

		// Price Excludes.
		if ( isset( $data['price_excludes'] ) && '' !== $data['price_excludes'] ) {
			$meta_key = apply_filters( 'lsx_wetu_importer_not_included_meta_key', 'not_included' );
			$this->save_custom_field( $data['price_excludes'], $meta_key, $id );
		}
	}

	/**
	 * Set the duration.
	 */
	public function set_duration( $data, $id ) {
		if ( isset( $data['days'] ) && ! empty( $data['days'] ) ) {
			$price = $data['days'];
			$price = preg_replace( '/[^0-9,.]/', '', $price );
			$this->save_custom_field( $price, 'duration', $id );
		}
	}

	/**
	 * Set the group size
	 */
	public function set_group_size( $data, $id ) {
		if ( isset( $data['group_size'] ) && ! empty( $data['group_size'] ) ) {
			$group_size = $data['group_size'];
			$this->save_custom_field( $group_size, 'group_size', $id );
		}
	}

	/**
	 * Takes the WETU tags and sets the Travel Styles.
	 *
	 * @param string $id
	 * @param array  $travel_styles
	 * @return void
	 */
	public function set_travel_styles( $id, $data ) {
		$tags = apply_filters( 'lsx_wetu_importer_tour_travel_styles', $data['tags'], $id, $this );
		if ( isset( $data['tags'] ) && ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$this->set_term( $id, $tag, 'travel-style' );
			}
		}
	}

	/**
	 * Connects the Accommodation if its available
	 */
	public function set_accommodation( $day, $id ) {
		$ac_id                       = false;
		$this->current_accommodation = $this->find_current_accommodation();

		if ( isset( $day['content_entity_id'] ) && ! empty( $day['content_entity_id'] ) && ! in_array( (int) $day['content_entity_id'], array( 25862 ) ) ) {
			if ( false !== $this->current_accommodation && ! empty( $this->current_accommodation ) && array_key_exists( $day['content_entity_id'], $this->current_accommodation ) ) {
				$ac_id = $this->current_accommodation[ $day['content_entity_id'] ];
			} else {
				$ac_id = wp_insert_post(
					array(
						'post_type'   => 'accommodation',
						'post_status' => 'draft',
						'post_title'  => $day['content_entity_id'],
					)
				);

				$this->save_custom_field( $day['content_entity_id'], 'lsx_wetu_id', $ac_id );
			}

			if ( '' !== $ac_id && false !== $ac_id ) {
				$this->save_custom_field( $ac_id, 'accommodation_to_tour', $id, false, false );
				$this->save_custom_field( $id, 'tour_to_accommodation', $ac_id, false, false );
				$this->queue_item( $ac_id );
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
	public function find_current_accommodation( $post_type = 'accommodation' ) {
		global $wpdb;
		$accommodation = parent::find_current_accommodation( $post_type );
		$return        = false;

		if ( ! empty( $accommodation ) ) {
			foreach ( $accommodation as $key => $acc ) {
				$return[ $acc->meta_value ] = $acc->post_id;
			}
		}

		return $return;
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 *
	 * @return boolean / array
	 */
	public function find_current_destinations() {
		return $this->find_current_accommodation( 'destination' );
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $day array
	 * @param $id string
	 * @return boolean / string
	 */
	public function set_destination( $day, $id, $leg_counter ) {
		$dest_id    = false;
		$country_id = false;

		$this->current_destinations = $this->find_current_destinations();

		if ( isset( $day['destination_content_entity_id'] ) && ! empty( $day['destination_content_entity_id'] ) ) {
			if ( false !== $this->current_destinations && ! empty( $this->current_destinations ) && array_key_exists( $day['destination_content_entity_id'], $this->current_destinations ) ) {
				$dest_id = $this->current_destinations[ $day['destination_content_entity_id'] ];

				// TODO Check for attachments here.
				$this->destination_images[ $id ][] = array( $dest_id, $day['destination_content_entity_id'] );

				// Check if there is a country asigned.
				$potential_id    = wp_get_post_parent_id( $dest_id );
				$country_wetu_id = get_post_meta( $potential_id, 'lsx_wetu_id', true );

				if ( false !== $country_wetu_id ) {
					$country_id = $this->set_country( $country_wetu_id, $id );
					// $this->destination_images[ $id ][] = array( $id, $country_wetu_id );
				}
			} else {
				$destination_json = wp_remote_get( 'https://wetu.com/API/Pins/' . $this->api_key . '/Get?ids=' . $day['destination_content_entity_id'] );

				if ( ! is_wp_error( $destination_json ) && ! empty( $destination_json ) && isset( $destination_json['response'] ) && isset( $destination_json['response']['code'] ) && 200 === $destination_json['response']['code'] ) {

					$destination_data = json_decode( $destination_json['body'], true );

					if ( ! empty( $destination_data ) && ! isset( $destination_data['error'] ) ) {
						$destination_title = $day['destination_content_entity_id'];

						if ( isset( $destination_data[0]['name'] ) ) {
							$destination_title = $destination_data[0]['name'];
						}

						if ( isset( $destination_data[0]['map_object_id'] ) && isset( $destination_data[0]['position']['country_content_entity_id'] )
							&& $destination_data[0]['map_object_id'] !== $destination_data[0]['position']['country_content_entity_id'] ) {

							$country_id = $this->set_country( $destination_data[0]['position']['country_content_entity_id'], $id );
							// Save the destination so we can grab the tour featured image and banner from them.
						}

						$dest_post = array(
							'post_type'   => 'destination',
							'post_status' => 'draft',
							'post_title'  => $destination_title,
						);

						if ( false !== $country_id ) {
							$dest_post['post_parent'] = $country_id;
						}
						$dest_id = wp_insert_post( $dest_post );

						// Make sure we register the.
						$this->current_destinations[ $day['destination_content_entity_id'] ] = $dest_id;

						// If there are images attached then use the destination.
						if ( isset( $destination_data[0]['content']['images'] ) && ! empty( $destination_data[0]['content']['images'] ) ) {
							$this->destination_images[ $id ][] = array( $dest_id, $day['destination_content_entity_id'] );
						}

						$this->save_custom_field( $day['destination_content_entity_id'], 'lsx_wetu_id', $dest_id );
					}
				}
			}

			if ( '' !== $dest_id && false !== $dest_id ) {
				$this->save_custom_field( $dest_id, 'destination_to_tour', $id, false, false );
				$this->save_custom_field( $id, 'tour_to_destination', $dest_id, false, false );

				// Save the item to display in the queue
				$this->queue_item( $dest_id );

				// Save the item to clean up the amount of connections.
				$this->cleanup_posts[ $dest_id ] = 'tour_to_destination';

				// Add this relation info so we can make sure certain items are set as countries.
				if ( 0 !== $country_id && false !== $country_id ) {
					$this->relation_meta[ $dest_id ]    = $country_id;
					$this->relation_meta[ $country_id ] = 0;
				} else {
					$this->relation_meta[ $dest_id ] = 0;
				}
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
	 *
	 * @return string
	 */
	public function set_country( $country_wetu_id, $id ) {
		$country_id                 = false;
		$this->current_destinations = $this->find_current_destinations();

		if ( false !== $this->current_destinations && ! empty( $this->current_destinations ) && array_key_exists( $country_wetu_id, $this->current_destinations ) ) {
			$country_id                        = $this->current_destinations[ $country_wetu_id ];
			$this->destination_images[ $id ][] = array( $country_id, $country_wetu_id );
		} else {
			$country_json = wp_remote_get( 'https://wetu.com/API/Pins/' . $this->api_key . '/Get?ids=' . $country_wetu_id );

			if ( ! is_wp_error( $country_json ) && ! empty( $country_json ) && isset( $country_json['response'] ) && isset( $country_json['response']['code'] ) && 200 === $country_json['response']['code'] ) {
				$country_data = json_decode( $country_json['body'], true );

				// Format the title of the destination if its available,  otherwise default to the WETU ID.
				$country_title = $country_wetu_id;

				if ( isset( $country_data[0]['name'] ) ) {
					$country_title = $country_data[0]['name'];
				}

				$country_id = wp_insert_post(
					array(
						'post_type'   => 'destination',
						'post_status' => 'draft',
						'post_title'  => $country_title,
					)
				);

				// add the country to the current destination stack
				$this->current_destinations[ $country_wetu_id ] = $country_id;

				// Check if there are images and save fore use later.
				if ( isset( $country_data[0]['content']['images'] ) && ! empty( $country_data[0]['content']['images'] ) ) {
					$this->destination_images[ $id ][] = array( $country_id, $country_wetu_id );
				}

				// Save the wetu field
				$this->save_custom_field( $country_wetu_id, 'lsx_wetu_id', $country_id );
			}
		}

		if ( '' !== $country_id && false !== $country_id ) {
			$this->save_custom_field( $country_id, 'destination_to_tour', $id, false, false );
			$this->save_custom_field( $id, 'tour_to_destination', $country_id, false, false );
			$this->queue_item( $country_id );
			$this->cleanup_posts[ $country_id ] = 'tour_to_destination';

			return $country_id;
		}
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $dest_id string
	 * @param $country_id array
	 * @param $id string
	 *
	 * @return string
	 */
	public function attach_destination_images( $importable_content = array() ) {
		if ( false !== $this->destination_images ) {
			$this->shuffle_assoc( $this->destination_images );
			foreach ( $this->destination_images as $tour => $destinations ) {
				shuffle( $destinations );
				$image_set = false;
				$forced    = false;

				foreach ( $destinations as $destination ) {
					if ( false === $image_set && false === $forced ) {
						$url = 'https://wetu.com/API/Pins/' . $this->api_key;

						$url_qs = '';
						$jdata  = wp_remote_get( $url . '/Get?' . $url_qs . '&ids=' . $destination[1] );

						if ( ! is_wp_error( $jdata ) && ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && 200 === $jdata['response']['code'] ) {
							$adata = json_decode( $jdata['body'], true );

							if ( ! empty( $adata ) && ! empty( $adata[0]['content']['images'] ) ) {
								$this->find_attachments( $destination[0] );

								// Set the featured image.
								if ( false !== $importable_content && in_array( 'featured_image', $importable_content ) ) {
									$image_set = $this->set_featured_image( $adata, $tour );
									if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
										$image_set = $this->set_banner_image( $adata, $tour );
										$forced    = true;
									}
									continue;
								}
								if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
									$image_set = $this->set_banner_image( $adata, $tour );
								}
							}
						}
					} else {
						continue;
					}
				}
			}
		}
	}

	/**
	 * Creates the main gallery data
	 */
	public function set_featured_image( $data, $id ) {
		$image_set = false;
		$counter   = 0;

		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			$images_array = $data[0]['content']['images'];

			if ( 'on' === $this->options['enable_tour_featured_random'] ) {
				shuffle( $images_array );
			}

			foreach ( $images_array as $v ) {

				if ( true === $image_set ) {
					$counter++;
					continue;
				}

				if ( ! $this->check_if_image_is_used( $v ) ) {
					$temp_featured_image = $this->attach_image( $v, $id );

					if ( false !== $temp_featured_image ) {
						$this->featured_image = $temp_featured_image;
						delete_post_meta( $id, '_thumbnail_id' );
						add_post_meta( $id, '_thumbnail_id', $this->featured_image, true );
						$image_set = true;
					}
				}

				$counter++;
			}
		}
		return $image_set;
	}

	/**
	 * Sets a banner image
	 */
	public function set_banner_image( $data, $id, $content = array( 'none' ) ) {
		$image_set = false;
		$counter   = 0;

		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {

			foreach ( $data[0]['content']['images'] as $v ) {
				/*
				print_r('<pre>');
				print_r( $v );
				print_r('</pre>');*/

				if ( true === $image_set || 0 === $counter ) {
					$counter++;
					continue;
				}

				if ( ! $this->check_if_image_is_used( $v ) ) {
					$temp_banner = $this->attach_image(
						$v,
						$id,
						array(
							'width'    => '1920',
							'height'   => '600',
							'cropping' => 'c',
						)
					);

					if ( false !== $temp_banner ) {
						$this->banner_image = $temp_banner;

						delete_post_meta( $id, 'image_group' );

						$new_banner = array(
							'banner_image' => array(
								'cmb-field-0' => $this->banner_image,
							),
						);
						add_post_meta( $id, 'image_group', $new_banner, true );
						$image_set = true;
					}
				}
				$counter++;
			}
		}

		return $image_set;
	}

	/**
	 * Grabs all of the current used featured images on the site.
	 */
	public function check_if_image_is_used( $v ) {
		global $wpdb;
		$return = false;

		$results        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id
				 FROM {$wpdb->postmeta}
				 WHERE meta_value = '%s'
				 AND meta_key = 'lsx_wetu_id'
				",
				array( $value )
			)
		);
		$attached_tours = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				if ( 'tour' === get_post_type( $result['post_id'] ) ) {
					$attached_tours[] = $result['post_id'];
				}
			}
		}
		if ( ! empty( $attached_tours ) ) {
			$return = true;
		}
		return $return;
	}

	/**
	 * Que an item to be saved.
	 *
	 * @param   $id     int
	 */
	public function queue_item( $id ) {
		if ( is_array( $this->import_queue ) && ! in_array( $id, $this->import_queue ) ) {
			$this->import_queue[] = $id;
		} else {
			$this->import_queue[] = $id;
		}
	}

	/**
	 * Saves the queue to the option.
	 */
	public function save_queue() {
		if ( ! empty( $this->import_queue ) ) {
			if ( ! empty( $this->queued_imports ) ) {
				$saved_imports = array_merge( $this->queued_imports, $this->import_queue );
			} else {
				$saved_imports = $this->import_queue;
			}

			delete_option( 'lsx_wetu_importer_que' );

			if ( ! empty( $saved_imports ) ) {
				$saved_imports = array_unique( $saved_imports );
				update_option( 'lsx_wetu_importer_que', $saved_imports );
			}
		}
	}

	/**
	 * The header of the item list
	 */
	public function table_header() {
		?>
		<thead>
		<tr>
			<th class="manage-column column-cb check-column" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'lsx-wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th class="manage-column column-order " id="order" scope="col"><?php esc_attr_e( 'Order', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-title " id="title" scope="col"><?php esc_attr_e( 'Title', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-date" id="ref" scope="col"><?php esc_attr_e( 'Ref', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-date" id="date" scope="col"><?php esc_attr_e( 'Date', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-ssid" id="ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'lsx-wetu-importer' ); ?></th>
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
			<th class="manage-column column-cb check-column" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'lsx-wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th class="manage-column column-order " id="order" scope="col"><?php esc_attr_e( 'Order', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-title" scope="col"><?php esc_attr_e( 'Title', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-date" id="ref" scope="col"><?php esc_attr_e( 'Ref', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-date" scope="col"><?php esc_attr_e( 'Date', 'lsx-wetu-importer' ); ?></th>
			<th class="manage-column column-ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'lsx-wetu-importer' ); ?></th>
		</tr>
		</tfoot>
		<?php
	}
}
