<?php
require_once 'interfaces.php';

/**
 * WPeCommerceAccess implementation that works with 3.8 versions of WP e-Commerce.
 */
class WPeCommerceAccessV38 implements WPeCommerceAccess {

	public function get_product($productid, $options = array('image_size'=>'wpsc_thumbnail_size')) {
		$result = get_post($productid);
		if ($result->post_status == 'publish') {
			$product = array('name'=>$result->post_title, 'description'=>$result->post_content, 'id'=>$result->ID);
			$product['image_src'] = $this->get_image_src($result->ID, $options);
			$product['product_href'] = get_permalink($result->ID);
			return $product;
		} else {
			return NULL;
		}
	}

	public function get_random_product($categoryID = -1, $options = array('image_size'=>'wpsc_thumbnail_size')) {
		global $wpdb;

        // TODO see wpsc_display_featured_products_page(); for how to search for 'sticky_products' - could add this as feature.
		$category_restriction = "";
		if ($categoryID > 0) {
			$category_restriction = "AND {$wpdb->terms}.term_id = $categoryID";
		}

		$query = "SELECT post.ID, post.post_content, post.post_title FROM {$wpdb->posts} AS post
		LEFT JOIN {$wpdb->term_relationships} ON (post.ID = {$wpdb->term_relationships}.object_id)
		LEFT JOIN {$wpdb->term_taxonomy} ON ({$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id)
		LEFT JOIN {$wpdb->terms} ON ({$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id) 
		WHERE post.post_status = 'publish' 
		AND {$wpdb->term_taxonomy}.taxonomy = 'wpsc_product_category' " .
		$category_restriction . " 
		ORDER BY RAND()
		LIMIT 1";

		$results = $wpdb->get_results($query, OBJECT);

		if (count($results) < 1) {
			return NULL;
		}

		$product = array('name'=>$results[0]->post_title, 'description'=>$results[0]->post_content, 'id'=>$results[0]->ID);
		$product['image_src'] = $this->get_image_src($results[0]->ID, $options);
		$product['product_href'] = get_permalink($results[0]->ID);
		return $product;
	}

	public function get_product_price($productid) {
		$org_price = get_product_meta($productid, 'price', true );
		$special_price = get_product_meta($productid, 'special_price', true );
		$price = (!empty($special_price)) ? $special_price : $org_price;
		
		return wpsc_currency_display($price);
	}

	public function get_available_products() {
		$products = get_posts(array('post_type'=>'wpsc-product', 'post_status'=>'publish', 'numberposts'=>-1,
			'order'=>'ASC', 'orderby'=>'title'));
		// repackage as an array of arrays containing 'id' & 'name' keys
		$results = array();
		foreach ($products as $product) {
			$results[] = array('id'=>$product->ID, 'name'=>$product->post_title);
		}
		return $results;
	}

	public function get_available_categories() {
		$terms = get_terms('wpsc_product_category');
		// repackage terms as array of arrays with 'id' & 'name' keys
		$results = array();
		foreach ($terms as $term) {
			$results[] = array('id'=>$term->term_id, 'name'=>$term->name);
		}
		return $results;
	}

	/**
	 * Generates the 'src' attribute for the product image based on the specified options.
	 * @param int $productid the ID of the product.
	 * @param array $options array containing the mandatory size option 'image_size', and the conditional options
	 * 'image_width' and 'image_height' which must be present if 'image_size' is 'custom_size'.
	 * @return string
	 */
	private function get_image_src($productid, $options) {
		$debug = $options['show_debug'];
		$attached_images = (array)get_posts(array(
			'post_type'   => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $productid,
			'orderby'     => 'menu_order',
			'order'       => 'ASC'
		));

		if (count($attached_images) > 0 && $attached_images[0]->ID > 0) {
			$attached_image = $attached_images[0];
			switch ($options['image_size']) {
				case 'full_size':
					$url = wp_get_attachment_url($attached_image->ID);
					$this->e_debug_comment("pndl-featuredproduct full_size: $url", $debug);
					return $url;
				case 'wpsc_thumbnail_size':
					// retrieve wpsc options for thumbnail size
					$image_width = get_option('product_image_width');
					$image_height = get_option('product_image_height');
					$url = wpsc_product_image($attached_image->ID, $image_width, $image_height);
					$this->e_debug_comment("pndl-featuredproduct wpsc_thumbnail_size: $url", $debug);
					return $url;
				case 'custom_size':
					$image_width = $options['image_width'];
					$image_height = $options['image_height'];
					$url = wpsc_product_image($attached_image->ID, $image_width, $image_height);
					$this->e_debug_comment("pndl-featuredproduct custom_size: $url", $debug);
					return $url;
			}
		} else {
			// use WP e-Commerce function for default no-image URL
			if ($debug) {
				echo "<!--\n";
				echo "attached_images:\n";
				print_r($attached_images);
				echo "\nget_post($productid):\n";
				print_r(get_post($productid));
				echo "\n-->\n";
			}
			return wpsc_product_no_image_fallback();
		}
	}

	/**
	 * Adds $comment as an HTML comment if WP_DEBUG is true.
	 */
	private function e_debug_comment($comment, $debug) {
		if ($debug) {
			echo "\n<!-- $comment -->\n";
		}
	}
}
?>