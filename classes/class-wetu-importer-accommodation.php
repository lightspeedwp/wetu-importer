<?php
/**
 * @package   WETU_Importer_Accommodation
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Accommodation extends WETU_Importer {

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
	public $accommodation_options = false;

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
			$this->url    = 'https://wetu.com/API/Pins/' . $this->api_key;
			$this->url_qs = 'all=include';
		//}

		$temp_options = get_option( '_lsx-to_settings', false );

		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) && ! empty( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];
		}

		$accommodation_options = get_option( 'wetu_importer_accommodation_settings',false );

		if ( false !== $accommodation_options ) {
			$this->accommodation_options = $accommodation_options;
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<?php $this->navigation( 'accommodation' ); ?>

			<?php $this->update_options_form(); ?>

			<?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

				<p><input class="button button-primary add" type="button" value="<?php esc_attr_e( 'Add to List', 'wetu-importer' ); ?>" />
					<input class="button button-primary clear" type="button" value="<?php esc_attr_e( 'Clear', 'wetu-importer' ); ?>" />
				</p>

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php esc_html_e( 'Enter a title to search for and press enter', 'wetu-importer' ); ?></label>
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

				<p><input class="button button-primary add" type="button" value="<?php esc_attr_e( 'Add to List', 'wetu-importer' ); ?>" />
					<input class="button button-primary clear" type="button" value="<?php esc_attr_e( 'Clear', 'wetu-importer' ); ?>" />
				</p>
			</form>

			<div style="display:none;" class="import-list-wrapper">
				<br />
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'What content to Sync from WETU' ); ?></h3>
							<ul>
								<li><input class="content select-all" <?php $this->checked( $this->accommodation_options, 'all' ); ?> type="checkbox" name="content[]" value="all" /> <?php esc_html_e( 'Select All', 'wetu-importer' ); ?></li>
								<?php if ( ! isset( $this->options['disable_accommodation_descriptions'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'description' ); ?>" type="checkbox" name="content[]" value="description" /> <?php esc_html_e( 'Description', 'wetu-importer' ); ?></li>
								<?php } ?>
								<?php if ( ! isset( $this->options['disable_accommodation_excerpts'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'excerpt' ); ?>" type="checkbox" name="content[]" value="excerpt" /> <?php esc_html_e( 'Excerpt', 'wetu-importer' ); ?></li>
								<?php } ?>

								<?php if ( ! isset( $this->accommodation_settings['disable_single'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'gallery' ); ?>" type="checkbox" name="content[]" value="gallery" /> <?php esc_html_e( 'Main Gallery', 'wetu-importer' ); ?></li>
								<?php } ?>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'category' ); ?>" type="checkbox" name="content[]" value="category" /> <?php esc_html_e( 'Category', 'wetu-importer' ); ?></li>
								<?php if ( class_exists( 'LSX_TO_Maps' ) && ! isset( $this->accommodation_settings['disable_single'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'location' ); ?>" type="checkbox" name="content[]" value="location" /> <?php esc_html_e( 'Location', 'wetu-importer' ); ?></li>
								<?php } ?>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'destination' ); ?>" type="checkbox" name="content[]" value="destination" /> <?php esc_html_e( 'Connect Destinations', 'wetu-importer' ); ?></li>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'rating' ); ?>" type="checkbox" name="content[]" value="rating" /> <?php esc_html_e( 'Rating', 'wetu-importer' ); ?></li>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'rooms' ); ?>" type="checkbox" name="content[]" value="rooms" /> <?php esc_html_e( 'Rooms', 'wetu-importer' ); ?></li>

								<?php if ( ! isset( $this->accommodation_settings['disable_single'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'checkin' ); ?>" type="checkbox" name="content[]" value="checkin" /> <?php esc_html_e( 'Check In / Check Out', 'wetu-importer' ); ?></li>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'facilities' ); ?>" type="checkbox" name="content[]" value="facilities" /> <?php esc_html_e( 'Facilities', 'wetu-importer' ); ?></li>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'friendly' ); ?>" type="checkbox" name="content[]" value="friendly" /> <?php esc_html_e( 'Friendly', 'wetu-importer' ); ?></li>
								<?php } ?>

								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'special_interests' ); ?>" type="checkbox" name="content[]" value="special_interests" /> <?php esc_html_e( 'Special Interests', 'wetu-importer' ); ?></li>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'spoken_languages' ); ?>" type="checkbox" name="content[]" value="spoken_languages" /> <?php esc_html_e( 'Spoken Languages', 'wetu-importer' ); ?></li>

								<?php if ( class_exists( 'LSX_TO_Videos' ) && ! isset( $this->accommodation_settings['disable_single'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'videos' ); ?>" type="checkbox" name="content[]" value="videos" /> <?php esc_html_e( 'Videos', 'wetu-importer' ); ?></li>
								<?php } ?>
							</ul>
							<h4><?php esc_html_e( 'Additional Content' ); ?></h4>
							<ul>
								<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'featured_image' ); ?>" type="checkbox" name="content[]" value="featured_image" /> <?php esc_html_e( 'Set Featured Image', 'wetu-importer' ); ?></li>
								<?php if ( class_exists( 'LSX_Banners' ) && ! isset( $this->accommodation_settings['disable_single'] ) ) { ?>
									<li><input class="content" checked="<?php $this->checked( $this->accommodation_options,'banner_image' ); ?>" type="checkbox" name="content[]" value="banner_image" /> <?php esc_html_e( 'Set Banner Image', 'wetu-importer' ); ?></li>
								<?php } ?>
							</ul>
						</div>
						<div style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Assign a Team Member' ); ?></h3>
							<?php $this->team_member_checkboxes( $this->accommodation_options ); ?>
						</div>

						<div style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Assign a Safari Brand' ); ?></h3>
							<?php
								// @codingStandardsIgnoreLine
								echo $this->taxonomy_checkboxes( 'accommodation-brand', $this->accommodation_options );
							?>
						</div>

						<br clear="both" />
					</div>

					<h3><?php esc_html_e( 'Your List' ); ?></h3>
					<p><input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Sync', 'wetu-importer' ); ?>" /></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Sync', 'wetu-importer' ); ?>" /></p>
				</form>
			</div>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php esc_html_e( 'Completed' ); ?> - <small><?php esc_html_e( 'Import your', 'wetu-importer' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=destination"><?php esc_html_e( 'destinations' ); ?></a> <?php esc_html_e( 'next', 'wetu-importer' ); ?></small></h3>
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
	public function update_options() {
		$data = file_get_contents( $this->url . '/List?' . $this->url_qs );

		$accommodation = json_decode( $data, true );

		if ( isset( $accommodation['error'] ) ) {
			return $accommodation['error'];
		} elseif ( isset( $accommodation ) && ! empty( $accommodation ) ) {
			set_transient( 'lsx_ti_accommodation',$accommodation,60 * 60 * 2 );
			return true;
		}
	}

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_tour_importer' && isset( $_POST['type'] ) && $_POST['type'] === 'accommodation' ) {
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
					$current_accommodation = $this->find_current_accommodation();

					foreach ( $accommodation as $row_key => $row ) {

						if ( 'Destination' === trim( $row['type'] ) ||
							 'Activity' === trim( $row['type'] ) ||
							 'Restaurant' === trim( $row['type'] ) ||
							 'None' === trim( $row['type'] ) ||
							 'Site / Attraction' === trim( $row['type'] ) ||
							 '' === trim( $row['type'] )
						) {
							continue;
						}

						//If this is a current tour, add its ID to the row.
						$row['post_id'] = 0;

						if ( false !== $current_accommodation && array_key_exists( $row['id'], $current_accommodation ) ) {
							$row['post_id'] = $current_accommodation[ $row['id'] ]->post_id;
						}

						//If we are searching for
						if ( false !== $post_status ) {
							if ( 'import' === $post_status ) {

								if ( is_array( $this->queued_imports ) && in_array( $row['post_id'],$this->queued_imports ) ) {
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
								$keywords = explode( ' ',$keyphrase );

								if ( ! is_array( $keywords ) ) {
									$keywords = array( $keywords );
								}

								if ( $this->multineedle_stripos( ltrim( rtrim( $row['name'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['id'] ] = $this->format_row( $row );
								}
							}
						}
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
	public function process_ajax_import() {
		$return = false;

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_import_items' && isset( $_POST['type'] ) && $_POST['type'] === 'accommodation' && isset( $_POST['wetu_id'] ) ) {
			// @codingStandardsIgnoreLine
			$wetu_id = $_POST['wetu_id'];

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['post_id'] ) ) {
				// @codingStandardsIgnoreLine
				$post_id = $_POST['post_id'];
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

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['safari_brands'] ) ) {
				// @codingStandardsIgnoreLine
				$safari_brands = $_POST['safari_brands'];
			} else {
				$safari_brands = false;
			}

			delete_option( 'wetu_importer_accommodation_settings' );

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['content'] ) && is_array( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
				// @codingStandardsIgnoreLine
				$content = $_POST['content'];
				add_option( 'wetu_importer_accommodation_settings',$content );
			} else {
				$content = false;
			}

			$jdata = file_get_contents( $this->url . '/Get?' . $this->url_qs . '&ids=' . $wetu_id );

			if ( $jdata ) {
				$adata = json_decode( $jdata,true );
				if ( ! empty( $adata ) && ! isset( $adata['error'] ) ) {
					$return = $this->import_row( $adata,$wetu_id,$post_id,$team_members,$content,$safari_brands );
					$this->format_completed_row( $return );
					$this->remove_from_queue( $return );
					$this->cleanup_posts();
				} else {
					if ( isset( $adata['error'] ) ) {
						$this->format_error( $adata['error'] );
					} else {
						$this->format_error( esc_html__( 'There was a problem importing your accommodation, please try refreshing the page.', 'wetu-importer' ) );
					}
				}
			}
		}
	}

	/**
	 * Connect to wetu
	 */
	public function import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = false, $safari_brands = false ) {
		$post_name = '';
		$data_post_content = '';
		$data_post_excerpt = '';

		$post = array(
		  'post_type' => 'accommodation',
		);

		$content_used_general_description = false;

		//Set the post_content
		if ( false !== $importable_content && in_array( 'description',$importable_content ) ) {
			if ( isset( $data[0]['content']['extended_description'] ) ) {
				$data_post_content = $data[0]['content']['extended_description'];
			} elseif ( isset( $data[0]['content']['general_description'] ) ) {
				$data_post_content = $data[0]['content']['general_description'];
				$content_used_general_description = true;
			} elseif ( isset( $data[0]['content']['teaser_description'] ) ) {
				$data_post_content = $data[0]['content']['teaser_description'];
			}

			$post['post_content'] = wp_strip_all_tags( $data_post_content );
		}

		//set the post_excerpt
		if ( false !== $importable_content && in_array( 'excerpt',$importable_content ) ) {
			if ( isset( $data[0]['content']['teaser_description'] ) ) {
				$data_post_excerpt = $data[0]['content']['teaser_description'];
			} elseif ( isset( $data[0]['content']['general_description'] ) && false === $content_used_general_description ) {
				$data_post_excerpt = $data[0]['content']['general_description'];
			}

			$post['post_excerpt'] = $data_post_excerpt;
		}

		if ( false !== $id && '0' !== $id ) {
			$post['ID'] = $id;

			if ( isset( $data[0]['name'] ) ) {
				$post['post_title'] = $data[0]['name'];
				$post['post_status'] = 'publish';
				$post['post_name'] = wp_unique_post_slug( sanitize_title( $data[0]['name'] ),$id, 'draft', 'accommodation', 0 );
			}

			$id = wp_update_post( $post );
			$prev_date = get_post_meta( $id,'lsx_wetu_modified_date', true );
			update_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $data[0]['last_modified'] ), $prev_date );
		} else {
			//Set the name
			if ( isset( $data[0]['name'] ) ) {
				$post_name = wp_unique_post_slug( sanitize_title( $data[0]['name'] ),$id, 'draft', 'accommodation', 0 );
			}

			$post['post_name'] = $post_name;
			$post['post_title'] = $data[0]['name'];
			$post['post_status'] = 'publish';
			$id = wp_insert_post( $post );

			//Save the WETU ID and the Last date it was modified.
			if ( false !== $id ) {
				add_post_meta( $id,'lsx_wetu_id',$wetu_id );
				add_post_meta( $id,'lsx_wetu_modified_date',strtotime( $data[0]['last_modified'] ) );
			}
		}

		//Setup some default for use in the import
		if ( false !== $importable_content && (in_array( 'gallery',$importable_content ) || in_array( 'banner_image',$importable_content ) || in_array( 'featured_image',$importable_content )) ) {
			$this->find_attachments( $id );
		}

		//Set the team member if it is there
		if ( post_type_exists( 'team' ) && false !== $team_members && '' !== $team_members ) {
			$this->set_team_member( $id,$team_members );
		}

		//Set the safari brand
		if ( false !== $safari_brands && '' !== $safari_brands ) {
			$this->set_safari_brands( $id,$safari_brands );
		}

		if ( class_exists( 'LSX_TO_Maps' ) ) {
			$this->set_map_data( $data,$id,9 );
		}

		if ( post_type_exists( 'destination' ) && false !== $importable_content && in_array( 'destination',$importable_content ) ) {
			$this->connect_destinations( $data,$id );
		}

		if ( false !== $importable_content && in_array( 'category',$importable_content ) ) {
			$this->set_taxonomy_style( $data,$id );
		}

		//Set the Room Data
		if ( false !== $importable_content && in_array( 'rooms',$importable_content ) ) {
			$this->set_room_data( $data,$id );
		}

		//Set the rating
		if ( false !== $importable_content && in_array( 'rating',$importable_content ) ) {
			$this->set_rating( $data,$id );
		}

		//Set the checkin checkout data
		if ( false !== $importable_content && in_array( 'checkin',$importable_content ) ) {
			$this->set_checkin_checkout( $data,$id );
		}

		//Set the Spoken Languages
		if ( false !== $importable_content && in_array( 'spoken_languages',$importable_content ) ) {
			$this->set_spoken_languages( $data,$id );
		}

		//Set the friendly options
		if ( false !== $importable_content && in_array( 'friendly',$importable_content ) ) {
			$this->set_friendly( $data,$id );
		}

		//Set the special_interests
		if ( false !== $importable_content && in_array( 'special_interests',$importable_content ) ) {
			$this->set_special_interests( $data,$id );
		}

		//Import the videos
		if ( false !== $importable_content && in_array( 'videos',$importable_content ) ) {
			$this->set_video_data( $data,$id );
		}

		//Import the facilities
		if ( false !== $importable_content && in_array( 'facilities',$importable_content ) ) {
			$this->set_facilities( $data,$id );
		}

		//Set the featured image
		if ( false !== $importable_content && in_array( 'featured_image',$importable_content ) ) {
			$this->set_featured_image( $data,$id );
		}

		if ( false !== $importable_content && in_array( 'banner_image',$importable_content ) ) {
			$this->set_banner_image( $data,$id );
		}

		//Import the main gallery
		if ( false !== $importable_content && in_array( 'gallery',$importable_content ) ) {
			$this->create_main_gallery( $data,$id );
		}

		return $id;
	}

	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member( $id, $team_members ) {
		delete_post_meta( $id, 'team_to_' . $this->tab_slug );

		foreach ( $team_members as $team ) {
			add_post_meta( $id,'team_to_' . $this->tab_slug,$team );
		}
	}

	/**
	 * Set the safari brand
	 */
	public function set_safari_brands( $id, $safari_brands ) {
		foreach ( $safari_brands as $safari_brand ) {
			wp_set_object_terms( $id, intval( $safari_brand ), 'accommodation-brand',true );
		}
	}

	/**
	 * Connects the destinations post type
	 */
	public function connect_destinations( $data, $id ) {
		if ( isset( $data[0]['position'] ) ) {
			$destinations = false;

			if ( isset( $data[0]['position']['country'] ) ) {
				$destinations['country'] = $data[0]['position']['country'];
			}

			if ( isset( $data[0]['position']['destination'] ) ) {
				$destinations['destination'] = $data[0]['position']['destination'];
			}

			if ( false !== $destinations ) {
				$prev_values = get_post_meta( $id,'destination_to_accommodation',false );

				if ( false === $prev_values || ! is_array( $prev_values ) ) {
					$prev_values = array();
				}

				delete_post_meta( $id,'destination_to_accommodation',$prev_values );

				//print_r($destinations);
				$destinations = array_unique( $destinations );
				//print_r($destinations);

				foreach ( $destinations as $key => $value ) {
					$destination = get_page_by_title( ltrim( rtrim( $value ) ), 'OBJECT', 'destination' );
					if ( null !== $destination ) {
						if ( ! in_array( $destination->ID,$prev_values ) ) {
							   add_post_meta( $id,'destination_to_accommodation',$destination->ID,false );
							   add_post_meta( $destination->ID,'accommodation_to_destination',$id,false );
							$this->cleanup_posts[ $destination->ID ] = 'accommodation_to_destination';
						}
					}
				}
			}
		}
	}

	/**
	 * Set the Travel Style
	 */
	public function set_taxonomy_style( $data, $id ) {
		$terms = false;

		if ( isset( $data[0]['category'] ) ) {
			// @codingStandardsIgnoreLine
			if ( ! $term = term_exists( trim( $data[0]['category'] ), 'accommodation-type' ) ) {
				$term = wp_insert_term( trim( $data[0]['category'] ), 'accommodation-type' );

				if ( is_wp_error( $term ) ) {
					// @codingStandardsIgnoreLine
					echo $term->get_error_message();
				}
			} else {
				wp_set_object_terms( $id, intval( $term['term_id'] ), 'accommodation-type',true );
			}
		} else {
			wp_set_object_terms( $id, intval( $term['term_id'] ), 'accommodation-type',true );
		}
	}

	/**
	 * Saves the room data
	 */
	public function set_room_data( $data, $id ) {
		if ( ! empty( $data[0]['rooms'] ) && is_array( $data[0]['rooms'] ) ) {
			$rooms = false;

			foreach ( $data[0]['rooms'] as $room ) {
				$temp_room = array();

				if ( isset( $room['name'] ) ) {
					$temp_room['title'] = $room['name'];
				}

				if ( isset( $room['description'] ) ) {
					$temp_room['description'] = strip_tags( $room['description'] );
				}

				$temp_room['price'] = 0;
				$temp_room['type'] = 'room';

				if ( ! empty( $room['images'] ) && is_array( $room['images'] ) ) {
					$temp_room['gallery'] = array();
					$temp_room['gallery'][] = $this->attach_image( $room['images'][0], $id );

					/*foreach($room['images'] as $image_data){
			    		$temp_room['gallery'][] = $this->attach_image($image_data,$id);
			    	}*/
				}

				$rooms[] = $temp_room;
			}

			if ( false !== $id && '0' !== $id ) {
				delete_post_meta( $id, 'units' );
			}

			foreach ( $rooms as $room ) {
				add_post_meta( $id,'units',$room,false );
			}

			if ( isset( $data[0]['features'] ) && isset( $data[0]['features']['rooms'] ) ) {
				$room_count = $data[0]['features']['rooms'];
			} else {
				$room_count = count( $data[0]['rooms'] );
			}

			if ( false !== $id && '0' !== $id ) {
				$prev_rooms = get_post_meta( $id,'number_of_rooms',true );
				update_post_meta( $id,'number_of_rooms',$room_count,$prev_rooms );
			} else {
				add_post_meta( $id,'number_of_rooms',$room_count,true );
			}
		}
	}

	/**
	 * Set the ratings
	 */
	public function set_rating( $data, $id ) {
		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['star_authority'] ) ) {
			$rating_type = $data[0]['features']['star_authority'];
		} else {
			$rating_type = 'Unspecified2';
		}

		$this->save_custom_field( $rating_type,'rating_type',$id );

		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['stars'] ) ) {
			$this->save_custom_field( $data[0]['features']['stars'],'rating',$id,true );
		}
	}

	/**
	 * Set the spoken_languages
	 */
	public function set_spoken_languages( $data, $id ) {
		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['spoken_languages'] ) && ! empty( $data[0]['features']['spoken_languages'] ) ) {
			$languages = false;

			foreach ( $data[0]['features']['spoken_languages'] as $spoken_language ) {
				$languages[] = sanitize_title( $spoken_language );
			}

			if ( false !== $languages ) {
				$this->save_custom_field( $languages,'spoken_languages',$id );
			}
		}
	}

	/**
	 * Set the friendly
	 */
	public function set_friendly( $data, $id ) {
		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['suggested_visitor_types'] ) && ! empty( $data[0]['features']['suggested_visitor_types'] ) ) {
			$friendly_options = false;

			foreach ( $data[0]['features']['suggested_visitor_types'] as $visitor_type ) {
				$friendly_options[] = sanitize_title( $visitor_type );
			}

			if ( false !== $friendly_options ) {
				$this->save_custom_field( $friendly_options,'suggested_visitor_types',$id );
			}
		}
	}

	/**
	 * Set the special interests
	 */
	public function set_special_interests( $data, $id ) {
		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['special_interests'] ) && ! empty( $data[0]['features']['special_interests'] ) ) {
			$interests = false;

			foreach ( $data[0]['features']['special_interests'] as $special_interest ) {
				$interests[] = sanitize_title( $special_interest );
			}

			if ( false !== $interests ) {
				$this->save_custom_field( $interests,'special_interests',$id );
			}
		}
	}

	/**
	 * Set the Check in and Check out Date
	 */
	public function set_checkin_checkout( $data, $id ) {
		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['check_in_time'] ) ) {
			$time = str_replace( 'h',':',$data[0]['features']['check_in_time'] );
			$time = date( 'h:ia',strtotime( $time ) );
			$this->save_custom_field( $time,'checkin_time',$id );
		}

		if ( ! empty( $data[0]['features'] ) && isset( $data[0]['features']['check_out_time'] ) ) {
			$time = str_replace( 'h',':',$data[0]['features']['check_out_time'] );
			$time = date( 'h:ia',strtotime( $time ) );
			$this->save_custom_field( $time,'checkout_time',$id );
		}
	}

	/**
	 * Set the Facilities
	 */
	public function set_facilities( $data, $id ) {
		$parent_facilities = array(
			'available_services' => 'Available Services',
			'property_facilities' => 'Property Facilities',
			'room_facilities' => 'Room Facilities',
			'activities_on_site' => 'Activities on Site',
		);

		foreach ( $parent_facilities as $key => $label ) {
			$terms = false;

			if ( isset( $data[0]['features'] ) && isset( $data[0]['features'][ $key ] ) ) {
				$parent_id = $this->set_term( $id,$label,'facility' );
			}

			foreach ( $data[0]['features'][ $key ] as $child_facility ) {
				$this->set_term( $id,$child_facility,'facility',$parent_id );
			}
		}
	}
}