<?php
/**
 * @package   LSX_WETU_Importer_Destination
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 **/

class LSX_WETU_Importer_Destination extends LSX_WETU_Importer {

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
	public function __construct() {
		$this->set_variables();
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		parent::set_variables();
		$this->url    = 'https://wetu.com/API/Pins/' . $this->api_key;
		$this->url_qs = 'all=include';
		$temp_options = get_option( '_lsx-to_settings', false );

		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) && ! empty( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];
		}

		$destination_options = get_option( 'lsx_wetu_importer_destination_settings', false );

		if ( false !== $destination_options ) {
			$this->destination_options = $destination_options;
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<div class="tablenav top">
				<div class="actions">
					<?php $this->search_form(); ?>
				</div>
			</div>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>"/>

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody id="the-list">
					<tr class="post-0 type-tour status-none" id="post-0">
						<th class="check-column" scope="row">
							<label for="cb-select-0"
								   class="screen-reader-text"><?php esc_html_e( 'Enter a title to search for and press enter', 'lsx-wetu-importer' ); ?></label>
						</th>
						<td class="post-title page-title column-title">
							<strong>
								<?php esc_html_e( 'Enter a title to search for', 'lsx-wetu-importer' ); ?>
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
						  value="<?php esc_html_e( 'Add to List', 'lsx-wetu-importer' ); ?>"/>
					<input class="button button-primary clear" type="button"
						   value="<?php esc_html_e( 'Clear', 'lsx-wetu-importer' ); ?>"/>
				</p>
			</form>

			<div style="display:none;" class="import-list-wrapper">
				<br/>
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'What content to Sync from WETU' ); ?></h3>
							<ul>
								<?php if ( isset( $this->options ) && isset( $this->options['disable_destination_descriptions'] ) && 'on' !== $this->options['disable_destination_descriptions'] ) { ?>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="description"/> <?php esc_html_e( 'Description', 'lsx-wetu-importer' ); ?></li>
								<?php } ?>

								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="gallery"/> <?php esc_html_e( 'Main Gallery', 'lsx-wetu-importer' ); ?></li>

									<li>
										<input class="content" checked="checked"
											   type="checkbox" name="content[]"
											   value="location"/> <?php esc_html_e( 'Location', 'lsx-wetu-importer' ); ?>
									</li>


								<?php if ( class_exists( 'LSX_TO_Videos' ) ) { ?>
									<li>
										<input class="content" checked="checked"
											   type="checkbox" name="content[]"
											   value="videos"/> <?php esc_html_e( 'Videos', 'lsx-wetu-importer' ); ?></li>
								<?php } ?>

							</ul>
							<h4><?php esc_html_e( 'Additional Content' ); ?></h4>
							<ul>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="country"/> <?php esc_html_e( 'Set Country', 'lsx-wetu-importer' ); ?></li>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="continent"/> <?php esc_html_e( 'Set Continent', 'lsx-wetu-importer' ); ?></li>

								<?php if ( isset( $this->options ) && isset( $this->options['disable_destination_image_featured'] ) && 'on' !== $this->options['disable_destination_image_featured'] ) { ?>
									<li>
										<input class="content" checked="checked"
											type="checkbox" name="content[]"
											value="featured_image"/> <?php esc_html_e( 'Set Featured Image', 'lsx-wetu-importer' ); ?>
									</li>
								<?php } ?>

								<?php if ( isset( $this->options ) && isset( $this->options['disable_destination_image_banner'] ) && 'on' !== $this->options['disable_destination_image_banner'] ) { ?>
									<li>
										<input class="content" checked="checked"
												type="checkbox" name="content[]"
												value="banner_image"/> <?php esc_html_e( 'Set Banner Image', 'lsx-wetu-importer' ); ?>
									</li>
								<?php } ?>
								<?php
								/*
								<li>
									<input class="content"
											type="checkbox" name="content[]"
											value="unique_banner_image"/> <?php esc_html_e( 'Use the WETU banner field', 'lsx-wetu-importer' ); ?>
								</li>*/
								?>

								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="strip_tags"/> <?php esc_html_e( 'Strip HTML from the description', 'lsx-wetu-importer' ); ?></li>
							</ul>
						</div>
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Travel Information' ); ?></h3>
							<ul>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="electricity"/> <?php esc_html_e( 'Electricity', 'lsx-wetu-importer' ); ?></li>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="banking"/> <?php esc_html_e( 'Banking', 'lsx-wetu-importer' ); ?></li>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="cuisine"/> <?php esc_html_e( 'Cuisine', 'lsx-wetu-importer' ); ?></li>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="climate"/> <?php esc_html_e( 'Climate', 'lsx-wetu-importer' ); ?></li>
								<li>
									<input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="transport"/> <?php esc_html_e( 'Transport', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="dress"/> <?php esc_html_e( 'Dress', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="health"/> <?php esc_html_e( 'Health', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="safety"/> <?php esc_html_e( 'Safety', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="visa"/> <?php esc_html_e( 'Visa', 'lsx-wetu-importer' ); ?></li>
								<li><input class="content" checked="checked"
										   type="checkbox" name="content[]"
										   value="additional_info"/> <?php esc_html_e( 'General', 'lsx-wetu-importer' ); ?></li>
							</ul>
						</div>

						<?php if ( class_exists( 'LSX_TO_Team' ) ) { ?>
							<div style="width:30%;display:block;float:left;">
								<h3><?php esc_html_e( 'Assign a Team Member' ); ?></h3>
								<?php $this->team_member_checkboxes( $this->destination_options ); ?>
							</div>
						<?php } ?>

						<br clear="both"/>
					</div>


					<h3><?php esc_html_e( 'Your List' ); ?></h3>
					<p><input class="button button-primary" type="submit"
							  value="<?php esc_html_e( 'Sync', 'lsx-wetu-importer' ); ?>"/></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit"
							  value="<?php esc_html_e( 'Sync', 'lsx-wetu-importer' ); ?>"/></p>
				</form>
			</div>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php esc_html_e( 'Completed' ); ?></h3>
				<ul>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Grab all the current destination posts via the lsx_wetu_id field.
	 */
	public function find_current_destination( $post_type = 'destination' ) {
		global $wpdb;
		$return = array();

		// @codingStandardsIgnoreStart
		$current_destination = $wpdb->get_results("
			SELECT key1.post_id,key1.meta_value,key2.post_title as name,key2.post_date as last_modified
			FROM {$wpdb->postmeta} key1

			INNER JOIN  {$wpdb->posts} key2
			ON key1.post_id = key2.ID

			WHERE key1.meta_key = 'lsx_wetu_id'
			AND key2.post_type = '{$post_type}'

			LIMIT 0,1000
		");
		// @codingStandardsIgnoreEnd

		if ( null !== $current_destination && ! empty( $current_destination ) ) {
			foreach ( $current_destination as $accom ) {
				$return[ $accom->meta_value ] = $accom;
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
		if ( isset( $_POST['action'] ) && 'lsx_tour_importer' === $_POST['action'] && isset( $_POST['type'] ) && 'destination' === $_POST['type'] ) {

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

			// If there is a post status use it.
			if ( false !== $post_status ) {

				$accommodation         = array();
				$current_accommodation = $this->find_current_accommodation( 'destination' );
				if ( ! empty( $current_accommodation ) ) {
					foreach ( $current_accommodation as $cs_key => $ccs_id ) {
						$accommodation[] = $this->prepare_row_attributes( $cs_key, $ccs_id->post_id );
					}
				}

				// Run through each accommodation and use it.
				if ( ! empty( $accommodation ) ) {
					foreach ( $accommodation as $row_key => $row ) {
						$row['post_title'] = $row['name'];
						if ( 'import' === $post_status ) {
							if ( is_array( $this->queued_imports ) && in_array( $row['post_id'], $this->queued_imports ) ) {
								$current_status = get_post_status( $row['post_id'] );
								if ( 'draft' === $current_status ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row, $row_key );
								}
							} else {
								continue;
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
							$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row, $row_key );
						}
					}
				}
			} else {

				$key_string_search = implode( '+', $keyphrases );
				$search_data       = wp_remote_get( $this->url . '/Search/' . $key_string_search . '/?all=include' );

				if ( ! is_wp_error( $search_data ) || ! empty( $search_data ) && isset( $search_data['response'] ) && isset( $search_data['response']['code'] ) && 200 === $search_data['response']['code'] ) {
					$search_data = json_decode( $search_data['body'], true );
					foreach ( $search_data as $sdata_key => $sdata ) {

						if ( isset( $sdata['type'] ) && 'Destination' !== trim( $sdata['type'] ) && 'Area' !== trim( $sdata['type'] ) ) {
							continue;
						}

						$temp_id = $this->get_post_id_by_key_value( $sdata['id'] );
						if ( false === $temp_id ) {
							$sdata['post_id']    = 0;
							$sdata['post_title'] = $sdata['name'];
						} else {
							$sdata['post_id']    = $temp_id;
							$sdata['post_title'] = get_the_title( $temp_id );
						}
						$searched_items[ sanitize_title( $sdata['name'] ) . '-' . $sdata['id'] ] = $this->format_row( $sdata, $sdata_key );
					}
				}
			}

			if ( false !== $searched_items ) {
				$return = implode( $searched_items );
			}
			print_r( $return );
		} else {
			echo esc_attr( 'None found' );
		}
		die();
	}

	public function prepare_row_attributes( $cs_key, $ccs_id ) {
		$row_item = array(
			'id'            => $cs_key,
			'type'          => 'Destination',
			'name'          => get_the_title( $ccs_id ),
			'last_modified' => date( 'Y-m-d', strtotime( 'now' ) ),
			'post_id'       => $ccs_id,
		);
		return $row_item;
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
				<th class="check-column" scope="row">
					<label for="cb-select-' . $row['id'] . '" class="screen-reader-text">' . $row['name'] . '</label>
					<input type="checkbox" data-identifier="' . $row['id'] . '" value="' . $row['post_id'] . '" name="post[]" id="cb-select-' . $row['id'] . '">
				</th>
				<td class="column-order">
					' . ( $row_key + 1 ) . '
				</td>
				<td class="post-title page-title column-title">
					<strong>' . $row['post_title'] . '</strong> - ' . $status . '
				</td>
				<td class="date column-date">
					<abbr title="' . date( 'Y/m/d', strtotime( $row['last_modified'] ) ) . '">' . date( 'Y/m/d', strtotime( $row['last_modified'] ) ) . '</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					' . $row['id'] . '
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

		check_ajax_referer( 'lsx_wetu_ajax_action', 'security' );
		if ( isset( $_POST['action'] ) && 'lsx_import_items' === $_POST['action'] && isset( $_POST['type'] ) && 'destination' === $_POST['type'] && isset( $_POST['wetu_id'] ) ) {

			$wetu_id = sanitize_text_field( $_POST['wetu_id'] );

			if ( isset( $_POST['post_id'] ) ) {
				$post_id            = sanitize_text_field( $_POST['post_id'] );
				$this->current_post = get_post( $post_id );
			} else {
				$post_id = 0;
			}

			if ( isset( $_POST['team_members'] ) ) {
				$team_members = array_map( 'sanitize_text_field', wp_unslash( $_POST['team_members'] ) );
			} else {
				$team_members = false;
			}

			$safari_brands = false;

			delete_option( 'lsx_wetu_importer_destination_settings' );

			if ( isset( $_POST['content'] ) && is_array( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
				$content = array_map( 'sanitize_text_field', wp_unslash( $_POST['content'] ) );
				add_option( 'lsx_wetu_importer_destination_settings', $content );
			} else {
				$content = false;
			}

			$jdata = wp_remote_get( $this->url . '/Get?' . $this->url_qs . '&ids=' . $wetu_id );

			if ( ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && 200 === $jdata['response']['code'] ) {
				$adata  = json_decode( $jdata['body'], true );
				$return = $this->import_row( $adata, $wetu_id, $post_id, $team_members, $content, $safari_brands );
				$this->remove_from_queue( $return );
				$this->format_completed_row( $return );
			} else {
				$this->format_error( esc_html__( 'There was a problem importing your destination, please try refreshing the page.', 'lsx-wetu-importer' ) );
			}
		}
		die();
	}

	/**
	 * Saves the queue to the option.
	 */
	public function remove_from_queue( $id ) {
		if ( ! empty( $this->queued_imports ) ) {
			$key = array_search( $id, $this->queued_imports );
			if ( false !== $key ) {
				unset( $this->queued_imports[ $key ] );

				delete_option( 'lsx_wetu_importer_que' );
				update_option( 'lsx_wetu_importer_que', $this->queued_imports );
			}
		}
	}

	/**
	 * Connect to wetu
	 */
	public function import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = array(), $safari_brands = false ) {
		if ( 'Destination' === trim( $data[0]['type'] ) || 'Area' === trim( $data[0]['type'] ) ) {
			$post_name         = '';
			$data_post_content = '';
			$data_post_excerpt = '';

			$post = array(
				'post_type' => 'destination',
			);

			if ( ! empty( $importable_content ) && in_array( 'country', $importable_content ) ) {
				$parent = $this->check_for_parent( $data );
				if ( false !== $parent ) {
					$post['post_parent'] = $parent;
				}
			}

			// Set the post_content.
			if ( ! empty( $importable_content ) && in_array( 'description', $importable_content ) ) {
				if ( isset( $data[0]['content']['general_description'] ) ) {

					if ( in_array( 'strip_tags', $importable_content ) ) {
						$post['post_content'] = wp_strip_all_tags( $data[0]['content']['general_description'] );
					} else {
						$post['post_content'] = $data[0]['content']['general_description'];
					}
				}
			}

			if ( false !== $id && '0' !== $id ) {
				$post['ID'] = $id;
				if ( isset( $this->options ) && 'on' !== $this->options['disable_destination_title'] && isset( $data[0]['name'] ) ) {
					$post['post_title'] = $data[0]['name'];
					$post['post_name']  = wp_unique_post_slug( sanitize_title( $data[0]['name'] ), $id, 'draft', 'destination', 0 );
				}
				$post['post_status'] = 'publish';

				$id        = wp_update_post( $post );
				$prev_date = get_post_meta( $id, 'lsx_wetu_modified_date', true );
				update_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data[0]['last_modified'] ), $prev_date );
			} else {
				// Set the name.
				if ( isset( $data[0]['name'] ) ) {
					$post_name = wp_unique_post_slug( sanitize_title( $data[0]['name'] ), $id, 'draft', 'destination', 0 );
				}

				$post['post_name']   = $post_name;
				$post['post_title']  = $data[0]['name'];
				$post['post_status'] = 'publish';
				$id                  = wp_insert_post( $post );

				// Save the WETU ID and the Last date it was modified.
				if ( false !== $id ) {
					add_post_meta( $id, 'lsx_wetu_id', $wetu_id );
					add_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data[0]['last_modified'] ) );
				}
			}

			$this->find_attachments( $id );

			// Set the team member if it is there.
			if ( post_type_exists( 'team' ) && false !== $team_members && '' !== $team_members ) {
				$this->set_team_member( $id, $team_members );
			}

			$this->set_map_data( $data, $id, 9 );

			// Set the Room Data.
			if ( false !== $importable_content && in_array( 'videos', $importable_content ) ) {
				$this->set_video_data( $data, $id );
			}

			// Set the Electricity.
			if ( false !== $importable_content && in_array( 'electricity', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'electricity', $importable_content );
			}
			// Set the cuisine.
			if ( false !== $importable_content && in_array( 'cuisine', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'cuisine', $importable_content );
			}
			// Set the banking.
			if ( false !== $importable_content && in_array( 'banking', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'banking', $importable_content );
			}
			// Set the transport.
			if ( false !== $importable_content && in_array( 'transport', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'transport', $importable_content );
			}
			// Set the dress.
			if ( false !== $importable_content && in_array( 'dress', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'dress', $importable_content );
			}
			// Set the climate.
			if ( false !== $importable_content && in_array( 'climate', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'climate', $importable_content );
			}
			// Set the Health.
			if ( false !== $importable_content && in_array( 'health', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'health', $importable_content );
			}
			// Set the Safety.
			if ( false !== $importable_content && in_array( 'safety', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'safety', $importable_content );
			}
			// Set the Visa.
			if ( false !== $importable_content && in_array( 'visa', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'visa', $importable_content );
			}
			// Set the General.
			if ( false !== $importable_content && in_array( 'additional_info', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'additional_info', $importable_content );
			}

			// Setup some default for use in the import.
			if ( false !== $importable_content && ( in_array( 'gallery', $importable_content ) || in_array( 'banner_image', $importable_content ) || in_array( 'featured_image', $importable_content ) ) ) {
				$this->find_attachments( $id );

				// Set the featured image.
				if ( false !== $importable_content && in_array( 'featured_image', $importable_content ) ) {
					$this->set_featured_image( $data, $id );
				}
				if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
					$this->set_banner_image( $data, $id, $importable_content );
				}
				// Import the main gallery.
				if ( false !== $importable_content && in_array( 'gallery', $importable_content ) ) {
					$this->create_main_gallery( $data, $id );
				}
			}

			// Set the continent.
			if ( false !== $importable_content && in_array( 'continent', $importable_content ) ) {
				$this->set_continent( $data, $id );
			}
		}

		return $id;
	}

	/**
	 * Saves the room data
	 */
	public function set_travel_info( $data, $id, $meta_key, $importable = array( 'none' ) ) {
		if ( ! empty( $data[0]['travel_information'] ) && isset( $data[0]['travel_information'][ $meta_key ] ) ) {
			$content = $data[0]['travel_information'][ $meta_key ];

			if ( in_array( 'strip_tags', $importable ) ) {
				$content = strip_tags( $content );
			}

			$this->save_custom_field( $content, $meta_key, $id );
		}
	}

	/**
	 * Set the Travel Style
	 */
	public function set_continent( $data, $id ) {

		if ( isset( $data[0]['position']['country'] ) && $data[0]['map_object_id'] === $data[0]['position']['country_content_entity_id'] ) {
			// Get the continent code.
			$country_code    = to_country_data( $data[0]['position']['country'], false );
			$continent_code  = to_continent_code( $country_code );
			$continent_label = to_continent_label( $continent_code );

			if ( ! empty( tour_operator()->options['display']['enable_search_region_filter'] ) ) {
				$continent_label = to_continent_region_label( $country_code );
			}

			if ( '' !== $continent_label ) {
				$term = term_exists( trim( $continent_label ), 'continent' );
				if ( ! $term ) {
					$term = wp_insert_term( trim( $continent_label ), 'continent' );

					if ( is_wp_error( $term ) ) {
						echo wp_kses_post( $term->get_error_message() );
					}
				} else {
					wp_set_object_terms( $id, sanitize_title( $continent_label ), 'continent', true );
				}
			}
		}
	}

	/**
	 * Save the list of Accommodation into an option
	 */
	public function check_for_parent( $data = array() ) {
		global $wpdb;

		if ( $data[0]['position']['country_content_entity_id'] !== $data[0]['position']['destination_content_entity_id'] ) {
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lsx_wetu_id' AND meta_value = '%s'", array( $data[0]['position']['country_content_entity_id'] ) ) );
			if ( ! empty( $result ) && '' !== $result && false !== $result ) {
				return $result;
			}
		}
		return false;
	}
}
