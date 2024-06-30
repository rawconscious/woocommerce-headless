<?php
/**
 * Database File Woocommerce POS.
 *
 * @package rawconscious
 */

/**
 * Function Which Runs DB creation When Plugin Loaded .
 */
function rc_pos_create_db() {
	// rc_wcpos_create_db_customer(); phpcs:ignore.
	rc_wcpos_create_db_auth();
	rc_wcpos_create_db_auth_log();
	rc_wcpos_create_db_user_meta();
	rc_wcpos_create_db_cart();
	rc_wcpos_create_db_orders();
	rc_wcpos_create_db_payment();
}

add_action( 'plugins_loaded', 'rc_pos_create_db' );
/**
 * Creates Database for Customers.
 */
function rc_wcpos_create_db_customer() {

	global $wpdb;
	$tablename = $wpdb->prefix . 'rc_wcpos_customer';

	$installed_version = get_option( $tablename . '_version', '0.0.0' );

	if ( version_compare( $installed_version, '1.3.0', '<' ) ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE $tablename (
		Id int(11) unsigned AUTO_INCREMENT,
		customer_id varchar(250) NOT NULL,
		customer_phone bigint NOT NULL,
		is_verified boolean NOT NULL DEFAULT FALSE,
		created_at TIMESTAMP NOT NULL DEFAULT current_timestamp,
		PRIMARY KEY (Id),
		UNIQUE (customer_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // phpcss: ignore.

		/**
		* It seems IF NOT EXISTS isn't needed if you're using dbDelta - if the table already exists it'll
		* compare the schema and update it instead of overwriting the whole table.
		*
		* @link https://code.tutsplus.com/tutorials/custom-database-tables-maintaining-the-database--wp-28455
		*/
		dbDelta( $sql );

		add_option( $tablename . '_version', '1.0.0' ); // phpcs: ignore.

	}
} //phpcs:ignore

/**
 * Creates table for custom user table.
 */
function rc_wcpos_create_db_auth() {
	global $wpdb;

	$table_auth = $wpdb->prefix . 'rc_auth';
	$table_user = $wpdb->prefix . 'users';

	$installed_version = get_option( $table_auth . '_version', '0.0.0' );
	$table_user_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $table_user ) );

	if ( version_compare( $installed_version, '1.0.0', '<' ) && $table_user_exists ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = 'CREATE TABLE ' . $table_auth . " (
			user_id bigint unsigned NOT NULL,
            user_name varchar(50) NOT NULL,
            user_sub varchar(50) NOT NULL,
            user_email varchar(50) DEFAULT NULL,
            user_phone bigint DEFAULT NULL,
			UNIQUE (user_id),
			UNIQUE (user_name),
			UNIQUE (user_sub),
			UNIQUE (user_email),
			UNIQUE (user_phone),
			FOREIGN KEY (user_id) REFERENCES $table_user(ID)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * It seems IF NOT EXISTS isn't needed if you're using dbDelta - if the table already exists it'll
		 * compare the schema and update it instead of overwriting the whole table.
		 *
		 * @link https://code.tutsplus.com/tutorials/custom-database-tables-maintaining-the-database--wp-28455
		 */
		dbDelta( $sql );

		add_option( $table_auth . '_version', '1.0.0' );

	}
}

/**
 * Creates table for authentication log table.
 */
function rc_wcpos_create_db_auth_log() {
	global $wpdb;

	$table_auth     = $wpdb->prefix . 'rc_auth';
	$table_auth_log = $wpdb->prefix . 'rc_auth_log';

	$installed_version = get_option( $table_auth_log . '_version', '0.0.0' );
	$table_auth_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $table_auth ) );

	if ( version_compare( $installed_version, '1.0.0', '<' ) && $table_auth_exists ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE $table_auth_log (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id bigint unsigned NOT NULL,
            event_type varchar(50) NOT NULL,
			event_log longtext NOT NUll,
			created_at TIMESTAMP NOT NULL DEFAULT current_timestamp,
			FOREIGN KEY (user_id) REFERENCES $table_auth(user_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * It seems IF NOT EXISTS isn't needed if you're using dbDelta - if the table already exists it'll
		 * compare the schema and update it instead of overwriting the whole table.
		 *
		 * @link https://code.tutsplus.com/tutorials/custom-database-tables-maintaining-the-database--wp-28455
		 */
		dbDelta( $sql );

		add_option( $table_auth_log . '_version', '1.0.0' );

	}
}

/**
 * Function to create user meta.
 */
function rc_wcpos_create_db_user_meta() {
	global $wpdb;

	$table_usermeta = $wpdb->prefix . 'rc_user_meta';
	$table_user     = $wpdb->prefix . 'users';

	$installed_version = get_option( $table_usermeta . '_version', '0.0.0' );
	$auth_table_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $table_user ) );

	if ( version_compare( $installed_version, '1.0.0', '<' ) && $auth_table_exists ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE $table_usermeta (
			id INT NOT NULL AUTO_INCREMENT,
			user_id bigint unsigned NOT NULL,
			meta_key varchar(50) NOT NULL,
			meta_value longtext,
			PRIMARY KEY (id),
			FOREIGN KEY (user_id) REFERENCES $table_user(ID)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * It seems IF NOT EXISTS isn't needed if you're using dbDelta - if the table already exists it'll
		 * compare the schema and update it instead of overwriting the whole table.
		 *
		 * @link https://code.tutsplus.com/tutorials/custom-database-tables-maintaining-the-database--wp-28455
		 */
		dbDelta( $sql );

		add_option( $table_usermeta . '_version', '1.0.0' );

	}
}

/**
 * Creates table for cart.
 */
function rc_wcpos_create_db_cart() {
	global $wpdb;
	$tablename  = $wpdb->prefix . 'rc_active_cart';
	$table_user = $wpdb->prefix . 'users';

	$auth_table_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $table_user ) );
	$installed_version = get_option( $tablename . '_version', '0.0.0' );

	if ( version_compare( $installed_version, '1.0.0', '<' ) ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE $tablename (
		id INT NOT NULL AUTO_INCREMENT,
		cart_id varchar(250) NOT NULL,
		user_id bigint unsigned,
		cart_data JSON NOT NULL,
		cart_meta longtext,
		PRIMARY KEY (id),
		UNIQUE (cart_id),
		FOREIGN KEY (user_id) REFERENCES $table_user(ID)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		add_option( $tablename . '_version', '1.0.0' );
	}
}

/**
 * Creates Database for Orders.
 */
function rc_wcpos_create_db_orders() {
	global $wpdb;
	$tablename  = $wpdb->prefix . 'rc_wcpos_orders';
	$auth_table = $wpdb->prefix . 'rc_auth';

	$installed_version = get_option( $tablename . '_version', '0.0.0' );
	$auth_table_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $auth_table ) );

	if ( version_compare( $installed_version, '1.0.0', '<' ) && $auth_table_exists ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = 'CREATE TABLE ' . $tablename . " (
            Id int(11) unsigned AUTO_INCREMENT,
			order_id varchar(250) NOT NULL,
			order_value int NOT NULL,
			order_items blob NOT NULL,
            order_status varchar(50) NOT NULL,
            pos_manager_id bigint unsigned,
            customer_id bigint unsigned,
			created_at TIMESTAMP NOT NULL DEFAULT current_timestamp, 
            PRIMARY KEY (Id),
			UNIQUE (order_id),
			FOREIGN KEY (customer_id) REFERENCES $auth_table(user_id),
			FOREIGN KEY (pos_manager_id) REFERENCES $auth_table(user_id),
			INDEX (pos_manager_id),
			INDEX (customer_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		update_option( $tablename . '_version', '1.0.0' );
	}
}

/**
 * Creates Database for Orders.
 */
function rc_wcpos_create_db_payment() {

	global $wpdb;
	$tablename    = $wpdb->prefix . 'rc_wcpos_payment';
	$orders_table = $wpdb->prefix . 'rc_wcpos_orders';

	$installed_version   = get_option( $tablename . '_version', '0.0.0' );
	$orders_table_exists = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $orders_table ) );

	if ( version_compare( $installed_version, '1.0.0', '<' ) && $orders_table_exists ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = 'CREATE TABLE ' . $tablename . " (
            Id int(11) unsigned AUTO_INCREMENT,
			payment_id varchar(250) NOT NULL,
            payment_status varchar(50) NOT NULL,
            order_id varchar(250) NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT current_timestamp, 
            PRIMARY KEY (Id),
			UNIQUE (payment_id),
			FOREIGN KEY (order_id) REFERENCES $orders_table(order_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * It seems IF NOT EXISTS isn't needed if you're using dbDelta - if the table already exists it'll
		 * compare the schema and update it instead of overwriting the whole table.
		 *
		 * @link https://code.tutsplus.com/tutorials/custom-database-tables-maintaining-the-database--wp-28455
		 */
		dbDelta( $sql );

		add_option( $tablename . '_version', '1.0.0' );

	}
}
