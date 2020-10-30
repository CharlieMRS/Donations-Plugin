<?php
/*
Plugin Name: Tallgrass Woocommerce Single Page Checkout
Description: Get it all happening on one page. Be sure to add [product_on_checkout_page] to the checkout page.
Dependencies: Paypal Braintree gateway, Woocommerce Add to cart Ajax for variable products by Rishi Mehta - Rcreators Websolutions
Author: C. Meers
Version: 1.1.1

*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Check if WooCommerce is active
 **/
if (
        in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
        || array_key_exists('woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins'))
) {

    function tallgrass_woo_enqueue_scripts()
    {
        wp_enqueue_style('tg-woo-styles', plugins_url('/css/style.css', __FILE__), array(), '1.2.4');
        wp_enqueue_script('select2OptionPicker', plugins_url('/js/vendor/select-to-option-picker.js', __FILE__), array('jquery'), '1.2.2', true);
        wp_enqueue_script('thisser-scripts', plugins_url('/js/update-variation-on-checkout.js', __FILE__), false, '1.2.4', true);
        wp_enqueue_script('thisser-loo-scripts', plugins_url('/js/toggle-variation-forms.js', __FILE__), array('woocommerce-nyp'), '1.2.2', true);
    }

    add_action('wp_enqueue_scripts', 'tallgrass_woo_enqueue_scripts');
    add_action('woocommerce_thankyou', 'tg_hide_elements');

    function tg_hide_elements()
    {
        wp_enqueue_script('tg-hide-scripts', plugins_url('/js/hide-after-submit.js', __FILE__), array(), false, true);
    }

    add_shortcode('product_on_checkout_page', 'product_on_checkout_page_func');
    function product_on_checkout_page_func()
    {
        ob_start(); ?>
        <div id="subscribeDonationWrapper">
            <label>
                <input type="checkbox" id="subscribeDonation" name="subscribeDonation" value="subscribeDonation"
                    <?php echo (get_class(array_values(WC()->cart->cart_contents)[0]['data']) != "WC_Product_Variation") ? 'checked' : ''; ?>
                >
                Yes, automatically repeat this donation every month.
            </label>
        </div>

        <?php

        $wp_query = new WP_Query([
            'p' => [238, 79],
            'post_type' => 'product'
        ]);
        if ($wp_query->have_posts()) :
            while ($wp_query->have_posts()) :
                $wp_query->the_post();

                global $woocommerce;
                remove_action('woocommerce_single_product_summary', [$woocommerce->structured_data, 'generate_product_data'], 60);
                $id = get_the_id();
                echo "<div id='var-form-" . $id . "'>";
                do_action('woocommerce_single_product_summary');
                echo "</div>";
            endwhile;

        else: ?>
            <p><?php _e('No Product'); ?></p>
        <?php endif; ?>

        <form id="dollyRadioWrapper">
            <fieldset>
                <legend>Use my donation for:</legend>
                <input type="radio" name="dollyRadio" id="dno" value="0" checked>
                <label for="dno">General Fund</label>
                <input type="radio" name="dollyRadio" id="dyes" value="1">
                <label for="dyes"><?php echo do_shortcode('[tooltip text="The Dolly Parton Imagination Library provides 1 book per month by mail to children ages 0-5. You can help our children&#39s success in school and in life by supporting the continued work of the Dolly Parton Imagination Library in our community."]The Dolly Parton Imagination Library[/tooltip]'); ?> </label>
            </fieldset>
        </form>
        <?php return ob_get_clean();
    }


    add_filter('woocommerce_thankyou_order_received_text', 'tg_thankyou_text');
    function tg_thankyou_text($order)
    {
        $order = 'Donation successfully submitted. Thank you for your generous gift.';
        return $order;
    }

    function tallgrass_enqueue_update_checkout_ajax_script()
    {
        wp_enqueue_script('thisser-scripts', get_stylesheet_directory_uri() . '/update-order-on-checkout.js', array('jquery'), false, true);
    }

    //this wouldn't work because the ajax call was firing before the cart was actually getting updated. i couldn't figure out the callback system in jQuery It's not hooked to
    //woocommerce_add_cart_item_data which gets called during the original ajax call to update the cart

    //add_action( 'wp_loaded', 'tallgrass_enqueue_update_checkout_ajax_script' );

    //because checkout form won't appear if nothing in cart
    add_action('wp_loaded', 'tg_populate_cart');

    function tg_populate_cart()
    {
        if (!is_admin()):
            if (WC()->cart->is_empty()) {
                WC()->cart->add_to_cart(238, 1, 239);
            }

        endif;
    }

    add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_product_add_to_cart_text');  // 2.1 +

    function woo_custom_product_add_to_cart_text()
    {
        return "Update";
    }

    //prevent mulitple donation cart items
    add_filter('woocommerce_add_cart_item_data', 'woo_custom_add_to_cart');

    function woo_custom_add_to_cart($cart_item_data)
    {

        global $woocommerce;
        $woocommerce->cart->empty_cart();

        // Do nothing with the data and return
        return $cart_item_data;
    }

    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);

    // Hook in
    add_filter('woocommerce_checkout_fields', 'tg_override_checkout_fields');

    // Our hooked in function - $fields is passed via the filter!
    function tg_override_checkout_fields($fields)
    {
        unset($fields['order']['order_comments']);

        $fields['billing']['honorary_gift'] = array(
            'label' => __("Give in Someone's Honor", 'woocommerce'),
            'placeholder' => _x("Person's Name", 'placeholder', 'woocommerce'),
            'required' => false,
            'class' => array('form-row-wide'),
            'clear' => true
        );
        $fields['billing']['dolly'] = array(
            'type' => 'select',
            'label' => __("Use my donation for:", 'woocommerce'),
            'required' => false,
            'class' => array('form-row-wide'),
            'clear' => true,
            'options' => ['General Fund', 'The Dolly Parton Imagination Library']
        );

        return $fields;
    }

    /**
     * Display field value on the order edit page
     */

    add_action('woocommerce_admin_order_data_after_shipping_address', 'tg_checkout_field_display_admin_order_meta', 10, 1);

    function tg_checkout_field_display_admin_order_meta($order)
    {

        $dolly = get_post_meta($order->get_id(), 'dolly', true) == 1 ? 'yes' : 'no';

        echo '<p><strong>' . __('Honorary Gift') . ':</strong> ' . get_post_meta($order->get_id(), 'honorary_gift', true) . '</p>';
        echo '<p><strong>' . __('The Dolly Parton Imagination Library?') . ':</strong> ' . $dolly . '</p>';
    }

    // save fields to order meta
    add_action('woocommerce_checkout_update_order_meta', 'tg_save_what_we_added');

    function tg_save_what_we_added($order_id)
    {
        if (!empty($_POST['honorary_gift']))
            update_post_meta($order_id, 'honorary_gift', sanitize_text_field($_POST['honorary_gift']));

        if (!empty($_POST['dolly']))
            update_post_meta($order_id, 'dolly', $_POST['dolly']);
    }

    add_filter('woocommerce_order_button_text', 'woo_custom_order_button_text');

    function woo_custom_order_button_text()
    {
        return __('Donate Now', 'woocommerce');
    }

    function crispshop_add_cart_single_ajax()
    {
        $product_id = 79;
        $variation_id = 35;
        $quantity = 1;

        if ($variation_id) {
            WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        } else {
            WC()->cart->add_to_cart($product_id, $quantity);
        }
    }

    add_action('woocommerce_add_to_cart', 'crispshop_add_cart_single_ajax');

    add_shortcode('tg_woo_debug', 'tg_woo_debug_func');
    function tg_woo_debug_func()
    {

        ob_start();

        echo '<pre>';
        print_r(get_class(array_values(WC()->cart->cart_contents)[0]['data']));
        echo '</pre>';

        return ob_get_clean();
    }

    /**
     * Set a custom add to cart URL to redirect to
     * on my variable product add to cart i was getting redirected to product page, must stay on checkout page or billing stuff fails
     * @return string
     */
    function custom_add_to_cart_redirect()
    {
        return 'https://dcsb6.org/checkout/';
    }

    add_filter('woocommerce_add_to_cart_redirect', 'custom_add_to_cart_redirect');
    add_filter('woocommerce_cart_redirect_after_error', 'custom_add_to_cart_redirect');


    add_action('woocommerce_after_add_to_cart_button', 'tg_add_content_after_addtocart_button_func');

    function tg_add_content_after_addtocart_button_func()
    {
        echo "<p class='submit-reminder' style='font-size: .8em;'>Important: Please click 'Update' button before proceeding to checkout form below.</p>";
    }

}


