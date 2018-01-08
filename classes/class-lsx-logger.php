<?php

namespace lsx;

if ( ! class_exists( '\lsx\LSX_Logger' ) ) {

	/**
	 * LSX_Logger Main Class
	 *
	 * @package   LSX_Logger
	 * @author    LightSpeed
	 * @license   GPL-3.0+
	 * @link
	 * @copyright 2017 LightSpeedDevelopment
	 */
	class LSX_Logger {

		/**
		 * Holds instance of the class
		 *
		 * @since   1.1.0
		 * @var     \lsx\Geo_Content
		 */
		private static $instance;

		/**
		 * Holds the Logs of what happened
		 */
		private $logs = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'wp_before_admin_bar_render', array( $this, 'wp_admin_bar_menu' ) );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return  LSX_Logger  A single instance
		 */
		public static function init() {

			// If the single instance hasn't been set, set it now.
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Adds a field to the log
		 *
		 * @param $plugin string
		 * @param $key string
		 * @param $message string
		 * @return  void
		 */
		public function log( $plugin = '', $key = '', $message = '' ) {
			$this->logs[ $plugin ][ $key ] = $message;
		}

		/**
		 * Gets a field from the API lookup object
		 *
		 * @param $plugin string | boolean
		 * @return  string
		 */
		public function output_log( $plugin = false ) {
			$return = '';
			if ( ! empty( $this->logs ) ) {
				foreach ( $this->logs as $plugin_key => $log ) {

					$return = '<div style="padding: 15px;">';
					if ( false !== $plugin && $plugin_key !== $plugin ) {
						continue;
					} else if ( false === $plugin ) {
						$return .= '<h4>' . $plugin_key . '</h4>';
					}
					$return .= '<ul>';
					$return .= $this->loop_through_logs( $log );
					$return .= '</ul>';
					$return .= '</div>';
				}
			}
			return $return;
		}

		/**
		 * Gets a field from the API lookup object
		 *
		 * @param $log_array array
		 * @return  string
		 */
		public function loop_through_logs( $log_array = array() ) {
			$return = '';
			foreach ( $log_array as $key => $message ) {
				$return .= '<li>' . $message . ' <small>(' . $key . ')</small></li>';
			}
			return $return;
		}

		/**
		 * Registers an admin bar menu so you can see the output of the log messages
		 *
		 * @return  void
		 */
		function wp_admin_bar_menu() {
			global $wp_admin_bar;
			$wp_admin_bar->add_menu( array(
				'parent' => false,
				'id' => 'lsx_logger',
				'title' => __( 'LSX Log' ), // link title
				'href' => '',
			));
			$wp_admin_bar->add_menu( array(
				'parent' => 'lsx_logger',
				'id' => 'lsx_logger_output',
				'title' => '', // link title
				'href' => '',
				'meta' => array(
					'html' => $this->output_log(),
					'class' => '',
				),
			));
		}

	}
}
