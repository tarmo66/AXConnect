<?php
#Laoseisude cron job, Axapta#

function update_product_stock_cron(){
    global $product;
    global $wpdb;
    global $tootedMassiiv;

    $koikTooted = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_sku'" );

    $tootedMassiiv = json_decode(json_encode($koikTooted), true);
    $tootedHulk = count($tootedMassiiv);
        
    //Laoseisude ja tooteandmete uuendamine ükshaaval
    for ($x=0; $x < $tootedHulk ; $x++) {
        //Laoseisude uuendamine Axaptast
        $postID = (int) $tootedMassiiv[$x]['post_id']; 
        $tootekood = $tootedMassiiv[$x]['meta_value'];

        // Mario: kontrollib ega ei ole tegemist järeltellimisega tootega
        if ( !(wc_get_product( $postID )->get_stock_status() == 'onbackorder') )
        {    
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
            $envelopeContent = '&lt;VPXRequest&gt;&lt;GetQties&gt;' . '&lt;ItemRange&gt;' . $tootekood . '&lt;/ItemRange&gt;' . '&lt;WhsRange&gt;' . '11..23' . '&lt;/WhsRange&gt;' . '&lt;/GetQties&gt;&lt;/VPXRequest&gt;';

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
            //var_dump($response);
            curl_close($ch);

            $envelope = str_replace(array('&lt;', '&gt;'), array('<', '>'), $response);
            $pikkus = strlen($envelope);
            $algus = strpos($envelope, '<?xml');
            $lopp = strpos($envelope, '</VPXResponse>');
            $output = substr($envelope, $algus, $lopp - $algus + strlen('</VPXResponse>'));

            $xml = simplexml_load_string($output);
            $skuObjekt = $xml->GetQties->Item;
            $kogus = (float) $xml->GetQties->Item;   
            $sku = (string) $skuObjekt['id'];


            // Mario: m2 müügiga toodete puhul üleliigne jääk
            // mis ei ole enam tervikpakend, eemaldatakse.
            //if ( has_term( $M2categories, 'product_cat' , $postID ) )
            if ( get_post_meta( $postID, 'muugiuhik', true ) == 'm2' )
            {
                $package_quantity = get_post_meta( $postID , 'package_quantity', true );

                if ( $package_quantity )
                {
                    $packages = (int)( $kogus / $package_quantity );
                    $kogus = (float)( $packages * $package_quantity );
                    // echo $sku . ' - ' . $kogus . ' / ';
                    $package_quantity = '';
                    $packages = '';
                }
            }

            // Uuendame laoseisu andmebaasis.
            $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $kogus), array('post_id'=> $postID, 'meta_key'=>'_stock'));

            // Muudame toote staatust instock/outofstock.
            if ( wc_get_product( $postID )->is_type( 'variable' ) ) {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'instock'), array('post_id'=> $postID, 'meta_key'=>'_stock_status'));
            }
            else if ($kogus <= '0') {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'outofstock'), array('post_id'=> $postID, 'meta_key'=>'_stock_status'));
            }
            else {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'instock'), array('post_id'=> $postID, 'meta_key'=>'_stock_status'));
            }

            // Muudame laoseisu ja instock/outofstock staatust tabelis wc_product_meta_lookup
            $wpdb->update( $wpdb->wc_product_meta_lookup, array( 'stock_quantity' => $kogus), array('product_id'=> $postID));

            if ($kogus <= '0') {
                $wpdb->update( $wpdb->wc_product_meta_lookup, array( 'stock_status' => 'outofstock'), array('product_id'=> $postID));
            }
            else {
                $wpdb->update( $wpdb->wc_product_meta_lookup, array( 'stock_status' => 'instock'), array('product_id'=> $postID));
            }
                    
            $postID = '';
            $tootekood = '';
            $skuObjekt = ''; 
            $kogus = ''; 
            $sku = '';      
        }  
    }
}
add_shortcode( 'update_product_stock_ax', 'update_product_stock_cron' );

?>