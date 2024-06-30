<?php
/**
 * Files to generate data into csv file.
 *
 * @package RawConscious.
 */

/**
 * Generates Customer Data Into CSV File.
 *
 * @param array $filter_status  Filters.
 *
 * @return array
 */
function rc_wcpos_customer_generate_csv( array $filter_status ) {
	$start_date = $filter_status['date'];

	$date_created = rc_wcpos_create_date_string( $start_date );

	$customer_data = rc_wcpos_retrieve_customer_data( $date_created );

	if ( ! $customer_data ) {
		return array(
			'isSuccess' => false,
		);
	}

	ob_clean();

	$csv_file = fopen( 'php://temp', 'w' );
	$csv_data = array();

	$headers = array( 'Id', 'Customer Id', 'Customer Phone', 'Registered At' );

	foreach ( $customer_data as $data ) {
		$csv_data[] = array( $data['Id'], $data['customer_id'], $data['customer_phone'], $data['created_at'] );
	}

	fputcsv( $csv_file, $headers );

	foreach ( $csv_data as $data ) {
		fputcsv( $csv_file, $data );
	}

	rewind( $csv_file );

	$csv_content = stream_get_contents( $csv_file );

	fclose( $csv_file );

	$base64_csv_content = base64_encode( $csv_content );

	header( 'Content-Type: application/json' );

	$response_data = array(
		'isSuccess'  => true,
		'csvContent' => $base64_csv_content,
		'fileName'   => 'customer-data.csv',
	);

	return rest_ensure_response( $response_data );
}

/**
 * Generates Orders Data Into CSV File.
 *
 * @param array $filter_status  Filters.
 *
 * @return array
 */
// function rc_wcpos_customer_generate_csv( array $filter_status ) {
// $start_date   = $filter_status['date'];
// $order_status = $filter_status['date'];

// $date_created = rc_wcpos_create_date_string( $start_date );

// $orders_data = rc_wcpos_retrieve_orders_data( $date_created );

// if ( ! $orders_data ) {
// return array(
// 'isSuccess' => false,
// );
// }

// ob_clean();

// $csv_file = fopen( 'php://temp', 'w' );
// $csv_data = array();

// $headers = array( 'Id', 'Order Id', 'Order Value', 'Order Items', 'Order Status', 'Payment ID' );

// foreach ( $customer_data as $data ) {
// $csv_data[] = array( $data['Id'], $data['customer_id'], $data['customer_phone'], $data['created_at'] );
// }

// fputcsv( $csv_file, $headers );

// foreach ( $csv_data as $data ) {
// fputcsv( $csv_file, $data );
// }

// rewind( $csv_file );

// $csv_content = stream_get_contents( $csv_file );

// fclose( $csv_file );

// $base64_csv_content = base64_encode( $csv_content );

// header( 'Content-Type: application/json' );

// $response_data = array(
// 'isSuccess'  => true,
// 'csvContent' => $base64_csv_content,
// 'fileName'   => 'customer-data.csv',
// );

// return rest_ensure_response( $response_data );
// }
