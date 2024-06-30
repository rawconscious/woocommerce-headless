<?php
/**
 * Callback function for Posts Routes.
 *
 * @package RawConscious.
 */

/**
 * Callback Function to get all blogs.
 *
 * @param WP_REST_Request $request Request object.
 * 
 * @return array
 */
function rc_wcpos_post_get_posts( WP_REST_Request $request) {
	$post_id   = $request->get_param('post-id');
	$post_slug = $request->get_param('post-slug');

	$include = array();
	if ( $post_id ) {
		$include = array($post_id);
	}

	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'include'		 => $include,
		'name'	         => $post_slug,
	);

	$all_posts = get_posts( $args );

	$formatted_posts = array();

	foreach ( $all_posts as $post ) {
		$post_data = array(
			'postId'    => $post->ID,
			'postTitle' => $post->post_title,
			'postImage' => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'postContent' => $post->post_content,
		);

		$formatted_posts[] = $post_data;
	}

	if ( $formatted_posts ) {
		return rc_wcpos_response_handler( 200, 'Posts fetched successfully', $formatted_posts );
	} else {
		return rc_wcpos_response_handler( 204, 'No Posts found', null );
	}
}

/**
 * Callback Function to get single.
 *
 * @param WP_REST_Request $request Rest Request.
 * @return array
 */
function rc_wcpos_post_get_single_post( WP_REST_Request $request ) {

	$post_id = ! empty( $request->get_param( 'post_id' ) ) ? (int) $request->get_param( 'post_id' ) : null;

	if ( ! $post_id ) {
		return rc_wcpos_response_handler( 400, 'empty post Id', null );
	}

	$post = get_post( $post_id );

	if ( $post ) {
		$post_data = array(
			'postId'      => $post->ID,
			'postTitle'   => $post->post_title,
			'postContent' => $post->post_content,
			'postImage'   => get_the_post_thumbnail_url( $post->ID, 'full' ),
		);

		return rc_wcpos_response_handler( 200, 'Post Data fetched successfully', $post_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No post found', null );
	}
}

/**
 * Callback Function to get page content.
 *
 * @param WP_REST_Request $request Rest Request.
 * @return array
 */
function rc_wcpos_post_get_page( WP_REST_Request $request ) {

	$page_slug = ! empty( $request->get_param( 'page_slug' ) ) ? $request->get_param( 'page_slug' ) : null;

	if ( ! $page_slug ) {
		return rc_wcpos_response_handler( 400, 'Empty page slug', null );
	}

	$page = get_page_by_path( $page_slug, OBJECT, 'page' );

	if ( $page ) {
		$page_content = apply_filters( 'the_content', $page->post_content );

		$post_data = array(
			'postId'      => $page->ID,
			'postTitle'   => $page->post_title,
			'postContent' => $page_content,
		);

		return rc_wcpos_response_handler( 200, 'Post data fetched successfully', $post_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Posts found for page slug', null );
	}
}
