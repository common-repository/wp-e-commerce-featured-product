=== Plugin Name ===
Tags: ecommerce, widgets
Requires at least: 2.8
Tested up to: 3.5.1
Stable tag: 1.1.5

Adds a Featured Product widget for use with the WP e-Commerce plugin.
This plugin is no longer officially supported.

== Description ==

A plugin that adds a Featured Product widget for use with the WP e-Commerce plugin. The featured product can either be explicitly selected such
that the same product will be shown each time the widget refreshes, or it can be randomly selected so that a different featured product is 
displayed each time the widget refreshes.

Requires PHP 5, and works with versions 3.7.x and 3.8.x of the WP e-Commerce plugin.

Support for this plugin has been discontinued since the author no longer uses WP e-Commerce and is unable to keep up with
the latest changes made to WP e-Commerce.

== Installation ==

1. Install and activate the plugin as usual.
1. Ensure the WP e-Commerce plugin is installed, and there is at least 1 (published) product in the store.
1. Go to Appearance - Widgets, and drag the "Featured Product" widget into a sidebar area and select a product from the drop-down.
1. If Random Product is selected, use the Category drop down to indicate whether the random product should be selected from any category,
   or if it should be restricted to a specific category.
1. Choose whether to show the product image or not.
1. If *Show product image* is selected, choose between the size options (*Thumbnail Size*, *Full Size* and *Custom Size*). 
   *Thumbnail Size* refers to the WP e-Commerce setting *Default Product Thumbnail Size* available from the WP e-Commerce
   settings page. *Custom Size* allows the image size to be specified explicitly for the particular widget.

Note that there are no plugin-specific settings, all configuration is done via the widget itself.

== Changelog ==
= 1.1.5 =
* Fixed wp_enqueue_script warning on dashboard.
* Added debugging information, included as html comments if the "Print debug" widget option is checked.
= 1.1.4 =
* Fixed issue where the widget title was not displayed under WP e-Commerce 3.8.x when choosing an explicit product.
* Made the widget title optional, controlled by a new checkbox in the widget settings.
= 1.1.3 =
* Fixed issue with missing image when using WP e-Commerce 3.7.x with the random product option.
= 1.1.2 =
* Added option to limit the length of the product description.
* Fixed incorrect version warning shown under Plugins when used with WP e-Commerce 3.8.x.
* Fixed incorrect image URL used when no product image is available (for 3.7.x and 3.8.x).
= 1.1.1 =
* Fixed defect with the available products drop-down showing only 5 products with WP e-Commerce 3.8.
= 1.1.0 =
* Added support for version 3.8 of the WP e-Commerce plugin, with backwards compatibility for version 3.7.
* Updated product image size options to allow for custom (per-widget) sizing.
* Added option to display price.
= 1.0.3 =
* Added option to display the product image as full size instead of being sized according to WP e-Commerce settings.
* Added Category selection for use with the Random Product option. When Random Product is selected, a Category can be chosen
  to restrict the range of products from which a random selection is made.
= 1.0.2 =
* Added optional display of product image, and made description display optional.
* Added internationalisation hooks.
* Corrected version numbering error.
= 1.0.1 =
* Corrected Plugin URI.
= 1.0.0 =
* Initial version.

