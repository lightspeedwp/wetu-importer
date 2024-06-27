<?php

add_action( 'init', 'lsx_wetu_register_meta' );

function lsx_wetu_register_meta() {

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
