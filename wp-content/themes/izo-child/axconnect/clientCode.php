<?php
#E-maili olemasolu kontroll Axaptas kasutaja loomisel#

add_action( 'woocommerce_register_post', 'action_function_name_5011', 10, 3 );
function action_function_name_5011( $username, $email, $errors ){
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
    $envelopeContent = '&lt;VPXRequest&gt;&lt;GetCustomer&gt;&lt;RequestID&gt;&lt;/RequestID&gt;&lt;Email&gt;' . $email . '&lt;/Email&gt;&lt;/GetCustomer&gt;&lt;/VPXRequest&gt;';

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

    $axStatus = $xml->GetCustomer[0]->Status;
    $axKood = $xml->GetCustomer[0]->CustID;
    $axError = $xml->GetCustomer[0]->Errors->Error;

    if ($axStatus == 'Error') {
        echo '<script>';
        echo "window.location = '" . get_home_url() . "/minu-konto/?axError=".$axError."';";
        echo '</script>';
        exit();
    };
    if ($axStatus == 'OK') {
        return;
    } else {
        echo '<script>';
        echo "window.location = '" . get_home_url() . "/minu-konto/';";
        echo '</script>';
        exit();
    };
}

add_action( 'woocommerce_account_content', 'getAxClientCode' );
function getAxClientCode() {
    $user_id = get_current_user_id();
    
    global $wpdb;
    
    $email = $wpdb->get_var( "SELECT user_email FROM $wpdb->users WHERE ID = " . $user_id );
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
    $envelopeContent = '&lt;VPXRequest&gt;&lt;GetCustomer&gt;&lt;RequestID&gt;&lt;/RequestID&gt;&lt;Email&gt;' . $email . '&lt;/Email&gt;&lt;/GetCustomer&gt;&lt;/VPXRequest&gt;';

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

    $axStatus = $xml->GetCustomer[0]->Status;
    $axKood = implode(array($xml->GetCustomer[0]->CustID));

    //var_dump($axKood);
    //var_dump($user_id);
    update_user_meta( $user_id, 'OrderAccount', $axKood );
}

add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

function extra_user_profile_fields( $user ) { ?>
<h3><?php _e("Axapta", "blank"); ?></h3>

<table class="form-table">
<tr>
    <th><label for="OrderAccount">AX kliendikood</label></th>
    <td>
        <label for="OrderAccount"><?php echo get_the_author_meta( 'OrderAccount', $user->ID ); ?></label>
    </td>
</tr>
</table>
<?php }

if (isset($_GET['axError'])) {
    add_action( 'woocommerce_register_form_start', 'emailError', 10, 1 );
    function emailError() {
        $axError = $_GET['axError'];
        echo '<div>';
        echo '<h2>';
        echo $axError;
        echo '</h2>';
        echo '<p style="color: red">Registreerimine ebaõnnestus!<p>';
        echo '<p>Meie andmebaasis ei ole volitatud isikute hulgas sellise meiliaadressiga isikut.<br> Palun saata allkirjastatud kujul <a href="mailto:info@vipex.ee">info@vipex.ee</a> aadressile enda ettevõtte volitatud isikute andmed.';
        echo '</div>';
    }
}