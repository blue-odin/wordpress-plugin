<?php

namespace BlueOdin\WordPress;

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 * @author     Your Name <email@example.com>
 */
final class BlueOdinDeactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate(): void {
		//self::drop_all_tables();
	}

	private static function drop_all_tables()
	{
		global $wpdb;
		$tables = [
			$wpdb->prefix . "bo_sessions",
			$wpdb->prefix . "bo_carts",
			$wpdb->prefix . "bo_cart_items",
			$wpdb->prefix . "bo_utm_data",
		];

		foreach ($tables as $tablename) {
			$wpdb->query("DROP TABLE IF EXISTS $tablename");
		}
	}

}