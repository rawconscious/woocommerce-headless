<?php
/**
 * Files To Enque Admin Styles and Script.
 *
 * @package RawConscious.
 */

/**
 * Enqueue Scripts and Styles.
 */
function rc_wcpos_admin_enqueue_scripts() {
	global $wp;

	$version = WC_POS_VERSION;

	if ( defined( 'LOCAL_ENV' ) && true === LOCAL_ENV ) {
		$version = wp_rand( 111, 9999 );
	}

	$dependancy = require RC_WOO_PATH . 'build/pos-admin/pos-admin.asset.php';

	if ( defined( 'LOCAL_ENV' ) && true === LOCAL_ENV ) {
		$dependancy = array_unique( array_merge( $dependancy['dependencies'], array( 'wp-element' ) ), SORT_REGULAR );
	} else {
		$dependancy = is_array( $dependancy ) ? $dependancy['dependencies'] : array();
	}

	wp_enqueue_style( WC_POS_PREFIX . 'admin-style', WC_POS_URI . 'build/pos-admin/pos-admin.css', array(), $version );
	wp_enqueue_script( WC_POS_PREFIX . 'admin-script', WC_POS_URI . 'build/pos-admin/pos-admin.js', $dependancy, $version, true );
}

add_action( 'admin_enqueue_scripts', 'rc_wcpos_admin_enqueue_scripts' );
