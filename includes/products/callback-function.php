<?php
/**
 * File which contains callback function for Product routes.
 *
 * @package RawConscious.
 */

/**
 * Callback function for get products.
 *
 * @param WP_REST_Request $request Rest Requests.
 *
 * @return array
 */
function rc_wcpos_get_products_handler( WP_REST_Request $request ) {
	$product_data = $request->get_params();

	$is_pos = $product_data['isPos'] ? $product_data['isPos'] : false;

	if ( $is_pos ) {
		return rc_wcpos_get_pos_products();
	} else {
		return rc_wcpos_get_woo_products( $product_data );
	}
}

/**
 * Callback function for get Categories.
 *
 * @param WP_REST_Request $request Rest Requests.
 *
 * @return array
 */
function rc_wcpos_get_categories_handler( WP_REST_Request $request ) {
	$categories_filters = $request->get_params();

	return rc_wcpos_get_woo_categories( $categories_filters );
}

/**
 * Callback function to check product stock.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_get_product_stock( WP_REST_Request $request ) {
	$product_id = $request->get_param( 'product_id' );

	if ( ! $product_id ) {
		return rc_wcpos_response_handler( 400, 'Product Id is empty', null );
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return rc_wcpos_response_handler( 400, 'Unable to get product details for ' . $product_id, null );
	}

	$available_stock = $product->get_stock_quantity();

	return rc_wcpos_response_handler( 200, 'Stock details fetched successfully', $available_stock );
}
