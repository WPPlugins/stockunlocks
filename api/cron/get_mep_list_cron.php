<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_mep_list_cron( $post_id ) {

    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_mep_list_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_mep_list_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;
    
    $reply =  array();
    $services = array();
    $flag_continue = FALSE;
    $request = $api->action('meplist');
  
    $i = 0;
    if (is_array($request)) {
      $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
      foreach ($iterator as $key1 => $val1) {
        if ($key1 === 'LIST') {
          if (is_array($val1)) {
            foreach ($val1 as $key2 => $val2) {
              $tmp_id = $key2;
              if (is_array($val2)) {
                foreach ($val2 as $key3 => $val3) {
                  if ($key3 === 'NAME') {
                    $flag_continue = TRUE;
                    $tmp_mep_name = $val3;
                    if ($tmp_mep_name != '') {
                      $tmp_key = $tmp_id . '-php-' . $tmp_mep_name;
                      $tmp_key_mep_id_display = $tmp_id;
                      $services[] = array(
                        'ID' => $tmp_key,
                        'MEPID' => $tmp_key_mep_id_display,
                        'MEPNAME' => $tmp_mep_name,
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
    
    $item = 0;
    
    if ( $flag_continue ) {
        
        foreach ($services as $service) {
          // Begin extraction at the actual MEP.
          if ((!empty($service['ID']) && ($service['ID'] != NULL))) {
            $reply[$service['ID']] = array(
              'mepid' => $service['MEPID'],
              'item' => $item,
              'mepname' => $service['MEPNAME'],
            );
            ++$item;
          }
        }
    
    }
    
    return $reply;

}

?>