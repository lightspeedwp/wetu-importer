<?php
/**
 * The main plugin class.
 *
 * @package   LSX_WETU_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 */

namespace lsx\wetu_importer\classes;

/**
 * The Main plugin class.
 */
class Cron {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|Module_Template
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'register_schedule' ), 10, 1 );
		add_action( 'lsx_wetu_importer_settings_before', array( $this, 'watch_for_trigger' ), 200 );
		add_action( 'lsx_wetu_accommodation_images_cron', array( $this, 'process' ), 10, 1 );
		add_action( 'lsx_wetu_accommodation_images_sync', array( $this, 'cron_callback' ), 10, 1 );
		add_filter( 'cmb_meta_boxes', array( $this, 'metaboxes' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object Cron()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Define the metabox and field configurations.
	 *
	 * @param  array $meta_boxes
	 * @return array
	 */
	public function metaboxes( array $meta_boxes ) {
		// Allowed post types.
		$allowed_post_types = array( 'accommodation' );

		$fields = array();

		$fields[] = array(
			'id'   => 'wetu_skip_banner',
			'name' => esc_html__( 'Skip Banner Image', 'lsx-banners' ),
			'type' => 'checkbox',
		);

		$fields[] = array(
			'id'   => 'wetu_skip_featured',
			'name' => esc_html__( 'Skip Featured Image', 'lsx-banners' ),
			'type' => 'checkbox',
		);

		$meta_boxes[] = array(
			'title'    => esc_html__( 'WETU Settings', 'lsx-banners' ),
			'pages'    => $allowed_post_types,
			'fields'   => $fields,
			'context'  => 'side',
			'priority' => 'low',
		);

		return $meta_boxes;
	}

	/**
	 * Registers a 5 min schedule for us to use.
	 *
	 * @param  array $schedules
	 * @return array
	 */
	public function register_schedule( $schedules ) {
		$schedules['wetu-5-minutes'] = array(
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 5 minutes', 'lsx-wetu-importer' ),
		);
		return $schedules;
	}

	/**
	 * Watches for changes in the button triggers.
	 *
	 * @return void
	 */
	public function watch_for_trigger() {

		if ( isset( $_GET['page'] ) && 'lsx-wetu-importer' === $_GET['page'] && isset( $_GET['tab'] ) && 'settings' === $_GET['tab'] ) {
			$options = lsx_wetu_get_options();

			// Check what state the option is in.
			$accommodation_cron = 'deactivate';
			if ( isset( $options['accommodation_images_cron'] ) && '' !== $options['accommodation_images_cron'] ) {
				$accommodation_cron = 'activate';
			}

			// Check what state the cron is in.
			$scheduled = false;
			if ( wp_next_scheduled( 'lsx_wetu_accommodation_images_cron' ) ) {
				$scheduled = true;
			}

			// If activate and its not running.
			if ( false === $scheduled && 'activate' === $accommodation_cron ) {
				$schedule = 'weekly';
				$this->schedule( 'lsx_wetu_accommodation_images_cron', $schedule );
			} elseif ( true === $scheduled && 'deactivate' === $accommodation_cron ) {
				$this->deactivate();
			}
		}
	}

	/**
	 * Remove our cron from the shedule.
	 *
	 * @return void
	 */
	public function deactivate( $task = 'lsx_wetu_accommodation_images_cron' ) {
		wp_clear_scheduled_hook( $task, array( $task ) );
	}

	/**
	 * This function will schedule the cron event.
	 *
	 * @param string $task
	 * @param string $schedule
	 * @param string $time
	 * @return void
	 */
	public function schedule( $task = 'lsx_wetu_accommodation_images_cron', $schedule = 'weekly', $time = 'Sunday 10pm' ) {
		$args = array( $task );
		if ( '' === $time ) {
			$time = time();
		}

		if ( isset( $_GET['accommodation_images_cron_featured'] ) && '' !== $_GET['accommodation_images_cron_featured'] ) {
			$args[] = 'featured_image';
		}
		wp_schedule_event( $time, $schedule, $task, $args );
	}

	/**
	 * This is the function that will be triggered by the cron event.
	 *
	 * @return void
	 */
	public function process( $task = '' ) {
		switch ( $task ) {
			case 'lsx_wetu_accommodation_images_cron':
					$this->register_accommodation_images_sync();
				break;

			default:
				break;
		}
	}

	/**
	 * This is the function that will be triggered by the cron event.
	 *
	 * @return void
	 */
	public function register_accommodation_images_sync() {
		$time = strtotime( '+5 min' );
		if ( ! wp_next_scheduled( 'lsx_wetu_accommodation_images_sync' ) ) {
			$this->load_items_to_sync( 'accommodation_images' );
			$this->schedule( 'lsx_wetu_accommodation_images_sync', 'wetu-5-minutes', $time );
		}
	}

	/**
	 * This is the function that will be triggered by the cron event.
	 *
	 * @return void
	 */
	public function cron_callback( $task = '', $featured_image = '' ) {
		$has_accommodation = get_option( $task );
		if ( false !== $has_accommodation && ! empty( $has_accommodation ) ) {
			$next_time = array_slice( $has_accommodation, 3 );
			$this_time = array_slice( $has_accommodation, 0, 2 );

			$api_key = $this->get_api_key();
			$url     = 'https://wetu.com/API/Pins/' . $api_key . '/Get?all=include&ids=';

			// Run through the current items.
			foreach ( $this_time as $accommodation ) {
				$wetu_id   = get_post_meta( $accommodation, 'lsx_wetu_id', true );
				$last_date = get_post_meta( $accommodation, 'lsx_wetu_modified_date', true );

				// Grabbing the image sync.
				$featured_image = get_post_meta( $accommodation, 'wetu_skip_featured', true );
				$banner_image   = get_post_meta( $accommodation, 'wetu_skip_banner', true );

				$accommodation_info = wp_remote_get( $url . $wetu_id );
				if ( ! empty( $accommodation_info ) && isset( $accommodation_info['response'] ) && isset( $accommodation_info['response']['code'] ) && 200 === $accommodation_info['response']['code'] ) {
					$adata = json_decode( $accommodation_info['body'], true );

					if ( isset( $adata[0] ) && isset( $adata[0]['last_modified'] ) && '' !== $adata[0]['last_modified'] ) {
						$modified_time = strtotime( $adata[0]['last_modified'] );
						if ( $modified_time > $last_date ) {
							$accommodation_importer = new \LSX_WETU_Importer_Accommodation();

							if ( false === $banner_image || '' === $banner_image ) {
								$accommodation_importer->set_banner_image( $adata, $accommodation );
							}

							if ( false === $featured_image || '' === $featured_image ) {
								$accommodation_importer->set_featured_image( $adata, $accommodation );
							}

							$accommodation_importer->create_main_gallery( $adata, $accommodation );
							update_post_meta( $accommodation, 'lsx_wetu_modified_date', $modified_time, $last_date );
						}
					}
				}
			}

			// Save the values for next time.
			if ( ! empty( $next_time ) ) {
				update_option( $task, $next_time );
			} else {
				delete_option( $task );
				$this->deactivate( $task );
			}
		} else {
			$this->deactivate( $task );
			update_option( 'lsx_wetu_nexttime', $task );
		}
	}

	/**
	 * This will grab the accommodation ids and load them up into an option field.
	 *
	 * @param  string $task
	 * @return void
	 */
	public function load_items_to_sync( $task = 'accommodation_images' ) {
		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'nopagin'        => true,
			'fields'         => 'ids',
		);
		switch ( $task ) {
			case 'accommodation_images':
					$args['post_type'] = 'accommodation';
				break;

			default:
				break;
		}
		$items = new \WP_Query( $args );
		if ( $items->have_posts() ) {
			update_option( 'lsx_wetu_' . $task . '_sync', $items->posts );
		}
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
Cron::get_instance();
