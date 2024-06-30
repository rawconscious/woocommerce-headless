<?php
/**
 * This File is used to retrieve data From database.
 *
 * @package RawConscioos.
 */

/**
 * Function To Retrive Customer Data.
 *
 * @param string $date_created Date which data created.
 * @param int    $limit        Number of data to retrieved.
 *
 * @return array
 */
function rc_wcpos_retrieve_customer_data( string $date_created, int $limit = 0 ) {
	global $wpdb;

	$table_customer = $wpdb->prefix . 'rc_wcpos_customer';

	if ( '' === $date_created && 0 === $limit ) {
		$query = $wpdb->prepare(
			"SELECT * FROM $table_customer"
		);

	} elseif ( '' === $date_created && 0 !== $limit ) {
		$query = $wpdb->prepare(
			"SELECT * FROM $table_customer ORDER BY created_at DESC LIMIT %d",
			$limit
		);

	} elseif ( '' !== $date_created && 0 === $limit ) {
		$query = $wpdb->prepare(
			"SELECT * FROM $table_customer WHERE created_at >= %s ORDER BY created_at DESC",
			$date_created
		);
	} else {
		$query = $wpdb->prepare(
			"SELECT * FROM $table_customer WHERE created_at >= %s ORDER BY created_at DESC LIMIT %d",
			$date_created,
			$limit
		);
	}

	$results = $wpdb->get_results( $query, ARRAY_A ); //phpcs:ignore

	return $results;
}

/** Function to get total count of Customer Table.
 *
 * @return int $count Customer Count.
 */
function rc_wcpos_get_customer_count() {
	global $wpdb;

	$table_customer = $wpdb->prefix . 'rc_wcpos_customer';

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_customer" );

	return $count;
}

/**
 * Function To Retrive Order Data.
 *
 * @param array $order_data     Order Details.
 *
 * @return array
 */
function rc_wcpos_retrieve_order_data( array $order_data ) {
	global $wpdb;

	$table_order = $wpdb->prefix . 'rc_wcpos_orders';

	$user_id      = ! empty( $order_data['userId'] ) ? $order_data['userId'] : null;
	$order_status = ! empty( $order_data['orderStatus'] ) ? $order_data['orderStatus'] : 'all';
	$order_limit  = ! empty( $order_data['limit'] ) ? $order_data['limit'] : null;

	$is_manager = rc_wcpos_validate_user_role( $user_id, 'pos_manager' );

	$query = "SELECT * FROM $table_order WHERE 1=1";

	if ( $is_manager ) {
		$query .= $wpdb->prepare( ' AND pos_manager_id = %s', $user_id );
	}

	if ( 'all' !== $order_status ) {
		$query .= $wpdb->prepare( ' AND order_status = %s', $order_status );
	}

	if ( ! empty( $date_created ) ) {
		$query .= $wpdb->prepare( ' AND created_at >= %s', $date_created );
	}

	if ( ! empty( $limit ) && is_numeric( $limit ) ) {
		$query .= ' LIMIT ' . intval( $limit );
	}

	$results = $wpdb->get_results( $query, ARRAY_A ); //phpcs:ignore

	if ( $results ) {
		$order_total = $results['order_value'] ? $resutls['order_value'] / 2 : null;

		$return_data = array(
			'orderId'     => $results['order_id'],
			'orderTotal'  => $order_total,
			'orderStatus' => $results['order_status'],
			'createdAt'   => $results['created_at'],
		);
		return rc_wcpos_response_handler( 200, 'Order Fetched', $return_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Order Found', null );
	}
}


/** Function to get total count of Customer Table.
 *
 * @return int $count Customer Count.
 */
function rc_wcpos_get_order_count() {
	global $wpdb;

	$table_order = $wpdb->prefix . 'rc_wcpos_orders';

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_order" );

	return $count;
}

/**
 * Function to check value exits in db table .
 *
 * @param string $table_name    Table name without WordPress prefix.
 * @param string $column_name   Column Name to return.
 * @param string $conditions    Conditions to check whether exists or not.
 *
 * @return bool|string
 */
function rc_wcpos_get_var( $table_name, $column_name, $conditions ) {
	global $wpdb;

	$table_name = $wpdb->prefix . $table_name;

	$is_existed = $wpdb->get_var( "SELECT $column_name FROM $table_name WHERE $conditions" );

	if ( $is_existed ) {
		return $is_existed;
	} else {
		return false;
	}
}

/**
 * Function to retrieve single row.
 *
 * @param string $table_name    Table Name Without Prefix.
 * @param string $conditions    Condition to retrieve row.
 *
 * @return array $resutls  Database retrieval results.
 */
function rc_wcpos_get_row( string $table_name, string $conditions ) {
	global $wpdb;

	$table_name = $wpdb->prefix . $table_name;

	$results = $wpdb->get_row( "SELECT * FROM $table_name WHERE $conditions", ARRAY_A );

	return $results;
}

/**
 * Function to retrieve all results.
 *
 * @param string $table_name    Table Name Without Prefix.
 * @param string $conditions    Condition to retrieve row.
 *
 * @return array $resutls  Database retrieval results.
 */
function rc_wcpos_get_results( string $table_name, string $conditions ) {
	global $wpdb;

	$table_name = $wpdb->prefix . $table_name;

	$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE $conditions", ARRAY_A );

	return $results;
}
