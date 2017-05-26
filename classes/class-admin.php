<?php
/**
 * @package   WETU_Importer_Admin
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Admin extends WETU_Importer {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
            <h2><?php _e('Welcome to the LSX Wetu Importer','wetu-importer'); ?></h2>
            <p>If this is the first time you are running the import, then follow the steps below.</p>
            <ul>
                <li>Step 1 - Import your <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=tour"><?php _e('Tours','wetu-importer'); ?></a></li>
                <li>Step 2 - The tour import will have created draft <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=accommodation"><?php _e('accommodation','wetu-importer'); ?></a> that will need to be imported.</li>
                <li>Step 3 - Lastly import the <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=destination"><?php _e('destinations','wetu-importer'); ?></a> draft posts created during the previous two steps.</li>
            </ul>

            <h3><?php _e('Additional Tools','wetu-importer'); ?></h3>
            <ul>
                <li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=connect_accommodation"><?php _e('Connect Accommodation','wetu-importer'); ?></a> <small><?php _e('If you already have accommodation, you can "connect" it with its WETU counter part, so it works with the importer.','wetu-importer'); ?></small></li>
                <?php if(class_exists('Lsx_Banners')){ ?>
                    <li><a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=banners"><?php _e('Sync High Res Banner Images','wetu-importer'); ?></a></li>
                <?php } ?>
            </ul>
		<?php
	}

}