<?php
/**
 * File to handle e-commerce payments.
 *
 * @package RawConscious.
 */

/**
 * General Authentication
 *
 * @param string $order_id      Order ID.
 */
function rc_wcpos_get_order_token( string $order_id ) {

	$order_data = wc_get_order( $order_id );

	if ( ! $order_data ) {
		return array(
			'isSuccess' => false,
		);
	}

	$order_total = $order_data->get_total();
	$customer_id = $order_data->get_customer_id();

	$condtion = "user_id = '" . $customer_id . "'";

	$customer_data = rc_wcpos_get_row( 'rc_auth', $condtion );

	if ( ! $customer_data ) {
		return array(
			'isSuccess' => false,
		);
	}

	$customer_details = array();

	$user = get_user_by( 'ID', $customer_id );

	$customer_details['customer_id']    = 'customer_' . $customer_id;
	$customer_details['customer_phone'] = $customer_data['user_phone'] ? $customer_data['user_phone'] : $user->billing_phone;

	// Cashfree payment gateway doesn't accept integer order id, so we are concatinating prefix 'order_' to order_id.
	if ( defined( 'LOCAL_ENV' ) && true === LOCAL_ENV ) {
		$string_orderid = 'order_' . $order_id;
	} else {
		$string_orderid = 'dev_' . $order_id;
	}

	$curl_data = array(
		'order_id'         => $string_orderid,
		'order_amount'     => $order_total,
		'order_currency'   => 'INR',
		'customer_details' => $customer_details,
	);

	return rc_wcpos_get_payment_token( $curl_data );
}

/**
 * Function to update payment details to WooCommerce.
 *
 * @param string $order_id            Order Id With Prefix 'order_'.
 * @param array  $cashfree_response   Cashfree Response.
 *
 * @return array
 */
function rc_wcpos_update_ecommerce_payment_cashfree( string $order_id, array $cashfree_response ) {
	// To extract original order id (eliminating prefix 'order_') we used substr() method.
	if ( defined( 'LOCAL_ENV' ) && true === LOCAL_ENV ) {
		$order_id = substr( $order_id, 6 );
	} else {
		$order_id = substr( $order_id, 4 );
	}

	$order_status = $cashfree_response['order_status'];
	$payment_id   = (string) $cashfree_response['cf_order_id'];

	$update_data = array(
		'order_id'     => $order_id,
		'payment_id'   => $payment_id,
		'order_status' => $order_status,
	);

	if ( ! $order_id || ! $payment_id || ! $order_status ) {
		return rc_wcpos_response_handler( 400 , 'Insufficient Payment Details', null );
	} else {
		return rc_wcpos_update_woocommerce_order( $update_data );
	}
}

/**
 * Function to update payment details to WooCommerce.
 *
 * @param array $payment_data  Payment Details.
 *
 * @return array
 */
function rc_wcpos_update_ecommerce_payment( array $payment_data ) {
	$order_id       = $payment_data['orderId'] ? $payment_data['orderId'] : null;
	$payment_id     = $payment_data['paymentId'] ? $payment_data['paymentId'] : null;
	$payment_status = $payment_data['paymentStatus'] ? $payment_data['paymentStatus'] : null;

	if ( ! $order_id || ! $payment_id || ! $payment_status ) {
		return rc_wcpos_response_handler( 400, 'Insufficient Payment Details', null );
	} else {
		$update_data = array(
			'order_id'     => $order_id,
			'payment_id'   => $payment_id,
			'order_status' => $payment_status,
		);
		return rc_wcpos_update_woocommerce_order( $update_data );
	}
}

/**
 * Function to Update payment details to WooCommerce Order.
 *
 * @param array $update_data    Update Data.
 *
 * @return array
 */
function rc_wcpos_update_woocommerce_order( array $update_data ) {
	$order_id     = $update_data['order_id'];
	$payment_id   = $update_data['payment_id'];
	$order_status = $update_data['order_status'];

	$order = wc_get_order( $order_id );

	if ( $order ) {
		$payment_status = ( 'PAID' === $order_status ) ? 'wc-processing' : 'wc-failed';
		$customer_note  = 'Your payment is successful – merchant transaction id: ' . $payment_id;

		$order->set_transaction_id( $payment_id );
		$order->set_payment_method( 'cashfree' );
		$order->set_payment_method_title( 'Cashfree Payments' );
		$order->set_status( $payment_status );
		$order->add_order_note( $customer_note );
		$order->save();

		return rc_wcpos_response_handler( 200, 'Payment Details Updated', $order_status );
	} else {
		return rc_wcpos_response_handler( false, 'Order not found. Payment details not updated.', null );
	}
}
