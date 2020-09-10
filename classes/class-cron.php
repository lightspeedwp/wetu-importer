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
		add_action( 'lsx_wetu_importer_settings_before', array( $this, 'watch_for_trigger' ), 200 );
		add_action( 'lsx_wetu_accommodation_images_cron', array( $this, 'process' ), 10, 1 );
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
			$schedule = false;
			if ( wp_next_scheduled( 'lsx_wetu_accommodation_images_cron' ) ) {
				$schedule = true;
			}

			// If activate and its not running.
			if ( false === $schedule && 'activate' === $accommodation_cron ) {
				$schedule = 'daily';
				$this->schedule( 'lsx_wetu_accommodation_images_cron', $schedule );
			} elseif ( true === $schedule && 'deactivate' === $accommodation_cron ) {
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
		wp_clear_scheduled_hook( $task );
	}

	/**
	 * This function will schedule the cron event.
	 *
	 * @param string $task
	 * @param string $schedule
	 * @param string $time
	 * @return void
	 */
	public function schedule( $task = 'lsx_wetu_accommodation_images_cron', $schedule = 'daily', $time = '' ) {
		if ( '' === $time ) {
			$time = time();
		}
		wp_schedule_event( $time, $schedule, $task, array( $task ) );
	}

	/**
	 * This is the function that will be triggered by the cron event.
	 *
	 * @return void
	 */
	public function process( $task = '' ) {
		switch ( $task ) {
			case 'lsx_wetu_accommodation_images_cron':

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
	public function sync_accommodation_images() {
		switch ( $task ) {
			case 'lsx_wetu_accommodation_images_cron':
				
				break;

			default:
				break;
		}
	}
}
Cron::get_instance();
