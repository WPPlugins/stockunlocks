<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_imei_order_details_cron( $post_id ) {

    $apidetails = suwp_dhru_get_provider_array( $post_id);
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    include( plugin_dir_path( __FILE__ ) . 'providers/get_imei_orders_details_constants_' . $post_id . '_cron.php' );
    
    $suwp_comments = array();
    
    // need to determine if 'wc-suwp-available', 'wc-suwp-avail-part', 'wc-suwp-unavailable'.
    $qty_loop = 1;
    $order_id_results = array();
    
    global $wpdb;
    
    // collect all of the relevant comments in order to extract order references
    // sorting results by comment_post_ID ASC because there can be different orders from the same supplier
    $suwp_comments = $wpdb->get_results("select * from ".$wpdb->prefix."comments where comment_type='suwp_order_success' AND comment_author_IP='". $suwp_dhru_api_key ."' ORDER BY comment_post_ID ASC");
    
    // total orders aren't neccessarily equal to the total number of comments as an order/orders may have failed to be placed on a previous run.
    // the total qty for the order may be obtained by accessing _woocommerce_order_itemmeta table
    
    $suwp_comments_total = count($suwp_comments);
    
    $current_order_id = '';
    
    // need a means to look ahead to determine when the order_id will change
    $suwp_comments_iter = new ArrayIterator($suwp_comments);
    
    // loop over the comments, get the info and check the status
    foreach( $suwp_comments as $comment ):
    
        // get next key and value...
        $suwp_comments_iter->next(); 
        $nextKey = $suwp_comments_iter->key();
        $nextValue = $suwp_comments_iter->current();
    
        // extract the next imei and order_item_id from the comment
        $comment_values_next = explode( "-php-", trim($nextValue->comment_content));
        $suwp_dhru_imei_next = $comment_values_next[0];
        $next_order_item_id = $comment_values_next[1];
        
        // need to know how many order items have the same order_id
        // to determine when processing is done for that order
        $suwp_order_id_next = $wpdb->get_results("select order_id from ".$wpdb->prefix."woocommerce_order_items where order_item_id='". $next_order_item_id . "'" );
        $next_order_id = $suwp_order_id_next[0]->order_id;
        
        $suwp_dhru_referenceid = $comment->comment_agent;
        $comment_id = $comment->comment_ID;
        $current_order_id = $comment->comment_post_ID;
        $prev_comment_content = $comment->comment_content;
        // extract the imei and order_item_id from the comment
        $comment_values = explode( "-php-", trim($prev_comment_content));
        $suwp_dhru_imei = $comment_values[0];
        $current_order_item_id = $comment_values[1];
        $qty_sold = wc_get_order_item_meta( $current_order_item_id, '_qty', true );
        
        include( plugin_dir_path( __FILE__ ) . 'providers/get_imei_orders_details_api_' . $post_id . '_cron.php' );
                    
        // Debug on
        $api->debug = true;
    
        $reply =  array();
        $para['ID'] = $suwp_dhru_referenceid;
        $request = $api->action('getimeiorder', $para);
        
        if (is_array($request)) {
          $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
          $tmp_orderid = '';
          foreach ($iterator as $key1 => $val1) {
            if ($key1 === 'ID') {
              $tmp_orderid = $val1;
            }
            if ($key1 === 'SUCCESS' || $key1 === 'ERROR') {
              $flag_continue = TRUE;
              $tmp_result = $key1;
              if (is_array($val1)) {
                foreach ($val1 as $key2 => $val2) {
                  if (is_array($val2)) {
                    $tmp_msg = '';
                    $tmp_imei = '';
                    $tmp_status = '';
                    $tmp_code = '';
                    $tmp_comments = '';
                    foreach ($val2 as $key3 => $val3) {
                      if ($key3 === 'MESSAGE') {
                        $tmp_msg = $val3;
                      }
                      if ($key3 === 'IMEI') {
                        $tmp_imei = $val3;
                      }
                      if ($key3 === 'STATUS') {
                        $tmp_status = $val3;
                      }
                      if ($key3 === 'CODE') {
                        $tmp_code = $val3;
                      }
                      if ($key3 === 'COMMENTS') {
                        $tmp_comments = $val3;
                      }
                    }
                    switch ($tmp_result) {
                      case 'ERROR':
                        $reply = array(
                          'ORDERID' => $tmp_orderid,
                          'RESULTS' => $tmp_result,
                          'MESSAGE' => $tmp_msg,
                        );
                        break;
      
                      case 'SUCCESS':
                        $reply = array(
                          'ORDERID' => $tmp_orderid,
                          'RESULTS' => $tmp_result,
                          'IMEI' => $tmp_imei,
                          'STATUS' => $tmp_status,
                          'CODE' => $tmp_code,
                          'COMMENTS' => $tmp_comments,
                        );
                        break;
      
                    }
                  }
                }
              }
            }
          }
        }
        
        // $flat_request = suwp_array_flatten($request, 2);
        // error_log('GET ORDERS FLAT : ' . print_r($flat_request, true));
        
        $reply_serialized = serialize($reply);
        $reply['API_REPLY'] = serialize($request);
        $reply_status = 0;
        
        // echo '<br>';
        // print_r($reply);
        // echo '<br>';
        
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
        
        switch ($reply['RESULTS']) {
        
          case 'ERROR':
            
            $comment_msg = $reply['RESULTS'] . '; ' . $reply['MESSAGE'];
            
            // reasons for possible error: ...
            // create message, inform admin AND customer
            // update comment to reflect changes: 'comment_type' from 'suwp_order_success' to 'suwp_reply_error'
            
            /**
            'ORDERID' => $tmp_orderid,
            'RESULTS' => $tmp_result,
            'MESSAGE' => $tmp_msg,
            **/
            
            // do not increment the '_suwp_qty_done' in the _woocommerce_order_itemmeta table
            // since this was an error, it will not increment as 'done', giving the ability to flag order as:
            // 'wc-suwp-avail-part', 'wc-suwp-unavailable'.
            $time = current_time('mysql');
            
            // $comment = get_comment( $comment_id );
            
            $comment_content = $suwp_dhru_imei . '-php-' .  $current_order_item_id . '-php-' . $reply_serialized;
            $email_template_name = 'suwp_reply_error';
                            
            // update comment
            $commentarr = array();
            $commentarr['comment_ID'] = $comment_id;
            $commentarr['comment_content'] = $comment_content;
            $commentarr['comment_author_email'] = $suwp_dhru_username;
            $commentarr['comment_author_url'] = $suwp_dhru_url;
            $commentarr['comment_type'] = $email_template_name; // no longer than 20 chars!!
            $commentarr['comment_author_IP'] = $suwp_dhru_api_key;
            $commentarr['comment_date'] = $time;
            $commentarr['comment_date_gmt'] = $time;
            
            wp_update_comment( $commentarr );
            
            // notify customer and, optionally, Bcc: admin with results 
            if ( suwp_send_cron_recipient_email( $comment_content, $email_template_name ) ) {
                
                error_log("ERROR: Get IMEI Order message successfully sent : " . $email_template_name);
                
            } else {
                 // failed to send
                error_log("ERROR: Get IMEI Order message failed to send : " . $email_template_name);
                
            }
            
            // ??? ADD A CUSTOMER COMMENT TO THE ORDER, TO BE SEEN BY CUSTOMER ...
            
            break;
            
          case 'SUCCESS':
            
            $comment_msg = $reply['CODE'];
            $reply_status = (int)$reply['STATUS'];
            
            // great, a code or unlocked status probably received
            // create message, inform admin AND customer
            // update comment to reflect changes: 'comment_type' from 'suwp_order_success' to 'suwp_reply_success'
            
            /**
            'ORDERID' => $tmp_orderid,
            'RESULTS' => $tmp_result,
            'IMEI' => $tmp_imei,
            'STATUS' => $tmp_status,
            'CODE' => $tmp_code,
            'COMMENTS' => $tmp_comments,
            **/
            
            // error_log("ACTUAL: switch( $reply_status ): " . $reply_status);
            
            $order_status = '';
            
            switch( $reply_status ) {
                case 3:
                    // unavailable
                    // reasons: Not found, reported stolen/lost, etc.
                    $order_status = 'Code Unavailable';
                    error_log("POST: switch( $reply_status ): unavailable");
                    $email_template_name = 'suwp_reply_reject';
                    break;
                case 4:
                    // available
                    $order_status = 'Code Available';
                    error_log("POST: switch( $reply_status ): available");
                    $email_template_name = 'suwp_reply_success';
                    break;
                default:
                // new or pending, try later
                $qty_loop--;
                
            }
            
            if( $reply_status > 1) {
                
                    // error_log("reply_status > 1, email_template_name = " . $email_template_name . ', comment_id = '. $comment_id);
                    
                // increment the '_suwp_qty_done' in the _woocommerce_order_itemmeta table
                // based on totals, later update post_status = 'wc-suwp-available', 'wc-suwp-avail-part', 'wc-suwp-unavailable'.
                if ( suwp_update_qty_done( $current_order_item_id ) ) {
                        
                    $time = current_time('mysql');
                    $comment_content = $suwp_dhru_imei . '-php-' .  $current_order_item_id . '-php-' . $reply_serialized;
                    
                    // update comment
                    $commentarr = array();
                    $commentarr['comment_ID'] = $comment_id;
                    $commentarr['comment_content'] = $comment_content;
                    $commentarr['comment_author_email'] = $suwp_dhru_username;
                    $commentarr['comment_author_url'] = $suwp_dhru_url;
                    $commentarr['comment_type'] = $email_template_name; // no longer than 20 chars!!
                    $commentarr['comment_author_IP'] = $suwp_dhru_api_key;
                    $commentarr['comment_date'] = $time;
                    $commentarr['comment_date_gmt'] = $time;
                    
                    $update_results = wp_update_comment( $commentarr );
                    
                    // error_log("update_results = ". $update_results);
                    
                    // notify customer and, optionally, Bcc: admin with results 
                    if ( suwp_send_cron_recipient_email( $comment_content, $email_template_name ) ) {
                        
                         error_log("SUCCESS: Get IMEI Order message successfully sent : " . $email_template_name);
                        
                    } else {
                        // failed to send
                       error_log("SUCCESS: Get IMEI Order message failed to send : " . $email_template_name);
                    
                    }
                            
                    // NEW: ADD A COMMENT TO THE ACTUAL ORDER ...
                        
                    $commentdata = array(
                        'comment_post_ID' => $current_order_id, // to which post the comment will show up
                        'comment_author' => $blog_title, //fixed value - can be dynamic 
                        'comment_author_email' => $admin_email, //fixed value - can be dynamic 
                        'comment_author_url' => $website_url, //fixed value - can be dynamic 
                        'comment_content' => $order_status . ': ' . $comment_msg, //fixed value - can be dynamic
                        'comment_approved' => 1,
                        'comment_agent' => 'WooCommerce',
                        'comment_type' => 'order_note', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
                        'comment_parent' => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
                        'user_id' => get_current_user_id(), //passing current user ID or any predefined as per the demand
                    );
                    
                    //Insert new comment and get the comment ID
                    $comment_id = wp_insert_comment( $commentdata );
                
                } //  if ( suwp_update_qty_done( $current_order_item_id ) )
                
            } // if( $reply_status > 1)
            
            break;
            
        default:
            
            // possible connection failure
            // do nothing. try again later
            
        } // switch ($reply['RESULTS'])
        
        // only do this when the entire order has been verified
        if ($qty_sold == $qty_loop) {
           
            $qty_done =  suwp_get_qty_done( $current_order_item_id );
            $qty_sent =  suwp_get_qty_sent( $current_order_item_id );
            
            // echo '<br><br> $suwp_qty_sent POST-value, $qty_sent: '. $qty_sent. ', quantity done: '. $qty_done . '<br><br>';
                
            // determine if all imei were replied to or not
            if ($qty_done == $qty_sent || $qty_done > $qty_sent) {
                // echo '<br> ALL IMEI PROCESSED FOR ORDER # : '. $current_order_item_id . '<br>';
                
                $order_id_results[] = array(
                                    "order_item_id" => $current_order_item_id,
                                    "results" => 'available'
                );
                
                
            } else {
                // echo '<br> FAILED OR INCOMPLETE IMEI PROCESSING <br>';
                
                if ($qty_done > 0) {
                    // partial success, only some imei replied
                    
                    $order_id_results[] = array(
                                        "order_item_id" => $current_order_item_id,
                                        "results" => 'partial'
                    );
                    
                    
                } else {
                    // failure, looks like no imei are available
                    
                    $order_id_results[] = array(
                                        "order_item_id" => $current_order_item_id,
                                        "results" => 'unavailable'
                    );
                    
                }
                
            } // if ($qty_done == $qty_sent)
            
            // reset for next batch
            $qty_loop = 0;
           
            ///////////////////////////////////
            // if this is the same order_id continue, otherwise, 
            // reset the $order_id_results[] array and update the order
            // and determine how to flag this order
            
            if ( !($current_order_id == $next_order_id) ) {
                
              // echo 'FINISHED - $order_id_results :<br> ';
              // print_r($order_id_results);
              // echo '<br><br>';
              
              $flag_results = '';
              $post_status = '';
              $flag_available = false;
              $flag_partial = false;
              $flag_unavailable = false;
              
              foreach( $order_id_results as $key => $value ):
                
                // echo '$order_id_results $key => $value:<br> ';
                // echo $value['results'];
                // print_r($value) ; // ->results;
                // echo '<br><br>';
                
                switch( $value['results'] ) {
                    case 'available':
                        $flag_available = true;
                        break;
                    case 'partial':
                        $flag_partial = true;
                        break;
                    case 'unavailable':
                        $flag_unavailable = true;
                        break;   
                }
              
              endforeach; // foreach( $order_id_results as $key => $value )
              
              if ( $flag_unavailable ) {
                // if one was available, this is partial
                if ( $flag_available ) {
                    $flag_results = 'partial';
                // if one was partial, this is partial
                } elseif ( $flag_partial ) {
                    $flag_results = 'partial';
                // otherwise, the entire order is unavailable
                } else {
                    $flag_results = 'unavailable';
                }
                // if one was partial, this is partial
              } elseif ( $flag_partial ) {
                      $flag_results = 'partial';
                      // otherwise, the entire order is available
              } else {
                      $flag_results = 'available';
              }
              
              // update the order status accordingly
              switch( $flag_results ) {
                  case 'available':
                      $post_status = 'wc-suwp-available';
                      break;
                  case 'partial':
                      $post_status = 'wc-suwp-avail-part';
                      break;
                  case 'unavailable':
                      $post_status = 'wc-suwp-unavailable';
                      break;   
              }
              
              // echo '<br><br> $post_status = ' . $post_status;
              
              // update order post_status
              $order_post = array(
                  'ID' => $current_order_id,
                  'post_status'    => $post_status,
               );
              // update the post into the database
              wp_update_post( $order_post );
            
            // reset array for next order set
            $order_id_results = array();
            
            } // if ( !($current_order_id == $next_order_id) )
            
            ///////////////////////////////////
            // if this is the same order_id continue, otherwise, 
            // reset the $order_id_results[] array and update the order
            // and determine how to flag this order
           
        } // if ($qty_sold == $qty_loop)
        
        $qty_loop++;
        
    endforeach; // foreach( $suwp_comments as $comment )
    
}

// hint: increases _suwp_qty_done count by one
function suwp_update_qty_done( $uid ) {
	
	global $wpdb;
    
	// setup our return value
	$return_value = false;
	
	try {
		
		$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
		
		// get current imei quantity done (replied) count
		$current_count = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT meta_value 
					FROM $table_name 
					WHERE meta_key = '_suwp_qty_done' AND order_item_id = %s
				", 
				$uid
			) 
		);
		
		// set new count
		$new_count = (int)$current_count+1;
		
		// update imei quantity done (replied) for this order entry
		$wpdb->query(
			$wpdb->prepare( 
				"
					UPDATE $table_name
					SET meta_value = $new_count 
					WHERE meta_key = '_suwp_qty_done' AND order_item_id = %s
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

// hint: return the _suwp_qty_done value; the typical function was caching results
function suwp_get_qty_done( $uid ) {
	
	global $wpdb;
    
	// setup our return value
	$return_value = -1;
	
	try {
		
		$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
		
		// get current imei quantity done (replied) count
		$current_count = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT meta_value 
					FROM $table_name 
					WHERE meta_key = '_suwp_qty_done' AND order_item_id = %s
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