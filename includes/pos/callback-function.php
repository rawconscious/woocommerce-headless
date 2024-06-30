<?php
/**
 * Callback function for POS Routes.
 *
 * @package RawConscious.
 */

/**
 * Callback Function to handle POS Customer.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_handle_pos_customer( WP_REST_Request $request ) {
	$headers      = $request->get_headers();
	$request_body = json_decode( $request->get_body(), true );

	$auth_token    = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	$customer_data = ! empty( $request_body['customerData'] ) ? $request_body['customerData'] : null;
	$product_data  = ! empty( $request_body['productData'] ) ? $request_body['productData'] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$member_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( empty( $member_id ) || ! $member_id ) {
		return rc_wcpos_response_handler( 401, 'Member Id is Empty', null );
	}

	if ( empty( $customer_data ) || ! $customer_data ) {
		return rc_wcpos_response_handler( 400, 'Customer Data is Empty', null );
	}

	if ( empty( $product_data ) || ! $product_data ) {
		return rc_wcpos_response_handler( 400, 'Customer Data is Empty', null );
	}

	$password = ! empty( $customer_data['password'] ) ? $customer_data['password'] : null;
	$email    = ! empty( $customer_data['email'] ) ? $customer_data['email'] : null;
	$phone    = ! empty( $customer_data['phone'] ) ? (int) $customer_data['phone'] : null;

	$is_existed = false;

	if ( null !== $email && null !== $phone ) {
		$condition  = "user_email = '$email' OR user_phone = $phone ";
		$is_existed = (int) rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
	} elseif ( null !== $email && null === $phone ) {
		$condition  = "user_email = '$email'";
		$is_existed = (int) rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
	} else {
		$condition  = "user_phone = $phone ";
		$is_existed = (int) rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
	}

	if ( $is_existed ) {
		$new_customer_data = array(
			'userId'   => $is_existed,
			'memberId' => $member_id,
		);
	} else {
		if ( $email ) {
			$email_otp = rc_wcpos_email_otp_handler( $email );
		}

		if ( 200 === $email_otp->status ) {
			$response_data = $email_otp->data;
			$data 			= $response_data['data'];
			return rc_wcpos_response_handler( 201, 'User not exists otp sent', $data );
		} else {
			return $email_otp;
		}
	}

	$order_details = array(
		'productData'  => $product_data,
		'customerData' => $new_customer_data,
	);

	return rc_wcpos_create_pos_order( $is_existed, $order_details );
}

/**
 * Callback Function Verify User Role.
 * 
 * @param WP_REST_Request $request	Rest Request.
 * 
 * @return array
 */
function rc_wcpos_verify_pos_manager( WP_REST_Request $request ) {
	$headers      = $request->get_headers();

	$auth_token    = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	
	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( empty( $user_id ) || ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'Member Id is Empty', null );
	}

	$condition       = "user_id = $user_id";
	$is_valid_member = true;
	$member_data     = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
	$member_role     = rc_wcpos_validate_user_role( $user_id, 'pos_manager' );
	$is_admin   	 = rc_wcpos_validate_user_role( $user_id, 'administrator' );
	$is_valid_member = $member_data ? $is_valid_member : false;
	$is_valid_member = ( $member_role || $is_admin ) ? $is_valid_member : false;

	if ( ! $is_valid_member ) {
		return rc_wcpos_response_handler( 400, 'POS manager is not valid', null );
	} else {
		return rc_wcpos_response_handler( 201, 'POS manager is valid', null );
	}
}