<?php
/**
 * Dedicated File to handle pos payments.
 *
 * @package RawConscious.
 */

/**
 * General Authentication for pos payment.
 *
 * @param int    $customer_id   Customer ID.
 * @param string $order_id      Order ID.
 *
 * @return array
 */
function rc_wcpos_get_pos_order_token( int $customer_id, string $order_id ) {

	$table_auth   = 'rc_auth';
	$table_orders = 'rc_wcpos_orders';

	$order_condition = "order_id = '" . $order_id . "'";

	$order_data = rc_wcpos_get_row( $table_orders, $order_condition );

	if ( ! $order_data ) {
		return rc_wcpos_response_handler( 400, 'Unable fetch order details. Please try again!!!', null );
	}

	$order_total = $order_data['order_value'];

	$customer_condtion = "user_id = $customer_id ";

	$customer_data = rc_wcpos_get_row( $table_auth, $customer_condtion );

	if ( ! $customer_data ) {
		return rc_wcpos_response_handler( 403, 'Unable to fetch customer details', null );
	}

	$customer_details = array();

	$customer_details['customer_id']    = 'customer_' . $customer_id;
	$customer_details['customer_phone'] = $customer_data['user_phone'];
	$customer_details['customer_email'] = $customer_data['user_email'];

	$curl_data = array(
		'order_id'         => $order_id,
		'order_amount'     => ( $order_total / 100 ),
		'order_currency'   => 'INR',
		'customer_details' => $customer_details,
	);

	return rc_wcpos_get_payment_token( $curl_data );
}

/**
 * Function to update payment details.
 *
 * @param string $order_id            Order ID.
 * @param array  $cashfree_response   Cashfree Response.
 *
 * @return array
 */
function rc_wcpos_update_pos_payment_cashfree( string $order_id, array $cashfree_response ) {
	$order_status = $cashfree_response['order_status'];
	$payment_id   = (string) $cashfree_response['cf_order_id'];

	$condition = "payment_id = '" . $payment_id . "'";

	$is_existed = rc_wcpos_get_var( 'rc_wcpos_payment', 'payment_id', $condition );

	if ( $is_exist ) {
		$paymentdb_result = rc_wcpos_update_payment( $payment_id, $order_status );
	} else {
		$paymentdb_result = rc_wcpos_insert_payment( $payment_id, $order_id, $order_status );
	}

	$orderdb_result = rc_wcpos_update_order( $order_id, $order_status );

	return rc_wcpos_response_handler( 200, 'Payment Details Updated', $order_status );
}


/**
 * Function to Update POS Payments.
 *
 * @param array $payment_data   Payment Data.
 *
 * @return array
 */
function rc_wcpos_update_pos_payment( array $payment_data ) {

	$order_id       = $payment_data['orderId'] ? $payment_data['orderId'] : null;
	$payment_id     = $payment_data['paymentId'] ? $payment_data['paymentId'] : null;
	$payment_status = $payment_data['paymentStatus'] ? $payment_data['paymentStatus'] : null;

	if ( null === $payment_id ) {
		$payment_id = rc_wcpos_generate_payment_id();
	}

	if ( ! $order_id || ! $payment_id || ! $payment_status ) {
		return rc_wcpos_response_handler( 400, 'Insufficient Payment Details', null );
	}

	$condition  = "payment_id = '" . $payment_id . "'";
	$is_existed = rc_wcpos_get_var( 'rc_wcpos_payment', 'payment_id', $condition );

	if ( $is_existed ) {
		$paymentdb_result = rc_wcpos_update_payment( $payment_id, $payment_status );
	} else {
		$paymentdb_result = rc_wcpos_insert_payment( $payment_id, $order_id, $payment_status );
	}

	$orderdb_result = rc_wcpos_update_order( $order_id, $payment_status );

	if ( $paymentdb_result && $orderdb_result ) {
		return rc_wcpos_response_handler( 200, 'Payment Status Updated', $payment_status );
	} else {
		return rc_wcpos_response_handler( 500, 'Unable to Update Details', null );
	}
}
