<?php
/**
 * Call back function for cart router.
 *
 * @package RawConscious.
 */

/**
 * Rest Callback Function to Insert Cart.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_insert_cart_handler( WP_REST_Request $request ) {
	$cart_details = json_decode( $request->get_body(), true );

	$cart_id = rc_wcpos_generate_cart_id();

	$insert_result = rc_wcpos_insert_cart( $cart_details, $cart_id );

	if ( $insert_result ) {
		return rc_wcpos_response_handler( 200, 'Inserted Successfully', $cart_id );
	} else {
		return rc_wcpos_response_handler( 400, 'Unable to update cart', null );
	}
}

/**
 * Rest Callback Function to Retrieve Cart.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_retrieve_cart_handler( WP_REST_Request $request ) {
	$headers = $request->get_headers();
	$cart_id = $request->get_param( 'cart-id' );
	$is_pos  = $request->get_param( 'is-pos' );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	
	if ( ! $auth_token ) {
		$user_id = null;
	} else {
		$user_id = rc_wcpos_verify_session_id( $auth_token );
		$user_id = ( ! empty( $user_id ) && $user_id ) ? (int) $user_id : null;
	}

	if ( null === $cart_id && null === $user_id ) {
		return rc_wcpos_response_handler( 403, 'Empty cart id or user id', null );
	}

	if ( null !== $cart_id && null !== $user_id ) {
		$condition = "( cart_id = '$cart_id' OR user_id = $user_id )";
	} elseif ( null !== $cart_id && null === $user_id ) {
		$condition = "( cart_id = '$cart_id' )";
	} else {
		$condition = "( user_id = $user_id )";
	}

	if ( $is_pos ) {
		$condition .= " AND ( cart_meta = 'is_pos: true' )";
	}

	$cart_result = rc_wcpos_get_row( 'rc_active_cart', $condition );

	if ( $cart_result ) {
			$cart_data = array(
				'cartId'   => $cart_result['cart_id'],
				'cartData' => json_decode( $cart_result['cart_data'] ),
			);
			return rc_wcpos_response_handler( 200, 'Cart Retrieved Successfully', $cart_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Cart Result', null );
	}
}

/**
 * Function to update cart.
 *
 * @param WP_REST_Request $request  Rest Reqeust.
 *
 * @return array
 */
function rc_wcpos_update_cart_handler( WP_REST_Request $request ) {
	$cart_details = json_decode( $request->get_body(), true );

	$cart_id   = ! empty( $cart_details['cartId'] ) ? $cart_details['cartId'] : null;
	$cart_data = ! empty( $cart_details['cartData'] ) ? wp_json_encode( $cart_details['cartData'] ) : null;

	if ( null === $cart_id || null === $cart_data ) {
		return rc_wcpos_response_handler( 403, 'Empty Cart Id or Cart Data', null );
	}

	$update_result = rc_wcpos_update_cart( $cart_data, $cart_id );

	if ( $update_result ) {
		return rc_wcpos_response_handler( 201, 'Cart Updated Successfully', null );
	} else {
		return rc_wcpos_response_handler( 400, 'Unable to update cart', null );
	}
}

/**
 * Rest Callback funtion to Remove Cart.
 *
 * @param WP_REST_Request $request  REST Request.
 *
 * @return array
 */
function rc_wcpos_remove_cart_handler( WP_REST_Request $request ) {
	$cart_id = json_decode( $request->get_body(), true );

	$drop_result = rc_wcpos_remove_cart( $cart_id );

	if ( $drop_result ) {
		return rc_wcpos_response_handler( 200, 'Cart Removed Successfully', $cart_id );
	} else {
		return rc_wcpos_response_handler( 400, 'Unable to remove cart', null );
	}
}

/**
 * Rest Callback funtion to Verify Cart.
 *
 * @param WP_REST_Request $request  REST Request.
 *
 * @return array
 */
function rc_wcpos_verify_cart_handler( WP_REST_Request $request ) {
	$cart_data = json_decode( $request->get_body(), true );

	return rc_wcpos_verify_product_availability( $cart_data );
}
