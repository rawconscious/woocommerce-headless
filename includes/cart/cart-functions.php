<?php
/**
 * File to handle cart functions.
 *
 * @package RawConscious
 */

/**
 * Fucntion to verify cart data with current product data.
 *
 * @param array $cart_data    Cart Data.
 *
 * @return array
 */
function rc_wcpos_verify_product_availability( $cart_data ) {
	$error_handler  = array();
	$messages       = array();
	$is_proceedable = true;
	$is_upgradable  = false;
	$new_cartdata   = $cart_data;

	foreach ( $cart_data as $key => $item ) {
		$product_id    = $item['productId'];
		$variation_id  = ! empty( $item['variationId'] ) ? $item['variationId'] : null;
		$quantity      = $item['quantity'];
		$product_price = $item['productPrice'];

		$product = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );

		if ( ! $product || ! $product->exists() ) {
			$messages[]     = "Product not found for {$item['productName']}";
			$is_proceedable = false;
			continue;
		}

		$available_quantity = $product->get_stock_quantity();
		$current_price      = $product->get_price();

		if ( 0 === $available_quantity ) {
				$messages[]     = "The {$item['productName']} is currently out of stock. Please remove it from the cart.";
				$is_proceedable = false;
		} elseif ( $available_quantity < $quantity ) {
			$messages[]     = "Only $available_quantity left in stock for {$item['productName']}. Please reduce quantity to $available_quantity.";
			$is_proceedable = false;
		}

		if ( $current_price !== $product_price ) {
			$messages[]     = "{$item['productName']} price has changed from $product_price to $current_price.";
			$is_upgradable  = true;
			$is_proceedable = false;

			$new_cartdata[ $key ]['productPrice'] = $current_price;
		}
	}

	$response_data = array(
		'messages'   => $messages,
		'upgradable' => $is_upgradable,
		'cartData'   => $new_cartdata,
	);

	if ( $is_proceedable ) {
		return rc_wcpos_response_handler( 201, 'Proceedable', null );
	} else {
		return rc_wcpos_response_handler( 200, 'Cart Data Required Update', $response_data );
	}
}
