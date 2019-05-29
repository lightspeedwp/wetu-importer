<?php
/**
 * The Settings Screen for the Importer Plugin
 *
 * @package   wetu_importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

namespace wetu_importer\classes;

/**
 * The Welcome Screen for the Importer Plugin
 */
class Settings {

	/**
	 * Holds instance of the class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds the settings fields available
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->fields = array(
			'wetu_api_key',
			'disable_tour_descriptions',
			'disable_accommodation_descriptions',
			'disable_accommodation_excerpts',
			'disable_destination_descriptions',
			'image_replacing',
			'image_limit',
			'image_scaling',
			'width',
			'height',
			'scaling',
		);

		add_action( 'admin_init', array( $this, 'save_options' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return  object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Display the importer welcome screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<form class="">
				<?php wp_nonce_field( 'wetu_importer_save', 'wetu_importer_save_options' ); ?>
				
				<h1><?php esc_html_e( 'General', 'wetu-importer' ); ?></h1>
				<table class="form-table">
					<tbody>
						<tr class="form-field">
							<th scope="row">
								<i class="dashicons-before dashicons-admin-network"></i> <label for="wetu_api_key"> <?php esc_html_e( 'Key', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="text" value="" name="wetu_api_key" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_tour_descriptions"><?php esc_html_e( 'Disable Tour Descriptions', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="disable_tour_descriptions" />
								<small><?php esc_html_e( 'If you are going to manage your tour descriptions on this site and not on WETU then enable this setting.', 'wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_accommodation_descriptions"><?php esc_html_e( 'Disable Accommodation Descriptions', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="disable_accommodation_descriptions" />
								<small><?php esc_html_e( 'If you are going to edit the accommodation descriptions imported then enable this setting.', 'wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_accommodation_excerpts"><?php esc_html_e( 'Disable Accommodation Excerpts', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="disable_accommodation_excerpts" />
								<small><?php esc_html_e( 'If you are going to edit the accommodation excerpts then enable this setting.', 'wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_destination_descriptions"><?php esc_html_e( 'Disable Destinations Descriptions', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="disable_destination_descriptions" />
								<small><?php esc_html_e( 'If you are going to edit the destination descriptions on this site then enable this setting.', 'wetu-importer' ); ?></small>
							</td>
						</tr>					
					</tbody>
				</table>

				<h1><?php esc_html_e( 'Images', 'wetu-importer' ); ?></h1>

				<table class="form-table">
					<tbody>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_replacing"><?php esc_html_e( 'Replace Images', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="image_replacing" />
								<p><?php esc_html_e( 'Do you want your images to be replaced on each import.', 'wetu-importer' ); ?></p>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_limit"> <?php esc_html_e( 'Limit the amount of images imported to the gallery', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="" type="text" value="" name="image_limit" />
							</td>
						</tr>

						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_scaling"><?php esc_html_e( 'Enable Image Scaling', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox" checked="checked" name="image_scaling" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="password"> <?php esc_html_e( 'Width (px)', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="1024" type="text" value="800" name="width" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="password"> <?php esc_html_e( 'Height (px)', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="768" type="text"  value="600" name="height" />
							</td>
						</tr>

						<tr class="form-field -wrap">
							<th scope="row">
								<label for="password"> <?php esc_html_e( 'Scaling', 'wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="radio" checked="checked" name="scaling" value="raw" /> <?php esc_html_e( 'Get the Full size image, no cropping takes place.', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="c" /> <?php esc_html_e( 'Crop image to fit fully into the frame, Crop is taken from middle, preserving as much of the image as possible.', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="h" /> <?php esc_html_e( 'Crop image to fit fully into the frame, but resize to height first, then crop on width if needed', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="w" /> <?php esc_html_e( 'Crop image to fit fully into the frame, but resize to width first, then crop on height if needed', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="nf" /> <?php esc_html_e( 'Resize the image to fit within the frame. but pad the image with white to ensure the resolution matches the frame', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="n" /> <?php esc_html_e( 'Resize the image to fit within the frame. but do not upscale the image.', 'wetu-importer' ); ?><br />
								<input type="radio" checked="checked" name="scaling"  value="W" /> <?php esc_html_e( 'Resize the image to fit within the frame. Image will not exceed specified dimensions', 'wetu-importer' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	}

	/**
	 * Save the options fields
	 *
	 * @return void
	 */
	public function save_options() {
		if ( ! isset( $_POST['wetu_importer_save_options'] ) || ! wp_verify_nonce( $_POST['wetu_importer_save_options'], 'wetu_importer_save' ) ) {
			return;
		}
		$data_to_save = array();
		foreach ( $this->fields as $field ) {
			$data_to_save[ $field ] = false;
			if ( isset( $_POST[ $field ] ) ) {
				$data_to_save[ $field ] = $_POST[ $field ];
			}
		}

		update_option( 'wetu_importer_settings', $data_to_save );
	}
}
