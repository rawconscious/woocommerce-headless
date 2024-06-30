<?php
/**
 * Files which containe callback functions for payment routes.
 *
 * @package RawConscious.
 */

/**
 * Rest Callback Function To Create Payment.
 *
 * @param WP_REST_Request $request request.
 *
 * @return array
 */
function rc_wcpos_create_payment( WP_REST_Request $request ) {
	$headers       = $request->get_headers();
	$order_details = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}
	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( null === $user_id ) {
		return rc_wcpos_response_handler( 401, 'Unable to get user id', null );
	}

	$order_data  = $order_details['orderData'];
	$order_id    = ! empty( $order_data['orderId'] ) ? $order_data['orderId'] : null;
	$order_total = ! empty( $order_data['orderTotal'] ) ? $order_data['orderTotal'] : null;
	$is_pos      = ! empty( $order_details['isPos'] ) ? $order_details['isPos'] : false;

	if ( empty( $order_id ) || ! $order_id ) {
		return rc_wcpos_response_handler( 400, 'Order ID is empty', null );
	}

	if ( $is_pos ) {
		return rc_wcpos_get_pos_order_token( $user_id, $order_id );
	} else {
		return rc_wcpos_get_order_token( $order_id );
	}
}

/**
 * Rest Callback Function to Verify Payment.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array
 */
function rc_wcpos_verify_payment( WP_REST_Request $request ) {
	$headers      = $request->get_headers();
	$payment_data = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( null === $user_id ) {
		return rc_wcpos_response_handler( 401, 'Unable to get user id', null );
	}

	$order_id = ! empty( $payment_data['orderId'] ) ? $payment_data['orderId'] : null;

	if ( ! $order_id ) {
		return rc_wcpos_response_handler( false, 'Invalid Order Id' );
	}

	return rc_wcpos_payment_confirmation( $payment_data );
}

/**
 * Rest Callback Function to Update Payment.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array
 */
function rc_wcpos_payment_update_handler( WP_REST_Request $request ) {
	$headers      = $request->get_headers();
	$payment_data = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( null === $user_id ) {
		return rc_wcpos_response_handler( 401, 'Unable to get user id', null );
	}

	if ( empty( $payment_data ) || ! $payment_data ) {
		return rc_wcpos_response_handler( 400, 'Empty Payment Details', null );
	}

	$is_pos = $payment_data ? $payment_data['isPos'] : false;

	if ( $is_pos ) {
		return rc_wcpos_update_pos_payment( $payment_data );
	} else {
		return rc_wcpos_update_ecommerce_payment( $payment_data );
	}
}
