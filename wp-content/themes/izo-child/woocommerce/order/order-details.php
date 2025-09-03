<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.5.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
					<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}

#Sisselogimata kasutaja tellimus#

$order_id = $item->get_order_id();

$userID = get_current_user_id();
//var_dump($userID);

//Sisse logitud

if ($userID > 0) {
  $kliendiAndmed = array();
  $tarneAndmed = array();

  global $wpdb;
  $kliendikood = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = 'OrderAccount'" );

  $kliendiAndmed['eesnimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_first_name'" );
  $kliendiAndmed['perenimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_last_name'" );
  $kliendiAndmed['firma'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_company'" );
  $kliendiAndmed['telefon'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_phone'" );
  $kliendiAndmed['epost'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_email'" );
  $kliendiAndmed['aadress1'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_address_1'" );
  $kliendiAndmed['aadress2'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_address_2'" );
  $kliendiAndmed['linn'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_city'" );
  $kliendiAndmed['indeks'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_postcode'" );
  $kliendiAndmed['maakond'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_state'" );
  $kliendiAndmed['riik'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_billing_country'" );
  $kliendiAndmed['regnr'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_regnr'" );
  $kliendiAndmed['vat'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_vat'" );
  $tarneAndmed['eesnimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_first_name'" );
  $tarneAndmed['perenimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_last_name'" );
  $tarneAndmed['firma'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_company'" );
  $tarneAndmed['aadress1'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_address_1'" );
  $tarneAndmed['aadress2'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_address_2'" );
  $tarneAndmed['linn'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_city'" );
  $tarneAndmed['indeks'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_postcode'" );
  $tarneAndmed['maakond'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_state'" );
  $tarneAndmed['riik'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = " . $userID . " AND meta_key = '_shipping_country'" );
} else {

  //Registreerimata kasutaja
  
  global $wpdb;

  $kliendiAndmed['firma'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_company'" );
  $kliendiAndmed['regnr'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_regnr'" );

  #Kliendikoodi loomine#

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
  $envelopeContent = '&lt;VPXRequest&gt;&lt;CheckRegNum&gt;' . '&lt;RegNum&gt;' . $kliendiAndmed['regnr'] . '&lt;/RegNum&gt;&lt;/CheckRegNum&gt;&lt;/VPXRequest&gt;';

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
  //var_dump($xml);

  $kliendiAndmed['eesnimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_first_name'" );
  $kliendiAndmed['perenimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_last_name'" );
  $kliendiAndmed['firma'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_company'" );
  $kliendiAndmed['telefon'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_phone'" );
  $kliendiAndmed['epost'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_email'" );
  $kliendiAndmed['aadress1'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_address_1'" );
  $kliendiAndmed['aadress2'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_address_2'" );
  $kliendiAndmed['linn'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_city'" );
  $kliendiAndmed['indeks'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_postcode'" );
  $kliendiAndmed['maakond'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_state'" );
  $kliendiAndmed['riik'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_country'" );
  $kliendiAndmed['regnr'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_regnr'" );
  $kliendiAndmed['vat'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_billing_vat'" );
  $tarneAndmed['eesnimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_first_name'" );
  $tarneAndmed['perenimi'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_last_name'" );
  $tarneAndmed['firma'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_company'" );
  $tarneAndmed['aadress1'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_address_1'" );
  $tarneAndmed['aadress2'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_address_2'" );
  $tarneAndmed['linn'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_city'" );
  $tarneAndmed['indeks'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_postcode'" );
  $tarneAndmed['maakond'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_state'" );
  $tarneAndmed['riik'] = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_shipping_country'" );
}

if ($kliendiAndmed['firma'] != '' && $kliendiAndmed['regnr'] != '') {
$kliendikood = implode(array($xml->CheckRegNum[0]->CustID));
//var_dump($kliendikood);
} else {
$kliendikood = 'EK1000';
//var_dump($kliendikood);
}

$soapUrl = "xxx"; // asmx URL of WSDL
$soapUser = "yyy";  //  username
$soapPassword = "zzz"; // password
$envelopeHeader = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Header><h:CallContext xmlns:h="http://schemas.microsoft.com/dynamics/2010/01/datacontracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><h:Company i:nil="true"/><h:Language i:nil="true"/><h:LogonAsUser i:nil="true"/><h:MessageId i:nil="true"/><h:PartitionKey i:nil="true"/><h:PropertyBag i:nil="true" xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"/></h:CallContext></s:Header><s:Body><WoocommerceServiceGetAnswerRequest xmlns="http://tempuri.org"><_request>';
$envelopeEnd = '</_request></WoocommerceServiceGetAnswerRequest></s:Body></s:Envelope>';

$envelopeContentStart = '&lt;VPXRequest&gt;&lt;SetOrder&gt;&lt;RequestID&gt;&lt;/RequestID&gt;&lt;E-Document&gt;&lt;Header&gt;&lt;Test&gt;no&lt;/Test&gt;&lt;/Header&gt;&lt;Document&gt;&lt;DocumentType&gt;order&lt;/DocumentType&gt;&lt;DocumentParties&gt;&lt;BuyerParty&gt;&lt;PartyCode&gt;' . $kliendikood . '&lt;/PartyCode&gt;&lt;Name&gt;' . $kliendiAndmed['firma'] . '&lt;/Name&gt;&lt;RegNum&gt;' . $kliendiAndmed['regnr'] . '&lt;/RegNum&gt;&lt;VATRegNum&gt;' . $kliendiAndmed['vat'] . '&lt;/VATRegNum&gt;&lt;ContactData&gt;&lt;PhoneNum&gt;' . $kliendiAndmed['telefon'] . '&lt;/PhoneNum&gt;&lt;ContactInfo extensionId="address"&gt;&lt;InfoContent&gt;' . $kliendiAndmed['aadress2'] . $kliendiAndmed['aadress1'] . ', ' . $kliendiAndmed['indeks'] . ' ' . $kliendiAndmed['linn'] . '&lt;/InfoContent&gt;&lt;/ContactInfo&gt;&lt;EmailAddress&gt;' . $kliendiAndmed['epost'] . '&lt;/EmailAddress&gt;&lt;ActualAddress&gt;&lt;Address1&gt;' . $kliendiAndmed['aadress2'] . $kliendiAndmed['aadress1'] . '&lt;/Address1&gt;&lt;City&gt;' . $kliendiAndmed['linn'] . '&lt;/City&gt;&lt;PostalCode&gt;' . $kliendiAndmed['indeks'] . '&lt;/PostalCode&gt;&lt;County&gt;' . $kliendiAndmed['maakond'] . '&lt;/County&gt;&lt;CountryCode&gt;' . $kliendiAndmed['riik'] . '&lt;/CountryCode&gt;&lt;/ActualAddress&gt;&lt;/ContactData&gt;&lt;/BuyerParty&gt;&lt;DeliveryParty&gt;&lt;PartyCode&gt;' . $kliendikood . '&lt;/PartyCode&gt;&lt;Name&gt;' . $tarneAndmed['firma'] . '&lt;/Name&gt;&lt;ContactData&gt;&lt;PhoneNum&gt;' . $kliendiAndmed['telefon'] . '&lt;/PhoneNum&gt;&lt;ContactInfo extensionId="delivery-address"&gt;&lt;InfoContent&gt;' . $tarneAndmed['aadress1'] . '-' . $tarneAndmed['aadress2'] . ', ' . $tarneAndmed['indeks'] . ' ' . $tarneAndmed['linn'] . '&lt;/InfoContent&gt;&lt;/ContactInfo&gt;&lt;EmailAddress&gt;' . $kliendiAndmed['epost'] . '&lt;/EmailAddress&gt;&lt;ActualAddress&gt;&lt;Address1&gt;' . $tarneAndmed['aadress1'] . '-' . $tarneAndmed['aadress2'] . '&lt;/Address1&gt;&lt;City&gt;' . $tarneAndmed['linn'] . '&lt;/City&gt;&lt;PostalCode&gt;' . $tarneAndmed['indeks'] . '&lt;/PostalCode&gt;&lt;County&gt;' . $tarneAndmed['maakond'] . '&lt;/County&gt;&lt;CountryCode&gt;' . $tarneAndmed['riik'] . '&lt;/CountryCode&gt;&lt;/ActualAddress&gt;&lt;/ContactData&gt;&lt;/DeliveryParty&gt;&lt;/DocumentParties&gt;&lt;DocumentInfo&gt;&lt;DocumentNum&gt;' . $order_id . '&lt;/DocumentNum&gt;&lt;DateInfo&gt;&lt;OrderDate&gt;&lt;/OrderDate&gt;&lt;DeliveryDateRequested&gt;&lt;/DeliveryDateRequested&gt;&lt;/DateInfo&gt;&lt;CreatedByContact&gt;&lt;ContactFirstName&gt;' . $kliendiAndmed['eesnimi'] . ' ' . $kliendiAndmed['perenimi'] . '&lt;/ContactFirstName&gt;&lt;PhoneNum&gt;' . $kliendiAndmed['telefon'] . '&lt;/PhoneNum&gt;&lt;EmailAddress&gt;' . $kliendiAndmed['epost'] . '&lt;/EmailAddress&gt;&lt;/CreatedByContact&gt;&lt;/DocumentInfo&gt;&lt;DocumentItem&gt;';

//var_dump($envelopeContentStart);
//Arve ridade koostaja
    
$nimekiri = array();

foreach( $order->get_items() as $item_id => $item ){
  $product = $item->get_product();
  $product_id = $item->get_product_id();
  $gtin = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $product_id . " AND meta_key = '_rank_math_gtin_code'");
  if ($gtin == NULL) {
    $gtin = '';
  }
  //var_dump($gtin);
  $unit = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $product_id . " AND meta_key = 'muugiuhik'" );
  $sku = $product->get_sku();
  $quantity = number_format($item->get_quantity(),5,".","'");
      $product_name = $item->get_name();
      $total = $item->get_subtotal();

      $tooted = '&lt;ItemEntry&gt;&lt;SellerItemCode&gt;' . $sku . '&lt;/SellerItemCode&gt;&lt;GTIN&gt;' . $gtin . '&lt;/GTIN&gt;&lt;ItemDescription&gt;' . $product_name . '&lt;/ItemDescription&gt;&lt;BaseUnit&gt;' . $unit . '&lt;/BaseUnit&gt;&lt;AmountOrdered&gt;' . $quantity . '&lt;/AmountOrdered&gt;&lt;ItemSum&gt;' . $total . '&lt;/ItemSum&gt;&lt;/ItemEntry&gt;';
  array_push($nimekiri, $tooted);
}

//Transport
//var_dump($order_id);
$shipping_method = @array_shift($order->get_shipping_methods());
//var_dump($shipping_method);

//09.04.2025 - Mario: Vana kulleri muudan cargosoni kulleri vastu
//Shipping method "Cargosoni kullerid"
if ($shipping_method['method_id'] == 'cargoson_shipping') {

  $deliveryCost = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_order_shipping'");

  $taxRate = $wpdb->get_var( "SELECT tax_rate FROM $wpdb->woocommerce_tax_rates WHERE tax_rate_country = 'EE'");

  $transport = '&lt;ItemEntry&gt;&lt;SellerItemCode&gt;TRANSPORT&lt;/SellerItemCode&gt;&lt;GTIN&gt;&lt;/GTIN&gt;&lt;ItemDescription&gt;TRANSPORT&lt;/ItemDescription&gt;&lt;ItemUnitRecord&gt;&lt;ItemUnit&gt;tk&lt;/ItemUnit&gt;&lt;ItemPrice&gt;' . $deliveryCost . '&lt;/ItemPrice&gt;&lt;/ItemUnitRecord&gt;&lt;BaseUnit&gt;tk&lt;/BaseUnit&gt;&lt;AmountOrdered&gt;1&lt;/AmountOrdered&gt;&lt;ItemPrice&gt;' . $deliveryCost . '&lt;/ItemPrice&gt;&lt;ItemBasePrice&gt;' . $deliveryCost . '&lt;/ItemBasePrice&gt;&lt;ItemSum&gt;' . $deliveryCost . '&lt;/ItemSum&gt;&lt;VAT vatID="TAX"&gt;&lt;VATRate&gt;' . $taxRate . '&lt;/VATRate&gt;&lt;/VAT&gt;&lt;/ItemEntry&gt;';
  array_push($nimekiri, $transport);

  //Delivery conditions

  $delivery = '&lt;AdditionalInfo&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryMethod&lt;/InfoName&gt;&lt;InfoContent&gt;Kuller&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryCondition&lt;/InfoName&gt;&lt;InfoContent&gt;DDP&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;/AdditionalInfo&gt;';
}

//Shipping method "Klient"
if ($shipping_method['method_id'] == 'local_pickup') {

  //Delivery conditions

  $delivery = '&lt;AdditionalInfo&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryMethod&lt;/InfoName&gt;&lt;InfoContent&gt;Klient&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryCondition&lt;/InfoName&gt;&lt;InfoContent&gt;FCA&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;/AdditionalInfo&gt;';
}

//09.04.2025 - Mario: Vana smartposti muudan cargosoni pakiautomaatide vastu
//Shipping method "Cargosoni pakiautomaadid"
if ($shipping_method['method_id'] == 'cargoson_parcels') {

  $deliveryCost = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $order_id . " AND meta_key = '_order_shipping'");

  $taxRate = $wpdb->get_var( "SELECT tax_rate FROM $wpdb->woocommerce_tax_rates WHERE tax_rate_country = 'EE'");

  $transport = '&lt;ItemEntry&gt;&lt;SellerItemCode&gt;TRANSPORT&lt;/SellerItemCode&gt;&lt;GTIN&gt;&lt;/GTIN&gt;&lt;ItemDescription&gt;TRANSPORT&lt;/ItemDescription&gt;&lt;ItemUnitRecord&gt;&lt;ItemUnit&gt;tk&lt;/ItemUnit&gt;&lt;ItemPrice&gt;' . $deliveryCost . '&lt;/ItemPrice&gt;&lt;/ItemUnitRecord&gt;&lt;BaseUnit&gt;tk&lt;/BaseUnit&gt;&lt;AmountOrdered&gt;1&lt;/AmountOrdered&gt;&lt;ItemPrice&gt;' . $deliveryCost . '&lt;/ItemPrice&gt;&lt;ItemBasePrice&gt;' . $deliveryCost . '&lt;/ItemBasePrice&gt;&lt;ItemSum&gt;' . $deliveryCost . '&lt;/ItemSum&gt;&lt;VAT vatID="TAX"&gt;&lt;VATRate&gt;' . $taxRate . '&lt;/VATRate&gt;&lt;/VAT&gt;&lt;/ItemEntry&gt;';
  array_push($nimekiri, $transport);

  //Delivery conditions

  $delivery = '&lt;AdditionalInfo&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryMethod&lt;/InfoName&gt;&lt;InfoContent&gt;Smartpost&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;Extension extensionId="remark"&gt;&lt;InfoName&gt;deliveryCondition&lt;/InfoName&gt;&lt;InfoContent&gt;DDP&lt;/InfoContent&gt;&lt;/Extension&gt;&lt;/AdditionalInfo&gt;';
}

$nimekiriString = implode('', $nimekiri);

if ($delivery == NULL) $delivery = '';

$itemsEnd = '&lt;/DocumentItem&gt;';
$envelopeContentEnd = '&lt;/Document&gt;&lt;/E-Document&gt;&lt;/SetOrder&gt;&lt;/VPXRequest&gt;';

$xml_post_string = $envelopeHeader . $envelopeContentStart . $nimekiriString . $itemsEnd . $delivery . $envelopeContentEnd . $envelopeEnd;

//var_dump($xml_post_string);

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