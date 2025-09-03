<?php
#Tooteandmete cron job, PIM#

function update_product_data_cron(){
    global $product;
    global $wpdb;
    global $tootedMassiiv;

    $koikTooted = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_sku'" );

    $tootedMassiiv = json_decode(json_encode($koikTooted), true);
    $tootedHulk = count($tootedMassiiv);

    //PIM api token genereerimine
    $curl = curl_init();

    curl_setopt_array($curl, array(
    //CURLOPT_URL => 'pim.vipex.ee/api/rest/v1',
    //CURLOPT_URL => 'pim.vipex.ee/api/oauth/v1/token',
    CURLOPT_URL => 'https://pim.vipex.ee/api/oauth/v1/token',

    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{
        "username" : "xxx",
        "password" : "yyy",
        "grant_type": "password"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Basic zzz'
    )
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $tokenObject = (array) json_decode($response);
    $token = $tokenObject['access_token'];
    $tokenString = 'Authorization: Bearer ' . $token;
    $headerArray = array('Content-Type: application/json', $tokenString);

    //Tooteandmete uuendamine ükshaaval
    for ($x=0; $x < $tootedHulk ; $x++) {
        
        $postID = (int) $tootedMassiiv[$x]['post_id']; 
        $tootekood = $tootedMassiiv[$x]['meta_value'];
        
        //Tooteandmete uuendamine PIM-ist.

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pim.vipex.ee/api/rest/v1/products/' . $tootekood,
            //CURLOPT_URL => 'https://pim.vipex.ee/api/rest/v1/products/S2800117',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "username" : "xxx",
                "password" : "yyy",
                "grant_type": "password"
            }',
            CURLOPT_HTTPHEADER => $headerArray
        ));

        $response = curl_exec($curl);
        $textObject = json_decode($response, true);

        //Nimi eesti keeles

        if (isset($textObject['values']['product_name_short'])) {
            $arrLength = count($textObject['values']['product_name_short']);
            for ($i=0; $i < $arrLength ; $i++) {
            $nameArray = $textObject['values']['product_name_short'][$i];
                foreach ($nameArray as $key => $value) {
                //echo "$key = $value <br>";
                if ($value == 'et_EE' & $value != NULL) {
                    //echo 'product_name_short(EE): ' . $nameArray['data'] . '<br>';
                    $wpdb->update( $wpdb->posts, array( 'post_title' => $nameArray['data']), array('ID'=> $postID));
                }
                }
            }
        }

        if (isset($textObject['values']['product_description_short'])) {
            $arrLength = count($textObject['values']['product_description_short']);
            for ($i=0; $i < $arrLength ; $i++) { 
                $nameArray = $textObject['values']['product_description_short'][$i];
                foreach ($nameArray as $key => $value) {
                //echo "$key = $value <br>";
                if ($value == 'et_EE' & $value != NULL) {
                    //echo 'product_description_short(EE): ' . $nameArray['data'] . '<br>';
                    $wpdb->update( $wpdb->posts, array( 'post_excerpt' => $nameArray['data']), array('ID'=> $postID));
                }
                }
            }
        }

        if (isset($textObject['values']['product_description_long'])) {
            $arrLength = count($textObject['values']['product_description_long']);
            //var_dump($arrLength);
            for ($i=0; $i < $arrLength ; $i++) { 
            $nameArray = $textObject['values']['product_description_long'][$i];
                foreach ($nameArray as $key => $value) {
                //echo "$key = $value <br>";
                if ($value == 'et_EE' & $value != NULL) {
                    //echo 'product_description_long(EE): ' . $nameArray['data'] . '<br>';
                    $wpdb->update( $wpdb->posts, array( 'post_content' => $nameArray['data']), array('ID'=> $postID));
                }
                }
            }
        }

        
        if (isset($textObject['values']['total_weight']['0']['data']['amount'])) {
            $weight = $textObject['values']['total_weight']['0']['data']['amount'];

            //Andmevälja olemasolu kontroll
            $dataFieldCheck1 = $wpdb->get_var( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = " . $postID . " AND meta_key = '_weight'" );
            if ($dataFieldCheck1 == '_weight')  {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $weight + 0), array('post_id'=> $postID, 'meta_key'=>'_weight'));
            }
            if ($dataFieldCheck1 == '') {
                $wpdb->insert($wpdb->postmeta, array(
                'post_id' => $postID,
                'meta_key' => '_weight',
                'meta_value' => $weight + 0));  
            }
        }
        
        if (isset($textObject['values']['package_depth']['0']['data']['amount'])) {
            $depth = $textObject['values']['package_depth']['0']['data']['amount'];
            $dataFieldCheck2 = $wpdb->get_var( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = " . $postID . " AND meta_key = '_length'" );
            if ($dataFieldCheck2 == '_length')  {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $depth + 0), array('post_id'=> $postID, 'meta_key'=>'_length'));
            } else {
                $wpdb->insert($wpdb->postmeta, array(
                'post_id' => $postID,
                'meta_key' => '_length',
                'meta_value' => $depth + 0));  
            } 
        }

        if (isset($textObject['values']['package_width']['0']['data']['amount'])) {
            $width = $textObject['values']['package_width']['0']['data']['amount'];
            $dataFieldCheck3 = $wpdb->get_var( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = " . $postID . " AND meta_key = '_width'" );
            if ($dataFieldCheck3 == '_width')  {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $width + 0), array('post_id'=> $postID, 'meta_key'=>'_width'));
            } else {
                $wpdb->insert($wpdb->postmeta, array(
                'post_id' => $postID,
                'meta_key' => '_width',
                'meta_value' => $width + 0));  
            } 
        }

        if (isset($textObject['values']['package_height']['0']['data']['amount'])) {
            $height = $textObject['values']['package_height']['0']['data']['amount']; 
            $dataFieldCheck4 = $wpdb->get_var( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = " . $postID . " AND meta_key = '_height'" );
            if ($dataFieldCheck4 == '_height')  {
                $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $height + 0), array('post_id'=> $postID, 'meta_key'=>'_height'));
            } else {
                $wpdb->insert($wpdb->postmeta, array(
                'post_id' => $postID,
                'meta_key' => '_height',
                'meta_value' => $height + 0));  
            } 
        }

        curl_close($curl);

        $postID = '';
        $tootekood = '';
    }
}
//add_shortcode( 'update_product_data_pim', 'update_product_data_cron' );
