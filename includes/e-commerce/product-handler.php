<?php
/**
 * Files to handle POS products.
 *
 * @package RawConscious.
 */

/**
 * Get WooCommerce Products Data.
 * 
 * @param array $filters Filter.
 *
 * @return array
 */
function rc_wcpos_get_woo_products( array $filters = null ) {

    $status       = isset( $filters['status'] ) ? $filters['status'] : 'publish';
    $limit        = isset( $filters['perPage'] ) ? $filters['perPage']: -1;
    $category_id  = isset( $filters['category'] ) ? (int) $filters['category'] : '';
    $include      = isset( $filters['productIds'] ) ? $filters['productIds'] : array();
    $stock_status = isset( $filters['stockStatus'] ) ? $filters['stockStatus'] : '';
    $order_by     = isset( $filters['orderBy'] ) ? $filters['orderBy'] : 'date';
    $order        = isset( $filters['order'] ) ? $filters['order'] : 'DESC'; 
    $search       = isset( $filters['search'] ) ? $filters['search'] : '';

    if ( $category_id ) {
        $category = get_term_by( 'id', $category_id, 'product_cat' );
        if ( $category ) {
            $category_slug = $category->slug;
        }
    }
    
	$args = array(
		'status'       => $status,
		'limit'        => $limit,
        'include'      => $include,
        'orderby'      => $order_by,
        'order'        => $order,
        'stock_status' => $stock_status,
        's'            => $search,
	);

    if ( $category_slug ) {
        $args['category'] = array( $category_slug );
    }

	$products_class = wc_get_products( $args );
	$product_data   = array();

    if ( empty( $products_class ) ) {
        return rc_wcpos_response_handler( 204, 'No Products Available', null );
    }

	foreach ( $products_class as $key => $product ) {
        $product_data[$key] = $product->get_data();

        $images = rc_wcpos_get_product_images( $product );
        $product_data[$key]['images'] = $images;

        $category = rc_wcpos_get_product_category( $product );
        $product_data[$key]['categories'] = $category;
        
        $variations = rc_wcpos_get_product_variations( $product );
        $product_data[$key]['variations'] = $variations;
        
        $related_ids = wc_get_related_products( $product->get_id() );
        $product_data[$key]['related_ids'] = $related_ids;
	}

	if ( 0 < count( $product_data ) ) {
		return rc_wcpos_response_handler( 200, 'Products fetched Successfully', $product_data );
	} else {
		return rc_wcpos_response_handler( 204, 'No Products Available', null );
	}
}

/**
 * Get WooCommerce Categories Data.
 * 
 * @param array $filters Filter parameters.
 * 
 * @return array
 */
function rc_wcpos_get_woo_categories( array $filters = null ) {
    $search     = isset( $filters['search'] ) ? $filters['search'] : '';
    $include    = isset( $filters['include'] ) ? $filters['include'] : '';
    $exclude    = isset( $filters['exclude'] ) ? $filters['exclude'] : '';
    $order      = isset( $filters['order'] ) ? $filters['order'] : 'DESC';
    $order_by   = isset( $filters['orderBy'] ) ? $filters['orderBy'] : 'date';
    $hide_empty = isset( $filters['hideEmpty'] ) ? $filters['hideEmpry'] : false;
    $slug       = isset( $filters['slug'] ) ? $filters['slug'] : '';
    $product_id = isset( $filters['product_id'] ) ? $filters['product_id'] : null;

    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => $hide_empty,
        'search'     => $search,
        'include'    => $include,
        'exclude'    => $exclude,
        'order'      => $order,
        'order_by'   => $order_by,
        'slug'       => $slug,
    );

    if ( $product_id ) {
        $product_cats    = wc_get_product_term_ids($product_id, 'product_cat');
        $args['include'] = $product_cats;
    }

    $categories = get_terms($args);

    if (is_wp_error($categories)) {
        return rc_wcpos_response_handler(204, 'Not Categories Found', null );
    }

    $category_data = array();

    foreach ($categories as $category) {
        $category_data[] = array(
            'id'          => $category->term_id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'count'       => $category->count,
            'parent'      => $category->parent,
            'url'         => get_term_link($category)
        );
    }

    return rc_wcpos_response_handler( 200, 'Categories fetched successfully', $category_data );
}
