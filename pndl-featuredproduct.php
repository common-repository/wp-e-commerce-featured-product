<?php
/*
Plugin Name: WP e-Commerce Featured Product
Description: Works with the WP e-commerce plugin and adds a featured-product widget.
Version: 1.1.6 (Dev)
*/

// prevent direct calls to this file from doing anything.
if(!defined('ABSPATH') || !defined('WPINC')) {
	die();
}

// If anything else clashes with our main classes, report a warning on the admin plugin panel, and do nothing else. 
if (class_exists('PNDL_FeaturedProductPlugin', false) || class_exists('PNDL_FeaturedProductWidget', false)) {
	if (is_admin()) {
		// add action using create_function to avoid adding a global function.
		add_action("after_plugin_row_".basename(dirname(__FILE__)) . "/" . basename(__FILE__), 
			create_function('', 'echo "<tr><td /><td /><td><strong>'.__('Warning').':</strong> '. __('There is a name-clash with another plugin. This plugin will not function until the name clash has been resolved.').'<td></tr>";'));
	}
	return;
}
// No clash, so we can launch the plugin.
else {
	// import the file containing the plugin class definition
	require_once 'include/featuredproduct_plugin.php';

	// use factory method to create plugin, store reference in a global variable to make it accessible to widget instances.
	$pndl_fp_plugin = PNDL_FeaturedProductPlugin::create(__FILE__);
}
?>