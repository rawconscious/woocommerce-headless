<?php
/**
 * File To Register Admin Menu.
 *
 * @package RawConscious.
 */

/**
 * Create subpage under WooCommerce menu
 */
function rc_pos_admin_menu() {
	add_menu_page(
		'Customer List', // page_title.
		'Woocommerce POS',
		'edit_posts', // capability.
		'woocommerce-pos', // menu_slug.
		'rc_pos_admin_menu_callback', // function.
		'dashicons-text', // icon_url.
		26 // position.
	);
}

add_action( 'admin_menu', 'rc_pos_admin_menu' );

/**
 * Callback Function For Admin Menu WooCommerce POS.
 */
function rc_pos_admin_menu_callback() {
	?>
	<div id="react-wrapper-pos-admin"> </div>
	<?php
}

