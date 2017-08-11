<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_single_imei_service_details_cron( $post_id ) {
    
    $suwp_dhru_referenceid = '176'; // not yet implemented, obtained from 'imeiservicelist' [SERVICEID]
    
    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_single_imei_service_details_constants_' . $post_id . '_cron.php' );
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_single_imei_service_details_api_' . $post_id . '_cron.php' );
    
    // Debug on
    $api->debug = true;

    $reply =  array();
    $services = array();
    $flag_continue = FALSE;
    $para['ID'] = $suwp_dhru_referenceid;
    $request = $api->action('getimeiservicedetails', $para);

    if (is_array($request)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $key1 => $val1) {
          if ($key1 === 'ID') {
            $tmp_serviceid = $val1;
          }
          if ($key1 === 'SUCCESS' || $key1 === 'ERROR') {
            $flag_continue = TRUE;
            $tmp_result = $key1;
            if (is_array($val1)) {
              foreach ($val1 as $key2 => $val2) {
                if (is_array($val2)) {
                  $tmp_msg = '';
                  foreach ($val2 as $key3 => $val3) {
                    if ($key3 === 'MESSAGE') {
                      $tmp_msg = $val3;
                    }
                    if ($key3 === 'LIST') {
                      if (is_array($val3)) {
                        $username = '';
                        $service_name = '';
                        $credit = '';
                        $id = '';
                        $assigned_brand = '';
                        $assigned_model = '';
                        $assigned_provider = '';
                        $type = '';
                        foreach ($val3 as $key4 => $val4) {
                          switch ($key4) {
                            case 'username':
                              $username = $val4;
                              break;
    
                            case 'service_name':
                              $service_name = $val4;
                              break;
    
                            case 'credit':
                              $credit = $val4;
                              break;
    
                            case 'purchase_cost':
                              $purchase_cost = $val4;
                              break;
    
                            case 'id':
                              $id = $val4;
                              break;
    
                            case 'API':
                              $id_api = $val4;
                              break;
    
                            case 'assigned_model':
                              $assigned_model = $val4;
                              break;
    
                            case 'assigned_provider':
                              $assigned_provider = $val4;
                              break;
    
                            case 'type':
                              $type = $val4;
                              break;
    
                            case 'listing':
                              $listing = $val4;
                              break;
    
                            case 'notification_mail':
                              $notification_mail = $val4;
                              break;
    
                          }
                          if (is_array($val4)) {
                            foreach ($val4 as $key5 => $val5) {
                              switch ($key5) {
                                case 'assigned_brand':
                                  $assigned_brand = $val5;
                                  break;
    
                              }
                            }
                          }
                        }
                      }
                    }
                  }
    
                  switch ($tmp_result) {
                    case 'ERROR':
                      $services = array('RESULT' => $tmp_result, 'MESSAGE' => $tmp_msg);
                      break;
    
                    case 'SUCCESS':
                      $services = array(
                        'RESULT' => $tmp_result,
                        'SERVICEID' => $tmp_serviceid,
                        'USERNAME' => $username,
                        'SERVICENAME' => $service_name,
                        'ID' => $id,
                        'ASSIGNEDBRAND' => $assigned_brand,
                        'ASSIGNEDMODEL' => $assigned_model,
                        'ASSIGNEDPROVIDER' => $assigned_provider,
                        'TYPE' => $type,
                        'CREDIT' => $credit,
                      );
                      break;
    
                  }
                }
              }
            }
          }
        }
    }
  
    return $services;

}

?>