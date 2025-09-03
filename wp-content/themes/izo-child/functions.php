<?php 

//Axapta Woocommerce liidestused. (kasuta pealkirju otsingus)
#Kliendihinna küsimine axaptast#
#Ostukorvis hindade asendamine Axapta hindadega#
#Laoseisude cron job, Axapta#
#Tooteandmete cron job, PIM#
#E-maili olemasolu kontroll Axaptas kasutaja loomisel#
#Ettevõtte väljad#
#Sisselogimata kasutaja tellimus# /* wp-content/themes/izo-child/woocommerce/order/order-details.php */
#Kliendikoodi loomine# /* wp-content/themes/izo-child/woocommerce/order/order-details.php */
#Krediidilimiidi kontroll# /* wp-content\plugins\wc-invoice-gateway\classes\class-wc-invoice-gateway.php */
	 
/* Do not remove this function */	 
function izo_child_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); 
} 
add_action( 'wp_enqueue_scripts', 'izo_child_enqueue_styles' );


/**
 * Remove Image Zoom on hover
 */
function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'wp', 'remove_image_zoom_support', 100 );

/**
 * Add linkable attributes to products
 * Register term fields
 */
add_action( 'init', 'register_attributes_url_meta' );
function register_attributes_url_meta() {
        $attributes = wc_get_attribute_taxonomies();

        foreach ( $attributes as $tax ) {
            $name = wc_attribute_taxonomy_name( $tax->attribute_name );

            add_action( $name . '_add_form_fields', 'add_attribute_url_meta_field' );
            add_action( $name . '_edit_form_fields', 'edit_attribute_url_meta_field', 10 );
            add_action( 'edit_' . $name, 'save_attribute_url' );
            add_action( 'create_' . $name, 'save_attribute_url' );
        }
}

/**
 * Add term fields form
 */
function add_attribute_url_meta_field() {

    wp_nonce_field( basename( __FILE__ ), 'attrbute_url_meta_nonce' );
    ?>

    <div class="form-field">
        <label for="attribute_url"><?php _e( 'URL', 'domain' ); ?></label>
        <input type="url" name="attribute_url" id="attribute_url" value="" />
    </div>
    <?php
}

/**
 * Edit term fields form
 */
function edit_attribute_url_meta_field( $term ) {

    $url = get_term_meta( $term->term_id, 'attribute_url', true );
    wp_nonce_field( basename( __FILE__ ), 'attrbute_url_meta_nonce' );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="attribute_url"><?php _e( 'URL', 'domain' ); ?></label></th>
        <td>
            <input type="url" name="attribute_url" id="attribute_url" value="<?php echo esc_url( $url ); ?>" />
        </td>
    </tr>
    <?php
}

/**
 * Save term fields
 */
function save_attribute_url( $term_id ) {
    if ( ! isset( $_POST['attribute_url'] ) || ! wp_verify_nonce( $_POST['attrbute_url_meta_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    $old_url = get_term_meta( $term_id, 'attribute_url', true );
    $new_url = esc_url( $_POST['attribute_url'] );


    if ( ! empty( $old_url ) && $new_url === '' ) {
        delete_term_meta( $term_id, 'attribute_url' );
    } else if ( $old_url !== $new_url ) {
        update_term_meta( $term_id, 'attribute_url', $new_url, $old_url );
    }
}

/**
 * Show term URL
 */
add_filter( 'woocommerce_attribute', 'make_product_atts_linkable', 10, 3 );
function make_product_atts_linkable( $text, $attribute, $values ) {
    $new_values = array();
    foreach ( $values as $value ) {

        if ( $attribute['is_taxonomy'] ) {
            $term = get_term_by( 'name', $value, $attribute['name'] );
            $url = get_term_meta( $term->term_id, 'attribute_url', true );

            if ( ! empty( $url ) ) {
                $val = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $value ) . '">' . $value . '</a>';
                array_push( $new_values, $val );
            } else {
                array_push( $new_values, $value );
            }
        } else {
            $matched = preg_match_all( "/\[([^\]]+)\]\(([^)]+)\)/", $value, $matches );

            if ( $matched && count( $matches ) == 3 ) {
                $val = '<a href="' . esc_url( $matches[2][0] ) . '" title="' . esc_attr( $matches[1][0] ) . '">' . sanitize_text_field( $matches[1][0] ) . '</a>';
                array_push( $new_values, $val );
            } else {
                array_push( $new_values, $value );
            }
        }
    }

    $text = implode( ', ', $new_values );

    return $text;
}

/**
 * Change the order of single product info boxes
*/
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 5 );

//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
//add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 15 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
//add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 50 );

/**
 * @snippet       Stock Quantity @ WooCommerce Shop / Cat / Archive Pages
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.7
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_action( 'woocommerce_after_shop_loop_item', 'bbloomer_show_stock_shop', 10 );  
function bbloomer_show_stock_shop() {
   global $product;
   if ( $product->is_type( 'variable' ) ) echo wc_get_stock_html( $product );
   else if ( $product->get_stock_status() == 'onbackorder' ) echo '';
   else echo wc_get_stock_html( $product );
}

add_filter( 'woocommerce_get_stock_html', 'filter_wc_get_stock_html', 10, 2 );
function filter_wc_get_stock_html( $html, $product ) {
    if ( $product->is_type('variable') && $product->is_in_stock() ) {
        $html = '<p class="stock in-stock">' . __( "Laos", "woocommerce" ) . '</p>';
    }
    return $html;
}

// Change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' ); 
function woocommerce_custom_single_add_to_cart_text() {
    global $product;
    if ( $product->is_type( 'variable' ) && $product->is_in_stock() ) return __( 'Osta', 'woocommerce' );
    //else if ($product->get_stock_quantity()<=0) return __( 'Telli', 'woocommerce' ); 
    else return __( 'Osta', 'woocommerce' );
}

// Change add to cart text on product arhives page
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text' );  
function woocommerce_custom_product_add_to_cart_text() {
    global $product;
    if ( $product->is_type( 'variable' ) && $product->is_in_stock() ) return __( 'Osta', 'woocommerce' );
    else if ($product->get_stock_quantity()<=0) return __( 'Vaata toodet', 'woocommerce' ); 
    else return __( 'Osta', 'woocommerce' );
}

add_action( 'woocommerce_single_product_summary', 'woocommerce_out_of_stock_notice', 21 );
function woocommerce_out_of_stock_notice() {
    global $product;
    if ( $product->get_stock_status() == 'onbackorder' ) echo '';
    else if ( $product->is_type( 'variable' ) ) echo '';
}

// Remove "Any" filter
add_filter ( 'woocommerce_layered_nav_any_label', 'remove_any_from_filter_dropdown', 10, 3 );
function remove_any_from_filter_dropdown ( $sprintf, $taxonomy_label, $taxonomy ) { 
    // filter ...
    $sprintf = sprintf (__( '%s', 'woocommerce'  ), $taxonomy_label );
    return $sprintf ; 
}

add_filter( 'woocommerce_loop_add_to_cart_link', 'replacing_add_to_cart_button', 10, 2 );
function replacing_add_to_cart_button( $button, $product ) {
    if ( $product->get_stock_status() == 'onbackorder' || $product->get_stock_status() == 'outofstock' )
    {
        if ( !has_term( 'Lõpumüük', 'product_tag', $product->get_id() ) )
        {
            $button_text = __('Vaata toodet', 'woocommerce');
            $button = '<a href=' . $product->get_permalink() . ' class="button">' . $button_text . '</a>';
        }       
    }    
    return $button;
}

// Outputing a custom button in Single product pages (you need to set the button link)
function single_product_custom_button()
{
    echo '<button class="button single_add_to_cart_button sg-popup-id-6139" data-popup-id="6139">Küsi pakkumist</button>';
}

// Replacing add-to-cart button in Single product pages
add_action( 'woocommerce_single_product_summary', 'removing_addtocart_buttons', 1 );
function removing_addtocart_buttons()
{
    global $product;

    if ( $product->get_stock_status() == 'onbackorder' || $product->get_stock_status() == 'outofstock' )
    {
        if ( !has_term( 'Lõpumüük', 'product_tag', $product->get_id() ) )
        {
            remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
            add_action( 'woocommerce_simple_add_to_cart', 'single_product_custom_button', 30 );
        }
    }
}


/**
 * @snippet       Min, Max, Increment & Start Value Add to Cart Quantity | WooCommerce
 */  

// Removes the WooCommerce filter, that is validating the quantity to be an int
remove_filter('woocommerce_stock_amount', 'intval');

// Add a filter, that validates the quantity to be a float
add_filter('woocommerce_stock_amount', 'floatval');

add_filter( 'woocommerce_quantity_input_args', 'quantity_changes', 10, 2 );
   
function quantity_changes( $args, $product )
{       
    if ( ! is_cart() )
    {
        if ( is_product() )
        {                
            if( 'm2' === get_field( 'muugiuhik' ) )
            {
                $package_quantity = get_field( 'package_quantity' );
                $args['min_value'] = $package_quantity;
                $args['input_value'] = $package_quantity;
                $args['step'] = $package_quantity;   
            }                 
        }
        else // FiboSearch
        {
            if( 'm2' === get_post_meta( $product->get_id(), 'muugiuhik', true ) )
            {
                $package_quantity = get_post_meta( $product->get_id(), 'package_quantity', true );
                $args['max_value'] = $product->get_stock_quantity();
                $args['input_value'] = $package_quantity;
                $args['min_value'] = $package_quantity;
                $args['step'] = $package_quantity;
            }                
        }
    }
    else // Cart
    {          
        if( 'm2' === get_post_meta( $product->get_id(), 'muugiuhik', true ) )
        {
            $package_quantity = get_post_meta( $product->get_id(), 'package_quantity', true );
            $args['step'] = $package_quantity;
        }
    }        
    return $args;                
    
}

// For Ajax add to cart button (define the min value)
add_filter( 'woocommerce_loop_add_to_cart_args', 'custom_loop_add_to_cart_quantity_arg', 10, 2 );
function custom_loop_add_to_cart_quantity_arg( $args, $product )
{
    if ( is_shop() )
    {
        if( 'm2' === get_field( 'muugiuhik' ) )
        {
            $package_quantity = get_field( 'package_quantity' );
            $args['quantity'] = $package_quantity; 
        }            
    }
    else // FiboSearch
    {
        if( 'm2' === get_post_meta( $product->get_id(), 'muugiuhik', true ) )
        {
            $package_quantity = get_post_meta( $product->get_id(), 'package_quantity', true );
            $args['quantity'] = $package_quantity; 
        }            
    }         
    
    return $args;                
}


/**
 * @snippet       Add Bcc: Recipient @ WooCommerce Completed Order Email
 */
 
add_filter( 'woocommerce_email_headers', 'order_completed_email_add_bcc', 9999, 3 );
 
function order_completed_email_add_bcc( $headers, $email_id, $order ) {
    if ( 'customer_completed_order' == $email_id ) {
        $headers .= "Bcc: Tatjana Simanovskaja <tatjana.simanovskaja@vipex.ee>; Piret Jaansalu <piret.jaansalu@vipex.ee>; It Osakond <it@vipex.ee>" . "\r\n";
    }
    else $headers .= "Bcc: It Osakond <it@vipex.ee>" . "\r\n";
    return $headers;
}


// Remove same tag products from related products section
add_filter( 'woocommerce_get_related_product_tag_terms', 'remove_related_tag_terms' );
function remove_related_tag_terms( $terms ) {
  foreach ( $terms as $key => $term ) {
    unset( $terms[ $key ] );
  }
  return $terms;
}

// Extra description on registration page
add_action( 'woocommerce_register_form_start','cust_add_reg_text' );  
function cust_add_reg_text() {
   echo '<p class="cust-register-description">Hetkel saavad automaatselt registreerida ainult ettevõtete töötajad, kes on meie majandustarkvaras volitatud isikute nimekirjas. Kui soovite veebilehel näha kokkulepitud hindu ja teha tellimusi krediidi alusel, palun saatke meile allkirjastatud volitatud isikute nimekiri kus on kirjas nimi, telefon ja e-maili aadress. <a href="mailto:info@vipex.ee?subject=Volitatud isikud">info@vipex.ee</a></p>';
}


// Order out-of-stock products to the end
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_filter('posts_clauses', 'order_by_stock_status', 2000);
}

function order_by_stock_status($posts_clauses) {
    global $wpdb;
  
    if (is_woocommerce() && (is_shop() || is_product_category() || is_product_tag())) {
	$posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
	$posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
	$posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
    }
	return $posts_clauses;
}

//Notice in the plugin name - custom plugin, don't update
function my_text_strings( $translated_text, $text, $domain )
{
    switch ( $translated_text ) {
        case 'WooCommerce Invoice Gateway' :
            $translated_text = __( 'WooCommerce Invoice Gateway - custom plugin (ära uuenda)', 'WooCommerce Invoice Gateway' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'my_text_strings', 20, 3 );

/* Mario: Dokumentide kuvamine toote kaardil */
function add_product_documents()
{
    global $product;
    $output = '';
    $product_id = $product->get_id();
    $locale = get_locale();

    $manualall = wp_get_attachment_url(get_post_meta($product_id, 'manual-all', true));
    $manualet = wp_get_attachment_url(get_post_meta($product_id, 'manual-et', true));
    $manualen = wp_get_attachment_url(get_post_meta($product_id, 'manual-en', true));
    $manualfi = wp_get_attachment_url(get_post_meta($product_id, 'manual-fi', true));
    $manualru = wp_get_attachment_url(get_post_meta($product_id, 'manual-ru', true));

    $usermanualall = wp_get_attachment_url(get_post_meta($product_id, 'user-manual-all', true));
    $usermanualet = wp_get_attachment_url(get_post_meta($product_id, 'user-manual-et', true));
    $usermanualen = wp_get_attachment_url(get_post_meta($product_id, 'user-manual-en', true));
    $usermanualfi = wp_get_attachment_url(get_post_meta($product_id, 'user-manual-fi', true));
    $usermanualru = wp_get_attachment_url(get_post_meta($product_id, 'manual-ru', true));

    $userandinstallationmanualall = wp_get_attachment_url(get_post_meta($product_id, 'user-and-installation-manual-all', true));
    $userandinstallationmanualet = wp_get_attachment_url(get_post_meta($product_id, 'user-and-installation-manual-et', true));
    $userandinstallationmanualen = wp_get_attachment_url(get_post_meta($product_id, 'user-and-installation-manual-en', true));
    $userandinstallationmanualfi = wp_get_attachment_url(get_post_meta($product_id, 'user-and-installation-manual-fi', true));
    $userandinstallationmanualru = wp_get_attachment_url(get_post_meta($product_id, 'user-and-installation-manual-ru', true));

    $technicaldrawing = wp_get_attachment_url(get_post_meta($product_id, 'technical-drawing', true));
    
    $usermanualvideo = get_post_meta($product_id, 'user-manual-video', true);
    $instructionvideo = get_post_meta($product_id, 'instruction-video', true);

    $output = '<p>';

    if ($manualall)
    {
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-all">%2$s</a>', $manualall, "Paigaldusjuhend");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-all">%2$s</a>', $manualall, "Instruction manual");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-all">%2$s</a>', $manualall, "Asennusohjeet");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-all">%2$s</a>', $manualall, "Инструкции по установке");
    }
    if ($manualet && 'et' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-et">%2$s</a>', $manualet, "Paigaldusjuhend");
    if ($manualen && 'en_US' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-en">%2$s</a>', $manualen, "Instruction manual");
    if ($manualfi && 'fi' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-fi">%2$s</a>', $manualfi, "Asennusohjeet");
    if ($manualru && 'ru_RU' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="manual-ru">%2$s</a>', $manualru, "Инструкции по установке");

    if ($usermanualall)
    {
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-all">%2$s</a>', $usermanualall, "Kasutusjuhend");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-all">%2$s</a>', $usermanualall, "User manual");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-all">%2$s</a>', $usermanualall, "Käyttöohjeet");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-all">%2$s</a>', $usermanualall, "Инструкции по эксплуатации");
    }
    if ($usermanualet && 'et' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-et">%2$s</a>', $usermanualet, "Kasutusjuhend");
    if ($usermanualen && 'en_US' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-en">%2$s</a>', $usermanualen, "User manual");
    if ($usermanualfi && 'fi' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-fi">%2$s</a>', $usermanualfi, "Käyttöohjeet");
    if ($usermanualru && 'ru_RU' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-ru">%2$s</a>', $usermanualru, "Инструкции по эксплуатации");

    if ($userandinstallationmanualall)
    {
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-all">%2$s</a>', $userandinstallationmanualall, "Kasutus- ja paigaldusjuhend");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="muser-and-installation-anual-all">%2$s</a>', $userandinstallationmanualall, "User and installation manual");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-all">%2$s</a>', $userandinstallationmanualall, "Asennus-ja käyttöohjeet");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-all">%2$s</a>', $userandinstallationmanualall, "Инструкции по эксплуатации и установке");
    }
    if ($userandinstallationmanualet && 'et' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-et">%2$s</a>', $userandinstallationmanualet, "Kasutus- ja paigaldusjuhend");
    if ($userandinstallationmanualen && 'en_US' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-en">%2$s</a>', $userandinstallationmanualen, "User and installation manual");
    if ($userandinstallationmanualfi && 'fi' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-fi">%2$s</a>', $userandinstallationmanualfi, "Asennus-ja käyttöohjeet");
    if ($userandinstallationmanualru && 'ru_RU' == $locale) $output .= sprintf('<a href="%1$s" target="_blank" id="user-and-installation-manual-ru">%2$s</a>', $userandinstallationmanualru, "Инструкции по эксплуатации и установке");

    if ($technicaldrawing)
    {
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="technical-drawing">%2$s</a>', $technicaldrawing, "Tehniline joonis");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="technical-drawing">%2$s</a>', $technicaldrawing, "Technical drawing");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="technical-drawing">%2$s</a>', $technicaldrawing, "Tekninen piirustus");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="technical-drawing">%2$s</a>', $technicaldrawing, "Технический рисунок");
    }

    if ($usermanualvideo){
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-video">%2$s</a>', $usermanualvideo, "Kasutusvideo");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-video">%2$s</a>', $usermanualvideo, "User manual video");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-video">%2$s</a>', $usermanualvideo, "Käyttöohjeet video");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="user-manual-video">%2$s</a>', $usermanualvideo, "Видео инструкция по эксплуатации");
    }

    if ($instructionvideo){
        if ( 'et' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="instruction-video">%2$s</a>', $instructionvideo, "Paigaldusvideo");
        if ( 'en_US' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="instruction-video">%2$s</a>', $instructionvideo, "Instruction video");
        if ( 'fi' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="instruction-video">%2$s</a>', $instructionvideo, "Asennus video");
        if ( 'ru_RU' == $locale ) $output .= sprintf('<a href="%1$s" target="_blank" id="instruction-video">%2$s</a>', $instructionvideo, "Видео по установке");
    }

    echo $output . '</p>';
}
add_shortcode( 'product_documents', 'add_product_documents' );

/*
 *  Suure kirjelduse lisamine
 */
/*
add_action( 'woocommerce_after_single_product_summary', 'prima_custom_below_product_summary', 10);
function prima_custom_below_product_summary() {
    global $product;
    $categories = array( 'Vannid');

	if ( has_term( $categories, 'product_cat' , $product->get_id() ) ) echo '<div class="new-description">' . $product->get_description() . '</div>';
}*/

// Arhiivis, üksiku toote ja ostukorvis kuvatavad hinnad
include 'custom/visiblePrices.php';

#Ostukorvis hindade asendamine Axapta hindadega#
include 'axconnect/cartSetPrice.php';

#Laoseisude cron job, Axapta#
include 'axconnect/cronStock.php';

#Baashindade cron job, Axapta#
include 'axconnect/cronPrice.php';

#Tooteandmete cron job, PIM#
include 'axconnect/cronPim.php';

#E-maili olemasolu kontroll Axaptas kasutaja loomisel#
include 'axconnect/clientCode.php';

#Ettevõtte väljad#
include 'axconnect/companyFields.php';

//Change label for WooCommerce dimensions attribute 
function dimensions_label_change( $product_attributes, $product )
{
    if(isset($product_attributes['dimensions']['label'])) {
        $product_attributes['dimensions']['label'] = __( 'Dimensions of package', 'woocommerce' );
    }
    return $product_attributes;
}
//Change product attributes order
function reorder_attributes( $product_attributes, $product )
{
    uksort($product_attributes, 'sort_attributes');
    return $product_attributes;
}
function sort_attributes( $a, $b )
{
    if($a == 'attribute_pa_toote-moodud') {
        return -1;
    }
    if($b == 'attribute_pa_toote-moodud') {
        return 1;
    }
    return 0;
}
add_filter( 'woocommerce_display_product_attributes', 'dimensions_label_change', 10, 2 );
add_filter( 'woocommerce_display_product_attributes', 'reorder_attributes', 10, 2 );
