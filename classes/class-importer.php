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

	// ACTIVATION FUNCTIONS

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wetu-importer', FALSE, basename( WETU_IMPORTER_PATH ) . '/languages');
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		$this->post_types = array('accommodation','destination','tour');
		$temp_options = get_option('_lsx-to_settings',false);

		if(isset($temp_options[$this->plugin_slug])) {
			$this->options = $temp_options[$this->plugin_slug];

			$this->api_key = false;
			$this->api_username = false;
			$this->api_password = false;
			if (false !== $temp_options) {
				if (isset($temp_options['api']['wetu_api_key']) && '' !== $temp_options['api']['wetu_api_key']) {
					$this->api_key = $temp_options['api']['wetu_api_key'];
				}
				if (isset($temp_options['api']['wetu_api_username']) && '' !== $temp_options['api']['wetu_api_username']) {
					$this->api_username = $temp_options['api']['wetu_api_username'];
				}
				if (isset($temp_options['api']['wetu_api_password']) && '' !== $temp_options['api']['wetu_api_password']) {
					$this->api_password = $temp_options['api']['wetu_api_password'];
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

	/**
	 * Displays the importers navigation
	 *
	 * @param $tab string
	 */
	public function navigation($tab='') {
		$post_types = array(
			'tour'              => esc_attr('Tours','wetu-importer'),
			'accommodation'     => esc_attr('Accommodation','wetu-importer'),
			'destination'       => esc_attr('Destinations','wetu-importer'),
		);
		echo '<div class="wet-navigation"><div class="subsubsub"><a class="'.$this->itemd($tab,'','current',false).'" href="'.admin_url('admin.php').'?page='.$this->plugin_slug.'">'.esc_attr('Home','wetu-importer').'</a>';
		foreach($post_types as $post_type => $label){
			echo ' | <a class="'.$this->itemd($tab,$post_type,'current',false).'" href="'.admin_url('admin.php').'?page='.$this->plugin_slug.'&tab='.$post_type.'">'.$label.'</a>';
		}
		echo '</div><br clear="both"/></div>';
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function team_member_checkboxes($selected=array()) {
		if(post_type_exists('team')) { ?>
			<ul>
				<?php
				$team_args=array(
					'post_type'	=>	'team',
					'post_status' => 'publish',
					'nopagin' => true,
					'fields' => 'ids'
				);
				$team_members = new WP_Query($team_args);
				if($team_members->have_posts()){
					foreach($team_members->posts as $member){ ?>
						<li><input class="team" <?php $this->checked($selected,$member); ?> type="checkbox" value="<?php echo $member; ?>" /> <?php echo get_the_title($member); ?></li>
					<?php }
				}else{ ?>
					<li><input class="team" type="checkbox" value="0" /> <?php _e('None','wetu-importer'); ?></li>
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
	public function checked($haystack=false,$needle='',$echo=true) {
		$return = $this->itemd($haystack,$needle,'checked');
		if('' !== $return) {
			if (true === $echo) {
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
	public function selected($haystack=false,$needle='',$echo=true) {
		$return = $this->itemd($haystack,$needle,'selected');
		if('' !== $return) {
			if (true === $echo) {
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
	public function itemd($haystack=false,$needle='',$type='',$wrap=true) {
		$html = '';
		if('' !== $type) {
			if (!is_array($haystack)) {
				$haystack = array($haystack);
			}
			if (in_array($needle, $haystack)) {
				if(true === $wrap || 'true' === $wrap) {
					$html = $type . '"' . $type . '"';
				}else{
					$html = $type;
				}
			}
		}
		return $html;

	}

	/**
	 * grabs any attachments for the current item
	 */
	public function find_attachments($id=false) {
		if(false !== $id){
			if(empty($this->found_attachments)){

				$attachments_args = array(
					'post_parent' => $id,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'order' => 'ASC',
					'nopagin' => 'true',
					'posts_per_page' => '-1'
				);

				$attachments = new WP_Query($attachments_args);
				if($attachments->have_posts()){
					foreach($attachments->posts as $attachment){
						$this->found_attachments[$attachment->ID] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
						$this->gallery_meta[] = $attachment->ID;
					}
				}
			}
		}
	}


	// CUSTOM FIELD FUNCTIONS

	/**
	 * Saves the room data
	 */
	public function save_custom_field($value=false,$meta_key,$id,$decrease=false,$unique=true) {
		if(false !== $value){
			if(false !== $decrease){
				$value = intval($value);
				$value--;
			}
			$prev = get_post_meta($id,$meta_key,true);

			if(false !== $id && '0' !== $id && false !== $prev && true === $unique){
				update_post_meta($id,$meta_key,$value,$prev);
			}else{
				add_post_meta($id,$meta_key,$value,$unique);
			}
		}
	}

	/**
	 * Grabs the custom fields,  and resaves an array of unique items.
	 */
	public function cleanup_posts() {
		if(!empty($this->cleanup_posts)){
			foreach($this->cleanup_posts as $id => $key) {
				$prev_items = get_post_meta($id, $key, false);
				$new_items = array_unique($prev_items);
				delete_post_meta($id, $key);
				foreach($new_items as $new_item) {
					add_post_meta($id, $key, $new_item, false);
				}
			}
		}
	}

	// TAXONOMY FUNCTIONS

	// SEARCH FUNCTIONS

	/**
	 * set_taxonomy with some terms
	 */
	public function set_taxonomy($taxonomy,$terms,$id) {
		$result=array();
		if(!empty($data))
		{
			foreach($data as $k)
			{
				if($id)
				{
					if(!$term = term_exists(trim($k), $tax))
					{
						$term = wp_insert_term(trim($k), $tax);
						if ( is_wp_error($term) )
						{
							echo $term->get_error_message();
						}
						else
						{
							wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
						}
					}
					else
					{
						wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
					}
				}
				else
				{
					$result[]=trim($k);
				}
			}
		}
		return $result;
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function taxonomy_checkboxes($taxonomy=false,$selected=array()) {
		$return = '';
		if(false !== $taxonomy){
			$return .= '<ul>';
			$terms = get_terms(array('taxonomy'=>$taxonomy,'hide_empty'=>false));

			if(!is_wp_error($terms)){
				foreach($terms as $term){
					$return .= '<li><input class="'.$taxonomy.'" '.$this->checked($selected,$term->term_id,false).' type="checkbox" value="'.$term->term_id.'" /> '.$term->name.'</li>';
				}
			}else{
				$return .= '<li><input type="checkbox" value="" /> '.__('None','wetu-importer').'</li>';
			}
			$return .= '</ul>';
		}
		return $return;
	}

}
$wetu_importer = new WETU_Importer();
