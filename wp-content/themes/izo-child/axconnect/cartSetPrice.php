<?php
#Ostukorvis hindade asendamine Axapta hindadega#

add_action( 'woocommerce_before_calculate_totals', 'misha_recalc_price' );
 
function misha_recalc_price( $cart_object ) {
    global $wpdb;

    $userID  = get_current_user_id();
    $cart = WC()->session->get('cart');

    //Kliendikoodi olemasolu kontroll
    $kliendikood = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = 'OrderAccount'" );

    if($kliendikood == NULL) {
        $kliendikood = 'EK1000';
    }

    foreach ($cart as $key => $value) {
            $postID = $value["product_id"];
            //$userID = $customer["id"];
            $tootekood = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $postID . " AND meta_key = '_sku'" );
            //$kliendikood = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = 'OrderAccount'" );
            $soapUrl = "xxx"; // asmx URL of WSDL
            $soapUser = "yyy";  //  username
            $soapPassword = "zzz"; // password

            $envelopeHeader = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Header>
            <h:CallContext xmlns:h="http://schemas.microsoft.com/dynamics/2010/01/datacontracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <h:Company i:nil="true"/>
                <h:Language i:nil="true"/>
                <h:LogonAsUser i:nil="true"/>
                <h:MessageId i:nil="true"/>
                <h:PartitionKey i:nil="true"/>
                <h:PropertyBag i:nil="true" xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"/>
            </h:CallContext>
            </s:Header>
            <s:Body>
            <WoocommerceServiceGetAnswerRequest xmlns="http://tempuri.org">
                <_request>';
            $envelopeEnd = '</_request>
            </WoocommerceServiceGetAnswerRequest>
            </s:Body>
            </s:Envelope>';
            $envelopeContent = '&lt;VPXRequest&gt;&lt;GetPrice&gt;' . '&lt;ItemID&gt;' . $tootekood . '&lt;/ItemID&gt;' . '&lt;CustID&gt;' . $kliendikood . '&lt;/CustID&gt;' . '&lt;/GetPrice&gt;&lt;/VPXRequest&gt;';

            $xml_post_string = $envelopeHeader . $envelopeContent . $envelopeEnd;

            $headers = array(
                'Content-Type: text/xml; charset=utf-8',
                'soapAction: http://tempuri.org/WoocommerceService/getAnswer'
                 ); //SOAPAction: your op URL

            $url = $soapUrl;

            // PHP cURL  for https connection with auth
            $ch = curl_init();
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
            curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch); 
            curl_close($ch);

            $envelope = str_replace(array('&lt;', '&gt;'), array('<', '>'), $response);
            $pikkus = strlen($envelope);
            $algus = strpos($envelope, "<?xml");
            $lopp = strpos($envelope, "</VPXResponse>");
            $output = substr($envelope, $algus, $lopp - $algus + strlen("</VPXResponse>"));

            $xml = simplexml_load_string($output);

            $uusHind = $xml->GetPrice[0]->Price;

            $ale = $xml->GetPrice[0]->Discount;
            foreach ( $cart_object->get_cart() as $hash => $value ) {
                if ($value["product_id"] == $postID) {
                    $formula = $uusHind-($uusHind * $ale / 100 );
                    $value['data']->set_price($formula);
                }   
            }
    }
    skip:;
}
