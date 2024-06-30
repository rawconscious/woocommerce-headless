<?php
/**
 * Custom REST Routes.
 *
 * @package rawconscious
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'register_rc_cart_wcpos_routes' );

/**
 * Register Routes
 */
function register_rc_cart_wcpos_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/cart/insert-cart',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_insert_cart_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/cart/retrieve',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_retrieve_cart_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/cart/update-cart',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_update_cart_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/cart/remove-cart',
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => 'rc_wcpos_remove_cart_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/cart/verify-cart-items',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_verify_cart_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
