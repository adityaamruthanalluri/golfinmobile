//Populate location dropdowns
//Datepicker (jquery-ui)
//Fancybox - File management
//Chat
//Validate e-mail address
//Validate phone and other inputs that require numbers, space or '-'
//Center popup	

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
	
	

// Populate location dropdowns
	$('.CountrySelect').change(function() { 
		var country = $(this).val();
		var target = $(this).attr('id'); 
		var prefix = target.replace('countryid',''); 
		//Get regions
		$.ajax({
	        url: '/_php/forms.management.php',
	        type: 'post',
	        data: {
	        	action: 'populateRegions',
	        	countryid: country,
	        	prefix: prefix
	        },
	        success: function(response) { 
	    		$('#form_'+prefix+'regionid').html(response);	
	    		//Get districts;
	    		$.ajax({
       				url: '/_php/forms.management.php',
       				type: 'post',
       				data: {
       					action: 'populateDistricts',
       					countryid: country
       				},
       				success: function(response) { alert('*');
   						$('#form_course_districtid').html(response);
	   				}
				});
	       	}
		});
	});
	$('body').on('change', '.RegionSelect', function() { 
		var region = $(this).val();
		var target = $(this).attr('id'); 
		var prefix = target.replace('regionid',''); 
		$.ajax({
	        url: '/_php/forms.management.php',
	        type: 'post',
	        data: {
	        	action: 'populateCities',
	        	regionid: region,
	        	prefix: prefix
	        },
	        success: function(response) { 
	    		$('#form_'+prefix+'cityid').html(response);	
	    		
	       	}
		});
	});

//Datepicker (jquery-ui)
	$(".datepicker").datepicker({dateFormat:"yy-mm-dd"});
	
//Fancybox - File management
	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 600,
		'type'		: 'iframe',
        'autoScale' : false
    });
    
//Chat
	$('#chatbox_wrapper.closed').click(function() {
		$(this).removeClass('closed');
		$(this).addClass('open');
		$(this).load('./_chat/index.php');
	});
	
//Contact card on offers
	$('.show_contact_info').click(function() {
		var user = $(this).attr('data-userid');
		var div = 'user_contact_'+user;
		if ($('#'+div).html() != '') {
			$('#user_contact_'+user).html('');
	   		$('#user_contact_'+user).hide();
	   		return false;
		}
		$.ajax({
	        url: '/_php/users.php',
	   	    type: 'post',
	       	data: {
	   	    	action: 'getContactInfo',
	   	    	user: user
	   	    },
	   	    success: function(response) { 
	   			$('#user_contact_'+user).html(response);
	   			$('#user_contact_'+user).show();
	       	}
		});
	});
	
	/*$('#os_categories').hover(function() {
		$('#category_list').show();
	});
	$('#category_list').mouseleave(function() {
		$('#category_list').hide();
	});*/
	
	$('.category-item').click(function() {
		var category = $(this).attr('id');
		var categoryName = $(this).attr('data-translate');
		$('#os_categories').html(categoryName);
		$('#os_categories').removeClass();
		$('#os_categories').addClass(category);
	});
	
	$('#SZoomIT_button').click(function() { 
		submitSearchForm();
	});
	
	$("#os_searchtext").keydown(function (e) {
  		if (e.keyCode == 13) {
	    	submitSearchForm();
	  	}
	});
	
	function submitSearchForm() {
		var category = $('#os_categories').attr('class').trim(); 
		var term = $('#os_searchtext').text().trim();
		var form = $(document.createElement('form'));
   		$(form).attr("action", "");
    	$(form).attr("method", "POST"); 
		if (category != '') {
	    	var category = $("<input>").attr("type", "hidden").attr("name", "category").val(category);
    		$(form).append($(category));
    	}
    	if (term != '') {
	    	var term = $("<input>").attr("type", "hidden").attr("name", "term").val(term);
    		$(form).append($(term));
    	}
    	$(form).submit(); 
	}
	

//Marquee for startpage
	$('.marquee').marquee({
    	//speed in milliseconds of the marquee
    	duration: 20000,
    	//gap in pixels between the tickers
    	gap: 100,
    	//time in milliseconds before the marquee will start animating
    	delayBeforeStart: 0,
    	//'left' or 'right'
    	direction: 'left',
    	//true or false - should the marquee be duplicated to show an effect of continues flow
    	duplicated: true
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
	
//Validate phone and other inputs that require numbers, space or '-'
	function validatePhone(phone) {
		phone = phone.replace(/\s+/g, '');
		phone = phone.replace('-', '')
		return $.isNumeric(phone)
	}
	function validatePasswords(pass1, pass2) {
		if (pass1 != pass2) { 
			return 1;
		}
		if (pass1.length<6) { 
			return 2;
		}
		var numbers = new RegExp('[0-9]');
		if (!pass1.match(numbers)) { 
			return 3;
		}
		var upperCase = new RegExp('[A-Z]');
		if (!pass1.match(upperCase)) { 
			return 3;
		}
		return 0;
	}

	
	function ErrMess(msg) { 
	    alert(msg);
	}
	
	$('.doRegister').click(function() {
		$('#register_form').attr('data-target', $(this).attr('data-target'));
		$('#forms_bkg').css({ opacity: 0.3, 'width':$(document).width(),'height':$(document).height()});
		$('body').css({'overflow':'hidden'});
		$('.register_startpage').center();
		$('.register_startpage').fadeIn(300);
		return false;
	});
	
	
	
});
	
	