<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_model_list_cron( $post_id ) {
    
    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_model_list_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_model_list_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;

    $reply =  array();
    $services = array();
    $flag_continue = FALSE;
    // FIGURE THIS OUT: $para['ID'] = $api_action_apiid;
    $para['ID'] = NULL; // NULL = GET ALL MODELS??; got from 'imeiservicelist' [SERVICEID]
    $request = $api->action('modellist', $para);
    
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
                    $tmp_name = $val3;
                  }
                  if ($key3 === 'MODELS') {
                    $flag_continue = TRUE;
                    if (is_array($val3)) {
                      foreach ($val3 as $key4 => $val4) {
                        if (is_array($val4)) {
                          $tmp_model_id = '';
                          $tmp_model_name = '';
                          foreach ($val4 as $key5 => $val5) {
                            if ($key5 === 'ID') {
                              $tmp_model_id = $val5;
                            }
                            if ($key5 === 'NAME') {
                              $tmp_model_name = $val5;
                            }
                            if ($tmp_model_name != '') {
                              $tmp_key = $tmp_id . '-php-' . $tmp_name . '-php-' . $tmp_model_id . '-php-' . $tmp_model_name;
                              $tmp_key_model_id_display = $tmp_model_id;
                              $services[] = array(
                                'ID' => $tmp_key,
                                'MODELID' => $tmp_key_model_id_display,
                                'MODELBRAND' => $tmp_name,
                                'MODELNAME' => $tmp_model_name,
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
          // Begin extraction at the actual model.
          if ((!empty($service['ID']) && ($service['ID'] != NULL))) {
            $reply[$service['ID']] = array(
              'id' => $service['MODELID'],
              'item' => $item,
              'brand' => $service['MODELBRAND'],
              'name' => $service['MODELNAME'],
            );
            ++$item;
          }
        }
        
    }

    return $reply;
}

?>