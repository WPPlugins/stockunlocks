<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function suwp_dhru_get_imeiservice_list_ui( $post_id ) {
    
    $apidetails = suwp_dhru_get_provider_array( $post_id );
    
    // get the api details
    $suwp_dhru_url = $apidetails['suwp_dhru_url'];
    $suwp_dhru_username = $apidetails['suwp_dhru_username'];
    $suwp_dhru_api_key = $apidetails['suwp_dhru_api_key'];
    
    define("SUWP_REQUESTFORMAT_IMEISERVICELIST_UI", "JSON");
    define('SUWP_DHRUFUSION_URL_IMEISERVICELIST_UI', $suwp_dhru_url);
    define("SUWP_USERNAME_IMEISERVICELIST_UI", $suwp_dhru_username);
    define("SUWP_API_ACCESS_KEY_IMEISERVICELIST_UI", $suwp_dhru_api_key);
    
    if (!extension_loaded('curl'))
    {
        trigger_error('cURL extension not installed', E_USER_ERROR);
    }
    class DhruFusionISListUI
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
                        'username' => SUWP_USERNAME_IMEISERVICELIST_UI,
                        'apiaccesskey' => SUWP_API_ACCESS_KEY_IMEISERVICELIST_UI,
                        'action' => $action,
                        'requestformat' => SUWP_REQUESTFORMAT_IMEISERVICELIST_UI,
                        'parameters' => $this->xmlData->saveHTML());
                     
                    $crul = curl_init();
                    curl_setopt($crul, CURLOPT_HEADER, false);
                    
                    curl_setopt($crul, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                    //curl_setopt($crul, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($crul, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($crul, CURLOPT_URL, SUWP_DHRUFUSION_URL_IMEISERVICELIST_UI.'/api/index.php');
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
    
    $api = new DhruFusionISListUI();
    
    // Debug on
    $api->debug = true;

    $services = array();
    $flag_continue = FALSE;
    
    $request = $api->action('imeiservicelist');
    
    if (is_array($request)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($request), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $key1 => $val1) {
          if ($key1 === 'LIST') {
            if (is_array($val1)) {
              foreach ($val1 as $key2 => $val2) {
                $tmp_id = $key2;
                if (is_array($val2)) {
                  foreach ($val2 as $key3 => $val3) {
                    if ($key3 === 'GROUPNAME') {
                      $tmp_groupname = $val3;
                    }
                    if ($key3 === 'SERVICES') {
                      $flag_continue = TRUE;
                      if (is_array($val3)) {
                        foreach ($val3 as $key4 => $val4) {
                          if (is_array($val4)) {
                            $tmp_id = '';
                            $tmp_servicename = '';
                            $tmp_credit = '';
                            $tmp_time = '';
                            $tmp_info = '';
                            $tmp_requiresnetwork = '';
                            $tmp_requiresmobile = '';
                            $tmp_requiresprovider = '';
                            $tmp_requirespin = '';
                            $tmp_requireskbh = '';
                            $tmp_requiresmep = '';
                            $tmp_requiresprd = '';
                            $tmp_requirestype = '';
                            $tmp_requireslocks = '';
                            $tmp_requiresreference = '';
                            foreach ($val4 as $key5 => $val5) {
                              if ($key5 === 'SERVICEID') {
                                $tmp_id = $val5;
                              }
                              if ($key5 === 'SERVICENAME') {
                                $tmp_servicename = $val5;
                              }
                              if ($key5 === 'CREDIT') {
                                $tmp_credit = $val5;
                              }
                              if ($key5 === 'TIME') {
                                $tmp_time = $val5;
                              }
                              if ($key5 === 'INFO') {
                                $tmp_info = $val5;
                              }
                              if ($key5 === 'Requires.Network') {
                                $tmp_requiresnetwork = $val5;
                              }
                              if ($key5 === 'Requires.Mobile') {
                                $tmp_requiresmobile = $val5;
                              }
                              if ($key5 === 'Requires.Provider') {
                                $tmp_requiresprovider = $val5;
                              }
                              if ($key5 === 'Requires.PIN') {
                                $tmp_requirespin = $val5;
                              }
                              if ($key5 === 'Requires.KBH') {
                                $tmp_requireskbh = $val5;
                              }
                              if ($key5 === 'Requires.MEP') {
                                $tmp_requiresmep = $val5;
                              }
                              if ($key5 === 'Requires.PRD') {
                                $tmp_requiresprd = $val5;
                              }
                              if ($key5 === 'Requires.Type') {
                                $tmp_requirestype = $val5;
                              }
                              if ($key5 === 'Requires.Locks') {
                                $tmp_requireslocks = $val5;
                              }
                              if ($key5 === 'Requires.Reference') {
                                $tmp_requiresreference = $val5;
                              }
                              if ($tmp_requiresreference != '') {
                                $tmp_key = $tmp_id . '-php-' . $tmp_groupname . '-php-' . $tmp_servicename;
                                $tmp_key_id_display = $tmp_id;
                                $services[] = array(
                                  'ID' => $tmp_id,
                                  'ID_DISPLAY' => $tmp_key_id_display,
                                  'GROUPNAME' => $tmp_groupname,
                                  'SERVICENAME' => $tmp_servicename,
                                  'INFO' => $tmp_info,
                                  'Requires.Network' => $tmp_requiresnetwork,
                                  'Requires.Mobile' => $tmp_requiresmobile,
                                  'Requires.Provider' => $tmp_requiresprovider,
                                  'Requires.PIN' => $tmp_requirespin,
                                  'Requires.KBH' => $tmp_requireskbh,
                                  'Requires.MEP' => $tmp_requiresmep,
                                  'Requires.PRD' => $tmp_requiresprd,
                                  'Requires.Type' => $tmp_requirestype,
                                  'Requires.Locks' => $tmp_requireslocks,
                                  'Requires.Reference' => $tmp_requiresreference,
                                  'CREDIT' => $tmp_credit,
                                  'TIME' => $tmp_time,
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
  
	// get the default values for our options
	$options = suwp_get_current_options();
    $troubleshoot_items = $options['suwp_manage_troubleshoot_run_id'];
	$loop_limit = 0;
    
    // whether or not to limit the number of services retrieved
    $flag_limit = false;
	if ( $troubleshoot_items > 0) {
		$loop_limit = $troubleshoot_items + 1;
        $flag_limit = true;
	}
    
    $request_serialized = serialize($request);
    $reply_serialized = serialize($services);
    
    $item = 0;

    $complete = array();
    $reply_part = array();
    $reply_all = array();
    
    if ( $flag_continue ) {
        
        // set up column headers
        $reply_part[] = array(
              'serviceid' => 'serviceid',
              'servicename' => 'servicename',
              'time' => 'time',
              'credit' => 'credit',
        );

        $reply_all[] = array(
              'serviceid' => 'serviceid',
              'servicename' => 'servicename',
              'time' => 'time',
              'credit' => 'credit',
              'groupname' => 'groupname',
              'info' => 'info',
              'network' => 'network',
              'mobile' => 'mobile',
              'provider' => 'provider',
              'pin' => 'pin',
              'kbh' => 'kbh',
              'mep' => 'mep',
              'prd' => 'prd',
              'type' => 'type',
              'locks' => 'locks',
              'reference' => 'reference',
        );
        
        foreach ($services as $service) {
                
              // Begin extraction at the actual service id.
            if ((!empty($service['ID']) && ($service['ID'] != NULL))) {
                
                ++$item;
                
                $reply_part[$service['ID']] = array(
                  'serviceid' => $service['ID_DISPLAY'],
                  'servicename' => $service['SERVICENAME'],
                  'time' => $service['TIME'],
                  'credit' => $service['CREDIT'],
                );
                
                $reply_all[$service['ID']] = array(
                  'serviceid' => $service['ID_DISPLAY'],
                  'servicename' => $service['SERVICENAME'],
                  'time' => $service['TIME'],
                  'credit' => $service['CREDIT'],
                  'groupname' => $service['GROUPNAME'],
                  'info' => $service['INFO'],
                  'network' => $service['Requires.Network'],
                  'mobile' => $service['Requires.Mobile'],
                  'provider' => $service['Requires.Provider'],
                  'pin' => $service['Requires.PIN'],
                  'kbh' => $service['Requires.KBH'],
                  'mep' => $service['Requires.MEP'],
                  'prd' => $service['Requires.PRD'],
                  'type' => $service['Requires.Type'],
                  'locks' => $service['Requires.Locks'],
                  'reference' => $service['Requires.Reference'],
                );
            
            }
        }
        
        if ( $flag_limit ) {
            $reply_part = array_slice($reply_part, 0, $loop_limit);
            $reply_all = array_slice($reply_all, 0, $loop_limit);
        }
        
        $complete[] = $reply_part;
        $complete[] = $reply_all;
    
    }
    
    // return partial and full details
    return $complete;

}

?>