<?php
/**
 * @package   WETU_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Settings extends WETU_Importer {
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$temp_options = get_option('_lsx-to_settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
			$this->set_variables();
		}

		add_filter( 'lsx_to_framework_settings_tabs', array( $this, 'settings_page_array') );
		add_action('lsx_to_framework_api_tab_content',array( $this, 'api_settings'),10,1);
	}

	/**
	 * Returns the array of settings to the UIX Class in the lsx framework
	 */
	public function settings_page_array($tabs){
		$tabs[$this->plugin_slug] = array(
			'page_title'        => __('Image Scaling','wetu-importer'),
			'page_description'  => __('','wetu-importer'),
			'menu_title'        => __('Importer','wetu-importer'),
			'template'          => WETU_IMPORTER_PATH.'settings/wetu.php',
			'default'	 		=> false
		);
		return $tabs;
	}

	/**
	 * Adds the API key to the API Tab
	 */
	public function api_settings($tab='general') {
		if('settings' === $tab){ ?>
			<tr class="form-field -wrap">
				<th scope="row">
                    <i class="dashicons-before dashicons-admin-network"></i> <label for="wetu_api_key"> WETU API Key</label>
				</th>
				<td>
                    <input type="text"  {{#if wetu_api_key}} value="{{wetu_api_key}}" {{/if}} name="wetu_api_key" />
				</td>
			</tr>
		<?php }
	}
}
$wetu_importer_settings = new WETU_Importer_Settings();
