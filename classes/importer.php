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
		$this->set_variables();
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
}
$wetu_importer = new WETU_Importer();
