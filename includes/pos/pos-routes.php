<?php
/**
 * Files to Register Rest API for POS.
 *
 * @package RawConscious.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'rc_wcpos_register_pos_routes' );

/**
 * Function which register Rest Routes for POS.
 */
function rc_wcpos_register_pos_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/pos/handle-customer',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_handle_pos_customer',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/pos/verify-pos-manager',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_verify_pos_manager',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
