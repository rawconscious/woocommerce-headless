<?php
/**
 * Rest Routes for Order.
 *
 * @package RawConscious.
 */

add_action( 'rest_api_init', 'register_rc_wcpos_order_routes' );

/**
 * Register Routes for order.
 */
function register_rc_wcpos_order_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'order/create-order',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rc_wcpos_create_order',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'order/get-order-list',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_get_order_list',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'order/get-order',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_get_order',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
