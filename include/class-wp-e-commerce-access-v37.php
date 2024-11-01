<?php
require_once 'interfaces.php';

/**
 * WPeCommerceAccess implementation that works with pre-3.8 versions of WP e-Commerce.
 */
class WPeCommerceAccessV37 implements WPeCommerceAccess {

	/** Query for 1 published, active product by its ID. */
	const productQuery = 'SELECT products.id,products.name,products.description,products.image,product_category.category_id FROM %1$swpsc_product_list AS products LEFT JOIN %1$swpsc_item_category_assoc AS product_category ON products.id=product_category.product_id WHERE products.id=%2$d AND products.publish=1 AND products.active=1';
	/** Query for 1 random published, active product. */
	const randomQuery  = 'SELECT products.id,products.name,products.description,products.image,product_category.category_id FROM %1$swpsc_product_list AS products LEFT JOIN %1$swpsc_item_category_assoc AS product_category ON products.id=product_category.product_id WHERE products.publish=1 AND products.active=1 %2$s ORDER BY rand() LIMIT 1';
	/** Query for all published, active products. */
	const availableProductQuery = 'SELECT products.id,products.name,products.description,product_category.category_id FROM %1$swpsc_product_list AS products LEFT JOIN %1$swpsc_item_category_assoc AS product_category ON products.id=product_category.product_id WHERE products.publish=1 AND products.active=1 ORDER BY products.name';
	/** Query for all active categories. */
	const availableCategoryQuery = 'SELECT categories.id,categories.name from %1$swpsc_product_categories AS categories WHERE categories.active=1';

	public function get_product($productid, $options = array('image_size'=>'wpsc_thumbnail_size')) {
		global $wpdb;
		return $this->perform_product_query(sprintf(WPeCommerceAccessV37::productQuery, $wpdb->prefix, $productid), $options);
	}

	public function get_random_product($categoryid = -1, $options = array('image_size'=>'wpsc_thumbnail_size')) {
		global $wpdb;
		$categoryRestriction = "";
		if ($categoryid > 0)
		{
			$categoryRestriction = "AND product_category.category_id=${categoryid}";
		}
		return $this->perform_product_query(sprintf(WPeCommerceAccessV37::randomQuery, $wpdb->prefix, $categoryRestriction), $options);
	}

	public function get_product_price($productid) {
		return nzshpcrt_currency_display(
			calculate_product_price($productid), /* null tax_status - not used by wpsc */ NULL, /* nohtml = */ true);		
	}

	public function get_available_products() {
		global $wpdb;
		return $wpdb->get_results(sprintf(WPeCommerceAccessV37::availableProductQuery, $wpdb->prefix), ARRAY_A);
	}

	public function get_available_categories() {
		global $wpdb;
		return $wpdb->get_results(sprintf(WPeCommerceAccessV37::availableCategoryQuery, $wpdb->prefix), ARRAY_A);
	}

	/**
	 * Performs the specified query and returns the resulting product details.
	 * @param string $product_query the SQL query that should return 1 row containing product details.
	 */
	private function perform_product_query($product_query, $options) {
		global $wpdb;
		$results = $wpdb->get_results($product_query, ARRAY_A);
		if (count($results) > 0) {
			$product = $results[0];
			$product['product_href'] = wpsc_product_url($results[0]['id'], $results[0]['category_id']);
			$product['image_src'] = $this->get_image_src($results[0], $options);
			return $product;
		} else {
			return NULL;
		}
	}

	/**
	 * Generates the 'src' attribute for the product image based on the specified options.
	 * @param array $result the result of the product query.
	 * @param array $options array containing the mandatory size option 'image_size', and the conditional options
	 * 'image_width' and 'image_height' which must be present if 'image_size' is 'custom_size'. 
	 */
	private function get_image_src($result, $options) {
		// return default no-image URL if image_id is undefined
		if ($result['image'] < 1) {
			return WPSC_URL."/images/no-image-uploaded.gif";
		}

		$image_src = "index.php?image_id={$result['image']}";
		switch ($options['image_size']) {
			case 'full_size':
				// nothing more to add.
				break;
			case 'wpsc_thumbnail_size':
				// retrieve wpsc options for thumbnail size
				$image_width = get_option('product_image_width');
				$image_height = get_option('product_image_height');
				break;
			case 'custom_size':
				$image_width = $options['image_width'];
				$image_height = $options['image_height'];
				break;
		}
		if (isset($image_width)) {
			$image_width_str = "&amp;width=".$image_width;
			$image_height_str = "&amp;height=".$image_height;
			$image_src .= "{$image_width_str}{$image_height_str}";
		}
		return $image_src;
	}
}

?>