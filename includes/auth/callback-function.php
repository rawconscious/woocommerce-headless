<?php
/**
 * Call back function for auth router.
 *
 * @package RawConscious.
 */

/**
 * Function to handle login.
 *
 * @param WP_REST_Request $request   Rest Request.
 */
function rc_wcpos_login_handler( WP_REST_Request $request ) {
	$login_details = json_decode( $request->get_body(), true );

	$password = ! empty( $login_details['password'] ) ? $login_details['password'] : null;
	$email    = ! empty( $login_details['email'] ) ? $login_details['email'] : null;
	$phone    = ! empty( $login_details['phone'] ) ? (int) $login_details['phone'] : null;

	if ( ! $email && ! $phone ) {
		return rc_wcpos_response_handler( 403, 'Email and Phone both fields are empty', null );
	}

	if ( ! $password ) {
		return rc_wcpos_response_handler( 403, 'Password is empty', null );
	}

	return rc_wcpos_login_customer( $password, $email, $phone );
}

/**
 * Function to handle register.
 *
 * @param WP_REST_Request $request   Rest Request.
 */
function rc_wcpos_register_handler( WP_REST_Request $request ) {
	$signup_details = json_decode( $request->get_body(), true );

	$password = ! empty( $signup_details['password'] ) ? $signup_details['password'] : null;
	$email    = ! empty( $signup_details['email'] ) ? $signup_details['email'] : null;
	$phone    = ! empty( $signup_details['phone'] ) ? (int) $signup_details['phone'] : null;

	if ( ! $email && ! $phone ) {
		return rc_wcpos_response_handler( 403, 'Email and Phone both fields are empty', null );
	}

	if ( ! $password ) {
		return rc_wcpos_response_handler( 403, 'Password is empty', null );
	}

	$signup_response = rc_wcpos_register_customer( $password, $email, $phone );

	if ( true !== $signup_response['isSuccess'] ) {
		return $signup_response;
	}

	return rc_wcpos_login_customer( $password, $email, $phone );
}

/**
 * Function which verify users.
 *
 * @param WP_REST_Request $request   Rest Request.
 */
function rc_wcpos_auth_verify( WP_REST_Request $request ) {
}

/**
 * Function which used to get user data.
 *
 * @param WP_REST_Request $request   Rest Request.
 */
function rc_wcpos_get_user( WP_REST_Request $request ) {
}

/**
 * Function which handles otp based authentication.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_auth_otp_generator( WP_REST_Request $request ) {
	$auth_details = json_decode( $request->get_body(), true );

	$phone = ! empty( $auth_details['phone'] ) ? $auth_details['phone'] : null;
	$email = ! empty( $auth_details['email'] ) ? $auth_details['email'] : null;

	if ( ! $phone && ! $email ) {
		return rc_wcpos_response_handler( 403, 'Empty email and password', null );
	}
	if ( ! $phone && $email ) {
		return rc_wcpos_email_otp_handler( $email );
	}
	if ( ! $email && $phone ) {
		return rc_wcpos_sms_otp_handler( $phone );
	}
}

/**
 * Function to validate OTP.
 *
 * @param WP_REST_Request $request  Rest Request.
 *
 * @return array
 */
function rc_wcpos_auth_verify_otp( WP_REST_Request $request ) {
	$otp_details = json_decode( $request->get_body(), true );

	$otp     = ! empty( $otp_details['otp'] ) ? (int) $otp_details['otp'] : null;
	$email   = ! empty( $otp_details['email'] ) ? $otp_details['email'] : null;
	$phone   = ! empty( $otp_details['phone'] ) ? (int) $otp_details['phone'] : null;
	$session = ! empty( $otp_details['session'] ) ? $otp_details['session'] : null;

	if ( ! $email && ! $phone ) {
		return rc_wcpos_response_handler( 403, 'Email and Phone both fields are empty', null );
	}

	if ( ! $otp || ! $session ) {
		return rc_wcpos_response_handler( 400, 'Unable process please try again', null );
	}

	return rc_wcpos_auth_validate_otp( $otp, $email, $phone, $session );
}
