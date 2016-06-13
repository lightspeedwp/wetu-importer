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
					<thead>
						<tr>
							<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
								<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
								<input type="checkbox" id="cb-select-all-1">
							</th>
							<th style="" class="manage-column column-title sortable desc" id="title" style="width:50%;" scope="col">
								<span>Title</span><span class="sorting-indicator"></span>
							</th>
							<th style="" class="manage-column column-date sortable asc" id="date" scope="col">
									<span>Date</span><span class="sorting-indicator"></span>
							</th>
							<th style="" class="manage-column column-ssid" id="ssid" scope="col">ID</th>
						</tr>
					</thead>
				
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

					<tfoot>
						<tr>
							<th style="" class="manage-column column-cb check-column" scope="col"><label for="cb-select-all-2" class="screen-reader-text">Select All</label><input type="checkbox" id="cb-select-all-2"></th>
							<th style="" class="manage-column column-title sortable desc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th>
							<th style="" class="manage-column column-date sortable asc" scope="col"><a href="http://localhost.localdomain/fmm/asc/wp-admin/edit.php?post_type=tour&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>
							<th style="" class="manage-column column-ssid" scope="col">ID</th>
						</tr>
					</tfoot>
				</table>
			</form>          
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
        		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="submit" type="submit" value="<?php _e('Search','lsx-tour-importer'); ?>" />
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

		echo '<h3>'.__('Wetu Status','lsx-tour-importer').'</h3>';
		$last_refresh_date = get_option('lsx_tour_operator_accommodation_timestamp',false);
		
		if(!isset($_GET['refresh_options'])){
			if(false === $last_refresh_date){
				echo '<p>'.__('Please update your accommodation list.','lsx-tour-importer').'</p>';
			}else{
				echo '<p>'.__('Last Update - ','lsx-tour-importer').$last_refresh_date.'</p>';
			}			
		?>
	        <form id="<?php echo $this->plugin_slug; ?>-update-form" method="get" action="tools.php">
	        	<input type="hidden" name="page" value="<?php echo $this->plugin_slug; ?>" />
	        	<input type="hidden" name="tab" value="<?php echo $this->tab_slug; ?>" />
	        	<input type="hidden" name="refresh_options" value="true" />
	        	<p><input class="submit" type="submit" value="<?php _e('Update','lsx-tour-importer'); ?>" /></p>
	        </form>	
		<?php 
		}elseif('true' === $_GET['refresh_options']){
			$this->update_options();
			?>
			<p><?php _e('Your accommodation list has been updated, please use the search form below to find what you want.','lsx-tour-importer'); ?></p>
			<?php
		}
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
							$searched_items[] = $row;
						}
					}		
				}
				$return = json_encode($searched_items);
			}
		}
		print_r($return);
		die();
	}
}
$lsx_tour_importer_accommodation = new Lsx_Tour_Importer_Accommodation();