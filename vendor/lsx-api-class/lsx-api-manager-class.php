<?php
/**
 * @package   LSX API Manager Class
 * @author     LightSpeeds
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015  LightSpeed Team
 */

class LSX_API_Manager {	

	/**
	 * Holds the API Key
	 *
	 * @var      string
	 */
	public $api_key = false;

	/**
	 * Holds the Email address used to purchase the API key
	 *
	 * @var      string
	 */
	public $email = false;	

	/**
	 * Holds the Products Title
	 *
	 * @var      string
	 */
	public $product_id = false;	

	/**
	 * Holds the Products Slug
	 *
	 * @var      string
	 */
	public $product_slug = false;		

	/**
	 * Holds the current version of the plugin
	 *
	 * @var      string
	 */
	public $version = false;	

	/**
	 * Holds the unique password for this site.
	 *
	 * @var      string
	 */
	public $password = false;	

	/**
	 * Holds any messages for the user.
	 *
	 * @var      string
	 */
	public $messages = false;

	/**
	 * Holds any path to the plugin file.
	 *
	 * @var      string
	 */
	public $file = false;	

	/**
	 * Holds class instance
	 *
	 * @var      string
	 */
	protected static $instance = null;
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	public function __construct($api_array = array()) {

		if(isset($api_array['api_key'])){
			$this->api_key = trim($api_array['api_key']);
		}			
		if(isset($api_array['email'])){
			$this->email = trim($api_array['email']);
		}
		if(isset($api_array['product_id'])){
			$this->product_id = $api_array['product_id'];
			$this->product_slug = sanitize_title($api_array['product_id']);
		}
		if(isset($api_array['version'])){
			$this->version = $api_array['version'];
		}
		if(isset($api_array['instance'])){
			$this->password = $api_array['instance'];
		}
		if(isset($api_array['file'])){
			$this->file = $api_array['file'];
		}		

		$this->api_url = 'https://www.lsdev.biz/wc-api/product-key-api';
		$this->products_api_url = 'https://www.lsdev.biz/';
		$this->license_check_url = 'https://www.lsdev.biz/wc-api/license-status-check';			


		if(isset($_GET['page']) && in_array($_GET['page'],apply_filters('lsx_api_manager_options_pages',array(false)))){
			$this->query('activation');
			$this->status = $this->check_status();
		}

		add_filter('site_transient_update_plugins', array($this,'injectUpdate'));
		add_action( "in_plugin_update_message-".$this->file,array($this,'plugin_update_message'),10,2);

		add_action('init',array($this,'set_update_status'));

		add_action('lsx_framework_dashboard_tab_content_api',array($this,'dashboard_tabs'),1);
		
		add_action('wp_ajax_wc_api_'.$this->product_slug,array($this,'activate_deactivate'));	
		add_action('wp_ajax_nopriv_wc_api_'.$this->product_slug,array($this,'activate_deactivate'));
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object|Module_Template    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}	

	/**
	 * Outputs the dashboard tab pages.
	 *
	 * @since 1.0.0
	 *
	 * @return    object|Module_Template    A single instance of this class.
	 */
	public static function dashboard_tabs() { ?>
		<tr class="form-field <?php echo $this->product_slug; ?>-wrap">
			<th class="<?php echo $this->product_slug; ?>_table_heading" style="padding-bottom:0px;" scope="row" colspan="2">

				<?php
					$colour = 'red';
					if('active' === $this->status){
						$colour = 'green';
					}
				?>

				<h4 style="margin-bottom:0px;">
					<span><?php echo $this->product_id; ?></span> 
					 - <span><?php echo $this->version; ?></span> 
					 - <span style="color:<?php echo $colour;?>;"><?php echo $this->status; ?></span>
					 <?php if(is_array($this->messages)) { ?> - <span class="messages" style="font-weight:normal;"><?php echo implode('. ',$this->messages); ?></span><?php } ?>
				</h4>
		
			</th>
		</tr>

		<tr class="form-field <?php echo $this->product_slug; ?>-api-email-wrap">
			<th style="font-size:13px;" scope="row">
				<i class="dashicons-before dashicons-email-alt"></i> Registered Email
			</th>
			<td>
				<input type="text" {{#if <?php echo $this->product_slug; ?>_email}} value="{{<?php echo $this->product_slug; ?>_email}}" {{/if}} name="<?php echo $this->product_slug; ?>_email" /><br />
			</td>

		</tr>
		<tr class="form-field <?php echo $this->product_slug; ?>-api-key-wrap">
			<th style="font-size:13px;" scope="row">
				<i class="dashicons-before dashicons-admin-network"></i> API Key
			</th>
			<td>
				<input type="text" {{#if <?php echo $this->product_slug; ?>_api_key}} value="{{<?php echo $this->product_slug; ?>_api_key}}" {{/if}} name="<?php echo $this->product_slug; ?>_api_key" />
			</td>
		</tr>

	<?php
		$this->settings_page_scripts();
	}

	/**
	 * outputs the scripts for the dashboard settings pages.
	 */
	public function settings_page_scripts(){ ?>
		{{#script}}
			jQuery( function( $ ){
				$( '.<?php echo $this->product_slug; ?>-api-email-wrap input' ).on( 'change', function() {
					$('input[name="<?php echo $this->product_slug; ?>_api_action"]').remove();

					var action = 'activate';
					if('' == $(this).val() || undefined == $(this).val()){
						action = 'deactivate';
					}
					$('.<?php echo $this->product_slug; ?>-wrap').append('<input type="hidden" value="'+action+'" name="<?php echo $this->product_slug; ?>_api_action" />');
				});
			});
		{{/script}}				
	<?php 
	}

	/**
	 * Return an instance of this class.
	 */
	public function activate_deactivate(){

		if(isset($_POST['trigger']) && 'Activate' === $_POST['trigger'] && 'inactive' === $this->status){
			$response = $this->query('activation');
	
			if(isset($response->activated)){
				echo 'Activated '.$response->message;
			}else{
				echo false;
			}
		}
		if(isset($_POST['trigger']) && 'Deactivate' === $_POST['trigger'] && 'active' === $this->status){
			$response = $this->query('deactivation');

			if(isset($response->deactivated)){
				echo 'Deactivated '.$response->activations_remaining;
			}else{
				echo false;
			}
		}		
		die();
	}

	
	/**
	 * Generates the API URL
	 */
	public function create_software_api_url( $args ) {

		$endpoint = 'am-software-api';
		if('pluginupdatecheck' === $args['request']){
			$endpoint = 'upgrade-api';
		}
		$api_url = add_query_arg( 'wc-api', $endpoint, $this->products_api_url );
		return $api_url . '&' . http_build_query( $args );
	}

	/**
	 * Checks if the software is activated or deactivated
	 * @return string
	 */
	public function check_status() {
		$response = $this->query('status');
		$status = 'inactive';
		if(is_object($response)){
			if(isset($response->error)){
				$this->messages[] = $this->format_error_code($response->code);
			}elseif(isset($response->status_check)){
				$status = $response->status_check;
				if(isset($response->activations_remaining)){
					$this->messages[] = $response->activations_remaining;
				}
				if(isset($response->message)){
					$this->messages[] = $response->message;
				}				
			}
		}
		return $status;
	}

	/**
	 * Does the actual contacting to the API.
	 * @param  string $action
	 * @return array
	 */
	public function query($action='status') {
		$args = array(
				'request' 		=> $action,
				'email' 		=> $this->email,
				'licence_key'	=> $this->api_key,
				'product_id' 	=> $this->product_id,
				'platform' 		=> home_url(),
				'instance' 		=> $this->password
		);
		$target_url = esc_url_raw( $this->create_software_api_url( $args ) );
		$request = wp_remote_get( $target_url );
		if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			// Request failed
			return false;
		}
		$response = wp_remote_retrieve_body( $request );
		return json_decode($response);
	}

	/**
	 * Formats the error code into a readable format.
	 * @param  array $args
	 * @return array
	 */
	public function format_error_code($code=false){
        switch ( $code ) {
          case '101' :
            $error = array( 'error' => __( 'Invalid API License Key. Login to your My Account page to find a valid API License Key', 'woocommerce-api-manager' ), 'code' => '101' );
            break;
          case '102' :
            $error = array( 'error' => __( 'Software has been deactivated', 'woocommerce-api-manager' ), 'code' => '102' );
            break;
          case '103' :
            $error = array( 'error' => __( 'Exceeded maximum number of activations', 'woocommerce-api-manager' ), 'code' => '103' );
            break;
          case '104' :
            $error = array( 'error' => __( 'Invalid Instance ID', 'woocommerce-api-manager' ), 'code' => '104' );
            break;
          case '105' :
            $error = array( 'error' => __( 'Invalid API License Key', 'woocommerce-api-manager' ), 'code' => '105' );
            break;
          case '106' :
            $error = array( 'error' => __( 'Subscription Is Not Active', 'woocommerce-api-manager' ), 'code' => '106' );
            break;
          default :
            $error = array( 'error' => __( 'Invalid Request', 'woocommerce-api-manager' ), 'code' => '100' );
            break;
        }		
	}	

	public static function generatePassword($length = 20) {
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	    $count = mb_strlen($chars);

	    for ($i = 0, $result = ''; $i < $length; $i++) {
	        $index = rand(0, $count - 1);
	        $result .= mb_substr($chars, $index, 1);
	    }

	    return $result;
	}	

	public function set_update_status(){
		$this->status = $this->check_status();
		if('active' === $this->status){
			$args = array(
					'request' 			=> 'pluginupdatecheck',
					'plugin_name' 		=> $this->product_slug.'/'.$this->file,
					'version' 			=> $this->product_slug,
					'activation_email' 	=> $this->email,
					'api_key'			=> $this->api_key,
					'product_id' 		=> $this->product_id,
					'domain' 			=> home_url(),
					'instance' 			=> $this->password,
					'software_version'	=> $this->version,			
			);
			$target_url = esc_url_raw( $this->create_software_api_url( $args ) );
			$request = wp_remote_get( $target_url );
			if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				// Request failed
				$this->upgrade_response=false;
			}
			$response = wp_remote_retrieve_body( $request );
			$this->upgrade_response = maybe_unserialize($response);
		}
	}
	
	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 * 
	 * @param StdClass $updates Update list.
	 * @return StdClass Modified update list.
	 */
	public function injectUpdate($updates=false){

		if('active' === $this->status && null !== $this->upgrade_response && is_object($this->upgrade_response) && isset($this->upgrade_response->new_version) && version_compare ( $this->upgrade_response->new_version , $this->version , '>' )){

			//setup the response if our plugin is the only one that needs updating.	
			if ( !is_object($updates) ) {
				$updates = new StdClass();
				$updates->response = array();
			}
			$updates->response[$this->product_slug.'/'.$this->file] = $this->upgrade_response;		
		}
		return $updates;
	}	
}