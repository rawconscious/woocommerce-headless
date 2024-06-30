<?php
/**
 * Database file Woocommerce Deletion.
 *
 * @package rawconsciois.
 */

/**
 * Function to delete cart data from table rc_active_cart
 *
 * @param string $cart_id  Cart ID.
 *
 * @return boolean
 */
function rc_wcpos_remove_cart( string $cart_id ) {
	global $wpdb;

	$table_cart = $wpdb->prefix . 'rc_active_cart';

	$where = array(
		'cart_id' => $cart_id,
	);

	$delete_result = $wpdb->delete( $table_cart, $where );

	if ( $delete_result ) {
		return true;
	} else {
		return false;
	}
}
