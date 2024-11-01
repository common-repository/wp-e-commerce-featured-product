<?php
/**
 * Implementation for displaying a "Featured Product" widget in a sidebar. 
 */
class PNDL_FeaturedProductWidget extends WP_Widget {

	private $defaults = array('productid'=>-1, 'categoryid'=>-1, 'show_image'=>false, 'show_description'=>true,
		'image_size'=>'wpsc_thumbnail_size', 'image_width'=>'', 'image_height'=>'', 'show_price'=>false,
		'limit_description'=>false, 'limit_description_length'=>100, 
		'title'=>'Featured Product', 'show_title'=>true, 'show_debug'=>false);

	/**
	 * @var WPeCommerceAccess
 	 */
	private $wpecAccess;
 
	public function __construct() {
		// construct the widget with "Base ID" set to false - if set, the base ID appears in the html 'id' attribute. 
		parent::__construct(false, "Featured Product");
		global $pndl_fp_plugin;
		$this->wpecAccess = $pndl_fp_plugin->getWPeCommerceAccess();
	}

	/**
	 * Displays the widget.
	 */
	public function widget($args, $instance) {
		// convert legacy options first
		$instance = $this->convert_legacy_options($instance);
		// merge default settings with instance settings - instance settings will overwrite default settings
		$instance = array_merge($this->defaults, $instance);

		// extract general widget arguments ($before_title etc)
		extract($args);
		// extract instance fields $productid and $title
		extract($instance);

		// do not display the widget if we do not have a valid eCommerce access implementation (either due to unsupported version or
		// eCommerce plugin not being present and activated).
		if (is_null($this->wpecAccess)) {
			return;
		}

		$product = NULL;

		// set up options for retrieving product info (currently limited to image size).
		// image size options are now 'full_size', 'wpsc_thumbnail_size' and 'custom_size' (in which case image_width 
		// and image_height must be specified).

		// check for explicit product choice
		if ($productid > 0) {
			$product = $this->wpecAccess->get_product($productid, $instance);
		}

		// perform random query if product ID is -1, or if results from previous query are empty which may be the case
		// if the explicit product is no longer available and the user has not updated the widget
		if ($productid == -1 || is_null($product)) {
			$product = $this->wpecAccess->get_random_product($categoryid, $instance);
		}

		// if we still do not have any results, then do not display the widget.
		if (is_null($product)) {
			return;
		}

		// allow registered widget_title filters to filter the title
		$title = apply_filters('widget_title', $title);

		$product_name = $product['name'];
		$product_detail = $product['description'];
		$product_href = $product['product_href'];
		echo $before_widget;

		if ($instance['show_title'])
			echo $before_title . $title . $after_title;

		if ($instance['show_image']) {
			// display image as is done by WP e-Commerce's latest_product_widget.php, except include option
			// to display full-size image
			$img_src = $product['image_src'];
			echo "<div class=\"item_image\"><a href=\"$product_href\"><img alt=\"$product_name\" title=\"$product_name\" " .
				"src=\"$img_src\"/></a></div>";
		}	
		echo "<div class=\"item_name\"><a href=\"$product_href\"><strong>$product_name</strong></a></div>";
		if ($instance['show_description']) {
			$description = $instance['limit_description'] ?
				$this->limit_text($product_detail, $instance['limit_description_length']) : $product_detail;
			echo "<div class=\"item_description\">$description</div>";
		}
		if ($instance['show_price']) {
			printf('<div class="pricedisplay">%s</div>', $this->wpecAccess->get_product_price($product['id']));
		}
		echo $after_widget;
	}

	/**
	 * Verifies new widget settings, before the new settings are stored by the super-class.
	 */
	public function update($new_instance, $old_instance) {
		return array(
			"productid"        => (int)$new_instance["productid"],
		    "categoryid"       => (int)$new_instance["categoryid"], 
			"show_title"       => (boolean)$new_instance["show_title"],
		    "title"            => strip_tags(trim($new_instance["title"])),
			"show_description" => (boolean)$new_instance['show_description'],
			"show_price"       => (boolean)$new_instance['show_price'],
			"show_image"       => (boolean)$new_instance['show_image'],
			"image_size"       => strip_tags(trim($new_instance["image_size"])),
			"image_width"      => (int)$new_instance["image_width"],
			"image_height"     => (int)$new_instance["image_height"],
			"limit_description"=> (boolean) $new_instance["limit_description"],
		    // use old value for length if limit_description is not selected - otherwise the disabled limit_description_length
		    // sets the value back to 0
			"limit_description_length"=> ($new_instance["limit_description"] ? 
				(int) $new_instance["limit_description_length"] : $old_instance['limit_description_length']),
			"show_debug"       => (boolean) $new_instance["show_debug"]
		);
	}

	/**
	 * Generates this widget's specific form elements.
	 */
	public function form($instance) {
		// convert legacy options first
		$instance = $this->convert_legacy_options($instance);
		// merge default settings with instance settings - instance settings will overwrite default settings
		$instance = array_merge($this->defaults, $instance);

		// include default values for image width and height (defaults are taken from the WP e-Commerce settings
		// for product thumbnail) - these values are only used when image_size is 'custom'.
		if (!$instance['image_width']) {
			$instance['image_width'] = get_option('product_image_width', 148);
		}
		if (!$instance['image_height']) {
			$instance['image_height'] = get_option('product_image_height', 148);
		}

		// return early if e-Commerce plugin is not available
		if (is_null($this->wpecAccess)) {
			echo sprintf("<p><strong>%s:</strong> %s</p>", __("Warning"), 
				__("This widget works with the \"WP e-Commerce\" plugin, which has not been detected on your installation. It is therefore currently not possible to select a featured product, and this widget will not be displayed."));
			return;
		}

		// product selection
		// get available products
		$results = $this->wpecAccess->get_available_products();

		echo "<p>";
		if (!$results || count($results) == 0) {
			_e("There are currently no products publicly available on your website. Check that you have at least one product in your store, and that it has been \"published\".");
		} else {
			// print product selector
			printf('<label for="%s">%s:</label>', $this->get_field_id('productid'), __("Product"));
			// use onchange event instead of jQuery because id is dynamically generated making it difficult to search
			// for later. 
			printf('<select id="%s" name="%s" value="%s" class="widefat" onchange="pndl_product_changed(this)">', 
				$this->get_field_id('productid'), $this->get_field_name('productid'), $instance['productid']);
			printf('<option value="-1">%s</option>', __("Random Product"));
			foreach ($results as $result) {
				$selected = ($result['id'] == $instance['productid']) ? " selected" : "";
				printf('<option value="%s"%s>%s</option>', $result['id'], $selected, $result['name']);
			}
			echo "</select>";

			// print category selector, only enabled if Random Product is selected
			$results = $this->wpecAccess->get_available_categories();

			$randomProduct = $instance['productid'] == -1;
			printf('<label for="%s">%s:</label>', $this->get_field_id('categoryid'), __("Category"));
			// add the pndl_category_selector class to help us identify this dropdown later (can't use dynamic id)
			printf('<select id="%s" name="%s" value="%s" class="widefat pndl_category_selector" %s>', $this->get_field_id('categoryid'),
				$this->get_field_name('categoryid'), $instance['categoryid'], $randomProduct ? "" : "disabled=\"disabled\"");
			printf('<option value="-1">%s</option>', __("Any Category"));
			foreach ($results as $result) {
				$selected = ($result['id'] == $instance['categoryid']) ? " selected" : "";
				printf('<option value="%s"%s>%s</option>', $result['id'], $selected, $result['name']);
			}
			echo "</select>";
		}
		echo "</p>";

		// widget title
		echo "<p>";
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s onclick="pndl_show_or_hide_element_checkbox_clicked(this, \'pndl_widget_title\')"/>',
			$this->get_field_id('show_title'), $this->get_field_name('show_title'), ($instance['show_title'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('show_title'), __("Show widget title"));

		printf('<div class="pndl_widget_title" %s>', $instance['show_title'] ? '' : 'style="display:none;"');
		printf('<input id="%s" name="%s" value="%s" class="widefat" type="text" />', 
			$this->get_field_id('title'), $this->get_field_name('title'), $instance['title']);
		echo '</div>';
		echo "</p>";

		// product description check box
		echo "<p>";
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s onclick="pndl_show_or_hide_element_checkbox_clicked(this, \'pndl_description_limit\')"/>',
			$this->get_field_id('show_description'), $this->get_field_name('show_description'), ($instance['show_description'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('show_description'), __("Show product description"));

		// product description text limit
		printf('<div class="pndl_description_limit" %s>', $instance['show_description'] ? '' : 'style="display:none;"');
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s onclick="pndl_limit_description_clicked(this)" />', 
			$this->get_field_id('limit_description'), $this->get_field_name('limit_description'), ($instance['limit_description'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('limit_description'), __("Limit description to"));
		printf(' <input type="text" id="%s" name="%s" value="%s" size="3" class="pndl_limit_description_length" %s/>',
			$this->get_field_id('limit_description_length'), $this->get_field_name('limit_description_length'), 
			$instance['limit_description_length'], $instance['limit_description'] ? "" : "disabled");
		printf(' <label for="%s">%s</label> ', $this->get_field_id('limit_description_length'), __(" chars"));
		echo '</div>';
		echo "</p>";

		// show price check box
		echo "<p>";
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s />',
			$this->get_field_id('show_price'), $this->get_field_name('show_price'), ($instance['show_price'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('show_price'), __("Show product price"));
		echo "</p>";

		// show product image check box
		echo "<p>";
		// use onclick event handler rather than jQuery because the element id is assigned dynamically making it 
		// difficult to search for later.
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s onclick="pndl_show_or_hide_element_checkbox_clicked(this, \'pndl_image_outer\')" />', 
			$this->get_field_id('show_image'), $this->get_field_name('show_image'), ($instance['show_image'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('show_image'), __("Show product image"));
		echo "</p>";

		// show dropdown for size selection
		$image_size_id = $this->get_field_id('image_size');
		// add pndl_image_outer class to help identify this div from javascript (can't use id as described elsewhere)
		echo "<p>";
		printf('<div class="pndl_image_outer" %s>', $instance['show_image'] ? "" : 'style="display:none;"');
		printf('<label for="%s">%s:</label>', $this->get_field_id('image_size'), __("Image Size"));
		// use onchange to hide/show image size options
		printf('<select id="%s" name="%s" value="%s" class="widefat" onchange="pndl_image_size_changed(this)">', $image_size_id,
			$this->get_field_name('image_size'), $instance['image_size']);
		$size_options = array(
			array('image_size'=>'wpsc_thumbnail_size', 'name'=>__('Thumbnail Size')),
			array('image_size'=>'full_size', 'name'=>__('Full Size')),
			array('image_size'=>'custom_size', 'name'=>__('Custom Size')));
		foreach ($size_options as $size_option)
		{
			$selected = ($size_option['image_size'] == $instance['image_size']) ? " selected" : "";
			printf('<option value="%s"%s>%s</option>', $size_option['image_size'], $selected, $size_option['name']);
		}
		echo "</select>";

		printf('<div class="pndl_size_options" %s>', 
			($instance['show_image'] && $instance['image_size']=='custom_size') ? '' : 'style="display:none;"');
		echo '<p>';
		printf('<label for="%s">%s</label> ', $this->get_field_id('image_width'), __("Width:"));
		printf('<input type="text" id="%s" name="%s" value="%s" size="3" /> ', $this->get_field_id('image_width'), $this->get_field_name('image_width'), $instance['image_width']);
		printf('<label for="%s">%s</label> ', $this->get_field_id('image_width'), __("Height:"));
		printf('<input type="text" id="%s" name="%s" value="%s" size="3" />', $this->get_field_id('image_height'), $this->get_field_name('image_height'), $instance['image_height']);
		echo '</p>';
		printf('</div>');

		printf('</div>');
		echo "</p>";

		echo "<p>";
		printf('<input id="%s" name="%s" class="checkbox" type="checkbox"%s />',
			$this->get_field_id('show_debug'), $this->get_field_name('show_debug'), ($instance['show_debug'] ? " checked=\"checked\"" : ""));
		printf(' <label for="%s">%s</label>', $this->get_field_id('show_debug'), __("Print debug HTML comments"));
		echo "</p>";
	}

	/**
	 * Limits the input text, after stripping html tags, to <code>$length</code>, taking into account word boundaries.
	 * Appends the HTML ellipsis element.
	 * @param string $text the text to be limited.
	 * @param int $length the maximum number of characters to be retained.
	 * @return string
	 */
	private function limit_text($text, $length) {
		// strip HTML - too many potential issues if we try to allow for html
		$text = strip_tags($text);

     	// return text immediately if shorter than length
     	if (strlen($text) <= $length) {
			return $text;
     	}

		// find last space within length
		$last_space = strrpos(substr($text, 0, $length), ' ');
		$trimmed_text = substr($text, 0, $last_space);

		// Add ellipsis character, first removing , and . chars
		$trimmed_text = trim($trimmed_text, ',.');
		$trimmed_text .= "&hellip;";

		return $trimmed_text;
	}

	/**
	 * Converts legacy options to their current equivalent, and deletes redundant terms where appropriate.
	 * @param array $instance the widget instance array holding the widget options.
 	 * @return array the modified isntance array.
	 */
	private function convert_legacy_options($instance) {
		// convert the legacy option 'fullsize_image' to 'full_size' if present
		if (isset($instance['fullsize_image'])) {
			$instance['image_size'] = 'full_size';
			unset($instance['fullsize_image']);
		}
		// convert legacy title - if explicitly set to &nbsp; then intention was to hide the widget title:
		if (isset($instance['title']) && $instance['title'] == '&nbsp;') {
			$instance['title'] = '';
			$instance['show_title'] = false;
		}
		// else if show_title is not defined and the title was empty, the default title was used, so make this explicit 
		// in the settings:
		else if (!isset($instance['show_title']) && isset($instance['title']) && strlen($instance['title']) == 0) {
			$instance['show_title'] = true;
			$instance['title'] = 'Featured Product';
		}
		return $instance;
	}
}
?>