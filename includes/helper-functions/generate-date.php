<?php
/**
 * File to generate Date String.
 *
 * @package RawConscious.
 */
/**
 * Generates Date String based on Filter Status.
 *
 * @param string $date Filter Date.
 *
 * @return string $date_created Date String.
 */
function rc_wcpos_create_date_string( string $date = '' ) {
	if ( 'today' === $date ) {
		$current_date = current_time( 'mysql' );
		$date_created = date( 'Y-m-d', strtotime( $current_date ) );

	} elseif ( 'last-3-days' === $date ) {
		$current_date = current_time( 'mysql' );
		$date_created = date( 'Y-m-d', strtotime( '-3 days', strtotime( $current_date ) ) );

	} elseif ( 'this-week' === $date ) {
		$current_date = current_time( 'mysql' );
		$date_created = date( 'Y-m-d', strtotime( '-7 days', strtotime( $current_date ) ) );

	} else {
		$date_created = '';
	}

	return $date_created;
}
