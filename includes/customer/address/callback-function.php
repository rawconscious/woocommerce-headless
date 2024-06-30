<?php
/**
 * Callback function for handle address.
 *
 * @package RawConscious.
 */

/**
 * Callback function to insert Customer address.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array
 */
function rc_wcpos_insert_customer_address_handler( WP_REST_Request $request ) {
	$headers          = $request->get_headers();
	$customer_details = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'Unable to get user id', null );
	}

	$billing_address  = ! empty( $customer_details['billingAddress'] ) ? maybe_serialize( $customer_details['billingAddress'] ) : null;
	$shipping_address = ! empty( $customer_details['shippingAddress'] ) ? maybe_serialize( $customer_details['shippingAddress'] ) : null;

	if ( ! $billing_address && ! $shipping_address ) {
		return rc_wcpos_response_handler( 400, 'Address Data Missing', null );
	}

	if ( $billing_address ) {
		$insert_result = rc_wcpos_insert_user_meta( $user_id, 'billing_address', $billing_address );
		if ( ! $insert_result ) {
			return rc_wcpos_response_handler( 500, 'Unable to save address', false );
		}
	}

	if ( $shipping_address ) {
		$insert_result = rc_wcpos_insert_user_meta( $user_id, 'shipping_address', $shipping_address );
		if ( ! $insert_result ) {
			return rc_wcpos_response_handler( 500, 'Unable to save address', null );
		}
	}

	return rc_wcpos_response_handler( 201, 'Address Added', null );
}

/**
 * Callback function to retrieve customer addresses.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_retrieve_customer_address_handler( WP_REST_Request $request ) {
	$headers    	 = $request->get_headers();
	$address_id 	 = (int) $request->get_param( 'address-id' );
	$default_address = $request->get_param( 'default-address' );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'User ID is not valid', null );
	}
	if ( $default_address ) {
		return rc_wcpos_get_default_address( $user_id );
	} else {
		return rc_wcpos_get_addresses( $address_id, $user_id );
	}
}

/**
 * Callback function to retrieve customer address.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_retrieve_customer_address_by_id( WP_REST_Request $request ) {
	$headers    = $request->get_headers();
	$address_id = (int) json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'User ID is not valid', null );
	}

	if ( null === $address_id ) {
		return rc_wcpos_response_handler( 400, 'Unable to get address id', null );
	}

	$condition = "id = $address_id ";

	$address_data = rc_wcpos_get_var( 'rc_user_meta', 'meta_value', $condition );

	if ( ! $address_data ) {
		return rc_wcpos_response_handler( 204, 'Unable to fetch address', null );
	}

	$return_data = maybe_unserialize( $address_data );

	return rc_wcpos_response_handler( 200, 'Fetched Successfully', $return_data );
}

/**
 * Callback function to update customer address.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_update_customer_address( WP_REST_Request $request ) {
	$headers         = $request->get_headers();
	$address_details = json_decode( $request->get_body(), true );

	$auth_token   = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;
	$address_id   = ! empty( $address_details['addressId'] ) ? (int) $address_details['addressId'] : null;
	$address_data = ! empty( $address_details['addressData'] ) ? $address_details['addressData'] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'User ID is not valid', null );
	}

	if ( ! $address_id ) {
		return rc_wcpos_response_handler( 400, 'Address ID is empty', null );
	}

	if ( ! $address_data ) {
		return rc_wcpos_response_handler( 400, 'Address Data is empty', null );
	}

	$update_data = array(
		'meta_value' => maybe_serialize( $address_data ),
	);

	$condition = array(
		'id' => $address_id,
	);

	$update_result = rc_wcpos_update_user_meta( $update_data, $condition );

	if ( $update_result ) {
		return rc_wcpos_response_handler( 201, 'Address Updated', null );
	} else {
		return rc_wcpos_response_handler( 400, 'Unable to update order. Please try again', null );
	}
}

/**
 * Callback function to retrieve customer defaukt addresses (i.e. WooCommerce Customer Address).
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_retrieve_default_customer_address( WP_REST_Request $request ) {
	$headers = $request->get_headers();

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'User ID is not valid', null );
	}

	$billing_address = array(
		'firstName' => get_user_meta( $user_id, 'billing_first_name', true ),
		'lastName'  => get_user_meta( $user_id, 'billing_last_name', true ),
		'address1'  => get_user_meta( $user_id, 'billing_address_1', true ),
		'address2'  => get_user_meta( $user_id, 'billing_address_2', true ),
		'city'      => get_user_meta( $user_id, 'billing_city', true ),
		'state'     => get_user_meta( $user_id, 'billing_state', true ),
		'postcode'  => get_user_meta( $user_id, 'billing_postcode', true ),
		'country'   => get_user_meta( $user_id, 'billing_country', true ),
		'phone'     => get_user_meta( $user_id, 'billing_phone', true ),
	);

	$shipping_address = array(
		'firstName' => get_user_meta( $user_id, 'shipping_first_name', true ),
		'lastName'  => get_user_meta( $user_id, 'shipping_last_name', true ),
		'address1'  => get_user_meta( $user_id, 'shipping_address_1', true ),
		'address2'  => get_user_meta( $user_id, 'shipping_address_2', true ),
		'city'      => get_user_meta( $user_id, 'shipping_city', true ),
		'state'     => get_user_meta( $user_id, 'shipping_state', true ),
		'postcode'  => get_user_meta( $user_id, 'shipping_postcode', true ),
		'country'   => get_user_meta( $user_id, 'shipping_country', true ),
		'phone'     => get_user_meta( $user_id, 'shipping_phone', true ),
	);

	$return_data = array(
		'billing'  => $billing_address,
		'shipping' => $shipping_address,
	);

	return rc_wcpos_response_handler( 200, 'Address fetced successfully', $return_data );
}

/**
 * Callback function to update customer defaukt addresses (i.e. WooCommerce Customer Address).
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_update_default_customer_address( WP_REST_Request $request ) {
	$headers      = $request->get_headers();
	$address_data = json_decode( $request->get_body(), true );

	$auth_token = ! empty( $headers['authorization'][0] ) ? $headers['authorization'][0] : null;

	if ( ! $auth_token ) {
		return rc_wcpos_response_handler( 401, 'Empty Auth Token', null );
	}

	$user_id = (int) rc_wcpos_verify_session_id( $auth_token );

	if ( ! $user_id ) {
		return rc_wcpos_response_handler( 401, 'User ID is not valid', null );
	}

	if ( ! $address_data ) {
		return rc_wcpos_response_handler( 400, 'Empty Address Data', null );
	}

	$billing_address  = ! empty( $address_data['billing'] ) ? $address_data['billing'] : null;
	$shipping_address = ! empty( $address_data['shipping'] ) ? $address_data['shipping'] : null;

	if ( null === $billing_address && null === $shipping_address ) {
		return rc_wcpos_response_handler( 400, 'Empty Address Data', null );
	}

	$customer = new WC_Customer( $user_id );

	if ( null !== $billing_address ) {
		$customer->set_billing_first_name( $billing_address['first_name'] );
		$customer->set_billing_last_name( $billing_address['last_name'] );
		$customer->set_billing_address_1( $billing_address['address_1'] );
		$customer->set_billing_address_2( $billing_address['address_2'] );
		$customer->set_billing_city( $billing_address['city'] );
		$customer->set_billing_state( $billing_address['state'] );
		$customer->set_billing_postcode( $billing_address['postcode'] );
		$customer->set_billing_country( $billing_address['country'] );
		$customer->set_billing_phone( $billing_address['phone'] );
	}

	if ( null !== $shipping_address ) {
		$customer->set_billing_first_name( $shipping_address['first_name'] );
		$customer->set_shipping_last_name( $shipping_address['last_name'] );
		$customer->set_shipping_address_1( $shipping_address['address_1'] );
		$customer->set_shipping_address_2( $shipping_address['address_2'] );
		$customer->set_shipping_city( $shipping_address['city'] );
		$customer->set_shipping_state( $shipping_address['state'] );
		$customer->set_shipping_postcode( $shipping_address['postcode'] );
		$customer->set_shipping_country( $shipping_address['country'] );
		$customer->set_shipping_phone( $shipping_address['phone'] );
	}

	$result = $customer->save();

	if ( ! $result ) {
		return rc_wcpos_response_handler( 500, 'Unable to Update Address', null );
	}

	return rc_wcpos_response_handler( 201, 'Default Address Updated', null);
}
