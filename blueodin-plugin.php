<?php

/*
Plugin Name: Blueodin Plugin
Plugin URI: https://www.blueodin.io/plugin
Description: Plugin to add extra functionality to BlueOdin.
Version: 1.0
Author: Blue Odin
Author URI: http://www.blueodin.io
License: A "Slug" license name e.g. GPL2
*/

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
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_blue_odin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-blue-odin-activator.php';
	BlueOdinActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_blue_odin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-blue-odin-deactivator.php';
	BlueOdinDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_blue_odin' );
register_deactivation_hook( __FILE__, 'deactivate_blue_odin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-blue-odin.php';

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