<?php
/**
 * Files to handle POS products.
 *
 * @package RawConscious.
 */

/**
 * Get POS Products Data From Woocommerce.
 *
 * @return array
 */
function rc_wcpos_get_pos_products() {

	$args = array(
		'status'     => array( 'private' ),
		'visibility' => 'hidden',
		'virtual'    => true,
		'limit'      => -1,
	);

	$products_class = wc_get_products( $args );
	$product_data   = array();

	foreach ( $products_class as $product ) {
		$image_url = wp_get_attachment_image_url( $product->image_id );
		if ( 'instock' === $product->stock_status ) {
			$product_data[] = array(
				'productId' => $product->id,
				'name'      => $product->name,
				'price'     => $product->price,
				'imageUrl'  => $image_url,
			);
		}
	}

	if ( 0 < count( $product_data ) ) {
		return rc_wcpos_response_handler( 200, 'Products fetched Successfully', $product_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Products Available', null );
	}
}
