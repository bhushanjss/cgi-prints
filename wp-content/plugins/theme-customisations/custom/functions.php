<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * functions.php
 * Add PHP snippets here
 */

/* after an order has been processed, we will use the  'woocommerce_thankyou' hook, to add our function, to send the data */
add_action('woocommerce_thankyou', 'wdm_send_order_to_ext', 10, 1);
function wdm_send_order_to_ext( $order_id ){
    $app_key = '1A2A-B1BB-CC3C-DD1D';
    $SHIPPING_METHOD_UPS_SD = 'UPS Standard';
    $SHIPPING_METHOD_UPS_PD = 'UPS Priority';
    $SHIPPING_METHOD_UPS_OD = 'UPS Overnight';

    // to test out the API, set $api_mode as ‘sandbox’
    $api_mode = 'sandbox';
    if($api_mode == 'sandbox'){
        //test url
        $endpoint = "https://preview.webservices.fujifilmesys.com/fes.digitalintegrationservices/order/orderservices.asmx?op=OrderSubmit";
    }
    else{
        // production URL
        $endpoint = "https://webservices.fujifilmesys.com/fes.digitalintegrationservices/order/orderservices.asmx?op=OrderSubmit";
    }


    $order = new WC_Order( $order_id ); 
    $shipping_type = $order->get_shipping_method();
    $shipping_cost = $order->get_total_shipping();

    $user_id = $order->get_customer_id();
    $total_tax = $order->get_total_tax();
    $total_price = $order->get_total();
    $total_item_price = $order->get_subtotal();
    $total_shipping = $order->get_shipping_total();
    $total_handling = 0;

    $customer_name = $order->get_formatted_billing_full_name();
    $order_date = $order->get_date_completed();//'1997-07-16T19:20:30+01:00';
    $fulfiller_id = '007';

    if($order->has_shipping_address()) {
        $shipping_first_name = $order->get_shipping_first_name();
        $shipping_last_name = $order->get_shipping_last_name();
        $shipping_address1 = $order->get_shipping_address_1();
        $shipping_address2 = $order->get_shipping_address_2();
        $shipping_city = $order->get_shipping_city();
        $shipping_state = $order->get_shipping_state();
        $shipping_post_code = $order->get_shipping_postcode();
        $shipping_country = $order->get_shipping_country();
        $shipping_phone = $order->get_billing_phone();
    } else if($order->has_billing_address()) {
        $shipping_first_name = $order->get_billing_first_name();
        $shipping_last_name = $order->get_billing_last_name();
        $shipping_address1 = $order->get_billing_address_1();
        $shipping_address2 = $order->get_billing_address_2();
        $shipping_city = $order->get_billing_city();
        $shipping_state = $order->get_billing_state();
        $shipping_post_code = $order->get_billing_postcode();
        $shipping_country = $order->get_billing_country();
        $shipping_phone = $order->get_billing_phone();
    }
    

    $shipping_method_code = 'SD';
    $shipping_method_name = $order->get_shipping_method();

    switch($shipping_method_name) {
        case $SHIPPING_METHOD_UPS_SD:
            $shipping_method_code = 'SD';
            break;
        case $SHIPPING_METHOD_UPS_PD:
            $shipping_method_code = 'PD';
            break;
        case $SHIPPING_METHOD_UPS_OD:
            $shipping_method_code = 'OD';
            break;        
    }

    // get product details
    $items = $order->get_items();    
    $item_name = array();
    $item_qty = array();
    $item_price = array();
    $item_sku = array();
    $item_attributes = array();

    $media_id = '';
    $media_file_name = '';
    $media_url = '';
        
    foreach( $items as $key => $item){
        $item_name[] = $item['name'];
        $item_qty[] = $item['qty'];
        $item_price[] = $item['line_total'];
        
        $item_id = $item['product_id'];
        $product = new WC_Product($item_id);
        $media_id = $product->get_sku();
        $media_url = $product->get_attribute('URL');
        $media_file_name = $product->get_name();
    }    

    

    $data = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
          <soap12:Body>
            <OrderSubmit xmlns="FES.DigitalIntegrationServices">
              <AppKey>'.$app_key.'</AppKey>
              <OrderManifest>
                <OrderID>'.$order_id.'</OrderID>
                <OriginatorOrderID>'.$order_id.'</OriginatorOrderID>
                <Summary>
                  <TotalTax>'.$total_tax.'</TotalTax>
                  <TotalPrice>'.$total_price.'</TotalPrice>
                  <TotalItemPrice>'.$total_item_price.'</TotalItemPrice>
                  <TotalShipping>'.$total_shipping.'</TotalShipping>
                  <TotalHandling>'.$total_handling.'</TotalHandling>
                  <OwnerInfo>
                    <OrderUserTypeID>0</OrderUserTypeID>
                    <UserID>'.$user_id.'</UserID>
                  </OwnerInfo>
                  <TaxList>
                    <Tax xsi:nil="true" />
                    <Tax xsi:nil="true" />
                  </TaxList>
                </Summary>
                <OriginatorName>'.$customer_name.'</OriginatorName>
                <CheckoutDate>'.$order_date.'</CheckoutDate>
                <AppKey>'.$app_key.'</AppKey>
                <SubOrders>
                  <SubOrder>
                    <Summary xsi:nil="true" />
                    <OriginatorSubOrderID>'.$order_id.'</OriginatorSubOrderID>
                    <FulfillerID>'.$fulfiller_id.'</FulfillerID>
                    <LineItems xsi:nil="true" />
                    <ShippingInfo>
                        <FirstName>'.$shipping_first_name.'</FirstName>
                        <LastName>'.$shipping_last_name.'</LastName>
                        <Address1>'.$shipping_address1.'</Address1>
                        <Address2>'.$shipping_address2.'</Address2>
                        <City>'.$shipping_city.'</City>
                        <State>'.$shipping_state.'</State>
                        <PostalCode>'.$shipping_post_code.'</PostalCode>
                        <Country>'.$shipping_country.'</Country>
                        <Phone>'.$shipping_phone.'</Phone>
                        <MethodCode>'.$shipping_method_code.'</MethodCode>
                        <MethodName>'.$shipping_method_name.'</MethodName>                            
                    </ShippingInfo>
                    <SubOrderProperties xsi:nil="true" />
                    <Payments xsi:nil="true" />
                    <TransactionCollection xsi:nil="true" />
                  </SubOrder>
                </SubOrders>
                <FileList>
                  <MediaFile>
                    <MediaID>'.$media_id.'</MediaID>
                    <SourceType>URL</SourceType>
                    <MediaFileName>'.$media_file_name.'</MediaFileName>
                    <MediaFileType>IMAGE</MediaFileType>
                    <Status xsi:nil="true" />
                    <Source>'.$media_url.'</Source>
                    <MediaFileProperties xsi:nil="true" />
                  </MediaFile>
                </FileList>
              </OrderManifest>
            </OrderSubmit>
          </soap12:Body>
        </soap12:Envelope>';

        $args = array ('method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => $data,
            'cookies' => array()
        );
        $response = wp_remote_post( $endpoint , $args );     

        echo "Sending Data:";
        echo $data;

        if ( is_wp_error( $response ) ) {
           $error_message = $response->get_error_message();
           echo "Something went wrong: $error_message";
        } else {
           echo 'Response:<pre>';
           print_r( $response );
           echo '</pre>';
        }
 }

 add_action( 'woocommerce_payment_complete', 'my_api_call', 10, 1);
function my_api_call( $order_id ){

	// Order Setup Via WooCommerce

	$order = new WC_Order( $order_id );

	// Iterate Through Items

	$items = $order->get_items(); 
	foreach ( $items as $item ) {	

		// Store Product ID

	$product_id = $item['product_id'];
        $product = new WC_Product($item['product_id']);

        // Check for "API" Category and Run

        if ( has_term( 'api', 'product_cat', $product_id ) ) {

	       	$name		= $order->billing_first_name;
        	$surname	= $order->billing_last_name;
        	$email		= $order->billing_email;
	        $projectsku     = $product->get_sku(); 
        	$apikey 	= "KEY_GOES_HERE";

        	// API Callout to URL

        	$url = 'https://preview.webservices.fujifilmesys.com/fes.digitalintegrationservices/order/orderservices.asmx';

			$body = array(
				"Project"	=> $projectsku,
				"Name" 		=> $name,
				"Surname"  	=> $surname,
				"Email"		=> $email,
				"KEY"		=> $apikey
			);

			$response = wp_remote_post( $url, 
				array(
					'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
					'method'    => 'POST',
					'timeout' => 75,				    
					'body'		=> json_encode($body),
				)
			);

			$vars = json_decode($response['body'],true);
        	        
                        // API Response Stored as Post Meta

  			update_post_meta( $order_id, 'meta_message_'.$projectsku, $vars['message'] );
  			update_post_meta( $order_id, 'meta_link_'.$projectsku, $vars['link']);
  			update_post_meta( $order_id, 'did-this-run','yes'); // just there as a checker variable for me
        }

    }
}

