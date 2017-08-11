<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_provider_list_cron( $post_id ) {
    
    $suwp_dhru_referenceid = ''; // not yet implemented, obtained from 'imeiservicelist' [SERVICEID]

    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_provider_list_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_provider_list_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;

    $reply =  array();
    $services = array();
    $flag_continue = FALSE;
    $para['ID'] = $suwp_dhru_referenceid; 
    $request = $api->action('providerlist', $para);
  
    if (is_array($request)) {
      $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
      $tmp_serviceid = '';
      foreach ($iterator as $key1 => $val1) {
        $tmp_succ_result = '';
        $tmp_succ_msg = '';
        $tmp_err_result = '';
        $tmp_err_msg = '';
        if ($key1 === 'ID') {
          $tmp_serviceid = $val1;
        }
        if ($key1 === 'ERROR') {
          $tmp_err_result = $key1;
          $tmp_err_msg = "No Parameter 'ID' submitted, returning all Country and Provider List";
        }
        if ($key1 === 'SUCCESS') {
          $tmp_succ_result = $key1;
          $tmp_succ_msg = 'Country and Provider List';
        }
        if ($key1 === 'LIST') {
          if (is_array($val1)) {
            foreach ($val1 as $key2 => $val2) {
              $tmp_id = $key2;
              if (is_array($val2)) {
                foreach ($val2 as $key3 => $val3) {
                  if ($key3 === 'NAME') {
                    $tmp_name = $val3;
                  }
                  if ($key3 === 'PROVIDERS') {
                    $flag_continue = TRUE;
                    if (is_array($val3)) {
                      foreach ($val3 as $key4 => $val4) {
                        if (is_array($val4)) {
                          $tmp_providername_id = '';
                          $tmp_providername = '';
                          foreach ($val4 as $key5 => $val5) {
                            if ($key5 === 'ID') {
                              $tmp_providername_id = $val5;
                            }
                            if ($key5 === 'NAME') {
                              $tmp_providername = $val5;
                            }
                            if ($tmp_providername != '') {
                              $tmp_key = $tmp_id . '-php-' . $tmp_name . '-php-' . $tmp_providername_id . '-php-' . $tmp_providername;
                              $tmp_key_provider_id_display = $tmp_providername_id;
                              $services[] = array(
                                'ID' => $tmp_key,
                                'COUNTRY' => $tmp_name,
                                'PROVIDERID' => $tmp_key_provider_id_display,
                                'PROVIDERNAME' => $tmp_providername,
                                'SERVICEID' => $tmp_serviceid,
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
      }
    }
  
    $item = 0;
        
    if ( $flag_continue ) {

        foreach ($services as $service) {
          // Begin extraction at the actual network provider.
          if ((!empty($service['ID']) && ($service['ID'] != NULL))) {
            $reply[$service['ID']] = array(
              'item' => $item,
              'country' => $service['COUNTRY'],
              'providerid' => $service['PROVIDERID'],
              'provider' => $service['PROVIDERNAME'],
            );
            ++$item;
          }
        }
      
    }
    
    return $reply;
    
}

?>