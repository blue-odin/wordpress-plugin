<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BlueOdin
 * @subpackage BlueOdin/includes
 * @author     Your Name <email@example.com>
 */
final class BlueOdin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      BlueOdinLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BLUE_ODIN_VERSION' ) ) {
			$this->version = BLUE_ODIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'blueodin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - BlueOdinLoader. Orchestrates the hooks of the plugin.
	 * - BlueOdin_i18n. Defines internationalization functionality.
	 * - BlueOdinAdmin. Defines all hooks for the admin area.
	 * - BlueOdinPublic. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-blue-odin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-blue-odin-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-blue-odin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-blue-odin-public.php';

		/**
		 * The class responsible for handling the session
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-blue-odin-session.php';

		/**
		 * The class responsible for tracking UTM parameters
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-blue-odin-utm-tracking.php';

		/**
		 * The class responsible for tracking additions to the cart
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-blue-odin-abandoned-cart.php';

		/**
		 * The class responsible for processing the webhook for carts
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-blue-odin-cart-webhook.php';

		$this->loader = new BlueOdinLoader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the BlueOdin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new BlueOdin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new BlueOdinAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new BlueOdinPublic( $this->get_plugin_name(), $this->get_version() );
		$session = new BlueOdinSession();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$plugin_utm_tracking = new BlueOdinUTMTracking($session);
		$this->loader->add_action('init', $plugin_utm_tracking, 'action_init');
		$this->loader->add_action('wp', $plugin_utm_tracking, 'action_wp');
		$this->loader->add_action('woocommerce_thankyou', $plugin_utm_tracking, 'action_woocommerce_thankyou');
		$this->loader->add_filter('query_vars', $plugin_utm_tracking, 'filter_query_vars');

		$plugin_abandoned_cart = new BlueOdinAbandonedCart($session);
		$this->loader->add_action('woocommerce_add_to_cart', $plugin_abandoned_cart, 'action_woocommerce_add_to_cart', 10, 6);
		$this->loader->add_action('woocommerce_cart_item_removed', $plugin_abandoned_cart, 'action_woocommerce_cart_item_removed', 10, 2);
		$this->loader->add_action('woocommerce_cart_item_restored', $plugin_abandoned_cart, 'action_woocommerce_cart_item_restored', 10, 2);
		$this->loader->add_action('woocommerce_cart_emptied', $plugin_abandoned_cart, 'action_woocommerce_cart_emptied');
		$this->loader->add_action('woocommerce_cart_item_set_quantity', $plugin_abandoned_cart, 'action_woocommerce_cart_item_set_quantity', 10, 3);

		$plugin_webhook = new BlueOdinCartWebhook($this->loader);
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    BlueOdinLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}