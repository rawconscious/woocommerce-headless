<?php
/**
 * Files to handle E Commerce Handler.
 *
 * @package RawConscious.
 */

/**
 * Function to create WooCommerce Order.
 *
 * @param int   $customer_id    User ID.
 * @param array $order_details  Order Details.
 *
 * @return array
 */
function rc_wcpos_create_woocommerce_order( int $customer_id, array $order_details ) {
	$products      = $order_details['productData'];
	$customer_data = $order_details['customerData'];
	$checkout_data = $order_details['checkoutData'];

	$user = get_user_by( 'ID', $customer_id );

	$billing_address = array(
		'first_name' => $user->billing_first_name,
		'last_name'  => $user->billing_last_name,
		'email'      => $user->billing_email,
		'phone'      => $user->billing_phone,
		'address_1'  => $user->billing_address_1,
		'address_2'  => $user->billing_address_2,
		'city'       => $user->billing_city,
		'state'      => $user->billing_state,
		'postcode'   => $user->billing_postcode,
		'country'    => $user->billing_country,
	);

	$shipping_address = array(
		'first_name' => $user->shipping_first_name,
		'last_name'  => $user->shipping_last_name,
		'address_1'  => $user->shipping_address_1,
		'address_2'  => $user->shipping_address_2,
		'city'       => $user->shipping_city,
		'state'      => $user->shipping_state,
		'postcode'   => $user->shipping_postcode,
		'country'    => $user->shipping_country,
		'phone'      => $user->shipping_phone,
	);

	$calculate_taxes_for = array(
		'country'  => ! empty( $shipping_address['country'] ) ? $shipping_address['country'] : $billing_address['country'],
		'state'    => ! empty( $shipping_address['state'] ) ? $shipping_address['state'] : $billing_address['state'],
		'postcode' => ! empty( $shipping_address['postcode'] ) ? $shipping_address['postcode'] : $billing_address['postcode'],
		'city'     => ! empty( $shipping_address['city'] ) ? $shipping_address['city'] : $billing_address['city'],
	);

	$coupon_applied = ! empty( $checkout_data['couponCode'] ) ? $checkout_data['couponCode'] : null;

	$order = wc_create_order();
	$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
	$order->set_address( $billing_address, 'billing' );
	$order->set_address( $shipping_address, 'shipping' );

	foreach ( $products as $product ) {
		$product_id   = (int) $product['productId'];
		$quantity     = (int) $product['quantity'];
		$variation_id = ! empty( $product['variationId'] ) ? $product['variationId'] : null;

		if ( $variation_id ) {
			$item_id = $order->add_product( wc_get_product( $variation_id ), $quantity );
		} else {
			$item_id = $order->add_product( wc_get_product( $product_id ), $quantity );
		}

		$line_item = $order->get_item( $item_id, false );
		$line_item->calculate_taxes( $calculate_taxes_for );
		$line_item->save();
	}

	
	$order->add_item( $shipping );
	$order->set_customer_id( $customer_id );
	if ( $coupon_applied ) {
		$order->apply_coupon( $coupon_applied );
	}
	$order->set_status( 'wc-pending' );
	$order_total = $order->calculate_totals();

	if ( $order_total > 499 ) {
		$free_shipping = new WC_Shipping_Rate( 'free_shipping', 'Free Shipping', 0, array(), 'free_shipping' );
		$order->add_shipping( $free_shipping );
	} else {
		$flat_rate = new WC_Shipping_Rate( 'flat_rate', 'Flat Rate Shipping', 50.00, array(), 'flat_rate' );
		$order->add_shipping( $flat_rate );
	}

	$order_total = $order->calculate_totals();
	$order_id    = $order->get_id();
	$order->save();

	$return_data = array(
		'orderId'    => $order_id,
		'orderTotal' => round( $order_total ),
	);

	if ( $order_total ) {
		return rc_wcpos_response_handler( 200, 'Order Created', $return_data );
	} else {
		rc_wcpos_response_handler( 500, 'Unable to create order', null );
	}
}

/**
 * Function to get WooCommerce Order List.
 *
 * @param int   $user_id        User Id.
 * @param array $order_data     Order Data.
 *
 * @return array
 */
function rc_wcpos_get_ecommerce_order_list( int $user_id, array $order_data ) {

	$status 	= $order_data['orderStatus'] ? 'wc-' . $order_data['orderStatus'] : array_keys( wc_get_order_statuses() );
	$limit  	= $order_data['limit'] ? $order_data['limit'] : -1;
	$search 	= $order_data['search'] ? $order_data['search'] : '';
	$exclude 	= $order_data['exclude'] ? $order_data['exclude'] : array();
	$include 	= $order_data['include'] ? $order_data['include'] : array();
	$order      = $order_data['order'] ? $order_data['order'] : 'date';
	$orderby    = $order_data['orderby'] ? $order_data['orderby'] : 'DESC';
	$product_id = $order_data['productId'] ? $order_data['productId'] : null;

	$args = array(
		'meta_key'    => '_customer_user',
		'meta_value'  => $user_id,
		'post_type'   => 'shop_order',
		'post_status' => $status,
		'orderby'     => $order,
		'order'       => $orderby,
		'limit'		  => $limit,
		'search'	  => $search,
		'exclude'     => $exclude,
		'include'     => $include,
	);

	if ( ! empty( $product_id ) ) {
        $query_args['meta_query'] = array(
            array(
                'key'     => '_product_id',
                'value'   => $product_id,
                'compare' => 'LIKE'
            )
        );
    }

	$orders = wc_get_orders( $args );

	$result = array();

	foreach ( $orders as $key => $order ) {
		$order_id     = $order->get_id();
		
		$order_date   = gmdate( 'F d, Y', strtotime( $order->get_date_created()->format( 'Y-m-d H:i:s' ) ) );
		$order_status = ucfirst( $order->get_status() );
		$order_total  = $order->get_total();

		$items      = $order->get_items();
		$first_item = reset( $items );
		$product_id = $first_item->get_product_id();
		$product    = wc_get_product( $product_id );
		
		$product_image = '';
		
		if ( $product ) {
			$attachment_ids   = $product->get_gallery_image_ids();
			$attachment_ids[] = $product->get_image_id();
			$attachment_count = count( $attachment_ids ) - 1;
			$attachment_id    = $attachment_ids[ $attachment_count ];
			
			if ( $attachment_id ) {
				$product_image = wp_get_attachment_url( $attachment_id );
			}
		}

		$result[ $key ] = array(
			'orderId'      => $order_id,
			'orderDate'    => $order_date,
			'orderStatus'  => $order_status,
			'orderTotal'   => round( $order_total, 0 ),
			'productImage' => $product_image,
		);
	}

	$data = array();
	$data = $result;
	
	if ( 0 < count( $include ) ) {
		$data = array();
		foreach( $result as $item ) {
			if ( in_array( $item['orderId'], $include ) ) {
				$data[] = $item;
			}
		}
	}
	
	if ( 0 < count( $data ) ) {
		return rc_wcpos_response_handler( 200, 'Order List Fetched Successfully', $data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Orders Found', null );
	}
}

/**
 * Function to get Single WooCommerce Order.
 *
 * @param int $user_id      User Id.
 * @param int $order_id     Order Id.
 *
 * @return array
 */
function rc_wcpos_get_ecommerce_order( int $user_id, int $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return rc_wcpos_response_handler( 400, 'Unable to get order details', null );
	}

	if ( $user_id !== $order->get_customer_id() ) {
		return rc_wcpos_response_handler( 403, 'Order does not belong to the specified user', null );
	}

	$order_date     = $order->get_date_created()->format( 'Y-m-d H:i:s' );
	$invoice_date   = $order->get_date_modified()->format( 'Y-m-d H:i:s' );
	$order_status   = ucfirst( $order->get_status() );
	$transaction_id = $order->transaction_id;

	$billing_address = array(
		'firstName' => $order->billing_first_name,
		'lastName'  => $order->billing_last_name,
		'company'   => $order->billing_company,
		'address1'  => $order->billing_address_1,
		'address2'  => $order->billing_address_2,
		'city'      => $order->billing_city,
		'state'     => $order->billing_state,
		'postcode'  => $order->billing_postcode,
		'country'   => $order->billing_country,
		'email'     => $order->billing_email,
		'phone'     => $order->billing_phone,
	);

	$shipping_address = array(
		'firstName' => $order->shipping_first_name,
		'lastName'  => $order->shipping_last_name,
		'company'   => $order->shipping_company,
		'address1'  => $order->shipping_address_1,
		'address2'  => $order->shipping_address_2,
		'city'      => $order->shipping_city,
		'state'     => $order->shipping_state,
		'postcode'  => $order->shipping_postcode,
		'country'   => $order->shipping_country,
		'email'     => $order->shipping_email,
		'phone'     => $order->shipping_phone,
	);

	$order_details = array();
	$order_totals  = array();
	$item_details  = array();

	$items = ! empty( $order->get_items() ) ? $order->get_items() : null;

	if ( ! $items ) {
		return rc_wcpos_response_handler( 400, 'Unable to get Order Items', null );
	}

	$shipping_total = $order->get_shipping_total();
	$shipping_tax   = $order->get_shipping_tax();
	$item_count     = 1;
	$total_quantity = 0;
	$total_subtotal = 0;
	$total_discount = $order->get_total_discount() + $order->get_discount_tax();
	$total_tax      = 0;
	$total_mrp      = 0;
	$total_amount   = $shipping_total;

	foreach ( $items as $item_id => $item ) {
		$product       = $item->get_product();
		$product_id    = $product->get_id();
		$product_name  = $product->get_name();
		$product_price = $product->get_price();
		$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );
		$product_sku   = $product->get_sku();
		$item_subtotal = $item->get_subtotal();
		$item_tax      = $item->get_total_tax();
		$quantity      = $item->get_quantity();
		$item_total    = $item->get_total() + $item_tax;
		$discount      = abs( ( $product_price * $quantity ) - $item_total );
		$sku           = isset( $product->sku ) ? $product->sku : '-';

		$total_quantity += $quantity;
		$total_subtotal += $item_subtotal;
		$total_tax      += $item_tax;
		$total_mrp      += $product_price;
		$total_amount   += $item_total;

		$item_details[] = array(
			'productId'    => $product_id,
			'productImage' => $product_image[0],
			'productName'  => $product_name,
			'sku'          => $sku,
			'mrp'          => round( $product_price, 2 ),
			'quantity'     => $quantity,
			'subtotal'     => round( $item_subtotal, 2 ),
			'discount'     => round( $discount, 2 ),
			'tax'          => round( $item_tax, 2 ),
			'total'        => round( $item_total, 2 ),
		);
	}
	++$total_quantity;
	$shipping_subtotal = $shipping_total - $shipping_tax;
	$amount_in_words   = rc_wcpos_generate_amount_in_words( $total_amount );

	$order_details = array(
		'orderDate'       => $order_date,
		'orderStatus'     => $order_status,
		'invoiceDate'     => $invoice_date,
		'transactionId'   => $transaction_id,
		'billingAddress'  => $billing_address,
		'shippingAddress' => $shipping_address,
	);

	$order_totals = array(
		'mrp'              => round( $total_mrp, 2 ),
		'quantity'         => $total_quantity,
		'tax'              => round( $total_tax, 2 ),
		'subtotal'         => round( $total_subtotal, 2 ),
		'discount'         => round( $total_discount, 2 ),
		'shippingSubtotal' => round( $shipping_subtotal, 2 ),
		'shippingTax'      => round( $shipping_tax, 2 ),
		'shippingTotal'    => round( $shipping_total, 2 ),
		'amount'           => round( $total_amount, 2 ),
		'amountInWords'    => $amount_in_words,
	);

	$response_data = array(
		'orderDetails'   => $order_details,
		'productDetails' => $item_details,
		'orderTotals'    => $order_totals,
	);

	return rc_wcpos_response_handler( 200, 'Orders Fetched Successfully', $response_data );
}
