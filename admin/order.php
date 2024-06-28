<?php

namespace Sejoli_Import_Data\Admin;

class Order {

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
        $this->version     = $version;

    }

    /**
     * Check user email, if product is physical then will create dummy email address
     * @since   1.0.0
     * @param   array   $user_data
     * @return  string
     */
    protected function check_user_email($user_data) {

        $email = $user_data['user_email'];

        if(
            empty($email) &&
            !empty($user_data['product_id']) &&
            is_sejolisa_product_physical($user_data['product_id'])
        ) :

            $email = sejolisa_get_email_domain( $user_data['user_phone'] );

        endif;

        return $email;

    }

    /**
     * Register user
     * Hooked via action sejoli/import-user/register, priority 100
     * @since   1.0.0
     * @param   array  $user_data Array of user data
     * @return  void
     */
    public function register(array $user_data) {

        $user_data = wp_parse_args($user_data,[
            'user_email'      => NULL,
            'user_name'       => NULL,
            'user_password'   => NULL,
            'user_phone'      => NULL,
            'product_id'      => NULL,
        ]);

        $user_data['user_email'] = $this->check_user_email($user_data);

        if( empty($user_data['user_password']) ) :

            $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!!!';
            $charactersLength = strlen($characters);
            $password         = '';

            for ($i = 0; $i < 8; $i++) :
                $password .= $characters[rand(0, $charactersLength - 1)];
            endfor;

            $user_data['user_password'] = $password;

        endif;

        $user_id = wp_insert_user([
            'user_login'   => sanitize_user($user_data['user_email']),
            'user_email'   => $user_data['user_email'],
            'display_name' => $user_data['user_name'],
            'first_name'   => $user_data['user_name'],
            'user_pass'    => $user_data['user_password'],
            'role'         => 'sejoli-member',
        ]);

        if(!is_wp_error($user_id)) :

            update_user_meta($user_id, '_phone', $user_data['user_phone']);

            $affiliate_id = $user_data['affiliate_id'];

            if(!empty($affiliate_id)) :

                update_user_meta($user_id, sejolisa_get_affiliate_key(), intval($affiliate_id));

            endif;

            do_action('sejoli/notification/registration', $user_data);

        endif;

    }

    /**
     * Get user by post data when import order
     * Hooked via filter sejoli/import-order/user-data, priority 100
     * @since   1.0.0
     * @param   bool|WP_User    $user_data
     * @param   array           $post_data
     * @return  bool|WP_User
     */
    public function get_user_data_when_import_order($user_data, array $post_data) {
 
        if(!is_a($user_data, 'WP_User')) :

            if ( !empty($post_data['user_id'])) :

                $user_data = sejolisa_get_user(intval($post_data['user_id']));

            else :

                // If product is physical
                $post_data['user_email'] = $this->check_user_email($post_data);

                $user_data = sejolisa_get_user($post_data['user_email']);

                if(!is_a($user_data, 'WP_User')) :

                    $user_data = sejolisa_get_user($post_data['user_phone']);

                endif;

            endif;

        endif;

        if(isset($user_data->meta->affiliate) && !empty($user_data->meta->affiliate)) :

            do_action('sejoli/checkout/affiliate/set', $user_data->meta->affiliate, 'user_meta');

        endif;

        return $user_data;

    }

    /**
     * Checkout action. there are validations
     * - validate product
     * - validate coupon
     * - validate user
     * Hooked via action sejoli/import-order/do
     * @since   1.0.0
     * @since   1.4.0   Add $post_data into sejoli/checkout/is-product-valid
     * @param   array  $args
     * @return  void
     */
    public function do_import_order( array $post_data ) {

        $enable_register = $valid = true;

        $post_data = wp_parse_args($post_data, [
            'user_id'            => NULL,
            'affiliate_id'       => NULL,
            'coupon'             => NULL,
            'payment_gateway'    => 'manual',
            'quantity'           => 1,
            'user_email'         => NULL,
            'user_name'          => NULL,
            'user_password'      => NULL,
            'postal_code'        => NULL,
            'user_phone'         => NULL,
            'shipment'           => NULL,
            'markup_price'       => NULL,
            'shipping_own_value' => NULL,
            'product_id'         => NULL,
            'meta_data'          => [],
            'address'            => NULL,
            'variants'           => NULL,
            'wallet'             => NULL,
        ]);

        error_log(print_r("post_data", true));
        error_log(print_r($post_data, true));

        $product = sejolisa_get_product( $post_data['product_id'] );

        if( is_a( $product, 'WP_Post' ) ) :

            // validate product
            $valid = apply_filters( 'sejoli/checkout/is-product-valid', $valid, $product, $post_data );

            // get user data by checkout data
            // if the value is in valid, then later need to register
            $user_data = apply_filters( 'sejoli/import-order/user-data', false, $post_data );

            // validate shipping
            $valid = apply_filters( 'sejoli/checkout/is-shipping-valid', $valid, $product, $post_data );

            // validate coupon
            $valid = apply_filters( 'sejoli/checkout/is-coupon-valid', $valid, $product, $post_data, 'checkout' );

            // validate variant
            $valid = apply_filters( 'sejoli/variant/are-variants-valid', $valid, $post_data );

            $password_field = boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_password_field'));

            if ( is_user_logged_in() && false === $user_data ) :
                
                $valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
                
            elseif ( !is_user_logged_in() && false !== $password_field && false === $user_data ) :
               
                $valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
                    
            endif;

            if( true === $valid ) :

                $order_data = [
                    'product_id'         => $product->ID,
                    'quantity'           => $post_data['quantity'],
                    'payment_gateway'    => $post_data['payment_gateway'],
                    'meta_data'          => $post_data['meta_data'],
                    'coupon'             => $post_data['coupon'],
                    'shipment'           => $post_data['shipment'],
                    'markup_price'       => $post_data['markup_price'],
                    'shipping_own_value' => $post_data['shipping_own_value'],
                    'wallet'             => $post_data['wallet']
                ];

                if (!isset($post_data['coupon']) && empty($post_data['coupon'])) {
                    $order_data['coupon_id'] = NULL; // Atur coupon ke NULL jika kolom kosong
                }

                if (empty($post_data['affiliate_id'])) {
                    $order_data['affiliate_id'] = NULL; // Atur affiliate_id ke NULL jika kolom kosong
                }

                error_log(print_r("order_data", true));
                error_log(print_r($order_data, true));

                // affiliate link simulation
                if( !empty( $post_data['affiliate_id'] ) ) :

                    do_action( 'sejoli/checkout/affiliate/set', $post_data['affiliate_id'], 'link' );

                else :

                    do_action( 'sejoli/checkout/check-cookie', $post_data );
                
                endif;

                do_action( 'sejoli/order/set-affiliate', $post_data );

                // user is not registered
                if(false === $user_data) :

                    if( $product->type === "physical" ) :

                        do_action('sejoli/import-user/register', $post_data);
                        $user_data = sejolisa_get_user($post_data['user_phone']); // user phone

                    else:

                        do_action('sejoli/import-user/register', $post_data);
                        $user_data = sejolisa_get_user($post_data['user_email']); // user email

                    endif;

                endif;

                $order_data['user_id'] = $user_data->ID;

                sejolisa_set_respond([
                    'valid' => true,
                ], 'checkout');

                do_action( 'sejoli/log/write', 'order create', $order_data );
                do_action( 'sejoli/order/create', $order_data );

            else :
    
                sejolisa_set_respond([
                    'valid' => false,
                    'messages' => [
                        'error' => sejolisa_get_messages()
                    ]
                ], 'checkout');
        
            endif;

        else :
        
            sejolisa_set_respond([
                'valid' => false,
                'messages' => [
                    'error' => [
                        __('Produk tidak valid', 'sejoli-import-data')
                    ]
                ]
            ], 'checkout');
        
        endif;
    
    }

    /**
     * Create order data request
     * @param   $data data from api request
     * @return  array|WP_Error
     * @since   1.0.0
     */
    public function sejoli_create_order_data() {

        $request = NULL;

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sejoli-checkout-ajax-submit-order')) :

            wp_send_json_error(array('message' => __('Invalid nonce', 'sejoli-import-data')));

            return;

        endif;

        $request = wp_parse_args($_POST,[
            'user_id'         => NULL,
            'affiliate_id'    => isset($_POST['aff_id']) ? $_POST['aff_id'] : NULL,
            'product_id'      => 0,
            'coupon'          => NULL,
            'quantity'        => 1,
            'payment_gateway' => 'manual',
            'user_name'       => NULL,
            'user_email'      => NULL,
            'user_phone'      => NULL,
            'user_password'   => NULL
        ]);

        if(!empty($_POST['quantity'])):
            $request['quantity'] = $_POST['quantity'];
        else:
            $request['quantity'] = 1;
        endif;

        error_log(print_r($request, true));

        if(is_array($request) && $request['product_id'] > 0) :

            do_action( 'sejoli/import-order/do', $request );

            $order    = sejolisa_get_respond('order');
            $checkout = sejolisa_get_respond('checkout');

            if(false === $checkout['valid']) :

                $response = [
                    'valid'    => false,
                    'messages' => $checkout['messages']['error']
                ];

                wp_send_json_error(array('message' => $response['messages']));

            elseif(false == $order['valid']) :

                $response = [
                    'valid'    => false,
                    'messages' => $order['messages']['error']
                ];

                wp_send_json_error(array('message' => $response['messages']));

            else:

                $d_order = $order['order'];

                $messages = [sprintf( __('Order created successfully. Order ID #%s', 'sejoli-import-data'), $d_order['ID'] )];

                if(0 < count($order['messages']['warning'])) :

                    foreach($order['messages']['warning'] as $message) :

                        $messages[] = $message;

                    endforeach;

                endif;

                if(0 < count($order['messages']['info'])) :

                    foreach($order['messages']['info'] as $message) :

                        $messages[] = $message;
                        
                    endforeach;

                endif;

                $response = [
                    'valid'         => true,
                    'messages'      => $messages,
                    'redirect_link' => site_url('checkout/loading?order_id='.$d_order['ID']),
                    'data'          => [
                        'order' => $d_order
                    ]
                ];

            endif;

            error_log(print_r($response, true));
            wp_send_json($response);
        
        else:

            $messages = [sprintf( __('Order created failed, no product found!.', 'sejoli-import-data') )];

            $response = [
                'valid'    => false,
                'messages' => $messages
            ];

            wp_send_json($response);

        endif;

    }

    protected function get_csv_chunk($file_handle, $chunk_size) {

        $chunk = [];
        $counter = 0;

        while (($row = fgetcsv($file_handle, 1000, ',')) !== false) {

            $chunk[] = $row;
            $counter++;

            if ($counter >= $chunk_size) {
                break;
            }

        }

        if (empty($chunk)) {
            return false;
        }

        return $chunk;

    }

    /**
     * Import order data request
     * @param   $data data from api request
     * @return  array|WP_Error
     * @since   1.0.0
     */
    public function sejoli_import_order_data() {

        $request = NULL;

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sejoli-checkout-ajax-submit-import-order')) :

            wp_send_json_error(array('message' => __('Invalid nonce', 'sejoli-import-data')));

            return;

        endif;

        if (empty($_FILES['import_order_file']['name'])) :

            $messages = [sprintf( __('Order import failed, no csv found!.', 'sejoli-import-data') )];

            $response = [
                'valid'    => false,
                'messages' => $messages
            ];

            wp_send_json($response);

            return;

        endif;

        $file = $_FILES['import_order_file'];
        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) :

            $response = [
                'valid'    => false,
                'messages' => $upload['error']
            ];

            wp_send_json($response);

            return;

        endif;

        $file_path = $upload['file'];

        // Process the CSV in chunks
        $chunk_size  = 100; // Adjust the chunk size as needed
        $file_handle = fopen($file_path, 'r');
        
        if ($file_handle === false) :

            $response = [
                'valid'    => false,
                'messages' => 'Error opening file'
            ];

            wp_send_json($response);

            return;

        endif;

        $header = fgetcsv($file_handle); // Assuming the first row is the header
        $rows_processed = 0;
        $successful_orders = 0;
        $failed_orders = [];

        while (($chunk = $this->get_csv_chunk($file_handle, $chunk_size)) !== false) :

            foreach ($chunk as $row) :

                $request = array(
                    'user_id'         => isset($row[0]) ? $row[0] : NULL,
                    'affiliate_id'    => isset($row[1]) ? $row[1] : NULL,
                    'product_id'      => isset($row[2]) ? $row[2] : 0,
                    'coupon'          => NULL,
                    'quantity'        => isset($row[4]) && !empty($row[4]) ? $row[4] : 1,
                    'payment_gateway' => 'manual',
                    'user_name'       => isset($row[5]) ? $row[5] : NULL,
                    'user_email'      => isset($row[6]) ? $row[6] : NULL,
                    'user_phone'      => isset($row[7]) ? $row[7] : NULL,
                    'user_password'   => isset($row[8]) ? $row[8] : NULL
                );

                if (isset($row[3]) && !empty($row[3])) {
                    $request['coupon'] = $row[3];
                } else {
                    $request['coupon'] = NULL; // Atur coupon ke NULL jika kolom kosong
                }

                // if(!empty($request['affiliate_id'])):
                //     $request['affiliate_id'] = $row[1];
                // else:
                //     $request['affiliate_id'] = NULL;
                // endif;

                // error_log(print_r($row, true));

                error_log(print_r("UPDT", true));
                error_log(print_r($request, true));

                if (is_array($request) && $request['product_id'] > 0) {
                    do_action('sejoli/import-order/do', $request);

                    $order = sejolisa_get_respond('order');
                    $checkout = sejolisa_get_respond('checkout');

                    // if (false === $checkout['valid']) {
                    //     $failed_orders[] = $checkout['messages']['error'];
                    // } elseif (false == $order['valid']) {
                    //     $failed_orders[] = $order['messages']['error'];
                    // } else {
                        $successful_orders++;
                    // }
                } else {
                    $failed_orders[] = __('Order creation failed, no product found.', 'sejoli-import-data');
                }

                // Process each row here
                // $row is an array of the CSV columns
            endforeach;

            $rows_processed += count($chunk);
            // $rows_processed++;

        endwhile;

        fclose($file_handle);

        if ($successful_orders > 0) {
            $messages = [sprintf(__('Successfully created %d orders.', 'sejoli-import-data'), $successful_orders)];
            if (!empty($failed_orders)) {
                $messages[] = sprintf(__('Failed to create %d orders. Errors: %s', 'sejoli-import-data'), count($failed_orders), implode(', ', $failed_orders));
            }

            $response = [
                'valid'    => true,
                'messages' => $messages
            ];

            wp_send_json($response);
        } else {
            $messages = [sprintf( __('No orders were created.', 'sejoli-import-data') )];

            $response = [
                'valid'    => false,
                'messages' => $messages
            ];

            wp_send_json($response);
        }

    }

}