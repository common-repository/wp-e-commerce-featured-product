/*
 * JavaScript containing event callbacks for the widget admin panel.
 */

/**
 * Shows or hides a div based on whether the check box was selected or deselected.
 * @param element the check box source of the change event.
 * @param div_class the class name of the div to be looked up and hidden/shown.
 */
function pndl_show_or_hide_element_checkbox_clicked(element, div_class) {
	// we do not use IDs because we can't work out what they are (especially when new widgets are dragged in)
	// instead we start from element, navigate up to the widget container, then match the div tagged with
	// div_class
	div = jQuery(element).closest(".widget-content").find("." + div_class);
	if (element.checked) {
		div.show();
	} else {
		div.hide();
	}
}

/**
 * Invoked when the Product drop down is changed - enables or disables the product category drop down based on
 * whether the Random Product option is selected.
 */
function pndl_product_changed(element) {
	div = jQuery(element).closest(".widget-content").find(".pndl_category_selector");
	if (element.selectedIndex == 0) {
		div.removeAttr('disabled');
	} else {
		div.attr('disabled', 'disabled');
	}
}

/**
 * Invoked when the Image Size drop down is changed - shows or hides the image size div appropriately.
 */
function pndl_image_size_changed(element) {
	div = jQuery(element).closest(".pndl_image_outer").find(".pndl_size_options");
	if (element.selectedIndex == 2) {
		div.show();
	} else {
		div.hide();
	}
}

/**
 * Invoked when the Limit Description check box is clicked - enables or disables the limit text field.
 */
function pndl_limit_description_clicked(element) {
	input = jQuery(element).closest(".widget-content").find(".pndl_limit_description_length");
	if (element.checked) {
		input.removeAttr('disabled');
	} else {
		input.attr('disabled', 'disabled');
	}
}