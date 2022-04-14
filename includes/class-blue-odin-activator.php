<?php

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
class BlueOdinActivator {
	const DB_VERSION = "1.0";

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_database();
	}

	private function create_database () {
		global $wpdb;

		$table_name = $wpdb->prefix . "bo_utm_data";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    session_id tinytext NOT NULL,
                    name tinytext NOT NULL,
                    value text NOT NULL,
                    PRIMARY KEY  (id),
                    UNIQUE KEY utm_data_name_session (name(50), session_id(50))
                ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( "bo_db_version", self::DB_VERSION );
	}


}