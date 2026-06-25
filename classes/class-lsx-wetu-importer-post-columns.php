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

		add_action( 'pre_get_posts', array( $this, 'tour_search_by_wetu_ref' ) );
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
	 * Post IDs found via lsx_wetu_ref meta lookup, passed to posts_search.
	 *
	 * @var int[]
	 */
	private $wetu_ref_post_ids = array();

	/**
	 * Looks up tour IDs matching the search term by lsx_wetu_ref, then hooks
	 * posts_search so those IDs are ORed into the title/content search clause.
	 *
	 * @param object $query WP_Query()
	 * @return void
	 */
	public function tour_search_by_wetu_ref( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'tour' !== $query->get( 'post_type' ) ) {
			return;
		}

		$term = $query->get( 's' );
		if ( empty( $term ) ) {
			return;
		}

		global $wpdb;
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$ids  = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
			WHERE meta_key = 'lsx_wetu_ref' AND meta_value LIKE %s",
			$like
		) );

		if ( ! empty( $ids ) ) {
			$this->wetu_ref_post_ids = array_map( 'intval', $ids );
			add_filter( 'posts_search', array( $this, 'tour_wetu_ref_posts_search' ), 10, 2 );
		}
	}

	/**
	 * Extends the SQL search clause to include the pre-fetched lsx_wetu_ref post IDs.
	 *
	 * @param string   $search
	 * @param WP_Query $_query
	 * @return string
	 */
	public function tour_wetu_ref_posts_search( $search, $_query ) {
		global $wpdb;

		remove_filter( 'posts_search', array( $this, 'tour_wetu_ref_posts_search' ), 10 );

		if ( empty( $this->wetu_ref_post_ids ) ) {
			return $search;
		}

		$id_list = implode( ',', $this->wetu_ref_post_ids );

		// $search arrives as " AND (title/excerpt/content conditions)".
		// Strip the leading AND and re-wrap so the OR stays inside the same
		// AND block, keeping post_type/status conditions in the outer WHERE intact.
		$inner  = preg_replace( '/^\s*AND\s*/i', '', $search );
		$search = " AND ({$inner} OR {$wpdb->posts}.ID IN ({$id_list}))";

		$this->wetu_ref_post_ids = array();

		return $search;
	}
}
