<?php

add_action( 'init', 'lsx_wetu_register_meta' );

function lsx_wetu_register_meta() {

	add_filter('acf/settings/remove_wp_meta_box', '__return_false');

	$fields = array(
		//Not needed
		'included'     => array(),
		'not_included' => array(),

		//Search.
		'duration'     => array(),
		'price'        => array(),
	);
	$defaults = array(
		'default' => '',
		'single' => true,
		'type' => 'string',
	);
	foreach ( $fields as $key => $args ) {
		$args = wp_parse_args( $args, $defaults );
		register_meta(
			'post',
			$key,
			array(
				'show_in_rest' => true,
				'single'       => $args['single'],
				'type'         => $args['type'],
				'default'      => $args['default'],
			)
		);
	}
}


function lsx_wetu_register_block_bindings() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		'lsx/post-connection',
		array(
			'label' => __( 'Post Connection', 'lsx-wetu-importer' ),
			'get_value_callback' => 'lsx_wetu_bindings_callback'
		)
	);
}
add_action( 'init', 'lsx_wetu_register_block_bindings' );

function lsx_wetu_bindings_callback( $source_args, $block_instance ) {
	if ( 'core/image' === $block_instance->parsed_block['blockName'] ) {

		return 'test_image';
	} elseif ( 'core/paragraph' === $block_instance->parsed_block['blockName'] ) {
		$value = get_post_meta( get_the_ID(), $source_args['key'], true );

		if ( '' !== $value ) {
			$value = '<a href="' . get_permalink( $value ) . '">' . get_the_title( $value ) . '</a>';
		}

		return $value;
	}
}

//add_filter( 'render_block', 'lsx_wetu_render_block', 10, 3 );
function lsx_wetu_render_block( $block_content, $parsed_block, $block_obj ) {
	// Determine if this is the custom block variation.
	if ( ! isset( $parsed_block['blockName'] ) || ! isset( $parsed_block['attrs'] )  ) {
		return $block_content;
	}
	$allowed_blocks = array(
		'core/paragraph'
	);
	$allowed_sources = array(
		'core/post-meta',
		'lsx/post-connection'
	);
	if ( ! in_array( $parsed_block['blockName'], $allowed_blocks, true ) ) {
		return $block_content; 
	}

	if ( ! isset( $parsed_block['attrs']['metadata']['bindings']['content']['source'] ) ) {
		return $block_content;
	}

	if ( ! in_array( $parsed_block['attrs']['metadata']['bindings']['content']['source'], $allowed_sources ) ) {
		return $block_content;
	}

	return $block_content;
}



add_action( 'cmb2_admin_init', 'lsx_wetu_tour_metaboxes' );
function lsx_wetu_tour_metaboxes() {
	/**
	 * Initiate the metabox
	 */
	$cmb = new_cmb2_box( array(
		'id'            => 'lsx_to_metabox',
		'title'         => __( 'LSX Tour Operator Plugin', 'cmb2' ),
		'object_types'  => array( 'tour', 'post' ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true,
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Featured', 'tour-operator' ),
		'id'   => 'featured',
		'type' => 'checkbox',
	) );

	$cmb->add_field( array(
		'id'   => 'duration',
		'name' => esc_html__( 'Duration', 'tour-operator' ),
		'type' => 'text',
	) );

	$cmb->add_field( array(
		'id'   => 'duration',
		'name' => esc_html__( 'Price', 'tour-operator' ),
		'type' => 'text',
	) );

	$cmb->add_field( array(
		'id'         => 'departs_from',
		'name'       => esc_html__( 'Departs From', 'tour-operator' ),
		'type'       => 'post_ajax_search',
		'query_args'      => array(
			'post_type'      => 'destination',
			'nopagin'        => true,
			'posts_per_page' => '-1',
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
	) );

	$cmb->add_field( array(
		'id'         => 'ends_in',
		'name'       => esc_html__( 'Ends In', 'tour-operator' ),
		'type'       => 'post_ajax_search',
		'query_args'      => array(
			'post_type'      => 'destination',
			'nopagin'        => true,
			'posts_per_page' => '-1',
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
	) );

	$cmb->add_field( array(
		'id'       => 'best_time_to_visit',
		'name'     => esc_html__( 'Best months to visit', 'tour-operator' ),
		'type'     => 'multicheck',
		'options'  => array(
			'january'   => 'January',
			'february'  => 'February',
			'march'     => 'March',
			'april'     => 'April',
			'may'       => 'May',
			'june'      => 'June',
			'july'      => 'July',
			'august'    => 'August',
			'september' => 'September',
			'october'   => 'October',
			'november'  => 'November',
			'december'  => 'December',
		),
	));

	/*$cmb->add_field();

	$cmb->add_field();

	$cmb->add_field();*/


	/**
	 * lsx_wetu_itinerary_complete
	 */
	function lsx_wetu_merge_itinerary() {
		
	}
}
