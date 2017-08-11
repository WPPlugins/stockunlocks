// wait until the page and jQuery have loaded before running the code below
jQuery(document).ready(function($){
	
	var doc_pathname = '';
	var suwp_pathname = document.location.pathname.split('/')[1];
	
	// add the path element, if necessary
	if( suwp_pathname == 'wp-admin' ) {
		doc_pathname = '/wp-admin/admin-ajax.php';
	} else {
		doc_pathname = '/' + suwp_pathname + '/wp-admin/admin-ajax.php';
	}
	
	// setup our wp ajax URL
	var wpajax_url = document.location.protocol + '//' + document.location.host  + doc_pathname;
	
	// email capture action url
	var email_capture_url = wpajax_url + '?action=suwp_save_subscription';
	
	$('form#suwp_register_form').bind('submit',function(){
		
		// get the jquery form object
		$form = $(this);
		
		// setup our form data for our ajax post
		var form_data = $form.serialize();
		
		// submit our form data with ajax
		$.ajax({
			'method':'post',
			'url':email_capture_url,
			'data':form_data,
			'dataType':'json',
			'cache':false,
			'success': function( data, textStatus ) {
				if( data.status == 1 ) {
					// success
					// reset the form
					$form[0].reset();
					// notify the user of success
					alert(data.message);
				} else {
					// error
					// begin building our error message text
					var msg = data.message + '\r' + data.error + '\r';
					// loop over the errors
					$.each(data.errors,function(key,value){
						// append each error on a new line
						msg += '\r';
						msg += '- '+ value;
					});
					// notify the user of the error
					alert( msg );
				}
			},
			'error': function( jqXHR, textStatus, errorThrown ) {
				// ajax didn't work
			}
			
		});
		
		// stop the form from submitting normally
		return false;
		
	});
	
	// email capture action url
	var unsubscribe_url = wpajax_url + '?action=suwp_unsubscribe';
	
	$(document).on('submit','form#suwp_manage_subscriptions_form',function(){
		
		// get the jquery form object
		$form = $(this);
		
		// setup our form data for our ajax post
		var form_data = $form.serialize();
		
		// submit our form data with ajax
		$.ajax({
			'method':'post',
			'url':unsubscribe_url,
			'data':form_data,
			'dataType':'json',
			'cache':false,
			'success': function( data, textStatus ) {
				if( data.status == 1 ) {
					// success
					// update form html
					$form.replaceWith(data.html);
					// notify the user of success
					alert(data.message);
				} else {
					// error
					// begin building our error message text
					var msg = data.message + '\r' + data.error + '\r';
					alert(msg);
				}
			},
			'error': function( jqXHR, textStatus, errorThrown ) {
				// ajax didn't work
			}
			
		});
		
		// stop the form from submitting normally
		return false;
		
	});

	
});