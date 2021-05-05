<?php
/**
 * @package   LSX_WETU_Importer_Connect_Accommodation
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 **/

class LSX_WETU_Importer_Connect_Accommodation extends LSX_WETU_Importer_Admin {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'connect_accommodation';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$temp_options = get_option( '_lsx-to_settings', false );

		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) && ! empty( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];
		}

		$this->url = 'http://wetu.com/API/Pins/' . $this->options['api_key'] . '/List';

		add_action( 'lsx_tour_importer_admin_tab_' . $this->tab_slug, array( $this, 'display_page' ) );
		add_action( 'wp_ajax_lsx_import_connect_accommodation', array( $this, 'process_connection' ) );
		add_action( 'wp_ajax_nopriv_lsx_import_connect_accommodation', array( $this, 'process_connection' ) );
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		global $post;
		?>
		<div class="wrap">
			<h3><span class="dashicons dashicons-admin-multisite"></span> <?php esc_html_e( 'Connect your Accommodation', 'lsx-wetu-importer' ); ?></h3>

			<form method="get" action="" id="connect-accommodation-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

				<p><?php esc_html_e( 'Below is a list of your accommodation that does not contain a WETU ID, but its Title matches a name in the WETU DB. Connecting it will all you to pull through information from WETU.', 'lsx-wetu-importer' ); ?></p>

				<div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
					<img style="width:32px;" src="<?php echo esc_url( LSX_WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif' ); ?>" />
				</div>

				<?php
					$loose_accommodation = $this->find_current_accommodation();
				?>
				<p><input class="button button-primary connect" type="button" value="<?php esc_html_e( 'Connect', 'lsx-wetu-importer' ); ?>" /></p>
				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody>
						<?php
						if ( false !== $loose_accommodation ) {

							$loose_args                = array(
								'post_type'   => 'accommodation',
								'post_status' => array( 'publish', 'pending' ),
								'nopagin'     => true,
								'post__in'    => $loose_accommodation,
							);
							$loose_accommodation_query = new WP_Query( $loose_args );
							$accommodation             = get_transient( 'lsx_ti_accommodation' );
							$identifier                = '';

							if ( $loose_accommodation_query->have_posts() && false !== $accommodation ) {
								while ( $loose_accommodation_query->have_posts() ) {
									$loose_accommodation_query->the_post();

									foreach ( $accommodation as $row_key => $row ) {
										if ( stripos( ltrim( rtrim( $row->name ) ), $post->post_title ) !== false ) {
											$identifier = $row->id;
										} else {
											continue;
										}
									}
									?>
									<tr class="post-<?php the_ID(); ?> type-accommodation status-none" id="post-<?php the_ID(); ?>">
										<th class="check-column" scope="row">
											<label for="cb-select-<?php the_ID(); ?>" class="screen-reader-text"><?php the_title(); ?></label>
											<input type="checkbox" data-identifier="<?php echo esc_attr( $identifier ); ?>" value="<?php the_ID(); ?>" name="post[]" id="cb-select-<?php the_ID(); ?>">
										</th>
										<td class="post-title page-title column-title">
											<strong><?php the_title(); ?></strong> - <a href="<?php echo esc_url( admin_url( '/post.php?post=' . $post->ID . '&action=edit' ) ); ?>" target="_blank"><?php echo esc_html( $post->post_status ); ?></a>
										</td>
										<td class="excerpt column-excerpt">
											<?php
												echo wp_kses_post( strip_tags( get_the_excerpt() ) );
											?>
										</td>
									</tr>
									<?php
								}
							}
						}
						?>
					</tbody>

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary connect" type="button" value="<?php esc_html_e( 'Connect', 'lsx-wetu-importer' ); ?>" /></p>

			</form>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php esc_html_e( 'Completed' ); ?></h3>
				<ul>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * The header of the item list
	 */
	public function table_header() {
		?>
		<thead>
			<tr>
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
					<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
					<input type="checkbox" id="cb-select-all-1">
				</th>
				<th style="width:40%;" class="manage-column column-title " id="title" style="width:49%;" scope="col">Title</th>
				<th style="width:40%;" class="manage-column column-date" id="date" style="width:49%;" scope="col">Excerpt</th>
			</tr>
		</thead>
		<?php
	}

	/**
	 * The footer of the item list
	 */
	public function table_footer() {
		?>
		<tfoot>
			<tr>
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
					<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
					<input type="checkbox" id="cb-select-all-1">
				</th>
				<th style="width:40%;" class="manage-column column-title " id="title" style="width:49%;" scope="col">Title</th>
				<th style="width:40%;" class="manage-column column-date" id="date" style="width:49%;" scope="col">Excerpt</th>
			</tr>
		</tfoot>
		<?php
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 */
	public function find_current_accommodation() {
		global $wpdb;
		$return = false;

		$all_accommodation = $wpdb->get_results(
			"
			SELECT ID
			FROM {$wpdb->posts}
			WHERE post_type = 'accommodation'
			LIMIT 0,500
		",
			ARRAY_A
		);

		$current_accommodation = $wpdb->get_results(
			"
			SELECT key1.post_id
			FROM {$wpdb->postmeta} key1

			INNER JOIN  {$wpdb->posts} key2
			ON key1.post_id = key2.ID

			WHERE key1.meta_key = 'lsx_wetu_id'
			AND key2.post_type = 'accommodation'

			LIMIT 0,500
		",
			ARRAY_A
		);

		if ( null !== $all_accommodation && ! empty( $all_accommodation ) ) {
			// remove the extra accommodation
			if ( null !== $current_accommodation && ! empty( $current_accommodation ) ) {
				$all_accommodation = array_diff( $this->format_array( $all_accommodation, 'ID' ), $this->format_array( $current_accommodation, 'post_id' ) );
			} elseif ( null !== $current_accommodation && empty( $current_accommodation ) ) {
				$all_accommodation = $this->format_array( $current_accommodation, 'post_id' );
			}

			$return = $all_accommodation;
		}

		return $return;
	}

	/**
	 * format the array
	 */
	public function format_array( $array, $key ) {
		$new_array = array();

		foreach ( $array as $value ) {
			$new_array[] = $value[ $key ];
		}

		return $new_array;
	}

	/**
	 * Run through the accommodation an connect them.
	 */
	public function process_connection() {
		$return = false;
		check_ajax_referer( 'lsx_wetu_ajax_action', 'security' );
		if ( isset( $_POST['action'] ) && 'lsx_import_connect_accommodation' === $_POST['action'] && isset( $_POST['type'] ) && $_POST['type'] === $this->tab_slug && isset( $_POST['post_id'] ) && isset( $_POST['wetu_id'] ) ) {
			$post_id     = false;
			$matching_id = false;
			$post_id     = sanitize_text_field( $_POST['post_id'] );
			$matching_id = sanitize_text_field( $_POST['wetu_id'] );

			add_post_meta( $post_id, 'lsx_wetu_id', $matching_id );
			$return = '<li class="post-' . $post_id . '"><span class="dashicons dashicons-yes"></span> <a target="_blank" href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a></li>';
		}

		print_r( $return );
		die();
	}

}

$lsx_wetu_importer_connect_accommodation = new LSX_WETU_Importer_Connect_Accommodation();
