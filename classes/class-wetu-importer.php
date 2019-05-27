<?php
/**
 * @package   WETU_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer {

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
	public $plugin_slug = 'wetu-importer';

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
	 * The WETU API Username
	 */
	public $api_username = false;

	/**
	 * The WETU API Password
	 */
	public $api_password = false;

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
	 * Logger Class
	 *
	 * @var      \lsx\LSX_Logger
	 */
	public $logger = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'compatible_version_check' ) );

		// Don't run anything else in the plugin, if we're on an incompatible PHP version
		if ( ! self::compatible_version() ) {
			return;
		}

		$this->set_variables();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) ,11 );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ),20 );

		require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer-accommodation.php' );
		require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer-destination.php' );
		require_once( WETU_IMPORTER_PATH . 'classes/class-wetu-importer-tours.php' );

		add_action( 'init', array( $this, 'load_class' ) );

		if ( 'default' !== $this->tab_slug ) {
			add_action( 'wp_ajax_lsx_tour_importer',array( $this, 'process_ajax_search' ) );
			add_action( 'wp_ajax_nopriv_lsx_tour_importer',array( $this, 'process_ajax_search' ) );

			add_action( 'wp_ajax_lsx_import_items',array( $this, 'process_ajax_import' ) );
			add_action( 'wp_ajax_nopriv_lsx_import_items',array( $this, 'process_ajax_import' ) );
		}
	}

	// ACTIVATION FUNCTIONS

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wetu-importer', false, basename( WETU_IMPORTER_PATH ) . '/languages' );
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		$this->post_types = array( 'accommodation','destination','tour' );
		$temp_options = get_option( '_lsx-to_settings',false );

		//Set the options.
		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];

			$this->accommodation_settings = $temp_options['accommodation'];
			$this->tour_settings = $temp_options['tour'];
			$this->destination_settings = $temp_options['destination'];

			$this->api_key = false;
			$this->api_username = false;
			$this->api_password = false;

			/*if ( false !== $this->options['enable_debug'] ) {
				$this->debug_enabled = true;
				$this->logger = \lsx\LSX_Logger::init();
			}*/

			if ( ! defined( 'WETU_API_KEY' ) ) {
				if ( isset( $temp_options['api']['wetu_api_key'] ) && '' !== $temp_options['api']['wetu_api_key'] ) {
					$this->api_key = $temp_options['api']['wetu_api_key'];
				}
				if ( isset( $temp_options['api']['wetu_api_username'] ) && '' !== $temp_options['api']['wetu_api_username'] ) {
					$this->api_username = $temp_options['api']['wetu_api_username'];
				}
				if ( isset( $temp_options['api']['wetu_api_password'] ) && '' !== $temp_options['api']['wetu_api_password'] ) {
					$this->api_password = $temp_options['api']['wetu_api_password'];
				}
			} else {
				$this->api_key = WETU_API_KEY;
			}

			//Set the tab slug
			// @codingStandardsIgnoreLine
			if ( isset( $_GET['tab'] ) || isset( $_POST['type'] ) ) {
				if ( isset( $_GET['tab'] ) ) {
					$this->tab_slug = $_GET['tab'];
				} else {
					// @codingStandardsIgnoreLine
					$this->tab_slug = $_POST['type'];
				}

				//If any tours were queued
				$this->queued_imports = get_option( 'wetu_importer_que', array() );
			}

			//Set the scaling options
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
	}

	// COMPATABILITY FUNCTIONS

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
			if ( is_plugin_active( plugin_basename( WETU_IMPORTER_CORE ) ) ) {
				deactivate_plugins( plugin_basename( WETU_IMPORTER_CORE ) );
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
		$class = 'notice notice-error';
		$message = esc_html__( 'Wetu Importer Plugin requires PHP 5.6 or higher.', 'wetu-importer' );
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
			deactivate_plugins( plugin_basename( WETU_IMPORTER_CORE ) );
			wp_die( esc_html__( 'Wetu Importer Plugin requires PHP 5.6 or higher.', 'wetu-importer' ) );
		}
	}

	// DISPLAY FUNCTIONS

	/*
     * Load the importer class you want to use
     */
	public function load_class() {
		switch ( $this->tab_slug ) {
			case 'accommodation':
				$this->current_importer = new WETU_Importer_Accommodation();
				break;

			case 'destination':
				$this->current_importer = new WETU_Importer_Destination();
				break;

			case 'tour':
				$this->current_importer = new WETU_Importer_Tours();
				break;

			default:
				$this->current_importer = false;
				break;
		}
	}

	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
		add_submenu_page( 'tour-operator',esc_html__( 'Importer', 'tour-operator' ), esc_html__( 'Importer', 'tour-operator' ), 'manage_options', 'wetu-importer', array( $this, 'display_page' ) );
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
			wp_enqueue_script( 'wetu-importers-script', WETU_IMPORTER_URL . 'assets/js/wetu-importer' . $min . '.js', array( 'jquery' ), WETU_IMPORTER_VER, true );

			wp_localize_script( 'wetu-importers-script', 'lsx_tour_importer_params', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<?php
				// @codingStandardsIgnoreLine
				screen_icon();
			?>

			<?php if ( ! is_object( $this->current_importer ) ) {
				?>
				<h2><?php esc_html_e( 'Welcome to the LSX Wetu Importer', 'wetu-importer' ); ?></h2>
				<p>If this is the first time you are running the import, then follow the steps below.</p>
				<ul>
					<li>Step 1 - Import your <a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=tour"><?php esc_html_e( 'Tours', 'wetu-importer' ); ?></a></li>
					<li>Step 2 - The tour import will have created draft <a href="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=accommodation"><?php esc_html_e( 'accommodation', 'wetu-importer' ); ?></a> that will need to be imported.</li>
					<li>Step 3 - Lastly import the <a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=destination"><?php esc_html_e( 'destinations', 'wetu-importer' ); ?></a> draft posts created during the previous two steps.</li>
				</ul>

				<?php /*<h3><?php esc_html_e('Additional Tools', 'wetu-importer'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=connect_accommodation"><?php esc_html_e('Connect Accommodation', 'wetu-importer'); ?></a> <small><?php esc_html_e('If you already have accommodation, you can "connect" it with its WETU counter part, so it works with the importer.', 'wetu-importer'); ?></small></li>
					<?php if(class_exists('Lsx_Banners')){ ?>
                        <li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=banners"><?php esc_html_e('Sync High Res Banner Images', 'wetu-importer'); ?></a></li>
					<?php } ?>
                </ul>
				<?php*/
			} else {
				$this->current_importer->display_page();
			}; ?>
		</div>
		<?php
	}

	/**
	 * search_form
	 */
	public function search_form() {
		?>
		<form class="ajax-form" id="<?php echo esc_attr( $this->plugin_slug ); ?>-search-form" method="get" action="tools.php" data-type="<?php echo esc_attr( $this->tab_slug ); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

			<h3><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Search', 'wetu-importer' ); ?></h3>

			<?php do_action( 'wetu_importer_search_form',$this ); ?>

			<div class="normal-search">
				<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php esc_html_e( 'Search', 'wetu-importer' ); ?>" />
			</div>

			<div class="advanced-search hidden" style="display:none;">
				<p><?php esc_html_e( 'Enter several keywords, each on a new line.', 'wetu-importer' ); ?></p>
				<textarea rows="10" cols="40" name="bulk-keywords"></textarea>
				<input class="button button-primary submit" type="submit" value="<?php esc_attr_e( 'Search', 'wetu-importer' ); ?>" />
			</div>

			<p>
				<a class="advanced-search-toggle" href="#"><?php esc_html_e( 'Bulk Search', 'wetu-importer' ); ?></a> |
				<a class="published search-toggle" href="#publish"><?php esc_attr_e( 'Published', 'wetu-importer' ); ?></a> |
				<a class="pending search-toggle"  href="#pending"><?php esc_attr_e( 'Pending', 'wetu-importer' ); ?></a> |
				<a class="draft search-toggle"  href="#draft"><?php esc_attr_e( 'Draft', 'wetu-importer' ); ?></a>

				<?php if ( 'tour' === $this->tab_slug ) { ?>
					| <a class="import search-toggle"  href="#import"><?php esc_attr_e( 'WETU', 'wetu-importer' ); ?></a>
				<?php } else if ( ! empty( $this->queued_imports ) ) { ?>
					| <a class="import search-toggle"  href="#import"><?php esc_attr_e( 'WETU Queue', 'wetu-importer' ); ?></a>
				<?php } ?>
			</p>

			<div class="ajax-loader" style="display:none;width:100%;text-align:center;">
				<img style="width:64px;" src="<?php echo esc_url( WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif' ); ?>" />
			</div>

			<div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
				<img style="width:32px;" src="<?php echo esc_url( WETU_IMPORTER_URL . 'assets/images/ajaxloader.gif' ); ?>" />
			</div>
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
			<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th style="" class="manage-column column-title " id="title" style="width:50%;" scope="col">Title</th>
			<th style="" class="manage-column column-date" id="date" scope="col">Date</th>
			<th style="" class="manage-column column-ssid" id="ssid" scope="col">WETU ID</th>
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
			<th style="" class="manage-column column-title" scope="col">Title</th>
			<th style="" class="manage-column column-date" scope="col">Date</th>
			<th style="" class="manage-column column-ssid" scope="col">WETU ID</th>
		</tr>
		</tfoot>
		<?php
	}

	/**
	 * Displays the importers navigation
	 *
	 * @param $tab string
	 */
	public function navigation( $tab = '' ) {
		$post_types = array(
			'tour'              => esc_attr( 'Tours', 'wetu-importer' ),
			'accommodation'     => esc_attr( 'Accommodation', 'wetu-importer' ),
			'destination'       => esc_attr( 'Destinations', 'wetu-importer' ),
		);

		// @codingStandardsIgnoreLine
		echo '<div class="wet-navigation"><div class="subsubsub"><a class="' . $this->itemd( $tab, '', 'current', false ) . '" href="' . admin_url( 'admin.php' ) . '?page=' . $this->plugin_slug . '">' . esc_attr__( 'Home', 'wetu-importer' ) . '</a>';

		foreach ( $post_types as $post_type => $label ) {
			// @codingStandardsIgnoreLine
			echo ' | <a class="' . $this->itemd( $tab, $post_type, 'current', false ) . '" href="' . admin_url( 'admin.php' ) . '?page=' . $this->plugin_slug . '&tab=' . $post_type . '">' . $label . '</a>';
		}

		echo '</div><br clear="both"/></div>';
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function team_member_checkboxes( $selected = array() ) {
		if ( post_type_exists( 'team' ) ) { ?>
			<ul>
				<?php
					$team_args = array(
						'post_type'	=> 'team',
						'post_status' => 'publish',
						'nopagin' => true,
						'fields' => 'ids',
					);

					$team_members = new WP_Query( $team_args );

					if ( $team_members->have_posts() ) {
						foreach ( $team_members->posts as $member ) {
							// @codingStandardsIgnoreLine ?>
							<li><input class="team" <?php $this->checked( $selected, $member ); ?> type="checkbox" value="<?php echo $member; ?>" /> <?php echo get_the_title( $member ); ?></li>
						<?php }
					} else { ?>
						<li><input class="team" type="checkbox" value="0" /> <?php esc_html_e( 'None', 'wetu-importer' ); ?></li>
					<?php }
				?>
			</ul>
		<?php }
	}


	// GENERAL FUNCTIONS

	/**
	 * Checks to see if an item is checked.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $echo bool
	 */
	public function checked( $haystack = false, $needle = '', $echo = true ) {
		$return = $this->itemd( $haystack,$needle, 'checked' );

		if ( '' !== $return ) {
			if ( true === $echo ) {
				// @codingStandardsIgnoreLine
				echo $return;
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
		$return = $this->itemd( $haystack,$needle,'selected' );

		if ( '' !== $return ) {
			if ( true === $echo ) {
				// @codingStandardsIgnoreLine
				echo $return;
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
	 * grabs any attachments for the current item
	 */
	public function find_attachments( $id = false ) {
		if ( false !== $id ) {
			if ( empty( $this->found_attachments ) ) {
				$attachments_args = array(
					'post_parent' => $id,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'order' => 'ASC',
					'nopagin' => 'true',
					'posts_per_page' => '-1',
				);

				$attachments = new WP_Query( $attachments_args );

				if ( $attachments->have_posts() ) {
					foreach ( $attachments->posts as $attachment ) {
						$this->found_attachments[ $attachment->ID ] = str_replace( array( '.jpg', '.png', '.jpeg' ),'',$attachment->post_title );
						//$this->gallery_meta[] = $attachment->ID;
					}
				}
			}
		}
	}

	// CUSTOM FIELD FUNCTIONS

	/**
	 * Saves the room data
	 */
	public function save_custom_field( $value = false, $meta_key, $id, $decrease = false, $unique = true ) {
		if ( false !== $value ) {
			if ( false !== $decrease ) {
				$value = intval( $value );
				$value--;
			}

			$prev = get_post_meta( $id,$meta_key,true );

			if ( false !== $id && '0' !== $id && false !== $prev && true === $unique ) {
				update_post_meta( $id,$meta_key,$value,$prev );
			} else {
				add_post_meta( $id,$meta_key,$value,$unique );
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
				$new_items = array_unique( $prev_items );
				delete_post_meta( $id, $key );

				foreach ( $new_items as $new_item ) {
					add_post_meta( $id, $key, $new_item, false );
				}
			}
		}
	}

	// TAXONOMY FUNCTIONS

	/**
	 * set_taxonomy with some terms
	 */
	public function set_taxonomy( $taxonomy, $terms, $id ) {
		$result = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $k ) {
				if ( $id ) {
					// @codingStandardsIgnoreLine
					if ( ! $term = term_exists( trim( $k ), $tax ) ) {
						$term = wp_insert_term( trim( $k ), $tax );

						if ( is_wp_error( $term ) ) {
							// @codingStandardsIgnoreLine
							echo $term->get_error_message();
						} else {
							wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy,true );
						}
					} else {
						wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy,true );
					}
				} else {
					$result[] = trim( $k );
				}
			}
		}
		return $result;
	}

	public function set_term( $id = false, $name = false, $taxonomy = false, $parent = false ) {
		// @codingStandardsIgnoreLine
		if ( ! $term = term_exists( $name, $taxonomy ) ) {
			if ( false !== $parent ) {
				$parent = array(
					'parent' => $parent,
				);
			}

			$term = wp_insert_term( trim( $name ), $taxonomy,$parent );

			if ( is_wp_error( $term ) ) {
				// @codingStandardsIgnoreLine
				echo $term->get_error_message();
			} else {
				wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy,true );
			}
		} else {
			wp_set_object_terms( $id, intval( $term['term_id'] ), $taxonomy,true );
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
			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			) );

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$return .= '<li><input class="' . $taxonomy . '" ' . $this->checked( $selected,$term->term_id,false ) . ' type="checkbox" value="' . $term->term_id . '" /> ' . $term->name . '</li>';
				}
			} else {
				$return .= '<li><input type="checkbox" value="" /> ' . __( 'None', 'wetu-importer' ) . '</li>';
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
		$latitude = false;
		$address = false;

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

				$address = implode( ', ',$address );
				$address = str_replace( ', , ', ', ', $address );
			}
		}

		if ( false !== $longitude ) {
			$location_data = array(
				'address'	=> (string) $address,
				'lat'		=> (string) $latitude,
				'long'		=> (string) $longitude,
				'zoom'		=> (string) $zoom,
				'elevation'	=> '',
			);

			if ( false !== $id && '0' !== $id ) {
				$prev = get_post_meta( $id,'location',true );
				update_post_meta( $id,'location',$location_data,$prev );
			} else {
				add_post_meta( $id,'location',$location_data,true );
			}
		}
	}

	// IMAGE FUNCTIONS

	/**
	 * Creates the main gallery data
	 */
	public function set_featured_image( $data, $id ) {
		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			$this->featured_image = $this->attach_image( $data[0]['content']['images'][0], $id,  array(
				'width' => '800',
				'height' => '600',
				'cropping' => 'h',
			) );

			if ( false !== $this->featured_image ) {
				delete_post_meta( $id,'_thumbnail_id' );
				add_post_meta( $id,'_thumbnail_id',$this->featured_image,true );
			}
		}
	}

	/**
	 * Sets a banner image
	 */
	public function set_banner_image( $data, $id, $content = array( 'none' ) ) {
		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			if ( in_array( 'unique_banner_image', $content ) && isset( $data[0]['destination_image'] ) && is_array( $data[0]['destination_image'] ) ) {
				$temp_banner = $this->attach_image( $data[0]['destination_image'], $id, array(
					'width' => '1920',
					'height' => '600',
					'cropping' => 'c',
				));
			} else {
				$temp_banner = $this->attach_image( $data[0]['content']['images'][1], $id, array(
					'width' => '1920',
					'height' => '600',
					'cropping' => 'c',
				));
			}

			if ( false !== $temp_banner ) {
				$this->banner_image = $temp_banner;

				delete_post_meta( $id,'image_group' );

				$new_banner = array(
					'banner_image' => array(
						'cmb-field-0' => $this->banner_image,
					),
				);

				add_post_meta( $id,'image_group',$new_banner,true );
			}
		}
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
						delete_post_meta( $id,'gallery', $g );

						if ( 'attachment' === get_post_type( $g ) ) {
							wp_delete_attachment( $g, true );
						}
					}
				}
			}

			$counter = 0;

			foreach ( $data[0]['content']['images'] as $image_data ) {
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

				$this->gallery_meta[] = $this->attach_image( $image_data,$id );
				$counter++;
			}

			if ( ! empty( $this->gallery_meta ) ) {
				delete_post_meta( $id,'gallery' );
				$this->gallery_meta = array_unique( $this->gallery_meta );

				foreach ( $this->gallery_meta as $gallery_id ) {
					if ( false !== $gallery_id && '' !== $gallery_id && ! is_array( $gallery_id ) ) {
						add_post_meta( $id,'gallery',$gallery_id,false );
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
			'width' => '1024',
			'height' => '768',
			//'cropping' => 'w',
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

		$args = wp_parse_args( $args, $defaults );
		$cropping = $args['cropping'];
		$width = $args['width'];
		$height = $args['height'];

		return 'https://wetu.com/ImageHandler/' . $cropping . $width . 'x' . $height . '/';
	}

	/**
	 * Attaches 1 image
	 */
	public function attach_image( $v = false, $parent_id, $image_sizes = false, $banner = false ) {
		if ( false !== $v ) {
			$temp_fragment = explode( '/',$v['url_fragment'] );
			$url_filename = $temp_fragment[ count( $temp_fragment ) -1 ];
			$url_filename = str_replace( array( '.jpg', '.png', '.jpeg' ),'',$url_filename );
			$url_filename = trim( $url_filename );
			$title = $url_filename;
			$url_filename = str_replace( ' ','_',$url_filename );

			if ( ! isset( $this->options['image_replacing'] ) && in_array( $url_filename, $this->found_attachments ) ) {
				return array_search( $url_filename,$this->found_attachments );
			}

			$postdata = array();

			if ( empty( $v['label'] ) ) {
				$v['label'] = '';
			}

			if ( ! empty( $v['description'] ) ) {
				$desc = wp_strip_all_tags( $v['description'] );
				$posdata = array(
					'post_excerpt' => $desc,
				);
			}

			if ( ! empty( $v['section'] ) ) {
				$desc = wp_strip_all_tags( $v['section'] );
				$posdata = array(
					'post_excerpt' => $desc,
				);
			}

			$attach_id = null;
			//Resizor - add option to setting if required
			$fragment = str_replace( ' ','%20',$v['url_fragment'] );
			$url = $this->get_scaling_url( $image_sizes ) . $fragment;

			$attach_id = $this->attach_external_image2( $url,$parent_id,'',$v['label'],$postdata );

			$this->found_attachments[ $attach_id ] = $url_filename;

			//echo($attach_id.' add image');
			if ( ! empty( $attach_id ) ) {
				return $attach_id;
			}
		}
		return 	false;
	}

	public function attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
		if ( ! $url || ! $post_id ) { return new WP_Error( 'missing', 'Need a valid URL and post ID...' ); }

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		// Download file to temp location, returns full server path to temp file
		//$tmp = download_url( $url );

		//var_dump($tmp);
		$tmp = tempnam( '/tmp', 'FOO' );

		$image = file_get_contents( $url );
		file_put_contents( $tmp, $image );
		chmod( $tmp,'777' );

		preg_match( '/[^\?]+\.(tif|TIFF|jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|pdf|PDF|bmp|BMP)/', $url, $matches );    // fix file filename for query strings
		$url_filename = basename( $matches[0] );
		$url_filename = str_replace( '%20','_',$url_filename );
		// extract filename from url for title
		$url_type = wp_check_filetype( $url_filename );                                           // determine file type (ext and mime/type)

		// override filename if given, reconstruct server path
		if ( ! empty( $filename ) && ' ' != $filename ) {
			$filename = sanitize_file_name( $filename );
			$tmppath = pathinfo( $tmp );

			$extension = '';
			if ( isset( $tmppath['extension'] ) ) {
				$extension = $tmppath['extension'];
			}

			$new = $tmppath['dirname'] . '/' . $filename . '.' . $extension;
			rename( $tmp, $new );                                                                 // renames temp file on server
			$tmp = $new;                                                                        // push new filename (in path) to be used in file array later
		}

		// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
		$file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

		if ( ! empty( $filename ) && ' ' != $filename ) {
			$file_array['name'] = $filename . '.' . $url_type['ext'];                           // user given filename for title, add original URL extension
		} else {
			$file_array['name'] = $url_filename;                                                // just use original URL filename
		}

		// set additional wp_posts columns
		if ( empty( $post_data['post_title'] ) ) {

			$url_filename = str_replace( '%20',' ',$url_filename );

			$post_data['post_title'] = basename( $url_filename, '.' . $url_type['ext'] );         // just use the original filename (no extension)
		}

		// make sure gets tied to parent
		if ( empty( $post_data['post_parent'] ) ) {
			$post_data['post_parent'] = $post_id;
		}

		// required libraries for media_handle_sideload

		// do the validation and storage stuff
		$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

		// If error storing permanently, unlink
		if ( is_wp_error( $att_id ) ) {
			unlink( $file_array['tmp_name'] );   // clean up
			return false; // output wp_error
			//return $att_id; // output wp_error
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
		// @codingStandardsIgnoreLine
		echo '<li class="post-' . $response . '"><span class="dashicons dashicons-yes"></span> <a target="_blank" href="' . get_permalink( $response ) . '">' . get_the_title( $response ) . '</a></li>';
	}

	/**
	 * Formats the error.
	 */
	public function format_error( $response ) {
		// @codingStandardsIgnoreLine
		echo '<li class="post-error"><span class="dashicons dashicons-no"></span>' . $response . '</li>';
	}

	/**
	 * Does a multine search
	 */
	public function multineedle_stripos( $haystack, $needles, $offset = 0 ) {
		$found = false;
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
				$videos[] = $temp_video;
			}

			if ( false !== $id && '0' !== $id ) {
				delete_post_meta( $id, 'videos' );
			}

			foreach ( $videos as $video ) {
				add_post_meta( $id,'videos',$video,false );
			}
		}
	}

	public function shuffle_assoc( &$array ) {
		$new = array();
		$keys = array_keys( $array );

		shuffle( $keys );

		foreach ( $keys as $key ) {
			$new[ $key ] = $array[ $key ];
		}

		$array = $new;

		return true;
	}

	/**
	 * Gets the Post ID by the wetu ID.
	 *
	 * @param boolean $wetu_id the wetu ID.
	 * @return boolean | string
	 */
	private function get_post_id_by_key_value( $wetu_id = false ) {
		global $wpdb;
		$id = false;

		if ( false !== $wetu_id && '' !== $wetu_id ) {
			$result = $wpdb->get_var( "SELECT post_id FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'lsx_wetu_id' AND `meta_value` = '{$wetu_id}'" );
			if ( false !== $result && ! empty( $result ) ) {
				$id = $result;
			}
		}
		return $id;
	}
}

$wetu_importer = new WETU_Importer();
