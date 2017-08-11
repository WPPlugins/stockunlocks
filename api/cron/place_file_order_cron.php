<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_place_file_order_cron( $post_id ) {
    
    $suwp_dhru_referenceid = '113'; // not yet implemented, obtained from ...
    $suwp_dhru_filename = 'ORDERID31TEST.txt'; // not yet implemented
    $suwp_dhru_filedata = base64_encode('TESTDATA'); // not yet implemented
    
    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/place_file_order_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/place_file_order_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;
    
    $reply =  array();
    $para['ID'] = $suwp_dhru_referenceid;
    $para['FILENAME'] = $suwp_dhru_filename;
    $para['FILEDATA'] = $suwp_dhru_filedata;
    $request = $api->action('placefileorder',$para);
    
    return $request;
    
}

?>