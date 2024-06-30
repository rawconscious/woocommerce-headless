<?php
/**
 * Files to inserts data into database.
 *
 * @package RawConscious
 */

/**
 * Function to add customer data to customer table.
 *
 * @param array  $customer_data    customer details.
 * @param string $customer_id      customer id.
 *
 * @return boolean
 */
function rc_wcpos_insert_customer( array $customer_data, string &$customer_id ) {
	global $wpdb;
	$table_customer = $wpdb->prefix . 'rc_wcpos_customer';

	$customer_phone = (int) $customer_data['phone'];

	$condition = "customer_phone = $customer_phone";

	$is_existed = rc_wcpos_get_var( 'rc_wcpos_customer', 'customer_id', $condition );

	if ( $is_existed ) {
		$customer_id = $is_existed;
		return true;
	}

	$insert_data = array(
		'customer_id'    => $customer_id,
		'customer_phone' => $customer_phone,
	);

	$insert_result = $wpdb->insert( $table_customer, $insert_data );

	if ( $insert_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function to insert cart data into active cart.
 *
 * @param array  $cart_details    Cart Details.
 * @param string $cart_id         Cart Id.
 *
 * @return boolean
 */
function rc_wcpos_insert_cart( array $cart_details, string $cart_id ) {
	global $wpdb;

	$table_cart = $wpdb->prefix . 'rc_active_cart';

	$user_id   = ! empty( $cart_details['userId'] ) ? (int) $cart_details['userId'] : null;
	$is_pos    = ! empty( $cart_details['isPos'] ) ? 'is_pos: true' : 'is_pos: false';
	$cart_data = $cart_details['cartData'];

	$insert_data = array(
		'cart_id'   => $cart_id,
		'user_id'   => $user_id,
		'cart_data' => wp_json_encode( $cart_data, true ),
		'cart_meta' => $is_pos,
	);

	$insert_result = $wpdb->insert( $table_cart, $insert_data );

	if ( $insert_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function to add order data to order table.
 *
 * @param int 	$customer_id	  customer id.
 * @param array $product_data     products details.
 * @param array $customer_data    customer Details.
 *
 * @return boolean
 */
function rc_wcpos_insert_order( int $customer_id, array $product_data, array $customer_data = null ) {
	global $wpdb;

	$table_orders = $wpdb->prefix . 'rc_wcpos_orders';

	$pos_manager_id = ! empty( $customer_data['memberId'] ) ? (int) $customer_data['memberId'] : null;

	$order_id     = rc_wcpos_generate_order_id();
	$order_total  = rc_wcpos_calculate_order_total( $product_data );
	$order_items  = maybe_serialize( $product_data );
	$order_status = 'Processing';

	$order_total = round( $order_total * 0.70 ); //This has to be changed when discount logic has changed.
	
	$insert_data = array(
		'order_id'       => $order_id,
		'order_value'    => $order_total * 100,
		'order_items'    => $order_items,
		'order_status'   => $order_status,
		'pos_manager_id' => $pos_manager_id,
		'customer_id'    => $customer_id,
	);

	$insert_result = $wpdb->insert( $table_orders, $insert_data );

	if ( $insert_result ) {
		return $order_id;
	} else {
		return false;
	}
}

/**
 * Function to add payment data to payment table.
 *
 * @param string $payment_id        payment id.
 * @param string $order_id          order id.
 * @param string $payment_status    payment status.
 *
 * @return string|bool
 */
function rc_wcpos_insert_payment( string $payment_id, string $order_id, string $payment_status ) {
	global $wpdb;

	$table_orders = $wpdb->prefix . 'rc_wcpos_payment';

	$insert_data = array(
		'payment_id'     => $payment_id,
		'payment_status' => $payment_status,
		'order_id'       => $order_id,
	);

	$insert_result = $wpdb->insert( $table_orders, $insert_data );

	if ( $insert_result ) {
		return $payment_id;
	} else {
		return false;
	}
}

/**
 * Function to insert user.
 *
 * @param int    $user_id       User Id.
 * @param string $user_name     User Name.
 * @param string $user_sub      Usersub.
 * @param string $user_email    User email.
 * @param int    $user_phone    Phone Number.
 *
 * @return boolean
 */
function rc_wcpos_insert_user( int $user_id, string $user_name, string $user_sub, string $user_email = null, int $user_phone = null ) {
	global $wpdb;

	$table_auth = $wpdb->prefix . 'rc_auth';

	$insert_data = array(
		'user_id'    => $user_id,
		'user_name'  => $user_name,
		'user_sub'   => $user_sub,
		'user_email' => $user_email,
		'user_phone' => $user_phone,
	);

	$insert_result = $wpdb->insert( $table_auth, $insert_data );

	if ( $insert_result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function to insert user.
 *
 * @param int    $user_id       User Id.
 * @param string $meta_key      Meta Key.
 * @param string $meta_value    Meta Value.
 *
 * @return boolean
 */
function rc_wcpos_insert_user_meta( int $user_id, string $meta_key, string $meta_value ) {
	global $wpdb;

	$table_user_meta = $wpdb->prefix . 'rc_user_meta';

	$insert_data = array(
		'user_id'    => $user_id,
		'meta_key'   => $meta_key,
		'meta_value' => $meta_value,
	);

	$insert_result = $wpdb->insert( $table_user_meta, $insert_data );

	if ( $insert_result ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Function to insert authentication log to auth log tabel.
 * 
 * @param int 		$user_id		User ID.
 * @param string	$event_type		Event Type.
 * @param string	$event_log		Event Log.
 * 
 * @return bool
 */
function rc_wcpos_insert_auth_log( int $user_id, string $event_type, string $event_log ) {
	global $wpdb;

	$table_auth_log = $wpdb -> prefix . "rc_auth_log";

	$insert_data = array(
		'user_id'	 => $user_id,
		'event_type' => $event_type,
		'event_log'  => $event_log,
	);

	$insert_result = $wpdb->insert( $table_auth_log, $insert_data );

	if ( $insert_result ) {
		return true;
	} else {
		return false;
	}
}
