<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Import_Data
 * @subpackage Sejoli_Import_Data/includes
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
 * @package    Sejoli_Import_Data
 * @subpackage Sejoli_Import_Data/includes
 * @author     Sejoli Team <dev@sejoli.co.id>
 */
class Sejoli_Import_Data {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Import_Data_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'SEJOLI_IMPORT_DATA_VERSION' ) ) {
			$this->version = SEJOLI_IMPORT_DATA_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-import-data';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Import_Data_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Import_Data_i18n. Defines internationalization functionality.
	 * - Sejoli_Import_Data_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Import_Data_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-import-data-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-import-data-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/order.php';

		$this->loader = new Sejoli_Import_Data_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Import_Data_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Import_Data_i18n();

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

		$admin = new Sejoli_Import_Data\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu',			$admin, 'add_admin_menu', 1999 );
		$this->loader->add_action( 'wp_ajax_get_users_select2', $admin, 'get_users_select2_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_users_select2', $admin, 'get_users_select2_ajax' );
		$this->loader->add_action( 'wp_ajax_get_products_select2', $admin, 'get_products_select2_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_products_select2', $admin, 'get_products_select2_ajax' ); 
		$this->loader->add_action( 'wp_ajax_get_coupon_select2', $admin, 'get_coupon_select2_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_coupon_select2', $admin, 'get_coupon_select2_ajax' ); 
		$this->loader->add_action( 'admin_init', 			$admin, 'handle_file_upload' );

		$order = new Sejoli_Import_Data\Admin\Order( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/import-order/user-data',	$order, 'get_user_data_when_import_order', 100, 2);
		$this->loader->add_action( 'sejoli/import-order/do', $order, 'do_import_order', 999);
		$this->loader->add_action( 'wp_ajax_sejoli_create_order_data', $order, 'sejoli_create_order_data', 999);
		$this->loader->add_action( 'wp_ajax_nopriv_sejoli_create_order_data', $order, 'sejoli_create_order_data', 999);
		$this->loader->add_action( 'wp_ajax_sejoli_import_order_data', $order, 'sejoli_import_order_data', 999);
		$this->loader->add_action( 'wp_ajax_nopriv_sejoli_import_order_data', $order, 'sejoli_import_order_data', 999);
		$this->loader->add_action( 'sejoli/import-user/register', $order, 'register', 100);

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
	 * @return    Sejoli_Import_Data_Loader    Orchestrates the hooks of the plugin.
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
