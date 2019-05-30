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
