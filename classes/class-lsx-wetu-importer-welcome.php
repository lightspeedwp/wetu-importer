<?php
/**
 * The Welcome Screen for the Importer Plugin
 *
 * @package   lsx_wetu_importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

/**
 * The Welcome Screen for the Importer Plugin
 */
class LSX_WETU_Importer_Welcome {

	/**
	 * Holds instance of the class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
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
		<div class="row section">
			<h1><?php esc_html_e( 'Welcome to the LSX Importer for Wetu', 'lsx-wetu-importer' ); ?></h1>
			<p><?php esc_html_e( 'If this is the first time running the import, please follow the steps below.', 'lsx-wetu-importer' ); ?></p>
		</div>
		<?php
		$this->importer_steps();
		$this->welcome_blocks();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function importer_steps() {
		?>
		<div class="row section postbox">
			<div class="welcome-block-header">
				<p class="heading"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>"><?php esc_html_e( 'Import your tours', 'lsx-wetu-importer' ); ?></a></p>
				<p class="value"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>"><span>1</span></a></p>
			</div>
			<div class="welcome-block-header">
				<p class="heading"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>"><?php esc_html_e( 'Import accommodation', 'lsx-wetu-importer' ); ?></a></p>
				<p class="value"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>"><span>2</span></a></p>
			</div>
			<div class="welcome-block-header">
				<p class="heading"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>"><?php esc_html_e( 'Import destination', 'lsx-wetu-importer' ); ?></a></p>
				<p class="value"><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>"><span>3</span></a></p>
			</div>
			<div class="welcome-block-header">
				<p class="heading"><?php esc_html_e( 'Done', 'lsx-wetu-importer' ); ?></p>
				<p class="value"><span class="dashicons dashicons-yes"></span></p>
			</div>
			<div class="spacer"></div>
		</div>
		<?php
	}

	/**
	 * Outputs the welcome blocks on the welcome screen
	 *
	 * @return void
	 */
	public function welcome_blocks() {
		?>
		<div class="row section">
			<div class="welcome-block postbox">
				<?php $this->tour_block(); ?>
			</div>
			<div class="welcome-block postbox">
				<?php $this->accommodation_block(); ?>
			</div>
			<div class="welcome-block postbox">
				<?php $this->destination_block(); ?>
			</div>
			<div class="welcome-block postbox">
				<?php $this->end_block(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Outputs the Tour Block.
	 *
	 * @return void
	 */
	public function tour_block() {
		?>
			<h2 class="title"><?php esc_html_e( 'Importing tours', 'lsx-wetu-importer' ); ?></h2>
			<p class="excerpt"><?php esc_html_e( 'Search for tours, select the ones you want to import and choose the data you want to sync on import. All connected accommodadtions will be imported as drafts to be pubished after completing the tour import.', 'lsx-wetu-importer' ); ?></p>
			<p><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>" class="button button-primary"><?php esc_html_e( 'Sync tours', 'lsx-wetu-importer' ); ?></a></p>
			<p>
				<ul class="link-list">
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>#publish"><?php esc_html_e( 'Published', 'lsx-wetu-importer' ); ?>  (<?php echo esc_attr( lsx_wetu_get_post_count( 'tour', 'publish ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>#pending"><?php esc_html_e( 'Pending', 'lsx-wetu-importer' ); ?>  (<?php echo esc_attr( lsx_wetu_get_post_count( 'tour', 'pending ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=tour' ); ?>#draft"><?php esc_html_e( 'Draft', 'lsx-wetu-importer' ); ?>  (<?php echo esc_attr( lsx_wetu_get_post_count( 'tour', 'draft ' ) ); ?>)</a></li>
				</ul>
			</p>			
		<?php
	}
	/**
	 * Outputs the Accommodation Block.
	 *
	 * @return void
	 */
	public function accommodation_block() {
		?>
			<h2 class="title"><?php esc_html_e( 'Import and publish accommodation', 'lsx-wetu-importer' ); ?></h2>
			<p class="excerpt"><?php esc_html_e( 'All accommodations connnected to your tours have been imported as drafts. Review the imported accommodations, sync selected data and publish.', 'lsx-wetu-importer' ); ?></p>
			<p><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>" class="button button-primary"><?php esc_html_e( 'Sync accommodation', 'lsx-wetu-importer' ); ?></a></p>

			<p>
				<ul class="link-list">
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>#publish"><?php esc_html_e( 'Published', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'accommodation', 'publish ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>#pending"><?php esc_html_e( 'Pending', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'accommodation', 'pending ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>#draft"><?php esc_html_e( 'Draft', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'accommodation', 'draft ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=accommodation' ); ?>#import"><?php esc_html_e( 'Wetu Queue', 'lsx-wetu-importer' ); ?>  (<?php echo esc_attr( lsx_wetu_get_queue_count( 'accommodation' ) ); ?>)</a></li>
				</ul>
			</p>			
		<?php
	}
	/**
	 * Outputs the Destination Block.
	 *
	 * @return void
	 */
	public function destination_block() {
		?>
			<h2 class="title"><?php esc_html_e( 'Import and publish destinations', 'lsx-wetu-importer' ); ?></h2>
			<p class="excerpt"><?php esc_html_e( 'All destinations and regions connnected to your tours & accommodations have been imported as drafts. Review the imported accommodations, sync selected data and publish.', 'lsx-wetu-importer' ); ?></p>
			<p><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>" class="button button-primary"><?php esc_html_e( 'Sync destinations', 'lsx-wetu-importer' ); ?></a></p>
			<p>
				<ul class="link-list">
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>#publish"><?php esc_html_e( 'Published', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'destination', 'publish ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>#pending"><?php esc_html_e( 'Pending', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'destination', 'pending ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>#draft"><?php esc_html_e( 'Draft', 'lsx-wetu-importer' ); ?> (<?php echo esc_attr( lsx_wetu_get_post_count( 'destination', 'draft ' ) ); ?>)</a></li>
					<li><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=lsx-wetu-importer&tab=destination' ); ?>#import"><?php esc_html_e( 'Wetu Queue', 'lsx-wetu-importer' ); ?>  (<?php echo esc_attr( lsx_wetu_get_queue_count( 'destination' ) ); ?>)</a></li>
				</ul>
			</p>
		<?php
	}
	/**
	 * Outputs the last welcome block
	 *
	 * @return void
	 */
	public function end_block() {
		?>
			<h2 class="title"><?php esc_html_e( 'Done! Check out your imported content', 'lsx-wetu-importer' ); ?></h2>
			<p><?php esc_html_e( 'If you’ve updated your content on Wetu then you can return to these steps at any stage to import and re-sync any updates', 'lsx-wetu-importer' ); ?></p>
		<?php
	}
}
