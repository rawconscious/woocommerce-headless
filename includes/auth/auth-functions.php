<?php
/**
 * File which includes authentication functions.
 *
 * @package Rawconscious.
 */

/**
 * Function to register customer.
 *
 * @param string $password   Password.
 * @param string $email      Email.
 * @param int    $phone      Phone Number.
 *
 * @return array
 */
function rc_wcpos_register_customer( string $password, string $email = null, int $phone = null ) {
	$role = 'customer';

	$user_attributes = array();

	if ( ! $password ) {
		return rc_wcpos_response_handler( 400, 'Password field is empty', null );
	}

	if ( null !== $email && null !== $phone ) {
		$condition  = "user_email = '$email' OR user_phone = $phone ";
		$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );

		if ( $is_existed ) {
			rc_wcpos_insert_auth_log( $is_existed, 'signup_failed', 'rc_auth: Existed User.' );
			return rc_wcpos_response_handler( 403, 'Email ID or Phone number already exist', null );
		}
		$user_attributes = array(
			array(
				'Name'  => 'email',
				'Value' => $email,
			),
			array(
				'Name'  => 'phone_number',
				'Value' => '+91' . $phone,
			),
		);
	} elseif ( null !== $email && null === $phone ) {
		$condition  = "user_email = '$email'";
		$is_existed = (int) rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
		
		if ( $is_existed ) {
			rc_wcpos_insert_auth_log( $is_existed, 'signup_failed', 'rc_auth: Email id already exist.' );
			return rc_wcpos_response_handler( 403, 'Email ID already exist', null );
		}
		$user_attributes = array(
			array(
				'Name'  => 'email',
				'Value' => $email,
			),
		);
	} else {
		$condition  = "user_phone = $phone ";
		$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
		
		if ( $is_existed ) {
			rc_wcpos_insert_auth_log( $is_existed, 'signup_failed', 'rc_auth: Phone number already exist.' );
			return rc_wcpos_response_handler( 403, 'Phone Number already exist', null );
		}
		$user_attributes = array(
			array(
				'Name'  => 'phone_number',
				'Value' => '+91' . $phone,
			),
		);
	}

	if ( email_exists( $email ) ) {
		$user_id    = email_exists( $email );
		$user_info  = get_userdata( $user_id );
		$user_login = $user_info->user_login;
	} else {
		$user_login = rc_wcpos_generate_username();
		$user_email = $email ? $email : $user_login . '@customers.legitfoods.store';

		$user_data = array(
			'user_login' => $user_login,
			'user_email' => $user_email,
			'user_pass'  => $password,
			'role'       => $role,
		);

		$user_id = wp_insert_user( $user_data );

		if ( $user_id->errors ) {
			rc_wcpos_insert_auth_log( $user_id, 'signup_failed', 'wp_user: ' . $user_id->errors );
			return rc_wcpos_response_handler( 400, 'Unable to register', null );
		}
	}

	$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	$rc_wcpos_aws_cognito->initialize();

	$signup_status = $rc_wcpos_aws_cognito->signup( $user_login, $password, $user_attributes );

	if ( ! true === $signup_status['is_success'] ) {
		rc_wcpos_insert_auth_log( $user_id, 'signup_failed', 'cognito_error: ' . $signup_status['error'] );
		return rc_wcpos_response_handler( 400, 'Unable to register', null );
	}

	$user_sub = $signup_status['userSub'];

	$signupdb_status = rc_wcpos_insert_user( $user_id, $user_login, $user_sub, $email, $phone );

	if ( ! $signupdb_status ) {
		rc_wcpos_insert_auth_log( $user_id, 'signup_failed', 'rc_auth: Unable to insert signup data to database' );
		return rc_wcpos_response_handler( 400, 'Unable to register', null );
	}

	rc_wcpos_insert_auth_log( $is_existed, 'signup_success', 'Signup Successful.' );
	return rc_wcpos_response_handler( 200, 'Registered Successfully', $user_login );
}

/**
 * Function signin customer.
 *
 * @param string $password   Password.
 * @param string $email      Email.
 * @param int    $phone      Phone Number.
 *
 * @return array
 */
function rc_wcpos_login_customer( string $password, string $email, int $phone ) {
	$is_existed = rc_wcpos_get_user_name( $email, $phone );

	if ( ! $is_existed ) {  
		return rc_wcpos_response_hanlder( 400, 'Account not found. Please Regitster', null );
	}

	$condition = "user_name = '$is_existed'";
	$user_id   = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );

	$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	$rc_wcpos_aws_cognito->initialize();

	$login_status = $rc_wcpos_aws_cognito->login( $is_existed, $password );

	if ( true === $login_status['is_success'] ) {
		$condition = "user_name = '$is_existed' ";

		$user_data = rc_wcpos_get_row( 'rc_auth', $condition );

		$return_data = array(
			'authToken' => $login_status['authToken'],
		);
		rc_wcpos_insert_auth_log( $user_id, 'login_success', 'Login Successful.' );
		return rc_wcpos_response_handler( 200, 'Login Successfully', $return_data );
	} else {
		rc_wcpos_insert_auth_log( $user_id, 'login_failed', 'cognito_error:' . $login_status['error'] );
		return rc_wcpos_response_handler( 400, $login_status['error'], null );
	}
}

/**
 * Function to generate otp for email address.
 *
 * @param string $email     Email Address.
 *
 * @return array
 */
function rc_wcpos_email_otp_handler( string $email ) {
	$is_existed = rc_wcpos_get_user_name( $email, null );
	
	if ( $is_existed ) {
		$condition = "user_name = '$is_existed'";
		$user_id   = rc_wcpos_get_var( 'rc_auth', 'user_id', $is_existed );

		$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
		$rc_wcpos_aws_cognito->initialize();

		$login_status = $rc_wcpos_aws_cognito->login( $is_existed, '' );
		if ( true === $login_status['is_success'] ) {
			rc_wcpos_insert_auth_log( $user_id, 'otp_generation_successful', 'Email OTP Generated.' );
			return rc_wcpos_response_handler( 200, 'OTP Sent Successfully', $login_status['session'] );
		} else {
			rc_wcpos_insert_auth_log( $user_id, 'otp_generation_failed', 'cognito_error: ' . $login_status['error'] );
			return rc_wcpos_response_handler( 400, 'Something went wrong. Please try again', null );
		}
	} else {
		$password = rc_wcpos_generate_password();

		$signup_response = rc_wcpos_register_customer( $password, $email, null );

		if ( 200 !== $signup_response->status ) {
			return $signup_response;
		}

		$response_data = $signup_response->data; 
		$username	   = $response_data['data'];
		$condition     = "user_name = '$username'";
		$user_id       = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );

		$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
		$rc_wcpos_aws_cognito->initialize();

		$login_status = $rc_wcpos_aws_cognito->login( $username, '' );

		if ( true === $login_status['is_success'] ) {
			rc_wcpos_insert_auth_log( $user_id, 'otp_generation_successful', 'Email OTP Generated' );
			return rc_wcpos_response_handler( 200, 'OTP Sent Successfully', $login_status['session'] );
		} else {
			rc_wcpos_insert_auth_log( $user_id, 'otp_generation_failed', 'cogntio_error:' . $login_status['error'] );
			return rc_wcpos_response_handler( 400, 'Something went wrong. Please try again', null );
		}
	}
}

/**
 * Function to generate otp for phone number.
 *
 * @param int $phone    Phone Number.
 *
 * @return array
 */
function rc_wcpos_sms_otp_handler( int $phone ) {
	return rc_wcpos_response_handler( 204, 'Comming Soon', null );

	/**
	 *  The following code will be uncommented after creation of new app client, which is dedicated to user.
	 */

	// $is_existed = rc_wcpos_get_user_name( null, $phone );

	// if ( $is_existed ) {
	// $rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	// $rc_wcpos_aws_cognito->initialize();

	// $login_status = $rc_wcpos_aws_cognito->login( $is_existed, '' );
	// if ( true === $login_status['is_success'] ) {
	// return rc_wcpos_response_handler( true );
	// } else {
	// return rc_wcpos_response_handler( false, 'Something went wrong. Please try again' );
	// }
	// } else {
	// $password = rc_wcpos_generate_password();

	// $signup_responseonse = rc_wcpos_register_customer( $password, null, $phone );

	// if ( true !== $signup_response ) {
	// return $signup_response;
	// }

	// $rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	// $rc_wcpos_aws_cognito->initialize();

	// $login_status = $rc_wcpos_aws_cognito->login( $is_existed, null );
	// if ( true === $login_status['is_success'] ) {
	// return rc_wcpos_response_handler( true );
	// } else {
	// return rc_wcpos_response_handler( false, 'Something went wrong. Please try again' );
	// }
	// }
}

/**
 * Function to validate otp.
 *
 * @param int    $otp       One time password.
 * @param string $email     Email Address.
 * @param int    $phone     Phone Number.
 * @param string $session   Session ID.
 *
 * @return array
 */
function rc_wcpos_auth_validate_otp( int $otp, string $email = null, int $phone = null, string $session ) {
	$username = rc_wcpos_get_user_name( $email, $phone );

	if ( ! $username ) {
		return rc_wcpos_response_handler( 403, 'Unable to get user details', null );
	}

	$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	$rc_wcpos_aws_cognito->initialize();

	$validation_result = $rc_wcpos_aws_cognito->verify_authentication_otp( $username, $otp, $session );
	
	$condition = "user_name = '$username' ";
	$user_data = rc_wcpos_get_row( 'rc_auth', $condition );

	if ( true === $validation_result['is_success'] ) {

		$return_data = array(
			'authToken' => $validation_result['authToken'],
		);
		rc_wcpos_insert_auth_log( $user_data['user_id'], 'otp_verification_successful', 'OTP verification successful' );
		return rc_wcpos_response_handler( 200, 'OTP Verified Successfully', $return_data );
	} else {
		rc_wcpos_insert_auth_log( $user_id, 'otp_verification_failed', 'cognito_error: ' . $validation_result['error'] );
		return rc_wcpos_response_handler( 400, $validation_result['error'], null );
	}
}

/**
 * Function To Get User Name.
 *
 * @param string $email     Email Address.
 * @param int    $phone     Phone Number.
 *
 * @return string $is_existed   Cognito User Name.
 */
function rc_wcpos_get_user_name( string $email = null, int $phone = null ) {

	$is_existed = false;

	if ( null !== $email && null !== $phone ) {
		$condition  = "user_email = '$email' OR user_phone = $phone ";
		$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_name', $condition );
	} elseif ( null !== $email && null === $phone ) {
		$condition  = "user_email = '$email'";
		$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_name', $condition );
	} else {
		$condition  = "user_phone = $phone ";
		$is_existed = rc_wcpos_get_var( 'rc_auth', 'user_name', $condition );
	}

	return $is_existed;
}

/**
 * Function to verify User Session.
 *
 * @param string $session   Session ID.
 *
 * @return int|bool
 */
function rc_wcpos_verify_session_id( string $session ) {

	$rc_wcpos_aws_cognito = RC_AWS_Cognito::get_instance();
	$rc_wcpos_aws_cognito->initialize();

	$user_result = $rc_wcpos_aws_cognito->get_user( $session );

	if ( true === $user_result['is_success'] ) {
		$user_name = $user_result['userName'];
		$condition = "user_name = '$user_name'";

		$user_id = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );

		return $user_id;
	} else {
		return false;
	}
}
