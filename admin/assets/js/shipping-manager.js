//Shipping Manager functionality.
jQuery(document).ready(function(){
	var $ = jQuery;
	
	$('#close-message').on('click', function(e){
		$('#shipping-messages').fadeOut(400);
	})
	$('#success-messages img').on('click', function(e){
		$('#success-messages').fadeOut(400);
	})
	
	$(document).on('click', '#upload_file', function(e){
		var url = $('#ajax_url').val();
		var path = $('#file_path').val();
		$('.upload-overlay').fadeIn(400);
		$.ajax({
			data: {file_path: path},
			url: url,
			dataType: 'json', 
			type: 'POST',
			success: function(response){
				$('.upload-overlay').fadeOut(400);
				if(response.result === 'success'){
					$('#success-messages').fadeIn(400);
					$('.shipping-messages-success').html(response.message);
				}
				else {
					$('#shipping-messages').fadeIn(400);
					$('.shipping-manager-errors').html(response.message);
				}
			},
			error: function(){
				$('.upload-overlay').fadeOut(400);
			}
			
		})
	})
	
})