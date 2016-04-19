//Multi language
//Login
//Logout

$(document).ready(function() {
	
	

//Login
	$('#login_submit').click(function() { 
		var email = $('#login_email').val();
		var pass = $('#login_password').val();
		//var account = $('.account:checked').val(); 
		$.ajax({
        	url: '/_php/login.php',
        	type: 'post',
        	data: {
        		email: email, 
        		pass: pass,
        	},
        	success: function(response) { 
        		if (response==0) {
	        		location.reload();
	        	}
	        	if (response==1) { 
	        		$('#error_message').html(jsLang.NOT_LOGGED_IN_NOT_VERIFIED);
	    			$('.error_message_wrapper').center();
	    			$('.error_message_wrapper').show();
	        	}
	        	else if (response==2) { 
	        		$('#error_message').html(jsLang.NOT_LOGGED_IN_WRONG_PASSWORD_EMAIL);
	       			$('.error_message_wrapper').center();
	       			$('.error_message_wrapper').show();
	        	}
        	},
        	error: function() {
        		alert('Security');
        	}
        });
	});
	
	$("#login_form").keydown(function (e) {
  		if (e.keyCode == 13) {
	    	var email = $('#login_email').val();
			var pass = $('#login_password').val();
			var account = $('.account:checked').val(); 
			$.ajax({
				url: '/_php/login.php',
				type: 'post',
				data: {
					email: email, 
					pass: pass,
					account: account
				},
				success: function(response) { 
					if (response==0) {
						location.reload();
					}
					if (response==1) { 
						$('#error_message').html(jsLang.NOT_LOGGED_IN_NOT_VERIFIED);
						$('.error_message_wrapper').center();
						$('.error_message_wrapper').show();
					}
					else if (response==2) { 
						$('#error_message').html(jsLang.NOT_LOGGED_IN_WRONG_PASSWORD_EMAIL);
						$('.error_message_wrapper').center();
						$('.error_message_wrapper').show();
					}
				},
				error: function() {
					alert('Security');
				}
			});
	  	}
	});

//Logout
	$('#logout').click(function() {
  		$.get('/_php/killsession.php', function() {
			obj = 'logout_message';
			$("."+obj).center();
			$("."+obj).show();
			setTimeout(function (){
				$(".logout_wrapper").hide();
				window.location = '/';
		    }, 1000);
		});
	});
	
});