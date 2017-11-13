<?php
/**
 * @package   WETU_Importer_Destination
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Destination extends WETU_Importer {

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
		// ** This request only works with API KEY **
		//if ( false !== $this->api_username && false !== $this->api_password ) {
		//	$this->url    = 'https://wetu.com/API/Pins/';
		//	$this->url_qs = 'username=' . $this->api_username . '&password=' . $this->api_password;
		//} elseif ( false !== $this->api_key ) {
		$this->url = 'https://wetu.com/API/Pins/' . $this->api_key;
		$this->url_qs = 'all=include';
		//}

		$temp_options = get_option( '_lsx-to_settings', false );

		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) && ! empty( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];
		}

		$destination_options = get_option( 'wetu_importer_destination_settings', false );

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
			<?php $this->navigation( 'destination' ); ?>

			<?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>"/>

				<p><input class="button button-primary add" type="button"
						  value="<?php esc_html_e( 'Add to List', 'wetu-importer' ); ?>"/>
					<input class="button button-primary clear" type="button"
						   value="<?php esc_html_e( 'Clear', 'wetu-importer' ); ?>"/>
				</p>

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody id="the-list">
					<tr class="post-0 type-tour status-none" id="post-0">
						<th class="check-column" scope="row">
							<label for="cb-select-0"
								   class="screen-reader-text"><?php esc_html_e( 'Enter a title to search for and press enter', 'wetu-importer' ); ?></label>
						</th>
						<td class="post-title page-title column-title">
							<strong>
								<?php esc_html_e( 'Enter a title to search for', 'wetu-importer' ); ?>
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
						  value="<?php esc_html_e( 'Add to List', 'wetu-importer' ); ?>"/>
					<input class="button button-primary clear" type="button"
						   value="<?php esc_html_e( 'Clear', 'wetu-importer' ); ?>"/>
				</p>
			</form>

			<div style="display:none;" class="import-list-wrapper">
				<br/>
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'What content to Sync from WETU' ); ?></h3>
							<ul>
								<li>
									<input class="content select-all" <?php $this->checked( $this->destination_options, 'all' ); ?>
										   type="checkbox" name="content[]"
										   value="all"/> <?php esc_html_e( 'Select All', 'wetu-importer' ); ?></li>
								<?php if ( isset( $this->options ) && 'on' !== $this->options['disable_destination_descriptions'] ) { ?>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'description' ); ?>
										   type="checkbox" name="content[]"
										   value="description"/> <?php esc_html_e( 'Description', 'wetu-importer' ); ?></li>
								<?php } ?>

								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'gallery' ); ?>
										   type="checkbox" name="content[]"
										   value="gallery"/> <?php esc_html_e( 'Main Gallery', 'wetu-importer' ); ?></li>
								<?php if ( class_exists( 'LSX_TO_Maps' ) ) { ?>
									<li>
										<input class="content" <?php $this->checked( $this->destination_options, 'location' ); ?>
											   type="checkbox" name="content[]"
											   value="location"/> <?php esc_html_e( 'Location', 'wetu-importer' ); ?></li>
								<?php } ?>

								<?php if ( class_exists( 'LSX_TO_Videos' ) ) { ?>
									<li>
										<input class="content" <?php $this->checked( $this->destination_options, 'videos' ); ?>
											   type="checkbox" name="content[]"
											   value="videos"/> <?php esc_html_e( 'Videos', 'wetu-importer' ); ?></li>
								<?php } ?>

							</ul>
							<h4><?php esc_html_e( 'Additional Content' ); ?></h4>
							<ul>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'country' ); ?>
										   type="checkbox" name="content[]"
										   value="country"/> <?php esc_html_e( 'Set Country', 'wetu-importer' ); ?></li>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'continent' ); ?>
										   type="checkbox" name="content[]"
										   value="continent"/> <?php esc_html_e( 'Set Continent', 'wetu-importer' ); ?></li>

								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'featured_image' ); ?>
										   type="checkbox" name="content[]"
										   value="featured_image"/> <?php esc_html_e( 'Set Featured Image', 'wetu-importer' ); ?>
								</li>
								<?php if ( class_exists( 'LSX_Banners' ) ) { ?>
									<li>
										<input class="content" <?php $this->checked( $this->destination_options, 'banner_image' ); ?>
											   type="checkbox" name="content[]"
											   value="banner_image"/> <?php esc_html_e( 'Set Banner Image', 'wetu-importer' ); ?>
									</li>
									<li>
										<input class="content" <?php $this->checked( $this->destination_options, 'unique_banner_image' ); ?>
											   type="checkbox" name="content[]"
											   value="unique_banner_image"/> <?php esc_html_e( 'Use the WETU banner field', 'wetu-importer' ); ?>
									</li>
								<?php } ?>

								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'strip_tags' ); ?>
										   type="checkbox" name="content[]"
										   value="strip_tags"/> <?php esc_html_e( 'Strip HTML from the description', 'wetu-importer' ); ?></li>
							</ul>
						</div>
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Travel Information' ); ?></h3>
							<ul>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'electricity' ); ?>
										   type="checkbox" name="content[]"
										   value="electricity"/> <?php esc_html_e( 'Electricity', 'wetu-importer' ); ?></li>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'banking' ); ?>
										   type="checkbox" name="content[]"
										   value="banking"/> <?php esc_html_e( 'Banking', 'wetu-importer' ); ?></li>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'cuisine' ); ?>
										   type="checkbox" name="content[]"
										   value="cuisine"/> <?php esc_html_e( 'Cuisine', 'wetu-importer' ); ?></li>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'climate' ); ?>
										   type="checkbox" name="content[]"
										   value="climate"/> <?php esc_html_e( 'Climate', 'wetu-importer' ); ?></li>
								<li>
									<input class="content" <?php $this->checked( $this->destination_options, 'transport' ); ?>
										   type="checkbox" name="content[]"
										   value="transport"/> <?php esc_html_e( 'Transport', 'wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->destination_options, 'dress' ); ?>
										   type="checkbox" name="content[]"
										   value="dress"/> <?php esc_html_e( 'Dress', 'wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->destination_options, 'health' ); ?>
										   type="checkbox" name="content[]"
										   value="health"/> <?php esc_html_e( 'Health', 'wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->destination_options, 'safety' ); ?>
										   type="checkbox" name="content[]"
										   value="safety"/> <?php esc_html_e( 'Safety', 'wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->destination_options, 'visa' ); ?>
										   type="checkbox" name="content[]"
										   value="visa"/> <?php esc_html_e( 'Visa', 'wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->destination_options, 'additional_info' ); ?>
										   type="checkbox" name="content[]"
										   value="additional_info"/> <?php esc_html_e( 'General', 'wetu-importer' ); ?></li>
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
							  value="<?php esc_html_e( 'Sync', 'wetu-importer' ); ?>"/></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit"
							  value="<?php esc_html_e( 'Sync', 'wetu-importer' ); ?>"/></p>
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

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_tour_importer' && isset( $_POST['type'] ) && $_POST['type'] === 'destination' ) {
			$accommodation = get_transient( 'lsx_ti_accommodation' );

			if ( false === $accommodation ) {
				$this->update_options();
			}

			if ( false !== $accommodation ) {
				$searched_items = false;

				// @codingStandardsIgnoreLine
				if ( isset( $_POST['keyword'] ) ) {
					// @codingStandardsIgnoreLine
					$keyphrases = $_POST['keyword'];
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

				if ( in_array( 'publish',$keyphrases ) ) {
					$post_status = 'publish';
				}
				if ( in_array( 'pending',$keyphrases ) ) {
					$post_status = 'pending';
				}
				if ( in_array( 'draft',$keyphrases ) ) {
					$post_status = 'draft';
				}
				if ( in_array( 'import',$keyphrases ) ) {
					$post_status = 'import';
				}

				if ( ! empty( $accommodation ) ) {

					$current_accommodation = $this->find_current_accommodation( 'destination' );

					foreach ( $accommodation as $row_key => $row ) {

						if ( 'Destination' === trim( $row['type'] ) ) {

							//If this is a current tour, add its ID to the row.
							$row['post_id'] = 0;

							if ( false !== $current_accommodation && array_key_exists( $row['id'], $current_accommodation ) ) {
								$row['post_id'] = $current_accommodation[ $row['id'] ]->post_id;
							}

							//If we are searching for
							if ( false !== $post_status ) {
								if ( 'import' === $post_status ) {

									if ( is_array( $this->queued_imports ) && in_array( $row['post_id'], $this->queued_imports ) ) {
										$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row );
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

									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row );
								}
							} else {
								//Search through each keyword.
								foreach ( $keyphrases as $keyphrase ) {
									//Make sure the keyphrase is turned into an array
									$keywords = explode( ' ', $keyphrase );

									if ( ! is_array( $keywords ) ) {
										$keywords = array( $keywords );
									}

									if ( $this->multineedle_stripos( ltrim( rtrim( $row['name'] ) ), $keywords ) !== false ) {
										$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row );
									}
								}
							}
						}// end of the destination if
					}
				}

				if ( false !== $searched_items ) {
					ksort( $searched_items );
					$return = implode( $searched_items );
				}
			}

			print_r( $return );
		}

		die();
	}

	/**
	 * Formats the row for output on the screen.
	 */
	public function format_row( $row = false ) {
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
				<td class="post-title page-title column-title">
					<strong>' . $row['name'] . '</strong> - ' . $status . '
				</td>
				<td class="date column-date">
					<abbr title="' . date( 'Y/m/d',strtotime( $row['last_modified'] ) ) . '">' . date( 'Y/m/d',strtotime( $row['last_modified'] ) ) . '</abbr><br>Last Modified
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

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_import_items' && isset( $_POST['type'] ) && $_POST['type'] === 'destination' && isset( $_POST['wetu_id'] ) ) {
			// @codingStandardsIgnoreLine
			$wetu_id = $_POST['wetu_id'];

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['post_id'] ) ) {
				// @codingStandardsIgnoreLine
				$post_id = $_POST['post_id'];
				$this->current_post = get_post( $post_id );
			} else {
				$post_id = 0;
			}

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['team_members'] ) ) {
				// @codingStandardsIgnoreLine
				$team_members = $_POST['team_members'];
			} else {
				$team_members = false;
			}

			$safari_brands = false;

			delete_option( 'wetu_importer_destination_settings' );

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['content'] ) && is_array( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
				// @codingStandardsIgnoreLine
				$content = $_POST['content'];
				add_option( 'wetu_importer_destination_settings', $content );
			} else {
				$content = false;
			}

			$jdata = file_get_contents( $this->url . '/Get?' . $this->url_qs . '&ids=' . $wetu_id );

			if ( $jdata ) {
				$adata = json_decode( $jdata, true );

				if ( ! empty( $adata ) && ! isset( $adata['error'] ) ) {
					$return = $this->import_row( $adata, $wetu_id, $post_id, $team_members, $content, $safari_brands );
					$this->remove_from_queue( $return );
					$this->format_completed_row( $return );
				} else {
					if ( isset( $adata['error'] ) ) {
						$this->format_error( $adata['error'] );
					} else {
						$this->format_error( esc_html__( 'There was a problem importing your destination, please try refreshing the page.','wetu-importer' ) );
					}
				}
			}
		}
	}

	/**
	 * Saves the queue to the option.
	 */
	public function remove_from_queue( $id ) {
		if ( ! empty( $this->queued_imports ) ) {
			// @codingStandardsIgnoreLine
			if ( ( $key = array_search( $id, $this->queued_imports ) ) !== false ) {
				unset( $this->queued_imports[ $key ] );

				delete_option( 'wetu_importer_que' );
				update_option( 'wetu_importer_que',$this->queued_imports );
			}
		}
	}

	/**
	 * Connect to wetu
	 */
	public function import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = false, $safari_brands = false ) {
		if ( 'Destination' === trim( $data[0]['type'] ) ) {
			$post_name = '';
			$data_post_content = '';
			$data_post_excerpt = '';

			$post = array(
				'post_type' => 'destination',
			);

			if ( false !== $importable_content && in_array( 'country', $importable_content ) ) {
				$parent = $this->check_for_parent();
				if( false !== $parent ) {
					//$post['post_parent'] = $parent;
				}
			}

			//Set the post_content
			if ( false !== $importable_content && in_array( 'description', $importable_content ) ) {
				if ( isset( $data[0]['content']['general_description'] ) ) {

					if ( false !== $importable_content && in_array( 'strip_tags', $importable_content ) ) {
						$post['post_content'] = wp_strip_all_tags( $data[0]['content']['general_description'] );
					} else {
						$post['post_content'] = $data[0]['content']['general_description'];
					}
				}
			}

			if ( false !== $id && '0' !== $id ) {
				$post['ID'] = $id;

				if ( isset( $data[0]['name'] ) ) {
					$post['post_title'] = $data[0]['name'];
					$post['post_status'] = 'publish';
					$post['post_name'] = wp_unique_post_slug( sanitize_title( $data[0]['name'] ), $id, 'draft', 'destination', 0 );
				}

				$id = wp_update_post( $post );
				$prev_date = get_post_meta( $id, 'lsx_wetu_modified_date', true );
				update_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data[0]['last_modified'] ), $prev_date );
			} else {
				//Set the name
				if ( isset( $data[0]['name'] ) ) {
					$post_name = wp_unique_post_slug( sanitize_title( $data[0]['name'] ), $id, 'draft', 'destination', 0 );
				}

				$post['post_name'] = $post_name;
				$post['post_title'] = $data[0]['name'];
				$post['post_status'] = 'publish';
				$id = wp_insert_post( $post );

				//Save the WETU ID and the Last date it was modified.
				if ( false !== $id ) {
					add_post_meta( $id, 'lsx_wetu_id', $wetu_id );
					add_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data[0]['last_modified'] ) );
				}
			}

			$this->find_attachments( $id );

			//Set the team member if it is there
			if ( post_type_exists( 'team' ) && false !== $team_members && '' !== $team_members ) {
				$this->set_team_member( $id, $team_members );
			}

			if ( class_exists( 'LSX_TO_Maps' ) ) {
				$this->set_map_data( $data, $id, 9 );
			}

			//Set the Room Data
			if ( false !== $importable_content && in_array( 'videos', $importable_content ) ) {
				$this->set_video_data( $data, $id );
			}

			//Set the Electricity
			if ( false !== $importable_content && in_array( 'electricity', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'electricity', $importable_content );
			}
			//Set the cuisine
			if ( false !== $importable_content && in_array( 'cuisine', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'cuisine', $importable_content );
			}
			//Set the banking
			if ( false !== $importable_content && in_array( 'banking', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'banking', $importable_content );
			}
			//Set the transport
			if ( false !== $importable_content && in_array( 'transport', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'transport', $importable_content );
			}
			//Set the dress
			if ( false !== $importable_content && in_array( 'dress', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'dress', $importable_content );
			}
			//Set the climate
			if ( false !== $importable_content && in_array( 'climate', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'climate', $importable_content );
			}
			//Set the Health
			if ( false !== $importable_content && in_array( 'health', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'health', $importable_content );
			}
			//Set the Safety
			if ( false !== $importable_content && in_array( 'safety', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'safety', $importable_content );
			}
			//Set the Visa
			if ( false !== $importable_content && in_array( 'visa', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'visa', $importable_content );
			}
			//Set the General
			if ( false !== $importable_content && in_array( 'additional_info', $importable_content ) ) {
				$this->set_travel_info( $data, $id, 'additional_info', $importable_content );
			}

			//Setup some default for use in the import
			if ( false !== $importable_content && (in_array( 'gallery', $importable_content ) || in_array( 'banner_image', $importable_content ) || in_array( 'featured_image', $importable_content )) ) {
				$this->find_attachments( $id );

				//Set the featured image
				if ( false !== $importable_content && in_array( 'featured_image', $importable_content ) ) {
					$this->set_featured_image( $data, $id );
				}
				if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
					$this->set_banner_image( $data, $id, $importable_content );
				}
				//Import the main gallery
				if ( false !== $importable_content && in_array( 'gallery', $importable_content ) ) {
					$this->create_main_gallery( $data, $id );
				}
			}

			//Set the continent
			if ( false !== $importable_content && in_array( 'continent', $importable_content ) ) {
				$this->set_continent( $data, $id );
			}
		}

		return $id;
	}

	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member( $id, $team_members ) {
		delete_post_meta( $id, 'team_to_' . $this->tab_slug );

		foreach ( $team_members as $team ) {
			add_post_meta( $id, 'team_to_' . $this->tab_slug, $team );
		}
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
			//get the continent code.
			$continent_code = to_continent_label( to_continent_code( to_country_data( $data[0]['position']['country'], false ) ) );

			if ( '' !== $continent_code ) {
				// @codingStandardsIgnoreLine
				if ( ! $term = term_exists( trim( $continent_code ), 'continent' ) ) {
					$term = wp_insert_term( trim( $continent_code ), 'continent' );

					if ( is_wp_error( $term ) ) {
						// @codingStandardsIgnoreLine
						echo $term->get_error_message();
					}
				} else {
					wp_set_object_terms( $id, sanitize_title( $continent_code ), 'continent', true );
				}
			}
		}
	}

	/**
	 * search_form
	 */
	public function update_options_form() {
		echo '<div style="display:none;" class="wetu-status"><h3>' . esc_html__( 'Wetu Status', 'wetu-importer' ) . '</h3>';

		$accommodation = get_transient( 'lsx_ti_accommodation' );

		if ( '' === $accommodation || false === $accommodation || isset( $_GET['refresh_accommodation'] ) ) {
			$this->update_options();
		}

		echo '</div>';
	}

	/**
	 * Save the list of Accommodation into an option
	 */
	public function check_for_parent( $wid = 0 ) {
		global $wpdb;

		$query = "
		SELECT post_id
		FROM {$wpdb->postmeta}
		WHERE meta_key = 'lsx_wetu_id'
		AND meta_valule = {$wid}";

		print_r( $query );

		$result = $wpdb->get_var( $query );

		if( ! empty( $result ) && '' !== $result && false !== $result ) {
			return $result;
		} else {
			return false;
		}
	}
}
