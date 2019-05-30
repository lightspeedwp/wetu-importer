<?php
/**
 * Helper functions
 *
 * @package   wetu_importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

namespace wetu_importer\includes\helpers;

/**
 * Gets the settings
 *
 * @return array
 */
function get_options() {
	$options = get_option( 'wetu_importer_settings', array() );
	if ( empty( $options ) ) {
		// Check for any previous options.
		$temp_options = get_option( '_lsx-to_settings', false );
		if ( false !== $temp_options && isset( $temp_options['wetu-importer'] ) && ! empty( $temp_options['wetu-importer'] ) ) {
			$options = $temp_options['wetu-importer'];		
		}
		if ( false !== $temp_options && isset( $temp_options['api']['wetu_api_key'] ) && '' !== $temp_options['api']['wetu_api_key'] ) {
			$options['api_key'] = $temp_options['api']['wetu_api_key'];
		}

	}
	return $options;
}

/**
 * Get the post count.
 *
 * @param string $post_type
 * @param string $post_status
 * @return void
 */
function get_post_count( $post_type = '', $post_status = '' ) {
	global $wpdb;
	$count = '0';
	if ( '' !== $post_type && '' !== $post_status ) {
		$result = $wpdb->get_var("
			SELECT COUNT(`ID`)
			FROM `{$wpdb->posts}`
			WHERE `post_status` = '{$post_status}' AND `post_type` = '{$post_type}'
		");
		if ( false !== $result && '' !== $result ) {
			$count = $result;
		}
	}
	return $count;
}

/**
 * Returns the qetu queue count.
 *
 * @param string $post_type
 * @return void
 */
function get_wetu_queue_count( $post_type = '' ) {
	$count = '0';
	$queued_imports = get_option( 'wetu_importer_que', array() );
	if ( isset( $queued_imports[ $post_type ] ) ) {
		$count = count( $queued_imports[ $post_type ] );
	}
	return $count;
}