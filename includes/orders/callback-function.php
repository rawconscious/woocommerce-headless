<?php
/**
 * Files Conatins Rest Call back function for order routes.
 *
 * @package RawConscious.
 */

/**
 * Callback function for create orders.
 *
 * @param WP_REST_Request $request creatable.
 *
 * @return array
 */
function rc_wcpos_create_order( WP_REST_Request $request ) {
	$headers       = $request->get_headers();
	$order_details = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	$is_pos     = $order_details ? $order_details['isPos'] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'Empty User ID', null );
	}

	if ( $is_pos ) {
		return rc_wcpos_create_pos_order( $user_id, $order_details );
	} else {
		return rc_wcpos_create_woocommerce_order( $user_id, $order_details );
	}
}

/**
 * Callback function to get orders.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_get_order_list( WP_REST_Request $request ) {
	$headers    = $request->get_headers();
	$order_data = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	$is_pos     = ! empty( $order_data['isPos'] ) ? $order_data['isPos'] : false;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'Empty User ID', null );
	}

	if ( $is_pos ) {
		return rc_wcpos_get_pos_order_list( $user_id, $order_data );
	} else {
		return rc_wcpos_get_ecommerce_order_list( $user_id, $order_data );
	}
}

/**
 * Callback function to get single order.
 *
 * @param WP_REST_Request $request      Rest Request.
 *
 * @return array
 */
function rc_wcpos_get_order( WP_REST_Request $request ) {
	$headers    = $request->get_headers();
	$order_data = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	$order_id   = ! empty( $order_data['orderId'] ) ? (int) $order_data['orderId'] : null;
	$is_pos     = ! empty( $order_data['isPos'] ) ? $order_data['isPos'] : false;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'Empty User ID', null );
	}

	if ( ! $order_id ) {
		return rc_wcpos_response_hanlder( 400, 'Order ID is empty0', null );
	}

	if ( $is_pos ) {
		return rc_wcpos_response_handler( 201, 'Proceedable', null );
	} else {
		return rc_wcpos_get_ecommerce_order( $user_id, $order_id );
	}
}
