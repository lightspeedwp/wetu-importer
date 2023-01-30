<?php
/**
 * The main plugin class.
 *
 * @package   LSX_WETU_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 */
/**
 * The Main plugin class.
 */
class LSX_WETU_Importer {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|Module_Template
	 */
	protected static $instance = null;

	/**
	 * The slug for this plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $plugin_slug = 'lsx-wetu-importer';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'default';

	/**
	 * The options for the plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;

	/**
	 * The url to import images from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $import_scaling_url = false;

	/**
	 * scale the images on import or not
	 *
	 * @since 0.0.1
	 *
	 * @var      boolean
	 */
	public $scale_images = false;

	/**
	 * The WETU API Key
	 */
	public $api_key = false;

	/**
	 * The post types this works with.
	 */
	public $post_types = array();

	/**
	 * The previously attached images
	 *
	 * @var      array()
	 */
	public $found_attachments = array();

	/**
	 * The gallery ids for the found attachements
	 *
	 * @var      array()
	 */
	public $gallery_meta = array();

	/**
	 * The post ids to clean up (make sure the connected items are only singular)
	 *
	 * @var      array()
	 */
	public $cleanup_posts = array();

	/**
	 * A post => parent relationship array.
	 *
	 * @var      array()
	 */
	public $relation_meta = array();

	/**
	 * Image Limit
	 *
	 * @var      int
	 */
	public $image_limit = false;

	/**
	 * the featured image id
	 *
	 * @var      int
	 */
	public $featured_image = false;

	/**
	 * the banner image
	 *
	 * @var      int
	 */
	public $banner_image = false;

	/**
	 * Holds the current import to display
	 *
	 * @var      int
	 */
	public $current_importer = false;

	/**
	 * if you ran a tour import then you will have accommodation and destination queued to sync as well.
	 *
	 * @var      int
	 */
	public $queued_imports = array();

	/**
	 * An Array to hold the items to queue
	 *
	 * @var      int
	 */
	public $import_queue = array();

	/**
	 * Holds the current post that is being imported. Use to check the content and excerpt.
	 *
	 * @var      int
	 */
	public $current_post = false;

	/**
	 * Holds the accommodation settings
	 *
	 * @var      int
	 */
	public $accommodation_settings = false;

	/**
	 * Holds the tour settings
	 *
	 * @var      int
	 */
	public $tour_settings = false;

	/**
	 * Holds the destination settings
	 *
	 * @var      int
	 */
	public $destination_settings = false;

	/**
	 * Hold the flag to let you know if the debug is enabled or not.
	 *
	 * @var      int
	 */
	public $debug_enabled = false;

	/**
	 * Hold the post columns object
	 *
	 * @var object LSX_WETU_Importer_Post_Columns()
	 */
	public $post_columns = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'compatible_version_check' ) );
		require_once LSX_WETU_IMPORTER_PATH . 'includes/helpers.php';

		// Don't run anything else in the plugin, if we're on an incompatible PHP version.
		if ( ! self::compatible_version() ) {
			return;
		}

		$this->set_variables();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 11 );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ), 20 );

		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-welcome.php';
		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-accommodation.php';
		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-destination.php';
		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-tours.php';
		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-settings.php';
		require_once LSX_WETU_IMPORTER_PATH . 'classes/class-wetu-automation.php';

		if ( isset( $this->options ) && isset( $this->options['enable_tour_ref_column'] ) && '' !== $this->options['enable_tour_ref_column'] ) {
			require_once LSX_WETU_IMPORTER_PATH . 'classes/class-lsx-wetu-importer-post-columns.php';
			$this->post_columns = LSX_WETU_Importer_Post_Columns::get_instance();
		}

		add_action( 'init', array( $this, 'load_class' ) );

		if ( 'default' !== $this->tab_slug ) {
			add_action( 'wp_ajax_lsx_tour_importer', array( $this, 'process_ajax_search' ) );
			add_action( 'wp_ajax_nopriv_lsx_tour_importer', array( $this, 'process_ajax_search' ) );

			add_action( 'wp_ajax_lsx_import_items', array( $this, 'process_ajax_import' ) );
			add_action( 'wp_ajax_nopriv_lsx_import_items', array( $this, 'process_ajax_import' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \tsp_child\classes\WETU_Automation()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'lsx-wetu-importer', false, basename( LSX_WETU_IMPORTER_PATH ) . '/languages' );
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		$this->post_types = array( 'accommodation', 'destination', 'tour' );
		$options          = lsx_wetu_get_options();

		// Set the options.
		$this->options = $options;

		$temp_options = get_option( '_lsx-to_settings', false );
		if ( false !== $temp_options ) {
			$this->accommodation_settings = $temp_options['accommodation'];
			$this->tour_settings          = $temp_options['tour'];
			$this->destination_settings   = $temp_options['destination'];
		}

		$this->api_key = false;

		if ( ! defined( 'WETU_API_KEY' ) ) {
			if ( isset( $options['api_key'] ) && '' !== $options['api_key'] ) {
				$this->api_key = $options['api_key'];
			}
		} else {
			$this->api_key = WETU_API_KEY;
		}

		// Set the tab slug.
		// @codingStandardsIgnoreLine
		if ( isset( $_GET['tab'] ) || ( defined( 'DOING_AJAX' ) && isset( $_POST['type'] ) ) ) {
			if ( isset( $_GET['tab'] ) ) {
				$this->tab_slug = sanitize_text_field( $_GET['tab'] );
			} else {
				// @codingStandardsIgnoreLine
				$this->tab_slug = sanitize_text_field( $_POST['type'] );
			}
		}

		// If any tours were queued.
		$this->queued_imports = get_option( 'lsx_wetu_importer_que', array() );

		// Set the scaling options.
		if ( isset( $this->options ) && isset( $this->options['image_scaling'] ) ) {
			$this->scale_images = true;

			$width = '1024';
			if ( isset( $this->options['width'] ) && '' !== $this->options['width'] ) {
				$width = $this->options['width'];
			}

			$height = '768';
			if ( isset( $this->options['height'] ) && '' !== $this->options['height'] ) {
				$height = $this->options['height'];
			}

			$cropping = 'w';
			if ( isset( $this->options['cropping'] ) && '' !== $this->options['cropping'] ) {
				$cropping = $this->options['cropping'];
			}

			$this->image_scaling_url = 'https://wetu.com/ImageHandler/' . $cropping . $width . 'x' . $height . '/';
		}

		if ( isset( $this->options ) && isset( $this->options['image_limit'] ) && '' !== $this->options['image_limit'] ) {
			$this->image_limit = $this->options['image_limit'];
		}
	}

	// COMPATABILITY FUNCTIONS.

	/**
	 * On plugin activation
	 *
	 * @since 1.0.0
	 */
	public static function register_activation_hook() {
		self::compatible_version_check_on_activation();
	}

	/**
	 * Check if the PHP version is compatible.
	 *
	 * @since 1.0.0
	 */
	public static function compatible_version() {
		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * The backup sanity check, in case the plugin is activated in a weird way,
	 * or the versions change after activation.
	 *
	 * @since 1.0.0
	 */
	public function compatible_version_check() {
		if ( ! self::compatible_version() ) {
			if ( is_plugin_active( plugin_basename( LSX_WETU_IMPORTER_CORE ) ) ) {
				deactivate_plugins( plugin_basename( LSX_WETU_IMPORTER_CORE ) );
				add_action( 'admin_notices', array( $this, 'compatible_version_notice' ) );

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Display the notice related with the older version from PHP.
	 *
	 * @since 1.0.0
	 */
	public function compatible_version_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'LSX Importer for Wetu Plugin requires PHP 5.6 or higher.', 'lsx-wetu-importer' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_html( $class ), esc_html( $message ) );
	}

	/**
	 * The primary sanity check, automatically disable the plugin on activation if it doesn't
	 * meet minimum requirements.
	 *
	 * @since 1.0.0
	 */
	public static function compatible_version_check_on_activation() {
		if ( ! self::compatible_version() ) {
			deactivate_plugins( plugin_basename( LSX_WETU_IMPORTER_CORE ) );
			wp_die( esc_html__( 'LSX Importer for Wetu Plugin requires PHP 5.6 or higher.', 'lsx-wetu-importer' ) );
		}
	}

	// DISPLAY FUNCTIONS.

	/**
	 * Load the importer class you want to use
	 */
	public function load_class() {
		switch ( $this->tab_slug ) {
			case 'accommodation':
				$this->current_importer = new LSX_WETU_Importer_Accommodation();
				break;

			case 'destination':
				$this->current_importer = new LSX_WETU_Importer_Destination();
				break;

			case 'tour':
				$this->current_importer = new LSX_WETU_Importer_Tours();
				break;

			case 'settings':
				$this->current_importer = LSX_WETU_Importer_Settings::get_instance();
				break;

			default:
				$this->current_importer = LSX_WETU_Importer_Welcome::get_instance();
				break;
		}
	}

	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
		add_submenu_page( 'tour-operator', esc_html__( 'Importer', 'tour-operator' ), esc_html__( 'Importer', 'tour-operator' ), 'manage_options', 'lsx-wetu-importer', array( $this, 'display_page' ) );
	}

	/**
	 * Enqueue the JS needed to contact wetu and return your result.
	 */
	public function admin_scripts() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$min = '';
		} else {
			$min = '.min';
		}

		$min = '';

		if ( is_admin() && isset( $_GET['page'] ) && $this->plugin_slug === $_GET['page'] ) {

			// wp_enqueue_style( 'datatables', LSX_WETU_IMPORTER_URL . 'assets/css/datatables' . $min . '.css', LSX_WETU_IMPORTER_VER, true );
			wp_enqueue_style( 'lsx-wetu-importer-style', LSX_WETU_IMPORTER_URL . 'assets/css/lsx-wetu-importer.css', LSX_WETU_IMPORTER_VER, true );

			if ( isset( $_GET['tab'] ) ) {
				wp_enqueue_script( 'datatables', LSX_WETU_IMPORTER_URL . 'assets/js/datatables' . $min . '.js', array( 'jquery' ), LSX_WETU_IMPORTER_VER, true );
				wp_enqueue_script( 'lsx-wetu-importers-script', LSX_WETU_IMPORTER_URL . 'assets/js/lsx-wetu-importer' . $min . '.js', array( 'jquery', 'datatables' ), LSX_WETU_IMPORTER_VER, true );

				wp_localize_script(
					'lsx-wetu-importers-script',
					'lsx_tour_importer_params',
					array(
						'ajax_url'   => admin_url( 'admin-ajax.php' ),
						'ajax_nonce' => wp_create_nonce( 'lsx_wetu_ajax_action' ),
					)
				);
			}
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<?php
			$this->navigation( $this->tab_slug );
			if ( 'default' !== $this->tab_slug && 'settings' !== $this->tab_slug ) {
				$this->wetu_status();
				$this->post_status_navigation();
			}
			$this->current_importer->display_page();
			?>
		</div>
		<?php
	}

	/**
	 * Outputs the post status navigation
	 *
	 * @return void
	 */
	public function post_status_navigation() {
		?>
		<ul class="subsubsub">
			<li class="searchform"><a class="current" href="#search"><?php esc_attr_e( 'Search', 'lsx-wetu-importer' ); ?></a> | </li>
			<li class="publish"><a href="#publish"><?php esc_attr_e( 'Published', 'lsx-wetu-importer' ); ?> <span class="count"> (<?php echo esc_attr( lsx_wetu_get_post_count( $this->tab_slug, 'publish ' ) ); ?>)</span></a> | </li>
			<li class="pending"><a href="#pending"><?php esc_attr_e( 'Pending', 'lsx-wetu-importer' ); ?> <span class="count"> (<?php echo esc_attr( lsx_wetu_get_post_count( $this->tab_slug, 'pending ' ) ); ?>)</span></a>| </li>
			<li class="draft"><a href="#draft"><?php esc_attr_e( 'Draft', 'lsx-wetu-importer' ); ?></a> <span class="count"> (<?php echo esc_attr( lsx_wetu_get_post_count( $this->tab_slug, 'draft ' ) ); ?>)</span></li>

			<?php if ( 'tour' === $this->tab_slug ) { ?>
				<li class="import"> | <a class="import search-toggle"  href="#import"><?php esc_attr_e( 'WETU', 'lsx-wetu-importer' ); ?> <span class="count"> (<?php echo esc_attr( lsx_wetu_get_tour_count() ); ?>)</span></a></li>
			<?php } elseif ( ! empty( $this->queued_imports ) ) { ?>
				<li class="import"> | <a class="import search-toggle"  href="#import"><?php esc_attr_e( 'WETU Queue', 'lsx-wetu-importer' ); ?> <span class="count"> (<?php echo esc_attr( lsx_wetu_get_queue_count( $this->tab_slug ) ); ?>)</span></a></li>
			<?php } ?>
		</ul>
		<a class="documentation" target="_blank"href="https://tour-operator.lsdev.biz/documentation/extension/wetu-importer/"><?php esc_attr_e( 'Documentation', 'lsx-wetu-importer' ); ?></a>
		<?php
	}

	/**
	 * Search Form
	 */
	public function search_form() {
		?>
		<form class="ajax-form" id="<?php echo esc_attr( $this->plugin_slug ); ?>-search-form" method="get" action="tools.php" data-type="<?php echo esc_attr( $this->tab_slug ); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

			<?php do_action( 'lsx_wetu_importer_search_form', $this ); ?>

			<div class="normal-search">
				<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php esc_html_e( 'Search', 'lsx-wetu-importer' ); ?>" />
			</div>

			<div class="advanced-search hidden" style="display:none;">
				<textarea rows="10" cols="40" name="bulk-keywords"></textarea>
				<input class="button button-primary submit" type="submit" value="<?php esc_attr_e( 'Search', 'lsx-wetu-importer' ); ?>" />
			</div>

			<div class="ajax-loader" style="display:none;width:100%;text-align:center;">
				<img style="width:64px;" src="<?php echo esc_url( LSX_WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif' ); ?>" />
			</div>

			<div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
				<img style="width:32px;" src="<?php echo esc_url( LSX_WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif' ); ?>" />
			</div>

			<a class="button advanced-search-toggle" href="#"><?php esc_html_e( 'Bulk Search', 'lsx-wetu-importer' ); ?></a>
		</form>
		<?php
	}

	/**
	 * The header of the item list
	 */
	public function table_header() {
		?>
		<thead>
		<tr>
			<th style="" class="manage-column column-cb check-column no-sort" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'lsx-wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th style="" class="manage-column column-order " id="order"><?php esc_attr_e( 'Order', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-title " id="title" style="width:50%;" scope="col"><?php esc_attr_e( 'Title', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-date" id="date" scope="col"><?php esc_attr_e( 'Date', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-ssid" id="ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'lsx-wetu-importer' ); ?></th>
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
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'lsx-wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th style="" class="manage-column column-order "><?php esc_attr_e( 'Order', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-title" scope="col"><?php esc_attr_e( 'Title', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-date" scope="col"><?php esc_attr_e( 'Date', 'lsx-wetu-importer' ); ?></th>
			<th style="" class="manage-column column-ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'lsx-wetu-importer' ); ?></th>
		</tr>
		</tfoot>
		<?php
	}

	/**
	 * Displays the importers navigation.
	 *
	 * @param $tab string
	 */
	public function navigation( $tab = '' ) {
		$post_types = array(
			'tour'          => esc_attr( 'Tours', 'lsx-wetu-importer' ),
			'accommodation' => esc_attr( 'Accommodation', 'lsx-wetu-importer' ),
			'destination'   => esc_attr( 'Destinations', 'lsx-wetu-importer' ),
		);

		echo wp_kses_post( '<div class="wp-filter">' );
		echo wp_kses_post( '<ul class="filter-links">' );
		echo wp_kses_post( '<li><a class="' . $this->itemd( $tab, 'default', 'current', false ) . '" href="' . admin_url( 'admin.php' ) . '?page=' . $this->plugin_slug . '">' . esc_attr__( 'Home', 'lsx-wetu-importer' ) . '</a></li>' );

		foreach ( $post_types as $post_type => $label ) {
			echo wp_kses_post( ' | <li><a class="' . $this->itemd( $tab, $post_type, 'current', false ) . '" href="' . admin_url( 'admin.php' ) . '?page=' . $this->plugin_slug . '&tab=' . $post_type . '">' . $label . '</a></li>' );
		}

		echo wp_kses_post( ' | <li><a class="' . $this->itemd( $tab, 'settings', 'current', false ) . '" href="' . admin_url( 'admin.php' ) . '?page=' . $this->plugin_slug . '&tab=settings">' . esc_attr__( 'Settings', 'lsx-wetu-importer' ) . '</a></li>' );
		echo wp_kses_post( '</ul> </div>' );
	}

	/**
	 * Wetu Status Bar.
	 */
	public function wetu_status() {
		$tours = get_transient( 'lsx_ti_tours' );
		echo '<div class="wetu-status tour-wetu-status"><h3>' . esc_html__( 'Wetu Status', 'lsx-wetu-importer' ) . ' - ';

		if ( '' === $tours || false === $tours || isset( $_GET['refresh_tours'] ) ) {
			$result = $this->update_options();
			if ( true === $result ) {
				echo '<span style="color:green;">' . esc_attr( 'Connected', 'lsx-wetu-importer' ) . '</span>';
				echo ' - <small><a href="#">' . esc_attr( 'Refresh', 'lsx-wetu-importer' ) . '</a></small>';
			} else {
				echo '<span style="color:red;">' . wp_kses_post( $result ) . '</span>';
			}
		} else {
			echo '<span style="color:green;">' . esc_attr( 'Connected', 'lsx-wetu-importer' ) . '</span> - <small><a href="#">' . esc_attr( 'Refresh', 'lsx-wetu-importer' ) . '</a></small>';
		}
		echo '</h3>';
		echo '</div>';
	}

	/**
	 * Set_taxonomy with some terms
	 */
	public function team_member_checkboxes( $selected = array() ) {
		if ( post_type_exists( 'team' ) ) {
			?>
			<ul>
				<?php
					$team_args = array(
						'post_type'   => 'team',
						'post_status' => 'publish',
						'nopagin'     => true,
						'fields'      => 'ids',
					);

					$team_members = new WP_Query( $team_args );

					if ( $team_members->have_posts() ) {
						foreach ( $team_members->posts as $member ) {
							?>
							<li><input class="team" <?php $this->checked( $selected, $member ); ?> type="checkbox" value="<?php echo esc_attr( $member ); ?>" /> <?php echo wp_kses_post( get_the_title( $member ) ); ?></li>
							<?php
						}
					} else {
						?>
						<li><input class="team" type="checkbox" value="0" /> <?php esc_html_e( 'None', 'lsx-wetu-importer' ); ?></li>
						<?php
					}
					?>
			</ul>
			<?php
		}
	}


	// GENERAL FUNCTIONS.

	/**
	 * Checks to see if an item is checked.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $echo bool
	 */
	public function checked( $haystack = false, $needle = '', $echo = true ) {
		$return = $this->itemd( $haystack, $needle, 'checked', false );

		if ( '' !== $return ) {
			if ( true === $echo ) {
				echo wp_kses_post( $return );
			} else {
				return $return;
			}
		}
	}

	/**
	 * Checks to see if an item is checked.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $echo bool
	 */
	public function selected( $haystack = false, $needle = '', $echo = true ) {
		$return = $this->itemd( $haystack, $needle, 'selected' );

		if ( '' !== $return ) {
			if ( true === $echo ) {
				echo wp_kses_post( $return );
			} else {
				return $return;
			}
		}
	}

	/**
	 * Checks to see if an item is selected. If $echo is false,  it will return the $type if conditions are true.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $type string
	 * @param $wrap bool
	 * @return $html string
	 */
	public function itemd( $haystack = false, $needle = '', $type = '', $wrap = true ) {
		$html = '';

		if ( '' !== $type ) {
			if ( ! is_array( $haystack ) ) {
				$haystack = array( $haystack );
			}
			if ( in_array( $needle, $haystack ) ) {
				if ( true === $wrap || 'true' === $wrap ) {
					$html = $type . '="' . $type . '"';
				} else {
					$html = $type;
				}
			}
		}

		return $html;
	}

	/**
	 * Grabs any attachments for the current item
	 */
	public function find_attachments( $id = false ) {
		if ( false !== $id ) {
			if ( empty( $this->found_attachments ) ) {
				$attachments_args = array(
					'post_parent'    => $id,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'order'          => 'ASC',
					'nopagin'        => 'true',
					'posts_per_page' => '-1',
				);

				$attachments = new WP_Query( $attachments_args );

				if ( $attachments->have_posts() ) {
					foreach ( $attachments->posts as $attachment ) {
						$this->found_attachments[ $attachment->ID ] = str_replace( array( '.jpg', '.png', '.jpeg' ), '', $attachment->post_title );
					}
				}
			}
		}
	}

	// CUSTOM FIELD FUNCTIONS.

	/**
	 * Saves the room data
	 */
	public function save_custom_field( $value = false, $meta_key, $id, $decrease = false, $unique = true ) {
		if ( false !== $value ) {
			if ( false !== $decrease ) {
				$value = intval( $value );
				$value--;
			}

			$prev = get_post_meta( $id, $meta_key, true );

			if ( false !== $id && '0' !== $id && false !== $prev && true === $unique ) {
				update_post_meta( $id, $meta_key, $value, $prev );
			} else {
				add_post_meta( $id, $meta_key, $value, $unique );
			}
		}
	}

	/**
	 * Grabs the custom fields,  and resaves an array of unique items.
	 */
	public function cleanup_posts() {
		if ( ! empty( $this->cleanup_posts ) ) {

			foreach ( $this->cleanup_posts as $id => $key ) {
				$prev_items = get_post_meta( $id, $key, false );
				$new_items  = array_unique( $prev_items );
				delete_post_meta( $id, $key );

				foreach ( $new_items as $new_item ) {
					add_post_meta( $id, $key, $new_item, false );
				}
			}
		}
	}

	// TAXONOMY FUNCTIONS.

	/**
	 * Set_taxonomy with some terms
	 */
	public function set_taxonomy( $taxonomy, $terms, $id ) {
		$result = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $k ) {
				if ( $id ) {
					$term = term_exists( trim( $k ), $tax );
					if ( ! $term ) {
						$term = wp_insert_term( trim( $k ), $tax );

						if ( is_wp_error( $term ) ) {
							echo wp_kses_post( $term->get_error_message() );
						} else {
							wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy, true );
						}
					} else {
						wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy, true );
					}
				} else {
					$result[] = trim( $k );
				}
			}
		}
		return $result;
	}

	/**
	 * Sets the terms of the current post
	 *
	 * @param boolean $id
	 * @param boolean $name
	 * @param boolean $taxonomy
	 * @param boolean $parent
	 * @return void
	 */
	public function set_term( $id = false, $name = false, $taxonomy = false, $parent = false ) {
		$term = term_exists( $name, $taxonomy );
		if ( ! $term ) {
			if ( false !== $parent ) {
				$parent = array(
					'parent' => $parent,
				);
			}
			$term = wp_insert_term( trim( $name ), $taxonomy, $parent );

			if ( is_wp_error( $term ) ) {
				echo wp_kses_post( $term->get_error_message() );
			} else {
				wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy, true );
			}
		} else {
			wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy, true );
		}

		return $term['term_id'];
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function taxonomy_checkboxes( $taxonomy = false, $selected = array() ) {
		$return = '';

		if ( false !== $taxonomy ) {
			$return .= '<ul>';
			$terms   = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$return .= '<li><input class="' . $taxonomy . '" ' . $this->checked( $selected, $term->term_id, false ) . ' type="checkbox" value="' . $term->term_id . '" /> ' . $term->name . '</li>';
				}
			} else {
				$return .= '<li><input type="checkbox" value="" /> ' . __( 'None', 'lsx-wetu-importer' ) . '</li>';
			}

			$return .= '</ul>';
		}

		return $return;
	}

	// MAP FUNCTIONS

	/**
	 * Saves the longitude and lattitude, as well as sets the map marker.
	 */
	public function set_map_data( $data, $id, $zoom = '10' ) {
		$longitude = false;
		$latitude  = false;
		$address   = false;

		if ( isset( $data[0]['position'] ) ) {
			if ( isset( $data[0]['position']['driving_latitude'] ) ) {
				$latitude = $data[0]['position']['driving_latitude'];
			} elseif ( isset( $data[0]['position']['latitude'] ) ) {
				$latitude = $data[0]['position']['latitude'];
			}

			if ( isset( $data[0]['position']['driving_longitude'] ) ) {
				$longitude = $data[0]['position']['driving_longitude'];
			} elseif ( isset( $data[0]['position']['longitude'] ) ) {
				$longitude = $data[0]['position']['longitude'];
			}
		}

		if ( isset( $data[0]['content'] ) && isset( $data[0]['content']['contact_information'] ) ) {
			if ( isset( $data[0]['content']['contact_information']['address'] ) ) {
				$address = strip_tags( $data[0]['content']['contact_information']['address'] );
				$address = explode( "\n", $address );

				foreach ( $address as $bitkey => $bit ) {
					$bit = ltrim( rtrim( $bit ) );

					if ( false === $bit || '' === $bit || null === $bit || empty( $bit ) ) {
						unset( $address[ $bitkey ] );
					}
				}

				$address = implode( ', ', $address );
				$address = str_replace( ', , ', ', ', $address );
			}
		}

		if ( false !== $longitude ) {
			$location_data = array(
				'address'   => (string) $address,
				'lat'       => (string) $latitude,
				'long'      => (string) $longitude,
				'zoom'      => (string) $zoom,
				'elevation' => '',
			);

			if ( false !== $id && '0' !== $id ) {
				$prev = get_post_meta( $id, 'location', true );
				update_post_meta( $id, 'location', $location_data, $prev );
			} else {
				add_post_meta( $id, 'location', $location_data, true );
			}
		}
	}

	// IMAGE FUNCTIONS

	/**
	 * Creates the main gallery data
	 */
	public function set_featured_image( $data, $id ) {
		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			$this->featured_image = $this->attach_image(
				$data[0]['content']['images'][0],
				$id,
				array(
					'width'    => '800',
					'height'   => '600',
					'cropping' => 'h',
				)
			);

			if ( false !== $this->featured_image ) {
				delete_post_meta( $id, '_thumbnail_id' );
				add_post_meta( $id, '_thumbnail_id', $this->featured_image, true );
			}
		}
	}

	/**
	 * Sets a banner image
	 */
	public function set_banner_image( $data, $id, $content = array( 'none' ) ) {
		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			if ( in_array( 'unique_banner_image', $content ) && isset( $data[0]['destination_image'] ) && is_array( $data[0]['destination_image'] ) ) {
				$temp_banner = $this->attach_image(
					$data[0]['destination_image'],
					$id,
					array(
						'width'    => '1920',
						'height'   => '600',
						'cropping' => 'c',
					)
				);
			} else {
				$temp_banner = $this->attach_image(
					$data[0]['content']['images'][1],
					$id,
					array(
						'width'    => '1920',
						'height'   => '600',
						'cropping' => 'c',
					)
				);
			}

			if ( false !== $temp_banner ) {
				$this->banner_image = $temp_banner;

				delete_post_meta( $id, 'image_group' );

				$new_banner = array(
					'banner_image' => array(
						'cmb-field-0' => $this->banner_image,
					),
				);

				add_post_meta( $id, 'image_group', $new_banner, true );
			}
		}
	}

	/**
	 * Checks if the current image is being used as a thumbnail somewhere else.
	 */
	public function is_image_being_used( $image_id = '', $post_id = '' ) {
		global $wpdb;
		$being_used = false;
		if ( '' !== $image_id ) {
			$sql     = "SELECT * FROM `{$wpdb->postmeta}` WHERE `post_id` != {$post_id} AND `meta_key` LIKE '_thumbnail_id' AND `meta_value` LIKE '{$image_id}'";
			$results = $wpdb->query( $sql );
			if ( false !== $results && ! empty( $results ) ) {
				$being_used = true;
			}
		}
		return $being_used;
	}

	/**
	 * Creates the main gallery data
	 */
	public function create_main_gallery( $data, $id ) {
		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			if ( isset( $this->options['image_replacing'] ) && 'on' === $this->options['image_replacing'] ) {
				$current_gallery = get_post_meta( $id, 'gallery', false );

				if ( false !== $current_gallery && ! empty( $current_gallery ) ) {
					foreach ( $current_gallery as $g ) {
						delete_post_meta( $id, 'gallery', $g );

						if ( 'attachment' === get_post_type( $g ) && false === $this->is_image_being_used( $g, $id ) ) {
							wp_delete_attachment( $g, true );
						}
					}
				}
			}

			$counter = 0;

			foreach ( $data[0]['content']['images'] as $image_data ) {
				//disable_destination_image_featured
				//disable_destination_image_banner

				if ( ( 0 === $counter && false !== $this->featured_image ) || ( 1 === $counter && false !== $this->banner_image ) ) {
					$counter++;

					if ( false !== $this->image_limit && false !== $this->image_limit ) {
						$this->image_limit++;
					}

					continue;
				}

				if ( false !== $this->image_limit && $counter >= $this->image_limit ) {
					continue;
				}

				$this->gallery_meta[] = $this->attach_image( $image_data, $id );
				$counter++;
			}

			if ( ! empty( $this->gallery_meta ) ) {
				delete_post_meta( $id, 'gallery' );
				$this->gallery_meta = array_unique( $this->gallery_meta );

				foreach ( $this->gallery_meta as $gallery_id ) {
					if ( false !== $gallery_id && '' !== $gallery_id && ! is_array( $gallery_id ) ) {
						add_post_meta( $id, 'gallery', $gallery_id, false );
					}
				}
			}
		}
	}

	/**
	 * search_form
	 */
	public function get_scaling_url( $args = array() ) {
		$defaults = array(
			'width'    => '1024',
			'height'   => '768',
			// 'cropping' => 'w',
			'cropping' => 'h',
		);

		if ( false !== $this->options ) {
			if ( isset( $this->options['width'] ) && '' !== $this->options['width'] ) {
				$defaults['width'] = $this->options['width'];
			}

			if ( isset( $this->options['height'] ) && '' !== $this->options['height'] ) {
				$defaults['height'] = $this->options['height'];
			}

			if ( isset( $this->options['cropping'] ) && '' !== $this->options['cropping'] ) {
				$defaults['cropping'] = $this->options['cropping'];
			}
		}

		$args     = wp_parse_args( $args, $defaults );
		$cropping = $args['cropping'];
		$width    = $args['width'];
		$height   = $args['height'];

		return 'https://wetu.com/ImageHandler/' . $cropping . $width . 'x' . $height . '/';
	}

	/**
	 * Attaches 1 image
	 */
	public function attach_image( $v = false, $parent_id, $image_sizes = false, $banner = false ) {
		if ( false !== $v ) {
			$temp_fragment = explode( '/', $v['url_fragment'] );
			$url_filename  = $temp_fragment[ count( $temp_fragment ) - 1 ];
			$url_filename  = str_replace( array( '.jpg', '.png', '.jpeg' ), '', $url_filename );
			$url_filename  = trim( $url_filename );
			$title         = $url_filename;
			$url_filename  = str_replace( ' ', '_', $url_filename );

			if ( ! isset( $this->options['image_replacing'] ) && in_array( $url_filename, $this->found_attachments ) ) {
				return array_search( $url_filename, $this->found_attachments );
			}

			$postdata = array();

			if ( empty( $v['label'] ) ) {
				$v['label'] = '';
			}

			if ( ! empty( $v['description'] ) ) {
				$desc    = wp_strip_all_tags( $v['description'] );
				$posdata = array(
					'post_excerpt' => $desc,
				);
			}

			if ( ! empty( $v['section'] ) ) {
				$desc    = wp_strip_all_tags( $v['section'] );
				$posdata = array(
					'post_excerpt' => $desc,
				);
			}

			$attach_id = null;
			// Resizor - add option to setting if required.
			$fragment  = str_replace( ' ', '%20', $v['url_fragment'] );
			$url       = $this->get_scaling_url( $image_sizes ) . $fragment;
			$attach_id = $this->attach_external_image2( $url, $parent_id, '', $v['label'], $postdata );
			if ( ! empty( $attach_id ) ) {
				$this->found_attachments[ $attach_id ] = $url_filename;
				add_post_meta( $attach_id, 'lsx_wetu_id', $v['url_fragment'], true );
				return $attach_id;
			}
		}
		return false;
	}

	public function attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
		if ( ! $url || ! $post_id ) {
			return new WP_Error( 'missing', 'Need a valid URL and post ID...' ); }
		$att_id = false;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		// Download file to temp location, returns full server path to temp file.

		$tmp   = tempnam( '/tmp', 'FOO' );
		$image = wp_remote_get( $url );
		if ( ! is_wp_error( $image ) && ! empty( $image ) && isset( $image['response'] ) && isset( $image['response']['code'] ) && 200 === $image['response']['code'] ) {
			file_put_contents( $tmp, $image['body'] );
			chmod( $tmp, '777' );

			preg_match( '/[^\?]+\.(tif|TIFF|jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|pdf|PDF|bmp|BMP)/', $url, $matches );    // fix file filename for query strings
			$url_filename = basename( $matches[0] );
			$url_filename = str_replace( '%20', '_', $url_filename );
			// extract filename from url for title
			$url_type = wp_check_filetype( $url_filename );                                           // determine file type (ext and mime/type)

			// override filename if given, reconstruct server path.
			if ( ! empty( $filename ) && ' ' != $filename ) {
				$filename = sanitize_file_name( $filename );
				$tmppath  = pathinfo( $tmp );

				$extension = '';
				if ( isset( $tmppath['extension'] ) ) {
					$extension = $tmppath['extension'];
				}

				$new = $tmppath['dirname'] . '/' . $filename . '.' . $extension;
				rename( $tmp, $new );                                                                 // renames temp file on server
				$tmp = $new;                                                                        // push new filename (in path) to be used in file array later
			}

			// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using).
			$file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

			if ( ! empty( $filename ) && ' ' != $filename ) {
				$file_array['name'] = $filename . '.' . $url_type['ext'];                           // user given filename for title, add original URL extension
			} else {
				$file_array['name'] = $url_filename;                                                // just use original URL filename
			}

			// set additional wp_posts columns.
			if ( empty( $post_data['post_title'] ) ) {

				$url_filename = str_replace( '%20', ' ', $url_filename );

				$post_data['post_title'] = basename( $url_filename, '.' . $url_type['ext'] );         // just use the original filename (no extension)
			}

			// make sure gets tied to parent.
			if ( empty( $post_data['post_parent'] ) ) {
				$post_data['post_parent'] = $post_id;
			}

			// do the validation and storage stuff.
			$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

			// If error storing permanently, unlink.
			if ( is_wp_error( $att_id ) ) {
				unlink( $file_array['tmp_name'] );
				return false;
			}
		}
		return $att_id;
	}

	// AJAX FUNCTIONS
	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$this->current_importer->process_ajax_search();
		die();
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import() {
		$this->current_importer->process_ajax_import();
		die();
	}

	/**
	 * Formats the row for the completed list.
	 */
	public function format_completed_row( $response ) {
		echo wp_kses_post( '<li class="post-' . $response . '"><span class="dashicons dashicons-yes"></span> <a target="_blank" href="' . get_permalink( $response ) . '">' . get_the_title( $response ) . '</a></li>' );
	}

	/**
	 * Formats the error.
	 */
	public function format_error( $response ) {
		echo wp_kses_post( '<li class="post-error"><span class="dashicons dashicons-no"></span>' . $response . '</li>' );
	}

	/**
	 * Does a multine search
	 */
	public function multineedle_stripos( $haystack, $needles, $offset = 0 ) {
		$found        = false;
		$needle_count = count( $needles );

		foreach ( $needles as $needle ) {
			if ( false !== stripos( $haystack, $needle, $offset ) ) {
				$found[] = true;
			}
		}

		if ( false !== $found && count( $found ) === $needle_count ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 */
	public function find_current_accommodation( $post_type = 'accommodation' ) {
		global $wpdb;
		$return = array();

		// @codingStandardsIgnoreStart
		$current_accommodation = $wpdb->get_results("
			SELECT key1.post_id,key1.meta_value
			FROM {$wpdb->postmeta} key1

			INNER JOIN  {$wpdb->posts} key2
			ON key1.post_id = key2.ID

			WHERE key1.meta_key = 'lsx_wetu_id'
			AND key2.post_type = '{$post_type}'

			LIMIT 0,5000
		");
		// @codingStandardsIgnoreEnd

		if ( null !== $current_accommodation && ! empty( $current_accommodation ) ) {
			foreach ( $current_accommodation as $accom ) {
				$return[ $accom->meta_value ] = $accom;
			}
		}

		return $return;
	}

	/**
	 * Set the Video date
	 */
	public function set_video_data( $data, $id ) {
		if ( ! empty( $data[0]['content']['youtube_videos'] ) && is_array( $data[0]['content']['youtube_videos'] ) ) {
			$videos = false;

			foreach ( $data[0]['content']['youtube_videos'] as $video ) {
				$temp_video = array();

				if ( isset( $video['label'] ) ) {
					$temp_video['title'] = $video['label'];
				}
				if ( isset( $video['description'] ) ) {
					$temp_video['description'] = strip_tags( $video['description'] );
				}
				if ( isset( $video['url'] ) ) {
					$temp_video['url'] = $video['url'];
				}

				$temp_video['thumbnail'] = '';
				$videos[]                = $temp_video;
			}

			if ( false !== $id && '0' !== $id ) {
				delete_post_meta( $id, 'videos' );
			}

			foreach ( $videos as $video ) {
				add_post_meta( $id, 'videos', $video, false );
			}
		}
	}

	public function shuffle_assoc( &$array ) {
		$new  = array();
		$keys = array_keys( $array );

		shuffle( $keys );

		foreach ( $keys as $key ) {
			$new[ $key ] = $array[ $key ];
		}

		$array = $new;

		return true;
	}

	/**
	 * Save the list of Tours into an option
	 */
	public function update_options() {
		$own     = '';
		$options = array();
		delete_option( 'lsx_ti_tours_api_options' );

		if ( isset( $_GET['own'] ) ) {
			$this->current_importer->url_qs .= '&own=true';
			$options[]                       = 'own';
		}

		if ( isset( $_GET['type'] ) && 'allitineraries' !== $_GET['type'] ) {
			$this->current_importer->url_qs .= '&type=' . $_GET['type'];
			$options[]                       = $_GET['type'];
		} else {
			$options[]                       = 'sample';
			$this->current_importer->url_qs .= '&type=sample';
		} 

		$url  = str_replace( 'Pins', 'Itinerary', $this->current_importer->url . '/V8/List?' . $this->current_importer->url_qs );
		$url .= '&results=2000';

		$url = apply_filters( 'lsx_wetu_tour_refresh_url', $url );

		add_option( 'lsx_ti_tours_api_options', $options );
		$data  = wp_remote_get( $url );
		$tours = json_decode( wp_remote_retrieve_body( $data ), true );

		if ( isset( $tours['error'] ) ) {
			return $tours['error'];
		} elseif ( isset( $tours['itineraries'] ) && ! empty( $tours['itineraries'] ) ) {
			set_transient( 'lsx_ti_tours', $tours['itineraries'], 60 * 60 * 4 );
			return true;
		}
	}

	/**
	 * Gets the post_id from the key
	 *
	 * @param boolean $wetu_id
	 * @return string
	 */
	public function get_post_id_by_key_value( $wetu_id = false ) {
		global $wpdb;
		$id = false;
		if ( false !== $wetu_id && '' !== $wetu_id ) {
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM `$wpdb->postmeta` WHERE `meta_key` = 'lsx_wetu_id' AND `meta_value` = '%s'", array( $wetu_id ) ) );
			if ( false !== $result && ! empty( $result ) ) {
				$id = $result;
			}
		}
		return $id;
	}
	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member( $id, $team_members ) {
		delete_post_meta( $id, 'team_to_' . $this->tab_slug );

		foreach ( $team_members as $team ) {
			add_post_meta( $id, 'team_to_' . $this->tab_slug, $team );
		}
	}
}

$lsx_wetu_importer = LSX_WETU_Importer::get_instance();
