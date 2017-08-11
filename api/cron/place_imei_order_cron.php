<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_place_imei_order_cron( $post_id ) {
    
    $suwp_orders = array();
    $suwp_order_items = array();
    
    global $wpdb;
    $suwp_orders = $wpdb->get_results("select * from ".$wpdb->prefix."posts where post_status='wc-processing' ORDER BY ID ASC" );
    
    // loop over orders to exclude/include processing
    foreach( $suwp_orders as $order ):
        
        $qty_loop = 1;
        $order_id_results = array();
        
        $current_order_id = $order->ID;
        
        $suwp_order_items = $wpdb->get_results("select order_item_id from ".$wpdb->prefix."woocommerce_order_items where order_id=". $current_order_id . " ORDER BY order_id ASC" );
        
        foreach( $suwp_order_items as $key => $loop_order_item_id ):
            
            $product_id = '';
            $imei_values = '';
            $suwp_email_reply = ''; // !!! meta_key/meta_value to be created ...
            // required parameters
            $suwp_dhru_serviceid = ''; // the service id attached to the product which was ordered
            $suwp_dhru_imei = ''; // the imei submitted with the order
            
            // The orders that need to be processed will have the meta_key='suwp_imei_values'
            // Also, now is the time to see if this order belongs to this api provider
            $current_order_item_id = $loop_order_item_id->order_item_id;
            $product_id = wc_get_order_item_meta( $current_order_item_id, '_product_id', true );
            // get the api provider id from 'product' entry postmeta (meta_key, meta_vaue)
            $post_id = get_field('_suwp_api_provider', $product_id );
            // get the api service id from 'product' entry postmeta (meta_key, meta_vaue)
            // actual required parameter to be submitted with the order via api
            $suwp_dhru_serviceid = get_field('_suwp_api_service_id', $product_id );
            $qty_sold = wc_get_order_item_meta( $current_order_item_id, '_qty', true );
            $suwp_qty_sent = wc_get_order_item_meta( $current_order_item_id, '_suwp_qty_sent', true );
            
            $imei = wc_get_order_item_meta( $current_order_item_id, 'suwp_imei_values', true );
            
            // GET THE DETAILS FROM THE PROVIDER ENTRY
            // IF THE PROVIDER IS ACTIVE, PROCEED WITH ORDER PROCESSING
            $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
        
            // if the Provider is active, this order should be placed
            if ( $suwp_activeflag ) {
              
                $apidetails = suwp_dhru_get_provider_array( $post_id );
                
                // get the api details
                $suwp_dhru_url = $apidetails['suwp_dhru_url'];
                $suwp_dhru_username = $apidetails['suwp_dhru_username'];
                $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
                
                include( plugin_dir_path( __FILE__ ) . 'providers/place_imei_order_constants_' . $post_id . '_cron.php' );
                        
                // proceed to loop through the number of IMEI, submitting an order for each,
                // saving each result to a custom, private comment for later retrieval.
                $imei_values = array();
                $imei_values = explode( "\n", trim($imei));
                $imei_count = count( $imei_values );
                
                // loop over all imei and place order for each
                foreach( $imei_values as $value ):
                
                    $suwp_dhru_imei = trim($value);
                    
                    include( plugin_dir_path( __FILE__ ) . 'providers/place_imei_order_api_' . $post_id . '_cron.php' );
                    
                    // Debug on
                    $api->debug = true;
                    
                    $reply =  array();
                    $flag_continue = FALSE;
                    $para['IMEI'] = $suwp_dhru_imei;
                    $para['ID'] = $suwp_dhru_serviceid;
                    
                    // REQUIRED PARAMETERS 
                    // $para['MODELID'] = "";
                    // $para['PROVIDERID'] = "";
                    // $para['MEP'] = "";
                    // $para['PIN'] = "";
                    // $para['KBH'] = "";
                    // $para['PRD'] = "";
                    // $para['TYPE'] = "";
                    // $para['REFERENCE'] = "";
                    // $para['LOCKS'] = "";
                    
                    $request = $api->action('placeimeiorder', $para);
                    
                    if (is_array($request)) {
                        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $key1 => $val1) {
                            if ($key1 === 'ID') {
                              $tmp_apiid = $val1;
                            }
                            if ($key1 === 'IMEI') {
                              $tmp_imei = $val1;
                            }
                            if ($key1 === 'MODELID') {
                              $tmp_modelid = $val1;
                            }
                            if ($key1 === 'PROVIDERID') {
                              $tmp_providerid = $val1;
                            }
                            if ($key1 === 'MEP') {
                              $tmp_mep = $val1;
                            }
                            if ($key1 === 'PIN') {
                              $tmp_pin = $val1;
                            }
                            if ($key1 === 'PRD') {
                              $tmp_prd = $val1;
                            }
                            if ($key1 === 'TYPE') {
                              $tmp_type = $val1;
                            }
                            if ($key1 === 'REFERENCE') {
                              $tmp_reference = $val1;
                            }
                            if ($key1 === 'LOCKS') {
                              $tmp_locks = $val1;
                            }
                            if ($key1 === 'SUCCESS' || $key1 === 'ERROR') {
                              $flag_continue = TRUE;
                              $tmp_result = $key1;
                              if (is_array($val1)) {
                                foreach ($val1 as $key2 => $val2) {
                                  if (is_array($val2)) {
                                    $tmp_msg = '';
                                    $tmp_full_desc = '';
                                    $tmp_referenceid = '';
                                    foreach ($val2 as $key3 => $val3) {
                                      if ($key3 === 'MESSAGE') {
                                        $tmp_msg = $val3;
                                      }
                                      if ($key3 === 'FULL_DESCRIPTION') {
                                        $tmp_full_desc = $val3;
                                      }
                                      if ($key3 === 'REFERENCEID') {
                                        $tmp_referenceid = $val3;
                                      }
                                    }
                                    switch ($tmp_result) {
                                      case 'ERROR':
                                        $reply = array(
                                          'RESULT' => $tmp_result,
                                          'APIID' => $tmp_apiid,
                                          'IMEI' => $tmp_imei,
                                          'MESSAGE' => $tmp_msg,
                                          'DESCRIPTION' => $tmp_full_desc,
                                        );
                                        break;
                            
                                      case 'SUCCESS':
                                        $reply = array(
                                          'RESULT' => $tmp_result,
                                          'APIID' => $tmp_apiid,
                                          'IMEI' => $tmp_imei,
                                          'MESSAGE' => $tmp_msg,
                                          'REFERENCEID' => $tmp_referenceid,
                                        );
                                        break;
                            
                                    }
                                  }
                                }
                              }
                            }
                        }
                    }
                    
                    // create unique comment(s) based on api reply
                                
                    // $flat_request = suwp_array_flatten($request, 2);
                    // error_log('PLACE ORDERS FLAT : ' . print_r($flat_request, true));
        
                    $reply_serialized = serialize($reply);
                    $reply['API_REPLY'] = serialize($request);
                    
                    $email_template_name = '';
                    $comment_msg = '';
        
                    $blog_title = get_bloginfo('name');
                    if( empty($blog_title) ) {
                        $blog_title = ''; // 'YourWebsiteName'
                    }
                    $admin_email = get_bloginfo('admin_email');
                    if( empty($admin_email) ) {
                        $admin_email = ''; // 'support@yourdomainhere.com'
                    }
                    $website_url = get_bloginfo('wpurl');
                    if( empty($website_url) ) {
                        $website_url = ''; // www.yourwebsitehere.com'
                    }
                    
                    switch ($reply['RESULT']) {
                    
                      case 'ERROR':
                        // possible duplicate imei, insufficient funds, etc.
                        
                        $comment_msg = $reply['MESSAGE'] . '; ' . $reply['DESCRIPTION'];
                        $time = current_time('mysql');
                        $comment_content = $suwp_dhru_imei . '-php-' . $current_order_item_id . '-php-' . $reply_serialized;
                        $email_template_name = 'suwp_order_error';
                        
                        $commentdata = array(
                            'comment_post_ID'       => $current_order_id,
                            'comment_author'        => 'StockUnlocks-php-'.$suwp_dhru_username,
                            'comment_author_email'  => $suwp_dhru_username, // dhru api username
                            'comment_author_url'    => $suwp_dhru_url, // dhru api url
                            'comment_content'       => $comment_content, // contains imei with api message
                            'comment_type'          => $email_template_name, // suwp_order_success, suwp_order_error, or suwp_connect_fail
                            'comment_parent'        => 0,
                            'user_id'               => 0,
                            'comment_author_IP'     => $suwp_dhru_api_key, // dhru api access key
                            'comment_agent'         => $reply['DESCRIPTION'], // $reply['REFERENCEID'] if success, $reply['DESCRIPTION'] if error, or suwp_default_fail
                            'comment_date'          => $time,
                            'comment_date_gmt'      => $time,
                            'comment_karma'         => 0,
                            'comment_approved'      => 1,
                        );
                        
                        //Insert new comment and get the comment ID
                       $comment_id = wp_insert_comment( $commentdata );
                        
                        // notify customer and, optionally, Bcc: admin with results 
                        if ( suwp_send_cron_recipient_email( $comment_content, $email_template_name ) ) {
                            
                            error_log("SUCCESS : Place IMEI Order ERROR message successfully sent.");
                        
                        } else {
                            // failed to send
                            error_log("ERROR : Place IMEI Order ERROR message failed to send.");
                        }
                            
                        // NEW: ADD A COMMENT TO THE ACTUAL ORDER ...
                        
                        $commentdata = array(
                            'comment_post_ID' => $current_order_id, // to which post the comment will show up
                            'comment_author' => $blog_title, //fixed value - can be dynamic 
                            'comment_author_email' => $admin_email, //fixed value - can be dynamic 
                            'comment_author_url' => $website_url, //fixed value - can be dynamic 
                            'comment_content' => $comment_msg, //fixed value - can be dynamic
                            'comment_approved' => 1,
                            'comment_agent' => 'WooCommerce',
                            'comment_type' => 'order_note', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
                            'comment_parent' => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
                            'user_id' => get_current_user_id(), //passing current user ID or any predefined as per the demand
                        );
                        
                        //Insert new comment and get the comment ID
                        $comment_id = wp_insert_comment( $commentdata );
                        
                        break;
                        
                      case 'SUCCESS':
                        // successful order submission, get the reference id
                        
                        $comment_msg = $reply['MESSAGE'];
                        // increment the '_suwp_qty_sent' in the _woocommerce_order_itemmeta table
                        // if all imei orders are placed, later update post_status = 'wc-suwp-ordered'.
                        if ( suwp_update_qty_sent( $current_order_item_id ) ) {
                            
                            $time = current_time('mysql');
                            $comment_content = $suwp_dhru_imei . '-php-' . $current_order_item_id . '-php-' . $reply_serialized;
                            $email_template_name = 'suwp_order_success';
                            
                            $commentdata = array(
                                'comment_post_ID'       => $current_order_id,
                                'comment_author'        => 'StockUnlocks-php-'.$suwp_dhru_username,
                                'comment_author_email'  => $suwp_dhru_username, // dhru api username
                                'comment_author_url'    => $suwp_dhru_url, // dhru api url
                                'comment_content'       => $comment_content, // contains imei with api message, no longer than 20 chars!!
                                'comment_type'          => $email_template_name, // suwp_order_success, suwp_order_error, or suwp_connect_fail
                                'comment_parent'        => 0,
                                'user_id'               => 0,
                                'comment_author_IP'     => $suwp_dhru_api_key, // dhru api access key
                                'comment_agent'         => $reply['REFERENCEID'], // $reply['REFERENCEID'] if success, $reply['DESCRIPTION'] if error, or suwp_default_fail
                                'comment_date'          => $time,
                                'comment_date_gmt'      => $time,
                                'comment_karma'         => 0,
                                'comment_approved'      => 1,
                            );
                            
                            //Insert new comment and get the comment ID
                            $comment_id = wp_insert_comment( $commentdata );
                            
                            // notify customer and, optionally, Bcc: admin with results 
                            if ( suwp_send_cron_recipient_email( $comment_content, $email_template_name ) ) {
                            
                                error_log("SUCCESS : Place IMEI Order SUCCESS message successfully sent.");
                        
                            } else {
                                // failed to send
                                error_log("ERROR : Place IMEI Order SUCCESS message failed to send.");
                            }
                            
                            // NEW: ADD A COMMENT TO THE ACTUAL ORDER ...
                        
                            $commentdata = array(
                                'comment_post_ID' => $current_order_id, // to which post the comment will show up
                                'comment_author' => $blog_title, //fixed value - can be dynamic 
                                'comment_author_email' => $admin_email, //fixed value - can be dynamic 
                                'comment_author_url' => $website_url, //fixed value - can be dynamic 
                                'comment_content' => $comment_msg, //fixed value - can be dynamic
                                'comment_approved' => 1,
                                'comment_agent' => 'WooCommerce',
                                'comment_type' => 'order_note', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
                                'comment_parent' => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
                                'user_id' => get_current_user_id(), //passing current user ID or any predefined as per the demand
                            );
                            
                            //Insert new comment and get the comment ID
                            $comment_id = wp_insert_comment( $commentdata );
                        
                        
                        } // if ( suwp_update_qty_sent( $current_order_item_id ) )
                        
                        break;
                        
                    default:
                        
                        // possible connection failure
                        // use this info to recover and resubmit, if necessary
                        
                        $time = current_time('mysql');
                        $comment_content = $suwp_dhru_imei . '-php-' . $current_order_item_id;
                        $email_template_name = 'suwp_connect_fail';
                        
                        // SHOULD RESET, OR LEAVE IT ALONE FOR NEXT RUN??
                        $commentdata = array(
                            'comment_post_ID'       => $current_order_id,
                            'comment_author'        => 'StockUnlocks-php-'.$suwp_dhru_username,
                            'comment_author_email'  => $suwp_dhru_username, // dhru api username
                            'comment_author_url'    => $suwp_dhru_url, // dhru api url
                            'comment_content'       => $comment_content, // contains imei with api message, no longer than 20 chars!!
                            'comment_type'          => $email_template_name, // suwp_order_success, suwp_order_error, or suwp_connect_fail
                            'comment_parent'        => 0,
                            'user_id'               => 0,
                            'comment_author_IP'     => $suwp_dhru_api_key, // dhru api access key
                            'comment_agent'         => 'suwp_default_fail', // $reply['REFERENCEID'] if success, $reply['DESCRIPTION'] if error, or suwp_default_fail
                            'comment_date'          => $time,
                            'comment_date_gmt'      => $time,
                            'comment_karma'         => 0,
                            'comment_approved'      => 1,
                        );
                        
                        // insert new comment and get the comment ID
                        $comment_id = wp_insert_comment( $commentdata ); // wp_new_comment ; wp_insert_comment
                        
                        // only notify admin about results 
                        if ( suwp_send_cron_recipient_email( $comment_content, $email_template_name ) ) {
                            
                            error_log("SUCCESS : Place IMEI Order CONNECT FAIL message successfully sent.");
                        
                        } else {
                            // failed to send
                            error_log("ERROR : Place IMEI Order CONNECT FAIL message failed to send.");
                        }
                        
                        // ??? ADD A PRIVATE COMMENT TO THE ORDER ...
                        
                    }
                    
                    // only proceed when the entire order has been looped through
                    if ($qty_sold == $qty_loop) {
                        
                        $flag_results = '';
                        $status_value = '';
                        
                        $qty_sent =  suwp_get_qty_sent( $current_order_item_id );
                        
                        // need to determine if 'ordered', 'partial', 'failed'.
                        // determine if all imei were submitted for order or not
                        if ($qty_sold == $qty_sent ) {
                            // ALL IMEI PROCESSED FOR ORDER

                            $flag_results = 'ordered';
                            
                        } else {
                            // FAILED OR INCOMPLETE IMEI ORDER SUBMISSION
                            
                            if ($qty_sent > 0) {
                                // partial success, only some imei submitted
                                
                                $flag_results = 'partial';
                                
                            } else {
                                // failure, looks like no imei were sent
                                
                                $flag_results = 'failed';
                                
                            }
                         
                        } // if ($qty_sold == $qty_sent)
                        
                        // reset for next batch
                        $qty_loop = 0;
                        
                        ///////////////////////////////////
                        // no need to look ahead for the next order_id
                        // already determined how to flag this order
                        
                        // FINISHED
                        
                        // update the order status accordingly
                        switch( $flag_results ) {
                           case 'ordered':
                               $status_value = 'wc-suwp-ordered';
                               break;
                           case 'partial':
                               $status_value = 'wc-suwp-order-part';
                               break;
                           case 'failed':
                               $status_value = 'wc-failed';
                               break;   
                        }
                        
                        // update order post_status
                        $order_post = array(
                           'ID' => $current_order_id,
                           'post_status'    => $status_value,
                        );
                        // update the post into the database
                        wp_update_post( $order_post );
                        
                        ///////////////////////////////////
                        // no need to look ahead for the next order_id
                        // already determined how to flag this order
                        
                    } // if ($qty_sold == $qty_loop) 
                    
                    $qty_loop++;
                
                endforeach; // foreach( $imei_values as $value )
            
            } // if ( $suwp_activeflag ) {
         
        endforeach; // foreach( $suwp_order_items as $key => $loop_order_item_id )
          
    endforeach; // foreach( $suwp_orders as $order )
    
}    

// hint: increases _suwp_qty_sent count by one
function suwp_update_qty_sent( $uid ) {
	
	global $wpdb;
    
	// setup our return value
	$return_value = false;
	
	try {
		
		$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
		
		// get current imei quantity sent count
		$current_count = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT meta_value 
					FROM $table_name 
					WHERE meta_key = '_suwp_qty_sent' AND order_item_id = %s
				", 
				$uid
			) 
		);
		
		// set new count
		$new_count = (int)$current_count+1;
		
		// update imei quantity sent for this order entry
		$wpdb->query(
			$wpdb->prepare( 
				"
					UPDATE $table_name
					SET meta_value = $new_count 
					WHERE meta_key = '_suwp_qty_sent' AND order_item_id = %s
				", 
				$uid
			) 
		);
		
		$return_value = true;
		
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	return $return_value;
	
}

// hint: return the _suwp_qty_sent value; the typical function was caching results
function suwp_get_qty_sent( $uid ) {
	
	global $wpdb;
    
	// setup our return value
	$return_value = -1;
	
	try {
		
		$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
		
		// get current imei quantity sent count
		$current_count = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT meta_value 
					FROM $table_name 
					WHERE meta_key = '_suwp_qty_sent' AND order_item_id = %s
				", 
				$uid
			) 
		);
		
		// set return value
		$return_value = $current_count;
		
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	return $return_value;
	
}

?>