<?php
/**
Plugin Name: Dentacoin Payment Gateway for WooCommerce
Description: Dentacoin (DCN) is an Ethereum crypto token, allowing for easy and secure payments of dental or other services and products.
Version: 1.0.0
Author: Dentacoin Foundation
Author URI: https://dentacoin.com
*/

function wcdp_enqueue_styles_and_scripts() {
    wp_localize_script( 'wcdp_ajax_script', 'MyAjax',
        array(
            // URL to wp-admin/admin-ajax.php to process the request
            'ajaxurl'          => admin_url( 'admin-ajax.php' ),

            // generate a nonce with a unique ID "myajax-post-comment-nonce"
            // so that you can check it later when an AJAX request is sent
            'postCommentNonce' => wp_create_nonce( 'myajax-post-comment-nonce' ),
        )
    );
}
add_action('wp_enqueue_scripts', 'wcdp_enqueue_styles_and_scripts', 1);

add_action( 'after_setup_theme', 'wcdp_woocommerce_support' );
function wcdp_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}


/* WooCommerce fallback notice. */
function wcdp_fallback_notice() {
    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Custom Payment Gateway depends on the last version of %s to work!', 'wcdp_domain' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
}

/* Load functions. */
function wcdp_load() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'wcdp_fallback_notice' );
        return;
    }

    function wc_Custom_add_gateway( $methods ) {
        $methods[] = 'WC_Dentacoin_Payment_Gateway';
        return $methods;
    }
    add_filter( 'woocommerce_payment_gateways', 'wc_Custom_add_gateway' );

    // Include the WooCommerce Custom Payment Gateway classes.
    require_once plugin_dir_path( __FILE__ ) . 'class-wc-custom_payment_gateway.php';
}

add_action('plugins_loaded', 'wcdp_load', 0);



/* Adds custom settings url in plugins page. */
function wcdp_action_links( $links ) {
    $settings = array(
		'settings' => sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wcdp' ),
		__( 'Payment Gateway', 'wcdp_domain' )
		)
    );

    return array_merge( $settings, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcdp_action_links' );

function wcdp_alter_shipping ($order) {
    if ($order->get_payment_method() == 'wcdp') {
        return $order;
    } else {
        return $order;
    }
}
add_filter( 'woocommerce_checkout_create_order', 'wcdp_alter_shipping', 10, 1 );

function wcdp_order_button_text($order_button_text) {
    $default = __( 'Place order', 'woocommerce' ); // If needed
    // Get the chosen payment gateway (dynamically)
    $chosen_payment_method = WC()->session->get('chosen_payment_method');

    /*if ( $chosen_payment_method == 'wcdp'){
        // HERE set your custom button text
        $order_button_text = __( 'Generate QR code.', 'woocommerce' );
    }*/


    return $order_button_text;
}
add_filter('woocommerce_order_button_text', 'wcdp_order_button_text', 10, 1);

function wcdp_on_checkout_form_load() {
    // jQuery code: Make dynamic text button "on change" event ?>
    <script type="text/javascript">
        (function($) {
            var dontHaveWalletAddressYet = '<span class="explainer-how-to-get-dcn-wallet" style="padding-top: 10px; display: inline-block;">Don\'t have a Dentacoin wallet yet? Create one at <a href="https://wallet.dentacoin.com/" target="_blank">https://wallet.dentacoin.com/</a>. or download Dentacoin Wallet app for <a href="https://apps.apple.com/us/app/dentacoin-wallet/id1478732657" target="_blank">iOS</a> or <a href="https://play.google.com/store/apps/details?id=wallet.dentacoin.com" target="_blank">Android</a>.</span>';

            // if wallet field exists on the checkout page
            if (jQuery('.billing-details-wallet-address').length) {
                jQuery('.billing-details-wallet-address').append(dontHaveWalletAddressYet);
            }

            <?php
            $walletAddressHtml = '<p class="form-row billing-details-wallet-address validate-required" id="wallet_address_field" data-priority=""><label for="wallet_address" class="">Wallet Address&nbsp;<abbr class="required" title="required">*</abbr></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="wallet_address" id="wallet_address" placeholder="0x..." value="" maxlength="42"></span></p>';
            ?>

            $('form.checkout').on('change', 'input[name^="payment_method"]', function() {
                $(document.body).trigger('update_checkout');
                $('.woocommerce-billing-fields__field-wrapper .billing-details-wallet-address').remove();
                if ($(this).val() == 'wcdp') {
                    $('.woocommerce-billing-fields__field-wrapper').append('<?php echo $walletAddressHtml; ?>');

                    if (jQuery('.billing-details-wallet-address').length) {
                        jQuery('.billing-details-wallet-address').append(dontHaveWalletAddressYet);
                    }
                }
            });
        })(jQuery);
    </script>
    <?php
}
add_filter('woocommerce_after_checkout_form', 'wcdp_on_checkout_form_load' );

// hide this on production
function wcdp_add_metabox_to_shop_orders()   {
    add_meta_box(
        'wcdp_meta',
        'Wallet address (sender)',
        'wcdp_shop_order_meta_callback',
        'shop_order',
        'normal',
        'core'
    );
}
add_action('add_meta_boxes', 'wcdp_add_metabox_to_shop_orders');

function wcdp_shop_order_meta_callback($post)    {
    wp_nonce_field( basename( __FILE__ ), 'wcdp_shop_orders_nonce' );
    $wcdp_stored_meta = get_post_meta( $post->ID );
    ?>
    <div>
        <div class="meta-row">
            <div class="meta-td">
                <input type="text" placeholder="0x..." step="any" name="wallet_address" id="wallet_address" style='width: 100%' value="<?php if ( ! empty ( $wcdp_stored_meta['wallet_address'] ) ) {
                    echo esc_attr( $wcdp_stored_meta['wallet_address'][0] );
                } ?>"/>
            </div>
        </div>
    </div>

    <?php
}

function wcdp_meta_save( $post_id ) {
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'wcdp_shop_orders_nonce' ] ) && wp_verify_nonce( $_POST[ 'wcdp_shop_orders_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
    
    if ( isset( $_POST[ 'wallet_address' ] ) ) {
        update_post_meta( $post_id, 'wallet_address', sanitize_text_field( trim($_POST[ 'wallet_address' ]) ) );
    }

    if ( isset( $_POST[ 'receiver_address' ] ) ) {
        update_post_meta( $post_id, 'receiver_address', sanitize_text_field( $_POST[ 'receiver_address' ] ) );
    }
    
    if ( isset( $_POST[ 'total_dcn_price' ] ) ) {
        update_post_meta( $post_id, 'total_dcn_price', sanitize_text_field( $_POST[ 'total_dcn_price' ] ) );
    }

    if ( isset( $_POST[ 'tx_hash' ] ) ) {
        update_post_meta( $post_id, 'tx_hash', sanitize_text_field( $_POST[ 'tx_hash' ] ) );
    }
}
add_action('save_post', 'wcdp_meta_save');

// add Wallet Address field to billing details form, only if payment method is Dentacoin
function wcdp_custom_woocommerce_billing_fields($fields) {
    $chosen_payment_method = WC()->session->get('chosen_payment_method');
    if ($chosen_payment_method == 'wcdp') {
        $fields['wallet_address'] = array(
            'label' => __('Wallet Address', 'wcdp_domain'), // Add custom field label
            'placeholder' => _x('0x...', 'placeholder', 'wcdp_domain'), // Add custom field placeholder
            'required' => true, // if field is required or not
            'clear' => false, // add clear or not
            'type' => 'text', // add field type
            'maxlength' => '42', // add field type
            'class' => array('billing-details-wallet-address')    // add class name
        );
    }
    return $fields;
}
add_filter('woocommerce_billing_fields', 'wcdp_custom_woocommerce_billing_fields');

// add QR code on the thank you page
function wcdp_add_content_thankyouadd_content_thankyou($order_id) {
    if (!empty($order_id)) {
        $order = wc_get_order($order_id);
        $payment_gateway_id = 'wcdp';
        // Get an instance of the WC_Payment_Gateways object
        $payment_gateways = WC_Payment_Gateways::instance();

        // Get the desired WC_Payment_Gateway object
        $dcn_payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];

        $currentTotal = get_post_meta($order->get_id(), 'total_dcn_price', true);
        if ($order->get_payment_method() == 'wcdp' && empty($currentTotal)) {
            $total = $order->get_total();
            if ($order->get_currency() == 'USD') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['USD']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'EUR') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['EUR']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'RUB') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['RUB']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'GBP') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['GBP']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'INR') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['INR']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'CNY') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['CNY']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'JPY') {
                $getDentacoinDataByExternalProvider = wcdp_getDentacoinDataByExternalProvider();
                $currentTotal = round(floatval($total) / $getDentacoinDataByExternalProvider['JPY']);
                update_post_meta($order->get_id(), 'total_dcn_price', $currentTotal);
            } else if ($order->get_currency() == 'DCN') {
                $currentTotal = $total;
                update_post_meta($order->get_id(), 'total_dcn_price', $total);
            }
        }

        $receiverAddress = get_post_meta($order->get_id(), 'receiver_address', true);
        if (property_exists($dcn_payment_gateway, 'settings') && !empty($dcn_payment_gateway->settings['walletAddress'])) {
            // save receiver address for this order
            if (empty($receiverAddress)) {
                update_post_meta($order->get_id(), 'receiver_address', $dcn_payment_gateway->settings['walletAddress']);
            }

            $timeframe = false;
            $timeframeDateTimestamp = 0;
            if (!empty($dcn_payment_gateway->settings['expireIn'])) {
                if (time() < strtotime($order->order_date) + $dcn_payment_gateway->settings['expireIn']) {
                    $timeframeDateTimestamp = strtotime($order->order_date) + $dcn_payment_gateway->settings['expireIn'];
                } else {
                    $timeframeDateTimestamp = 0;
                }
                $timeframe = true;
            }

            $timeframeHtml = '';
            if ($timeframe) {
                $timeframeHtml = __('<div class="wcdp-timeframe-timer-label" style="text-align: center; font-size: 18px;padding-top: 30px;font-weight: bold;">Time left to complete the DCN payment:</div><div id="wcdp-timeframe-timer" style="padding-top: 5px; padding-bottom: 15px; font-size: 24px; text-align: center;"></div>', 'wcdp_domain');
            }

            // show blockchain payment details
            $paymentDetails = __('<div class="wcdp-amount-to-pay" style="padding-top: 25px;text-align: center; padding-bottom: 10px;font-size: 16px;">Amount to pay: <b>'.$currentTotal.' DCN</b></div><div class="wcdp-receiving-wallet-address" style="text-align: center;word-break: break-all;font-size: 16px;">Receiving Wallet Address: <b>'.$dcn_payment_gateway->settings['walletAddress'].'</b></div>'.$timeframeHtml.'<div class="wcdp-qr-code-title" style="padding-top: 25px; text-align: center; font-size: 20px;font-weight: bold;">SCAN THIS QR CODE IN OUR DENTACOIN WALLET:</div><div class="wcdp-qr-code-container" style="text-align: center;padding-bottom: 15px;"><img src="'.plugin_dir_url(__FILE__).'generate-qr-code.php?wallet_address='.$dcn_payment_gateway->settings['walletAddress'].'"/></div>', 'wcdp_domain');
            ?>
                <div class="wcdp-order-container"></div>
                <script>
                    var fetchStatusInterval = setInterval(fetchStatus, 5000);
                    var currentStatus;
                    
                    function fetchStatus() {
                        jQuery.ajax({
                            url : '<?php echo site_url(''); ?>/wp-admin/admin-ajax.php?action=fetch_order_status&order_id=<?php echo $order->get_id(); ?>',
                            type : 'post',
                            error : function(response) {
                                console.log(response);
                            },
                            success : function(response) {
                                response = JSON.parse(response);

                                if (currentStatus != response.status) {
                                    currentStatus = response.status;

                                    if (response.success) {
                                        if (response.status == 'processing') {
                                            jQuery('.wcdp-order-container').html('<div class="wcdp-successful-payment" style="text-align: center; font-size: 20px;padding-top: 20px;">Your payment was successfully processed.<div style="padding-top: 20px;font-size: 16px;"><a href="https://etherscan.io/tx/'+response.data.transactionHash+'" target="_blank" class="button">See Payment Confirmation</a></div></div>');

                                            clearInterval(fetchStatusInterval);
                                        } else {
                                            jQuery('.wcdp-order-container').html('<div class="wcdp-payment-response" style="text-align: center; font-size: 20px;padding-top: 20px;padding-bottom: 20px;">Your order has been moved to status '+response.status+'.</div>');
                                        }
                                    } else {
                                        jQuery('.wcdp-order-container').html('<?php echo $paymentDetails; ?>');

                                        <?php
                                        if ($timeframe) {
                                        ?>
                                        if (jQuery('#wcdp-timeframe-timer').length) {
                                            var countDownDate = <?php echo $timeframeDateTimestamp; ?> * 1000;
                                            // Update the count down every 1 second
                                            var timerInterval = setInterval(function() {
                                                // Get today's date and time
                                                var now = new Date().getTime();

                                                // Find the distance between now and the count down date
                                                var distance = countDownDate - now;

                                                // Time calculations for days, hours, minutes and seconds
                                                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                                var timer;
                                                if (days > 0) {
                                                    timer = days + 'd ' + hours + 'h ' + minutes + 'min ' + seconds + 'sec ';
                                                } else if (hours > 0) {
                                                    timer = hours + 'h ' + minutes + 'min ' + seconds + 'sec ';
                                                } else {
                                                    timer = minutes + 'min ' + seconds + 'sec ';
                                                }

                                                jQuery('#wcdp-timeframe-timer').html(timer);

                                                // If the count down is over, write some text
                                                if (distance < 0) {
                                                    clearInterval(timerInterval)

                                                    clearInterval(fetchStatusInterval);

                                                    jQuery('.wcdp-order-container').html('<div class="wcdp-order-expired" style="font-size: 22px; text-align: center; padding-top: 20px; padding-bottom: 20px;">Order payment expired.</div>');
                                                }
                                            }, 1000);
                                        }
                                        <?php
                                        }
                                        ?>
                                    }
                                }
                            }
                        });
                    }

                    fetchStatus();
                </script>
            <?php
        }
    }
    //echo '<h2 class="h2thanks">Get 20% off</h2><p class="pthanks">Thank you for making this purchase! Come back and use the code "<strong>Back4More</strong>" to receive a 20% discount on your next purchase!</p>';
}
add_action( 'woocommerce_thankyou_wcdp', 'wcdp_add_content_thankyouadd_content_thankyou' );

// get order_status
function wcdp_fetch_order_status(){
    $order = wc_get_order(trim($_REQUEST['order_id']));
    if (!empty($order)) {
        $order_data = $order->get_data();

        if ($order_data['status'] == 'processing') {
            echo json_encode(array('success' => true, 'status' => $order_data['status'], 'data' => array('transactionHash' => get_post_meta($order->get_id(), 'tx_hash', true))));
        } else if ($order_data['status'] == 'on-hold') {
            echo json_encode(array('error' => true, 'status' => $order_data['status'], 'message' => 'Order status still not completed.'));
        } else {
            echo json_encode(array('success' => true, 'status' => $order_data['status']));
        }
    } else {
        echo json_encode(array('error' => true, 'message' => 'Missing order.'));
    }
    wp_die();
}

add_action('wp_ajax_nopriv_fetch_order_status', 'wcdp_fetch_order_status');
add_action('wp_ajax_fetch_order_status','wcdp_fetch_order_status');

// add Dentacoin currency
function wcdp_add_my_currency($currencies) {
    // removing any other currency
    //$currencies = array();
    $currencies['DCN'] = __('Dentacoin', 'woocommerce');
    return $currencies;
}
add_filter( 'woocommerce_currencies', 'wcdp_add_my_currency' );

// add Dentacoin currency symbol
function wcdp_add_my_currency_symbol($currency_symbol, $currency) {
    switch($currency) {
        case 'DCN':
            $currency_symbol = '٨';
        break;
    }
    return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'wcdp_add_my_currency_symbol', 10, 2);

// current currency is Dentacoin => remove the decimals from prices
if (get_option('woocommerce_currency') == 'DCN') {
    add_filter('woocommerce_price_trim_zeros', '__return_true');
}

// method to check orders
add_action('wcdp_update_current_orders', 'wcdp_updateCurrentOrders');
function wcdp_updateCurrentOrders() {
    /*$current = 'Test string';
    file_put_contents(time()."-blabla.txt", $current);*/

    // get orders on-hold which are selected to be paid with Dentacoin payment gateway
    $orders = wc_get_orders(array('numberposts' => -1, 'status' => 'on-hold', 'payment_method' => 'wcdp'));
    $payment_gateway_id = 'wcdp';
    // Get an instance of the WC_Payment_Gateways object
    $payment_gateways = WC_Payment_Gateways::instance();

    // Get the desired WC_Payment_Gateway object
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    if (!empty($orders)) {
        $ordersArr = array();
        foreach ($orders as $order) {
            $walletAddress = get_post_meta($order->get_id(), 'wallet_address', true);
            $receiverAddress = get_post_meta($order->get_id(), 'receiver_address', true);
            if (!empty($walletAddress) && !empty($receiverAddress)) {
                if ($order->get_currency() == 'DCN') {
                    $dataToPassToApi = ['order_id' => $order->get_id(), 'dcn' => $order->get_total(), 'sender' => $walletAddress, 'receiver' => $receiverAddress];
                } else if ($order->get_currency() == 'USD' || $order->get_currency() == 'EUR' || $order->get_currency() == 'GBP' || $order->get_currency() == 'RUB' || $order->get_currency() == 'INR' || $order->get_currency() == 'CNY' || $order->get_currency() == 'JPY') {
                    $dataToPassToApi = ['order_id' => $order->get_id(), 'dcn' => get_post_meta($order->get_id(), 'total_dcn_price', true), 'sender' => $walletAddress, 'receiver' => $receiverAddress];
                }

                array_push($ordersArr, $dataToPassToApi);
            }
        }

        $arrayToSubmitToApi = array('parameters' => $ordersArr);

        if (!empty($payment_gateway->settings['expireIn'])) {
            $arrayToSubmitToApi['timeframe'] = true;
        }

        // POST request to check for previous Dentacoin payments on the blockchain
        $json = json_encode($arrayToSubmitToApi);
        $response = wp_remote_post('https://methods.dentacoin.com/check-for-dcn-transfer', array (
            'method'  => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Content-Length' => mb_strlen($json)
            ),
            'body' => $json
        ));

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $resp = json_decode($body);

            if (is_object($resp) && property_exists($resp, 'success') && !empty($resp->success)) {
                foreach ($resp->success as $event) {
                    $orderEvents = (array)$event;
                    foreach ($orderEvents as $order_id => $events) {
                        $order = wc_get_order($order_id);
                        if (!empty($events)) {
                            foreach ($events as $event) {
                                $wpQueryArgs = array(
                                    'post_type' => wc_get_order_types(),
                                    'post_status' => array_keys(wc_get_order_statuses()),
                                    'posts_per_page' => -1,
                                    'meta_query' => array(
                                        'relation' => 'AND',
                                        array(
                                            'key'     => 'tx_hash', //meta type is plain string and i need results alphabetically.
                                            'value'   => $event->transactionHash,
                                            'compare' => '='
                                        )
                                    )
                                );

                                $checkIfExistingTxHashQuery = new WP_Query($wpQueryArgs);
                                if (empty($checkIfExistingTxHashQuery->get_posts())) {
                                    if (property_exists($payment_gateway, 'settings') && !empty($payment_gateway->settings['expireIn']) && property_exists($event, 'timestamp')) {
                                        // if timeframe is set
                                        if (strtotime($order->order_date) + (int)$payment_gateway->settings['expireIn'] < $event->timestamp) {
                                            // it doesn't matter if you paid or not, if passed the payment date limit order going to be failed
                                            $order->update_status('wc-failed');
                                            update_post_meta($order_id, 'tx_hash', $event->transactionHash);
                                        } else {
                                            // order was paid in the timeframe
                                            $order->update_status('wc-processing');
                                            update_post_meta($order_id, 'tx_hash', $event->transactionHash);
                                        }
                                    } else {
                                        // if timeframe isnt set
                                        // if paid change order status to processing
                                        $order->update_status('wc-processing');
                                        update_post_meta($order_id, 'tx_hash', $event->transactionHash);
                                    }
                                }
                            }
                        } else {
                            // no events registered for this order
                            if (property_exists($payment_gateway, 'settings') && !empty($payment_gateway->settings['expireIn'])) {
                                if (time() > strtotime($order->order_date) + (int)$payment_gateway->settings['expireIn']) {
                                    // if time expired and still havent paid change order status to failed
                                    $order->update_status('wc-failed');
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
add_action('init', 'wcdp_updateCurrentOrders');

// get dentacoin price in fiat currencies from external provider
function wcdp_getDentacoinDataByExternalProvider() {
    $response = wp_remote_get('https://api.coingecko.com/api/v3/coins/dentacoin');
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        return array(
            'USD' => $data->market_data->current_price->usd,
            'EUR' => $data->market_data->current_price->eur,
            'GBP' => $data->market_data->current_price->gbp,
            'RUB' => $data->market_data->current_price->rub,
            'INR' => $data->market_data->current_price->inr,
            'CNY' => $data->market_data->current_price->cny,
            'JPY' => $data->market_data->current_price->jpy
        );
    }
}

// minutely schedule to wp_schedules
function wcdp_add_cron_interval($schedules) {
    $schedules['minutely'] = array(
        'interval' => 60,
        'display'  => esc_html__( 'Every Sixty Seconds' ));
    return $schedules;
}
add_filter('cron_schedules', 'wcdp_add_cron_interval');

// setup "cron" to run orders update check
function wcdp_register_daily_revision_delete_event() {
    // Make sure this event hasn't been scheduled
    if (!wp_next_scheduled('wcdp_update_current_orders')) {
        // Schedule the event
        wp_schedule_event(time(), 'minutely', 'wcdp_update_current_orders');
    }
}
add_action( 'init', 'wcdp_register_daily_revision_delete_event');

function wcdp_admin_notice_about_not_having_main_wallet_address_saved() {
    $payment_gateway_id = 'wcdp';
    // Get an instance of the WC_Payment_Gateways object
    $payment_gateways = WC_Payment_Gateways::instance();
    $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
    if (property_exists($payment_gateway, 'settings')) {
        if (empty($payment_gateway->settings['walletAddress'])) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'In order to receive Dentacoin payments, you must enter your Wallet Address in your Dentacoin Payment Gateway for WooCommerce plugin settings. Access them in the WooCommerce Payments tab.', 'wcdp_domain' ); ?></p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'wcdp_admin_notice_about_not_having_main_wallet_address_saved');

function wcdp_disable_payment_method($available_gateways) {
    if (isset( $available_gateways['wcdp']) && current_user_can( 'manage_woocommerce')) {
        $payment_gateway_id = 'wcdp';
        // Get an instance of the WC_Payment_Gateways object
        $payment_gateways = WC_Payment_Gateways::instance();
        $dcn_payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        if (property_exists($dcn_payment_gateway, 'settings')) {
            if (empty($dcn_payment_gateway->settings['walletAddress'])) {
                unset( $available_gateways['wcdp']);
            }
        }
    }
    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'wcdp_disable_payment_method');