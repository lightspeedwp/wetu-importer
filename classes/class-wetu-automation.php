<?php
/**
 * Class WETU Automation using the Action Scheduler.
 *
 * @package   lsx\wetu_importer\classes
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2020 LightSpeed
 */

namespace lsx\wetu_importer\classes;

use WP_Query;

/**
 * Class Admin
 *
 * @package tsp_child\classes
 */
class WETU_Automation {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object tsp_child\classes\WETU_Automation()
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'tester_init' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \tsp_child\classes\WETU_Automation()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register the initian functions
	 *
	 * @return void
	 */
	public function init() {
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			//$this->schedule_main_action();
			add_action( 'lsx_wetu_master_sync', array( $this, 'run_main_actions' ), 10 );

			// These are the actions which sync the individual items.
			add_action( 'lsx_wetu_sync_tour', array( $this, 'tour_sync_action' ), 10, 1 );
			add_action( 'lsx_wetu_sync_que', array( $this, 'que_sync_action' ), 10 );
			add_action( 'lsx_wetu_sync_pin', array( $this, 'pins_sync_action' ), 10, 2 );
		}
	}

	/**
	 * Register the test functions.
	 *
	 * @return void
	 */
	public function tester_init() {
		if ( isset( $_GET['wetu_main_debug'] ) ) {
			$this->run_main_actions();
			die();
		}

		if ( isset( $_GET['wetu_tour_debug'] ) ) {
			$this->tour_sync_action( $_GET['wetu_tour_debug'] );
			die();
		}

		if ( isset( $_GET['wetu_pin_debug'] ) ) {
			$this->pins_sync_action( $_GET['wetu_pin_debug'], false );
			die();
		}
	}

	/**
	 * This will schedule our main action if it is not found.
	 * It will run daily.
	 *
	 * @return void
	 */
	public function schedule_main_action() {
		//master scheduled actions
		if ( ! wp_next_scheduled( 'lsx_wetu_master_sync' ) ) {
			$master_sync_scheduler = strtotime( '01:00:00' );
			wp_schedule_event( $master_sync_scheduler, 'daily', 'lsx_wetu_master_sync' );
		}
	}

	/**
	 * Grab the tours and compare them to the 
	 *
	 * @return void
	 */
	public function run_main_actions() {
		$tours      = $this->get_current_tours();
		$wetu_tours = $this->get_wetu_tours();

		if ( ! empty( $tours ) ) {

			foreach ( $tours as $tour ) {
				// Remove our current tours from the que.
				$wetu_id = get_post_meta( $tour, 'lsx_wetu_id', true );

				if ( ! empty( $wetu_tours ) && isset( $wetu_tours[ $wetu_id ] ) ) {
					
					// Next lets make sure the tour has been updated on the WETU side.
					$tour_date = get_post_meta( $tour, 'lsx_wetu_modified_date', true );

					if ( false !== $tour_date && '' !== $tour_date ) {
						$tour_mod = strtotime( $tour_date );
						$wetu_mod = strtotime( $wetu_tours[ $wetu_id ]['last_modified'] );

						if ( $wetu_mod <= $tour_mod ) {
							if ( class_exists( 'ActionScheduler_Logger' ) ) {
								\ActionScheduler_Logger::instance()->log(
									'wetu_skipped',
									$wetu_id . 'Tour Skipped Date - ' . $wetu_tours[ $wetu_id ]['last_modified'],
								);
							}

							unset( $wetu_tours[ $wetu_id ] );
							// Skip the tour import if it is 
							continue;
						}
						delete_post_meta( $tour, 'lsx_wetu_modified_date' );
						update_post_meta( $tour, 'lsx_wetu_modified_date', $wetu_tours[ $wetu_id ]['last_modified'] );
					} else {
						delete_post_meta( $tour, 'lsx_wetu_modified_date' );
						update_post_meta( $tour, 'lsx_wetu_modified_date', $wetu_tours[ $wetu_id ]['last_modified'] );
					}
					
					unset( $wetu_tours[ $wetu_id ] );
				}

				/*print_r('<pre>');
				print_r($tour);print_r('-');print_r(get_the_title( $tour ));
				print_r('</pre>');*/

				$action_id = as_enqueue_async_action(
					'lsx_wetu_sync_tour', 
					array(
						'tour_id' => $tour,
						'overrides' => array(),
					),
					'lsx_wetu_sync' // . date('dmy')
				);
				update_post_meta( $tour, 'lsx_wetu_sync_action_id', $action_id);
			}

			// Enque the que action
			$action_id = as_enqueue_async_action(
				'lsx_wetu_sync_que',
				array(),
				'lsx_wetu_sync'
			);

			//To be removed 

			// Let run through our new tours and import those.
			/*if ( ! empty( $wetu_tours ) && ! isset( $_GET['wetu_main_debug'] ) ) {
				foreach ( $wetu_tours as $key => $tour_info ) {
					$post['post_title']  = $tour_info['name'];
					$post['post_status'] = 'publish';
					$id                  = wp_insert_post( $post );
		
					// Save the WETU ID and the Last date it was modified.
					if ( false !== $id ) {
						add_post_meta( $id, 'lsx_wetu_id', $key );
						add_post_meta( $id, 'lsx_wetu_modified_date', strtotime( $tour_info['last_modified'] ) );
					}

					$action_id = as_enqueue_async_action(
						'lsx_wetu_sync_tour', 
						array(
							'tour_id' => $id,
							'overrides' => array(),
						),
						'lsx_wetu_sync' // . date('dmy')
					);
					update_post_meta( $tour, 'lsx_wetu_sync_action_id', $action_id);
				}
			}*/
		}

		return true;

		/*$items = $this->get_pins();
		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$action_id = as_enqueue_async_action(
					'lsx_wetu_sync_pin', 
					array(
						'item_id' => $item,
						'gallery' => false
					),
					'lsx_wetu_sync' // . date('dmy')
				);
				update_post_meta( $tour, 'lsx_wetu_sync_action_id', $action_id);
			}
		}*/
	}

	/**
	 * Gets the current tours in the system.
	 *
	 * @return array
	 */
	public function get_current_tours() {
		$tours = array();
		$args = array(
			'post_type' => 'tour',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			//'posts_per_page' => 1,
			'meta_key' => 'lsx_wetu_id',
			'meta_compare' => 'EXISTS',
			'fields' => 'ids',
		);
		$tour_query = new \WP_Query( $args );
		if ( $tour_query->have_posts() ) {
			$tours = $tour_query->posts;
		}
		return $tours;
	}

	/**
	 * Gets the accommodation and destinations.
	 *
	 * @return array
	 */
	public function get_pins() {
		$items = array();
		$args = array(
			'post_type' => array( 'accommodation', 'destination' ),
			'post_status' => 'publish',
			//'posts_per_page' => -1,
			'posts_per_page' => 10,
			'meta_key' => 'lsx_wetu_id',
			'meta_compare' => 'EXISTS',
			'fields' => 'ids',
		);
		$item_query = new \WP_Query( $args );
		if ( $item_query->have_posts() ) {
			$items = $item_query->posts;
		}
		return $items;
	}

	public function get_wetu_tours() {
		$tours = array();

		$tags = array(
			'Website',
		);

		foreach ( $tags as $tag ) {
			$data = wp_remote_get( 'https://wetu.com/API/Itinerary/' . $this->get_api_key() . '/V8/List?type=Personal&own=true&results=10000' );

			if ( ! is_wp_error( $data ) && ! empty( $data ) && isset( $data['response'] ) && isset( $data['response']['code'] ) && 200 === $data['response']['code'] ) {
				$jdata  = json_decode( $data['body'], true );

				if ( isset( $jdata['itineraries'] ) && ! empty( $jdata['itineraries'] ) ) {
					foreach ( $jdata['itineraries'] as $jata ) {
						$tours[ $jata['identifier'] ] = $jata;
					}
				}
			}
		}

		return $tours;
	}

	public function tour_sync_action( $tour_id, $overrides = array() ) {
		$return = false;

		$wetu_id = get_post_meta( $tour_id, 'lsx_wetu_id', true );

		$importable = array(
			'price',
			'description',
			'duration',
			'group_size',
			'category',
			'itineraries',
			'start_end_point',
			'itinerary_description',
			'itinerary_included',
			'itinerary_excluded',
			'room_basis',
			'drinks_basis',
			'accommodation',
			'destination',
		);

		// Check for a thumbnail.
		if ( ! has_post_thumbnail( $tour_id ) ) {
			$importable[] = 'featured_image';
			$importable[] = 'banner_image';
		}

		if ( ! empty( $overrides ) ) {
			$importable = $overrides;
		}

		$importable = apply_filters( 'wetu_automation_tour_flags', $importable );

		if ( false !== $wetu_id && '' !== $wetu_id ) {
			$jdata = wp_remote_get( 'https://wetu.com/API/Itinerary/V8/Get?id=' . $wetu_id );
	
			if ( ! is_wp_error( $jdata ) && ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && 200 === $jdata['response']['code'] ) {
				$jdata             = json_decode( $jdata['body'], true );
				$lsx_wetu_importer = new \LSX_WETU_Importer_Tours();
				$response          = $lsx_wetu_importer->import_row( $jdata, $wetu_id, $tour_id, array(), $importable );
				$lsx_wetu_importer->attach_destination_images( $importable );
				$lsx_wetu_importer->cleanup_posts();
			}
			if ( null !== $response ) {
				$return = true;
				$lsx_wetu_importer->save_queue();
				delete_post_meta( $tour_id, 'lsx_wetu_modified_date' );
				update_post_meta( $tour_id, 'lsx_wetu_modified_date', strtotime( 'now' ) );
			} else {
				// If the above didnt complete, run the action again.
				/*$action_id = as_enqueue_async_action(
					'lsx_wetu_sync_tour',
					array(
						'tour_id' => $tour_id,
						'overrides' => array(),
					),
					'lsx_wetu_sync' // . date('dmy')
				);
				update_post_meta( $tour_id, 'lsx_wetu_sync_action_id', $action_id);*/
			}
		}
		return $return;
	}

	
	public function que_sync_action() {
		$que = get_option( 'lsx_wetu_importer_que', array() );
		if ( ! empty( $que ) ) {
			foreach ( $que as $q ) {
				// Register the pin sync action
				$action_id = as_enqueue_async_action(
					'lsx_wetu_sync_pin', 
					array(
						'item_id' => $q,
						'gallery' => true,
					),
					'lsx_wetu_sync' // . date('dmy')
				);
				update_post_meta( $q, 'lsx_wetu_sync_action_id', $action_id );
			}
			delete_option( 'lsx_wetu_importer_que' );
		}
	}

	public function pins_sync_action( $item_id = '', $gallery = false ) {
		$return     = false;
		$importable = array();

		if ( '' !== $item_id && false !== $item_id ) {
			$post_type   = get_post_type( $item_id );
			$wetu_id     = get_post_meta( $item_id, 'lsx_wetu_id', true );
			$skip_import = false;
			
			if ( false !== $wetu_id && '' !== $wetu_id ) {
				$jdata = wp_remote_get( 'https://wetu.com/API/Pins/ROARLEMOUP5IENOE/Get?ids=' . $wetu_id );

				if ( ! is_wp_error( $jdata ) && ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && 200 === $jdata['response']['code'] ) {
					$jata  = json_decode( $jdata['body'], true );
					$return = true;

					// Next lets make sure the tour has been updated on the WETU side.
					$tour_date = get_post_meta( $item_id, 'lsx_wetu_modified_date', true );

					if ( false !== $tour_date && '' !== $tour_date ) {
						$tour_mod = strtotime( $tour_date );
						$wetu_mod = strtotime( $jata['last_modified'] );

						if ( $wetu_mod <= $tour_mod ) {

							if ( class_exists( 'ActionScheduler_Logger' ) ) {
								\ActionScheduler_Logger::instance()->log(
									'wetu_skipped',
									'WETU Date - ' . $jata['last_modified'],
								);
							}
							$skip_import = true;
							delete_post_meta( $item_id, 'lsx_wetu_modified_date' );
							update_post_meta( $item_id, 'lsx_wetu_modified_date', $jata['last_modified'] );
						}
					} else {
						delete_post_meta( $item_id, 'lsx_wetu_modified_date' );
						update_post_meta( $item_id, 'lsx_wetu_modified_date', $jata['last_modified'] );
					}

					if ( false === $skip_import ) {
						// Check for a thumbnail.
						if ( ! has_post_thumbnail( $item_id ) ) {
							$importable[] = 'featured_image';
							$importable[] = 'banner_image';
						}
						$importer     = false;
						$has_gallery = get_post_meta( $item_id, 'gallery', false );
						if ( false === $has_gallery || empty( $has_gallery ) ) {
							$importable[] = 'gallery';
						}

						if ( 'accommodation' === $post_type ) {
							$importable = $this->get_accommodation_flags( $importable );
							if ( class_exists( 'ActionScheduler_Logger' ) ) {
								\ActionScheduler_Logger::instance()->log(
									'wetu_parameters',
									print_r( $importable, true ),
								);
							}
							$importer = new \LSX_WETU_Importer_Accommodation();
							$response = $importer->import_row( $jata, $wetu_id, $item_id, array(), $importable, array() );
							$importer->remove_from_queue( $response );
						} else if ( 'destination' === $post_type ) {
							$importable = $this->get_destination_flags( $importable );
							if ( class_exists( 'ActionScheduler_Logger' ) ) {
								\ActionScheduler_Logger::instance()->log(
									'wetu_parameters',
									print_r( $importable, true ),
								);
							}
							$importer = new \LSX_WETU_Importer_Destination();
							$response = $importer->import_row( $jata, $wetu_id, $item_id, array(), $importable, array() );
							$importer->remove_from_queue( $response );
						}

						if ( false !== $importer ) {
							$importer->cleanup_posts();
						}
					}
				}
			}
		}
		return $return;
	}
	
	public function get_destination_flags( $flags = array() ) {
		$flags[] = 'location';
		$flags[] = 'country';
		$flags[] = 'continent';
		$flags[] = 'electricity';
		$flags[] = 'banking';
		$flags[] = 'cuisine';
		$flags[] = 'climate';
		$flags[] = 'transport';
		$flags[] = 'dress';
		$flags[] = 'health';
		$flags[] = 'safety';
		$flags[] = 'visa';
		$flags[] = 'additional_info';
		$flags   = apply_filters( 'wetu_automation_destination_flags', $flags );
		return $flags;
	}

	public function get_accommodation_flags( $flags = array() ) {
		$flags[] = 'description';
		$flags[] = 'excerpt';
		$flags[] = 'category';
		$flags[] = 'location';
		$flags[] = 'destination';
		$flags[] = 'rating';
		$flags[] = 'rooms';
		$flags[] = 'checkin';
		$flags[] = 'facilities';
		$flags[] = 'friendly';
		$flags[] = 'special_interests';
		$flags[] = 'spoken_languages';
		$flags   = apply_filters( 'wetu_automation_accommodation_flags', $flags );
		return $flags;
	}

	/**
	 * Gets the API key stored in the options table.
	 *
	 * @return string
	 */
	public function get_api_key() {
		$api_key = false;
		$options = lsx_wetu_get_options();

		if ( ! defined( 'WETU_API_KEY' ) ) {
			if ( isset( $options['api_key'] ) && '' !== $options['api_key'] ) {
				$api_key = $options['api_key'];
			}
		} else {
			$api_key = WETU_API_KEY;
		}
		return $api_key;
	}
}
WETU_Automation::get_instance();