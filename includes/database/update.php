<?php
/**
 * Updates Database tables.
 *
 * @package RawConscious
 */

/**
 * Function to update customer verification.
 *
 * @param string $customer_id   Customer Id.
 *
 * @return bool
 */
function rc_wcpos_update_customer_verification( string $customer_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'rc_wcpos_customer';

	$update_data = array(
		'is_verified' => true,
	);

	$where = array(
		'customer_id' => $customer_id,
	);

	$update_result = $wpdb->update( $table_name, $update_data, $where );

	if ( $update_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Update Order Status in Order Table.
 *
 * @param string $order_id         order_id.
 * @param string $payment_status   payment_status.
 *
 * @return bool
 */
function rc_wcpos_update_order( string $order_id, string $payment_status ) {
	global $wpdb;

	$table_orders = $wpdb->prefix . 'rc_wcpos_orders';

	if ( 'PAID' === $payment_status ) {
		$order_status = 'Completed';
	} elseif ( 'FAILED' === $payment_status ) {
		$order_status = 'Failed';
	} else {
		$order_status = 'processing';
	}

	$update_data = array(
		'order_status' => $order_status,
	);

	$where = array(
		'order_id' => $order_id,
	);

	$update_result = $wpdb->update( $table_orders, $update_data, $where );

	if ( $update_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Update Payment Status in Payment Table.
 *
 * @param string $payment_id         order_id.
 * @param string $payment_status     payment_status.
 *
 * @return bool
 */
function rc_wcpos_update_payment( string $payment_id, string $payment_status ) {
	global $wpdb;

	$table_payment = $wpdb->prefix . 'rc_wcpos_payment';

	$update_data = array(
		'payment_status' => $payment_status,
	);

	$where = array(
		'payment_id' => $payment_id,
	);

	$update_result = $wpdb->update( $table_payment, $update_data, $where );

	if ( $update_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Updates new order total if discount code applied.
 *
 * @param string $order_id      Order ID.
 * @param array  $product_data  Product Data.
 * @param int    $order_valie   Order Value.
 *
 * @return bool
 */
function rc_wcpos_update_discount( string $order_id, array $product_data, int $order_value ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'rc_wcpos_orders';

	$update_data = array(
		'order_items' => maybe_serialize( $product_data ),
		'order_value' => $order_value,
	);

	$where = array(
		'order_id' => $order_id,
	);

	$update_result = $wpdb->update( $table_name, $update_data, $where );

	if ( $update_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function to update cart.
 *
 * @param string $cart_data     Cart Details.
 * @param string $cart_id       Cart Id.
 *
 * @return boolean
 */
function rc_wcpos_update_cart( string $cart_data, string $cart_id ) {
	global $wpdb;

	$table_cart = $wpdb->prefix . 'rc_active_cart';

	$update_data = array(
		'cart_id'   => $cart_id,
		'cart_data' => $cart_data,
	);

	$where = array(
		'cart_id' => $cart_id,
	);

	$update_result = $wpdb->update( $table_cart, $update_data, $where );

	if ( $update_result ) {
		return true;
	} else {
		return true;
	}
}

/**
 * Function to update User Meta.
 *
 * @param array $update_data    Update Data.
 * @param array $condition      Condition.
 *
 * @return boolean
 */
function rc_wcpos_update_user_meta( array $update_data, array $condition ) {
	global $wpdb;

	$table_usermeta = $wpdb->prefix . 'rc_user_meta';

	$update_result = $wpdb->update( $table_usermeta, $update_data, $condition );

	if ( $update_result ) {
		return true;
	} else {
		return true;
	}
}
