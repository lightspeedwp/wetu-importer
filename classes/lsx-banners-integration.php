<?php
/**
 * @package   Lsx_Tour_Importer_Banner_Integration
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 LightSpeed
 **/

class Lsx_Tour_Importer_Banner_Integration extends Lsx_Tour_Importer_Admin {
	
	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'banners';	

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->set_variables();

		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );

		add_action('wp_ajax_lsx_import_sync_banners',array($this,'process_ajax_import'));	
		add_action('wp_ajax_nopriv_lsx_import_sync_banners',array($this,'process_ajax_import'));		
	}	

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {

		if(isset($this->options['image_scaling'])){
			$this->scale_images = true;
			$width = '1920';
			if(isset($this->options['width']) && '' !== $this->options['width']){
				$width = $this->options['width'];
			}
			$height = '500';
			if(isset($this->options['height']) && '' !== $this->options['height']){
				$height = $this->options['height'];
			}
			$cropping = 'c';
			if(isset($this->options['cropping']) && '' !== $this->options['cropping']){
				$cropping = $this->options['cropping'];
			}				
			$this->image_scaling_url = 'https://wetu.com/ImageHandler/'.$cropping.$width.'x'.$height.'/';
		}	
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>

            <h2><?php _e('Download new banners straight from WETU','lsx-tour-importer'); ?></h2>  

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>" />
				
				<table class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
								<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
								<input type="checkbox" id="cb-select-all-1">
							</th>
							<th style="width:15%" class="manage-column column-title " id="title" scope="col">Title</th>
							<th style="" class="manage-column column-date" id="date" scope="col">Images</th>
						</tr>
					</thead>
				
					<?php 
						$accommodation_args = array(
							'post_type' => 'accommodation',
							'post_status' => array('publish','pending','draft','future','private'),
							'nopagin' => true,
							'meta_query' => array(
								'relation' => 'AND',
								array(
									'key' => 'lsx_wetu_id',
									'compare' => 'EXISTS'
								),								
								array(
									'key' => 'image_group',
									'compare' => 'EXISTS'
								),
								array(
									'key' => 'image_group',
									'value' => 'a:1:{s:12:"banner_image";a:0:{}}',
									'compare' => '!='
								),
							)
						);
						$accommodation = new WP_Query($accommodation_args);
					?>

					<tbody id="the-list">
						<?php
							if($accommodation->have_posts()){ 
								while($accommodation->have_posts()) {
									$accommodation->the_post();
								?>
								<tr class="post-<?php the_ID(); ?> type-tour status-none" id="post-<?php the_ID(); ?>">	
									<?php
									$banner_size_appropriate = false;
									$min_width = '1920';
									$min_height = '500';

									$img_group = get_post_meta(get_the_ID(),'image_group',true);

									$thumbnails_html = false;

									if(false !== $img_group){
										foreach($img_group['banner_image'] as $banner_image){
											$large = wp_get_attachment_image_src($banner_image,'full');
											$real_width = $large[1];
											$real_height = $large[2];

											$status = 'optimized';
											if($real_width < intval($real_width)){
												$status = 'width not enough.';
											}

											$thumbnail = wp_get_attachment_image_src($banner_image,'thumbnail');
											$thumbnails_html[] = '
												<div style="display:block;float:left;">
													<img src="'.$thumbnail[0].'" />
													<p style="text-align:center;">'.$real_width.'px by '.$real_height.'px</p>
												</div>';
										}
									}
									?>
									<th class="check-column" scope="row">
										<label for="cb-select-<?php the_ID(); ?>" class="screen-reader-text"></label>
										<input type="checkbox" data-identifier="<?php the_ID(); ?>" value="<?php the_ID(); ?>" name="post[]" id="cb-select-<?php the_ID(); ?>">
									</th>

									<td class="post-title page-title column-title"><?php the_title(); ?></td>

									<td colspan="2" class="thumbnails column-thumbnails">
										<?php if(false !== $thumbnails_html){ echo implode('',$thumbnails_html); } else { echo '<p>There was an error retrieving your images.</p>'; } ?>
									</td>
								</tr>
						<?php 	}
							} 
						?>
					</tbody>

					<tfoot>
						<tr>
							<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
								<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
								<input type="checkbox" id="cb-select-all-1">
							</th>
							<th style="width:15%;" class="manage-column column-title " id="title" scope="col">Title</th>
							<th style="" class="manage-column column-date" id="date" scope="col">Images</th>
						</tr>
					</tfoot>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','lsx-tour-importer'); ?>" /> 
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','lsx-tour-importer'); ?>" />
				</p>
			</form>
        </div>
        <?php
	}
}
$lsx_tour_importer_lsx_banners_integration = new Lsx_Tour_Importer_Banner_Integration();