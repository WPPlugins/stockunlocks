<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_file_order_details_cron( $post_id ) {
    
    $suwp_dhru_referenceid = ''; // not yet implemented

    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_file_order_details_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_file_order_details_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;

    $reply =  array();
    $para['ID'] = $suwp_dhru_referenceid;
    $reply = $api->action('getfileorder',$para);
    
    return $reply;

}

?>