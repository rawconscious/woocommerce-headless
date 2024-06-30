<?php
/**
 * Files to handle WooCommerce Products.
 * 
 * @package RawConscious
 */

/**
 * Function to get all images related to product.
 * 
 * @param $product Product.
 * 
 * @return array $images Image.
 */
function rc_wcpos_get_product_images( $product ) {
    $images = array();

    if ( has_post_thumbnail( $product->get_id() ) ) {
        $thumbnail_id   = $product->get_image_id();
        $thumbnail_src  = wp_get_attachment_image_src( $thumbnail_id, 'full' );
        $thumbnail_alt  = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
        $thumbnail_meta = wp_get_attachment_metadata( $thumbnail_id );
        
        $images[] = array(
            'src'    => $thumbnail_src[0],
            'alt'    => $thumbnail_alt,
            'width'  => $thumbnail_meta['width'],
            'height' => $thumbnail_meta['height'],
        );
    }
    
    $gallery_image_ids = $product->get_gallery_image_ids();
    
    foreach ( $gallery_image_ids as $gallery_image_id ) {
        $gallery_image_src  = wp_get_attachment_image_src( $gallery_image_id, 'full' );
        $gallery_image_alt  = get_post_meta( $gallery_image_id, '_wp_attachment_image_alt', true );
        $gallery_image_meta = wp_get_attachment_metadata( $gallery_image_id );
        
        $images[] = array(
            'src'    => $gallery_image_src[0],
            'alt'    => $gallery_image_alt,
            'width'  => $gallery_image_meta['width'],
            'height' => $gallery_image_meta['height'],
        );
    }

    return $images;
}

/**
 * Function to get the product category.
 * 
 * @param $product Product.
 * 
 * @return array $category_data Product Category.
 */
function rc_wcpos_get_product_category( $product ) {
    $categories = wp_get_post_terms( $product->get_id(), 'product_cat' );
    
    $category_data = array();
    
    foreach ( $categories as $category ) {
        $category_data[] = array(
            'name' => $category->name,
            'id'   => $category->term_id,
            'slug' => $category->slug,
        );
    }
    return $category_data;
}

/**
 * Function to get variation of product.
 * 
 * @param $product product.
 * 
 * @return array $variations product variations.
 */
function rc_wcpos_get_product_variations( $product ) {
    $variations = array();

    if ( $product->is_type( 'variable' ) ) {
        foreach ( $product->get_available_variations() as $variation_data ) {
            $variation = new WC_Product_Variation( $variation_data['variation_id'] );

            $variations[] = array(
                "id"                    => $variation->get_id(),
                "date_created"          => wc_rest_prepare_date_response($variation->get_date_created()),
                "date_created_gmt"      => wc_rest_prepare_date_response($variation->get_date_created(), true),
                "date_modified"         => wc_rest_prepare_date_response($variation->get_date_modified()),
                "date_modified_gmt"     => wc_rest_prepare_date_response($variation->get_date_modified(), true),
                "description"           => $variation->get_description(),
                "permalink"             => get_permalink($variation->get_id()),
                "sku"                   => $variation->get_sku(),
                "price"                 => $variation->get_price(),
                "regular_price"         => $variation->get_regular_price(),
                "sale_price"            => $variation->get_sale_price(),
                "date_on_sale_from"     => wc_rest_prepare_date_response($variation->get_date_on_sale_from()),
                "date_on_sale_from_gmt" => wc_rest_prepare_date_response($variation->get_date_on_sale_from(), true),
                "date_on_sale_to"       => wc_rest_prepare_date_response($variation->get_date_on_sale_to()),
                "date_on_sale_to_gmt"   => wc_rest_prepare_date_response($variation->get_date_on_sale_to(), true),
                "on_sale"               => $variation->is_on_sale(),
                "status"                => $variation->get_status(),
                "purchasable"           => $variation->is_purchasable(),
                "virtual"               => $variation->is_virtual(),
                "downloadable"          => $variation->is_downloadable(),
                "downloads"             => $variation->get_downloads(),
                "download_limit"        => $variation->get_download_limit(),
                "download_expiry"       => $variation->get_download_expiry(),
                "tax_status"            => $variation->get_tax_status(),
                "tax_class"             => $variation->get_tax_class(),
                "manage_stock"          => $variation->managing_stock(),
                "stock_quantity"        => $variation->get_stock_quantity(),
                "stock_status"          => $variation->get_stock_status(),
                "backorders"            => $variation->get_backorders(),
                "backorders_allowed"    => $variation->backorders_allowed(),
                "backordered"           => $variation->is_on_backorder(),
                "weight"                => $variation->get_weight(),
                "dimensions"            => array(
                    "length" => $variation->get_length(),
                    "width"  => $variation->get_width(),
                    "height" => $variation->get_height(),
                ),
                "shipping_class"        => $variation->get_shipping_class(),
                "shipping_class_id"     => $variation->get_shipping_class_id(),
                "image"                 => wc_get_product_attachment_props($variation->get_image_id()),
                "attributes"            => array_map(function($attribute_key, $attribute_value) {
                    $attribute_id     = wc_attribute_taxonomy_id_by_name($attribute_key);
                    $attribute_name   = wc_attribute_label($attribute_key);
                    $attribute_option = $attribute_value;

                    return array(
                        "id"     => $attribute_id,
                        "name"   => $attribute_name,
                        "option" => $attribute_option
                    );
                }, array_keys( $variation->get_attributes() ), $variation->get_attributes() ),
                "menu_order"            => $variation->get_menu_order(),
                "meta_data"             => $variation->get_meta_data(),
                "_links"                => array(
                    "self" => array(
                        array(
                            "href" => rest_url('/wc/v3/products/' . $product->get_id() . '/variations/' . $variation->get_id())
                        )
                    ),
                    "collection" => array(
                        array(
                            "href" => rest_url('/wc/v3/products/' . $product->get_id() . '/variations')
                        )
                    ),
                    "up" => array(
                        array(
                            "href" => rest_url('/wc/v3/products/' . $product->get_id())
                        )
                    )
                )
            );
        }
    }

    return $variations;
}

