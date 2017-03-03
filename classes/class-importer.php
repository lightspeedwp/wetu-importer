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
	}
	
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

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		$temp_options = get_option('_lsx-to_settings',false);

		if(isset($temp_options[$this->plugin_slug])) {
			$this->options = $temp_options[$this->plugin_slug];

			$this->api_key = false;
			if (false !== $temp_options) {
				if (isset($temp_options['api']['wetu_api_key']) && '' !== $temp_options['api']['wetu_api_key']) {
					$this->api_key = $temp_options['api']['wetu_api_key'];
				}

				if (isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug]) && isset($this->options['image_scaling'])) {
					$this->scale_images = true;
					$width = '800';
					if (isset($this->options['width']) && '' !== $this->options['width']) {
						$width = $this->options['width'];
					}
					$height = '600';
					if (isset($this->options['height']) && '' !== $this->options['height']) {
						$height = $this->options['height'];
					}
					$cropping = 'raw';
					if (isset($this->options['cropping']) && '' !== $this->options['cropping']) {
						$cropping = $this->options['cropping'];
					}
					$this->image_scaling_url = 'https://wetu.com/ImageHandler/' . $cropping . $width . 'x' . $height . '/';
				}
			}
		}
	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wetu-importer', FALSE, basename( WETU_IMPORTER_PATH ) . '/languages');
	}
}
$wetu_importer = new WETU_Importer();
