<?php
/**
 * Custom REST Routes.
 *
 * @package rawconscious
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'rc_wcpos_register_payment_routes' );

/**
 * Register Routes for payments.
 */
function rc_wcpos_register_payment_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/payment/get-payment-token',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_create_payment',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/payment/verify-payment',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_verify_payment',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/payment/update-payment',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_payment_update_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
