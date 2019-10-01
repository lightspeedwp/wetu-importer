<?php
/**
 * Helper functions
 *
 * @package   lsx_wetu_importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

/**
 * Gets the settings
 *
 * @return array
 */
function lsx_wetu_get_options() {
	$options = get_option( 'lsx_wetu_importer_settings', array() );
	if ( empty( $options ) ) {
		// Check for any previous options.
		$temp_options = get_option( '_lsx-to_settings', false );
		if ( false !== $temp_options && isset( $temp_options['lsx-wetu-importer'] ) && ! empty( $temp_options['lsx-wetu-importer'] ) ) {
			$options = $temp_options['lsx-wetu-importer'];
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
function lsx_wetu_get_post_count( $post_type = '', $post_status = '' ) {
	global $wpdb;
	$count = '0';
	if ( '' !== $post_type && '' !== $post_status ) {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(`ID`) FROM $wpdb->posts WHERE `post_status` = '%s' AND `post_type` = '%s'", array( trim( $post_status ), $post_type ) ) );
		if ( false !== $result && '' !== $result ) {
			if ( 'tour' === $post_type ) {
				$wetu_tours = get_transient( 'lsx_ti_tours' );
				if ( false !== $wetu_tours ) {
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT `ID` FROM $wpdb->posts WHERE `post_status` = '%s' AND `post_type` = '%s'", array( trim( $post_status ), $post_type ) ) );
					$result_count = 0;
					$tour_wetu_ids = array();
					foreach ( $wetu_tours as $wetu_tour ) {
						$tour_wetu_ids[] = $wetu_tour['identifier'];
					}

					if ( ! empty( $results ) ) {
						foreach ( $results as $tour ) {
							$current_wetu_id = get_post_meta( $tour->ID, 'lsx_wetu_id', true );
							if ( in_array( $current_wetu_id, $tour_wetu_ids ) ) {
								$result_count++;
							}
						}
					}
					$result = $result_count;
				} else {
					$result = 0;
				}
			}
			$count = $result;
		}
	}
	return $count;
}

/**
 * Returns the wetu queue count.
 *
 * @param string $post_type
 * @return void
 */
function lsx_wetu_get_queue_count( $post_type = '' ) {
	$count = '0';
	$queued_imports = get_option( 'lsx_wetu_importer_que', array() );
	if ( isset( $queued_imports[ $post_type ] ) ) {
		$count = count( $queued_imports[ $post_type ] );
	}
	return $count;
}

/**
 * Returns the wetu tour count.
 *
 * @param string $post_type
 * @return void
 */
function lsx_wetu_get_tour_count( $post_type = '' ) {
	$count = '0';
	$wetu_tours = get_transient( 'lsx_ti_tours', array() );
	if ( ! empty( $wetu_tours ) ) {
		$count = count( $wetu_tours );
	}
	return $count;
}
