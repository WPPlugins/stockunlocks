<?php
/**
 * Plugin Name: StockUnlocks
 * Plugin URI: https://www.stockunlocks.com/forums/forum/stockunlocks-wordpress-plugin/
 * Description: Automate your mobile unlocking store with the StockUnlocks plugin combined with WooCommerce and the power of the Dhru Fusion API. Now, focus your time and energy where they're needed the most.
 * Version: 1.1.0
 * Author: StockUnlocks
 * Author URI: https://www.stockunlocks.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * Text Domain: stockunlocks
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// 1.1
// registers all custom shortcodes, etc. on init
add_action('init', 'suwp_register_allcodes');
add_action( 'plugins_loaded', 'suwp_plugin_init' );

function suwp_plugin_init() {
	
	// ?? INCLUDE A ONE-TIME NOTIFICATION FOR THE REQUIRED PLUGINS
	
    /**
	if( class_exists( 'Woocommerce' ) ) {
    
        echo 'Woocommerce YES';
 
	} else {
        
        echo 'Woocommerce NO';
    }
    
	if( class_exists('acf') ) {
		
        echo 'ACF YES';
		
	} else {
        
        echo 'ACF NO';
    }
	**/
	
}

// 1.x Custom General Fields TABS: Woocommerce
// Display Tabs
function suwp_custom_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['suwp-custom-tab'] = array(
        'label' => __( 'StockUnlocks', 'stockunlocks' ),
        'target' => 'suwp_custom_product_data',
    );
    return $product_data_tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'suwp_custom_product_data_tab' , 10 , 1 );

function suwp_custom_product_data_fields() {
    global $woocommerce, $post;
    ?>
    <!-- id below must match target registered in above suwp_custom_product_data_tab function -->
    <div id="suwp_custom_product_data" class="panel woocommerce_options_panel">
        <?php
        
        // get all api providers
        $lists = get_posts(
            array(
                'post_type'			=>'suwp_apiprovider',
                'status'			=>'publish',
                'posts_per_page'   	=> -1,
                'orderby'         	=> 'post_title',
                'order'            	=> 'ASC',
            )
        );
        
        $options_tmp = array();
        $options = array();
        // loop over each email list
        foreach( $lists as &$list ):
            
			// create the select option for that list
            $title = get_field('suwp_sitename', $list->ID ); // $list->post_title
			
			// Check if the custom field is available.
			if ( ! empty( $title ) ) {
				
				// $title = $title . ': ' . $list->post_title;
				$options_tmp[] = array(
					 $list->ID  => __( $title, 'stockunlocks' ),
				);
			
			}
			
        endforeach;
        
        $options = suwp_array_flatten($options_tmp, 2);

        $no_yes = array();
        $no_yes = array(
                        'None' => __( 'No', 'stockunlocks' ),
                        'Required' => __( 'Required', 'stockunlocks' ),
                        );
        
        $disabled_enabled = array();
        $disabled_enabled = array(
                        'disabled' => __( 'Disabled', 'stockunlocks' ),
                        'custom' => __( 'Custom', 'stockunlocks' ),
                        'global' => __( 'Global', 'stockunlocks' ),
                        );
		
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_api_provider', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'API provider', 'stockunlocks' ), 
            'options'         => $options,
            'desc_tip'      => 'true',
            'description'   => __( 'Select the API Provider for this service.', 'stockunlocks' ) 
        ) );
        
        // Text Field
		// 'required' was affecting non-Remote Service Products when saving
        woocommerce_wp_text_input( array( 
            'id'          => '_suwp_api_service_id', 
            'wrapper_class' => 'show_if_simple', 
            'label'       => __( 'API id', 'stockunlocks' ), 
            // 'placeholder' => '',
            'desc_tip'    => 'true',
            // 'custom_attributes' => array( 'required' => 'required' ),
            'description' => __( 'Enter the API id for this service.', 'stockunlocks' )
        ) );
        
        // Text Field
        woocommerce_wp_text_input( array( 
            'id'            => '_suwp_process_time', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Estimated Delivery Time', 'stockunlocks' ), 
            'placeholder'   => '',
            'desc_tip'      => 'false',
            'description' => __( 'Enter the estimated reply time.', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_mep', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is mep', 'stockunlocks' ), 
            'options'       => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Does this service require an mep selection?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_network', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is network', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Does this service require a network selection?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_model', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is model', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Does this service require a model selection?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_pin', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is pin', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths a pin type service?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_rm_type', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is RM type', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths a RM type service?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_kbh', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is kbh', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths a kbh type service?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_reference', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is Reference', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths a reference tag type service?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_service_tag', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is Service Tag', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths a service tag type service?', 'stockunlocks' ) 
        ) );
        
        // Select Field
        woocommerce_wp_select( array( 
            'id'            => '_suwp_is_activation', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Is Activation', 'stockunlocks' ),  
            // 'placeholder'   => '',
            'options'         => $no_yes,
            'desc_tip'      => 'true',
            'description'   => __( 'Is ths an activation type service?', 'stockunlocks' ) 
        ) );
        
        // Text Field
        woocommerce_wp_text_input( array( 
            'id'          => '_suwp_price_group_id', 
            'wrapper_class' => 'show_if_simple', 
            'label'       => __( 'Price Group id', 'stockunlocks' ), 
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __( 'Enter the Price Group ID for this service.', 'stockunlocks' ) 
        ) );
        
        // Text Field
        woocommerce_wp_text_input( array( 
            'id'          => '_suwp_price_group_name', 
            'wrapper_class' => 'show_if_simple', 
            'label'       => __( 'Price Group Name', 'stockunlocks' ), 
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __( 'Enter the Price Group Name for this service.', 'stockunlocks' ) 
        ) );
        
        // Textarea
        woocommerce_wp_textarea_input( 
            array( 
                'id'          => '_suwp_assigned_brand', 
                'label'       => __( 'Assigned Brand', 'stockunlocks' ), 
                'placeholder' => '', 
                'value'       => get_post_meta( $post->ID, '_suwp_assigned_brand', true ),
                'desc_tip'      => 'true',
                'description' => __( 'List of assigned brands, comma separated.', 'stockunlocks' ),
        ) );
        
        // Number Field
        woocommerce_wp_text_input( array( 
                'id'            => '_suwp_serial_length', 
                'wrapper_class' => 'show_if_simple', 
                'label'         => __( 'Serial length', 'stockunlocks' ), 
                'placeholder'   => '', 
                'type'          => 'number', 
                'custom_attributes' => array(
                        'step'  => 'any',
                        'min'   => '0'
                    ),
                'desc_tip'      => 'true',
                'description'   => __( 'Enter the required length of the IMEI or serial number.', 'stockunlocks' ), 
        ) );
        
        // Textarea
        woocommerce_wp_textarea_input( 
            array( 
                'id'          => '_suwp_not_found', 
                'label'       => __( 'Not Found Options', 'stockunlocks' ), 
                'placeholder' => '', 
                'value'       => get_post_meta( $post->ID, '_suwp_not_found', true ),
                'desc_tip'      => 'true',
                'description' => __( 'List of options when code not found, comma separated.', 'stockunlocks' ),
        ) );
        
        // Textarea
        woocommerce_wp_textarea_input( 
            array( 
                'id'          => '_suwp_assigned_model', 
                'label'       => __( 'Assigned Model', 'stockunlocks' ), 
                'placeholder' => '', 
                'value'       => get_post_meta( $post->ID, '_suwp_assigned_model', true ),
                'desc_tip'      => 'true',
                'description' => __( 'List of assigned models, comma separated.', 'stockunlocks' ),
        ) );
        
        // Textarea
		$assigned_network_provider = get_post_meta( $post->ID, '_suwp_assigned_network_provider', true );
		$assigned_network_provider_val = '';
		// Check if the custom field is available.
		if ( ! empty( $assigned_network_provider ) ) {
			$assigned_network_provider_val = $assigned_network_provider;
		}
		
        woocommerce_wp_textarea_input( 
            array( 
                'id'          => '_suwp_assigned_network_provider', 
                'label'       => __( 'Assigned Network Provider', 'stockunlocks' ), 
                'placeholder' => '', 
                'value'       => $assigned_network_provider_val,
                'desc_tip'      => 'true',
                'description' => __( 'List of assigned network providers, comma separated.', 'stockunlocks' ),
        ) );
        
		
        // Checkbox
		$online_status = get_post_meta( $post->ID, '_suwp_online_status', true );
		$online_status_val = 'yes';
		// Check if the custom field is available.
		if ( ! empty( $online_status ) ) {
			$online_status_val = $online_status;
		}
           
        woocommerce_wp_checkbox( array( 
            'id'            => '_suwp_online_status', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Online', 'stockunlocks' ),
            'value'         => $online_status_val,
            'desc_tip'      => 'true',
            'description'   => __( 'When "Online", orders may be submitted. Otherwise, this service will be displayed as "Offline”.', 'stockunlocks' ),
        ) );
        
        // Textarea
        woocommerce_wp_textarea_input( 
            array( 
                'id'          => '_suwp_service_notes', 
                'label'       => __( 'Service notes', 'stockunlocks' ), 
                'placeholder' => '', 
                'value'       => get_post_meta( $post->ID, '_suwp_service_notes', true ),
                'desc_tip'      => 'true',
                'description' => __( 'Holding area for previously used service details or future ideas.', 'stockunlocks' ),
        ) );
        
		// Select Field: Disabled, Custom, Global
        woocommerce_wp_select( array( 
            'id'            => '_suwp_price_adj', 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Auto Adjust Price', 'stockunlocks' ), 
            'options'       => $disabled_enabled,
            'desc_tip'      => 'true',
            'description'   => __( 'Automatically adjust Regular price when supplier credit changes; Custom > based on settings below; Global > based on Plugin Options.', 'stockunlocks' ) 
        ) );
		
        // Number Field
		$price_adj_custom = get_post_meta( $post->ID, '_suwp_price_adj_custom', true );
		$price_adj_custom_val = '1';
		// Check if the custom field is available.
		if ( ! empty( $price_adj_custom ) ) {
			$price_adj_custom_val = $price_adj_custom;
		}
                
        woocommerce_wp_text_input( array( 
                'id'            => '_suwp_price_adj_custom', 
                'wrapper_class' => 'show_if_simple', 
                'label'         => __( 'Custom price multiplier', 'stockunlocks' ), 
                'placeholder'   => '',
                'value'       => $price_adj_custom_val,
                'type'          => 'number', 
                'custom_attributes' => array(
                        'step'  => '0.01',
                        'min'   => '1'
                    ),
                'desc_tip'      => 'true',
                'description'   => __( 'Automatically adjust Regular price by a custom multiplier value when supplier credit changes. Only works when Custom is selected above.', 'stockunlocks' ), 
        ) );
		
        // Number Field
        woocommerce_wp_text_input( array( 
                'id'            => '_suwp_service_credit', 
                'wrapper_class' => 'show_if_simple', 
                'label'         => __( 'Service credit', 'stockunlocks' ), 
                'placeholder'   => '', 
                'type'          => 'number', 
                'custom_attributes' => array(
                        'step'  => '0.01',
                        'min'   => '0'
                    ),
                'desc_tip'      => 'true',
                'description'   => __( 'The required credit for this service: From Supplier.', 'stockunlocks' ), 
        ) );
    
        // Hidden field
        /**
        woocommerce_wp_hidden_input(
            array( 
                'id'    => '_hidden_field', 
                'value' => 'hidden_value'
                )
        );
        **/
        
        ?>
    </div>
    <?php
}
add_action( 'woocommerce_product_data_panels', 'suwp_custom_product_data_fields' );

// recursively reduces deep arrays to single-dimensional array
// $preserve_keys: (0=>never, 1=>strings, 2=>always)
function suwp_array_flatten($array, $preserve_keys = 1, &$newArray = Array()) {
  foreach ($array as $key => $child) {
    if (is_array($child)) {
      $newArray = suwp_array_flatten($child, $preserve_keys, $newArray);
    } elseif ($preserve_keys + is_string($key) > 1) {
      $newArray[$key] = $child;
    } else {
      $newArray[] = $child;
    }
  }
  return $newArray;
}

// 1.x Custom General Fields: Woocommerce
// saving field values
function suwp_add_custom_general_fields_save( $post_id ){

	// Text Field
	$suwp_api_provider_field = sanitize_text_field($_POST['_suwp_api_provider']);
	if( !empty( $suwp_api_provider_field ) )
		update_post_meta( $post_id, '_suwp_api_provider', $suwp_api_provider_field );
        
	$suwp_process_time_field = sanitize_text_field($_POST['_suwp_process_time']);
	if( !empty( $suwp_process_time_field ) )
		update_post_meta( $post_id, '_suwp_process_time', $suwp_process_time_field );
        
	$suwp_is_mep_select = sanitize_text_field($_POST['_suwp_is_mep']);
	if( !empty( $suwp_is_mep_select ) )
		update_post_meta( $post_id, '_suwp_is_mep', $suwp_is_mep_select );
        
	$suwp_is_network_select = sanitize_text_field($_POST['_suwp_is_network']);
	if( !empty( $suwp_is_network_select ) )
		update_post_meta( $post_id, '_suwp_is_network', $suwp_is_network_select );
        
	$suwp_is_model_select = sanitize_text_field($_POST['_suwp_is_model']);
	if( !empty( $suwp_is_model_select ) )
		update_post_meta( $post_id, '_suwp_is_model', $suwp_is_model_select );
        
	$suwp_is_pin_select = sanitize_text_field($_POST['_suwp_is_pin']);
	if( !empty( $suwp_is_pin_select ) )
		update_post_meta( $post_id, '_suwp_is_pin', $suwp_is_pin_select );
        
	$suwp_is_rm_type_select = sanitize_text_field($_POST['_suwp_is_rm_type']);
	if( !empty( $suwp_is_rm_type_select ) )
		update_post_meta( $post_id, '_suwp_is_rm_type', $suwp_is_rm_type_select );
        
	$suwp_is_kbh_select = sanitize_text_field($_POST['_suwp_is_kbh']);
	if( !empty( $suwp_is_kbh_select ) )
		update_post_meta( $post_id, '_suwp_is_kbh', $suwp_is_kbh_select );
        
	$suwp_is_reference_select = sanitize_text_field($_POST['_suwp_is_reference']);
	if( !empty( $suwp_is_reference_select ) )
		update_post_meta( $post_id, '_suwp_is_reference', $suwp_is_reference_select );
        
	$suwp_is_service_tag_select = sanitize_text_field($_POST['_suwp_is_service_tag']);
	if( !empty( $suwp_is_service_tag_select ) )
		update_post_meta( $post_id, '_suwp_is_service_tag', $suwp_is_service_tag_select );
        
	$suwp_is_activation_select = sanitize_text_field($_POST['_suwp_is_activation']);
	if( !empty( $suwp_is_activation_select ) )
		update_post_meta( $post_id, '_suwp_is_activation', $suwp_is_activation_select );
        
	$suwp_price_group_id_field = sanitize_text_field($_POST['_suwp_price_group_id']);
	if( !empty( $suwp_price_group_id_field ) )
		update_post_meta( $post_id, '_suwp_price_group_id', $suwp_price_group_id_field );
        
	$suwp_price_group_name_field = sanitize_text_field($_POST['_suwp_price_group_name']);
	if( !empty( $suwp_price_group_name_field ) )
		update_post_meta( $post_id, '_suwp_price_group_name', $suwp_price_group_name_field );
        
	$suwp_assigned_brand_textarea = sanitize_text_field( $_POST['_suwp_assigned_brand']);
	if( !empty( $suwp_assigned_brand_textarea ) )
		update_post_meta( $post_id, '_suwp_assigned_brand', $suwp_assigned_brand_textarea );
        
	$suwp_assigned_model_textarea = sanitize_text_field($_POST['_suwp_assigned_model']);
	if( !empty( $suwp_assigned_model_textarea ) )
		update_post_meta( $post_id, '_suwp_assigned_model', $suwp_assigned_model_textarea );
        
	$suwp_serial_length_field = sanitize_text_field($_POST['_suwp_serial_length']);
	if( !empty( $suwp_serial_length_field ) )
		update_post_meta( $post_id, '_suwp_serial_length', $suwp_serial_length_field );
        
	$suwp_api_service_id_field = sanitize_text_field($_POST['_suwp_api_service_id']);
	if( !empty( $suwp_api_service_id_field ) )
		update_post_meta( $post_id, '_suwp_api_service_id', $suwp_api_service_id_field );
    
	$suwp_not_found_textarea = sanitize_text_field($_POST['_suwp_not_found']);
	if( !empty( $suwp_not_found_textarea ) )
		update_post_meta( $post_id, '_suwp_not_found', $suwp_not_found_textarea );
    
	$suwp_assigned_network_provider_textarea = sanitize_text_field($_POST['_suwp_assigned_network_provider']);
	if( !empty( $suwp_assigned_network_provider_textarea ) )
		update_post_meta( $post_id, '_suwp_assigned_network_provider', $suwp_assigned_network_provider_textarea );
    
	$suwp_online_status_checkbox = isset( $_POST['_suwp_online_status'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_suwp_online_status', sanitize_text_field($suwp_online_status_checkbox) );
    
	$suwp_service_notes_textarea = sanitize_text_field($_POST['_suwp_service_notes']);
	if( !empty( $suwp_service_notes_textarea ) )
		update_post_meta( $post_id, '_suwp_service_notes', $suwp_service_notes_textarea );

	$suwp_price_adj_field = sanitize_text_field($_POST['_suwp_price_adj']);
	if( !empty( $suwp_price_adj_field ) )
		update_post_meta( $post_id, '_suwp_price_adj', $suwp_price_adj_field );
	
	$suwp_price_adj_custom_field = sanitize_text_field($_POST['_suwp_price_adj_custom']);
	if( !empty( $suwp_price_adj_custom_field ) )
		update_post_meta( $post_id, '_suwp_price_adj_custom', $suwp_price_adj_custom_field );
	
	$suwp_service_credit_field = sanitize_text_field($_POST['_suwp_service_credit']);
	if( !empty( $suwp_service_credit_field ) )
		update_post_meta( $post_id, '_suwp_service_credit', $suwp_service_credit_field );
		
}
add_action( 'woocommerce_process_product_meta', 'suwp_add_custom_general_fields_save' );
  
function suwp_add_product_custom_fields() {
    
    global $post;
    
	$options = suwp_get_current_options();
    $from_email = trim( $options['suwp_copyto_ordersuccess'] );
    
    if( has_term( 'suwp_service', 'product_cat' ) ) {

        $suwp_serial_length = get_field('_suwp_serial_length', $post->ID );
        $is_mep = get_field('_suwp_is_mep', $post->ID );
        $is_network = get_field('_suwp_is_network', $post->ID );
        $is_model = get_field('_suwp_is_model', $post->ID );
        $is_pin = get_field('_suwp_is_pin', $post->ID );
        $is_rm_type = get_field('_suwp_is_rm_type', $post->ID );
        $is_kbh = get_field('_suwp_is_kbh', $post->ID );
        $is_reference = get_field('_suwp_is_reference', $post->ID );
        $is_service_tag = get_field('_suwp_is_service_tag', $post->ID );
        $is_activation = get_field('_suwp_is_activation', $post->ID );
        
        $yes = 'Required';
        $no = 'None';
        
        echo '<table class="variations" cellspacing="0">
              <tbody>';
        
        if ($is_network == $yes) {
              
          echo '<tr>
                  <td class="value">
				  
				<div class="suwp-group">
						<label for="country-id" name="suwp-country-id-label">
							Country:
						</label>
				</div>
				
				<div class="suwp-group">
                      <select name="suwp-country-id">';
                      
                        // get all our email lists
                        $lists = get_posts(
                            array(
                                'post_type'			=>'suwp_list',
                                'status'			=>'publish',
                                'posts_per_page'   	=> -1,
                                'orderby'         	=> 'post_title',
                                'order'            	=> 'ASC',
                            )
                        );
                        
                        // loop over each email list
                        foreach( $lists as &$list ):
                        
                            // create the select option for that list
                            $option = '
                                <option value="'. $list->ID .'">
                                    '. $list->post_title .'
                                </option>';
                            
                            // echo the new option	
                            echo $option;
                            
                        endforeach;
                                            
                    echo '</select>
				</div>
                    
                  </td>
                </tr>';
                
          echo '<tr>
                  <td class="value">
				  
				<div class="suwp-group">
                        <label for="network-id" name="suwp-network-id-label">
							Network Provider:
						</label>
				</div>
				
				<div class="suwp-group">
                      <select name="suwp-network-id">';
                      
                        // get all our email lists
                        $lists = get_posts(
                            array(
                                'post_type'			=>'suwp_list',
                                'status'			=>'publish',
                                'posts_per_page'   	=> -1,
                                'orderby'         	=> 'post_title',
                                'order'            	=> 'ASC',
                            )
                        );
                        
                        // loop over each email list
                        foreach( $lists as &$list ):
                        
                            // create the select option for that list
                            $option = '
                                <option value="'. $list->ID .'">
                                    '. $list->post_title .'
                                </option>';
                            
                            // echo the new option	
                            echo $option;
                            
                        endforeach;
                                            
                    echo '</select>
				</div>
                      
                  </td>
                </tr>';
        }
        
        if ($is_model == $yes) {
				
          echo '<tr>
                  <td class="value">
				  
				<div class="suwp-group">
                        <label for="brand-id" name="suwp-brand-id-label">
							Brand:
						</label>
				</div>
				
				<div class="suwp-group">
                      <select name="suwp-brand-id">';
                      
                        // get all our email lists
                        $lists = get_posts(
                            array(
                                'post_type'			=>'suwp_list',
                                'status'			=>'publish',
                                'posts_per_page'   	=> -1,
                                'orderby'         	=> 'post_title',
                                'order'            	=> 'ASC',
                            )
                        );
                        
                        // loop over each email list
                        foreach( $lists as &$list ):
                        
                            // create the select option for that list
                            $option = '
                                <option value="'. $list->ID .'">
                                    '. $list->post_title .'
                                </option>';
                            
                            // echo the new option	
                            echo $option;
                            
                        endforeach;
                                            
                    echo '</select>
				</div>
                      
                  </td>
             </tr>';
                
        echo '<tr>
                  <td class="value">
				  
				<div class="suwp-group">
                        <label for="model-id" name="suwp-model-id-label">
							Model:
						</label>
				</div>
				
				<div class="suwp-group">
                      <select name="suwp-model-id">';
                      
                        // get all our email lists
                        $lists = get_posts(
                            array(
                                'post_type'			=>'suwp_list',
                                'status'			=>'publish',
                                'posts_per_page'   	=> -1,
                                'orderby'         	=> 'post_title',
                                'order'            	=> 'ASC',
                            )
                        );
                        
                        // loop over each email list
                        foreach( $lists as &$list ):
                        
                            // create the select option for that list
                            $option = '
                                <option value="'. $list->ID .'">
                                    '. $list->post_title .'
                                </option>';
                            
                            // echo the new option	
                            echo $option;
                            
                        endforeach;
                                            
                    echo '</select>
				</div>
                      
                  </td>
                </tr>';
        }
    
        if ($is_mep == $yes) {
                
           echo '<tr>
                  <td class="value">
				  
				<div class="suwp-group">
                        <label for="mep-id" name="suwp-mep-id-label">
							MEP Name:
						</label>
				</div>
				
				<div class="suwp-group">
                      <select name="suwp-mep-id">';
                      
                        // get all our email lists
                        $lists = get_posts(
                            array(
                                'post_type'			=>'suwp_list',
                                'status'			=>'publish',
                                'posts_per_page'   	=> -1,
                                'orderby'         	=> 'post_title',
                                'order'            	=> 'ASC',
                            )
                        );
                        
                        // loop over each email list
                        foreach( $lists as &$list ):
                        
                            // create the select option for that list
                            $option = '
                                <option value="'. $list->ID .'">
                                    '. $list->post_title .'
                                </option>';
                            
                            // echo the new option	
                            echo $option;
                            
                        endforeach;
                                            
                    echo '</select>
				</div>
                      
                  </td>
                </tr>';
        }
                
        echo '<tr>
                <td class="value">

				<div class="suwp-group">
						<label for="imei-values" name="suwp-imei-values-label">IMEI: <br /> 
							(Enter '. $suwp_serial_length . ' digits), dial *#06# to display <br> Bulk Submit: One Per Line <br />
						</label>
				</div>
				
				<div class="suwp-group">
					<textarea cols="40" rows="5" wrap="soft" name="suwp-imei-values"></textarea>
				</div>
				
                </td>
                </tr>';
    
    if ($is_kbh == $yes) {
            
        echo '<tr>
                    <td class="value">
					
					<div class="suwp-group">
                        <label for="kbh-values" name="suwp-kbh-values-label">
							KBH/KRH/ESN:
						</label>
					</div>
					
					<div class="suwp-group">
                        <input type="text" name="suwp-kbh-values" value="" />
					</div>
					
                    </td>
                </tr>';
    }
    
    if ($is_activation == $yes) {
                
        echo '<tr>
                    <td class="value">
					
					<div class="suwp-group">
                        <label for="activation-number" name="suwp-activation-number-label">
							Phone Number:
						</label>
					</div>
					
					<div class="suwp-group">
                        <input type="text" name="suwp-activation-number" value="" />
					</div>
					
                    </td>
					
                </tr>';
    }
                
        echo '<tr>
                    <td class="value">

					<div class="suwp-group">
                        <label for="email-response" name="suwp-email-response-label">
							Response Email: <br />
							(Please add ' . $from_email . ' to your address book!)<br />
						</label>
					</div>
					
					<div class="suwp-group">
                        <input type="email" name="suwp-email-response" value="" />
					</div>
					
                    </td>
                </tr>';
                
        echo '<tr>
                    <td class="value">
					
					<div class="suwp-group">
                        <label for="email-confirm" name="suwp-email-confirm-label">
							Confirm Email:
						</label>
					</div>
					
					<div class="suwp-group">
                        <input type="email" name="suwp-email-confirm" value="" />
					</div> 
                   
                    </td>
                </tr>';
                
        echo '<tr>
                    <td class="value">
                        <input type="hidden" name="_suwp-qty-sent" value="0" />
                    </td>
                </tr>
                
                <tr>
                    <td class="value">
                        <input type="hidden" name="_suwp-qty-done" value="0" />
                    </td>
                </tr>
                
              </tbody>
        </table>';
    
    }
   
}
add_action( 'woocommerce_before_add_to_cart_button', 'suwp_add_product_custom_fields' );

/**
 * Access to the single product page
 *
 * https://docs.woocommerce.com/document/conditional-tags/
 **/
function suwp_access_single_product_jscript() {
    
    global $woocommerce;
 
    if ( $woocommerce ) {
        
        if ( is_product( ) ):
            if( has_term( 'suwp_service', 'product_cat' ) ) :
                ?>
                <script>
                    // code here
                </script>
                <?php
            endif;
        endif;
       
    }
}
add_action( 'wp_footer', 'suwp_access_single_product_jscript' );

// x.x Front End Custom Fields: Woocommerce
// This code will do the validation for the custom fields.
// works for a specific category only
function suwp_custom_fields_validation($flaq, $product_id, $cart_item_data) {

        $slugs = array();
        $terms = get_the_terms( $product_id, 'product_cat' );
        
        if (is_array($terms)){
          foreach ( $terms as $term ) {
            $slugs[] = $term->slug; // $term->$vals['term']
          }
        }
        
        $test = array();
        $i = 1;
        if (is_array($cart_item_data)){
          foreach ( $cart_item_data as $term ) {
            $test[] = $term; // $term->$vals['term']
            $i++;
          }
        }
        
    
        if (in_array('suwp_service', $slugs, TRUE)){
            
            $email_response = $_REQUEST['suwp-email-response'];
            $email_confirm = $_REQUEST['suwp-email-confirm'];
                    
            $serial_length = (int)get_field('_suwp_serial_length', $product_id );
    
            $imei_values = trim($_REQUEST['suwp-imei-values']);
            $imeis = explode( "\n", trim($imei_values));
            $imei_count = count( $imeis );
            
            $flag_continue_imei = TRUE;
            $flag_msg_imei = array();
            $chk_dup_imei = array();
            
            $flag_continue_email_response = TRUE;
            $flag_continue_email_confirm = TRUE;
            $flag_msg_email = array();
    
            if ( empty( $imei_values ) ) {
                // empty submission
                $flag_continue_imei = FALSE;
                $flag_msg_imei[] = 'Please enter at least one IMEI.<br />';
                
            } else {
                
                foreach( $imeis as $imei_val ):
                
                    $imei = trim($imei_val);
                    $chk_dup_imei[] = $imei;
          
                    $actual_length = (int)strlen($imei);
                
                    if ( $actual_length != $serial_length && suwp_is_digits($imei) == 1 ) {
                    
                        $flag_continue_imei = FALSE;
                        $flag_msg_imei[] = $imei . ' should be ' . $serial_length . ' characters, not '. $actual_length . '. This is not a valid IMEI entry.<br />';
                        
                    }
                    
                    if ( suwp_is_digits($imei) != 1 ) {
                    
                        $flag_continue_imei = FALSE;
                        $flag_msg_imei[] = $imei . ' is invalid. IMEI should be digits only: no letters, punctuation, or spaces.<br />';
                        
                    }
                    
                    if ( $actual_length == $serial_length && suwp_is_digits($imei) == 1 ) {
						
						// only do the suwp_check_imei when imei is 15 digits
						if ($serial_length == 15) {
							
							if (suwp_check_imei($imei) == 0) {
                        
								$flag_continue_imei = FALSE;
								$flag_msg_imei[] = $imei . ' is not a valid IMEI entry.<br />';
								
							  }
						}
                    
						if (count($chk_dup_imei) != count(array_unique($chk_dup_imei)))  {
							// Confirm that there were no duplicate IMEI values submitted.
							
							  $flag_continue_imei = FALSE;
							  $flag_msg_imei[] = 'IMEI entries should not contain any duplicate values. <br />';
		
						  }
                      
                    }
                    
                endforeach; // foreach( $imeis as $imei_val )
                
            }
            
            $flag_msg_string = '';
            
            foreach( $flag_msg_imei as $msg_val ):
            
            $flag_msg_string .= $msg_val;
            
            endforeach; // foreach( $flag_msg_imei as $msg_val )
            
            
            $flag_continue_email_response = TRUE;
            $flag_continue_email_confirm = TRUE;
            $flag_msg_email = '';
            
            if ( empty( $_REQUEST['suwp-email-response'] ) ) {
                $flag_continue_email_response = FALSE;
                $flag_msg_email = 'Please enter a Response Email';
                
            } elseif ( empty( $_REQUEST['suwp-email-confirm'] ) ) {
                $flag_continue_email_confirm = FALSE;
                $flag_msg_email = 'Please enter a Confirmation Email';
                
            } elseif ($email_response != $email_confirm) {
                // both email fields have values, check if match
        
                $flag_continue_email_confirm = FALSE;
                $flag_msg_email = 'Sorry, the email addresses must match: ' . $email_response . ' != ' . $email_confirm;
                
            }

            if ( !$flag_continue_imei ) {
                wc_add_notice( __( $flag_msg_string, 'stockunlocks' ), 'error' );
                return false;
            }
            
            if ( !$flag_continue_email_response ) {
                wc_add_notice( __( $flag_msg_email, 'stockunlocks' ), 'error' );
                return false;
            }
            
            if ( !$flag_continue_email_confirm ) {
                wc_add_notice( __( $flag_msg_email, 'stockunlocks' ), 'error' );
                return false;
            }
            
        }
        
    return true;
}
add_action( 'woocommerce_add_to_cart_validation', 'suwp_custom_fields_validation', 10, 3 );

/**
 * Returns an int, flagging for digits only, no decimals or characters.
 *
 * @param string $element
 *   A string representation of a numerical value.
 */
function suwp_is_digits($element) {
  return !preg_match("/[^0-9]/", $element);
}

/**
 * Returns an int, confirming the validity of a 15 digit IMEI.
 *
 * @param string $imei
 *   A string representation of a 15 digit IMEI number.
 */
function suwp_check_imei($imei) {
  $dig = 0;
  for ($i = 0; $i < 14; $i += 2) {
    $cdigit = $imei[$i + 1] << 1;
    $dig += $imei[$i] + (int) ($cdigit / 10) + ($cdigit % 10);
  }
  $dig = (10 - ($dig % 10)) % 10;
  if ($dig == $imei[14]) {
    return 1;
  }
  else {
    return 0;
  }
}

/**
 * Hook: Set the quantity based on the number of IMEI submitted
 */
function suwp_modify_cart_before_add( $cart_item_data, $product_id ) {
    
    global $woocommerce;
    
    $cartQty = $woocommerce->cart->get_cart_item_quantities();
    $cartItems = $woocommerce->cart->cart_contents;
    
    $slugs = array();
    $terms = get_the_terms( $product_id, 'product_cat' );
    
    if (is_array($terms)){
      foreach ( $terms as $term ) {
        $slugs[] = $term->slug; // $term->$vals['term']
      }
    }

    if (in_array('suwp_service', $slugs, TRUE)){
        
        if (array_key_exists($product_id,$cartQty)) {
            
            foreach ($cartItems as $item => $values) {
                // $item = unique cart item id
                // $values['suwp_country_id'];
                // $values['unique_key'];
                // $values['suwp_imei_values'];
                
                $imei_values = trim($values['suwp_imei_values']);
                $text_area = explode( "\n", $imei_values);
                $imei_count = count( $text_area );
                $woocommerce->cart->set_quantity( $item, $imei_count );
            }
        }
    }
}
add_action ('woocommerce_add_to_cart', 'suwp_modify_cart_before_add', 10, 2);

// x.x Front End Custom Fields: Woocommerce
// This code will store the custom fields ( for the product that is being added to cart ) into cart item data ( each cart item has their own data )
function suwp_save_values_to_cutsom_fields( $cart_item_data, $product_id ) {
 
    if( isset( $_REQUEST['suwp-imei-values'] ) ) {
        
        /* below statement make sure every add to cart action as unique line item */
        $suwp_session_product_key = strtoupper( md5( date('dmYHisu') ) ); // md5( microtime().rand() );
        $cart_item_data['unique_key'] = $suwp_session_product_key;
        $_SESSION['suwp_session_product_key'] = $suwp_session_product_key;
        
        $cart_item_data[ 'suwp_imei_values' ] = $_REQUEST['suwp-imei-values'];
        $cart_item_data[ '_suwp_qty_sent' ] = $_REQUEST['_suwp-qty-sent'];
        $cart_item_data[ '_suwp_qty_done' ] = $_REQUEST['_suwp-qty-done'];
        
    }
    if( isset( $_REQUEST['suwp-country-id'] ) ) {
        $cart_item_data[ 'suwp_country_id' ] = $_REQUEST['suwp-country-id'];
    }
    if( isset( $_REQUEST['suwp-network-id'] ) ) {
        $cart_item_data[ 'suwp_network_id' ] = $_REQUEST['suwp-network-id'];
    }
    if( isset( $_REQUEST['suwp-brand-id'] ) ) {
        $cart_item_data[ 'suwp_brand_id' ] = $_REQUEST['suwp-brand-id'];
    }
    if( isset( $_REQUEST['suwp-model-id'] ) ) {
        $cart_item_data[ 'suwp_model_id' ] = $_REQUEST['suwp-model-id'];
    }
    if( isset( $_REQUEST['suwp-mep-id'] ) ) {
        $cart_item_data[ 'suwp_mep_id' ] = $_REQUEST['suwp-mep-id'];
    }
    if( isset( $_REQUEST['suwp-kbh'] ) ) {
        $cart_item_data[ 'suwp_kbh' ] = $_REQUEST['suwp-kbh'];
    }
    if( isset( $_REQUEST['suwp-activation-number'] ) ) {
        $cart_item_data[ 'suwp_activation_number' ] = $_REQUEST['suwp-activation-number'];
    }
    if( isset( $_REQUEST['suwp-email-response'] ) ) {
        $cart_item_data[ 'suwp_email_response' ] = $_REQUEST['suwp-email-response'];
    }
    if( isset( $_REQUEST['suwp-email-confirm'] ) ) {
        $cart_item_data[ 'suwp_email_confirm' ] = $_REQUEST['suwp-email-confirm'];
    }
    if( isset( $_REQUEST['suwp-notes'] ) ) {
        $cart_item_data[ 'suwp_notes' ] = $_REQUEST['suwp-notes'];
    }
    
    return $cart_item_data;
}
add_action( 'woocommerce_add_cart_item_data', 'suwp_save_values_to_cutsom_fields', 10, 2 );

// x.x Front End Custom Fields: Woocommerce
// This code will render the custom data in the cart and checkout page: web browser only
function suwp_render_meta_on_cart_and_checkout( $cart_data, $cart_item = null ) {
    $custom_items = array();

    /* Woo 2.4.2 updates */
    if( !empty( $cart_data ) ) {
        $custom_items = $cart_data;
    }
    if( isset( $cart_item['suwp_country_id'] ) ) {
        $custom_items[] = array( "name" => 'Country', "value" => $cart_item['suwp_country_id'] );
    }
    if( isset( $cart_item['suwp_imei_values'] ) ) {
        $custom_items[] = array( "name" => 'IMEI', "value" => $cart_item['suwp_imei_values'] );
    }
    if( isset( $cart_item['suwp_email_response'] ) ) {
        $custom_items[] = array( "name" => 'Email', "value" => $cart_item['suwp_email_response'] );
    }
    
	
    return $custom_items;
}
add_filter( 'woocommerce_get_item_data', 'suwp_render_meta_on_cart_and_checkout', 10, 2 );

//////////////////////////////////
// legacy detection for proper order details display
function suwp_woocommerce_version_check( $version ) {
	global $woocommerce;
	if( $woocommerce && version_compare( WC()->version, $version, ">=" ) ) {
		return true;
	}
	return false;
}  

function suwp_check_woo() {
	
	if( suwp_woocommerce_version_check('2.8') ) {  
		// Use new, updated functions
		add_action('woocommerce_checkout_create_order_line_item','suwp_order_item_meta_update_3_0', 1, 4); // 50, 2
		add_filter( 'woocommerce_display_item_meta', 'suwp_modify_order_items_meta_display_3_0', 99, 2 );
	
	} else {  
		// Use older, deprecated functions
		add_action( 'woocommerce_add_order_item_meta', 'suwp_order_item_meta_update_2_7', 1, 3 ); // 50, 2
		add_filter( 'woocommerce_order_items_meta_display', 'suwp_modify_order_items_meta_display_2_7', 99, 2 );
	}
}
/////////////////////////////////

// x.x Front End Custom Fields: Woocommerce
// This code will add the custom field with order meta:
// order details -> browser, order details -> emails, back end -> meta fields
function suwp_order_item_meta_update_3_0( $item, $cart_item_key, $values, $order ) {
	
	if(isset($values['suwp_imei_values']) ){
		$item->add_meta_data('suwp_imei_values', $values['suwp_imei_values'], true);
	}
    if( isset( $values['suwp_country_id'] ) ) {
		$item->add_meta_data('suwp_country_id', $values['suwp_country_id'], true);
    }
    if( isset( $values['suwp_network_id'] ) ) {
		$item->add_meta_data('suwp_network_id', $values['suwp_network_id'], true);
    }
    if( isset( $values['suwp_brand_id'] ) ) {
		$item->add_meta_data('suwp_brand_id', $values['suwp_brand_id'], true);
    }
    if( isset( $values['suwp_model_id'] ) ) {
		$item->add_meta_data('suwp_model_id', $values['suwp_model_id'], true);
    }
    if( isset( $values['suwp_mep_id'] ) ) {
		$item->add_meta_data('suwp_mep_id', $values['suwp_mep_id'], true);
    }
    if( isset( $values['suwp_kbh'] ) ) {
		$item->add_meta_data('suwp_kbh', $values['suwp_kbh'], true);
    }
    if( isset( $values['suwp_activation_number'] ) ) {
		$item->add_meta_data('suwp_activation_number', $values['suwp_activation_number'], true);
    }
    if( isset( $values['suwp_email_response'] ) ) {
		$item->add_meta_data('suwp_email_response', $values['suwp_email_response'], true);
    }
    if( isset( $values['suwp_email_confirm'] ) ) {
		$item->add_meta_data('suwp_email_confirm', $values['suwp_email_confirm'], true);
    }
    if( isset( $values['suwp_notes'] ) ) {
		$item->add_meta_data('suwp_notes', $values['suwp_notes'], true);
    }
    if( isset( $values['_suwp_qty_sent'] ) ) {
		$item->add_meta_data('_suwp_qty_sent', $values['_suwp_qty_sent'], true);
    }
    if( isset( $values['_suwp_qty_done'] ) ) {
		$item->add_meta_data('_suwp_qty_done', $values['_suwp_qty_done'], true);
    }
}
// add_action('woocommerce_checkout_create_order_line_item','suwp_order_item_meta_update_3_0', 1, 4);

// deprecated
function suwp_order_item_meta_update_2_7( $item_id, $values, $cart_item_key ) {

    if( isset( $values['suwp_imei_values'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_imei_values", $values['suwp_imei_values'] ); // previously: suwp_imei_values
    }
    if( isset( $values['suwp_country_id'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_country_id", $values['suwp_country_id'] );
    }
    if( isset( $values['suwp_network_id'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_network_id", $values['suwp_network_id'] );
    }
    if( isset( $values['suwp_brand_id'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_brand_id", $values['suwp_brand_id'] );
    }
    if( isset( $values['suwp_model_id'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_model_id", $values['suwp_model_id'] );
    }
    if( isset( $values['suwp_mep_id'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_mep_id", $values['suwp_mep_id'] );
    }
    if( isset( $values['suwp_kbh'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_kbh", $values['suwp_kbh'] );
    }
    if( isset( $values['suwp_activation_number'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_activation_number", $values['suwp_activation_number'] );
    }
    if( isset( $values['suwp_email_response'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_email_response", $values['suwp_email_response'] ); // previously: suwp_email_response
    }
    if( isset( $values['suwp_email_confirm'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_email_confirm", $values['suwp_email_confirm'] ); // previously: suwp_email_confirm
    }
    if( isset( $values['suwp_notes'] ) ) {
        wc_add_order_item_meta( $item_id, "suwp_notes", $values['suwp_notes'] );
    }
    if( isset( $values['_suwp_qty_sent'] ) ) {
        wc_add_order_item_meta( $item_id, "_suwp_qty_sent", $values['_suwp_qty_sent'] );
    }
    if( isset( $values['_suwp_qty_done'] ) ) {
        wc_add_order_item_meta( $item_id, "_suwp_qty_done", $values['_suwp_qty_done'] );
    }
    
    
}
// add_action( 'woocommerce_add_order_item_meta', 'suwp_order_item_meta_update_2_7', 1, 3 );

// https://woocommerce.wp-a2z.org/oik_api/wc_display_item_meta/
function suwp_modify_order_items_meta_display_3_0( $output, $order ) {
	
	$meta_list = array();
	$html = '';
	$formatted_meta = $order->get_formatted_meta_data();
	
	$replace = array(
	  'suwp_imei_values' => 'IMEI',
	  'suwp_email_response' => 'Email',
	  'suwp_email_confirm' => 'Email',
	);
	
	$args = wp_parse_args( $output, array(
		  'before'    => '<ul class="wc-item-meta"><li>',
		  'after'    => '</li></ul>',
		  'separator'  => '</li><li>',
		  'echo'    => true,
		  'autop'    => false,
		) );
	
	// var_dump($output);
	
	// echo '<pre>'; print_r($output); echo '</pre>';
	
    foreach ( $formatted_meta  as $meta_id => $meta ) {
      $value = $args['autop'] ? wp_kses_post( wpautop( make_clickable( $meta->display_value ) ) ) : wp_kses_post( make_clickable( $meta->display_value ) );
	  // add as many conditions as needed. Depends upon how many fields need to be hidden
	  if( $meta->display_key != "suwp_email_confirm" ) {
		$meta_list[] = '<strong class="wc-item-meta-label">' . wp_kses_post( $meta->display_key ) . ':</strong> ' . $value;
	  }
    }
	
    if ( $meta_list ) {
		$meta_list = suwp_string_replace_assoc( $replace, $meta_list );
		$html = $args['before'] . implode( $args['separator'], $meta_list ) . $args['after'];
    }
	
    if ( $args['echo'] ) {
      echo $html;
    } else {
      return $html;
    }

}
// add_filter( 'woocommerce_display_item_meta', 'suwp_modify_order_items_meta_display_3_0', 99, 2 );

// deprecated
function suwp_modify_order_items_meta_display_2_7( $output, $order ) {
	
    $meta_list = array();
	$html = '';
	$formatted_meta = $order->get_formatted( '_' );

	$replace = array(
	  'suwp_imei_values' => 'IMEI',
	  'suwp_email_response' => 'Email',
	  'suwp_email_confirm' => 'Email',
	);
	
	$args = wp_parse_args( $output, array(
	  'before'    => '<ul class="wc-item-meta"><li>',
	  'after'    => '</li></ul>',
	  'separator'  => '</li><li>',
	  'echo'    => true,
	  'autop'    => false,
	) );
	
	// var_dump($output);
	
	// echo '<pre>'; print_r($output); echo '</pre>';
	
    foreach ( $formatted_meta as $meta ) {
      $value = $args['autop'] ? wp_kses_post( wpautop( make_clickable( $meta['label'] ) ) ) : wp_kses_post( make_clickable( $meta['value'] ) );
	  // add as many conditions as needed. Depends upon how many fields need to be hidden
	  if( $meta['key'] != "suwp_email_confirm" ) {
		$meta_list[] = '
							<dt class="variation-' . sanitize_html_class( sanitize_text_field( $meta['key'] ) ) . '">' . wp_kses_post( $meta['label'] ) . ':</dt>
							<dd class="variation-' . sanitize_html_class( sanitize_text_field( $meta['key'] ) ) . '">' . wp_kses_post( wpautop( make_clickable( $meta['value'] ) ) ) . '</dd>
						';
	  }
    }
	
    if ( $meta_list ) {
		$meta_list = suwp_string_replace_assoc( $replace, $meta_list );
		$html = $args['before'] . implode( $args['separator'], $meta_list ) . $args['after'];
    }
	
    if ( $args['echo'] ) {
      echo $html;
    } else {
      return $html;
    }

}
// add_filter( 'woocommerce_order_items_meta_display', 'suwp_modify_order_items_meta_display_2_7', 99, 2 );

// x.x limit the quantity increase/decrease of the suwp_service in the Cart only
function suwp_quantity_input_args( $args, $product) {
         
    // print_r($args);
    // echo $args['input_name'];
    $cart_item_id = $args['input_name']; // format: cart[73338a514fd2b6b75ec8eb03d3e78155][qty]
    
    $serial_length = get_post_meta( $product->get_id(), '_suwp_serial_length', true ); // _suwp_service_notes ; _suwp_serial_length

    $slugs = array();
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    
    $input_value = $args['input_value'];
    
    if (is_array($terms)) {
      foreach ( $terms as $term ) {
        $slugs[] = $term->slug;
      }
    }
    
    // don't allow changing suwp_service because qty is based on the number of IMEI
    if (in_array('suwp_service', $slugs, TRUE)){
        
        $args['max_value'] = $input_value;
        $args['min_value'] = $input_value;
        $args['step'] = 1;
        
    }
    
    return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'suwp_quantity_input_args', 10, 2 );

/**
 * Access to the cart page
 *
 * https://docs.woocommerce.com/document/conditional-tags/
 **/
function suwp_access_cart_page_jscript() {
    
    global $woocommerce;
 
    if ( $woocommerce ) {
        
        if ( is_cart() ) :
            ?> 
                <script type="text/javascript">
                    
                    jQuery( document ).ready( function($){
                        
                        var parentSelector = $('.input-text.qty.text');
                        
                        if( parentSelector.length ) {
                        
                            // just replace the input with a text representation of the quantity
                            parentSelector.replaceWith(function(){
                                return '<span class='+this.className+'>'+this.value+'</span>';
                            });
                            
                        }
                        
                        $( document ).on( 'updated_cart_totals', function(){
                        //re-do the jquery
                        // alert('trying to mess me up.');
                        
                        var parentSelector = $('.input-text.qty.text');
                        
                        if( parentSelector.length ) {
                        
                            parentSelector.replaceWith(function(){
                                return '<span class='+this.className+'>'+this.value+'</span>';
                            });
                        }
                        
                        });
                        
                    });
                    
                </script>
            <?php
        endif;
    
    } // if ( $woocommerce )
}
add_action( 'wp_footer', 'suwp_access_cart_page_jscript' );

// remove the add to cart from suwp_service on the shop page ONLY
function suwp_remove_add_to_cart_buttons_shop() {
    
    global $product; 
    $slugs = array();
    
    $terms = get_the_terms( $product->get_id(), 'product_cat' ); // $product->id
    
    if (is_array($terms)){
      foreach ( $terms as $term ) {
        $slugs[] = $term->slug; // $term->$vals['term']
      }
    }
    
    if (in_array('suwp_service', $slugs, TRUE)) {

        if( is_product_category() || is_shop()) { 
            remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
        }
        
    }
}
add_action( 'woocommerce_after_shop_loop_item', 'suwp_remove_add_to_cart_buttons_shop', 1 );

// remove the add to cart from suwp_service on the single product page
function suwp_remove_add_to_cart_buttons_product() {
    
    global $product;
    $slugs = array();
    
    $terms = get_the_terms( $product->get_id(), 'product_cat' ); // $product->id
    
    if (is_array($terms)){
      foreach ( $terms as $term ) {
        $slugs[] = $term->slug; // $term->$vals['term']
      }
    }
    
    if (in_array('suwp_service', $slugs, TRUE)) {
        
        $is_online = get_post_meta( $product->get_id(), '_suwp_online_status', true  ); // $product->id
        
        //Remove Add to Cart button from product description of product that is 'offline'
        if ( $is_online !== 'yes' ){
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        }
        
    }
    
}
add_action( 'woocommerce_single_product_summary', 'suwp_remove_add_to_cart_buttons_product', 5 );


/*
*  suwp_print_cron_tasks()
*
*  This function will display the current cron schedules
*
*  @type	function
*  @since	1.0.0
*  @date	02/07/17
*
*  @return	N/A
*/

function suwp_print_cron_tasks() {
    echo '<pre>'; print_r( _get_cron_array() ); echo '</pre>';
    echo '<pre>'; print_r( wp_get_schedules() ); echo '</pre>';
}

// to create a custom interval we tap into the ‘cron_schedules’ filter and alter the schedules array
function suwp_add_cron_intervals( $schedules ) {
    
    // units are in seconds
    // use these for 'interval' below.
    $period6 = 6*3600; // 6 hours
    $period3 = 3*3600; // 3 hours
    $period30min = 0.5*3600; // 30 minutes
    $period15min = 0.25*3600; // 15 minutes
    $period5min = 0.08333333*3600; // 5 minutes
    
    // '6hours' is a unique name for our custom period
    $schedules['6hours'] = array( // Provide the programmatic name to be used in code
                                 'interval' => $period6, // Intervals are listed in seconds
                                 'display' =>  __( 'Every 6 hours' ) // Easy to read display name
                                 );
    
    $schedules['3hours'] = array(
                                 'interval' => $period3,
                                 'display' =>  __( 'Every 3 hours' )
                                 );
    
    $schedules['30minutes'] = array(
                                 'interval' => $period30min,
                                 'display' =>  __( 'Every 30 minutes' )
                                 );
    
    $schedules['15minutes'] = array(
                                 'interval' => $period15min,
                                 'display' =>  __( 'Every 15 minutes' )
                                 );
    
    $schedules['5minutes'] = array(
                                 'interval' => $period5min,
                                 'display' =>  __( 'Every 5 minutes' )
                                 );
    
    $schedules['5seconds'] = array(
                                   'interval' => 5,
                                   'display' => __( 'Every 5 Seconds' )
                                   );
    
    return $schedules; // Do not forget to give back the list of schedules!
 
}
add_filter( 'cron_schedules', 'suwp_add_cron_intervals' );

// convenient function called wp_next_scheduled() to check if a particular hook is already scheduled.
// >>> wp_next_scheduled( 'suwp_cron_hook' );

// scheduling a recurring task is accomplished with wp_schedule_event()
/**
1. $timestamp – The UNIX timestamp of the first time this task shoud execute
2. $recurrence – The name of the interval in which the task will recur in seconds
3. $hook – The name of our custom hook to call
**/
// >>> wp_schedule_event( time(), '5seconds', 'suwp_cron_hook' );

// we need to first ensure the task is not already scheduled:
function suwp_verify_cron_schedule() {
	
	if( !wp_next_scheduled( 'suwp_cron_hook_5seconds' ) ) {
		wp_schedule_event( time(), '5seconds', 'suwp_cron_hook_5seconds' );
	}
	
	if( !wp_next_scheduled( 'suwp_cron_hook_5minutes' ) ) {
		wp_schedule_event( time(), '5minutes', 'suwp_cron_hook_5minutes' );
	}
	
	if( !wp_next_scheduled( 'suwp_cron_hook_15minutes' ) ) {
		wp_schedule_event( time(), '15minutes', 'suwp_cron_hook_15minutes' );
	}
	
	if( !wp_next_scheduled( 'suwp_cron_hook_30minutes' ) ) {
		wp_schedule_event( time(), '30minutes', 'suwp_cron_hook_30minutes' );
	}
	
	if( !wp_next_scheduled( 'suwp_cron_hook_1hour' ) ) {
		wp_schedule_event( time(), 'hourly', 'suwp_cron_hook_1hour' );
	}
	
	if( !wp_next_scheduled( 'suwp_cron_hook_3hours' ) ) {
		wp_schedule_event( time(), '3hours', 'suwp_cron_hook_3hours' );
	}

}

function suwp_cron_exec_5seconds() {
    // do you really want to do this??
}

function suwp_cron_exec_5minutes() {
    
    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
	// get the default values for our options
	$options = suwp_get_current_options();
	
    $cron_run = $options['suwp_manage_cron_run_id'];
    
    if ( $cron_run == '5min' ) {
        error_log('STOCKUNLOCKS CRON RUNNING EVERY 5 MINUTES, OK!');
        suwp_run_cron_run( $cron_run );
    } else {
        // error_log('STOCKUNLOCKS CRON NOT RUNNING EVERY 5 MINUTES, BUT EVERY : ' . $cron_run);
    }
    
}

function suwp_cron_exec_15minutes() {
    
    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
	// get the default values for our options
	$options = suwp_get_current_options();
	
    $cron_run = $options['suwp_manage_cron_run_id'];
    
    if ( $cron_run == '15min' ) {
        error_log('STOCKUNLOCKS CRON RUNNING EVERY 15 MINUTES, OK!');
        suwp_run_cron_run( $cron_run );
    } else {
        // error_log('STOCKUNLOCKS CRON NOT RUNNING EVERY 15 MINUTES, BUT EVERY : ' . $cron_run);
    }
    
}

function suwp_cron_exec_30minutes() {
    
    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
	// get the default values for our options
	$options = suwp_get_current_options();
	
    $cron_run = $options['suwp_manage_cron_run_id'];
    
    if ( $cron_run == '30min' ) {
        error_log('STOCKUNLOCKS CRON RUNNING EVERY 30 MINUTES, OK!');
        suwp_run_cron_run( $cron_run );
    } else {
        // error_log('STOCKUNLOCKS CRON NOT RUNNING EVERY 30 MINUTES, BUT EVERY : ' . $cron_run);
    }
    
}

function suwp_cron_exec_1hour() {

    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
	// get the default values for our options
	$options = suwp_get_current_options();
	
    $cron_run = $options['suwp_manage_cron_run_id'];
    
    if ( $cron_run == '1hr' ) {
        error_log('STOCKUNLOCKS CRON RUNNING EVERY 1 HOUR, OK!');
        suwp_run_cron_run( $cron_run );
    } else {
        // error_log('STOCKUNLOCKS CRON NOT RUNNING EVERY 1 HOUR, BUT EVERY : ' . $cron_run);
    }
    
}

function suwp_cron_exec_3hours() {

    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
	// get the default values for our options
	$options = suwp_get_current_options();
	
    $cron_run = $options['suwp_manage_cron_run_id'];
    
    if ( $cron_run == '3hrs' ) {
        error_log('STOCKUNLOCKS CRON RUNNING EVERY 3 HOURS, OK!');
        suwp_run_cron_run( $cron_run );
    } else {
        // error_log('STOCKUNLOCKS CRON NOT RUNNING EVERY 3 HOURS, BUT EVERY : ' . $cron_run);
    }
    
}

function suwp_run_cron_run( $cron_run ) {

    // '5min'  => '5 minutes',
    // '15min' => '15 minutes',
    // '30min' => '30 minutes',
    // '1hr'   => '1 hour',
    // '3hrs'  => '3 hours',
    
    global $wpdb;
    
    // suwp_print_cron_tasks();
    
    // loop through all active providers and proceed to place or check on order(s)
    $suwp_apiproviders = $wpdb->get_results("select ID from ".$wpdb->prefix."posts where post_type='suwp_apiprovider' AND post_status='publish' ORDER BY ID ASC" );
    
    error_log('suwp_apiproviders : ');
    error_log(print_r($suwp_apiproviders,true));
        
    suwp_cron_place_imei_orders( $suwp_apiproviders );
    
    suwp_cron_check_imei_orders( $suwp_apiproviders );
    
	// TO BE IMPLEMENTED IN A FUTURE UPDATE
	
    // suwp_cron_get_account_info( $suwp_apiproviders );
    
    // suwp_cron_get_file_order_details( $suwp_apiproviders );
    
    // suwp_cron_get_imeiservice_list( $suwp_apiproviders );
    
    // suwp_cron_get_model_list( $suwp_apiproviders );
    
    // suwp_cron_get_provider_list( $suwp_apiproviders );
    
    // suwp_cron_get_single_imei_service_details( $suwp_apiproviders );
    
    // suwp_cron_place_file_order( $suwp_apiproviders );
    
	// udpate Product Regular price if enabled to do so
	suwp_cron_auto_update_prices( $suwp_apiproviders );
	
}

function suwp_cron_auto_update_prices( $suwp_apiproviders ) {
	
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
		
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
		
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'AUTO UPDATE PRODUCT REGULAR PRICE',
            'cron_comment' => 'auto update product regular price',
            'template_constants' => 'api/cron/get_single_imei_service_details_constants_cron_template.txt',
            'template_api' => 'api/cron/get_single_imei_service_details_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_single_imei_service_details_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_single_imei_service_details_api_' . $post_id . '_cron.php',
        );
		
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
			// update the regular price
            $api_results = suwp_api_cron_action( $post_id, 11 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
	
}

function suwp_cron_place_imei_orders( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
	
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
        
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'PLACING ORDERS',
            'cron_comment' => 'place the orders',
            'template_constants' => 'api/cron/place_imei_order_constants_cron_template.txt',
            'template_api' => 'api/cron/place_imei_order_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/place_imei_order_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/place_imei_order_api_' . $post_id . '_cron.php',
        );
        
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
            
            suwp_cron_file_creation( $file_array );
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
	
	$post_id = 0;
	
	// place the orders
	suwp_api_cron_action( $post_id, 4 );
    
}

function suwp_cron_check_imei_orders( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
	
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'CHECKING ORDERS',
            'cron_comment' => 'check the orders',
            'template_constants' => 'api/cron/get_imei_orders_details_constants_cron_template.txt',
            'template_api' => 'api/cron/get_imei_orders_details_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_imei_orders_details_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_imei_orders_details_api_' . $post_id . '_cron.php',
        );
        
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
            
            suwp_cron_file_creation( $file_array );
            
            // check the orders
            suwp_api_cron_action( $post_id, 2 );
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_account_info( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
	
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET ACCOUNT INFO',
            'cron_comment' => 'get account info',
            'template_constants' => 'api/cron/get_account_info_constants_cron_template.txt',
            'template_api' => 'api/cron/get_account_info_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_account_info_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_account_info_api_' . $post_id . '_cron.php',
        );
        
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get account info
            $api_results = suwp_api_cron_action( $post_id, 0 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_file_order_details( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET FILE ORDER DETAILS',
            'cron_comment' => 'get file order details',
            'template_constants' => 'api/cron/get_file_order_details_constants_cron_template.txt',
            'template_api' => 'api/cron/get_file_order_details_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_file_order_details_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_file_order_details_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get file order details
            $api_results = suwp_api_cron_action( $post_id, 6 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_imeiservice_list( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET IMEI SERVICE LIST',
            'cron_comment' => 'get imei service list',
            'template_constants' => 'api/cron/get_imeiservice_list_constants_cron_template.txt',
            'template_api' => 'api/cron/get_imeiservice_list_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_imeiservice_list_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_imeiservice_list_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get imei service list
            $api_results = suwp_api_cron_action( $post_id, 1 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_model_list( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET MODEL LIST',
            'cron_comment' => 'get model list',
            'template_constants' => 'api/cron/get_model_list_constants_cron_template.txt',
            'template_api' => 'api/cron/get_model_list_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_model_list_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_model_list_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get model list
            $api_results = suwp_api_cron_action( $post_id, 9 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_provider_list( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET PROVIDER LIST',
            'cron_comment' => 'get provider list',
            'template_constants' => 'api/cron/get_provider_list_constants_cron_template.txt',
            'template_api' => 'api/cron/get_provider_list_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_provider_list_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_provider_list_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get provider list
            $api_results = suwp_api_cron_action( $post_id, 10 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_get_single_imei_service_details( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'GET SINGLE IMEI SERVICE DETAILS',
            'cron_comment' => 'get single imei service details',
            'template_constants' => 'api/cron/get_single_imei_service_details_constants_cron_template.txt',
            'template_api' => 'api/cron/get_single_imei_service_details_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/get_single_imei_service_details_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/get_single_imei_service_details_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // get single imei service details
            $api_results = suwp_api_cron_action( $post_id, 3 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_place_file_order( $suwp_apiproviders ) {
    
    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
    
    foreach( $suwp_apiproviders as $apiprovider ):
    
        $file_array = array();
        $post_id = $apiprovider->ID;
        $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
    
        error_log('post_id = ' . $post_id . ' - suwp_activeflag : ');
        error_log(print_r($suwp_activeflag,true));
        
        $file_array = array(
            'post_id' => $post_id,
            'old_text' => 'SUAPIPROVIDERNUM',
            'new_text' => $post_id,
            'cron_type' => 'PLACE FILE ORDER',
            'cron_comment' => 'place file order',
            'template_constants' => 'api/cron/place_file_order_constants_cron_template.txt',
            'template_api' => 'api/cron/place_file_order_api_cron_template.txt',
            'provider_constants' => 'api/cron/providers/place_file_order_constants_' . $post_id . '_cron.php',
            'provider_api' => 'api/cron/providers/place_file_order_api_' . $post_id . '_cron.php',
        );
    
        if ( $suwp_activeflag ) {
            
            // only proceed if api provider's profile is enabled
         
            suwp_cron_file_creation( $file_array );
            
            // place file order
            $api_results = suwp_api_cron_action( $post_id, 5 );
            error_log(print_r($api_results,true));
            
        } //  if ( $suwp_activeflag )
        
    endforeach; // foreach( $suwp_apiprovider as $apiprovider )
    
}

function suwp_cron_file_creation( $file_array ) {
    
    $FilePathTemplateConstants = plugin_dir_path( __FILE__ ) . $file_array['template_constants'];
    $FilePathTemplateApi = plugin_dir_path( __FILE__ ) . $file_array['template_api'];
    
    // does this provider have the specific constants api file?
    $CheckFilePathConstants = plugin_dir_path( __FILE__ ) . $file_array['provider_constants'];
    $CheckFilePathApi = plugin_dir_path( __FILE__ ) . $file_array['provider_api'];
    
    $result = suwp_create_file($CheckFilePathConstants, $FilePathTemplateConstants);
    
    // error_log($file_array['cron_type'] . ' - CREATE/ACCESS UNIQUE FILE CRON CONSTANTS:');
    // error_log(print_r($result,true));
    
    // $result = array('status' => false, 'message' => 'file already exists');
    
    if ( $result['status'] ) {
        // file did not exist, just created it, modify the contents
        $FilePathTarget = $CheckFilePathConstants;
        $msg = suwp_replace_in_file($FilePathTarget, $file_array['old_text'], $file_array['new_text']);
        // error_log($file_array['cron_type'] . ' - UPDATING NEW FILE CRON CONSTANTS:');
        // error_log(print_r($msg,true));
    }
    
    // does this provider have the specific api file?
    $result = suwp_create_file($CheckFilePathApi, $FilePathTemplateApi);
    
    // $result = array('status' => false, 'message' => 'file already exists');
    
    // error_log($file_array['cron_type'] . ' - CREATE/ACCESS UNIQUE FILE CRON API:');
    // error_log(print_r($result,true));
    
    if ( $result['status'] ) {
        // file did not exist, just created it, modify the contents
        $FilePathTarget = $CheckFilePathApi;
        $msg = suwp_replace_in_file($FilePathTarget, $file_array['old_text'], $file_array['new_text']);
        // error_log($file_array['cron_type'] . ' - UPDATING NEW FILE CRON API:');
        // error_log(print_r($msg,true));
    }
    
    error_log( '' );
    error_log( $result["message"] );
    error_log( $file_array['cron_comment'] );
    error_log( '' );
}


// in order to get our task to execute we must create our own custom hook
// the first parameter is the name of the hook, and the second is the name of our function to call.
add_action( 'suwp_cron_hook_5seconds', 'suwp_cron_exec_5seconds' );
add_action( 'suwp_cron_hook_5minutes', 'suwp_cron_exec_5minutes' );
add_action( 'suwp_cron_hook_15minutes', 'suwp_cron_exec_15minutes' );
add_action( 'suwp_cron_hook_30minutes', 'suwp_cron_exec_30minutes' );
add_action ('suwp_cron_hook_1hour', 'suwp_cron_exec_1hour' );
add_action ('suwp_cron_hook_3hours', 'suwp_cron_exec_3hours' );

// this function will not only unschedule the task indicated by the timestamp,
// it will also unschedule all future occurrences of the task.
register_deactivation_hook( __FILE__, 'suwp_cron_hook_deactivate' );

function suwp_cron_hook_deactivate() {
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_5seconds' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_5seconds' );
    
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_5minutes' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_5minutes' );
    
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_15minutes' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_15minutes' );
    
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_30minutes' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_30minutes' );
    
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_1hour' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_1hour' );
    
    $timestamp = wp_next_scheduled( 'suwp_cron_hook_3hours' );
    wp_unschedule_event($timestamp, 'suwp_cron_hook_3hours' );
    
}

// 1.2
// register custom admin column headers
add_filter('manage_edit-suwp_apiprovider_columns','suwp_apiprovider_column_headers');

// 1.3
// register custom admin column data ; including 1,2 means that we want column name AND post id.
add_filter('manage_suwp_apiprovider_posts_custom_column','suwp_apiprovider_column_data',1,2);
add_action(
    'admin_head-edit.php',
    'suwp_register_custom_admin_titles'
);


// 1.4
// register ajax actions (giving permission to submit forms, for example)
add_action('wp_ajax_suwp_parse_import_csv', 'suwp_parse_import_csv'); // admin users
add_action('wp_ajax_suwp_api_action', 'suwp_api_action'); // admin users
add_action('wp_ajax_suwp_import_services', 'suwp_import_services'); // admin users
add_action('wp_ajax_suwp_parse_import_api', 'suwp_parse_import_api'); // admin users
add_action('wp_ajax_suwp_download_services_csv', 'suwp_download_services_csv'); // admin users

// 1.5
// load external files to public website
add_action('wp_enqueue_scripts', 'suwp_public_scripts');

// 1.6
// Advanced Custom Fields Settings
// Including ACF is allowed as per: https://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
add_filter('acf/settings/path', 'suwp_acf_settings_path');
add_filter('acf/settings/dir', 'suwp_acf_settings_dir');
add_filter('acf/settings/show_admin', 'suwp_acf_show_admin');
if( !defined('ACF_LITE') ) define('ACF_LITE',true); // turn off ACF plugin menu

// 1.7 
// register our custom menus
add_action('admin_menu', 'suwp_admin_menus');

// 1.8
// load external files in WordPress admin
add_action('admin_enqueue_scripts', 'suwp_admin_scripts');

// 1.9
// register plugin options
add_action('admin_init', 'suwp_register_options');

// 1.10
// register activate/deactivate/uninstall functions
register_activation_hook( __FILE__, 'suwp_activate_plugin' );
add_action( 'admin_notices', 'suwp_check_wp_version' );
register_uninstall_hook( __FILE__, 'suwp_uninstall_plugin' );

// 2.1
// registers all our custom shortcodes, etc.
// labels appear above all orders as categories: not sure wher 'label' appears yet
// 'label_count' -> first is for singular, second is for multiple
function suwp_register_allcodes() {
    
	suwp_check_woo();
	
    // create the suwp_service product category
    suwp_insert_custom_category();
    
    // create the SU import post status
    suwp_insert_custom_post_status();
    
    // register custom post statuses
    register_post_status('wc-suwp-rejected', array(
        'label' => 'Paypal Rejected',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Paypal rejected <span class="count">(%s)</span>', 'Paypal rejected <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-error', array(
        'label' => 'Processing Error',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Processing error <span class="count">(%s)</span>', 'Processing error <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-ordered', array(
        'label' => 'Code Ordered',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Code ordered <span class="count">(%s)</span>', 'Code ordered <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-order-part', array(
        'label' => 'Partially Ordered',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Partially ordered <span class="count">(%s)</span>', 'Partially ordered <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-pending', array(
        'label' => 'Code Pending',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Code pending <span class="count">(%s)</span>', 'Code pending <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-available', array(
        'label' => 'Code Delivered',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Code delivered <span class="count">(%s)</span>', 'Code delivered <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-avail-part', array(
        'label' => 'Codes Partially Delivered',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Codes partially delivered <span class="count">(%s)</span>', 'Codes partially delivered <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-unavailable', array(
        'label' => 'Code Unavailable',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Code unavailable <span class="count">(%s)</span>', 'Code unavailable <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-refunding', array(
        'label' => 'Code Pending Refund',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Code Pending refund <span class="count">(%s)</span>', 'Code Pending refund <span class="count">(%s)</span>')
    ));
    
    register_post_status('wc-suwp-refund-part', array(
        'label' => 'Codes Partially Refunded',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Codes Partially refunded <span class="count">(%s)</span>', 'Codes Partially refunded <span class="count">(%s)</span>')
    ));
    
}

// creates the suwp_service category
function suwp_insert_custom_category() {
    
	if(!term_exists('suwp_service')) {
		wp_insert_term(
			'Remote Service',
			'product_cat',
			array(
			  'description'	=> 'Mobile unlocking service',
			  'slug' 		=> 'suwp_service'
			)
		);
	}
    
}

function suwp_insert_custom_post_status(){
    
	register_post_status( 'imported', array(
		'label'                     => _x( 'Imported', 'post' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Imported <span class="count">(%s)</span>', 'Imported <span class="count">(%s)</span>' ),
	) );
    
}

function suwp_append_post_status_list(){
    // $("#save-post").text("Save Imported"); // supposed to change the button text, but doesn't
    // this actually changes the button, but not at the right time: $("#save-post").val("Save Imported");
    // removed this: $(".misc-pub-section label").append("'.$label.'"); it was slapping labels all over the place
     global $post;
     $complete = '';
     $label = '';
     if($post->post_type == 'product'){
          if($post->post_status == 'imported'){
               $complete = 'selected=\"selected\"';
               $label = '<span id=\"post-status-display\"> Imported</span>';
          }
          echo '
          <script>
          jQuery(document).ready(function($){
               $("#post_status").val("imported");
               $("select#post_status").append("<option value=\"imported\" '.$complete.'>Imported</option>");
          });
          </script>
          ';
     }
}
add_action('admin_footer-post.php', 'suwp_append_post_status_list');

function suwp_display_imported_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != 'imported'){
          if($post->post_status == 'imported'){
               return array('Imported');
          }
     }
    return $states;
}
add_filter( 'display_post_states', 'suwp_display_imported_state' );


// Add to list of WC Order statuses
// These labels appear in browser for customer and when editing the order as admin
function suwp_add_custom_order_statuses($order_statuses) {
    $new_order_statuses = array();

    // add new order status after processing
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-suwp-rejected'] = 'Paypal rejected';
            $new_order_statuses['wc-suwp-error'] = 'Processing error';
            $new_order_statuses['wc-suwp-ordered'] = 'Code ordered';
            $new_order_statuses['wc-suwp-order-part'] = 'Partially ordered';
            $new_order_statuses['wc-suwp-pending'] = 'Code pending';
            $new_order_statuses['wc-suwp-available'] = 'Code delivered';
            $new_order_statuses['wc-suwp-avail-part'] = 'Codes partially delivered';
            $new_order_statuses['wc-suwp-unavailable'] = 'Code unavailable';
            $new_order_statuses['wc-suwp-refunding'] = 'Code Pending refund';
            $new_order_statuses['wc-suwp-refund-part'] = 'Codes Partially refunded';
        }
    }
    return $new_order_statuses;
}
add_filter('wc_order_statuses', 'suwp_add_custom_order_statuses');

// Admin reports for custom order status
// Just drop "wc" as the prefix
function suwp_reports_get_order_custom_report_data_args( $args ) {
    $args['order_status'] = array(
                                  'completed',
                                  'processing',
                                  'on-hold',
                                  'suwp-rejected',
                                  'suwp-error',
                                  'suwp-ordered',
                                  'suwp-order-part',
                                  'suwp-pending',
                                  'suwp-available',
                                  'suwp-avail-part',
                                  'suwp-unavailable',
                                  'suwp-refunding',
                                  'suwp-refund-part'
                                  );
    
    return $args;
};
add_filter( 'woocommerce_reports_get_order_report_data_args', 'suwp_reports_get_order_custom_report_data_args');

// 3.2.2
// registers special custom admin title columns
function suwp_register_custom_admin_titles() {
    add_filter(
        'the_title',
        'suwp_custom_admin_titles',
        99,
        2
    );
}

// 3.2.3
// handles custom admin title "title" column data for post types without titles
function suwp_custom_admin_titles( $title, $post_id ) {
   
    global $post;
	
    $output = $title;
   
    if( isset($post->post_type) ):
                switch( $post->post_type ) {
                        case 'suwp_apiprovider':
                                $provider = get_field('suwp_sitename', $post_id );
	                            $output = $provider;
	                            break;
                }
        endif;
   
    return $output;
}

// 3.x
function suwp_apiprovider_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('Site Name'),
		'active'=>__('Active'),
		'url'=>__('API URL'),
		'post_id'=>__('Post ID'),
		'api_key'=>__('API Key'),
	);
	
	// returning new columns
	return $columns;
	
}

// 3.x
function suwp_apiprovider_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		
		case 'title':
			// get the custom provider name data
			$provider = get_field('suwp_sitename', $post_id ); // $title = get_the_title( $post_id );
			$output .= $provider;
			break;
		case 'active':
			// get the site active data
            $suwp_activeflag = (int)get_field('suwp_activeflag', $post_id );
            $active = 'No';
            
            switch( $suwp_activeflag ) {
                case 0:
                    $active = 'No';
                    break;
                case 1:
                    $active = 'Yes';
                    break;
            } 
            
			$output .= $active;
			break;
		case 'url':
			// get the site url data
			$urlsite = get_field('suwp_url', $post_id );
			$output .= $urlsite;
			break;
		case 'post_id':
			// get the post identifier
			$output .= $post_id .' status: '.get_post_status( $post_id );
			break;
		case 'api_key':
			// get the api key
            $apidetails = suwp_dhru_get_provider_array( $post_id );
            $output .= $apidetails['suwp_dhru_api_key'];
			break;
		
	}
	
	// echo the output
	echo $output;
	
}

// 3.5
// registers custom plugin admin menus
function suwp_admin_menus() {
    
	/* main menu */
	
		$top_menu_item = 'suwp_dashboard_admin_page';
	    
	    add_menu_page( '', 'StockUnlocks', 'manage_options', 'suwp_dashboard_admin_page', 'suwp_dashboard_admin_page', 'dashicons-unlock' );
    
    /* submenu items */
    
	    // dashboard
	    add_submenu_page( $top_menu_item, '', 'Dashboard', 'manage_options', $top_menu_item, $top_menu_item );
        
	    // api provider list
	    add_submenu_page( $top_menu_item, '', 'Providers', 'manage_options', 'edit.php?post_type=suwp_apiprovider' ); // actually linking to the WP edit post page
        
	    // import services
	    add_submenu_page( $top_menu_item, '', 'Import Services', 'manage_options', 'suwp_importservices_admin_page', 'suwp_importservices_admin_page' );
        
	    // plugin options
	    add_submenu_page( $top_menu_item, '', 'Plugin Options', 'manage_options', 'suwp_options_admin_page', 'suwp_options_admin_page' );

}


/* !4. EXTERNAL SCRIPTS */

// 4.1
// Including ACF is allowed as per: https://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
include_once( plugin_dir_path( __FILE__ ) .'lib/advanced-custom-fields/acf.php' );
include_once( plugin_dir_path( __FILE__ ) .'api/cron/suwp_cron_email_templates.php' );

// 4.2
// loads external files into PUBLIC website
function suwp_public_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_script('stockunlocks-js-public', plugins_url('/js/public/stockunlocks.js?ver=1.1.0',__FILE__), array('jquery'),'',true);
	wp_register_style('stockunlocks-css-public', plugins_url('/css/public/stockunlocks.css?ver=1.1.0',__FILE__));
    
	// add to que of scripts that get loaded into every page
	wp_enqueue_script('stockunlocks-js-public');
	wp_enqueue_style('stockunlocks-css-public');
	
}

// 4.3
// loads external files into wordpress ADMIN
function suwp_admin_scripts() {

	// register scripts with WordPress's internal library
	wp_register_script('stockunlocks-js-private', plugins_url('/js/private/stockunlocks.js?ver=1.1.0',__FILE__), array('jquery'),'',true);
	
	// add to que of scripts that get loaded into every admin page
	wp_enqueue_script('stockunlocks-js-private');
	
}

/* !5. ACTIONS */

// 5.2
// creates a new service or updates and existing one
function suwp_save_service( $service_data ) {
	
    global $woocommerce;
 
	// get the default values for our options
	$options = suwp_get_current_options();
	
	error_log('');
	
    // flags for specific services : 'None' or 'Required'
    
	// setup default service id
	// 0 means the service was not saved
	$product_id = 0;
	
	// set up the product array
	// flag: 0 means added, 1 means updated
	$product_array = array(
		'product_id' => $product_id,
		'flag' => '',
	);
	
	try {
		
		$product_id = suwp_get_product_id( $service_data['apiproviderid'], $service_data['api'] );
    
        // Writing an array to the log: error_log(print_r($array,true));
        // error_log(print_r($service_data,true));
    
		// IF the product does not already exists...
		if ( !$product_id ) {
			
            // add new product to database
            error_log('----- NON-EXISTING : ADDING NEW PRODUCT TO THE DATABASE ----- ');
            $product_id = suwp_add_product_to_database( $service_data );
			
			$product_array = array(
				'product_id' => $product_id,
				'flag' => 0,
			);
			
		} else {
            error_log('----- PRODUCT ALREADY EXISTED IN THE DATABASE >>> UPDATE ITS VALUES ----- ');
			
			// will update existing product
			$product_array = array(
				'product_id' => $product_id,
				'flag' => 1,
			);
			
		}
        
		$service_credit = $service_data['credit'];
		
		// add/update custom meta data
		$flag = $product_array['flag'];
			
		if ( $flag ) {
			
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
		
		} else {
			
			// new import, simply set the Product service credit value (from Supplier)
			update_post_meta( $product_id, '_suwp_service_credit', $service_credit );
			update_post_meta( $product_id, '_regular_price', $service_credit );
			update_post_meta( $product_id, '_price', $service_credit );
			
		}// if ( $flag )
					
	} catch( Exception $e ) {
		
		// a php error occurred
        error_log('----- ERROR - ADDING OR CHECKING FOR PRODUCT TO THE DATABASE ----- ');
		error_log(print_r($e,true));
	}
	
	return $product_array;
	
}

function suwp_change_price_by_type( $product_id, $multiply_price_by, $price_type ) {
	$the_price = get_post_meta( $product_id, '_' . $price_type, true );
	$the_price *= $multiply_price_by;
	update_post_meta( $product_id, '_' . $price_type, $the_price );
}

function suwp_change_price_all_types( $product_id, $multiply_price_by ) {
	suwp_change_price_by_type( $product_id, $multiply_price_by, 'price' );
	suwp_change_price_by_type( $product_id, $multiply_price_by, 'sale_price' );
	suwp_change_price_by_type( $product_id, $multiply_price_by, 'regular_price' );
}

/*
 * 'suwp_change_product_price' is main function you should call to change product's price (NOT TESTED)
 */
function suwp_change_product_price( $product_id, $multiply_price_by ) {
	suwp_change_price_all_types( $product_id, $multiply_price_by );	
	$product = wc_get_product( $product_id ); // Handling variable products
	if ( $product->is_type( 'variable' ) ) {
		$variations = $product->get_available_variations();
		foreach ( $variations as $variation ) {
			suwp_change_price_all_types( $variation['variation_id'], $multiply_price_by );
		}
	}
}

function suwp_add_product_to_database( $product_data ) {

    $suwp_api_provider = $product_data['apiproviderid'];
    $suwp_api_service_id = $product_data['api'];
    $suwp_serial_length = 15;
	
    $user_id = get_current_user_id();
    $category = get_term_by( 'slug', 'suwp_service', 'product_cat' );
    $cat_id = $category->term_id;
    
	$regular_price_txt = sprintf("%01.2f", $product_data['credit']);
	
    // None or Required
    
    $post_id = wp_insert_post( array(
        'post_author' => $user_id,
        'post_title' => $product_data['name'],
        'post_content' => $product_data['info'],
        'post_status' => 'imported', // draft, publish
        'post_type' => "product",
    ) );
    
    wp_set_object_terms( $post_id, $cat_id, 'product_cat' );
    wp_set_object_terms( $post_id, 'simple', 'product_type' );
    update_post_meta( $post_id, '_visibility', 'visible' );
    update_post_meta( $post_id, '_stock_status', 'instock');
    update_post_meta( $post_id, 'total_sales', '0' );
    update_post_meta( $post_id, '_downloadable', 'no' );
    update_post_meta( $post_id, '_virtual', 'yes' );
    update_post_meta( $post_id, '_regular_price', $regular_price_txt );
    update_post_meta( $post_id, '_price', $regular_price_txt );
    update_post_meta( $post_id, '_sale_price', '' );
    update_post_meta( $post_id, '_purchase_note', '' );
    update_post_meta( $post_id, '_featured', 'no' );
    update_post_meta( $post_id, '_weight', '' );
    update_post_meta( $post_id, '_length', '' );
    update_post_meta( $post_id, '_width', '' );
    update_post_meta( $post_id, '_height', '' );
    update_post_meta( $post_id, '_sku', '' );
    update_post_meta( $post_id, '_product_attributes', array() );
    update_post_meta( $post_id, '_sale_price_dates_from', '' );
    update_post_meta( $post_id, '_sale_price_dates_to', '' );
    update_post_meta( $post_id, '_price', '' );
    update_post_meta( $post_id, '_sold_individually', '' );
    update_post_meta( $post_id, '_manage_stock', 'no' );
    update_post_meta( $post_id, '_backorders', 'no' );
    update_post_meta( $post_id, '_stock', '' );
    update_post_meta( $post_id, '_suwp_serial_length', $suwp_serial_length );
    update_post_meta( $post_id, '_suwp_api_provider', $suwp_api_provider );
    update_post_meta( $post_id, '_suwp_api_service_id', $suwp_api_service_id );
    update_post_meta( $post_id, '_suwp_process_time', $product_data['time'] );
    update_post_meta( $post_id, '_suwp_service_credit', $product_data['credit'] );
    update_post_meta( $post_id, '_suwp_price_group_name', $product_data['groupname'] );
    update_post_meta( $post_id, '_suwp_service_notes', $product_data['info'] );
    update_post_meta( $post_id, '_suwp_is_network', $product_data['provider'] );
    update_post_meta( $post_id, '_suwp_is_model', $product_data['mobile'] );
    update_post_meta( $post_id, '_suwp_is_pin', $product_data['pin'] );
    update_post_meta( $post_id, '_suwp_is_kbh', $product_data['kbh'] );
    update_post_meta( $post_id, '_suwp_is_mep', $product_data['mep'] );
    update_post_meta( $post_id, '_suwp_is_rm_type', $product_data['type'] );
    update_post_meta( $post_id, '_suwp_is_reference', $product_data['reference'] );
    update_post_meta( $post_id, '_suwp_online_status', 'yes' );

    return $post_id;
    
}

// sample Provider entry
function suwp_add_stockunlocks_to_database() {

    static $suwp_default_created;
    
    if ( $suwp_default_created === null ) {
            
        $user_id = get_current_user_id();
        
        $post_id = wp_insert_post( array(
            'post_author' => $user_id,
            'post_title' => 'The Real 2',
            'post_content' => '',
            'post_status' => 'publish', // draft, publish
            'post_type' => "suwp_apiprovider",
        ) );
        
        update_post_meta( $post_id, 'suwp_activeflag', '1' );
        update_post_meta( $post_id, 'suwp_sitename', 'StockUnlocks');
        update_post_meta( $post_id, 'suwp_url', 'http://reseller.stockunlocks.com/' );
        update_post_meta( $post_id, 'suwp_username', 'Your assigned username' );
        update_post_meta( $post_id, 'suwp_apikey', 'XXX-XXX-XXX-XXX-XXX-XXX-XXX-XXX' );
        update_post_meta( $post_id, 'suwp_apinotes', 'Your Own Mobile Unlocking Website. Please visit http://reseller.stockunlocks.com/singup.html for account creation.' );
        
        $suwp_default_created = $post_id;
            
    }
    
    return $suwp_default_created;
    
}

// 5.8
// creates custom tables for the plugin
function suwp_create_plugin_tables() {
	
	global $wpdb;
	
	// setup return value
	$return_value = false;
	
	try {
		
		$table_name = $wpdb->prefix . "suwp_reward_links";
		$charset_collate = $wpdb->get_charset_collate();
	
		// sql for our table creation
		$sql = "CREATE TABLE $table_name (
			id mediumint(11) NOT NULL AUTO_INCREMENT,
			uid varchar(128) NOT NULL,
			product_id varchar(128) NOT NULL,
			subscriber_id mediumint(11) NOT NULL,
			list_id mediumint(11) NOT NULL,
			attachment_id mediumint(11) NOT NULL,
			downloads mediumint(11) DEFAULT 0 NOT NULL ,
			UNIQUE KEY id (id)
			) $charset_collate;";
		
		// make sure we include wordpress functions for dbDelta	
		require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			
		// dbDelta will create a new table if none exists or update an existing one
		dbDelta($sql);
		
		// return true
		$return_value = true;
	
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return result
	return $return_value;
	
}

// retrieve the support id
function suwp_get_support_id() {
	
	$suwp_product_id = 'A50-B46-A01-64B-2F6-DC7-791-417';
	
	global $wpdb;
	
    $list_id = 0; // specifically for support related issues
	
	// setup our return value
	$return_value = -1;
	
	try {
		
		$table_name = $wpdb->prefix . "suwp_reward_links";
		
		// after 1.0.1 > have to check if product_id column exists first
		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '". $table_name . "' AND column_name = 'product_id'"  );
		
		if(empty($row)){
		   $wpdb->query("ALTER TABLE " . $table_name . " ADD product_id varchar(128) NOT NULL");
		}
		
		// get the product id
		$id = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT id 
					FROM $table_name 
					WHERE product_id = '" . $suwp_product_id . "' AND list_id = %s
				", 
				$list_id
			) 
		);
		
		// set return value
		if ($id) {
			$return_value = $id;
		} else {
			
			$uid = suwp_uuid_make();
			$subscriber_id = get_current_user_id();
			
			// insert the product identifier
			$wpdb->insert(
				$table_name, 
				array( 
					'uid' => $uid, 
					'product_id' => $suwp_product_id, 
					'subscriber_id' => $subscriber_id,
					'list_id' => $list_id, 
					'attachment_id' => $subscriber_id, 
				), 
				array( 
					'%s', 
					'%s', 
					'%d',
					'%d', 
				) 
			);
			
		}
		
		// get the uid
		$uid = $wpdb->get_var( 
			$wpdb->prepare( 
				"
					SELECT uid 
					FROM $table_name 
					WHERE product_id = '" . $suwp_product_id . "' AND list_id = %s
				", 
				$list_id
			) 
		);
		
		$return_value = $uid;
		
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	return $return_value;
	
}

// creates unique id for general use
function suwp_uuid_make(){
	
	$string = substr( strtoupper( md5( date('dmYHisu') ) ),0,24 );

	$string = substr($string, 0, 3 ) .'-'.
	substr($string, 3, 3) .'-'.
	substr($string, 6, 3) .'-'.
	substr($string, 9, 3) .'-'.
	substr($string, 12, 3) .'-'.
	substr($string, 15, 3) .'-'.
	substr($string, 18, 3) .'-'.
	substr($string, 21);
	
	return $string;

}

// 5.9
// runs on plugin activation
function suwp_activate_plugin() {
	
	// make sure schedules are ready
	suwp_verify_cron_schedule();
	
	// setup custom database tables
	suwp_create_plugin_tables();
    
}

// 5.13
// generates a .csv file of services data
// expects $_GET['provider_id'] to be set in the URL
function suwp_download_services_csv() {
    
	// reset flag to remove the "No Unlocking Products to export." notice
    $_SESSION['suwp_services_export_flag'] = '1';

	// get the provider id from the URL scope
	$provider_id = ( isset($_GET['provider_id']) ) ? (int)$_GET['provider_id'] : 0;
	
	// setup our return data
	$csv = '';
	
	// get the provider object
	$provider = get_post( $provider_id );
	
	// get the provider's services or get all services if no provider id is given
	$services = suwp_get_provider_services( $provider_id );
	
	// IF we have confirmed services
	if( $services !== false ):
	
		// get the current date
		$now = new DateTime();
		
		// setup a unique filename for the generated export file
		$fn1 = 'stockunlocks-export-provider_id-'. $provider_id .'-date-'. $now->format('Ymd'). '.csv';
		$fn2 = plugin_dir_path( __FILE__ ) .'exports/'.$fn1;
		
		// open new file in write mode
		$fp = fopen($fn2, 'w');
		
		// get the first services' data
		$service_data = suwp_get_service_data( $services[0] );
		
		// remove the subscriptions and name column from the data
		// unset($service_data['subscriptions']);
		// unset($service_data['name']);
		
		// build our csv headers array from $service_data's data keys
		$csv_headers = array();
		foreach( $service_data as $key => $value ):
			array_push($csv_headers, $key);
		endforeach;
		
		// append $csv_headers to our csv file
		fputcsv($fp, $csv_headers);
	
		// loop over all our services
		foreach( $services as &$service_id ):
	
			// get the service data of the current service
			$service_data = suwp_get_service_data( $service_id );
		
			// remove the subscriptions and name columns from the data
			// unset($service_data['subscriptions']);
			// unset($service_data['name']);
			
			// append this services' data to our csv file
			fputcsv($fp, $service_data);
		
		endforeach;
		
		// read open our new file is read mode
		$fp = fopen($fn2, 'r');
		// read our new csv file and store it's contents in $fc
		$fc = fread($fp, filesize($fn2) );
		// close our open file pointer
		fclose($fp);
	
		// setup file headers
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=".$fn1);
		// echo the contents of our file and return it to the browser
		echo($fc);
		// exit php processes 
		exit;
        
	else:
        
        wp_redirect(admin_url('/admin.php?page=suwp_dashboard_admin_page'), 302);
        
		// set the flag to display the "No Unlocking Products to export." notice
        $_SESSION['suwp_services_export_flag'] = '0';
        
        // stop all other processing 
        exit;
    
	endif;
	
	// return false if we were unable to download our csv
	return false;
	
}

function suwp_no_services_admin_notice() {

    $screen = get_current_screen();
	
	if ( $screen->id == 'toplevel_page_suwp_dashboard_admin_page' ) {
		
		if (isset( $_SESSION['suwp_services_export_flag']) ) {
			
			if ( $_SESSION['suwp_services_export_flag'] == '0') {
				$notice = suwp_get_admin_notice('No Unlocking Products to export.','notice notice-warning is-dismissible');
				echo $notice;
				
			}
		}
	}
}
add_action( 'admin_notices', 'suwp_no_services_admin_notice' );

function suwp_troubleshooting_admin_notice() {
	
    $screen = get_current_screen();
	$import_services_url = '';
	
	if ( $screen->id == 'stockunlocks_page_suwp_importservices_admin_page' ) {
		$import_services_url = '. <a href="'. admin_url('/admin.php?page=suwp_options_admin_page') .'">Disable here</a>';
	}
	
	// get the default values for our options
	$options = suwp_get_current_options();
    $troubleshoot_items = $options['suwp_manage_troubleshoot_run_id'];
	// parent_base ; parent_file ; suwp_dashboard_admin_page ; suwp_importservices_admin_page
	if ( ( $screen->id == 'stockunlocks_page_suwp_importservices_admin_page' || $screen->id == 'stockunlocks_page_suwp_options_admin_page' ) && $troubleshoot_items > 0 ) {
			$notice = suwp_get_admin_notice('Troubleshooting Option is enabled. Now limiting Service Imports to : ' . $troubleshoot_items . '' . $import_services_url,'notice notice-error ');
			echo $notice;
	}
}
add_action( 'admin_notices', 'suwp_troubleshooting_admin_notice' );

function suwp_cron_disabled_admin_notice() {
	
    $screen = get_current_screen();
	$import_services_url = '';
	
	if ( $screen->id !== 'stockunlocks_page_suwp_options_admin_page' ) {
		$import_services_url = '<a href="'. admin_url('/admin.php?page=suwp_options_admin_page') .'">Enable here</a>';
	}
	
	// get the default values for our options
	$options = suwp_get_current_options();
    $cron_run = $options['suwp_manage_cron_run_id'];
	
	if ( ( $screen->parent_file == 'suwp_dashboard_admin_page' ) && $cron_run == '') {
			$notice = suwp_get_admin_notice('Cron is disabled for the StockUnlocks plugin. No orders are being processed with your Provider(s). Automated emails are not being sent out. '. $import_services_url,'notice notice-error ');
			echo $notice;
	}
}
add_action( 'admin_notices', 'suwp_cron_disabled_admin_notice' );

// 5.x
// returns the results based on the api action id and the api provider id
// expects $_GET['$api_provider_id'] and $_GET['$api_action_id'] to be set in the URL
function suwp_api_action() {
	
	// get the api provider id from the URL scope
	$api_provider_id = ( isset($_GET['$api_provider_id']) ) ? (int)$_GET['$api_provider_id'] : 0;
    
	// get the api action id from the URL scope
	$api_action_id = ( isset($_GET['$api_action_id']) ) ? (int)$_GET['$api_action_id'] : 0;
    
    // echo '$api_provider_id = '.$api_provider_id.'<br>';
    // echo '$api_action_id = '.$api_action_id.'<br>';
    
    switch( $api_action_id ) {
		
		case 0:
            // return the account details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_account_info.php');
			break;
		case 1:
            // return a list of all imei services
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_imeiservice_list.php');
			break;
		case 2:
            // return all imei orders details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_imei_orders_details.php');
			break;
		case 3:
            // return a single imei service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_single_imei_service_details.php');
			break;
		case 4:
            // place an imei order
            include_once( plugin_dir_path( __FILE__ ) . 'api/place_imei_order.php');
			break;
		case 5:
            // place a file oder
            include_once( plugin_dir_path( __FILE__ ) . 'api/place_file_order.php.php');
			break;
		case 6:
            // return file order details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_file_order_details.php');
			break;
		case 7:
            // return a list of all file service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_fileservice_list.php');
			break;
		case 8:
            // return a list of all mep service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_mep_list.php');
			break;
		case 9:
            // return a list of all models
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_model_list.php');
			break;
		case 10:
            // return a list of all providers
            include_once( plugin_dir_path( __FILE__ ) . 'api/get_provider_list.php');
			break;
        
	}


}

function suwp_api_ui_action( $post_id = 0, $api_action_id = 0 ) {

    switch( $api_action_id ) {
		
		case 1:
            // return a list of all imei services
            include_once( plugin_dir_path( __FILE__ ) . 'api/ui/get_imeiservice_list_ui.php' );
            return suwp_dhru_get_imeiservice_list_ui( $post_id );
			break;
		case 4:
            // place an imei order
            include_once( plugin_dir_path( __FILE__ ) . 'api/ui/place_imei_order_ui.php');
			
			// hard coding for testing
			 $suwp_dhru_imei = '000000000000000';
			 $suwp_dhru_serviceid = '14'; // 14 = TEST - Available ; 23 = TEST - Unavailable
			 
            return suwp_dhru_place_imei_order_ui( $post_id, $suwp_dhru_imei, $suwp_dhru_serviceid );
			break;
        
	}
	
}

function suwp_api_cron_action( $post_id, $api_action_id ) {
    
    switch( $api_action_id ) {
		
		case 0:
            // OK - return the account details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_account_info_cron.php' );
            return suwp_dhru_get_account_info_cron( $post_id );
			break;
		case 1:
            // OK - return a list of all imei services
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_imeiservice_list_cron.php' );
            return suwp_dhru_get_imeiservice_list_cron( $post_id );
			break;
		case 2:
            // return all imei orders details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_imei_orders_details_cron.php' );
            suwp_dhru_get_imei_order_details_cron( $post_id );
			break;
		case 3:
            // return a single imei service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_single_imei_service_details_cron.php' );
            return suwp_dhru_get_single_imei_service_details_cron( $post_id );
			break;
		case 4:
            // place an imei order
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/place_imei_order_cron.php' );
            suwp_dhru_place_imei_order_cron( $post_id );
			break;
		case 5:
            // place a file oder
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/place_file_order_cron.php' );
            return suwp_dhru_place_file_order_cron( $post_id );
			break;
		case 6:
            // return file order details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_file_order_details_cron.php' );
            return suwp_dhru_get_file_order_details_cron( $post_id );
			break;
		case 7:
            // return a list of all file service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_fileservice_list_cron.php');
			break;
		case 8:
            // return a list of all mep service details
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_mep_list_cron.php' );
			break;
		case 9:
            // return a list of all models
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_model_list_cron.php' );
            return suwp_dhru_get_model_list_cron( $post_id );
			break;
		case 10:
            // return a list of all providers
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/get_provider_list_cron.php' );
            return suwp_dhru_get_provider_list_cron( $post_id );
			break;
		case 11:
            // update product regular price
            include_once( plugin_dir_path( __FILE__ ) . 'api/cron/update_regular_price_cron.php' );
            suwp_dhru_update_regular_price_cron( $post_id );
			break;
		
	}


}

function suwp_create_file($FilePath, $FilePathSource)
{
    $Result = array('status' => 0, 'message' => 'file already exists');
    
    if(file_exists($FilePath)===FALSE)
    {
        $handle = fopen($FilePath, 'w') or die('Cannot open file:  '.$FilePath); //implicitly creates file
    
        // error_log('fopen handle results:');
        
        // error_log(print_r($handle,true));
     
        if(is_writeable($FilePath))
        {
            try
            {
                $FileContent = file_get_contents($FilePathSource);
                if(file_put_contents($FilePath, $FileContent) > 0)
                {
                    $Result["status"] = 1;
                    $Result["message"] = 'Successfully wrote to new file';
                }
                else
                {
                    $Result["status"] = 0;
                    $Result["message"] = 'Error while writing file';
                }
            }
            catch(Exception $e)
            {
                $Result["status"] = 0;
                $Result["message"] = 'Error : '.$e;
            }
        }
        else
        {
            $Result["status"] = 0;
            $Result["message"] = 'File '.$FilePath.' is not writable !';
        }    
        
    }
    
    return $Result;
}

function suwp_replace_entire_file($FilePathOld, $FilePathNew)
{
    $Result = array('status' => 0, 'message' => 'replacing entire contents of file');
    if(file_exists($FilePathOld)===TRUE && file_exists($FilePathNew)===TRUE)
    {
        if(is_writeable($FilePathOld))
        {
            try
            {
                $FileContent = file_get_contents($FilePathNew);
                if(file_put_contents($FilePathOld, $FileContent) > 0)
                {
                    $Result["status"] = 1;
                }
                else
                {
                   $Result["message"] = 'Error while writing file';
                }
            }
            catch(Exception $e)
            {
                $Result["message"] = 'Error : '.$e;
            }
        }
        else
        {
            $Result["message"] = 'File '.$FilePathOld.' is not writable !';
        }
    }
    else
    {
        $Result["message"] = 'File '.$FilePathOld.' OR '.$FilePathNew. ' does not exist !';
    }
    return $Result;
}

function suwp_replace_in_file($FilePath, $OldText, $NewText)
{
    $Result = array('status' => 0, 'message' => 'replacing in file');
    if(file_exists($FilePath)===TRUE)
    {
        if(is_writeable($FilePath))
        {
            try
            {
                $FileContent = file_get_contents($FilePath);
                $FileContent = str_replace($OldText, $NewText, $FileContent);
                if(file_put_contents($FilePath, $FileContent) > 0)
                {
                    $Result["status"] = 1;
                }
                else
                {
                   $Result["message"] = 'Error while writing file';
                }
            }
            catch(Exception $e)
            {
                $Result["message"] = 'Error : '.$e;
            }
        }
        else
        {
            $Result["message"] = 'File '.$FilePath.' is not writable !';
        }
    }
    else
    {
        $Result["message"] = 'File '.$FilePath.' does not exist !';
    }
    return $Result;
}


// 5.x
// returns an array of essential api provider info
function suwp_dhru_get_provider_array( $post_id = 0 ) {

	// setup our return array
	$apidetails = array(
		'suwp_dhru_url'=>'',
		'suwp_dhru_username'=>'',
		'suwp_dhru_api_key'=>'',
		'suwp_dhru_api_notes'=>'',
	);
    
    $suwp_dhru_url = get_field('suwp_url', $post_id );
    $suwp_dhru_username = get_field('suwp_username', $post_id );
    $suwp_dhru_api_key = get_field('suwp_apikey', $post_id );
    $suwp_dhru_api_notes = get_field('suwp_apinotes', $post_id );
    
	$apidetails = array(
		'suwp_dhru_url'=>$suwp_dhru_url,
		'suwp_dhru_username'=>$suwp_dhru_username,
		'suwp_dhru_api_key'=>$suwp_dhru_api_key,
		'suwp_dhru_api_notes'=>$suwp_dhru_api_notes,
	);
    
    return $apidetails;
            
}

// 5.x
// this function retrieves services data from the remote server in the form of a php array
// it then returns that array in a json formatted object
// this function is an ajax post form handler
// expects: $_POST['suwp_import_provider_list_id']
function suwp_parse_import_api() {
	
	// setup our return array
	$result = array(
		'status' => 0,
        'provider_id' => '',
		'message' => 'Could not import remote services. ',
		'error' => '',
		'data1' => array(),
		'data2' => array(),
	);
	
	try {
	
		// get the provider id from $_POST['suwp_import_provider_list_id']
		$provider_id = (isset($_POST['suwp_import_provider_list_id'])) ? esc_attr( $_POST['suwp_import_provider_list_id'] ) : 0;
		
        $api_id = (int)sanitize_text_field($provider_id);
		
		if ( ! $api_id ) {
			$api_id = 0;
		}
		
        $reply = suwp_api_ui_action( $api_id, $api_action_id = 1 );
        
		// if there is a reply
		if( !empty($reply) ):
		
            // setup our return array
            $result = array(
                'status'=> 1,
                'provider_id' => $provider_id,
                'message'=> 'Imported remote services successfully.',
                'error'=> '',
                'data1'=> $reply[0],
                'data2'=> $reply[1],
            );
			
		else:
        
			// return an error message if we could not retrieve the file
			$result['error']='Failed connection to the remote server or no assigned services available. ';
			$result['data1']=$reply;
            
		
		endif;
        
	} catch( Exception $e ) {
		
		// php error
	}
	
	// return the result as json
	suwp_return_json( $result );
	
}

// 5.xx
// imports new services from our import admin page
// >>> this function is a form handler and expect services data in the $_POST scope
function suwp_import_services() {
	
	error_log( '' );
	error_log( '' );
	error_log('>>>>> ----- START IMPORT SUBMISSION ----- <<<<< ');
	error_log( '' );
	error_log( '' );
	
	// setup our return array
	$result = array(
		'status' => 0,
		'message' => 'Could not import services. ',
		'error' => '',
		'errors' => array(),
	);
	
	try {
		
		// get the assignment values
		$api_column = (isset($_POST['suwp_api_column'])) ? (int)$_POST['suwp_api_column'] : 0;
		$name_column = (isset($_POST['suwp_name_column'])) ? (int)$_POST['suwp_name_column'] : 0;
		$time_column = (isset($_POST['suwp_time_column'])) ? (int)$_POST['suwp_time_column'] : 0;
		$credit_column = (isset($_POST['suwp_credit_column'])) ? (int)$_POST['suwp_credit_column'] : 0;
		$groupname_column = (isset($_POST['suwp_groupname_column'])) ? (int)$_POST['suwp_groupname_column'] : 0;
		$info_column = (isset($_POST['suwp_info_column'])) ? (int)$_POST['suwp_info_column'] : 0;
		$network_column = (isset($_POST['suwp_network_column'])) ? (int)$_POST['suwp_network_column'] : 0;
		$mobile_column = (isset($_POST['suwp_mobile_column'])) ? (int)$_POST['suwp_mobile_column'] : 0;
		$provider_column = (isset($_POST['suwp_provider_column'])) ? (int)$_POST['suwp_provider_column'] : 0;
		$pin_column = (isset($_POST['suwp_pin_column'])) ? (int)$_POST['suwp_pin_column'] : 0;
		$kbh_column = (isset($_POST['suwp_kbh_column'])) ? (int)$_POST['suwp_kbh_column'] : 0;
		$mep_column = (isset($_POST['suwp_mep_column'])) ? (int)$_POST['suwp_mep_column'] : 0;
		$prd_column = (isset($_POST['suwp_prd_column'])) ? (int)$_POST['suwp_prd_column'] : 0;
		$type_column = (isset($_POST['suwp_type_column'])) ? (int)$_POST['suwp_type_column'] : 0;
		$locks_column = (isset($_POST['suwp_locks_column'])) ? (int)$_POST['suwp_locks_column'] : 0;
		$reference_column = (isset($_POST['suwp_reference_column'])) ? (int)$_POST['suwp_reference_column'] : 0;
        
		if ( ! $api_column ) {
			$api_column = 0;
		}
		
		if ( ! $name_column ) {
			$name_column = 0;
		}
		
		if ( ! $time_column ) {
			$time_column = 0;
		}
		
		if ( ! $credit_column ) {
			$credit_column = 0;
		}
		
		if ( ! $groupname_column ) {
			$groupname_column = 0;
		}
		
		if ( ! $info_column ) {
			$info_column = 0;
		}
		
		if ( ! $network_column ) {
			$network_column = 0;
		}
		
		if ( ! $mobile_column ) {
			$mobile_column = 0;
		}
		
		if ( ! $provider_column ) {
			$provider_column = 0;
		}
		
		if ( ! $pin_column ) {
			$pin_column = 0;
		}
		
		if ( ! $kbh_column ) {
			$kbh_column = 0;
		}
		
		if ( ! $mep_column ) {
			$mep_column = 0;
		}
		
		if ( ! $prd_column ) {
			$prd_column = 0;
		}
		
		if ( ! $type_column ) {
			$type_column = 0;
		}
		
		if ( ! $locks_column ) {
			$locks_column = 0;
		}
		
		if ( ! $reference_column ) {
			$reference_column = 0;
		}
		
		// get the provider id to import to
		$api_provider_id = (isset($_POST['suwp_selected_api_provider_id'])) ? (int)$_POST['suwp_selected_api_provider_id'] : 0;
        
		// check api_provider_id contents
		if ( !$api_provider_id ) {
				
			error_log('----- ERROR - NO api_provider_id DETECTED IN IMPORT SUBMISSION, SET TO ZERO ----- ');

			$api_provider_id = 0;
			
		} else {
			
			error_log('----- SUCCESS - api_provider_id DETECTED IN IMPORT SUBMISSION >>> ' . $api_provider_id . ' ----- ');

		}
		
		// get the selected services rows to import
		$selected_rows = (isset($_POST['suwp_import_rows'])) ? (array)$_POST['suwp_import_rows'] : array();
		
		$row_count = count($selected_rows);
		
		error_log('----- NUMBER OF ROWS RETURNED FROM IMPORT SUBMISSION >>> ' . $row_count . ' ----- ');

		$product_array = array();
					
		// setup a variable for counting the added and modified services
		$added_count = 0;
		$updated_count = 0;
		
		if ( is_array( $selected_rows ) ) {

			foreach ( $selected_rows as $id ) {
				
				// 'serviceid' is included when 'all' is selected
				// do a test for is_numeric to exclude it.
				// not including the first row, bec it's headers
				if ( is_numeric( $id ) ) {
					
					error_log('');
					error_log('----- PROCESSING API ROW DURING IMPORT SUBMISSION >>> ' . $id . ' ----- ');
					
					// build our service data 
					$service_data = array(
						'apiproviderid' => $api_provider_id,
						'api' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $api_column] ),
						'name' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $name_column] ),
						'time' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $time_column] ),
						'credit' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $credit_column] ),
						'groupname' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $groupname_column] ),
						'info' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $info_column] ),
						'network' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $network_column] ),
						'mobile' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $mobile_column] ),
						'provider' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $provider_column] ),
						'pin' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $pin_column] ),
						'kbh' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $kbh_column] ),
						'mep' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $mep_column] ),
						'prd' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $prd_column] ),
						'type' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $type_column] ),
						'locks' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $locks_column] ),
						'reference' => sanitize_text_field( (string)$_POST['suwp_'. $id .'_'. $reference_column] ),
					);
					
					// add the product to the database
					$product_array = suwp_save_service( $service_data );
					
					// flag: 0 means added, 1 means updated
					$product_id = $product_array['product_id'];
					
					// IF product was created or updated successfully
					if ( $product_id ) {
					
						// updated our added count
						error_log('----- OBTAINED PRODUCT ID DURING IMPORT SUBMISSION >>> ' . $product_id . ' ----- ');
						
						$flag = $product_array['flag'];
						
						if ( $flag ) {
							// updated existing product
							$updated_count++;
						} else {
							// added a new product
							$added_count++;
						}
						
					} else {
						
						error_log('----- ERROR - NO PRODUCT ID OBTAINED DURING IMPORT SUBMISSION, PRODUCT ID IS >>> ' . $product_id . ' ----- ');
						
					} // if ( $product_id ) {
					
				} else {
					
					error_log('----- SKIPPING ROW DURING IMPORT SUBMISSION BECAUSE ITS VALUE IS >>> ' . $id . ', NOT NUMERIC ----- ');

				} // if ( is_numeric( $id ) && !empty( $id ) ) {
			}
			
			// IF no products were actually added or updated...
			if( $added_count == 0 && $updated_count == 0):
			
				error_log('----- ERROR - NO SERVICES IMPORTED OR UPDATED INTO TO THE DATABASE >>> MODIFY wp-config.php or php.ini ----- ');
			
				// return error message
				$result['error'] = 'No services were imported or updated. Please select fewer services or modify memory settings in wp-config.php or php.ini';
			
			else:
			
				error_log('----- SUCCESS - ADDED OR UPDATED SERVICES IN THE DATABASE TOTAL >>> ADDED = ' . $added_count . ', UPDATED = ' . $updated_count . ' ----- ');
			
				// IF products were added...
				// return success!
				$result = array(
					'status' => 1,
					'message' => 'SUCCESS - Service(s) : ' . $added_count .' imported, ' . $updated_count . ' updated.',
					'error' => '',
					'errors' => array(),
				);
			
			endif;
		
		} // if ( is_array( $selected_rows ) ) 
	
	} catch( Exception $e ) {
		
		// php error
		error_log('----- PHP ERROR: suwp_import_services() ----- ');
		error_log(print_r($e,true));
		
	}
	
	error_log( '' );
	error_log( '' );
	error_log( '>>>>> ----- END IMPORT SUBMISSION ----- <<<<<' );
	error_log( '' );
	error_log( '' );
	
	// find the log and send it
	
	
	// return result as json
	suwp_return_json( $result );
	
}

// 5.17
// checks the current version of wordpress and displays a message in the plugin page if the version is untested
function suwp_check_wp_version() {
	
	global $pagenow;
	
	
	if ( $pagenow == 'plugins.php' && is_plugin_active('stockunlocks/stockunlocks.php') ):
	
		// get the wp version
		$wp_version = get_bloginfo('version');
		
		// tested vesions
		// these are the versions we've tested our plugin in
		$tested_versions = array(
			'4.2.0',
            '4.2.1',
            '4.2.2',
            '4.2.3',
            '4.2.4',
            '4.2.5',
            '4.2.6',
            '4.7',
            '4.7.1',
		);
		
		$tested_range = array(4.0,4.6);
		
		// IF the current wp version is  in our tested versions...
		if( (float)$wp_version >= (float)$tested_range[0] && (float)$wp_version <= (float)$tested_range[1] ):
		
			// we're good!
		
		else:
			
			// get notice html
			$notice = suwp_get_admin_notice('StockUnlocks has not been tested in your version of WordPress. It may still work though...','error');
			
			// echo the notice html
			// echo( $notice );
			
		endif;
	
	endif;
	
}

// 5.18
// runs functions for plugin uninstall
function suwp_uninstall_plugin() {
	
	// remove our custom plugin tables
	suwp_remove_plugin_tables();
	// remove custom post types posts and data
	suwp_remove_post_data();
	// remove plugin options
	suwp_remove_options();
	
}

// 5.19
// removes our custom database tabels
function suwp_remove_plugin_tables() {
	
	// get WP's wpdb class
	global $wpdb;
	
	// setup return variable
	$tables_removed = false;
	
	try {
		
		// get our custom table name
		$table_name = $wpdb->prefix . "suwp_reward_links";
	
		// delete table from database
		$tables_removed = $wpdb->query("DROP TABLE IF EXISTS $table_name;");
	
	} catch( Exception $e ) {
		
		
	}
	
	// return result
	return $tables_removed;
	
}

// 5.20
// removes plugin related custom post type post data
function suwp_remove_post_data() {
	
	// get WP's wpdb class
	global $wpdb;
	
	// setup return variable
	$data_removed = false;
	
	try {
		
		// get our custom table name
		$table_name = $wpdb->prefix . "posts";
		
		// set up custom post types array
		$custom_post_types = array(
            'suwp_apiprovider',
		);
		
		// remove data from the posts db table where post types are equal to our custom post types
		$data_removed = $wpdb->query(
			$wpdb->prepare( 
				"
					DELETE FROM $table_name 
					WHERE post_type = %s OR post_type = %s
				", 
				$custom_post_types[0],
				$custom_post_types[1]
			) 
		);
		
		// get the table names for postmeta and posts with the correct prefix
		$table_name_1 = $wpdb->prefix . "postmeta";
		$table_name_2 = $wpdb->prefix . "posts";
		$wpID = 'NULL';
		
		// delete orphaned meta data
		$wpdb->query(
			$wpdb->prepare( 
				"
				DELETE pm
				FROM $table_name_1 pm
				LEFT JOIN $table_name_2 wp ON wp.ID = pm.post_id
				WHERE wp.ID IS %s
				",
				$wpID
			) 
		);
		
		
		
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return result
	return $data_removed;
	
}

// 5.21
// removes any custom options from the database
// !! Not sure if this is working on delete of plugin
// Options still appear when plugin is reinstalled, why?
function suwp_remove_options() {
	
	$options_removed = false;
	
	try {
	
		// get plugin option settings
		$options = suwp_get_options_settings();
		
		// loop over all the settings
		foreach( $options['settings'] as &$setting ):
			
			// unregister the setting
			unregister_setting( $options['group'], $setting );
		
		endforeach;
		
		// return true if everything worked
		$options_removed = true;
	
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return result
	return $options_removed;
	
}


/* !6. HELPERS */

// 6.xx
// retrieves a product based on a unique provider and api service combo
function suwp_get_product_id( $apiproviderid, $apiserviceid ) {
    	
    $product_id = 0;
    
	try {
        
		// check if product already exists
		$product_query = new WP_Query( 
			array(
				'post_type'	    =>	'product',
                // if already published or imported ..., don't import again. 'trash' is also an option 
                'post_status'   => array('publish', 'imported', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
				'posts_per_page'=> 1, // only expecting one result, so set to '1', get all = '-1'
				'meta_query'    => array(
                    'relation'  => 'AND',
                    array(
                            'key' => '_suwp_api_provider',
                            'value' => $apiproviderid,
                            'compare' => '=',
                    ),
                    array(
                            'key' => '_suwp_api_service_id',
                            'value' => $apiserviceid,
                            'compare' => '=',
                    ),
				),
			)
		);
		
		// IF the product exists...
		if( $product_query->have_posts() ):
		
			// get the product_id
			$product_query->the_post();
            
			$product_id = get_the_ID();
			
		endif;
	
	} catch( Exception $e ) {
		
		// a php error occurred
        error_log('----- ERROR - OBTAINING PRODUCT BASED ON PROVIDER POST ID AND API SERVICE ID COMBO ----- ');
		error_log(print_r($e,true));
	}
		
	// reset the Wordpress post object, avoids bleeding memory
	wp_reset_query();
	
    // will return the id if found in db, otherwise returns '0'
	return (int)$product_id;
	
}

// 6.4
function suwp_return_json( $php_array ) {
	
	// encode result as json string
	$json_result = json_encode( $php_array );
	
	// return result
	die( $json_result ); // whatever process php was in - stop doing that
	
	// stop all other processing 
	exit;
	
}

//6.5
// gets the unique act field key from the field name
function suwp_get_acf_key( $field_name ) {
	
	$field_key = $field_name;
	
	switch( $field_name ) {
		
		case 'suwp_activeflag':
			$field_key = 'field_5854d17bd5e5d';
			break;
		case 'suwp_sitename':
			$field_key = 'field_58557dfcd5e5e';
			break;
		case 'suwp_url':
			$field_key = 'field_58557fa7d5e5f';
			break;
		case 'suwp_username':
			$field_key = 'field_58558042d5e60';
			break;
		case 'suwp_apikey':
			$field_key = 'field_585580a8d5e61';
			break;
		case 'suwp_apinotes':
			$field_key = 'field_58558102d5e62';
			break;
		
	}
	
	return $field_key;
	
}

// 6.6
// returns an array of service data
function suwp_get_service_data( $service_id ) {
	
	// setup service_data
	$service_data = array();
	
	// get service object
	$service = get_post( $service_id );
	
	// IF service object is valid (checking if this is the correct post type)
	if( isset($service->post_type) && $service->post_type == 'product' ):
    
        $title = $service->post_title;
        $api_service_id = get_post_meta( $service_id, '_suwp_api_service_id', true );
        $price = get_post_meta( $service_id, '_regular_price', true );
        $provider_ID = get_post_meta( $service_id, '_suwp_api_provider', true );
        $provider_name = get_post_meta( $provider_ID, 'suwp_sitename', true );
        $provider_title = get_post_field( 'post_title', $provider_ID );
        $process_time = get_post_meta( $service_id, '_suwp_process_time', true );
        $service_credit = get_post_meta( $service_id, '_suwp_service_credit', true );
        $online_status = get_post_meta( $service_id, '_suwp_online_status', true );
    
		// build service_data for return
		$service_data = array(
			'title'=> $title,
			'api_service_id'=>$api_service_id,
			'price'=>$price,
			'process_time'=>$process_time,
			'service_credit'=>$service_credit,
			'online_status'=>$online_status,
			'provider_name'=>$provider_name,
			'provider_title'=>$provider_title,
			'provider_ID'=>$provider_ID
		);
		
	
	endif;
	
	// return service_data
	return $service_data;
	
}

// 6.xx
// returns html for a page selector: Manage Cron Schedule
function suwp_get_cron_select( $input_name="suwp_page", $input_id="", $selected_value="" ) {

    // get cron settings
    $pages = array(
                '5min'  => '5 minutes',
                '15min' => '15 minutes',
                '30min' => '30 minutes',
                '1hr'   => '1 hour',
                '3hrs'  => '3 hours',
    );
    
	// setup our select html
	$select = '<select name="'. $input_name .'" ';
	
	// IF $input_id was passed in
	if( strlen($input_id) ):
	
		// add an input id to our select html
		$select .= 'id="'. $input_id .'" ';
	
	endif;
	
	// setup our first select option
	$select .= '><option value="">- Cron Disabled -</option>';
	
    // loop over all the pages
    foreach( $pages as $key => $value ):
	
		// check if this option is the currently selected option
		$selected = '';
		if( $selected_value == $key ):
			$selected = ' selected="selected" ';
		endif;
	
		// build our option html
		$option = '<option value="' . $key . '" '. $selected .'>';
		$option .= $value;
		$option .= '</option>';
		
		// append our option to the select html
		$select .= $option;
		
	endforeach;
    
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
	
}

// 6.xx
// returns html for a page selector: Troubleshooting Option
function suwp_get_troubleshoot_select( $input_name="suwp_page", $input_id="", $selected_value="" ) {

    // get cron settings
    $pages = array(
                '1'  => '1 item',
                '5' => '5 items',
                '10' => '10 items',
                '25' => '25 items',
                '50' => '50 items',
                '75' => '75 items',
                '100' => '100 items',
                '150' => '150 items',
                '200' => '200 items',
                '250' => '250 items',
    );
    
	// setup our select html
	$select = '<select name="'. $input_name .'" ';
	
	// IF $input_id was passed in
	if( strlen($input_id) ):
	
		// add an input id to our select html
		$select .= 'id="'. $input_id .'" ';
	
	endif;
	
	// setup our first select option
	$select .= '><option value="">- Disabled -</option>';
	
    // loop over all the pages
    foreach( $pages as $key => $value ):
	
		// check if this option is the currently selected option
		$selected = '';
		if( $selected_value == $key ):
			$selected = ' selected="selected" ';
		endif;
	
		// build our option html
		$option = '<option value="' . $key . '" '. $selected .'>';
		$option .= $value;
		$option .= '</option>';
		
		// append our option to the select html
		$select .= $option;
		
	endforeach;
    
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
	
}

// 6.xx
// returns html for a page selector: Price Enabled 01 value
function suwp_get_price_enabled_01( $input_name="suwp_page", $input_id="", $selected_value="" ) {

    // get cron settings
    $pages = array(
                '1'  => 'Enabled',
    );
    
	// setup our select html
	$select = '<select name="'. $input_name .'" ';
	
	// IF $input_id was passed in
	if( strlen($input_id) ):
	
		// add an input id to our select html
		$select .= 'id="'. $input_id .'" ';
	
	endif;
	
	// setup our first select option
	$select .= '><option value="">- Disabled -</option>';
	
    // loop over all the pages
    foreach( $pages as $key => $value ):
	
		// check if this option is the currently selected option
		$selected = '';
		if( $selected_value == $key ):
			$selected = ' selected="selected" ';
		endif;
	
		// build our option html
		$option = '<option value="' . $key . '" '. $selected .'>';
		$option .= $value;
		$option .= '</option>';
		
		// append our option to the select html
		$select .= $option;
		
	endforeach;
    
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
	
}

// 6.7
// returns html for a page selector
function suwp_get_page_select( $input_name="suwp_page", $input_id="", $parent=-1, $value_field="id", $selected_value="" ) {
	
	// get WP pages
	$pages = get_pages( 
		array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'post_type' => 'page',
			'parent' => $parent,
			'status'=>array('draft','publish'),	
		)
	);
	
	// setup our select html
	$select = '<select name="'. $input_name .'" ';
	
	// IF $input_id was passed in
	if( strlen($input_id) ):
	
		// add an input id to our select html
		$select .= 'id="'. $input_id .'" ';
	
	endif;
	
	// setup our first select option
	$select .= '><option value="">- Select One -</option>';
	
	// loop over all the pages
	foreach ( $pages as &$page ): 
	
		// get the page id as our default option value
		$value = $page->ID;
		
		// determine which page attribute is the desired value field
		switch( $value_field ) {
			case 'slug':
				$value = $page->post_name;
				break;
			case 'url':
				$value = get_page_link( $page->ID );
				break;
			default:
				$value = $page->ID;
		}
		
		// check if this option is the currently selected option
		$selected = '';
		if( $selected_value == $value ):
			$selected = ' selected="selected" ';
		endif;
	
		// build our option html
		$option = '<option value="' . $value . '" '. $selected .'>';
		$option .= $page->post_title;
		$option .= '</option>';
		
		// append our option to the select html
		$select .= $option;
		
	endforeach;
	
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
	
}

// 6.8
// returns default option values as an associative array
function suwp_get_default_options() {
	
	$defaults = array();
	
	try {
		
        // get cron run id
        $cron_run_id = '';
        // get cron troubleshoot id
		$cron_troubleshoot_id = '';
        // get price adjustment vals
        $price_enabled_01 = '';
		$price_adj_default = 1;
        $price_adj_01 = 1;
        $price_range_01 = 0;
        $price_range_02 = 0;
        $price_adj_02 = 1;
        $price_range_03 = 0;
        $price_range_04 = 0;
		// get front page id
		$front_page_id = get_option('page_on_front');
        
        $blog_title = get_bloginfo('name');
        if( empty($blog_title) ) {
            $blog_title = 'YourWebsiteName';
        }
        $admin_email = get_bloginfo('admin_email');
        if( empty($admin_email) ) {
            $admin_email = 'support@yourdomainhere.com';
        }
        $website_url = get_bloginfo('wpurl');
        if( empty($website_url) ) {
            $website_url = 'www.yourwebsitehere.com';
        }
    
        // setup order success email values
        $suwp_subject_ordersuccess = 'Order #{$orderid} Submitted, IMEI : {$imei}';
        $suwp_message_ordersuccess = 'Dear {$customerfirstname}:' .
            chr(10) .
            chr(10) . 'This message is to inform you that the following has been submitted:' .
            chr(10) .
            chr(10) . 'Order ID: {$orderid}' .
            chr(10) . 'IMEI: {$imei}' .
            chr(10) . 'Service: {$service}' .
            chr(10) . '{$phoneinfo}' .
            chr(10) .
            chr(10) . 'Browse to the following page to login to your account and view your order details:' .
            chr(10) . $website_url . '/my-account (Please check your account FIRST before contacting us about the status of your order)' .
            chr(10) .
            chr(10) . 'Once the code is available, we will email the code with instructions to you.' .
            chr(10) . 'If you have any questions or comments, you may contact us by replying to this email.' .
            chr(10) .
            chr(10) . 'NOTE: if you do not receive a notification from us within the time period mentioned above, please sign into your account to see if your code was delivered. If we are not in your address book, our notifications may get blocked by spam filters.' .
            chr(10) .
            chr(10) . 'Once again, thank you for your order.' .
            chr(10) .
            chr(10) . 'Regards, ' .
            chr(10) . $blog_title .
            chr(10) . $website_url .
            chr(10) ;
        $suwp_fromname_ordersuccess = $blog_title;
        $suwp_fromemail_ordersuccess = $admin_email;
        $suwp_copyto_ordersuccess = $admin_email;
        
        // setup order available email values
        $suwp_subject_orderavailable = 'Order #{$orderid} completed - ' . $blog_title . ', IMEI : {$imei}';
        $suwp_message_orderavailable = 'Dear {$customerfirstname}:' .
            chr(10) .
            chr(10) . 'The code for your mobile phone IMEI : {$imei} has been successfully calculated.' .
            chr(10) .
            chr(10) . 'Service: {$service}' .
            chr(10) . 'IMEI: {$imei}' .
            chr(10) . 'CODE: {$reply}' .
            chr(10) . 'Order ID: {$orderid}' .
            chr(10) . '{$phoneinfo}' .
            chr(10) .
            chr(10) . 'Instructions:' .
            chr(10) . $website_url . '/how-to-unlock/unlock-instructions' .
            chr(10) .
            chr(10) . 'Browse to the following page to login to your account and view your order details:' .
            chr(10) . $website_url . '/my-account' .
            chr(10) .
            chr(10) . 'Thanks again, ' .
            chr(10) . $blog_title .
            chr(10) . $website_url .
            chr(10) ;
        $suwp_fromname_orderavailable = $blog_title;
        $suwp_fromemail_orderavailable = $admin_email;
        $suwp_copyto_orderavailable = $admin_email;        
        
        // setup order reply error/rejected email values
        $suwp_subject_orderrejected = 'Code Unsuccessful, IMEI : {$imei}, Order #{$orderid}';
        $suwp_message_orderrejected = 'Dear {$customerfirstname}:' .
            chr(10) .
            chr(10) . 'Your Order #{$orderid} was Not Found On Database/Not Available.' .
            chr(10) .
            chr(10) . 'Service: {$service}' .
            chr(10) . 'IMEI: {$imei}' .
            chr(10) . 'Reason: {$reason}' .
            chr(10) . 'Order ID: {$orderid}' .
            chr(10) . '{$phoneinfo}' .
            chr(10) .
            chr(10) . 'Reasons why not found:' .
            chr(10) . '1) Invalid IMEI.' .
            chr(10) . '2) IMEI is requested through wrong Network.' .
            chr(10) . '    Example: T-Mobile IMEI is requested through AT&T Service.' .
            chr(10) . '    Note: Networks only database unlock codes for their phones.' .
            chr(10) . '3) IMEI is requested prior to scheduled release date by Network.' .
            chr(10) . '    Note: Networks will release unlock codes but not prior to certain date or age of device.' .
            chr(10) . '4) IMEI is lost/stolen, fraud, past due balance and will not be released by Network.' .
            chr(10) . '5) IMEI is requested through wrong Factory service.' .
            chr(10) . '    Example: HTC G2 is requested through LG G2 service.' .
            chr(10) .
            chr(10) . 'Certain orders are non-refundable. However, if this order was refundable,' .
            chr(10) . 'the refund will process within 72 hours. [ONE IMEI REFUND]' .
            chr(10) .
            chr(10) . 'Browse to the following page to login to your account and view your order details:' .
            chr(10) . $website_url . '/my-account' .
            chr(10) .
            chr(10) . 'Thank you, ' .
            chr(10) . 'Admin' .
            chr(10) ;
        $suwp_fromname_orderrejected = $blog_title;
        $suwp_fromemail_orderrejected = $admin_email;
        $suwp_copyto_orderrejected = $admin_email;    
        
        // setup placed order error email values (error at the time of submission)
        $suwp_subject_ordererror = 'ERROR: Order Processing Error/Failure - IMEI : {$imei}, Order #{$orderid}';
        $suwp_message_ordererror = 'Customer\'s email: {$customeremail}' .
			chr(10) .
            chr(10) . 'Dear ' . $blog_title . ' Admin:' .
            chr(10) .
            chr(10) . 'This message is to inform you that there has been an Order Processing Error/Failure:' .
            chr(10) .
            chr(10) . 'Order ID: {$orderid}' .
            chr(10) . 'IMEI: {$imei}' .
            chr(10) . 'Service: {$service}' .
            chr(10) . '{$phoneinfo}' .
            chr(10) .
            chr(10) . 'API Provider: {$apiprovider}' .
            chr(10) .
            chr(10) . 'API Error Message: {$apierrormsg}' .
            chr(10) . 'API Error Description: {$apierrordesc}' .
            chr(10) .
            chr(10) . 'NOTE: The Customer has not been notified about this error.' .
            chr(10) . 'Please take the appropriate action regarding this situation.' .
            chr(10) .
            chr(10) . 'Once resolved, it may be necessary to reset the above order\'s status to "Processing" as it has not been submitted to your Provider.' .
            chr(10) .
            chr(10) . $blog_title .
            chr(10) . $website_url .
            chr(10) ;
        $suwp_fromname_ordererror = $blog_title;
        $suwp_fromemail_ordererror = $admin_email;
        $suwp_copyto_ordererror = $admin_email;  
        
        // setup check order error email values (error checking status of existing order)
        $suwp_subject_checkerror = 'ERROR: Checking Order #{$orderid} - IMEI: {$imei}';
        $suwp_message_checkerror = 'This message is to inform you that there has been an Order Checking Processing Error.' .
            chr(10) .
            chr(10) . 'Details: The server is attempting to check the status of an existing order, but has failed:' .
            chr(10) .
            chr(10) . 'Order ID: {$orderid}' .
            chr(10) . 'IMEI: {$imei}' .
            chr(10) . 'Service: {$service}' .
            chr(10) . '{$phoneinfo}' .
            chr(10) .
            chr(10) . 'API Provider: {$apiprovider}' .
            chr(10) .
            chr(10) . 'API Error Description: {$apierrordesc}' .
            chr(10) . 'API Error Message: {$apierrormsg}' .
            chr(10) . 'API Error Results: {$apiresults}' .
            chr(10) .
            chr(10) . 'NOTE: The Customer has not been notified about this error.' .
            chr(10) . 'Please take the appropriate action regarding this situation.' .
            chr(10) .
            chr(10) . $blog_title .
            chr(10) . $website_url .
            chr(10) ;
        $suwp_fromname_checkerror = $blog_title;
        $suwp_fromemail_checkerror = $admin_email;
        $suwp_copyto_checkerror = $admin_email;  
        
		// setup default email footer
		$default_email_footer = '
			<p>
				Sincerely, <br /><br />
				The '. get_bloginfo('name') .' Team<br />
				<a href="'. get_bloginfo('url') .'">'. get_bloginfo('url') .'</a>
			</p>
		';
    
		// setup defaults array
		$defaults = array(
            'suwp_manage_cron_run_id'=>$cron_run_id,
			'suwp_price_enabled_01'=>$price_enabled_01,
			'suwp_price_adj_default'=>$price_adj_default,
			'suwp_price_adj_01'=>$price_adj_01,
			'suwp_price_range_01'=>$price_range_01,
			'suwp_price_range_02'=>$price_range_02,
			'suwp_price_adj_02'=>$price_adj_02,
			'suwp_price_range_03'=>$price_range_03,
			'suwp_price_range_04'=>$price_range_04,
            'suwp_manage_troubleshoot_run_id'=>$cron_troubleshoot_id,
            'suwp_subject_ordersuccess'=>$suwp_subject_ordersuccess,
            'suwp_message_ordersuccess'=>$suwp_message_ordersuccess,
            'suwp_fromname_ordersuccess'=>$suwp_fromname_ordersuccess,
            'suwp_fromemail_ordersuccess'=>$suwp_fromemail_ordersuccess,
            'suwp_copyto_ordersuccess'=>$suwp_copyto_ordersuccess,
            'suwp_subject_orderavailable'=>$suwp_subject_orderavailable,
            'suwp_message_orderavailable'=>$suwp_message_orderavailable,
            'suwp_fromname_orderavailable'=>$suwp_fromname_orderavailable,
            'suwp_fromemail_orderavailable'=>$suwp_fromemail_orderavailable,
            'suwp_copyto_orderavailable'=>$suwp_copyto_orderavailable,
            'suwp_subject_orderrejected'=>$suwp_subject_orderrejected,
            'suwp_message_orderrejected'=>$suwp_message_orderrejected,
            'suwp_fromname_orderrejected'=>$suwp_fromname_orderrejected,
            'suwp_fromemail_orderrejected'=>$suwp_fromemail_orderrejected,
            'suwp_copyto_orderrejected'=>$suwp_copyto_orderrejected,
            'suwp_subject_ordererror'=>$suwp_subject_ordererror,
            'suwp_message_ordererror'=>$suwp_message_ordererror,
            'suwp_fromname_ordererror'=>$suwp_fromname_ordererror,
            'suwp_fromemail_ordererror'=>$suwp_fromemail_ordererror,
            'suwp_copyto_ordererror'=>$suwp_copyto_ordererror,
            'suwp_subject_checkerror'=>$suwp_subject_checkerror,
            'suwp_message_checkerror'=>$suwp_message_checkerror,
            'suwp_fromname_checkerror'=>$suwp_fromname_checkerror,
            'suwp_fromemail_checkerror'=>$suwp_fromemail_checkerror,
            'suwp_copyto_checkerror'=>$suwp_copyto_checkerror,
            
		);
	
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return defaults
	return $defaults;
	
	
}

// 6.9
// returns the requested page option value or it's default
function suwp_get_option( $option_name ) {
	 
	// setup return variable
	$option_value = '';	
	
	
	try {
		
		// get default option values
		$defaults = suwp_get_default_options();
		
		// get the requested option
		switch( $option_name ) {
			
			case 'suwp_manage_cron_run_id':
				// cron run id
				$option_value = (get_option('suwp_manage_cron_run_id')) ? get_option('suwp_manage_cron_run_id') : $defaults['suwp_manage_cron_run_id'];
				break;
			
			case 'suwp_manage_troubleshoot_run_id':
				// cron troubleshoot id
				$option_value = (get_option('suwp_manage_troubleshoot_run_id')) ? get_option('suwp_manage_troubleshoot_run_id') : $defaults['suwp_manage_troubleshoot_run_id'];
				break;
			
			case 'suwp_price_enabled_01':
				// price adjustment 01 enabled
				$option_value = (get_option('suwp_price_enabled_01')) ? get_option('suwp_price_enabled_01') : $defaults['suwp_price_enabled_01'];
				break;
			
			case 'suwp_price_adj_default':
				// price adjustment default value
				$option_value = (get_option('suwp_price_adj_default')) ? get_option('suwp_price_adj_default') : $defaults['suwp_price_adj_default'];
				break;
			
			case 'suwp_price_adj_01':
				// price adjustment 01 value
				$option_value = (get_option('suwp_price_adj_01')) ? get_option('suwp_price_adj_01') : $defaults['suwp_price_adj_01'];
				break;
			
			case 'suwp_price_range_01':
				// price adjustment 01 range
				$option_value = (get_option('suwp_price_range_01')) ? get_option('suwp_price_range_01') : $defaults['suwp_price_range_01'];
				break;
			
			case 'suwp_price_range_02':
				// price adjustment 02 range
				$option_value = (get_option('suwp_price_range_02')) ? get_option('suwp_price_range_02') : $defaults['suwp_price_range_02'];
				break;
			
			case 'suwp_price_adj_02':
				// price adjustment 02 value
				$option_value = (get_option('suwp_price_adj_02')) ? get_option('suwp_price_adj_02') : $defaults['suwp_price_adj_02'];
				break;
			
			case 'suwp_price_range_03':
				// price adjustment 03 range
				$option_value = (get_option('suwp_price_range_03')) ? get_option('suwp_price_range_03') : $defaults['suwp_price_range_03'];
				break;
			
			case 'suwp_price_range_04':
				// price adjustment 04 range
				$option_value = (get_option('suwp_price_range_04')) ? get_option('suwp_price_range_04') : $defaults['suwp_price_range_04'];
				break;
			
			case 'suwp_subject_ordersuccess':
				// order success subject
				$option_value = (get_option('suwp_subject_ordersuccess')) ? get_option('suwp_subject_ordersuccess') : $defaults['suwp_subject_ordersuccess'];
				break;
			case 'suwp_message_ordersuccess':
				// order success message
				$option_value = (get_option('suwp_message_ordersuccess')) ? get_option('suwp_message_ordersuccess') : $defaults['suwp_message_ordersuccess'];
				break;
			case 'suwp_fromname_ordersuccess':
				// order success from name (when sending email)
				$option_value = (get_option('suwp_fromname_ordersuccess')) ? get_option('suwp_fromname_ordersuccess') : $defaults['suwp_fromname_ordersuccess'];
				break;
			case 'suwp_fromemail_ordersuccess':
				// order success from email (message originator)
				$option_value = (get_option('suwp_fromemail_ordersuccess')) ? get_option('suwp_fromemail_ordersuccess') : $defaults['suwp_fromemail_ordersuccess'];
				break;
			case 'suwp_copyto_ordersuccess':
				// order success copy to (cc destination)
				$option_value = (get_option('suwp_copyto_ordersuccess')) ? get_option('suwp_copyto_ordersuccess') : $defaults['suwp_copyto_ordersuccess'];
				break;
			case 'suwp_subject_orderavailable':
				// order available subject
				$option_value = (get_option('suwp_subject_orderavailable')) ? get_option('suwp_subject_orderavailable') : $defaults['suwp_subject_orderavailable'];
				break;
			case 'suwp_message_orderavailable':
				// order available message
				$option_value = (get_option('suwp_message_orderavailable')) ? get_option('suwp_message_orderavailable') : $defaults['suwp_message_orderavailable'];
				break;
			case 'suwp_fromname_orderavailable':
				// order available from name (when sending email)
				$option_value = (get_option('suwp_fromname_orderavailable')) ? get_option('suwp_fromname_orderavailable') : $defaults['suwp_fromname_orderavailable'];
				break;
			case 'suwp_fromemail_orderavailable':
				// order available from email (message originator)
				$option_value = (get_option('suwp_fromemail_orderavailable')) ? get_option('suwp_fromemail_orderavailable') : $defaults['suwp_fromemail_orderavailable'];
				break;
			case 'suwp_copyto_orderavailable':
				// order available copy to (cc destination)
				$option_value = (get_option('suwp_copyto_orderavailable')) ? get_option('suwp_copyto_orderavailable') : $defaults['suwp_copyto_orderavailable'];
				break;
			case 'suwp_subject_orderrejected':
				// order reply error subject
				$option_value = (get_option('suwp_subject_orderrejected')) ? get_option('suwp_subject_orderrejected') : $defaults['suwp_subject_orderrejected'];
				break;
			case 'suwp_message_orderrejected':
				// order reply error message
				$option_value = (get_option('suwp_message_orderrejected')) ? get_option('suwp_message_orderrejected') : $defaults['suwp_message_orderrejected'];
				break;
			case 'suwp_fromname_orderrejected':
				// order reply error from name (when sending email)
				$option_value = (get_option('suwp_fromname_orderrejected')) ? get_option('suwp_fromname_orderrejected') : $defaults['suwp_fromname_orderrejected'];
				break;
			case 'suwp_fromemail_orderrejected':
				// order reply error from email (message originator)
				$option_value = (get_option('suwp_fromemail_orderrejected')) ? get_option('suwp_fromemail_orderrejected') : $defaults['suwp_fromemail_orderrejected'];
				break;
			case 'suwp_copyto_orderrejected':
				// order reply error copy to (cc destination)
				$option_value = (get_option('suwp_copyto_orderrejected')) ? get_option('suwp_copyto_orderrejected') : $defaults['suwp_copyto_orderrejected'];
				break;
			case 'suwp_subject_ordererror':
				// placing order error subject
				$option_value = (get_option('suwp_subject_ordererror')) ? get_option('suwp_subject_ordererror') : $defaults['suwp_subject_ordererror'];
				break;
			case 'suwp_message_ordererror':
				// placing order error message
				$option_value = (get_option('suwp_message_ordererror')) ? get_option('suwp_message_ordererror') : $defaults['suwp_message_ordererror'];
				break;
			case 'suwp_fromname_ordererror':
				// placing order error from name (when sending email)
				$option_value = (get_option('suwp_fromname_ordererror')) ? get_option('suwp_fromname_ordererror') : $defaults['suwp_fromname_ordererror'];
				break;
			case 'suwp_fromemail_ordererror':
				// placing order error from email (message originator)
				$option_value = (get_option('suwp_fromemail_ordererror')) ? get_option('suwp_fromemail_ordererror') : $defaults['suwp_fromemail_ordererror'];
				break;
			case 'suwp_copyto_ordererror':
				// placing order error copy to (cc destination)
				$option_value = (get_option('suwp_copyto_ordererror')) ? get_option('suwp_copyto_ordererror') : $defaults['suwp_copyto_ordererror'];
				break;
			case 'suwp_subject_checkerror':
				// checking order error subject
				$option_value = (get_option('suwp_subject_checkerror')) ? get_option('suwp_subject_checkerror') : $defaults['suwp_subject_checkerror'];
				break;
			case 'suwp_message_checkerror':
				// checking order error message
				$option_value = (get_option('suwp_message_checkerror')) ? get_option('suwp_message_checkerror') : $defaults['suwp_message_checkerror'];
				break;
			case 'suwp_fromname_checkerror':
				// checking order error from name (when sending email)
				$option_value = (get_option('suwp_fromname_checkerror')) ? get_option('suwp_fromname_checkerror') : $defaults['suwp_fromname_checkerror'];
				break;
			case 'suwp_fromemail_checkerror':
				// checking order error from email (message originator)
				$option_value = (get_option('suwp_fromemail_checkerror')) ? get_option('suwp_fromemail_checkerror') : $defaults['suwp_fromemail_checkerror'];
				break;
			case 'suwp_copyto_checkerror':
				// checking order error copy to (cc destination)
				$option_value = (get_option('suwp_copyto_checkerror')) ? get_option('suwp_copyto_checkerror') : $defaults['suwp_copyto_checkerror'];
				break;
			
		}
		
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return option value or it's default
	return $option_value;
	
}

// 6.10
// get's the current options and returns values in associative array
function suwp_get_current_options() {
	
	// setup our return variable
	$current_options = array();
	
	try {
	
		// build our current options associative array
		$current_options = array(
			'suwp_manage_cron_run_id' => suwp_get_option('suwp_manage_cron_run_id'),
			'suwp_manage_troubleshoot_run_id' => suwp_get_option('suwp_manage_troubleshoot_run_id'),
			'suwp_price_enabled_01' => suwp_get_option('suwp_price_enabled_01'),
			'suwp_price_adj_default' => suwp_get_option('suwp_price_adj_default'),
			'suwp_price_adj_01' => suwp_get_option('suwp_price_adj_01'),
			'suwp_price_range_01' => suwp_get_option('suwp_price_range_01'),
			'suwp_price_range_02' => suwp_get_option('suwp_price_range_02'),
			'suwp_price_adj_02' => suwp_get_option('suwp_price_adj_02'),
			'suwp_price_range_03' => suwp_get_option('suwp_price_range_03'),
			'suwp_price_range_04' => suwp_get_option('suwp_price_range_04'),
			'suwp_subject_ordersuccess' => suwp_get_option('suwp_subject_ordersuccess'),
			'suwp_message_ordersuccess' => suwp_get_option('suwp_message_ordersuccess'),
			'suwp_fromname_ordersuccess' => suwp_get_option('suwp_fromname_ordersuccess'),
			'suwp_fromemail_ordersuccess' => suwp_get_option('suwp_fromemail_ordersuccess'),
			'suwp_copyto_ordersuccess' => suwp_get_option('suwp_copyto_ordersuccess'),
			'suwp_subject_orderavailable' => suwp_get_option('suwp_subject_orderavailable'),
			'suwp_message_orderavailable' => suwp_get_option('suwp_message_orderavailable'),
			'suwp_fromname_orderavailable' => suwp_get_option('suwp_fromname_orderavailable'),
			'suwp_fromemail_orderavailable' => suwp_get_option('suwp_fromemail_orderavailable'),
			'suwp_copyto_orderavailable' => suwp_get_option('suwp_copyto_orderavailable'),
			'suwp_subject_orderrejected' => suwp_get_option('suwp_subject_orderrejected'),
			'suwp_message_orderrejected' => suwp_get_option('suwp_message_orderrejected'),
			'suwp_fromname_orderrejected' => suwp_get_option('suwp_fromname_orderrejected'),
			'suwp_fromemail_orderrejected' => suwp_get_option('suwp_fromemail_orderrejected'),
			'suwp_copyto_orderrejected' => suwp_get_option('suwp_copyto_orderrejected'),
			'suwp_subject_ordererror' => suwp_get_option('suwp_subject_ordererror'),
			'suwp_message_ordererror' => suwp_get_option('suwp_message_ordererror'),
			'suwp_fromname_ordererror' => suwp_get_option('suwp_fromname_ordererror'),
			'suwp_fromemail_ordererror' => suwp_get_option('suwp_fromemail_ordererror'),
			'suwp_copyto_ordererror' => suwp_get_option('suwp_copyto_ordererror'),
			'suwp_subject_checkerror' => suwp_get_option('suwp_subject_checkerror'),
			'suwp_message_checkerror' => suwp_get_option('suwp_message_checkerror'),
			'suwp_fromname_checkerror' => suwp_get_option('suwp_fromname_checkerror'),
			'suwp_fromemail_checkerror' => suwp_get_option('suwp_fromemail_checkerror'),
			'suwp_copyto_checkerror' => suwp_get_option('suwp_copyto_checkerror'),
		);
	
	} catch( Exception $e ) {
		
		// php error
	
	}
	
	// return current options
	return $current_options;
	
}

// 6.13
// validates whether the post object exists and that it's a validate post_type
function suwp_validate_provider( $provider_object ) {
	
	$provider_valid = false;
	
	if( isset($provider_object->post_type) && $provider_object->post_type == 'suwp_apiprovider' ):
	
		$provider_valid = true;
	
	endif;
	
	return $provider_valid;
	
}

// 6.16
// returns the appropriate character for the begining of a querystring
function suwp_get_querystring_start( $permalink ) {
	
	// setup our default return variable
	$querystring_start = '&';
	
	// IF ? is not found in the permalink
	if( strpos($permalink, '?') === false ):
		$querystring_start = '?';
	endif;
	
	return $querystring_start;
	
}

// 6.18
// returns html for messages
function suwp_get_message_html( $message, $message_type ) {
	
	$output = '';
	
	try {
		
		$message_class = 'confirmation';
		
		switch( $message_type ) {
			case 'warning': 
				$message_class = 'suwp-warning';
				break;
			case 'error': 
				$message_class = 'suwp-error';
				break;
			default:
				$message_class = 'suwp-confirmation';
				break;
		}
		
		$output .= '
			<div class="suwp-message-container">
				<div class="suwp-message '. $message_class .'">
					<p>'. $message .'</p>
				</div>
			</div>
		';
		
	} catch( Exception $e ) {
		
	}
	
	return $output;
	
}

// 6.21
// generates a unique id
function suwp_generate_reward_uid( $subscriber_id, $list_id ) {
	
	// setup our return variable
	$uid = '';
	
	// get subscriber post object
	$subscriber = get_post( $subscriber_id );
	
	// get list post object
	$list = get_post( $list_id );
	
	// IF subscriber and list are valid
	if( suwp_validate_subscriber( $subscriber ) && suwp_validate_list( $list ) ):
			
			// get list reward
			$reward = suwp_get_list_reward( $list_id );
			
			// IF reward is not equal to false
			if( $reward !== false ):
				
				// generate a unique id
				$uid = uniqid( 'suwp', true );
			
			endif;
			
	
	endif;
	
	return $uid;
	
}

// 6.22
// returns false if list has no reward or returns the object containing file and title if it does
function suwp_get_reward( $uid ) {
	
	global $wpdb;
	
	// setup return data
	$reward_data = false;
	
	// reward links download table name
	$table_name = $wpdb->prefix . "suwp_reward_links";
	
	// get list id from reward link
	$list_id = $wpdb->get_var( 
		$wpdb->prepare( 
			"
				SELECT list_id 
				FROM $table_name 
				WHERE uid = %s
			", 
			$uid
		) 
	);
	
	// get downloads from reward link
	$downloads = $wpdb->get_var( 
		$wpdb->prepare( 
			"
				SELECT downloads 
				FROM $table_name 
				WHERE uid = %s
			", 
			$uid
		) 
	);
	
	// get reward data
	$reward = suwp_get_list_reward( $list_id );
	
	// IF reward was found
	if( $reward !== false ):
	
		// set reward data
		$reward_data = $reward;
		
		// add downloads to reward data
		$reward_data['downloads']=$downloads;
		
	endif;
	
	// return $reward_data
	return $reward_data;
	
}

// 6.23
// returns an array of service_id's
function suwp_get_provider_services( $provider_id = 0 ) {
	
	// setup return variable
	$services = false;
	
	// get provider object
	$provider = get_post( $provider_id );
	
    // take a peek at the object
    // var_dump($provider);

	if( suwp_validate_provider( $provider ) ):
    
		// query all services from post this provider only
		$services_query = new WP_Query( 
			array(
				'post_type' => 'product',
				'published' => true,
				'posts_per_page' => -1,
				'orderby'=>'post_date',
				'order'=>'DESC',
				'post_status'=>'publish',
				'meta_query' => array(
					array(
						'key' => '_suwp_api_provider', 
						'value' => ':"'.$provider->ID.'"', 
						'compare' => 'LIKE'
					)
				)
			)
		);
	
	elseif( $provider_id === 0 ):
	
		// query all services from all providers
		$services_query = new WP_Query( 
			array(
				'post_type' => 'product',
				'published' => true,
				'posts_per_page' => -1,
				'orderby'=>'post_date',
				'order'=>'DESC',
				'post_status'=>'publish',
				'tax_query' => array(
					array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'suwp_service'
                    )
				)
			)
		);
	
	endif;
		
	// IF $services_query isset and query returns results
	if( isset($services_query) && $services_query->have_posts() ):
            
		// set services array
		$services = array();
		
		// loop over results
		while ($services_query->have_posts() ) : 
		
			// get the post object
			$services_query->the_post();
			
			$post_id = get_the_ID();
		
			// append result to services array
			array_push( $services, $post_id);
		
		endwhile;
	
	endif;
	
	// reset wp query/postdata
	wp_reset_query();
	wp_reset_postdata();
	
	// return result
	return $services;
}

// 6.24
// returns the amount of services for this provider
function suwp_get_provider_service_count( $provider_id = 0 ) {
	
	// setup return variable
	$count = 0;
	
	// get array of service ids
	$services = suwp_get_provider_services( $provider_id );
	
	// IF array was returned
	if( $services !== false ):
	
		// update count
		$count = count($services);
	
	endif;
	
	// return result
	return $count;
	
}

// 6.25
// returns a unique link for downloading a services csv
function suwp_get_service_export_link( $provider_id = 0 ) {
	
	$link_href = 'admin-ajax.php?action=suwp_download_services_csv&provider_id='. $provider_id;
	
	// return service link
	return esc_url($link_href);
	
}

// 6.26
// this function reads a csv file and converts the contents into a php array
function suwp_csv_to_array($filename='', $delimiter=',')
{

	// this is an important setting!
	ini_set('auto_detect_line_endings', true);
	
	// IF the file doesn't exist or the file is not readable return false
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;
      
    // setup our return data  
    $return_data = array();
    
    // IF we can open and read the file
    if (($handle = fopen($filename, "r")) !== FALSE) {
	  
	  	$row = 0;
	  
	    // while data exists loop over data
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        // count the number of items in this data
	        $num = count($data);
	        // increment our row variable
	        $row++;
	        // setup our row data array
	        $row_data = array();
	        // loop over all items and append them to our row data
	        for ($c=0; $c < $num; $c++) {
	            // if this is the first row set it up as our header
				if( $row == 1):
					$header[] = $data[$c];
				else:
					// all rows greate than 1
					// add row data item
					$return_data[$row-2][$header[$c]] = $data[$c];
				endif;
	        }
	    } 
	    
	    // close our file
	    fclose($handle);
	}
	
	// return the new data as a php array
    return $return_data;
}

// 6.27
// returns html formatted for WP admin notices
function suwp_get_admin_notice( $message, $class ) {
	
	// setup our return variable
	$output = '';
	
	try {
		
		// create output html
		$output = '
		 <div class="'. $class .'">
		    <p>'. $message .'</p>
		</div>
		';
	    
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return output
	return $output;
	
}

// 6.28
// get's an array of plugin option data (group and settings) so as to save it all in one place
function suwp_get_options_settings() {
    
	// setup our return data
	$settings = array( 
		'group'=>'suwp_plugin_options',
		'settings'=>array(
            'suwp_manage_cron_run_id',
			'suwp_manage_troubleshoot_run_id',
			'suwp_price_enabled_01',
			'suwp_price_adj_default',
			'suwp_price_adj_01',
			'suwp_price_range_01',
			'suwp_price_range_02',
			'suwp_price_adj_02',
			'suwp_price_range_03',
			'suwp_price_range_04',
            'suwp_subject_ordersuccess',
            'suwp_message_ordersuccess',
            'suwp_fromname_ordersuccess',
            'suwp_fromemail_ordersuccess',
            'suwp_copyto_ordersuccess',
            'suwp_subject_orderavailable',
            'suwp_message_orderavailable',
            'suwp_fromname_orderavailable',
            'suwp_fromemail_orderavailable',
            'suwp_copyto_orderavailable',
            'suwp_subject_orderrejected',
            'suwp_message_orderrejected',
            'suwp_fromname_orderrejected',
            'suwp_fromemail_orderrejected',
            'suwp_copyto_orderrejected',
            'suwp_subject_ordererror',
            'suwp_message_ordererror',
            'suwp_fromname_ordererror',
            'suwp_fromemail_ordererror',
            'suwp_copyto_ordererror',
            'suwp_subject_checkerror',
            'suwp_message_checkerror',
            'suwp_fromname_checkerror',
            'suwp_fromemail_checkerror',
            'suwp_copyto_checkerror',
		),
	);
	
	// return option data
	return $settings;
	
}


/* !7. CUSTOM POST TYPES */

//7.3
// providers
include_once( plugin_dir_path( __FILE__ ) . 'cpt/suwp_apiprovider.php');


/* !8. ADMIN PAGES */

// 8.1
// dashboard admin page
function suwp_dashboard_admin_page() {
	
	// reset flag to remove the "No Unlocking Products to export." notice
	$_SESSION['suwp_services_export_flag'] = '1';
	
	// create unique support id
	suwp_get_support_id();
	
	// echo '<pre>Support ID: '; echo suwp_get_support_id(); echo '</pre>';
	
	// get our provider export link
    // since not passing a link id, it will get all of our services
	$export_service_href = suwp_get_service_export_link();
    
    // setup default admin footer text
    add_filter( 'admin_footer_text', 'suwp_default_admin_text', 11 );
    
	$output = '
		<div class="wrap">
			
			<h2>StockUnlocks</h2>
			
			<p>The best solution for transforming WordPress into a remote mobile unlocking machine</p>
			<p>Create your account at <a href= "http://reseller.stockunlocks.com/singup.html" target="_blank"> reseller.stockunlocks.com</a> to test your plugin settings.</p>
			<p><a href="'. $export_service_href .'"  class="button button-primary">Export All Unlocking Product Data</a></p>
            
		</div>
	';
	
    echo $output;

    // 0 - return the account details
    // 1 - return a list of all imei services
    // 2 - return all imei orders details
    // 3 - return a single imei service details
    // 4 - place an imei order
    // 5 - place a file oder
    // 6 - return file order details
    // 7 - return a list of all file service details
    // 8 - return a list of all mep service details
    // 9 - return a list of all models
    // 10 - return a list of all providers
	
}

// 8.2
// import services from api provider(s) admin page
function suwp_importservices_admin_page() {
	
    // setup default admin footer text
    add_filter( 'admin_footer_text', 'suwp_default_admin_text', 11 );
     
	// enque special scripts required for our file import field
	wp_enqueue_media();
     
	echo('
	
	<div class="wrap" id="import_services">
			
			<h2>Import Services</h2>
						
			<form id="import_form_1">
			
				<table class="form-table">
				
					<tbody>
                    
                        <tr>
							<th scope="row"><label for="suwp_import_services">Import From Provider</label></th>
							<td>
								<select name="suwp_import_provider_list_id">');
                                        
                                        // get all api providers
                                        $lists = get_posts(
                                            array(
                                                'post_type'			=>'suwp_apiprovider',
                                                'status'			=>'publish',
                                                'posts_per_page'   	=> -1,
                                                'orderby'         	=> 'post_title',
                                                'order'            	=> 'ASC',
                                            )
                                        );
                                        
                                        // loop over each api provider
                                        foreach( $lists as &$list ):
                                            // create the select option for that list
                                            $title = get_field('suwp_sitename', $list->ID ); // $list->post_title
                                            // $title = $title . ': ' . $list->post_title;
                                            
											// create the select option for that list
											$option = '
												<option value="'. $list->ID .'">
													'. $title .'
												</option>';
											
											// echo the new option	
											echo $option;
                                            
                                        endforeach;
                                        
						echo('</select>
                                <div class="suwp-importer">
								    <input type="hidden" name="suwp_import_api_provider_id" class="api-provider-id" value="0" />
                                    <input type="button" name="import-services-btn" class="import-services-btn button-secondary" value="Retrieve">
                                </div>
                                <p class="description" id="suwp_import_services-description">Select the api provider to import services from.</p>
                            </td>
						</tr>
                        
					</tbody>
					
				</table>
				
			</form>
			
			<form id="import_form_2" method="post"
			action="/wp-admin/admin-ajax.php?action=suwp_import_services">
				
				<table class="form-table">
				
					<tbody class="suwp-dynamic-content">
						
					</tbody>
					
					<tbody class="form-table show-only-on-valid" style="display: none">
						
						<tr>
							<td>
                                <div class="suwp-selected">
                                <input type="hidden" name="suwp_selected_api_provider_id" class="api-provider-id-selected" value="0" />
                                </div>
							</td>
						</tr>
						
					</tbody>
					
				</table>
				
				<p class="submit show-only-on-valid" style="display:none"><input type="submit" name="submit" id="submit" class="button button-primary" value="Import"></p>
				
			</form>
			
	</div>
	
	');
	
}

// 8.2
// import subscribers admin page
function suwp_import_admin_page() {
	
    // setup default admin footer text
    add_filter( 'admin_footer_text', 'suwp_default_admin_text', 11 );
     
	// enque special scripts required for our file import field
	wp_enqueue_media();
	
	echo('
	
	<div class="wrap" id="import_subscribers">
			
			<h2>Import Subscribers</h2>
						
			<form id="import_form_1">
			
				<table class="form-table">
				
					<tbody>
				
						<tr>
							<th scope="row"><label for="suwp_import_file">Import CSV</label></th>
							<td>
								
								<div class="wp-uploader">
								    <input type="text" name="suwp_import_file_url" class="file-url regular-text" accept="csv">
								    <input type="hidden" name="suwp_import_file_id" class="file-id" value="0" />
								    <input type="button" name="upload-btn" class="upload-btn button-secondary" value="Upload">
								</div>
								
								<p class="description" id="suwp_import_file-description">Expects a CSV file containing a "Name" (First, Last or Full) and "Email Address".</p>
							</td>
						</tr>
						
					</tbody>
					
				</table>
				
			</form>
			
			<form id="import_form_2" method="post"
			action="/wp-admin/admin-ajax.php?action=suwp_import_subscribers">
				
				<table class="form-table">
				
					<tbody class="suwp-dynamic-content">
						
					</tbody>
					
					<tbody class="form-table show-only-on-valid" style="display: none">
						
						<tr>
							<th scope="row"><label>Import To List</label></th>
							<td>
								<select name="suwp_import_list_id">');
									
									
										// get all our email lists
										$lists = get_posts(
											array(
												'post_type'			=>'suwp_list',
												'status'			=>'publish',
												'posts_per_page'   	=> -1,
												'orderby'         	=> 'post_title',
												'order'            	=> 'ASC',
											)
										);
										
										// loop over each email list
										foreach( $lists as &$list ):
										
											// create the select option for that list
											$option = '
												<option value="'. $list->ID .'">
													'. $list->post_title .'
												</option>';
											
											// echo the new option	
											echo $option;
											
										
										endforeach;
										
								echo('</select>
								<p class="description"></p>
							</td>
						</tr>
						
					</tbody>
					
				</table>
				
				<p class="submit show-only-on-valid" style="display:none"><input type="submit" name="submit" id="submit" class="button button-primary" value="Import"></p>
				
			</form>
			
	</div>
	
	');
	
}

// 8.3
// plugin options admin page
function suwp_options_admin_page() {
	
    // setup default admin footer text
    add_filter( 'admin_footer_text', 'suwp_default_admin_text', 11 );
     
	// get the default values for our options
	$options = suwp_get_current_options();
	
	echo('<div class="wrap">
		
		<h2>StockUnlocks Options</h2>
		
		<form action="options.php" method="post">');
		
			// outputs a unique nounce for our plugin options
			settings_fields('suwp_plugin_options');
			// generates a unique hidden field with our form handling url
			@do_settings_fields('suwp_plugin_options');
			
			echo('<table class="form-table">
			
			
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_manage_cron_run_id">Manage Cron Schedule</label></th>
						<td>
							'. suwp_get_cron_select( 'suwp_manage_cron_run_id', 'suwp_manage_cron_run_id', $options['suwp_manage_cron_run_id'] ) .'
							<p class="description" id="suwp_manage_cron_run_id-description">This setting controls how often StockUnlocks will process and check on orders. <br />
								IMPORTANT: When set to \'-Cron Disabled-\', no orders will be processed and no automated messages will be sent to customers or administrators.</p>');
			
								@submit_button();
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_manage_troubleshoot_run_id">Troubleshooting Option</label></th>
						<td>
						<hr />
							'. suwp_get_troubleshoot_select( 'suwp_manage_troubleshoot_run_id', 'suwp_manage_troubleshoot_run_id', $options['suwp_manage_troubleshoot_run_id'] ) .'
							<p class="description" id="suwp_manage_troubleshoot_run_id-description">When enabled, StockUnlocks will limit the number of Services to be imported from a Provider. <br />
								<span style="color:#FF0000">IMPORTANT</span>: This is only used when trying to resolve memory issues while importing services. Set to \'-Disabled-\' to retrieve all services.</p>');
			
								@submit_button();
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
					
					<tr>
						<th scope="row"><label for="suwp_manage_troubleshoot_run_id">Price Adjustment Option</label></th>
						<td>
						<hr />
						
						<p> <strong>GLOBAL SETTING:</strong> When enabled, StockUnlocks will automatically adjust the Product Regular price based on the cron schedule. Individual Products need to be enabled.</p>
						<p><br /></p>

						'. suwp_get_price_enabled_01( 'suwp_price_enabled_01', 'suwp_price_enabled_01', $options['suwp_price_enabled_01'] ) .'
						
						<p>When source credit is <strong>more than</strong> or equal to <input type="number" name="suwp_price_range_01" style="width: 5em" min="0" step="0.01" value="'. $options['suwp_price_range_01'] .'" class="" />, multiply my price by 
						<input type="number" name="suwp_price_adj_01" style="width: 6em" min="1" step="0.01" value="'. $options['suwp_price_adj_01'] .'" class="" /> <br />
						
						When source credit is <strong>less than</strong> or equal to <input type="number" name="suwp_price_range_02" style="width: 5em" min="0" step="0.01" value="'. $options['suwp_price_range_02'] .'" class="" />, multiply my price by 
						<input type="number" name="suwp_price_adj_02" style="width: 6em" min="1" step="0.01" value="'. $options['suwp_price_adj_02'] .'" class="" />
						</p><br />
						
						<p><strong>Default</strong>: multiply my price by <input type="number" name="suwp_price_adj_default" style="width: 6em" min="1" step="0.01" value="'. $options['suwp_price_adj_default'] .'" class="" /> when the settings above do not apply.</p><br />
						
						');
								
								@submit_button();
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
				
					<tr>
						<th scope="row"><label for="suwp_subject_ordersuccess">ORDER SUBMITTED</label></th>
						<td>
						<hr />
							<strong>NOTIFICATION:</strong> This is the message the customer receives after successfully placing an order for processing by this plugin.
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="suwp_subject_ordersuccess">Order Submitted Subject</label></th>
						<td>
							<input type="text" name="suwp_subject_ordersuccess" style="width: 25em" value="'. $options['suwp_subject_ordersuccess'] .'" class="" />
							<p class="description" id="suwp_subject_ordersuccess-description">The subject line for the message.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_message_ordersuccess">Order Submitted Message</label></th>
						<td>');
						
							
							// wp_editor will act funny if it's stored in a string so we run it like this...
							wp_editor( $options['suwp_message_ordersuccess'], 'suwp_message_ordersuccess', array( 'textarea_rows'=>8 ) );
							
							
							echo('<p class="description" id="suwp_message_ordersuccess-description">This is the message the customer receives after successfully placing an order for processing by this plugin.</p>
                                <p class="description" id="suwp_message_ordersuccess-description">Available variables: {$customerfirstname} = Customer first name, {$imei} = Submitted IMEI, {$orderid} = Order number, {$phoneinfo} = Phone/Device information, {$service} = Service name, {$reply} = Admin order reply 
                                 </p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="suwp_fromname_ordersuccess">Order Submitted From Name</label></th>
						<td>
							<input type="text" name="suwp_fromname_ordersuccess" style="width: 25em" value="'. $options['suwp_fromname_ordersuccess'] .'" class="" />
							<p class="description" id="suwp_fromname_ordersuccess-description">The name associated with the \'From Email\' address. </p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_fromemail_ordersuccess">Order Submitted From Email</label></th>
						<td>
							<input type="email" name="suwp_fromemail_ordersuccess" style="width: 25em" value="'. $options['suwp_fromemail_ordersuccess'] .'" class="" />
							<p class="description" id="suwp_fromemail_ordersuccess-description">Originates from your website. Usually an admin account.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_copyto_ordersuccess">Order Submitted Copy To</label></th>
						<td>
							<input type="email" name="suwp_copyto_ordersuccess" style="width: 25em" value="'. $options['suwp_copyto_ordersuccess'] .'" class="" />
							<p class="description" id="suwp_copyto_ordersuccess-description">Send a copy to this address. Usually an admin account.</p>');
			// <tfoot><strong>ORDER SUCCESS NOTIFICATION - END</strong></tfoot>
			// outputs the WP submit button html
			// @submit_button();
            @submit_button( 'Save Changes: Order Success' );
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_subject_orderavailable">ORDER AVAILABLE</label></th>
						<td>
						 <hr />
						 <strong>NOTIFICATION:</strong> This is the message sent to the customer when the code is successful after placing an order for processing by this plugin.
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="suwp_subject_orderavailable">Order Available Subject</label></th>
						<td>
							<input type="text" name="suwp_subject_orderavailable" style="width: 25em" value="'. $options['suwp_subject_orderavailable'] .'" class="" />
							<p class="description" id="suwp_subject_orderavailable-description">The subject line for the message.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_message_orderavailable">Order Available Message</label></th>
						<td>');
						
							
							// wp_editor will act funny if it's stored in a string so we run it like this...
							wp_editor( $options['suwp_message_orderavailable'], 'suwp_message_orderavailable', array( 'textarea_rows'=>8 ) );
							
							
							echo('<p class="description" id="suwp_message_orderavailable-description">This is the message sent to the customer when the code is successful after placing an order for processing by this plugin.</p>
                                <p class="description" id="suwp_message_orderavailable-description">Available variables: {$customerfirstname} = Customer first name, {$imei} = Submitted IMEI, {$orderid} = Order number, {$phoneinfo} = Phone/Device information, {$service} = Service name, {$reply} = Admin order reply 
                                 </p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="suwp_fromname_orderavailable">Order Available From Name</label></th>
						<td>
							<input type="text" name="suwp_fromname_orderavailable" style="width: 25em" value="'. $options['suwp_fromname_orderavailable'] .'" class="" />
							<p class="description" id="suwp_fromname_orderavailable-description">The name associated with the \'From Email\' address. </p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_fromemail_orderavailable">Order Available From Email</label></th>
						<td>
							<input type="email" name="suwp_fromemail_orderavailable" style="width: 25em" value="'. $options['suwp_fromemail_orderavailable'] .'" class="" />
							<p class="description" id="suwp_fromemail_orderavailable-description">Originates from your website. Usually an admin account.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_copyto_orderavailable">Order Available Copy To</label></th>
						<td>
							<input type="email" name="suwp_copyto_orderavailable" style="width: 25em" value="'. $options['suwp_copyto_orderavailable'] .'" class="" />
							<p class="description" id="suwp_copyto_orderavailable-description">Send a copy to this address. Usually an admin account.</p>');
			// <tfoot><strong>ORDER AVAILABLE NOTIFICATION - END</strong></tfoot>
			// outputs the WP submit button html
			// @submit_button();
            @submit_button( 'Save Changes: Order Available' );
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_subject_orderrejected">ORDER REJECTED</label></th>
						<td>
						 <hr />
						 <strong>NOTIFICATION:</strong> This is the message sent to the customer when the code is unsuccessful after placing an order for processing by this plugin.
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="suwp_subject_orderrejected">Order Rejected Subject</label></th>
						<td>
							<input type="text" name="suwp_subject_orderrejected" style="width: 25em" value="'. $options['suwp_subject_orderrejected'] .'" class="" />
							<p class="description" id="suwp_subject_orderrejected-description">The subject line for the message.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_message_orderrejected">Order Rejected Message</label></th>
						<td>');
						
							
							// wp_editor will act funny if it's stored in a string so we run it like this...
							wp_editor( $options['suwp_message_orderrejected'], 'suwp_message_orderrejected', array( 'textarea_rows'=>8 ) );
							
							
							echo('<p class="description" id="suwp_message_orderrejected-description">This is the message sent to the customer when the code is unsuccessful after placing an order for processing by this plugin.</p>
                                <p class="description" id="suwp_message_orderrejected-description">Available variables: {$customerfirstname} = Customer first name, {$imei} = Submitted IMEI, {$orderid} = Order number, {$phoneinfo} = Phone/Device information, {$service} = Service name, {$reply} = Admin order reply 
                                 </p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="suwp_fromname_orderrejected">Order Rejected From Name</label></th>
						<td>
							<input type="text" name="suwp_fromname_orderrejected" style="width: 25em" value="'. $options['suwp_fromname_orderrejected'] .'" class="" />
							<p class="description" id="suwp_fromname_orderrejected-description">The name associated with the \'From Email\' address. </p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_fromemail_orderrejected">Order Rejected From Email</label></th>
						<td>
							<input type="email" name="suwp_fromemail_orderrejected" style="width: 25em" value="'. $options['suwp_fromemail_orderrejected'] .'" class="" />
							<p class="description" id="suwp_fromemail_orderrejected-description">Originates from your website. Usually an admin account.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_copyto_orderrejected">Order Rejected Copy To</label></th>
						<td>
							<input type="email" name="suwp_copyto_orderrejected" style="width: 25em" value="'. $options['suwp_copyto_orderrejected'] .'" class="" />
							<p class="description" id="suwp_copyto_orderrejected-description">Send a copy to this address. Usually an admin account.</p>');
			// <tfoot><strong>ORDER REJECTED NOTIFICATION - END</strong></tfoot>
			// outputs the WP submit button html
			// @submit_button();
            @submit_button( 'Save Changes: Order Rejected' );
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_subject_ordererror">ORDER SUBMIT ERROR</label></th>
						<td>
						 <hr />
						 <strong>NOTIFICATION:</strong> This is the message sent to the admin when the order fails to be submitted by this plugin.
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="suwp_subject_ordererror">Order Submit Error Subject</label></th>
						<td>
							<input type="text" name="suwp_subject_ordererror" style="width: 25em" value="'. $options['suwp_subject_ordererror'] .'" class="" />
							<p class="description" id="suwp_subject_ordererror-description">The subject line for the message.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_message_ordererror">Order Submit Error Message</label></th>
						<td>');
						
							
							// wp_editor will act funny if it's stored in a string so we run it like this...
							wp_editor( $options['suwp_message_ordererror'], 'suwp_message_ordererror', array( 'textarea_rows'=>8 ) );
							
							
							echo('<p class="description" id="suwp_message_ordererror-description">This is the message sent to the admin when the order fails to be submitted by this plugin.</p>
                                <p class="description" id="suwp_message_ordererror-description">Available variables: {$customerfirstname} = Customer first name, {$imei} = Submitted IMEI, {$orderid} = Order number, {$phoneinfo} = Phone/Device information, {$service} = Service name, {$reply} = Admin order reply 
                                 </p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="suwp_fromname_ordererror">Order Submit Error From Name</label></th>
						<td>
							<input type="text" name="suwp_fromname_ordererror" style="width: 25em" value="'. $options['suwp_fromname_ordererror'] .'" class="" />
							<p class="description" id="suwp_fromname_ordererror-description">The name associated with the \'From Email\' address. </p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_fromemail_ordererror">Order Submit Error From Email</label></th>
						<td>
							<input type="email" name="suwp_fromemail_ordererror" style="width: 25em" value="'. $options['suwp_fromemail_ordererror'] .'" class="" />
							<p class="description" id="suwp_fromemail_ordererror-description">Originates from your website. Usually an admin account.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_copyto_ordererror">Order Submit Error Copy To</label></th>
						<td>
							<input type="email" name="suwp_copyto_ordererror" style="width: 25em" value="'. $options['suwp_copyto_ordererror'] .'" class="" />
							<p class="description" id="suwp_copyto_ordererror-description">Send a copy to this address. Usually an admin account.</p>');
			// <tfoot><strong>ORDER SUBMIT ERROR NOTIFICATION - END</strong></tfoot>
			// outputs the WP submit button html
			// @submit_button();
            @submit_button( 'Save Changes: Order Submit Error' );
            echo('</td>
					</tr>
                    
				</tbody>
				
				<tbody>
                
					<tr>
						<th scope="row"><label for="suwp_subject_checkerror">CHECK ORDER ERROR</label></th>
						<td>
						 <hr />
						 <strong>NOTIFICATION:</strong> This is the message sent to the admin when attempting to check the status of an existing order fails.
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="suwp_subject_checkerror">Check Order Error Subject</label></th>
						<td>
							<input type="text" name="suwp_subject_checkerror" style="width: 25em" value="'. $options['suwp_subject_checkerror'] .'" class="" />
							<p class="description" id="suwp_subject_checkerror-description">The subject line for the message.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_message_checkerror">Check Order Error Message</label></th>
						<td>');
						
							
							// wp_editor will act funny if it's stored in a string so we run it like this...
							wp_editor( $options['suwp_message_checkerror'], 'suwp_message_checkerror', array( 'textarea_rows'=>8 ) );
							
							
							echo('<p class="description" id="suwp_message_checkerror-description">This is the message sent to the admin when attempting to check the status of an existing order fails.</p>
                                <p class="description" id="suwp_message_checkerror-description">Available variables: {$customerfirstname} = Customer first name, {$imei} = Submitted IMEI, {$orderid} = Order number, {$phoneinfo} = Phone/Device information, {$service} = Service name, {$reply} = Admin order reply 
                                 </p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="suwp_fromname_checkerror">Check Order Error From Name</label></th>
						<td>
							<input type="text" name="suwp_fromname_checkerror" style="width: 25em" value="'. $options['suwp_fromname_checkerror'] .'" class="" />
							<p class="description" id="suwp_fromname_checkerror-description">The name associated with the \'From Email\' address. </p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_fromemail_checkerror">Check Order Error From Email</label></th>
						<td>
							<input type="email" name="suwp_fromemail_checkerror" style="width: 25em" value="'. $options['suwp_fromemail_checkerror'] .'" class="" />
							<p class="description" id="suwp_fromemail_checkerror-description">Originates from your website. Usually an admin account.</p>
						</td>
					</tr>
                    
					<tr>
						<th scope="row"><label for="suwp_copyto_checkerror">Check Order Error Copy To</label></th>
						<td>
							<input type="email" name="suwp_copyto_checkerror" style="width: 25em" value="'. $options['suwp_copyto_checkerror'] .'" class="" />
							<p class="description" id="suwp_copyto_checkerror-description">Send a copy to this address. Usually an admin account.</p>');
			// <tfoot><strong>CHECK ORDER ERROR NOTIFICATION - END</strong></tfoot>
			// outputs the WP submit button html
			// @submit_button();
            @submit_button( 'Save Changes: Check Order Error' );
            echo('</td>
					</tr>
                    
				</tbody>
				
			</table>');
		
		echo('</form>
	
	</div>');
	
}


/* !9. SETTINGS */

// 9.1
// registers all our plugin options
function suwp_register_options() {
    
	// make sure that we can use session variables
	if (!session_id()) {
		session_start();
	}
	
	// included here in order to appear on every page
    if (in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
    add_filter('admin_footer_text', 'suwp_custom_footer_admin_text');
	
	// get plugin options settings
	
	$options = suwp_get_options_settings();
	
	// loop over settings
	foreach( $options['settings'] as $setting ):
	
		// register this setting
		register_setting($options['group'], $setting);
	
	endforeach;
	
}

// 9.x
// returns the default admin footer text
// since these 'pages' are static, have to set this on the fly
function suwp_default_admin_text($content) {
    
    return 'Create your account at <a href= "http://reseller.stockunlocks.com/singup.html" target="_blank"> reseller.stockunlocks.com</a> to test your plugin settings.';
    // return 'If you like <strong>StockUnlocks</strong> please consider leaving a ★★★★★ rating. Thanks for your support!';
}

// 9.x
// returns the default admin footer text for suwp specific custom post types
function suwp_custom_footer_admin_text($text) {
    
    $content = '';
    
    $footertext = suwp_default_admin_text($content);
    
    $post_type = filter_input(INPUT_GET, 'post_type');
    
    if (! $post_type)
        $post_type = get_post_type(filter_input(INPUT_GET, 'post'));

    switch( $post_type ) {
		
		case 'suwp_apiprovider':
			// return the custom text for apiprovider
            // return 'my custom API Providers message';
            return $footertext;
			break;
		case 'suwp_list':
			// return the custom text for email lists
            // return 'my custom Email Lists message';
            return $footertext;
			break;
		case 'suwp_subscriber':
			// return the custom text for subscribers
            // return 'my custom Subscribers message';
            return $footertext;
			break;
		
	}

    return $text;
}
