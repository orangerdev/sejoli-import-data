<?php

namespace Sejoli_Import_Data;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Import_Data
 * @subpackage Sejoli_Import_Data/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_Import_Data
 * @subpackage Sejoli_Import_Data/admin
 * @author     Sejoli Team <dev@sejoli.co.id>
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sejoli_Import_Data_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sejoli_Import_Data_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sejoli-import-data-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sejoli_Import_Data_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sejoli_Import_Data_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'sejoli-import-data-admin', plugin_dir_url( __FILE__ ) . 'js/sejoli-import-data-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script('sejoli-import-data-admin', 'sejoli_import', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => [
                    'submit_order' => wp_create_nonce('sejoli-checkout-ajax-submit-order'),
                    'import_order' => wp_create_nonce('sejoli-checkout-ajax-submit-import-order'),
                ]
            ]
        );

	}

	/* *
	 * Add Import Data menu
	 * Hooked via action admin_menu, 2999
	 * @since 	1.0.0
	 */
	public function add_admin_menu() {

		add_submenu_page( 'crb_carbon_fields_container_sejoli.php', __('Import Data', 'sejoli-import-data'), __('Import Data', 'sejoli-import-data'), 'manage_sejoli_licenses', 'sejoli-import-data', [$this, 'display_page_data']);

	}

	/**
	 * Display import data admin
	 * Called internally via add_menu_page
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_page_data() {

		ob_start();

		require_once( plugin_dir_path( __FILE__ ) . '/partials/import-data.php' );

		$import_data =  ob_get_clean();

		echo $import_data;

	}

	/**
	 * Get user data with select2 ajax
	 * Hooked via action wp_ajax_get_users_select2, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function get_users_select2_ajax() {

	    $search_query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

	    // Get userss
	    $args = array(
	        'search' => '*' . esc_attr($search_query) . '*',
	        'search_columns' => array('user_login', 'user_nicename', 'user_email', 'display_name'),
	        'number' => 10 // Limit the number of users
	    );
	    $user_query = new \WP_User_Query($args);

	    $users = $user_query->get_results();
	    $results = array();

	    foreach ($users as $user) :
	        
	        $results[] = array(
	            'id' => $user->ID,
	            'text' => $user->user_email .' ('.$user->display_name.')'
	        );

	    endforeach;

	    wp_send_json($results);

	}

	/**
	 * Get product data with select2 ajax
	 * Hooked via action wp_ajax_get_products_select2, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function get_products_select2_ajax() {

	    $search_query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
	    $product_limit = 10;

	    $query_args = array(
	        's'                      => $search_query,
	        'post_type'              => 'sejoli-product',
	        'posts_per_page'         => $product_limit,
	        'post_status'            => 'publish',
	        'meta_query'             => array(
		        array(
		            'key'     => '_product_type',
		            'value'   => 'digital',
		            'compare' => '=',
		        ),
		    ),
	        'no_found_rows'          => true,
	        'update_post_meta_cache' => false,
	        'update_post_term_cache' => false
	    );

	    $products_query = new \WP_Query($query_args);
	    $products = $products_query->posts;

	    $results = array();

	    foreach ($products as $product) :

	        $results[] = array(
	            'id'   => $product->ID,
	            'text' => $product->post_title
	        );

	    endforeach;

	    wp_send_json($results);

	}

	/**
	 * Get coupon data with select2 ajax
	 * Hooked via action wp_ajax_get_coupon_select2, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function get_coupon_select2_ajax() {

		$coupon_code = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

		$responds = sejolisa_get_coupon_by_code($coupon_code);

		$results = array();

		if(false !== $responds['valid']) :

	        $coupon = $responds['coupon'];

	        if(!in_array($coupon['status'], ['pending', 'need-approve'])) :

	        	$results[] = array(
		            'id' => $coupon['code'],
		            'text' => $coupon['code']
		        );

	        else :

	            $results[] = array();

	        endif;

	    endif;

        wp_send_json($results);

    }

    /**
	 * Get payment data with select2 ajax
	 * Hooked via action wp_ajax_get_coupon_select2, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function get_payment_select2_ajax() {

		$coupon_code = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

		$payment_gateway = [];

        $payment_options = sejolisa_get_payment_options();

		$product_id = isset($request['product_id']) ? $request['product_id'] : null;
		
        $display_text_payment_channel = boolval(sejolisa_carbon_get_post_meta($product_id, 'display_text_payment_channel'));

        foreach ( $payment_options as $key => $value ) :

        	if( \str_contains( strtolower( $key ), 'moota' ) || \str_contains( strtolower( $key ), 'duitku' ) ) {
        		$label_check = __('(dicek otomatis)', 'sejoli');
        	} else {
        		$label_check = '';
        	}

            $payment_gateway[] = [
                'id' => $key,
                'title' => $value['label'],
                'image' => $value['image'],
                'display_payment' => $display_text_payment_channel,
                'label_check' => $label_check
            ];

        endforeach;

        return $payment_gateway;

        wp_send_json($payment_gateway);

    }

	public function handle_file_upload() {

	    if (isset($_FILES['import_file']) && !empty($_FILES['import_file']['name'])) {
	        $uploadedfile = $_FILES['import_file'];
	        $upload_overrides = array('test_form' => false);

	        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

	        if ($movefile && !isset($movefile['error'])) {
	            echo "File is valid, and was successfully uploaded.\n";
	            // Process the uploaded CSV file.
	        } else {
	            echo $movefile['error'];
	        }
	    }

	}

}
