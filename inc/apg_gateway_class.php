<?php

function apg_gateway_class( $gateways ) {

	$gateways[] = 'WC_apg_Gateway';

	return $gateways;

}

 

function apg_gateway_init_class() {

 

	class WC_apg_Gateway extends WC_Payment_Gateway {

 		/**

 		 * Class constructor, more about it in Step 3

 		 */

 		public function __construct() {

 				$this->id = 'aiglobal'; // payment gateway plugin ID
				

				
				 // Set the default icon to use
				 $this->icon = plugins_url( 'assets/mastercard.png', dirname( __FILE__ ) );

				 // Check if a custom icon has been uploaded
				 $this->apg_icon = $this->get_option( 'apg_icon' );
				 if ( $this->apg_icon ) {
					 $this->icon = $this->apg_icon;
				 }

				

				$this->has_fields = false; 

				$this->method_title = 'AiGlobal Payment Gateway';

				$this->method_description = 'Pay with AiGlobal Payment Gateway'; // will be displayed on the options page



				$this->supports = array(

					'products'

				);

			 

				// Method with all the options fields

				$this->init_form_fields();

			 

				// Load the settings.

				$this->init_settings();

				$this->enabled          = $this->get_option( 'enabled' );

				$this->title            = $this->get_option( 'title' );

				$this->description      = $this->get_option( 'description' );

				$this->merNo 		    = $this->get_option( 'merNo' );

				$this->gatewayNo 	    = $this->get_option( 'gatewayNo' );

				$this->merKey 	        = $this->get_option( 'merKey' );

				$this->testmode         = $this->get_option( 'testmode' );

				$this->test_merNo 		= $this->get_option( 'test_merNo' );

				$this->test_gatewayNo 	= $this->get_option( 'test_gatewayNo' );

				$this->test_merKey 	    = $this->get_option( 'test_merKey' );



				// This action hook saves the settings

				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		 

 		}

 

		/**

 		 * Plugin options, we deal with it in Step 3 too

 		 */

 		public function init_form_fields(){



 			$this->form_fields = array(

				'enabled' => array(

					'title'       => 'Enable/Disable',

					'label'       => 'Enable AiGlobal Gateway',

					'type'        => 'checkbox',

					'description' => 'Enable AiGlobal Gateway',

					'desc_tip'    => true,

				),

				'title' => array(

					'title'       => 'Title',

					'type'        => 'text',

					'description' => 'Enter the title for payment gateway.',

					'desc_tip'    => true,

				),

				'description' => array(

					'title'       => 'Description',

					'type'        => 'textarea',

					'description' => 'Enter the desciption for payment gateway.',

					'desc_tip'    => true,

				),

				'merNo' => array(

					'title'       => 'Merchant No',

					'type'        => 'number',

					'description' => 'Merchant account number.',

					'desc_tip'    => true,

				),

				'gatewayNo' => array(

					'title'       => 'Gateway No',

					'type'        => 'number',

					'description' => 'Merchant Gateway Number.',

					'desc_tip'    => true,

				),

				'merKey' => array(

					'title'       => 'Merchant Key',

					'type'        => 'text',

					'description' => 'Merchant Key.',

					'desc_tip'    => true,

				),

				'apg_icon' => array(
					'title'         => 'Icon URL',
					'type'          => 'text',
					'description'   => 'Enter Icon URL.',
					'desc_tip'      => true,
				),

				'testmode' => array(

					'title'       => 'Test Mode',

					'label'       => 'Enable',

					'type'        => 'checkbox',

					'description' => 'Place the payment gateway in test mode using test API keys.',

					'desc_tip'    => true,

				),

				'test_merNo' => array(

					'title'       => 'Test Merchant No',

					'type'        => 'number',

					'description' => 'Testing Merchant account number.',

					'desc_tip'    => true,

				),

				'test_gatewayNo' => array(

					'title'       => 'Test Gateway No',

					'type'        => 'number',

					'description' => 'Testing Merchant Gateway Number.',

					'desc_tip'    => true,

				),

				'test_merKey' => array(

					'title'       => 'Test Merchant Key',

					'type'        => 'text',

					'description' => 'Test Merchant Key.',

					'desc_tip'    => true,

				),

			);

 

		 

	 	}

 

		/**

		 * You will need it if you want your custom credit card form, Step 4 is about it

		 */

		public function payment_fields() {

			?>

			<script type="text/javascript">

				jQuery('#apg_expdate').keyup(function () {

				  var val = this.value.replace(/[^0-9]/g, '');

				  if (val.length > 4) {

				    val = val.slice(0, 4);

				  }

				  input = val.split(/[\s\/]+/g).join('');

				  if (input.length > 2) {

				    input = input.slice(0, 2) + ' / ' + input.slice(2);

				  }

				  this.value = input;

				});



				jQuery(document).ready(function(){

				  jQuery("#apg_ccNo").on("keyup", function(){

				    var val = jQuery(this).val().replace(/\D/g, '');

				    var newVal = '';

				    var sizes = [4, 4, 4, 4];



				    for(var i in sizes){

				      var size = sizes[i];

				      if(val.length > 0){

				        newVal += val.substr(0, size) + ' ';

				        val = val.substr(size);

				      }

				    }



				    jQuery(this).val(newVal.trim());

				  });

				});


			</script>

			<?php
			// ok, let's display some description before the payment form

			if ( $this->description ) {

				// you can instructions for test mode, I mean test card numbers etc.

				if ( $this->testmode == 'yes' ) {

					$this->description .= ' TEST MODE ENABLED.';

					$this->description  = trim( $this->description );

				}

				// display the description with <p> tags etc.

				echo wpautop( wp_kses_post( $this->description ) );

			}

		 

			// I will echo() the form, but you can close PHP tags and print it directly in HTML

			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

		 

			// Add this action hook if you want your custom payment gateway to support it

			do_action( 'woocommerce_credit_card_form_start', $this->id );

		 

			// I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc

			echo '<div class="apg_ccno"><label>Card number <span class="required">*</span></label>

				<input class="max_payment_fields" name="apg_ccNo" id="apg_ccNo" type="text" autocomplete="off" placeholder="**** ***** ***** ****">

				</div>

				<div class="apg_parent">

				<div class="apg_card_date">

					<label>Expiry (MM/YY) <span class="required">*</span></label>

					<input class="max_payment_fields"  name="apg_expdate" id="apg_expdate" type="text" autocomplete="off" placeholder="MM / YY">

				</div>

				<div class="apg_card_cvv">

					<label>Card code<span class="required">*</span></label><br>

					<input  name="apg_cvv" id="apg_cvv" type="password" autocomplete="off" placeholder="CVC">

				</div>

				</div>

				<div class="clear"></div>';

		 

			do_action( 'woocommerce_credit_card_form_end', $this->id );

			echo '<div class="clear"></div></fieldset>';

		 

		}

 

		/*

		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form

		 */

	 	public function payment_scripts() {

 

		 

	 	}

 

		/*

 		 * Fields validation, more in Step 5

		 */

		public function validate_fields() {

 		

		 

		}

	





 

 

		/*

		 * We're processing the payments here, everything about it is in Step 5

		 */

		public function process_payment( $order_id ) 

		{

         

			global $woocommerce;

			$order  = wc_get_order( $order_id );

			// Get payment fields values

			$apg_ccNo     = isset( $_POST['apg_ccNo'] ) ? wc_clean( $_POST['apg_ccNo'] ) : '';

			$apg_ccNo     = str_replace(' ', '', $apg_ccNo);

			$apg_expdate  = isset( $_POST['apg_expdate'] ) ? wc_clean( $_POST['apg_expdate'] ) : '';

			$apg_cvv      = isset( $_POST['apg_cvv'] ) ? wc_clean( $_POST['apg_cvv'] ) : '';

			$date_parts = explode('/', $apg_expdate);

			$apg_month = trim($date_parts[0]);

			$apg_year = '20'.trim($date_parts[1]);

			// Loop through the cart items and extract the product names

			$product_names = array();

			foreach( WC()->cart->get_cart() as $cart_item ) {

				$product = $cart_item['data'];

				$product_names[] = $product->get_name();

			}



			// Join the product names into a comma-separated string

			$product_names_str = implode(', ', $product_names);

			$apg_mode = $this->get_option( 'testmode' );

			$merNo        = '';

			$gatewayNo    = '';

			if($apg_mode == 'yes'){

				$merNo      = $this->get_option( 'test_merNo' );

				$gatewayNo  = $this->get_option( 'test_gatewayNo' );

				$merKey     = $this->get_option( 'test_merKey' );

				$api_url    = 'https://try.aiglobalpay.com/payment';

			}else{

				$merNo      =  $this->get_option( 'merNo' );

				$gatewayNo  = $this->get_option( 'gatewayNo' );

				$merKey     = $this->get_option( 'merKey' );

				$api_url    = 'https://tran.aiglobalpay.com/payment';

			}

			$signInfo=hash("sha256",$merNo.$gatewayNo.$order->get_order_number().$order->get_currency().$order->get_total().$apg_ccNo.$apg_year.$apg_month.$apg_cvv.$merKey);

			



           

			$data = array(

				'merNo'          => $merNo,

				'gatewayNo'      => $gatewayNo,

				'orderNo'        => $order->get_order_number(),

				'orderCurrency'  => $order->get_currency(),

				'orderAmount'    => $order->get_total(),

				'shipFee'        => $order->get_shipping_total(),

				'discount'       => $order->get_discount_total(),

				'goodsInfo'      => $product_names_str,

				'cardNo'         => $apg_ccNo,

				'month'          => $apg_month,

				'year'           => $apg_year,

				'cvv'            => $apg_cvv,

				'issuingBank'    => 'ICBC',

				'firstName'      => $order->get_billing_first_name(),

				'lastName'       => $order->get_billing_last_name(),

				'email'          => $order->get_billing_email(),

				'ip'             => $order->get_customer_ip_address(),

				'phone'          => $order->get_billing_phone(),

				'country'        => $order->get_billing_country(),

				'state'          => $order->get_billing_state(),

				'city'           => $order->get_billing_city(),

				'address'        => $order->get_billing_address_1(),

				'zip'            => $order->get_billing_postcode(),

				'shipFirstName'  => !empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : $order->get_billing_first_name(),

				'shipLastName'   => !empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : $order->get_billing_last_name(),

				'shipEmail'      => $order->get_billing_email(),

				'shipPhone'      => $order->get_billing_phone(),

				'shipCountry'    => !empty($order->get_shipping_country()) ? $order->get_shipping_country() : $order->get_billing_country(),

				'shipState'      => !empty($order->get_shipping_state()) ? $order->get_shipping_state() : $order->get_billing_state(),

				'shipCity'       => !empty($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city(),

				'shipAddress'    => !empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : $order->get_billing_address_1(),

				'shipZip'        => !empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() :  $order->get_billing_postcode(),

				'os'             => 'win10',

				'brower'         => 'google',

				'browerLang'     => 'en',

				'timeZone'       => '-180',

				'resolution'     => '2K',

				'isCopyCard'     => '0',

				'newCookie'      => 'ip=192.168.1.1',

				'oldCookie'      => 'ip=192.168.1.1',

				'webSite'        => wp_specialchars_decode(site_url()),

				'notifyUrl'      => wp_specialchars_decode($order->get_checkout_payment_url()),

				'returnUrl'      => wp_specialchars_decode($order->get_checkout_payment_url()),

				'signInfo'       => $signInfo,

				'remark'         => 'order note'

			);

			$apg_request_body = json_encode($data);
		
		
			try {
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $api_url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'GET',
					CURLOPT_POSTFIELDS =>$apg_request_body,
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json',
						'Cookie: JSESSIONID=FBDB906A42CE7F30BA3DF83D97E41471'
					),
				));
				$response = curl_exec($curl);
				curl_close($curl);
			} catch (Exception $e) {
				$decoded_response = json_decode($response, true);
				wc_add_notice( $decoded_response, 'error' );
				return;
			}

			$jsonResponse = json_decode($response, true);

	
			if ($jsonResponse['orderErrorCode'] === '0000' || $jsonResponse['orderErrorCode'] === '00' ) {

				$order->payment_complete();
				update_post_meta( $order_id, '_transaction_id', $jsonResponse['tradeNo']);
				update_post_meta( $order_id, '_apg_success_response', $response);

				return array(

					'result' => 'success',

					'redirect' => $this->get_return_url( $order )

				);

			}elseif ($jsonResponse['orderErrorCode'] === '0001') {
				update_post_meta( $order_id, '_apg_success_response', $response);
				return array(

					'result' => 'success',

					'redirect' => $jsonResponse['redirectUrl']

				);

			}else{
				update_post_meta( $order_id, '_apg_error_response', $response);
				wc_add_notice( $jsonResponse['orderInfo'], 'error' );
				return;
			}



	 	}

 

		/*

		 * In case you need a webhook, like PayPal IPN etc

		 */



	

		public function webhook() {

			

 

		 

	 	}





 	}

}



