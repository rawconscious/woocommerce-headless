<?php
/**
 * Rest Route For Admin Page.
 *
 * @package RawConscious.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', 'register_rc_wcpos_admin_routes' );

/**
 * Register Routes
 */
function register_rc_wcpos_admin_routes() {
	register_rest_route(
		'rc-wcpos/v1',
		'customer/get-list/(?P<filter_status>[a-zA-Z0-9\-]+)/(?P<offset>[\d]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rc_wcpos_admin_get_customer_list',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'order/get-list/',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_admin_get_order_list',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
	register_rest_route(
		'rc-wcpos/v1',
		'generate-csv',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'rc_wcpos_admin_generate_csv',
			'permission_callback' => '__return_true',
			'args'                => array(),
		)
	);
}

/**
 * Rest API callback function.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array.
 */
function rc_wcpos_admin_get_customer_list( WP_REST_Request $request ) {
	$filter_status = $request->get_param( 'filter_status' );
	$offset        = (int) $request->get_param( 'offset' );

	$results       = array();
	$customer_data = array();

	$header = array(
		'Id'            => 'Database ID',
		'customerId'    => 'Customer Id',
		'customerPhone' => 'Phone Number',
		'createdAt'     => 'Registered At',
	);

	$results['header'] = $header;

	$date_created = rc_wcpos_create_date_string( $filter_status );

	$retrieved_data = rc_wcpos_retrieve_customer_data( $date_created, $offset );

	if ( 0 === count( $retrieved_data ) ) {
		return array(
			'isSuccess' => false,
			'results'   => $results,
		);
	}

	$customer_count = rc_wcpos_get_customer_count();

	$has_more = 0 >= ( $customer_count - ( $offset + 10 ) ) ? false : true;

	foreach ( $retrieved_data as $data ) {
		$db_id          = $data['Id'];
		$customer_id    = $data['customer_id'];
		$customer_phone = $data['customer_phone'];
		$created_at     = $data['created_at'];

		$customer_data[] = array(
			'Id'            => $db_id,
			'customerId'    => $customer_id,
			'customerPhone' => $customer_phone,
			'createdAt'     => $created_at,
		);
	}

	$results['data'] = $customer_data;

	return array(
		'isSuccess' => true,
		'results'   => $results,
		'hasMore'   => $has_more,
	);
}

/**
 * Rest API callback function.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array.
 */
function rc_wcpos_admin_get_order_list( WP_REST_Request $request ) {
	$filter_status = $request->get_param( 'filterStatus' );
	$offset        = (int) $request->get_param( 'offset' );

	$start_date   = $filter_status['date'];
	$order_status = $filter_status['status'];

	$results    = array();
	$order_data = array();

	$header = array(
		'Id'          => 'Database ID',
		'orderId'     => 'Order Id',
		'orderValue'  => 'Order Value',
		'orderStatus' => 'Order Status',
		'customerId'  => 'Customer ID',
		'createdAt'   => 'Ordered At',
	);

	$results['header'] = $header;

	$date_created = rc_wcpos_create_date_string( $start_date );

	$retrieved_data = rc_wcpos_retrieve_order_data( $date_created, $order_status, $offset );

	if ( 0 === count( $retrieved_data ) ) {
		return array(
			'isSuccess' => false,
			'results'   => $results,
		);
	}

	$order_count = rc_wcpos_get_order_count();

	$has_more = 0 >= ( $order_count - ( $offset + 10 ) ) ? false : true;

	foreach ( $retrieved_data as $data ) {
		$db_id        = $data['Id'];
		$order_id     = $data['order_id'];
		$order_value  = $data['order_value'];
		$order_status = $data['order_status'];
		$customer_id  = $data['customer_id'];
		$created_at   = $data['created_at'];

		$order_value = round( $order_value / 100, 2 );
		$order_value = '₹' . $order_value;

		$order_data[] = array(
			'Id'          => $db_id,
			'orderId'     => $order_id,
			'orderValue'  => $order_value,
			'orderStatus' => $order_status,
			'customerId'  => $customer_id,
			'createdAt'   => $created_at,
		);
	}

	$results['data'] = $order_data;

	return array(
		'isSuccess' => true,
		'results'   => $results,
		'hasMore'   => $has_more,
	);
}

/**
 * Rest API Callback Function.
 *
 * @param WP_REST_Request $request Rest Request.
 *
 * @return array
 */
function rc_wcpos_admin_generate_csv( WP_REST_Request $request ) {
	$table_name    = $request->get_param( 'table' );
	$filter_status = $request->get_param( 'filterStatus' );

	if ( 'customer-table' === $table_name ) {
		return rc_wcpos_customer_generate_csv( $filter_status );
	}

	if ( 'order-table' === $table_name ) {
		return rc_wcpos_orders_generate_csv( $filter_status );
	}
}
