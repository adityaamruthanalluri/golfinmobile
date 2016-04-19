//Multi language
//Popups

$(document).ready(function() {
	
	//Multi language
	var language = null;
	$.ajax({
        url: '/_php/common.php',
        type: 'post',
        data: {
        	action: 'get_lang'
        },
        success: function(response) { 
    		$.getScript('/_lang/'+response+'.js');		
       	}
	});
	 
	//Popups
	$('.lightbox').click(function() { 
		$('.register_startpage').hide();
		$('#forms_bkg').css({ opacity: 0.3, 'width':$(document).width(),'height':$(document).height()});
		$('body').css({'overflow':'hidden'});
		$(".popup").hide();
		var obj = $(this).attr('id')+'_wrapper'; 
		$("."+obj).center(); 
		$("."+obj).fadeIn(300);
	}); 
	$('body').on('click', ".popup_close", function() { 
		if ($(this).attr('class').indexOf('secondary') < 0) {
			$('#forms_bkg').css({'width':0,'height':0});
			$('body').css({'overflow':'visible'});
		}
		var src = $(this).parent().find('iframe').attr('src');
		$(this).parent().find('iframe').attr('src','');
		$(this).parent().find('iframe').attr('src',src);
		$(this).parent().parent().hide();
	});
	$('#forms_bkg').click(function() {
		$('#forms_bkg').css({'width':0,'height':0});
		$('body').css({'overflow':'visible'});
		var src = $(this).parent().find('iframe').attr('src');
		$(this).parent().find('iframe').attr('src','');
		$(this).parent().find('iframe').attr('src',src);
		$('.popup').hide();
	});
	$('#forgot_password').click(function() { 
		$('.password_email').val($('.login_email').val());
		$('.password_wrapper').center();
		$('.password_wrapper').fadeIn(300);
		$('.login_wrapper').hide();
	});
	
	//Check for duplicates on company registration number
	$('#company_registration_number').blur(function() {
		var regnr = $(this).val();
		$.ajax({ 
	        url: '/_php/companies.php',
	    	type: 'post',
	    	data: {
	    		action: 'checkRegNr',
	    		regnr: regnr,
	    	},
	    	success: function(response) { 
	       		if (response) {
	       			$('#forms_bkg').css({ opacity: 0.3, 'width':$(document).width(),'height':$(document).height()});
					$('body').css({'overflow':'hidden'});
					$('.error_message_title').html('&nbsp;');
			       	$('#error_message').html(response);
	    		   	$('.error_message_wrapper').center();
			       	$('.error_message_wrapper').show();
	       		}
	    	}
	    });
	});
	$('body').on('click', '#append_company', function() {
		var cid = $(this).attr('data-company');
		var cname = $(this).attr('data-companyname');
		var uid = $(this).attr('data-user');
		$.ajax({ 
	        url: '/_php/companies.php',
	    	type: 'post',
	    	data: {
	    		action: 'appendCompanyUser',
	    		cid: cid,
	    		cname: cname,
	    		uid: uid
	    	},
	    	success: function(response) { 
	       		if (response) {
	       			$('.error_message_title').html('&nbsp;');
			       	$('#error_message').html(response);
	       		}
	    	}
	    });
	});
	$('body').on('click', '#append_company_decline', function() {
		$('#company_registration_number').val('');
		$('#forms_bkg').css({'width':0,'height':0});
		$('body').css({'overflow':'visible'});
		$(this).parents('.error_message_wrapper').hide();
	});

//Check e-mail against users
	$('#user_email').focusout(function() {
		email = $(this).val();
		if (!validateMail(email)) {
			//alert('E-mail incorrect');
			$('#forms_bkg').css({ opacity: 0.3, 'width':$(document).width(),'height':$(document).height()});
			$('body').css({'overflow':'hidden'});
	       	$('#error_message').html(jsLang.EMAIL_IN_SYSTEM);
   		   	$('.error_message_wrapper').center();
	       	$('.error_message_wrapper').show();
			return false;
		}
		else {
			$.ajax({
        		url: '/_php/users.php',
        		type: 'post',
        		data: {
        			action: 'check_email', 
        			email: email
        		},
        		success: function(response) {
        			if (response != 0) {
        				//$('#register_form').hide();
        				$('#user_email').val('');
        				$('#forms_bkg').css({ opacity: 0.3, 'width':$(document).width(),'height':$(document).height()});
						$('body').css({'overflow':'hidden'});
						$('#error_message_content .popup_close').addClass('secondary');
	       				$('#error_message').html(response);
	       				$('.error_message_wrapper').center();
	       				$('.error_message_wrapper').show();
        			}
        		}
        	});
		}
	});
	
	$('#share_friend .submit_button').click(function() {
		var sender_name = $('#share_your_name').val();
		var sender_email = $('#share_your_email').val();
		var reciever_name = $('#share_friends_name').val();
		var reciever_email = $('#share_friends_email').val();
		var message = $('#share_message').val();
		if (sender_name != '' && sender_email != '' && reciever_name != '' && reciever_email != '') {
			$.ajax({
	        	url: '/_php/form.management.php',
	        	type: 'post',
	        	data: {
	       			action: 'tell_a_friend', 
	       			sender_name: sender_name,
	       			sender_email: sender_email,
	       			reciever_name: reciever_name,
	       			reciever_email: reciever_email,
	       			message: message
	       		},
	       		success: function(response) {
	       			if (response == 'success') {
	       				$('#share_friend').html(jsLang.SHARE_OK);
	       			}
	       			else {
	       			
	       			}
	       		}
	       	});
		}
		return false;
	});
	
	$('.user_account_type').change(function() {
		if ($(this).val() == 1) {
			$('#user_first_name').parents('.form_row').show();
			$('#usersform_title_user_last_name').html('Last name');
		}
		else {
			$('#user_first_name').parents('.form_row').hide();
			if ($(this).val() == 2) {
				$('#usersform_title_user_last_name').html('Course name');
			}
			else if ($(this).val() == 3) {
				$('#usersform_title_user_last_name').html('Shop name');
			}
		}
	});
	
	//Validate e-mail address
	function validateMail(mail) { 
		mail_dot = mail.lastIndexOf(".");
		mail_dot_ext = mail_dot+2;
		mail_at = mail.indexOf("@");
		if (mail_dot-2 > mail_at && mail_dot_ext < mail.length && mail_dot!=-1 && mail_at!=-1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	//Center popup	
	jQuery.fn.center = function () { 
		var height = ($(this).outerHeight() / 2);
		var width = ($(this).outerWidth() / 2);
		var marg = '-'+height+'px 0 0 -' + width + 'px';
		var top = '50%';
		var left = '50%';
		//alert(marg);
		this.css('margin', marg);
		//this.css('top', '50%');
		//this.css('left', '50%');
		//var top = Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +  $(window).scrollTop())
		//var left = Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +  $(window).scrollLeft())
		//var top = (window.offsetHeight / 2) - (this.offsetHeight / 2);
		//var left = (window.innerWidth / 2) - (this.offsetWidth / 2);
		this.css('position', 'fixed');
		this.css("top", top);
		this.css("left", left);
		return this;
	}
	
	

});
	
	