<?php
interface WPeCommerceAccess {
	
	/**
	 * Returns the product details for $productID.
	 * @param int $productid the explicit product ID (must be > 1).
	 * @return array the product detail array.
	 */
	public function get_product($productid, $options = array());

	/**
	 * Returns a random product's details, with optionally
	 * @param int $categoryid optional categoryID - if no category is specified, a value of -1 is assumed which indicates no 
	 * category selection.
	 * @return array the product detail array.
	 */
	public function get_random_product($categoryid = -1, $options = array());

	/**
	 * Returns the product price as a string including currency.
	 * @param int $productid the product ID.
	 */
	public function get_product_price($productid);

	/**
	 * Returns the available products.
	 */
	public function get_available_products();

	/**
	 * Returns the available categories.
	 */
	public function get_available_categories();
}
?>