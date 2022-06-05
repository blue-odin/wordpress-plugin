<?php

/*
 * Plugin Name: Blueodin Plugin
 * Plugin URI: https://www.blueodin.io/plugin
 * Description: Plugin to add extra functionality to BlueOdin.
 * Version: 1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: Blue Odin
 * Author URI: http://www.blueodin.io
 * License: A "Slug" license name e.g. GPL2
 * WC requires at least: 3.0.0
 * WC tested up to: 6.5.1
*/

require_once dirname(__FILE__)."/vendor/autoload.php";

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
use BlueOdin\WordPress\BlueOdin;
use BlueOdin\WordPress\BlueOdinActivator;
use BlueOdin\WordPress\BlueOdinDeactivator;

// If this file is called directly, abort.


if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
const BLUE_ODIN_VERSION = '1.0.0';

/**
 * The code that runs during plugin activation.
 */
function activate_blue_odin() {
	BlueOdinActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_blue_odin() {
	BlueOdinDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_blue_odin' );
register_deactivation_hook( __FILE__, 'deactivate_blue_odin' );

require plugin_dir_path( __FILE__ ) . 'includes/functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_blue_odin() {
	$plugin = new BlueOdin();
	$plugin->run();
}
run_blue_odin();