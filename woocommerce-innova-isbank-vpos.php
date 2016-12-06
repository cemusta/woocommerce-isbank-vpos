<?php
/**
 * Plugin Name:       WooCommerce Innova Isbank Vpos
 * Plugin URI:        https://github.com/cemusta/woocommerce-isbank-vpos
 * Description:       This Payment Gateway allows you to accept payments on your Woocommerce store via Innova/İşbank Vpos. Supports only sale operation for Innova İşbank Vpos documentation v1.7.
 * Version:           1.0.1
 * Author:            Cem Usta
 * Author URI:        http://nooneelse.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Github URI:		  https://github.com/cemusta/woocommerce-isbank-vpos
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Begins execution of the plugin.
 */
add_action( 'plugins_loaded', 'init_WC_innova_isbank_payment_gateway' );

function init_WC_innova_isbank_payment_gateway() {
	
	add_filter( 'woocommerce_payment_gateways', 'add_WC_innova_isbank_payment_gateway' );
	
	 if (!class_exists('WC_Payment_Gateway')) { return;} //woocommerse yoksa çık.


	class WC_innova_isbank_payment_gateway extends WC_Payment_Gateway {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id               	= 'innova-isbank';
			$this->icon             	=  plugins_url( 'images/cards.png' , __FILE__ );
			$this->has_fields       	= true;
			$this->method_title       	= 'Innova İşbank Vpos';
			$this->method_description	= 'Innova İşbank Vpos authorizes credit card payments and processes them securely with your merchant account.';

			// Load the fo  rm fields
			$this->init_form_fields();
			// Load the settings
			$this->init_settings();
			// Get setting values
			$this->title       	= $this->get_option( 'title' );
			$this->description 	= $this->get_option( 'description' );
			$this->enabled     	= $this->get_option( 'enabled' );
			$this->test     	= $this->get_option( 'test' );
			$this->environment 	= $this->test == 'no' ? 'production' : 'test';
			$this->merchant_id 	= $this->test == 'no' ? $this->get_option( 'merchant_id' ) : $this->get_option( 'test_merchant_id' );
			$this->password 	= $this->test == 'no' ? $this->get_option( 'password' ) : $this->get_option( 'test_password' );
            $this->url          = $this->test == 'no' ? $this->get_option( 'live_url' ) : $this->get_option( 'test_url' );

			// Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'admin_notices', array( $this, 'checks' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		}
		/**
		 * Admin Panel Options
		 */
		public function admin_options() {
			?>
			<h3><?php _e( 'Innova İşbank Vpos (for v1.7)','woocommerce' ); ?></h3>
			<p><?php _e( 'Innova VPos Gateway This Payment Gateway allows you to accept payments on your Woocommerce store via Innova/İşbank Vpos. Supports only sale operation at the moment for Innova İşbank Vpos documentation v1.7.','woocommerce' ); ?></p>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
				<script type="text/javascript">
					jQuery( '#woocommerce_innova-isbank_test' ).change( function () {
						var test    = jQuery( '#woocommerce_innova-isbank_test_merchant_id, #woocommerce_innova-isbank_test_password, #woocommerce_innova-isbank_test_url' ).closest( 'tr' ),
							production = jQuery( '#woocommerce_innova-isbank_merchant_id, #woocommerce_innova-isbank_password, #woocommerce_innova-isbank_live_url' ).closest( 'tr' );
						if ( jQuery( this ).is( ':checked' ) ) {
							test.show();
							production.hide();
						} else {
							test.hide();
							production.show();
						}
					}).change();
				</script>
			</table> <?php
		}
		/**
		 * Check if SSL is enabled and notify the user
		 */
		public function checks() {
			if ( $this->enabled == 'no' ) {
				return;
			}
			// PHP Version
			if ( version_compare( phpversion(), '5.2.1', '<' ) ) {
				echo '<div class="error"><p>' . sprintf( __( 'Error: Gateway requires PHP 5.2.1 and above. You are using version %s.', 'woocommerce' ), phpversion() ) . '</p></div>';
			}
			// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
			elseif ( 'no' == get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) {
				echo '<div class="error"><p>' . sprintf( __( 'Gateway is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate - Gateway will only work in test mode.', 'woocommerce'), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '</p></div>';
			}
		}
		/**
		 * Check if this gateway is enabled
		 */
		public function is_available() {
			if ( 'yes' != $this->enabled ) {
				return false;
			}
			if ( ! is_ssl() && 'yes' != $this->test ) {
				return false;
			}
			return true;
		}
		/**
	 	 * Initialise Gateway Settings Form Fields
	 	 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Enable Innova Payment Gateway', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'Credit card', 'woocommerce' ),
					'desc_tip'    => true
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default'     => 'Pay securely with your credit card.',
					'desc_tip'    => true
				),
				'test' => array(
					'title'       => __( 'Test', 'woocommerce' ),
					'label'       => __( 'Enable Test Mode', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => __( 'Place the payment gateway in test mode using test API keys (real payments will not be taken).', 'woocommerce' ),
					'default'     => 'yes'
				),
				'test_merchant_id' => array(
					'title'       => __( 'Test Merchant ID', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Gateway account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'test_password' => array(
					'title'       => __( 'Test Password', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Gateway account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
                'test_url' => array(
                    'title'       => __( 'Test Payment Url', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'Test mode Payment Url.', 'woocommerce' ),
                    'default'     => 'https://sanalpos.innova.com.tr/ISBANK/VposWeb/v3/Vposreq.aspx',
                    'desc_tip'    => true
                ),
				'merchant_id' => array(
					'title'       => __( 'Merchant ID', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Gateway account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'password' => array(
					'title'       => __( 'Password', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Gateway account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
                'live_url' => array(
                    'title'       => __( 'Live Payment Url', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'Live  mode Payment Url.', 'woocommerce' ),
                    'default'     => 'https://trx.vpos.isbank.com.tr/v3/Vposreq.aspx',
                    'desc_tip'    => true
                ),
			);
		}
		/**
	 	 * Initialise Credit Card Payment Form Fields
	 	 */
		public function payment_fields() {			
			?>
			<fieldset id="credit-card-fieldset">
                <ul style="list-style: none">
                    <li>
                        <label for="innova-card-number"><?php echo __( 'Card Number', 'woocommerce') ?> <span class="required">*</span></label>
                        <input type="text" onkeyup="validateCC()" onchange="validateCC()" name="innova-card-number" placeholder="Credit Card No" maxlength="16" id="innova-card-number" autocomplete="cc-number">
                        <small class="help">We only support Visa and MasterCard</small>
                    </li>
                    <li class="vertical">
                        <ul style="list-style: none">
                            <li>
                                <label for="innova-card-expiry-month"><?php echo __( 'Month', 'woocommerce') ?> <span class="required">*</span></label>
                                <select name="innova-card-expiry-month" id="innova-card-expiry-month" class="input-text" autocomplete="cc-month">
                                    <option value=""><?php _e( 'Month', 'woocommerce' ) ?></option>
                                    <option value='01'>01</option>
                                    <option value='02'>02</option>
                                    <option value='03'>03</option>
                                    <option value='04'>04</option>
                                    <option value='05'>05</option>
                                    <option value='06'>06</option>
                                    <option value='07'>07</option>
                                    <option value='08'>08</option>
                                    <option value='09'>09</option>
                                    <option value='10'>10</option>
                                    <option value='11'>11</option>
                                    <option value='12'>12</option>
                                </select>
                            </li>
                            <li>
                                <label for="innova-card-expiry-year"><?php echo __( 'Year', 'woocommerce') ?> <span class="required">*</span></label>
                                <select name="innova-card-expiry-year" id="innova-card-expiry-year" class="input-text" autocomplete="cc-year">
                                    <option value=""><?php _e( 'Year', 'woocommerce' ) ?></option><?php
                                    for ($iYear = date('Y'); $iYear < date('Y') + 21; $iYear++) {
                                        echo '<option value="' . $iYear . '">' . $iYear . '</option>';
                                    } ?>
                                </select>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <label for="innova-card-cvc"><?php echo __( 'Card Code', 'woocommerce') ?> <span class="required">*</span></label>
                        <input type="text" name="innova-card-cvc" maxlength="3"  placeholder="CVC" id="innova-card-cvc" autocomplete="cc-csc">
                    </li>

                </ul>



			</fieldset>
			<?php
		}
		/**
		 * Outputs style used for Payment fields
		 * Outputs scripts used at Checkout
		 */
		public function payment_scripts() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}

			wp_register_style( 'wc-innova-isbank-vpos-style', plugins_url( 'css/credit-style.css' , __FILE__ ), array(), '20160918', 'all' );
    		wp_enqueue_style( 'wc-innova-isbank-vpos-style' );

    		wp_enqueue_script( 'ccvalidator', plugins_url( 'js/jquery.creditCardValidator.js' , __FILE__ ), array( 'jquery' ), WC_VERSION, true );
            wp_enqueue_script( 'wc-innova-isbank-vpos-scripts', plugins_url( 'js/credit-scripts.js' , __FILE__ ), array( 'jquery','ccvalidator' ), WC_VERSION, true );
		}
		/**
		 * Process the payment
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );
			
			$PostUrl 								= $this->url;

            $merchant_id                            = $this->merchant_id;
            $merchant_pass         					= $this->password;

            $CardNo                                 = $_POST["innova-card-number"];
			$CartMonth                              = $_POST["innova-card-expiry-month"];
			$CartYear                				= $_POST["innova-card-expiry-year"];
			$CVV                                    = $_POST["innova-card-cvc"];

            $orderSum                               = $order->order_total;

            if($CardNo == ""){
                wc_add_notice( "Credit Cart number is empty", 'error' );
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }
            if ($CartMonth == ""){
                wc_add_notice( "Expire Date Month is empty", 'error' );
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }
            if( $CartYear == ""){
                wc_add_notice( "Expire Date Year is empty", 'error' );
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }
            if($CVV == ""){
                wc_add_notice( "CVV code is empty", 'error' );
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }


            $transactionId  = $order_id.'-'.uniqid(); //max 20 char // her işlemde unique olmalı

			$PosXML ='prmstr=<?xml version="1.0" encoding="utf-8"?>
			<VposRequest>
			  <MerchantId>'.$merchant_id.'</MerchantId>
			  <Password>'.$merchant_pass.'</Password>
			  <BankId>1</BankId>
			  <TransactionType>Sale</TransactionType>
			  <TransactionId>'.$transactionId.'</TransactionId>
			  <OrderId>'.$order_id.'</OrderId>
			  <CurrencyAmount>'.$orderSum.'</CurrencyAmount>
			  <CurrencyCode>840</CurrencyCode>			  
			  <Pan>'.$CardNo.'</Pan>
			  <Cvv>'.$CVV.'</Cvv>
			  <Expiry>'.$CartYear.$CartMonth.'</Expiry>
			</VposRequest>';


			$ch = curl_init(); // Dip Not 1

			curl_setopt($ch, CURLOPT_URL,$PostUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 59);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


			$result = curl_exec($ch);

            $xml_object = simplexml_load_string($result);

			curl_close($ch);


           if ($xml_object->ResultCode == "0000") { //checkout successful
                // Add order note
                $order->add_order_note( sprintf( __( '%s payment approved! Trnsaction ID: %s', 'woocommerce' ), $this->title, $xml_object->TransactionId ) ); //yolladığımız transaction no
                $order->add_order_note( sprintf( __( 'Bank Transaction Referans no: %s', 'woocommerce' ), $xml_object->Rrn ) );   // iade için gerekiyor.
               // Order complete
               $order->payment_complete();
                // Remove cart
                WC()->cart->empty_cart();
                // Return thank you page redirect
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order )
                );
            } else if ($xml_object->ResultCode) { //checkout failed
               // Add order note
                $order->add_order_note( sprintf( __( '%s payment declined.<br />Error: %s<br />Code: %s', 'woocommerce' ), $this->title, $xml_object->ResultDetail, $xml_object->ResultCode ) );
                // Print on-screen notice
                $resultDetailEn = getEnglishDetail( "" . $xml_object->ResultCode );
                wc_add_notice( sprintf( __( 'Checkout failed: %s (%s)', 'woocommerce' ), $resultDetailEn, $xml_object->ResultCode ) , 'error' );
               // Order failed
               $order->update_status('failed', __('Order Failed', 'woothemes'));
               // Return to same page
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            } else {
                if($errno = curl_errno($ch)) {

                    $error_message = curl_strerror($errno);

                    $order->add_order_note( sprintf( __( '%s Curl Error occured.<br />Error: %s', 'woocommerce' ), $this->title, $error_message ) );

                    wc_add_notice( "Connection error, please try again later", 'error' );
                }
                else
                {
                    wc_add_notice( "General error, please try again later", 'error' );
                }

                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }
		}
	}

    function getEnglishDetail($code){
        $array = [
            "0000"	=> "Success",
            "1001"	=> "System Error, please try again later",
            "1050"	=> "Incorrect CVV",
            "1051"	=> "Incorrect Credit Card Number",
            "1052"	=> "Credit Card Expiry is incorrect",
            "7777"	=> "Failed due to end of day issued by bank, please try again later",
            "9000"	=> "Problem with Host, please try again later",
            "01" 	=> "Call your bank for confirmation",
            "02" 	=> "Call your bank for confirmation",
            "05" 	=> "Credit Card Denied",
            "15" 	=> "Credit Card issuer error",
            "19" 	=> "Please try again later",
            "51" 	=> "Unsufficient Limit on Credit Card",
            "54" 	=> "Credit Card Expired",
            "55" 	=> "Wrong PIN",
            "82" 	=> "CVV Error",
            "85" 	=> "Denied(General)",
            "96" 	=> "System error, please try again later",
            "99" 	=> "Operation failed, please try again later",
            "GK" 	=> "Credit Card is closed for foreign use"
        ];

        $ret = $array[$code];

        return $ret;
    }

    function add_WC_innova_isbank_payment_gateway( $methods ){
        $methods[] = 'WC_innova_isbank_payment_gateway';
        return $methods;
    }

}