<?php
/**
 * File to handle orders.
 *
 * @package RawConscious.
 */

/**
 * Function handle discounts.
 *
 * @param string $order_id 			Order Id.
 * @param bool   $registered_user	Register user or not.
 *
 * @return array $order_details Returns new order details.
 */
function rc_wcpos_handle_discount( string $order_id, bool $registered_user ) {

	$condition = "order_id = '" . $order_id . "'";

	$order_details = rc_wcpos_get_row( 'rc_wcpos_orders', $condition );
	$product_data  = $order_details['order_items'];
	$order_total   = $order_details['order_value'] / 100;
	$discount_json = file_get_contents( RC_WOO_PATH . '/includes/data/discount-logic.json' );
	$discount_data = json_decode( $discount_json, true );

	if ( empty( $discount_data ) ) {
		return $return_data['order_total'] = $order_total;
	}
	return rc_wcpos_calculate_discount( $order_id, $product_data, $registered_user, $discount_data );

}

/**
 * Function to calculate order total.
 *
 * @param array $product_data Product Data.
 *
 * @return int $order_total.
 */
function rc_wcpos_calculate_order_total( array $product_data ) {
	$order_total = 0;
	foreach ( $product_data as $data ) {
		$order_total += $data['productPrice'] * $data['quantity'];
	}

	return $order_total;
}

/**
 * Function to calculate total quantity.
 *
 * @param array $product_data Product Data.
 *
 * @return int $total_quantity.
 */
function rc_wcpos_calculate_order_qunatity( $product_data ) {
	$total_quantity = 0;
	foreach ( $product_data as $data ) {
		$total_quantity += $data['quantity'];
	}

	return $total_quantity;
}

/**
 * Function to calculate discount.
 *
 * @param string $order_id          Order Id.
 * @param string $product_data      Product Details.
 * @param bool   $registered_user   Registerd User.
 * @param array  $discount_data     Discount Data.
 *
 * @return int $order_total Order Total.
 */
function rc_wcpos_calculate_discount( string $order_id, $product_data, bool $registered_user, array $discount_data ) {
	$cart_items                = maybe_unserialize( $product_data );
	$total_free_discount  	   = 0;
	$total_discount       	   = 0;
	$free_product_id 	  	   = null;
	$order_subtotal  	   	   = rc_wcpos_calculate_order_total( $cart_items );
	$total_discount_percentage = null;
	
	if ( !empty( $discount_data['type_x+y'] ) ) {
        foreach ( $discount_data['type_x+y'] as $discount ) {
            if ( ! isset( $discount['quantity_required'], $discount['free_quantity'], $discount['condition'] ) ) {
                continue;
            }

            $condition = $discount['condition'];
			if ( ( true === $condition['registered_user'] ) && ( false !== $registered_user ) ) {
                continue;
            }

            $total_quantity = array_sum( array_column( $cart_items, 'quantity' ) );

            if ($total_quantity >= $discount['quantity_required']) {
                $lowest_price_item    = min( array_column( $cart_items, 'productPrice' ) );
				$free_quantity        = floor($total_quantity / $discount['quantity_required']) * $discount['free_quantity'];
                $total_free_discount += $lowest_price_item * $free_quantity;
            }
        }
		$order_total = round( $order_subtotal - $total_free_discount );
    }

	$total_discount = 0;

	if ( !empty( $discount_data['type_percentage'] ) ) {
		foreach ( $discount_data['type_percentage'] as $discount ) {
			$condition = $discount['condition'];
			
			if ( ( true === $condition['registered_user'] ) && ( false === $registered_user ) ) {
				continue;
            }
			$total_discount_percentage = $discount_data['type_percentage'][0]['percentage'];

			$percentage = $discount['percentage'];
			$total_discount += round( ($percentage / 100) * $order_total );
		}
		$order_total = round ( $order_total - $total_discount );
	}
	$db_total = $order_total * 100;

	$update_status = rc_wcpos_update_discount( $order_id, $cart_items, $db_total );

	$order_details = array(
		'free_item_count'     => $free_quantity,
		'free_product_price'  => $total_free_discount,
		'discount_percentage' => $total_discount_percentage,
		'discount_amount'     => $total_discount,
		'order_subtotal'      => $order_subtotal,
		'order_total'         => $order_total,
	);

	return $order_details;

}
