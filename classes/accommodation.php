<?php
/**
 * @package   Lsx_Tour_Importer_Accommodation
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer_Accommodation extends Lsx_Tour_Importer_Admin {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'accommodation';

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
		$temp_options = get_option('_lsx_lsx-settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
		}
		$this->url = 'http://wetu.com/API/Pins/'.$this->options['api_key'].'/List';

		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );
		add_action('wp_ajax_lsx_tour_importer',array($this,'process_ajax_search'));	
		add_action('wp_ajax_nopriv_lsx_tour_importer',array($this,'process_ajax_search'));		

		add_action('wp_ajax_lsx_import_items',array($this,'process_ajax_import'));	
		add_action('wp_ajax_nopriv_lsx_import_items',array($this,'process_ajax_import'));			
	}	

	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
        add_management_page(
            __('LSX Tour Importer','lsx-tour-importer'),
            __('LSX Tour Importer','lsx-tour-importer'),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_importer_page' )
        );		
	}	

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

            <?php $this->update_options_form(); ?>

            <?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>" />
				
				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>
				
					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php _e('Enter a title to search for and press enter','lsx-tour-importer'); ?></label>
							</th>
							<td class="post-title page-title column-title">
								<strong>
									<?php _e('Enter a title to search for','lsx-tour-importer'); ?>
								</strong>
							</td>
							<td class="date column-date">							
							</td>
							<td class="ssid column-ssid">
							</td>
						</tr>									
					</tbody>

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','lsx-tour-importer'); ?>" /> 
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','lsx-tour-importer'); ?>" />
				</p>
			</form> 

			<div style="display:none;" class="import-list-wrapper">
				<br />
				<h2><?php _e('Your List'); ?></h2>         
				<form method="get" action="" id="import-list">
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input disabled="disabled" class="button button-primary" type="submit" value="<?php _e('Import','lsx-tour-importer'); ?>" /></p>
				</form>
			</div>
        </div>
        <?php
	}

	/**
	 * search_form
	 */
	public function search_form() {
	?>
        <form class="ajax-form" id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php" data-type="accommodation">
        	<input type="hidden" name="page" value="<?php echo $this->tab_slug; ?>" />

        	<h3><?php _e('Search','lsx-tour-importer'); ?></h3>
        	<p>
        		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
        	</p>

            <div class="ajax-loader" style="display:none;width:100%;text-align:center;">
            	<img style="width:64px;" src="<?php echo LSX_TOUR_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>
        	
        </form>	
	<?php 
	}	

	/**
	 * search_form
	 */
	public function update_options_form() {

		echo '<div class="wetu-status"><h3>'.__('Wetu Status','lsx-tour-importer').'</h3>';
		$last_refresh_date = get_option('lsx_tour_operator_accommodation_timestamp',false);
		
		if(!isset($_GET['refresh_options'])){
			if(false === $last_refresh_date){
				echo __('Please update your accommodation list.','lsx-tour-importer');
			}else{
				echo __('Last Update - ','lsx-tour-importer').$last_refresh_date;
			}			
		?>
	        <form id="<?php echo $this->plugin_slug; ?>-update-form" method="get" action="tools.php">
	        	<input type="hidden" name="page" value="<?php echo $this->plugin_slug; ?>" />
	        	<input type="hidden" name="tab" value="<?php echo $this->tab_slug; ?>" />
	        	<input type="hidden" name="refresh_options" value="true" />
	        	<input class="submit button button-primary" type="submit" value="<?php _e('Update','lsx-tour-importer'); ?>" />
	        </form>	
		<?php 
		}elseif('true' === $_GET['refresh_options']){
			$this->update_options();
			?>
			<p><?php _e('Your accommodation list has been updated, please use the search form below to find what you want.','lsx-tour-importer'); ?></p>
			<?php
		}
		echo '</div>';
	}


	/**
	 * Save the list of Accommodation into an option
	 */
	public function update_options() {
		if(isset($_GET['page']) && $this->plugin_slug === $_GET['page']
		 && isset($_GET['refresh_options']) && 'true' === $_GET['refresh_options']
		 && isset($_GET['tab']) && $this->tab_slug === $_GET['tab']) {
			$data= file_get_contents($this->url);
			$accommodation  = json_decode($data, true);
			if (!empty($data)) {
				update_option('lsx_tour_operator_accommodation',$data);
				update_option('lsx_tour_operator_accommodation_timestamp',date("d M Y - h:ia",strtotime("+2 Hours")));
			}
		}
	}
	/**
	 * Connect to wetu
	 */
	public function process_ajax_search() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === 'accommodation'){
			$accommodation = get_option('lsx_tour_operator_accommodation',false);
			if ( false !== $accommodation && isset($_POST['keyword'] )) {
				$searched_items = false;
				$search_keyword = urldecode($_POST['keyword']);
				$accommodation = json_decode($accommodation);
				if (!empty($accommodation)) {
					foreach($accommodation as $row_key => $row){
						if(stripos($row->name, $search_keyword) !== false){
							print_r($row);
							$searched_items[] = $this->format_row($row);
						}
					}		
				}
				$return = implode($searched_items);
			}
		}
		print_r($return);
		die();
	}

	public function format_row($row = false){
		if(false !== $row){
			$row_html = '
			<tr class="post-'.$row->id.' type-tour" id="post-'.$row->id.'">
				<th class="check-column" scope="row">
					<label for="cb-select-'.$row->id.'" class="screen-reader-text">'.$row->name.'</label>
					<input type="checkbox" data-identifier="'.$row->id.'" value="'.$row->id.'" name="post[]" id="cb-select-'.$row->id.'">
				</th>
				<td class="post-title page-title column-title">
					<strong>'.$row->name.'</strong>
				</td>
				<td class="date column-date">
					<abbr title="'.date('Y/m/d',strtotime($row->last_modified)).'">'.date('Y/m/d',strtotime($row->last_modified)).'</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					'.$row->id.'
				</td>
			</tr>';		
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === 'accommodation' && isset($_POST['wetu_id'])){
			
			$wetu_id = $_POST['wetu_id'];	
            $jdata=file_get_contents("http://wetu.com/API/Pins/".$this->options['api_key']."/Get?ids=".$wetu_id);

            if($jdata)
            {
                $adata=json_decode($jdata,true);
                if(!empty($adata))
                {
                	$return = $this->import_row($adata);
                }
            }
		}
		print_r($return);
		die();
	}	

	/**
	 * Connect to wetu
	 */
	public function import_row($data) {
		$id = false;

        if(trim($data[0]['type'])=='Accommodation')
        {
	        $post_name = '';
	        if(!empty($data[0]['name'])){
	            $post_name = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'accommodation', 0);
	        }

	        $data_post_content = '';
	        if(!empty($data[0]['content']['general_description']))
	        {
	            $data_post_content = $data[0]['content']['general_description'];
	        }
	        $data_post_excerpt = '';       	                	

	        $post = array(
	          'post_content'   => wp_strip_all_tags($data_post_content),// The full text of the post.
	          'post_name'      => $post_name,// The name (slug) for your post
	          'post_excerpt'   => $data_post_excerpt, // For all your post excerpt needs.
	          'post_type'		=> 'accommodation',
	        );
	        if(false !== $id && '0' !== $id){
	        	$post['ID'] = $id;
	        	$id = wp_update_post($post);
	        }else{
	        	$post['post_title'] = $data[0]['name'];
	        	$post['post_status'] = 'pending';
	        	$id = wp_insert_post($post);
	        }
        }
        return $id;
	}	
}
$lsx_tour_importer_accommodation = new Lsx_Tour_Importer_Accommodation();