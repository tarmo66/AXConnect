<?php
#Ettevõtte väljad#

#Admin lehel kasutaja reg.kood ja kmkr väljad#

add_action( 'show_user_profile', 'extra_user_profile_fields5' );
add_action( 'edit_user_profile', 'extra_user_profile_fields5' );

function extra_user_profile_fields5( $user_id ) {
    if (! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
        $user_id = $_GET['user_id'];
    }
?>

<table class="form-table">
<tr>
    <th><label for="billing_vat">KMKR nr</label></th>
    <td>
        <input type="text" name="billing_vat" id="billing_vat" value="<?php echo esc_attr( get_the_author_meta( 'billing_vat', $user_id ) ); ?>" class="regular-text" /><br />
        <span class="description"><?php _e("KMKR"); ?></span>
    </td>
</tr>
</table>    
<?php }

add_action( 'personal_options_update', 'save_extra_user_profile_fields5' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields5' );

function save_extra_user_profile_fields5( $user_id ) {
    if (! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
        $user_id = $_GET['user_id'];
    }

    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        return;
    }
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'billing_vat', $_POST['billing_vat'] );
    //update_user_meta( $user_id, 'Discount', $_POST['Discount'] );
}

add_action( 'show_user_profile', 'extra_user_profile_fields6' );
add_action( 'edit_user_profile', 'extra_user_profile_fields6' );

function extra_user_profile_fields6( $user_id ) {
    if (! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
        $user_id = $_GET['user_id'];
    }
?>

<table class="form-table">
<tr>
    <th><label for="billing_regnr">Reg.kood</label></th>
    <td>
        <input type="text" name="billing_regnr" id="billing_regnr" value="<?php echo esc_attr( get_the_author_meta( 'billing_regnr', $user_id ) ); ?>" class="regular-text" /><br />
        <span class="description"><?php _e("Reg.kood"); ?></span>
    </td>
</tr>
</table>    
<?php }

add_action( 'personal_options_update', 'save_extra_user_profile_fields6' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields6' );

function save_extra_user_profile_fields6( $user_id ) {
    if (! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
        $user_id = $_GET['user_id'];
    }

    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        return;
    }
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'billing_regnr', $_POST['billing_regnr'] );
    //update_user_meta( $user_id, 'Discount', $_POST['Discount'] );
}

#Firma, reg.kood ja kmkr väljad kasutaja seadetes ja kassa lehel#

add_action( 'woocommerce_edit_account_form', 'misha_add_field_edit_account_form1' );
// or add_action( 'woocommerce_edit_account_form_start', 'misha_add_field_edit_account_form' );
function misha_add_field_edit_account_form1() {

    woocommerce_form_field(
        'billing_company',
        array(
            'type'        => 'text',
            'required'    => true, // remember, this doesn't make the field required, just adds an "*"
            'label'       => 'Firma',
            'description' => '',
        ),
        get_user_meta( get_current_user_id(), 'billing_company', true ) // get the data
    );

}

add_action( 'woocommerce_save_account_details', 'misha_save_account_details1' );
function misha_save_account_details1( $user_id ) {

    update_user_meta( $user_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );

}

add_action( 'woocommerce_edit_account_form', 'misha_add_field_edit_account_form2' );
function misha_add_field_edit_account_form2() {

    woocommerce_form_field(
        'billing_regnr',
        array(
            'type'        => 'text',
            'required'    => true, // remember, this doesn't make the field required, just adds an "*"
            'label'       => 'Registrinumber',
            'description' => '',
        ),
        get_user_meta( get_current_user_id(), 'billing_regnr', true ) // get the data
    );

}

add_action( 'woocommerce_save_account_details', 'misha_save_account_details2' );
function misha_save_account_details2( $user_id ) {

    update_user_meta( $user_id, 'billing_regnr', sanitize_text_field( $_POST['billing_regnr'] ) );

}

add_action( 'woocommerce_edit_account_form', 'misha_add_field_edit_account_form3' );
// or add_action( 'woocommerce_edit_account_form_start', 'misha_add_field_edit_account_form' );
function misha_add_field_edit_account_form3() {

    woocommerce_form_field(
        'billing_vat',
        array(
            'type'        => 'text',
            'required'    => true, // remember, this doesn't make the field required, just adds an "*"
            'label'       => 'KMKR nr',
            'description' => '',
        ),
        get_user_meta( get_current_user_id(), 'billing_vat', true ) // get the data
    );

}

add_action( 'woocommerce_save_account_details', 'misha_save_account_details3' );
function misha_save_account_details3( $user_id ) {

    update_user_meta( $user_id, 'billing_vat', sanitize_text_field( $_POST['billing_vat'] ) );

}

#Kassa lehel ettevõtte väljad#

add_filter( 'woocommerce_checkout_fields' , 'bbloomer_display_checkbox_and_new_checkout_field' );
function bbloomer_display_checkbox_and_new_checkout_field( $fields ) {

$userID = get_current_user_id();

$company = get_user_meta( get_current_user_id(), 'billing_company', true ); // get the data
if ( empty( $company )) {  
    $fields['billing']['checkbox_trigger'] = array(
        'type'      => 'checkbox',
        'label'     => __('Ettevõte', 'woocommerce'),
        'class'     => array('form-row-wide'),
        'clear'     => false
    );
    $fields['billing']['checkbox_trigger']['priority'] = 33;
}

$fields['billing']['billing_company'] = array(
    'label' => __('Ettevõte', 'woocommerce'), // Add custom field label
    'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => false, // if field is required or not
    'clear' => true, // add clear or not
    'type' => 'text', // add field type
    'class'     => array('form-row-wide'),
);
$fields['billing']['billing_company']['priority'] = 34;

$fields['billing']['billing_regnr'] = array(
    'label' => __('Registrinumber', 'woocommerce'), // Add custom field label
    'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => false, // if field is required or not
    'clear' => true, // add clear or not
    'type' => 'text', // add field type
    'class'     => array('form-row-wide'),
);
$fields['billing']['billing_regnr']['priority'] = 35;


$fields['billing']['billing_vat'] = array(
    'label' => __('Käibemaksukohuslase number', 'woocommerce'), // Add custom field label
    'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => false, // if field is required or not
    'clear' => true, // add clear or not
    'type' => 'text', // add field type
    'class'     => array('form-row-wide'),
);
$fields['billing']['billing_vat']['priority'] = 36;
  
return $fields;
}

//Ettevõte reg.koodi väli nõutud, kui ettevõtte nime väli täidetud.

add_action( 'woocommerce_after_checkout_validation', 'misha_validate_regnr', 10, 2);
function misha_validate_regnr( $fields, $errors ){

    if ( $fields[ 'billing_company' ] != '' && $fields[ 'billing_regnr' ] == ''){
        $errors->add( 'validation', 'Puudub ettevõte registrinumber' );
    }

    
    if ($fields[ 'billing_regnr' ] != '' && (strlen($fields[ 'billing_regnr' ]) <= '3')) {
        $errors->add( 'validation', 'Registrikood liiga lühike' );
    }
}

function filter_woocommerce_form_field_text( $field, $key, $args, $value ) {
// Based on key
    //var_dump(esc_html__( 'optional', 'woocommerce' ));
if ( $key == 'billing_regnr' ) {
$optionalString = '(' . esc_html__( 'optional', 'woocommerce' ) . ')';
$optionalStringReplace = '<span class="optional">' . $optionalString . '</span>';
$field = str_replace( $optionalStringReplace, '<abbr class="required" title="required">*</abbr>', $field );
$field = str_replace( 'form-row-wide', 'form-row-wide validate-required', $field );
}
return $field;
}
add_filter( 'woocommerce_form_field_text', 'filter_woocommerce_form_field_text', 10, 4 );

//Ettevõte väljad peidetud

$company = get_user_meta( get_current_user_id(), 'billing_company', true ); // get the data  
//var_dump($company);
if ($company == '') {
    add_action( 'woocommerce_after_checkout_form', 'bbloomer_conditionally_hide_show_new_field', 9999 );
    function bbloomer_conditionally_hide_show_new_field() {
        
      wc_enqueue_js( "
          jQuery('input#checkbox_trigger').change(function(){
               
             if (! this.checked) {
                // HIDE IF NOT CHECKED
                jQuery('#new_billing_field_field, #billing_company_field, #billing_regnr_field, #billing_vat_field').fadeOut();
                jQuery('#new_billing_field_field input, #billing_company_field input, #billing_regnr_field input, #billing_vat_field input').val('');         
             } else {
                // SHOW IF CHECKED
                jQuery('#new_billing_field_field, #billing_company_field, #billing_regnr_field, #billing_vat_field').fadeIn();
             }
               
          }).change();
      ");
           
    }
}