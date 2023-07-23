<?php

/*

 * Plugin Name: AiGlobal Payment Gateway

 * Plugin URI: http://zubitechsol.com/

 * Description: Custom solution to accept online payments from customer.

 * Author: Rock Providers

 * Author URI: http://zubitechsol.com/

 * Version: 1.3

 *





/*Prevent Direct Access*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );





if ( !defined( 'APG__PLUGIN_DIR' ) )

{

	define( 'APG__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

}

if ( !defined( 'APG__PLUGIN_URL' ) )

{

	define( 'APG__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

}



function apg_front_scripts() {

    wp_enqueue_style('apg-css',APG__PLUGIN_URL.'/assets/css/apg_gateway.css');

}





add_action('wp_enqueue_scripts', 'apg_front_scripts');

include_once APG__PLUGIN_DIR . 'inc/apg_gateway_class.php';





/*

 * Declare a Class for Linc Settings

*/

add_filter( 'woocommerce_payment_gateways',  'apg_gateway_class' );

add_action( 'plugins_loaded', 'apg_gateway_init_class' );













add_filter( 'woocommerce_available_payment_gateways', 'remove_payment_gateways_from_order_pay_page' );

function remove_payment_gateways_from_order_pay_page( $available_gateways ) {

    if ( is_checkout_pay_page() ) {

        $available_gateways = array();

    }

    return $available_gateways;

}



add_action( 'woocommerce_pay_order_after_submit', 'apg_update_order_status_after_payment', 10, 1 );

function apg_update_order_status_after_payment( ) {

    $order_id = absint( get_query_var( 'order-pay' ) );

    $order = wc_get_order( $order_id );

    if ( 'aiglobal' === $order->get_payment_method() ) {

        // Get the order error code from the header

        $order_error_code = isset( $_GET['orderErrorCode'] ) ? sanitize_text_field( $_GET['orderErrorCode'] ) : '';

        $tradeNo = isset( $_GET['tradeNo'] ) ? sanitize_text_field( $_GET['tradeNo'] ) : '';

        

         // Check if the order error code is 0000

        if ( '0000' === $order_error_code || '00' === $order_error_code ) {

            update_post_meta( $order_id, '_transaction_id', $tradeNo);

            $order->payment_complete();

            $redirect_url = $order->get_checkout_order_received_url();

            wp_redirect( $redirect_url );

            exit;

        }

    }

}

