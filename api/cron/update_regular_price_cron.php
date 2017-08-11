<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_update_regular_price_cron( $post_id ) {
    
    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_single_imei_service_details_constants_' . $post_id . '_cron.php' );
    
    $suwp_products = array();
    
    global $wpdb;
    global $woocommerce;
 
	// get the default values for our options
	$options = suwp_get_current_options();
	
    // collect all of the relevant products in order to update prices
    $suwp_products = $wpdb->get_results("select * from ".$wpdb->prefix."postmeta where meta_key='_suwp_api_provider' AND meta_value='". $post_id ."' ORDER BY post_id ASC");
    
    // loop over the products, get the info and check the status
    foreach( $suwp_products as $product ):
        
        $product_id = $product->post_id;
        
        $service_credit_current = '';
        $service_credit_new = '';
        $regular_price_current = '';
        $regular_price_new = '';
        $multiplier_custom_enabled = '';
        $multiplier_custom_value = '';
        $multiplier_global_enabled = '';
        $multiplier_global_value = '';
        
        error_log('$post_id = ' . $post_id . ' [Provider]');
        error_log('$product_id = ' . $product_id . ' [Product]');
        
        $suwp_postmeta = $wpdb->get_results("select meta_value from ".$wpdb->prefix."postmeta where meta_key='_suwp_api_service_id' AND post_id='". $product_id ."'");
    
        foreach( $suwp_postmeta as $meta_value ):
        
            $suwp_dhru_referenceid = $meta_value->meta_value;
            
            error_log('_postmeta table meta_value: '. $suwp_dhru_referenceid . ' [API ID from service Provider]');
          
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
          
            switch ($services['RESULT']) {
                
              case 'ERROR':
                error_log( 'cron: auto update product regular price = ERROR' );
                error_log( 'MESSAGE: ' . $services['MESSAGE'] );
                
                break;
            
              case 'SUCCESS':
                error_log( 'cron: auto update product regular price = SUCCESS' );
                
                $service_credit = $services['CREDIT'];
                
                // check if this product is to be updated
                
                // update existing product, check settings for adjusting Product Regular price
                // is this product enabled for custom price adjustment?
                $price_adj = 'NOT YET SET';
                $price_adj_val = get_post_meta( $product_id, '_suwp_price_adj', true );
                
                // Check if the custom field is available.
                if ( ! empty( $price_adj_val ) ) {
                    $price_adj = $price_adj_val;
                }
                
                $price = get_post_meta( $product_id, '_regular_price', true );
                
                $service_credit_current = get_post_meta( $product_id, '_suwp_service_credit', true );
                $service_credit_new = $service_credit;
                $regular_price_current = $price;
                $regular_price_new = 'NO CHANGE';
                $multiplier_custom_enabled = $price_adj;
                // $multiplier_custom_value = '';
                
                $multiplier_global_enabled = 'DISABLED';
                $multiplier_global_value = 'ERROR - ATTEMPTING TO USE VALUES WHILE GLOBAL IS DISABLED!';
                
                error_log( 'Current Product service credit = ' . $service_credit_current);
                error_log( 'Current Remote service credit = ' . $service_credit_new);
                error_log( 'Current Product regular price = ' . $regular_price_current);
                error_log( 'Product automatic price adjustment setting = ' . $multiplier_custom_enabled);
                
                switch( $price_adj ) {
                    
                    case 'disabled':
                        
                        // don't make any price adjustments
                        
                        break;
                    case 'custom':
                        
                        // use the settings found directly on the Product
                        $price_adj_custom = get_post_meta( $product_id, '_suwp_price_adj_custom', true );
                        $multiplier_custom_value = $price_adj_custom;
                        
                        error_log( '[CUSTOM] price multiplier = ' . $multiplier_custom_value);
                        
                        $regular_price = ( (float)$service_credit * (float)$price_adj_custom );
                        $regular_price_txt = sprintf("%01.2f", $regular_price);
                        
                        if ( $woocommerce ) {
                            
                            // only update if price is different
                            if ( $price != $regular_price_txt ) {
                                update_post_meta( $product_id, '_suwp_service_credit', $service_credit );
                                update_post_meta( $product_id, '_regular_price', $regular_price_txt );
                                update_post_meta( $product_id, '_price', $regular_price_txt );
                                $regular_price_new = $regular_price_txt;
                            }
                            
                        }
                        
                        error_log( '[CUSTOM] NEW Product regular price = ' . $regular_price_new);
                        
                        break;
                    case 'global':
                        
                        // use the settings found in Plugin Options
                        $credit = (float)$service_credit;
                        $more_equal = (float)$options['suwp_price_range_01']; // more than or equal to
                        $more_equal_mult = (float)$options['suwp_price_adj_01']; // more than or equal to multiplier
                        $less_equal = (float)$options['suwp_price_range_02']; // less than or equal to
                        $less_equal_mult = (float)$options['suwp_price_adj_02']; // less than or equal to multiplier
                        $default_mult = (float)$options['suwp_price_adj_default']; // default multiplier
                        
                        if ( $woocommerce ) {
                            
                            // check if the global option is enabled
                            // 1 = Enabled, '' = disabled
                            if ( $options['suwp_price_enabled_01'] === '1' ) {
                                
                                $multiplier_global_enabled = 'enabled';
                                
                                if ($credit >= $more_equal) {
                                    
                                    $multiplier_global_value = $more_equal_mult;
                                    
                                    $regular_price = ( (float)$service_credit * (float)$more_equal_mult );
                                    $regular_price_txt = sprintf("%01.2f", $regular_price);
                                            
                                    // only update if price is different
                                    if ( $price != $regular_price_txt ) {
                                        update_post_meta( $product_id, '_suwp_service_credit', $service_credit );
                                        update_post_meta( $product_id, '_regular_price', $regular_price_txt );
                                        update_post_meta( $product_id, '_price', $regular_price_txt );
                                        $regular_price_new = $regular_price_txt;
                                    }
                                    
                                } elseif ($credit <= $less_equal) {
                                    
                                    $multiplier_global_value = $less_equal_mult;
                                    
                                    $regular_price = ( (float)$service_credit * (float)$less_equal_mult );
                                    $regular_price_txt = sprintf("%01.2f", $regular_price);
                                            
                                    // only update if price is different
                                    if ( $price != $regular_price_txt ) {
                                        update_post_meta( $product_id, '_suwp_service_credit', $service_credit );
                                        update_post_meta( $product_id, '_regular_price', $regular_price_txt );
                                        update_post_meta( $product_id, '_price', $regular_price_txt );
                                        $regular_price_new = $regular_price_txt;
                                    }
                                    
                                } else {
                                    
                                    $multiplier_global_value = $default_mult;
                                    
                                    // default multiplier
                                    $regular_price = ( (float)$service_credit * (float)$default_mult );
                                    $regular_price_txt = sprintf("%01.2f", $regular_price);
                                            
                                    // only update if price is different
                                    if ( $price != $regular_price_txt ) {
                                        update_post_meta( $product_id, '_suwp_service_credit', $service_credit );
                                        update_post_meta( $product_id, '_regular_price', $regular_price_txt );
                                        update_post_meta( $product_id, '_price', $regular_price_txt );
                                        $regular_price_new = $regular_price_txt;
                                    }
                                    
                                } // if ($credit >= $more_equal)
                                
                            } // if ( $options['suwp_price_enabled_01'] === '1' )
                            
                            error_log( '[GLOBAL] automatic price adjustment setting = ' . $multiplier_global_enabled);
                            error_log( '[GLOBAL] price multiplier = ' . $multiplier_global_value);
                            error_log( '[GLOBAL] NEW Product regular price = ' . $regular_price_new);
                            
                        } // if ( $woocommerce )
                    
                } // switch( $price_adj )
                
                break;
            
            } // switch ($reply['RESULT'])
            
            error_log( '' );
            
        endforeach; // foreach( $suwp_postmeta as $meta_value )
        
    endforeach; // foreach( $suwp_products as $product )
    
}

?>