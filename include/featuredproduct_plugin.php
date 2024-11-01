<?php
/**
 * Main plugin class.
 */
class PNDL_FeaturedProductPlugin {
	private $pluginFile;
	private $wpdb;
	private $wpecAccess;

	private function __construct($pluginFile) {
		global $wpdb;

		$this->pluginFile = $pluginFile;
		$this->wpdb = $wpdb;
	}

	/**
	 * Creates a new instance and registers the appropriate actions.
	 * @param string $pluginFile the path to the plugin file.
 	 * @return PNDL_FeaturedProductPlugin
	 */
	public static function create($pluginFile) {
		// instantiate the plugin
		$localPlugin = new PNDL_FeaturedProductPlugin($pluginFile);

		// register hook for displaying warning messages inline within the Plugins table.
		if (is_admin()) {
			// note that we're using "/" instead of DIRECTORY_SEPARATOR - it would appear that wordpress has the plugin
			// name registered using "/" regardless of platform.
			$pluginName = basename(dirname($pluginFile)) . "/" . basename($pluginFile);
			add_action("after_plugin_row_$pluginName", array($localPlugin, 'after_plugin_row'));

			add_action('admin_enqueue_scripts', array($localPlugin, 'admin_enqueue_scripts'));
		}

		// add hook for plugins_loaded in order to ensure the e-Commerce plugin is also loaded before initialising
		// wpecAccess.
		add_action('plugins_loaded', array($localPlugin, "plugins_loaded"));

		// hook for initialising our widget.
		add_action('widgets_init', array($localPlugin, "widgets_init"));


		return $localPlugin;
	}

	/**
	 * Callback that initialises the WPeCommerceAccess reference.
	 */
	public function plugins_loaded() {
		// initialise wpecAccess based on the current WP e-Commerce version
		if (!defined('WPSC_VERSION')) {
			$this->wpecAccess = NULL;
		} else if (version_compare(WPSC_VERSION, '3.8') < 0) {
			require_once 'class-wp-e-commerce-access-v37.php';
			$this->wpecAccess = new WPeCommerceAccessV37();
		} else {
			require_once 'class-wp-e-commerce-access-v38.php';
			$this->wpecAccess = new WPeCommerceAccessV38();
		}
	}

	/**
	 * Callback implementation, displays a warning message in the plugin table if the WP e-Commerce plugin is not detected.
	 */
	public function after_plugin_row() {
		if (!defined('WPSC_VERSION')) {
			printf('<tr><td /><td /><td><strong>%s:</strong> %s.<td></tr>', __('Warning'), 
				__('This plugin works with the "WP e-Commerce" plugin, which has not been detected on your installation. The widgets associated with this plugin will therefore not appear as expected.'));
		}
	}

	/**
	 * Callback implementation for queueing admin scripts.
	 */
	public function admin_enqueue_scripts() {
		// enqueue javascript used for widget settings panels
		$plugin_url = plugin_dir_url($this->pluginFile);
		wp_enqueue_script('pndl_script', $plugin_url . 'js/javascript.js', array('jquery'), '1.1.5');
	}

	/**
	 * Callback implementation that initialises our widget.
	 */
	public function widgets_init() {
		require_once 'featuredproduct_widget.php';
		register_widget('PNDL_FeaturedProductWidget');
	}

	/**
	 * Returns the WPeCommerceAccess object that provides access to the WP e-Commerce data based on the version of
	 * WP e-Commerce installed.
	 */
	public function getWPeCommerceAccess() {
		return $this->wpecAccess;
	}
}
?>