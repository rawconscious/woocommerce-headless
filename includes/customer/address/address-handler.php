<?php
/**
 * File for handle customer address.
 *
 * @package RawConscious.
 */

/**
 * Function to fetch customer addresses from the database.
 *
 * @param int $address_id The ID of the address to fetch. If 0, all addresses for the user will be fetched.
 * @param int $user_id The ID of the user whose addresses to fetch.
 *
 * @return array|null An array containing the billing and shipping addresses, or null if unable to fetch addresses.
 */
function rc_wcpos_get_addresses( int $address_id, int $user_id ) {
    // Initialize the condition for the database query.
    $condition = '';

    // If an address ID is provided, fetch only that address for the user.
    // Otherwise, fetch all addresses for the user.
    if ( $address_id ) {
        $condition = "user_id = $user_id AND id = $address_id";
    } else {
        $condition = "user_id = $user_id ";
    }

    // Fetch the addresses from the database.
    $address_result = rc_wcpos_get_results( 'rc_user_meta', $condition );

    // If no addresses were found, return a 204 No Content response.
    if (! $address_result ) {
        return rc_wcpos_response_handler( 204, 'Unable to fetch address', null );
    }

    // Initialize arrays to store the billing and shipping addresses.
    $billing_address  = array();
    $shipping_address = array();

    // Loop through the fetched addresses and separate them into billing and shipping arrays.
    foreach ( $address_result as $result ) {
        if ( 'billing_address' === $result['meta_key'] ) {
            $form_data         = array();
            $form_data         = maybe_unserialize( $result['meta_value'] );
            $form_data['id']   = $result['id'];
            $billing_address[] = $form_data;
        }
        if ( 'shipping_address' === $result['meta_key'] ) {
            $form_data          = array();
            $form_data          = maybe_unserialize( $result['meta_value'] );
            $form_data['id']    = $result['id'];
            $shipping_address[] = $form_data;
        }
    }

    // Prepare the return data array.
    $return_data = array(
        'billingAddress'  => $billing_address,
        'shippingAddress' => $shipping_address,
    );

    // Return a 200 OK response with the fetched addresses.
    return rc_wcpos_response_handler( 200, 'Address Fetched Successfully', $return_data );
}

/**
 * Function to fetch the default billing and shipping addresses of a user.
 *
 * @param int $user_id The ID of the user whose addresses to fetch.
 *
 * @return array A response array containing the billing and shipping addresses.
 *
 */
function rc_wcpos_get_default_address( int $user_id ) {
    // Fetch billing address details from WooCommerce user meta data.
    $billing_address = array(
        'firstName' => get_user_meta( $user_id, 'billing_first_name', true ),
        'lastName'  => get_user_meta( $user_id, 'billing_last_name', true ),
        'address1'  => get_user_meta( $user_id, 'billing_address_1', true ),
        'address2'  => get_user_meta( $user_id, 'billing_address_2', true ),
        'city'      => get_user_meta( $user_id, 'billing_city', true ),
        'state'     => get_user_meta( $user_id, 'billing_state', true ),
        'postcode'  => get_user_meta( $user_id, 'billing_postcode', true ),
        'country'   => get_user_meta( $user_id, 'billing_country', true ),
        'phone'     => get_user_meta( $user_id, 'billing_phone', true ),
    );

    // Fetch shipping address details from WooCommerce user meta data.
    $shipping_address = array(
        'firstName' => get_user_meta( $user_id, 'hipping_first_name', true ),
        'lastName'  => get_user_meta( $user_id, 'hipping_last_name', true ),
        'address1'  => get_user_meta( $user_id, 'hipping_address_1', true ),
        'address2'  => get_user_meta( $user_id, 'hipping_address_2', true ),
        'city'      => get_user_meta( $user_id, 'hipping_city', true ),
        'state'     => get_user_meta( $user_id, 'hipping_state', true ),
        'postcode'  => get_user_meta( $user_id, 'hipping_postcode', true ),
        'country'   => get_user_meta( $user_id, 'hipping_country', true ),
        'phone'     => get_user_meta( $user_id, 'hipping_phone', true ),
    );

    // Prepare the return data array.
    $return_data = array(
        'billingAddress'  => $billing_address,
        'shippingAddress' => $shipping_address,
    );

    // Return a 200 OK response with the fetched addresses.
    return rc_wcpos_response_handler( 200, 'Address fetched successfully', $return_data );
}