<?php
/**
 * Files to Register Rest API for Posts.
 *
 * @package RawConscious.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'rc_wcpos_register_post_routes' );

/**
 * Function which register Rest Routes for POS.
 */
function rc_wcpos_register_post_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'/posts/get-posts',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_post_get_posts',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/posts/get-post/(?P<post_id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_post_get_single_post',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'/posts/get-page/(?P<page_slug>[a-zA-Z0-9-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_post_get_page',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}
