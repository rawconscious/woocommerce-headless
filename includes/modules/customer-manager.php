<?php
/**
 * Files which handles customer related issues.
 *
 * @package RawConscious.
 */

/**
 * Function to Generate OTP.
 *
 * @param string $customer_id Customer Id.
 *
 * @return boolean
 */
function rc_wcpos_handle_otp( string $customer_id ) {

	$generated_otp = rc_wcpos_generate_otp();
	$expiration    = 300;
	$transient_key = 'userverify_otp_' . $customer_id;

	$transient_result = set_transient( $transient_key, $generated_otp, $expiration );

	rc_wcpos_send_otp( $customer_id, $generated_otp );

	if ( $transient_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function to validate Generated OTP.
 *
 * @param string $customer_id   Customer ID.
 * @param string $entered_otp   Entered OTP.
 *
 * @return boolean
 */
function rc_wcpos_validate_otp( string $customer_id, string $entered_otp ) {

	$transient_key = 'userverify_otp_' . $customer_id;

	$generated_otp = get_transient( $transient_key );

	if ( $generated_otp === $entered_otp ) {
		$update_status = rc_wcpos_update_customer_verification( $customer_id );
		return true;
	} else {
		return false;
	}
}
