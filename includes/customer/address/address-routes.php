<?php
/**
 * Custom REST Routes.
 *
 * @package rawconscious
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'register_rc_address_wcpos_routes' );

/**
 * Register Routes
 */
function register_rc_address_wcpos_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/add',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rc_wcpos_insert_customer_address_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/get',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_retrieve_customer_address_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/get-single-address',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_retrieve_customer_address_by_id',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/update-address',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_update_customer_address',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/get-default-address',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_retrieve_default_customer_address',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/customer/address/update-default-address',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_update_default_customer_address',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
