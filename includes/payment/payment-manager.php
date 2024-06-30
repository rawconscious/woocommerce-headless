<?php
/**
 * File to handle Payment.
 *
 * @package RawConscious
 */

/**
 * Create donation token from payment gateway.
 *
 * @param array $curl_data .
 */
function rc_wcpos_get_payment_token( array $curl_data ) {
	// CURL.
	$curl = curl_init();
	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL            => 'https://sandbox.cashfree.com/pg/orders',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => json_encode( $curl_data ),
			CURLOPT_HTTPHEADER     => array(
				'Accept: application/json',
				'Content-Type: application/json',
				'x-api-version: 2022-09-01',
				'x-client-id: 278039b657fcd8bd50ae6dc3b7930872',
				'x-client-secret: 9ca6d511b1a863e428f34c59d59270a974ba223f',
			),
		)
	);

	$response = curl_exec( $curl );
	$error    = curl_error( $curl );

	curl_close( $curl );

	if ( $error ) {
		add_option( 'cashfree_error' . $order_id, $error );
		return rc_wcpos_response_handler( 500, 'Payment gateway error', $error );
	} else {
		$result      = json_decode( $response, true );
		$return_data = array(
			'paymentId'        => $result['cf_order_id'],
			'paymentSessionId' => $result['payment_session_id'],
			'paymentMessagea'  => $result['message'],
		);
		add_option( 'cashfree_response' . $order_id, $response );
		return rc_wcpos_response_handler( 200, 'Payment Token Created Successfully', $return_data );
	}
}

/**
 * Verify Donation Status.
 *
 * @param array $payment_data  Payment Data .
 */
function rc_wcpos_payment_confirmation( array $payment_data ) {

	$is_pos   = ! empty( $payment_data['isPos'] ) ? $payment_data['isPos'] : false;
	$order_id = $payment_data['orderId'];

	if ( ! $is_pos ) {
		// Cashfree payment gateway doesn't accept integer order id, so we are concatinating prefix 'order_' to order_id.
		if ( defined( 'LOCAL_ENV' ) && true === LOCAL_ENV ) {
			$order_id = 'order_' . $order_id;
		} else {
			$order_id = 'dev_' . $order_id;
		}
	}

	// CURL.
	$curl = curl_init();
	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL            => 'https://sandbox.cashfree.com/pg/orders/' . $order_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
			CURLOPT_HTTPHEADER     => array(
				'Accept: application/json',
				'Content-Type: application/json',
				'x-api-version: 2022-09-01',
				'x-client-id: 278039b657fcd8bd50ae6dc3b7930872',
				'x-client-secret: 9ca6d511b1a863e428f34c59d59270a974ba223f',
			),
		)
	);

	$response = curl_exec( $curl );
	$error    = curl_error( $curl );

	curl_close( $curl );

	if ( $error ) {
		return rc_wcpos_response_handler( 400, 'Payment Gateway Error', wp_json_encode( $error ) );
	} else {
		$result       = json_decode( $response, true );
		$order_status = $result['order_status'];
		$payment_id   = (string) $result['cf_order_id'];

		if ( $is_pos ) {
			return rc_wcpos_update_pos_payment_cashfree( $order_id, $result );
		} else {
			return rc_wcpos_update_ecommerce_payment_cashfree( $order_id, $result );
		}
	}
}
