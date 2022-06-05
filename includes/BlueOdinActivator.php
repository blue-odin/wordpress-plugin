<?php
namespace BlueOdin\WordPress;

use wpdb;

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 * @author     Your Name <email@example.com>
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

final class BlueOdinActivator {
	const DB_VERSION = "1.0";

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate(): void {
		self::create_database();
	}

	private static function create_database (): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		self::create_bo_utm_data( $wpdb, $charset_collate );
		self::create_bo_carts($wpdb, $charset_collate);
		self::create_bo_cart_items($wpdb, $charset_collate);

		add_option( "bo_db_version", self::DB_VERSION );
	}

	/**
	 * @param wpdb $wpdb
	 * @param string $charset_collate
	 *
	 * @return void
	 */
	private static function create_bo_utm_data( wpdb $wpdb, string  $charset_collate ): void {
		$table_name = $wpdb->prefix . "bo_utm_data";

		$sql = "CREATE TABLE $table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    session_id tinytext NOT NULL,
                    name tinytext NOT NULL,
                    value text NOT NULL,
                    PRIMARY KEY  (id),
                    UNIQUE KEY utm_data_name_session (name(50), session_id(50))
                ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * @param wpdb $wpdb
	 * @param $charset_collate
	 *
	 * @return void
	 */
	private static function create_bo_carts( wpdb $wpdb, $charset_collate ): void {
		$table_name = $wpdb->prefix . "bo_carts";
		$unique_key_session_id = $wpdb->prefix . 'cart_session_id';

		$sql = "CREATE TABLE $table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    session_id tinytext NOT NULL,
                    user_id mediumint(11),
                    ip_address tinytext NOT NULL,
                    PRIMARY KEY  (id),
                    UNIQUE KEY $unique_key_session_id (session_id(50))
                ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * @param wpdb $wpdb
	 * @param $charset_collate
	 *
	 * @return void
	 */
	private static function create_bo_cart_items( wpdb $wpdb, $charset_collate ): void {
		$table_name = $wpdb->prefix . "bo_cart_items";
		$unique_key_cart_id_item_key = $wpdb->prefix . 'cart_items_cart_id_item_key';

		$sql = "CREATE TABLE $table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    cart_id mediumint(9) NOT NULL,
                    item_key varchar(32) NOT NULL,
                    product_id mediumint(9) NOT NULL,
                    quantity smallint NOT NULL,
                    PRIMARY KEY  (id),
                   UNIQUE KEY $unique_key_cart_id_item_key (cart_id, item_key)
                ) $charset_collate;";

		dbDelta( $sql );
	}


}