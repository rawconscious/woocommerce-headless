<?php
/**
 * Loads the WordPress hooks.
 *
 * @package RawConscious .
 */

/**
 * Adds new WordPress role recruiter.
 *
 * @access public
 */
function rc_wcpos_add_custom_wordpress_role() {
	$role         = 'pos_manager';
	$display_name = 'POS Manager';
	$capabilities = array(
		'delete_posts'           => false,
		'delete_private_posts'   => false,
		'delete_published_posts' => false,
		'delete Reusable Blocks' => false,
		'edit_posts'             => false,
		'edit_pages'             => false,
		'edit_private_posts'     => false,
		'edit_published_posts'   => false,
		'create Reusable Blocks' => false,
		'edit Reusable Blocks'   => false,
		'manage_categories'      => false,
		'manage_links'           => false,
		'moderate_comments'      => false,
		'publish_posts'          => false,
		'read'                   => true,
		'read_private_posts'     => false,
		'unfiltered_html'        => false,
		'upload_files'           => false,
	);

	if ( ( wp_roles()->is_role( 'pos_manager' ) ) ) {
		wp_roles()->remove_role( $role );
	}
	wp_roles()->add_role( $role, $display_name, $capabilities );
}

add_action( 'plugins_loaded', 'rc_wcpos_add_custom_wordpress_role' );

/**
 * Store Query variables in array.
 *
 * @param string $qvars Query Variable.
 */
function rc_pos_custom_query_vars( $qvars ) {
	$qvars[] = 'action';
	return $qvars;
}
add_filter( 'query_vars', 'rc_pos_custom_query_vars' );

/**
 * Hide WordPress admin bar for recruiter.
 */
function rc_pos_hide_wp_admin_bar() {
	global $wp;
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$role = $user->roles;
		if ( 0 === strpos( $wp->request, 'pos' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}
}

add_action( 'wp_head', 'rc_pos_hide_wp_admin_bar' );


/**
 * Handle CORS preflight request.
 */
function rc_wcpos_handle_preflight() {
	$allowed_origins_string = defined( 'RC_ALLOWED_ORIGIN' ) ? RC_ALLOWED_ORIGIN : '';
	$allowed_origins        = array_map( 'trim', explode( ',', $allowed_origins_string ) );

	$request_origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : '';

	// Check if the request origin is in the list of allowed origins.
	if ( in_array( $request_origin, $allowed_origins ) ) {
		header( 'Access-Control-Allow-Origin: ' . $request_origin );
	}

	header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
	header( 'Access-Control-Allow-Credentials: true' );
	header( 'Access-Control-Expose-Headers: *' );
	header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization' );
	if ( ! empty( $_SERVER['REQUEST_METHOD'] ) && 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
		status_header( 200 );
		exit();
	}
}

add_action( 'init', 'rc_wcpos_handle_preflight' );
