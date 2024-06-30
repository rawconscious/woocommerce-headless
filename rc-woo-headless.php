<?php
/**
 * Plugin Name: RC WooCommerce Headless.
 * Version: 1.0.9
 *
 * @package Rawconscious
 */

define( 'RC_WOO_VERSION', '1.0.9' );
define( 'RC_WOO_PREFIX', 'rc_wcpos' );
define( 'RC_WOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'RC_WOO_URI', plugin_dir_url( __FILE__ ) );

add_action( 'activate_plugin', 'rc_woo_headless_dependencies' );

/**
 * Check if dependencies are installed
 *
 * @return void
 */
function rc_woo_headless_dependencies() {

	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	$dependencies_installed = true;
	$dependencies_installed = $dependencies_installed && is_plugin_active( 'rc-aws-sdk/rc-aws-sdk.php' );

	if ( ! $dependencies_installed ) {
		wp_die( 'rc-aws-sdk: dependencies "rc-aws-sdk/rc-aws-sdk.php" not installed. Please check all the required dependencies installed before activating rc-woocommerce-headless' );
	}
}

// require to load filter hooks.
require_once RC_WOO_PATH . '/includes/hooks.php';

// require_once RC_WOO_PATH . '/includes/admin/admin-menu.php';
// require_once RC_WOO_PATH . '/includes/admin/admin-enqueue.php';
// require_once RC_WOO_PATH . '/includes/admin/admin-api.php'; phpcs:ignore.

require_once RC_WOO_PATH . 'includes/database/creation.php';
require_once RC_WOO_PATH . 'includes/database/deletion.php';
require_once RC_WOO_PATH . 'includes/database/insertion.php';
require_once RC_WOO_PATH . 'includes/database/retrieve.php';
require_once RC_WOO_PATH . 'includes/database/update.php';

require_once RC_WOO_PATH . 'includes/modules/customer-manager.php';
require_once RC_WOO_PATH . 'includes/modules/sms-manager.php';

require_once RC_WOO_PATH . 'includes/helper-functions/generate-csv.php';
require_once RC_WOO_PATH . 'includes/helper-functions/generate-date.php';
require_once RC_WOO_PATH . 'includes/helper-functions/generate-message.php';
require_once RC_WOO_PATH . 'includes/helper-functions/generate-uniqid.php';

// Require to load authentication model.
require_once RC_WOO_PATH . 'includes/auth/auth.php';

// Require to load cart model.
require_once RC_WOO_PATH . 'includes/cart/cart.php';

// Require to load order files.
require_once RC_WOO_PATH . 'includes/orders/order.php';

// Require to load pos files.
require_once RC_WOO_PATH . 'includes/pos/pos.php';

// Require to load posts files.
require_once RC_WOO_PATH . 'includes/posts/post.php';

// Require to load e-commerce file.
require_once RC_WOO_PATH . 'includes/e-commerce/e-commerce.php';

// Require to load customer file.
require_once RC_WOO_PATH . 'includes/customer/customer.php';

// Requires to load product files.
require_once RC_WOO_PATH . 'includes/products/product.php';

// Require to load payment files.
require_once RC_WOO_PATH . 'includes/payment/payment.php';
