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
		
		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );

		add_action('wp_ajax_lsx_import_sync_banners',array($this,'sync_new_banner'));	
		add_action('wp_ajax_nopriv_lsx_import_sync_banners',array($this,'sync_new_banner'));		
	}	

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
			
            <h2><?php _e('Download new banners straight from WETU','lsx-tour-importer'); ?></h2>  

			<form method="get" action="" id="banners-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>" />

	           <div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
	            	<img style="width:32px;" src="<?php echo LSX_TOUR_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
	            </div>  				
				
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
							'nopagin' => 'true',
							'posts_per_page' => '1000',
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

									<td class="post-title page-title column-title"><?php echo '<a href="'.admin_url('/post.php?post='.get_the_ID().'&action=edit').'" target="_blank">';the_title(); echo '</a>'; ?></td>

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

				<p><input class="button button-primary download" type="button" value="<?php _e('Download new Banners','lsx-tour-importer'); ?>" /> 
				</p>
			</form>
        </div>
        <?php
	}

	/**
	 * Creates the main gallery data
	 */
	public function sync_new_banner() {

		if(isset($_POST['action']) && 'lsx_import_sync_banners' === $_POST['action'] && isset($_POST['post_id'])){

			$banners = get_post_meta($_POST['post_id'],'image_group',true);
			$this->wetu_id = get_post_meta($_POST['post_id'],'lsx_wetu_id',true);

			$new_banner_array = false;
			$array_index = 0;



			foreach($banners['banner_image'] as $banner_image){
				$image_id = $this->attach_external_image2($this->format_wetu_url($banner_image));
				if(null !== $image_id && '' !== $image_id){
					$new_banner_array['banner_image']['cmb-field-'.$array_index] = $image_id;
					$array_index++;
				}
			}

			if(false !== $new_banner_array){
				delete_post_meta($_POST['post_id'],'image_group');
				add_post_meta($_POST['post_id'],'image_group',$new_banner_array,true);
				echo true;
			}else{
				echo false;
			}		

		}else{
			echo false;
		}
		die();
	}	

	/**
	 * formats the url
	 */
	public function format_wetu_url($post_id) {
		return 'https://wetu.com/ImageHandler/c1920x800/'.$this->wetu_id.'/'.$this->format_filename($post_id);
	}	

	/**
	 * formats the filename
	 */
	public function format_filename($post_id) {
		$base = str_replace('_',' ',get_the_title($post_id));
		$base = rawurlencode($base);
	  $type = get_post_mime_type($post_id);
	  switch ($type) {
	    case 'image/jpeg':
	    	return $base . ".jpg"; break;
	    case 'image/png':
	    	return $base . ".png"; break;
	    case 'image/gif':
	      	return $base . ".gif"; break;
	    default:
	      return false;
	  }
	}	

	public function attach_external_image2( $url = null, $post_data = array() ) {
	
		if ( !$url ) { return new WP_Error('missing', "Need a valid URL"); }

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		//var_dump($tmp);
		$tmp = tempnam("/tmp", "FOO");
		print_r($url);
		$image = file_get_contents($url);
		print_r($image);
		file_put_contents($tmp, $image);
		chmod($tmp,'777');

		preg_match('/[^\?]+\.(tif|TIFF|jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|pdf|PDF|bmp|BMP)/', $url, $matches);    // fix file filename for query strings
		$url_filename = basename($matches[0]);
		$url_filename=str_replace('%20','_',$url_filename);
		// extract filename from url for title
		$url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)

		// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
		$file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

		if ( !empty( $filename) && " " != $filename )
		{
			$file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
		}
		else
		{
			$file_array['name'] = $url_filename;                                                // just use original URL filename
		}

		// set additional wp_posts columns
		if ( empty( $post_data['post_title'] ) )
		{

			$url_filename=str_replace('%20',' ',$url_filename);

			$post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
		}

		// make sure gets tied to parent
		if ( empty( $post_data['post_parent'] ) )
		{
			$post_data['post_parent'] = $_POST['post_id'];
		}

		// required libraries for media_handle_sideload

		// do the validation and storage stuff
		$att_id = media_handle_sideload( $file_array, $_POST['post_id'], null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status
		 
		// If error storing permanently, unlink
		if ( is_wp_error($att_id) )
		{
			unlink($file_array['tmp_name']);   // clean up
			return false; // output wp_error
			//return $att_id; // output wp_error
		}

		return $att_id;
	}

}
$lsx_tour_importer_lsx_banners_integration = new Lsx_Tour_Importer_Banner_Integration();