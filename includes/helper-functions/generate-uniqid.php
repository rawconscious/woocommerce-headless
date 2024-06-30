<?php
/**
 * File to generates Unique Id.
 *
 * @package RawConscious.
 */

/**
 * Generates Customer Id.
 *
 * @return string $customer_id .
 */
function rc_wcpos_generate_customer_id() {

	$customer_id = uniqid( 'customer-id-' );

	$condition = "customer_id = '$customer_id'";

	$is_existed = rc_wcpos_get_var( 'rc_wcpos_customer', 'customer_id', $condition );

	if ( $is_existed ) {
		rc_wcpos_generate_customer_id();
	} else {
		return $customer_id;
	}
}

/**
 * Generates Order Id.
 *
 * @return string $order_id .
 */
function rc_wcpos_generate_order_id() {

	$order_id = uniqid( 'order-id-' );

	$condition = "order_id = '$order_id'";

	$is_existed = rc_wcpos_get_var( 'rc_wcpos_orders', 'order_id', $condition );

	if ( $is_existed ) {
		rc_wcpos_generate_order_id();
	} else {
		return $order_id;
	}
}

/**
 * Generates Payment Id.
 *
 * @return string $payment_id .
 */
function rc_wcpos_generate_payment_id() {

	$payment_id = uniqid( 'payment-id-' );

	$condition = "payment_id = '$payment_id'";

	$is_existed = rc_wcpos_get_var( 'rc_wcpos_payment', 'order_id', $condition );

	if ( $is_existed ) {
		rc_wcpos_generate_payment_id();
	} else {
		return $payment_id;
	}
}

/**
 * Generate OTP.
 *
 * @return string $generated_otp   OTP Generated.
 */
function rc_wcpos_generate_otp() {
	$alphanumeric_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	$otp_lentgh = 6;

	$shuffled_string = str_shuffle( $alphanumeric_string );
	$generated_otp   = substr( $shuffled_string, 0, $otp_lentgh );

	return $generated_otp;
}

/**
 * Generate Cart ID
 *
 * @return string $cart_id      Cart Id.
 */
function rc_wcpos_generate_cart_id() {
	$cart_id = uniqid( 'cart-id-' );

	$condition = "cart_id = '$cart_id'";

	$is_existed = rc_wcpos_get_var( 'rc_active_cart', 'cart_id', $condition );

	if ( $is_existed ) {
		rc_wcpos_generate_cart_id();
	} else {
		return $cart_id;
	}
}

/**
 * Generate User id.
 *
 * @return string $user_name    Username.
 */
function rc_wcpos_generate_username() {
	$user_name = uniqid( 'username-' );

	$condition = "user_name = '$user_name'";

	$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_name', $condition );

	if ( $is_existed ) {
		rc_wcpos_generate_username();
	} else {
		return $user_name;
	}
}

/**
 * Function to generate password and password criteria:
 * Contains length of 8 character
 * Contains at least 1 number
 * Contains at least 1 special character
 * Contains at least 1 uppercase letter
 * Contains at least 1 lowercase letter
 *
 * @return string $password
 */
function rc_wcpos_generate_password() {
	$password_length    = 8;
	$digits             = '0123456789';
	$special_charecters = '!@#$%^&*()-_+=<>?';
	$uppercase_letter   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$lowercase_letter   = 'abcdefghijklmnopqrstuvwxyz';

	$password = '';

	// Ensuring all characters are present.
	$password .= $digits[ wp_rand( 0, strlen( $digits ) - 1 ) ];
	$password .= $special_charecters[ wp_rand( 0, strlen( $special_charecters ) - 1 ) ];
	$password .= $uppercase_letter[ wp_rand( 0, strlen( $uppercase_letter ) - 1 ) ];
	$password .= $lowercase_letter[ wp_rand( 0, strlen( $lowercase_letter ) - 1 ) ];

	// Generate remaining characters randomly.
	for ( $index = strlen( $password ); $index < $password_length; $index++ ) {
		$char_set  = $digits . $special_charecters . $uppercase_letter . $lowercase_letter;
		$password .= $char_set[ wp_rand( 0, strlen( $char_set ) - 1 ) ];
	}

	// Shuffle the password to make it more random.
	$password = str_shuffle( $password );

	return $password;
}
