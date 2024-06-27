<?php

add_action( 'init', 'lsx_wetu_register_meta' );

function lsx_wetu_register_meta() {

	$fields = array(
		'included'     => array(),
		'not_included' => array(),
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
