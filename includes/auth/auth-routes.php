<?php
/**
 * Custom REST Routes.
 *
 * @package rawconscious
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'register_rc_wcpos_auth_routes' );

/**
 * Register Routes
 */
function register_rc_wcpos_auth_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/login',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_login_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/register',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_register_handler',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/verify',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_auth_verify',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/get-user',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_get_user',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/generate-otp',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_auth_otp_generator',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/auth/validate-otp',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_auth_verify_otp',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
