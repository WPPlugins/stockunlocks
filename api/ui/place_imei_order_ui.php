<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_place_imei_order_ui( $post_id, $suwp_dhru_imei, $suwp_dhru_serviceid) {
    
    $apidetails = suwp_dhru_get_provider_array( $post_id );
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    define("SUWP_REQUESTFORMAT_PLACEIMEIORDER_UI", "JSON");
    define('SUWP_DHRUFUSION_URL_PLACEIMEIORDER_UI', $suwp_dhru_url);
    define("SUWP_USERNAME_PLACEIMEIORDER_UI", $suwp_dhru_username);
    define("SUWP_API_ACCESS_KEY_PLACEIMEIORDER_UI", $suwp_dhru_api_key);
    
    // This is direct from the ui, so values will be included with submission
    
    if (!extension_loaded('curl'))
    {
        trigger_error('cURL extension not installed', E_USER_ERROR);
    }
    class DhruFusionPlaceIMEIUI
    {
        var $xmlData;
        var $xmlResult;
        var $debug;
        var $action;
        function __construct()
        {
            $this->xmlData = new DOMDocument();
        }
        function getResult()
        {
            return $this->xmlResult;
        }
        function action($action, $arr = array())
        {
            if (is_string($action))
            {
                if (is_array($arr))
                {
                    if (count($arr))
                    {
                        $request = $this->xmlData->createElement("PARAMETERS");
                        $this->xmlData->appendChild($request);
                        foreach ($arr as $key => $val)
                        {
                            $key = strtoupper($key);
                            $request->appendChild($this->xmlData->createElement($key, $val));
                        }
                    }
                    $posted = array(
                        'username' => SUWP_USERNAME_PLACEIMEIORDER_UI,
                        'apiaccesskey' => SUWP_API_ACCESS_KEY_PLACEIMEIORDER_UI,
                        'action' => $action,
                        'requestformat' => SUWP_REQUESTFORMAT_PLACEIMEIORDER_UI,
                        'parameters' => $this->xmlData->saveHTML());
                     
                    $crul = curl_init();
                    curl_setopt($crul, CURLOPT_HEADER, false);
                    
                    curl_setopt($crul, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                    //curl_setopt($crul, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($crul, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($crul, CURLOPT_URL, SUWP_DHRUFUSION_URL_PLACEIMEIORDER_UI.'/api/index.php');
                    curl_setopt($crul, CURLOPT_POST, true);
                    curl_setopt($crul, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($crul, CURLOPT_POSTFIELDS, $posted);
                    $response = curl_exec($crul);
                    if (curl_errno($crul) != CURLE_OK)
                    {
                        echo curl_error($crul);
                        curl_close($crul);
                    }
                    else
                    {
                        curl_close($crul);
                        
                        if ($this->debug)
                        {
                            // echo "<textarea rows='20' cols='200'> ";
                            // print_r($response);
                            // echo "</textarea>";
                        }
                        return (json_decode($response, true));
                    }
                }
            }
            return false;
        }
    }

    $api = new DhruFusionPlaceIMEIUI();

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
    
    switch ($reply['RESULT']) {
    
      case 'ERROR':
        // possible duplicate imei, insufficient funds, etc.
        $time = current_time('mysql');
        
        break;
        
      case 'SUCCESS':
        // successful order submission, get the reference id
        $time = current_time('mysql');
        
        break;
        
    default:
        
        // possible connection failure
        $time = current_time('mysql');
        
    }

    return $reply_serialized; 
}  

?>