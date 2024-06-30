<?php
/**
 * File to return response and messages.
 *
 * @package RawConscious.
 */

/**
 * Function to return status and message for rest callback function.
 *
 * @param int     		$status    Status Code.
 * @param string  		$message   Message.
 * @param array|string	$data		 Data.		
 *
 * @return array
 */
function rc_wcpos_response_handler( int $status, string $message, $data = null  ) {
	$response_data = array(
		'status'  => $status,
		'message' => $message,
		'data'    => $data,
	);
	
	$response = new WP_REST_Response($response_data, $status);

	return $response;
}

/**
 * Check WordPress User Roles.
 * 
 * @param int     $user_id      User Id.
 * @param string  $user_role    User Role.
 */
function rc_wcpos_validate_user_role( int $user_id, string $user_role ) {
    $user_data = get_userdata($user_id);

    if ($user_data && in_array($user_role, $user_data->roles)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function which converts Number to English Words.
 *
 * @param float $number.
 *
 * @return string $words.
 */
function rc_wcpos_generate_amount_in_words( float $number ) {

	$no       = floor( $number );
	$point    = round( $number - $no, 2 ) * 100;
	$hundred  = null;
	$digits_1 = strlen( $no );
	$i        = 0;
	$str      = array();

	$words = array(
		'0'  => '',
		'1'  => 'one',
		'2'  => 'two',
		'3'  => 'three',
		'4'  => 'four',
		'5'  => 'five',
		'6'  => 'six',
		'7'  => 'seven',
		'8'  => 'eight',
		'9'  => 'nine',
		'10' => 'ten',
		'11' => 'eleven',
		'12' => 'twelve',
		'13' => 'thirteen',
		'14' => 'fourteen',
		'15' => 'fifteen',
		'16' => 'sixteen',
		'17' => 'seventeen',
		'18' => 'eighteen',
		'19' => 'nineteen',
		'20' => 'twenty',
		'30' => 'thirty',
		'40' => 'forty',
		'50' => 'fifty',
		'60' => 'sixty',
		'70' => 'seventy',
		'80' => 'eighty',
		'90' => 'ninety',
	);

	$digits = array( '', 'hundred', 'thousand', 'lakh', 'crore' );

	while ( $i < $digits_1 ) {
		$divider = ( $i == 2 ) ? 10 : 100;
		$number  = floor( $no % $divider );
		$no      = floor( $no / $divider );
		$i      += ( $divider == 10 ) ? 1 : 2;
		if ( $number ) {
			$plural  = ( ( $counter = count( $str ) ) && $number > 9 ) ? 's' : null;
			$hundred = ( $counter == 1 && $str[0] ) ? 'and ' : null;
			$str []  = ( $number < 21 ) ? $words[ $number ] .
				' ' . $digits[ $counter ] . $plural . ' ' . $hundred
				:
				$words[ floor( $number / 10 ) * 10 ]
				. ' ' . $words[ $number % 10 ] . ' '
				. $digits[ $counter ] . $plural . ' ' . $hundred;
		} else {
			$str[] = null;
		}
	}
	$str                   = array_reverse( $str );
	$result                = implode( '', $str );
	$points                = ( $point ) ?
	'.' . $words[ $point / 10 ] . ' ' .
			$words[ $point = $point % 10 ] : '';

	$words = $result . 'Rupees  ';
	$words = ! empty( $points ) ? $words . $points . ' Paise Only' : $words;

	return $words;
}
