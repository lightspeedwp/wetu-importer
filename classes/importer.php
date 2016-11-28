<?php
/**
 * @package   Lsx_Tour_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer {
	
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
	public $plugin_slug = 'lsx-tour-importer';

	/**
	 * The options for the plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;	

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $wetu_url = false;	

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
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$temp_options = get_option('_to_settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
			$this->set_variables();
		}
	}	

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		if(isset($this->options['api_key'])){
			$this->wetu_url = 'http://wetu.com/API/Itinerary/'.$this->options['api_key'].'/V7/';

			if(isset($this->options['image_scaling'])){
				$this->scale_images = true;
				$width = '800';
				if(isset($this->options['width']) && '' !== $this->options['width']){
					$width = $this->options['width'];
				}
				$height = '600';
				if(isset($this->options['height']) && '' !== $this->options['height']){
					$height = $this->options['height'];
				}
				$cropping = 'raw';
				if(isset($this->options['cropping']) && '' !== $this->options['cropping']){
					$cropping = $this->options['cropping'];
				}				
				$this->image_scaling_url = 'https://wetu.com/ImageHandler/'.$cropping.$width.'x'.$height.'/';
			}	
		}
	}
}
$lsx_tour_importer = new Lsx_Tour_Importer();
