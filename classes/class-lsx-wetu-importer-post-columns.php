<?php
/**
 * The Post Columns code for the Importer Plugin
 *
 * @package   lsx_wetu_importer
 * @author    LightSpeed
 * @license   GPL-3.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

/**
 * The Post Columns code for the Importer Plugin
 */
class LSX_WETU_Importer_Post_Columns {

	/**
	 * Holds instance of the class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_filter( 'manage_tour_posts_columns', array( $this, 'register_tour_columns' ) );
		add_action( 'manage_tour_posts_custom_column', array( $this, 'output_tour_ref_column' ), 10, 2 );

		// Sortables Columns, sorting needs to be fixed
		// add_filter( 'manage_edit-tour_sortable_columns', array( $this, 'register_sortable_columns' ) );
		// add_action( 'pre_get_posts', array( $this, 'columns_posts_orderby' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return  object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Display the importer welcome screen
	 */
	public function display_page() {

	}

	/**
	 * Registers the tour ref column
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_tour_columns( $columns ) {
		$new_columns = array(
			'cb'       => $columns['cb'],
			'title'    => $columns['title'],
			'wetu_ref' => __( 'Ref', 'lsx-wetu-importer' ),
		);
		unset( $columns['cb'] );
		unset( $columns['title'] );
		foreach ( $columns as $column_key => $column_label ) {
			$new_columns[ $column_key ] = $column_label;
		}
		$columns = $new_columns;
		return $columns;
	}

	/**
	 * Outputs the tour reference column
	 *
	 * @param string $column
	 * @param string $post_id
	 * @return void
	 */
	public function output_tour_ref_column( $column, $post_id ) {
		if ( 'wetu_ref' === $column ) {
			echo esc_attr( get_post_meta( $post_id, 'lsx_wetu_ref', true ) );
		}
	}

	/**
	 * Register the columns that will be sortable
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_sortable_columns( $columns = array() ) {
		$columns['wetu_ref'] = 'price_per_month';
		return $columns;
	}

	/**
	 * Sort the columns
	 *
	 * @param object $query WP_Query()
	 * @return void
	 */
	public function columns_posts_orderby( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'wetu_ref' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'lsx_wetu_reference' );
		}
		/*
		if ( $query->is_search() && 'tour' === $query->get( 'post_type' ) ) {
			$meta_query = array(
				'relation' => 'OR',
				array(
					'key' => 'lsx_wetu_ref',
					'value' => get_search_query(),
					'compare' => 'LIKE',
				),
			);
			$query->set( 'meta_query', $meta_query );
		}*/
	}
}
