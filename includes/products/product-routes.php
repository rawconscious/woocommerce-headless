<?php
/**
 * Rest Routes for Products.
 *
 * @package RawConscious.
 */

add_action( 'rest_api_init', 'register_rc_wcpos_product_routes' );

/**
 * Register Routes for order.
 */
function register_rc_wcpos_product_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'products/get-products',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_get_products_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'products/get-categories',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_get_categories_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'products/get-product-stock/(?P<product_id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_get_product_stock',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
