<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Import_Data
 * @subpackage Sejoli_Import_Data/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <h1><?php _e('Import Data', 'sejoli-import-data'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="#tab-1" class="nav-tab nav-tab-active" id="tab-1-nav"><?php _e('Form Penjualan (Produk Digital)', 'sejoli-import-data'); ?></a>
        <a href="#tab-2" class="nav-tab" id="tab-2-nav"><?php _e('Import Penjualan (Produk Digital)', 'sejoli-import-data'); ?></a>
    </h2>
    <div id="tab-1-content" class="tab-content">
        <h2><?php _e('Input Data Penjualan', 'sejoli-import-data'); ?></h2>
        <form method="post" id="input_order_data">
            <div class="form-group">
                <label for="user"><?php _e('User', 'sejoli-import-data'); ?></label></br>
                <select name="user_id" id="user-id" class="user-data" style="width: 50%;">
                    <option value=""><?php _e('Select a user', 'sejoli-import-data'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="affiliate"><?php _e('Affiliate', 'sejoli-import-data'); ?></label></br>
                <select name="aff_id" id="affiliate-id" class="affiliate-data" style="width: 50%;">
                    <option value=""><?php _e('Select a affiliate', 'sejoli-import-data'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="product"><?php _e('Product', 'sejoli-import-data'); ?></label></br>
                <select name="product_id" id="product-id" class="product-data" style="width: 50%;">
                    <option value=""><?php _e('Select a product', 'sejoli-import-data'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="coupon"><?php _e('Coupon', 'sejoli-import-data'); ?></label></br>
                <select name="coupon" id="coupon" class="coupon-data" style="width: 50%;">
                    <option value=""><?php _e('Select a coupon', 'sejoli-import-data'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity"><?php _e('Quantity', 'sejoli-import-data'); ?></label></br>
                <input type="number" name="quantity" id="quantity" class="quantity-data" value="" style="width: 50%;"/>
            </div>
            <div class="form-group optional-fields">
                <label for="username"><?php _e('Username', 'sejoli-import-data'); ?></label></br>
                <input type="text" name="user_name" id="username" class="username-data" value="" style="width: 50%;"/>
            </div>
            <div class="form-group optional-fields">
                <label for="useremail"><?php _e('Email', 'sejoli-import-data'); ?></label></br>
                <input type="text" name="user_email" id="useremail" class="useremail-data" value="" style="width: 50%;"/>
            </div>
            <div class="form-group optional-fields">
                <label for="userphone"><?php _e('Phone Number', 'sejoli-import-data'); ?></label></br>
                <input type="text" name="user_phone" id="userphone-data" class="userphone-data" value="" style="width: 50%;"/>
            </div>
            <div class="form-group optional-fields">
                <label for="userpassword"><?php _e('Password', 'sejoli-import-data'); ?></label></br>
                <input type="password" name="user_password" id="userpassword-data" class="userpassword-data" value="" style="width: 50%;"/>
            </div>
            <!-- <div class="form-group">
                <label for="postalcode"><?php _e('Postal Code', 'sejoli-import-data'); ?></label></br>
                <input type="number" name="postalcode" id="postalcode-data" class="postalcode-data" value="" style="width: 50%;"/>
            </div> -->
            <!-- <div class="form-group">
                <label for="payment"><?php _e('Payment Method', 'sejoli-import-data'); ?></label></br>
                <select name="payment" id="payment-data" class="payment-data" style="width: 50%;">
                    <option value=""><?php _e('Select a payment method', 'sejoli-import-data'); ?></option>
                </select>
            </div> -->
            </br>
            <button type="submit" class="button button-primary"><?php _e('Create Data', 'sejoli-import-data'); ?></button>
        </form>

        <div id="form-message" class="alert" style="display:none; margin-top:2em;"></div>
    </div>

    <div id="tab-2-content" class="tab-content" style="display:none;">
        <h2><?php _e('Import Data Penjualan', 'sejoli-import-data'); ?></h2>
        <form method="post" id="import_order_data" enctype="multipart/form-data">
            <input type="file" name="import_order_file" id="import_order_file" />
            <p><?php _e('Upload data penjualan (.csv)', 'sejoli-import-data'); ?></p>

            <button type="submit" class="button button-primary"><?php _e('Import Data', 'sejoli-import-data'); ?></button>
        </form>
        
        <div id="form-message-import" class="alert" style="display:none; margin-top:2em;"></div>
    </div>
</div>