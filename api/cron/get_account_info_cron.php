<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_account_info_cron( $post_id ) {
    
    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_account_info_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_account_info_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;

    $reply =  array();
    $request = $api->action('accountinfo');
    
    if (is_array($request)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $key1 => $val1) {
          if ($key1 === 'ERROR') {
            $reply = array('MSG' => 'ERROR', 'MESSAGE' => 'Authentication Failed');
          }
          if ($key1 === 'SUCCESS') {
            if (is_array($val1)) {
              foreach ($val1 as $key2 => $val2) {
                if (is_array($val2)) {
                  foreach ($val2 as $key3 => $val3) {
                    if ($key3 === 'message') {
                      $tmp_msg = $val3;
                    }
                    if ($key3 === 'AccoutInfo') {
                      $active_api = TRUE;
                      if (is_array($val3)) {
                        $tmp_credit = '';
                        $tmp_mail = '';
                        $tmp_currency = '';
                        foreach ($val3 as $key4 => $val4) {
                          if ($key4 === 'credit') {
                            $tmp_credit = $val4;
                          }
                          if ($key4 === 'mail') {
                            $tmp_mail = $val4;
                          }
                          if ($key4 === 'currency') {
                            $tmp_currency = $val4;
                          }
                          if ($tmp_currency != '') {
                            $reply = array(
                              'MSG' => $tmp_msg,
                              'CREDIT' => $tmp_credit,
                              'MAIL' => $tmp_mail,
                              'CURRENCY' => $tmp_currency,
                            );
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
         }
    }
    
    return $reply;

}

?>