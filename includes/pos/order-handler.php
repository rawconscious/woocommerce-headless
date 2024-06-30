<?php
/**
 * File to handle orders.
 *
 * @package RawConscious.
 */

/**
 * Function to create pos order.
 *
 * @param int   $customer_id        Customer Id.
 * @param array $order_details      Order Details.
 *
 * @return array
 */
function rc_wcpos_create_pos_order( int $customer_id, array $order_details ) {

	$product_data  = ! empty( $order_details['productData'] ) ? $order_details['productData'] : null;
	$customer_data = ! empty( $order_details['customerData'] ) ? $order_details['customerData'] : null;
	$checkout_data = ! empty( $order_details['checkoutData'] ) ? $order_details['checkoutData'] : null;

	if ( null === $product_data ) {
		return rc_wcpos_response_handler( 400, 'Product Data is empty', null );
	}

	$member_id         = $customer_data['memberId'] ? (int) $customer_data['memberId'] : null;
	$is_valid_customer = false;
	$is_assisted	   = true; 

	if ( $customer_id ) {
		$condition         = "user_id = $customer_id";
		$is_valid_customer = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
		if ( ! $is_valid_customer ) {
			return rc_wcpos_response_handler( 403, 'Not a valid customer', null );
		}
		$member_role = rc_wcpos_validate_user_role( $customer_id, 'pos_manager' );
		$is_admin    = rc_wcpos_validate_user_role( $customer_id, 'administrator' );
		$is_assisted = ( $member_role || $is_admin ) ? $is_assisted : false;
	}

	if ( $member_id ) {
		$condition       = "user_id = $member_id";
		$is_valid_member = true;
		$member_data     = rc_wcpos_get_var( 'rc_auth', 'user_id', $condition );
		$member_role     = rc_wcpos_validate_user_role( $member_id, 'pos_manager' );
		$is_admin        = rc_wcpos_validate_user_role( $member_id, 'administrator' );
		$is_valid_member = $member_data ? $is_valid_member : false;
		$is_valid_member = ( $member_role || $is_admin ) ? $is_valid_member : false;

		if ( ! $is_valid_member ) {
			return rc_wcpos_response_handler( 403, 'POS manager is not valid', null );
		}
	}

	$order_id = rc_wcpos_insert_order( $customer_id, $product_data, $customer_data );

	if ( ! $order_id ) {
		return rc_wcpos_response_handler( 400, 'Order Not Created Successfully' );
	}

	$registered_user = ( $is_valid_customer && ! $is_assisted ) ? true : false; 
	$discounted_data = rc_wcpos_handle_discount( $order_id, $registered_user );
	
	$return_data = array(
		'orderId'            => $order_id,
		'freeProductCount'   => ! empty( $discounted_data['free_item_count'] ) ? $discounted_data['free_item_count'] : null,
		'freeProductPrice'   => ! empty( $discounted_data['free_product_price'] ) ? $discounted_data['free_product_price'] : null,
		'discountPercentage' => ! empty( $discounted_data['discount_percentage'] ) ? $discounted_data['discount_percentage'] : null,
		'discountAmount'     => ! empty( $discounted_data['discount_amount'] ) ? $discounted_data['discount_amount'] : null,
		'orderSubtotal'      => ! empty( $discounted_data['order_subtotal'] ) ? $discounted_data['order_subtotal'] : null,
		'orderTotal'         => ! empty( $discounted_data['order_total'] ) ? $discounted_data['order_total'] : null,
	);

	return rc_wcpos_response_handler( 200, 'Order Created Successfully', $return_data );
}

/**
 * Function get pos order list.
 *
 * @param int   $manager_id     Manager ID.
 * @param array $order_data     Order Details.
 *
 * @return array
 */
function rc_wcpos_get_pos_order_list( int $manager_id, array $order_data ) {
	$manager_id;
	$order_data;
	return rc_wcpos_response_handler( 503, 'Service Unavailable', null );
}
