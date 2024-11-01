<?php
/**
 * WC wcdp Gateway Class.
 * Built the wcdp method.
 */
class WC_Dentacoin_Payment_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
        global $woocommerce;

        $this->id             = 'wcdp';
        $this->icon           = plugin_dir_url(__FILE__) . 'assets/images/dentacoin-icon.png';
        $this->has_fields     = false;
        $this->method_title   = __('Dentacoin Payment Gateway for WooCommerce','wcdp_domain');
        $this->title   = __('Dentacoin Payment Gateway for WooCommerce','wcdp_domain');
        $this->method_description    = __('Dentacoin (DCN) is an Ethereum crypto token, allowing for easy and secure payments of dental or other services and products.', 'wcdp_domain');

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
		$this->instructions = $this->get_option('instructions');

        // Actions.
        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=') )
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options'));
        else
            add_action('woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options'));


    }


    /* Admin Panel Options.*/
	function admin_options() {
		?>
		<h3><?php __('Dentacoin Payment Gateway for WooCommerce','wcdp_domain'); ?></h3>
    	<table class="form-table">
    		<?php $this->generate_settings_html(); ?>
		</table> <?php
    }

    /* Initialise Gateway Settings Form Fields. */
    public function init_form_fields() {
    	global $woocommerce;
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'wcdp_domain'),
                'type' => 'checkbox',
                'label' => __('Enable Dentacoin Payment Gateway for WooCommerce', 'wcdp_domain'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'wcdp_domain'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'wcdp_domain'),
                /*'desc_tip' => true,*/
                'default' => __('Dentacoin (DCN) Payment', 'wcdp_domain')
            ),
            'description' => array(
                'title' => __('Description', 'wcdp_domain'),
                'type' => 'textarea',
                'description' => __('This is the description customers will see during checkout.', 'wcdp_domain'),
                'default' => __('Dentacoin (DCN) is an Ethereum crypto token, allowing for easy and secure payments of dental or other services and products.', 'wcdp_domain')
            ),
            'walletAddress' => array(
                'title' => __('Wallet address:', 'wcdp_domain'),
                'type' => 'text',
                'class' => 'walletAddressValidation',
                'description' => __('This is where you will receive your DCN payments for completed orders. <br><br> Don\'t have a Dentacoin wallet yet? Create one at <a href="https://wallet.dentacoin.com/" target="_blank">https://wallet.dentacoin.com/</a>. or download Dentacoin Wallet app for <a href="https://apps.apple.com/us/app/dentacoin-wallet/id1478732657" target="_blank">iOS</a> or <a href="https://play.google.com/store/apps/details?id=wallet.dentacoin.com" target="_blank">Android</a>.', 'wcdp_domain')
            ),
            'expireIn' => array(
                'title' => __('Time frame to pay after ordering:', 'wcdp_domain'),
                'type' => 'select',
                    'options' => array('0' => 'No time frame', '3600' => '1 hour', '7200' => '2 hours', '10800' => '3 hours', '14400' => '4 hours', '18000‬' => '5 hours', '21600‬' => '6 hours', '25200‬' => '7 hours', '28800' => '8 hours', '32400' => '9 hours', '36000' => '10 hours', '86400‬' => '1 day', '172800' => '2 days', '259200' => '3 days', '345600‬' => '4 days', '432000‬' => '5 days', '518400' => '6 days', '604800' => '7 days', '691200' => '8 days', '777600' => '9 days', '864000‬' => '10 days', '1296000‬' => '15 days', '1728000‬' => '20 days', '2160000‬' => '25 days', '2592000' => '30 days'),
                'description' => __('Example: If you set the time frame to 1 hour, customers will have exactly 1 hour to pay for their order. If no payment is processed within 1 hour, the order status will automatically be changed to <b>Failed</b>.', 'wcdp_domain')
            )
        );
    }

    /* Process the payment and return the result. */
	function process_payment ($order_id) {
		global $woocommerce;

		$order = new WC_Order( $order_id );

		// Mark as on-hold
		$order->update_status('on-hold', __('Your order wont be shipped until the funds have cleared in our account.', 'woocommerce'));

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect
        // added order-received/ because wc_get_page_permalink('thanks') not returning full thankyou page url
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, wc_get_page_permalink('thanks') . 'order-received/'))
		);
	}

    /* Output for the order received page.   */
	function thankyou() {
		echo $this->instructions != '' ? wpautop( $this->instructions ) : '';
	}
}
