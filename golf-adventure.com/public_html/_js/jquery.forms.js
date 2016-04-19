//Multi language
//Check inputs in forms
//Clear inputs on focus
//Security question on delete
//Fill nested after dropdown when choice is made
//Change dropdown match owner
//Transforms article title to url
//Transforms match title to matchurl
//Autosearch for archive
//Connection forms
	//Parent autocomplete
	//Choose parent 
	//Posts autocomplete
	//Chose post
	//Delete post
	//Save connections
//Contact form
//App management


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
	
	//Check inputs in forms
	$(".submit_button").click(function() { 
		var $form = $(this).closest('form'); 
		form_name = $form.attr('name'); 
		msg = ''; 
		$("#"+form_name+" .required").each(function() { 
			if ($(this).val() == '') { 
				msg = msg + $(this).attr('data-errormsg').trim()+' '+jsLang.IS_MISSING+'<br />';
				$(this).css('border','#c00 solid 2px');
			}
			else {
				$(this).css('border','#7fb545 solid 2px');
			}
		});
		switch (form_name) {
			case 'inv_register_form':
				var content = new Array('first_name', 'last_name', 'street', 'zip', 'city', 'phone', 'email', 'password');
				if ($("#company").is(':checked')) {
					content.push('company_registration_number');
					content.push('company_name');
				}
				break;
			case 'login_form':
				var content = new Array('email');
				break;
			case 'users':
				var content = new Array('email', 'selectbox', 'phone');
				break;
			case 'password_form':
				var content = new Array('email');
				break;
			case 'article_form':
				var content = new Array('article_parent');
				break;
			case 'golf_form':
				var content = new Array();
				break;
			case 'offers':
				$.ajax({
   					url: '/_php/users.php',
    				type: 'post',
      				data: {
    					action: 'get_user_level'
       				},
   					success: function(response) { 	
   						if (response == 1) {
   							var content = new Array('offer_accepted');
   						}
		   			}
   				});
				break;
			case 'password_recovery_form':
				var content = new Array('password', 'password_confirm');
				break;
			case 'newsletter':
				var content = new Array ('email')
				break;
			case 'share':
				var content = new Array('email');
				break;
			case 'deals':
				var content = new Array('deal_limiter');
				break;
		}
		if ($.inArray('article_parent', content) !== -1) { 
			if ($('#article_parent').val() == '-') {
				msg = msg + jsLang.PICK_PARENT+'<br />';
			}
		}
		if ($.inArray('company_registration_number', content) !== -1) {
			number = $("#"+form_name+" #company_registration_number").val();
			var valid = validatePhone(number);
			if (!valid) {
				msg = msg + $("#company_registration_number").attr('data-errormsg').trim()+' '+jsLang.IS_NOT_CORRECT+'<br />';
				$("#company_registration_number").css('border','#c00 solid 2px');
			}
		}
		if ($.inArray('offer_accepted', content) !== -1) {
			if (!$('#offer_accepted').prop('checked')) {
				msg = msg + jsLang.ACCEPT_UNCHECKED+'<br />';
				$("#offer_accepted").css('border','#c00 solid 2px');
			}
		}
		if ($.inArray('deal_limiter', content) !== -1) {
			var checked = false;
			$("#"+form_name+" .deal_limiter").each(function() { 
				if ($(this).prop('checked')) {
					checked = true;
				}
			});
			if (!checked) {
				msg = msg + jsLang.DEAL_TYPE_UNCHECKED+'<br />';
				$(".deal_limiter .form_input").css('color','#c00');
			}
		}
		if ( $.inArray('email', content) !== -1 ) {
			$("#"+form_name+" .email").each(function() { 
				mail = $(this).val();
				var valid = validateMail(mail);
				if (!valid) {
					msg = msg + $(this).attr('data-errormsg').trim()+' '+jsLang.IS_NOT_CORRECT+'<br />';
					$(this).css('border','#c00 solid 2px');
				}
				else {
					$(this).css('border','#7fb545 solid 2px');
				}
			});
		}
		if ( $.inArray('selectbox', content) !== -1 ) {
			$("#"+form_name+" .selectbox").each(function() { 
				if ($(this).val() < 1) {
					msg = msg + $(this).attr('data-errormsg').trim()+' '+jsLang.IS_COMPULSORY+'<br />';
					$(this).css('border','#c00 solid 2px');
				}
				else {
					$(this).css('border','#7fb545 solid 2px');
				}
			});
		}
		if ( $.inArray('phone', content) !== -1 && $("#phone").val()!='') {
			$("#"+form_name+" .phone").each(function() { 
				phone = $(".phone").val();
				if (phone.length > 0) {
					var valid = validatePhone(phone);
					if (!valid) {
						msg = msg + jsLang.PHONE_INCORRECT+'<br />';
						$(this).css('border','#c00 solid 2px');
					}
					else {
						$(this).css('border','#7fb545 solid 2px');
					}
				}
			});
		}
		if ( $.inArray('zip', content) !== -1 ) {
			phone = $("#zip").val();
			var valid = validatePhone(phone);
			if (!valid) {
				msg = msg + $("#zip").attr('data-errormsg').trim()+' '+jsLang.IS_NOT_CORRECT+'<br />';
				$("#zip").css('border','#c00 solid 2px');
			}
		}
		if ( $.inArray('password', content) !== -1 ) {
			pass1 = $('#'+form_name+' #password').val();
			pass2 = $('#'+form_name+' #password_confirm').val(); 
			var status = validatePasswords(pass1, pass2);
			switch (status) {
				case 1:
					msg = msg + $("#password").attr('data-errormsg').trim()+' '+jsLang.IS_NOT_SAME+'<br />';
					break;
				case 2:
					msg = msg + $("#password").attr('data-errormsg').trim()+' '+jsLang.ATLEAST_6_CHARACTERS+'<br />';
					break;
				case 3:
					msg = msg + $("#password").attr('data-errormsg').trim()+'<br />'; //PASSWORD
					break;
				case 4:
					msg = msg + $("#password").attr('data-errormsg').trim()+' '+jsLang.ATLEAST_1_DIGIT_AND_1_CAPITAL_LETTER+'<br />';
					break;
			}
			if (status != '0') {
				$("#"+form_name+" #password").css('border','#c00 solid 2px');
				$("#"+form_name+" #password_confirm").css('border','#c00 solid 2px');
			}
			else {
				$("#password").css('border','#7fb545 solid 2px');
				$("#password_confirm").css('border','#7fb545 solid 2px');
			}
		} 
		if (msg.length > 0) { 
			//alert (msg); //MESSAGEHERE
			$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
			$('body').css({'overflow':'hidden'});
	       	$('#error_message').html(msg); 
	       	$('.error_message_wrapper').center(); 
	       	$('.error_message_wrapper').show(); 
			return false;
		}
		else {
			return true;
		}
	});

//Check URL inputs
	$('.url').blur(function() {
		var url = $(this).val();
		if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0) {
			$(this).val('http://'+url);
		}
	});

//Clear inputs on focus
	$('#searchform_input').focus(function() { 
		$(this).val('');
		$(this).css('color', '#000');
	});
	$('#subscriber_email').focus(function() { 
		$(this).val('');
		$(this).css('color', '#000');
	});

//Security question on delete
	$('body').on('click', '.link_delete', function() {
		if (confirm(jsLang.DELETE_OK)) {
			return true;
		}
		else {
			return false;
		}
	});

//Fill nested after dropdown when choice is made
	$('#nested_under').change(function() { 
		type = $(this).attr('name').replace('_parent',''); 
		switch (type) {
			case 'category':
				url = '/_php/categories.php';
				break;
			case 'article':
				url = '/_php/articles.php';
				break;
		}
		id = $(this).val();
		if (id != '-') {
			$.ajax({
    	    	url: url,
        		type: 'post',
	        	data: {
    	    		action: 'getChildren', 
        			id: id,
	        	},
    	    	success: function(response) { 
        			$('#subcategories_dropdown').html(response);
 	       		}
    	    });
    	}
	});
	
//Handle DateTime
	$('.date').change(function() {
		var date;
		var time = $(this).parent().find('.time').val();
		date = $(this).val(); 
		$(this).parent().find('.datetime').val(date+' '+time);
		$(this).val(date);
	});
	$('.time').change(function() {
		var time;
		var date = $(this).parent().find('.date').val(); 
		time = $(this).val(); 
		$(this).parent().find('.datetime').val(date+' '+time);
		$(this).parent().find('.date').val(date);
	});
	
// Handle Deal types
	$('.deal_limiter.multiradio').click(function() { 
		switch ($(this).val()) {
			case '1':
				$('.form_row.deal_buys').hide();
				$('.form_row.deal_start').show();
				$('.form_row.deal_end').show();
				break;
			case '2':
				$('.form_row.deal_buys').show();
				$('.form_row.deal_start').hide();
				$('.form_row.deal_end').hide();
				break;
		}
	});
	
//Change dropdown match owner
	$('.match_owner_type').click(function() {
		type = $(this).val();
		$.ajax({
        	url: '/_php/matches.php',
        	type: 'post',
        	data: {
        		action: 'getOwnerDropdown', 
        		type: type
        	},
        	success: function(response) {
        		$('#form_match_owner').html(response);
        	}
        });
	});
	
//Transforms article title to url
	$("#article_title").blur(function() {
		title = $(this).val();
		post_id = $('#post_id').val(); 
		if (typeof post_id == 'undefined') {
			post_id = 0;
		}
		parent = $('#nested_under').children(':selected').val(); 
		$.ajax({
        	url: '/_php/articles.php',
        	type: 'post',
        	data: {
        		action: 'cleanUrl', 
        		title: title,
        		post_id: post_id,
        		parent: parent
        	},
        	success: function(response) {
        		$("#article_url").val($.trim(response));
        	}
        });
	});
	
//Transforms match title to matchurl
	$('#match_title').blur(function() {
		title = $(this).val();
		post_id = $('#match_id').val(); 
		if (typeof post_id == 'undefined') {
			post_id = 0;
		}
		$.ajax({
        	url: '/_php/matches.php',
        	type: 'post',
        	data: {
        		action: 'cleanUrl', 
        		title: title,
        		post_id: post_id
        	},
        	success: function(response) {
        		$("#match_matchurl").val($.trim(response));
        	}
        });
	});

//Connection forms
	//Parent autocomplete
	$('.conn_parent_auto#article').autocomplete({ 
		source: "/_php/autocomplete.php?type=article",
		minLength: 1
	});
	//Choose parent 
	$('#connChooseParent').click(function() { 
		url = $(this).attr('data-url');
		action = $(this).attr('data-action'); 
		input = $('.conn_parent_auto').val().split(' : ');
		$('#conn_title').addClass(input[0]);
		$('#conn_title').html(input[1]);
		$.ajax({ 
			url: url,
        	type: 'post',
        	data: {
        		action: action, 
        		article: input[0]
        	},
        	success: function(response) { 
        		$('#conn_posts').html(response);
        	}
        });
	});
//AUTO COMPLETE
	//Archive
	$('.conn_auto#archive').autocomplete({ 
		source: "/_php/autocomplete.php?type=archive",
		minLength: 1
	});
	//Articles
	$('.conn_auto#article').autocomplete({ 
		source: "/_php/autocomplete.php?type=article",
		minLength: 1
	});
	//Categiries
	$('.conn_auto#category').autocomplete({ 
		source: "/_php/autocomplete.php?type=category",
		minLength: 1
	});
	//Offers
	$('.conn_auto#offer').autocomplete({ 
		source: "/_php/autocomplete.php?type=offer",
		minLength: 1
	});
	//Users
	$('.conn_auto#user').autocomplete({ 
		source: "/_php/autocomplete.php?type=user",
		minLength: 1
	});
	//Golf clubs:
	$(".conn_auto#golfclub").autocomplete({
		source: "/_php/autocomplete.php?type=golfclubs",
		minLength: 1
	});
	//Golf courses:
	$(".conn_auto#golfcourse").autocomplete({
		source: "/_php/autocomplete.php?type=golfcourses",
		minLength: 1
	});
	//Golf course admins:
	$(".conn_auto#golfcourse_admin").autocomplete({
		source: "/_php/autocomplete.php?type=golfcourse_admins",
		minLength: 1
	});
	//Restaurants:
	$(".conn_auto#restaurants").autocomplete({
		source: "/_php/autocomplete.php?type=restaurants",
		minLength: 1
	});
	//Beds:
	$(".conn_auto#bed").autocomplete({
		source: "/_php/autocomplete.php?type=bed",
		minLength: 1
	});
	//Deals
	$(".conn_auto#deal").autocomplete({
		source: "/_php/autocomplete.php?type=deal",
		minLength: 1
	});
	//Deal suppliers
	$(".conn_auto#deal_suppliers").autocomplete({
		source: "/_php/autocomplete.php?type=deal_suppliers",
		minLength: 1
	});
	//Events
	$(".conn_auto#event").autocomplete({
		source: "/_php/autocomplete.php?type=event",
		minLength: 1
	});
	//App Articles
	//Events
	$(".conn_auto#app_article").autocomplete({
		source: "/_php/autocomplete.php?type=app_article",
		minLength: 1
	});
	
//AUTOCOMPLETE ACTIONS
	//Offers:
	$('#offer_edit').click(function() {
		var val = $('.conn_auto#offer').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/offers/update/?id='+id;
	});
	$('#offer_view').click(function() {
		var val = $('.conn_auto#offer').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/offers/?oid='+id;
	});
	//Users
	$('#user_edit').click(function() {
		var val = $('.conn_auto#user').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/users/update/?id='+id;
	});
	
	$('#course_club_select').click(function() {
		var val = $('.conn_auto#golfclub').val();
		var space = val.indexOf(' ');
		var clubid = val.substr(0, space).trim();
		var clubname = val.substr(val.indexOf(': ')+1).trim();
		var spanid = $(this).attr('data-spanid');
		$('#'+spanid).html(clubname);
		$('#course_parent_club').val(clubid);
	});
	$('#course_admin_select').click(function() {
		var val = $('.conn_auto#golfcourse_admin').val(); //alert(val);
		var space = val.indexOf(' ');
		var userid = val.substr(0, space).trim();
		var username = val.substr(val.indexOf(': ')+1).trim();
		var spanid = $(this).attr('data-spanid');
		$('#'+spanid).html(username);
		$('#course_administrator').val(userid); 
	});
	
	
	$('#user_view').click(function() {
		var val = $('.conn_auto#user').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/users/?uid='+id;
	});
	//Golf clubs:
	$('#golfclub_edit').click(function() {
		var val = $('.conn_auto#golfclub').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/golfclubs/update/?id='+id;
	});
	$('#golfclub_view').click(function() {
		var val = $('.conn_auto#golfclub').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/golfclubs/?gcid='+id;
	});
	//Golf courses:
	$('#golfcourse_edit').click(function() {
		var val = $('.conn_auto#golfcourse').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/golfcourses/update/?id='+id;
	});
	$('#golfcourse_view').click(function() {
		var val = $('.conn_auto#golfcourse').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/golfcourses/?gcid='+id;
	});
	//Restaurants:
	$('#restaurant_edit').click(function() {
		var val = $('.conn_auto#restaurant').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/restaurants/update/?id='+id;
	});
	$('#restaurant_view').click(function() {
		var val = $('.conn_auto#restaurant').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/restaurants/?rid='+id;
	});
	//Beds:
	$('#bed_edit').click(function() {
		var val = $('.conn_auto#bed').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/beds/update/?id='+id;
	});
	$('#bed_view').click(function() {
		var val = $('.conn_auto#bed').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/beds/?bid='+id;
	});
	//Events:
	$('#event_edit').click(function() {
		var val = $('.conn_auto#event').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/events/update/?id='+id;
	});
	$('#event_view').click(function() {
		var val = $('.conn_auto#event').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/events/?bid='+id;
	});
	//Deals:
	$('#deal_edit').click(function() { 
		var val = $('.conn_auto#deal').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/deals/update/?id='+id;
	});
	$('#deal_view').click(function() {
		var val = $('.conn_auto#deal').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/deals/?id='+id;
	});
	
	//Deal suppliers:
	$('#deal_edit').click(function() { 
		var val = $('.conn_auto#deal').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/deals/update/?id='+id;
	});
	$('#deal_view').click(function() {
		var val = $('.conn_auto#deal').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/deals/?id='+id;
	});
	
	//App articles
	$('#app_article_edit').click(function() {
		var val = $('.conn_auto#app_article').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/admin/apps_articles/update/?id='+id;
	});
	$('#app_article_view').click(function() {
		var val = $('.conn_auto#app_article').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		window.location = '/apps_articles/?aid='+id;
	});
	
	
	
	
	
	
	//Choose post
	$('#connChoosePost').click(function() { 
		$('#conn_save').addClass('notSaved');
		if ($('#conn_title').length) {
			var str = $('#conn_title').html().replace(/\s\s+/g, ' ');
			if (str.length < 2) {
				$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
				$('body').css({'overflow':'hidden'});
		       	$('#error_message').html(jsLang.CHOOSE_ARTICLE_FIRST);
		       	$('.error_message_wrapper').center();
		       	$('.error_message_wrapper').show();
				return false();
			}
		}
		input = $('.conn_auto').val().split(' : ');
		place = $('.choosen_post').length + 1;
		chosen = $('#conn_posts').html() + '<div id="post_' + input[0] + '" class="choosen_post"><span id="' + place + '" class="conn_place">' + place + '</span>' + input[1] + '<span id="delete_' + input[0] + '" class="delete_conn_post">X</span></div>'; 
		$('#conn_posts').html(chosen);
		$('.conn_auto').val('');
	});
	//Delete post
	$('body').on('click', '.delete_conn_post', function() {
		$('#conn_save').addClass('notSaved');
		$(this).parent().remove();
		i = 0;
		$('.conn_place').each(function() {
			i++;
			$(this).html(i);
		});
	});
	//Save connections
	$('#conn_save').click(function() {
		url = $(this).attr('data-url');
		action = $(this).attr('data-action'); 
		parent = $('#conn_title').attr('class');
		$(this).removeClass('notSaved');
		posts = '';
		$('.choosen_post').each(function() {
			posts = posts + $(this).attr('id').replace('post_','') + ', ';
		}); 
		company = $(this).attr('data-company');
		$.ajax({ 
			url: url,
        	type: 'post',
        	data: {
        		action: action, 
        		posts: posts,
        		parent: parent,
        		company: company
        	},
        	success: function(response) { 
        		
        	}
        });
	});
	$('#deal_owner_select').click(function() {
		var val = $(this).parent().find('.conn_auto').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		var name = val.substr(val.indexOf(': ')+1).trim();
		$(this).parent().find('.autofill_value').html(name);
		$(this).parent().find('.autofill_id').val(id);
	});
	
	$('.contact_form_input').focus(function() {
		if ($(this).attr('class').indexOf('contact_changed') == -1) {
			$(this).html('');
			$(this).addClass('contact_changed');
		}
	});
	$('.contact_form_input').focusout(function() {
		if ($(this).attr('id') == 'contact_form_name' && $(this).html()=='') {
			$(this).html(jsLang.NAME);
			$(this).removeClass('contact_changed');
		}
		if ($(this).attr('id') == 'contact_form_phone' && $(this).html()=='') {
			$(this).html(jsLang.PHONE);
			$(this).removeClass('contact_changed');
		}
		if ($(this).attr('id') == 'contact_form_email' && $(this).html()=='') {
			$(this).html(jsLang.EMAIL);
			$(this).removeClass('contact_changed');
		}
		if ($(this).attr('id') == 'contact_form_subject' && $(this).html()=='') {
			$(this).html(jsLang.SUBJECT);
			$(this).removeClass('contact_changed');
		}
		if ($(this).attr('id') == 'contact_form_message' && $(this).html()=='') {
			$(this).html(jsLang.MESSAGE);
			$(this).removeClass('contact_changed');
		}
	});
	$('#contact_form_submit').click(function() { 
		var msg = '';
		if ($.trim($('#contact_form_name').html()) == jsLang.NAME) {
			msg = jsLang.NAME_MISSING + '<br />';
		} 
		if ( $.trim($('#contact_form_phone').html())==jsLang.PHONE) {
			msg = msg + jsLang.PHONE_MISSING + '<br />';
		} 
		else { 
			if ( !validatePhone ( $.trim ( $('#contact_form_phone').html() ) ) ) { 
				msg = msg + jsLang.PHONE_INCORRECT + '<br />';
			}
		} 
		if ( $.trim($('#contact_form_email').html())==jsLang.EMAIL ) {
			msg = msg + jsLang.EMAIL_MISSING + '<br />';
		}
		else {
			if ( !validateMail ( $.trim ($('#contact_form_email').html() ))) {
				msg = msg + jsLang.EMAIL_INCORRECT + '<br />';
			}
		} 
		if ( $.trim($('#contact_form_subject').html())==jsLang.SUBJECT ) {
			msg = msg + jsLang.SUBJECT_MISSING + '<br />';
		}
		if ( $.trim($('#contact_form_message').html())==jsLang.MESSAGE ) {
			msg = msg + jsLang.MESSAGE_MISSING + '<br />';
		} 
		if (msg.length > 10) { 
			$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
       		$('body').css({'overflow':'hidden'});
			$('#error_message').html(msg);
			$('.error_message_wrapper').center();
			$('.error_message_wrapper').show();
		}
		else { 
			name = $.trim($('#contact_form_name').html());
			phone = $.trim($('#contact_form_phone').html());
			email = $.trim($('#contact_form_email').html());
			subject = $.trim($('#contact_form_subject').html());
			message = $.trim($('#contact_form_message').html());
			$.ajax({
        		url: '/_php/mail.management.php',
        		type: 'post',
        		data: {
        			action: 'contact_us', 
        			name: name,
        			phone: phone,
        			email: email,
        			message: message
        		},
        		success: function(response) {
        			$('#contact_form_inner').html(response);
        		},
        		error: function() {
        			alert('Fel');
        		}
        	});
		}
	});
	$('#contact_form_cancel').click(function() {
		$('#contact_form').hide();
	});
	
	function validatePhone(phone) {
		phone = phone.replace(/\s+/g, '');
		phone = phone.replace('-', '')
		return $.isNumeric(phone)
	}
	
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
	
	//App management
	$('body').on('click', '.list_bool', function() {
		var id = $(this).attr('id');
		$(this).removeClass('red');
		$(this).removeClass('green')
		var table = $(this).attr('class').substr($(this).attr('class').lastIndexOf(' ')).trim();
		//alert(table + ': ' + id  );
		$.ajax({ 
			url: '/_php/common.php',
        	type: 'post',
        	data: {
        		action: 'toggle_status', 
        		table: table,
        		id: id
        	},
        	success: function(response) { 
        		if (response > 0) {
        			$('.list_bool#'+id).addClass('green');
        			$('.list_bool#'+id).html('&#x2713;');
        		}
        		if (response == 0) {
        			$('.list_bool#'+id).addClass('red');
        			$('.list_bool#'+id).html('&#x2717;');
        		}
        	}
        });
	});
	
	$('.update_account').click(function() {
		var url = '/admin/users/update/?id='+$(this).attr('id');
		window.location = url;
	});
	$('.update_course').click(function() {
		var url = '/admin/golfcourses/update/?id='+$(this).attr('id');
		window.location = url;
	});
	$('.update_shop').click(function() {
		var url = '/admin/shops/update/?id='+$(this).attr('id');
		window.location = url;
	});
	
	$('#user_company_offers').click(function() {
		$('#company_offers_form').toggle();
	});
	$('.update_company_details').click(function() {
		if ($(this).prop('value') == 'Close') {
			$(this).prop('value','Change');
		}
		else {
			$(this).prop('value','Close');
		}
		$('#company_offers_form').toggle();
	});

	$('body').on('click', '.preview', function() {
		var id = $(this).attr('id');
		if ($(this).attr('class').indexOf('preview_app')) {
			url = '/_php/apps.php';
			action = 'preview_app';
			container = '#preview_app_content';
			parent = '#preview_app_wrapper';
		}
		$.ajax({ 
			url: url,
        	type: 'post',
        	data: {
        		action: action, 
        		id: id
        	},
        	success: function(response) { 
        		$('#preview_app_content').html(response);
        		$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
				$('body').css({'overflow':'hidden'});
				$(parent).center();
				$(parent).show();
        	}
        });
	});


	/* Image Preview and Upload */
	function imageIsLoaded(e) {
	    $('#myImg').attr('src', e.target.result);
	};
	$('#filesToUpload').change(function(evt) {
	    var reader = new FileReader();
	    reader.onload = imageIsLoaded;
	    reader.readAsDataURL(this.files[0]);
	});
	
	/* Add city, add district */
	$('.form_title_link').click(function() { 
		if ($(this).attr('id') == 'add_city') {
			if ($('#course_countryid').val() != 0 && $('#course_regionid').val() != 0) {
				$('.extra_city').html('<span id="add_city_input" contenteditable="true"></span><span class="add_city_submit">'+jsLang.ADD+' &raquo;</span>');
			}
			else {
				alert(jsLang.ADD_CITY_NOT);
			}
		}
		if ($(this).attr('id') == 'add_district') {
			if ($('#course_countryid').val() != 0) {
				$('.extra_district').html('<span id="add_district_input" contenteditable="true"></span><span class="add_district_submit">'+jsLang.ADD+' &raquo;</span>');
			}
			else {
				alert(jsLang.ADD_DISTRICT_NOT);
			}
		}
	});
	
	$('body').on('click', '.add_city_submit', function() {
		var country = $('.CountrySelect').val();
		var region = $('.RegionSelect').val();
		var city = $.trim($('#add_city_input').text());
		$.ajax({ 
			url: '/_php/db.management.php',
        	type: 'post',
        	data: {
        		action: 'addCity', 
        		country: country,
        		region: region,
        		city: city
        	},
        	success: function(response) { 
        		$('.CitySelect').append($("<option/>", {
        			value: response,
        			text: city
    			}));
        		$('.CitySelect').val(response);
        		$('#add_city_input').html('');
        	}
        });
	});
	
	$('body').on('click', '.add_district_submit', function() { 
		var country = $('.CountrySelect').val();
		var district = $.trim($('#add_district_input').text()); 
		$.ajax({ 
			url: '/_php/db.management.php',
        	type: 'post',
        	data: {
        		action: 'addDistrict', 
        		country: country,
        		district: district
        	},
        	success: function(response) { 
        		$('.district_select').append($("<option/>", {
        			value: response,
        			text: district
    			}));
        		$('.district_select').val(response);
        		$('#add_district_input').html('');
        	}
        });
	});
	
	$('.shortdesc').keyup(function() {
		var chars = $(this).val().length;
		var max = $('.max_chars').attr('data-maxchars');
		var warn = max - 5;
		if (chars > warn) {
			$('.usedchars').addClass('red');
			$('.usedchars').effect('pulsate');
		}
		if (chars > max) {
			$(this).val($(this).val().substring(0, max));
			$('.usedchars').html(max);
			$('.max_chars').css('font-weight', 'bold');
			$('.max_chars').addClass('red');
			alert(jsLang.MAXCHAR_MESSAGE);
		}
		else {
			$('.usedchars').html(chars);
		}
	});
	
	//Connections to golf course -> golf club
	$('#golfclub_conn_add').click(function() {
		var choice = $('#golfclub').val().substring(0, $('#golfclub').val().indexOf(':')).trim();
		var parent = $(this).attr('data-parent');
		var type = 'club';
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'addConnection', 
        		choice: choice,
        		parent: parent,
        		type: 'club'
        	},
        	success: function(response) { 
        		location.reload();
        	}
        });
	});
	//Connections to golf course -> golf course
	$('#golfcourse_conn_add').click(function() { 
		var choice = $('#golfcourse').val().substring(0, $('#golfcourse').val().indexOf(':')).trim();
		var parent = $(this).attr('data-parent');
		var type = 'course';
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'addConnection', 
        		choice: choice,
        		parent: parent,
        		type: type
        	},
        	success: function(response) { 
        		location.reload();
        	}
        });
	});
	//Connections to golf course -> restaurants
	$('#restaurants_conn_add').click(function() { 
		var choice = $('#restaurants').val().substring(0, $('#restaurants').val().indexOf(':')).trim();
		var parent = $(this).attr('data-parent');
		var type = 'restaurant';
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'addConnection', 
        		choice: choice,
        		parent: parent,
        		type: type
        	},
        	success: function(response) { 
        		location.reload();
        	}
        });
	});
	//Connections to golf course -> accomodations
	$('#bed_conn_add').click(function() { 
		var choice = $('#bed').val().substring(0, $('#bed').val().indexOf(':')).trim();
		var parent = $(this).attr('data-parent');
		var type = 'accomodation';
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'addConnection', 
        		choice: choice,
        		parent: parent,
        		type: type
        	},
        	success: function(response) { 
        		location.reload();
        	}
        });
	});
	
	//Reviews
	$('.review_point').click(function() {
		var id = $(this).attr('id'); 
		var type = id.substring(0, id.indexOf('_'));
		var point = id.substring(id.lastIndexOf('_')+1);
		$('#'+type+'_marker').val(point);
		for (i=1;i<=5;i++) {
			var target = '#'+type+'_image_point_'+i;
			$(target).css('-moz-opacity', '0.5');
			$(target).css('opacity', '0.5');
		}
		for (i=1;i<=point;i++) {
			var target = '#'+type+'_image_point_'+i;
			$(target).css('-moz-opacity', '1');
			$(target).css('opacity', '1');
		}
	});
	$('#review_submit').click(function() {
		var id = $('#review_form_content').attr('class');
		$('.point_marker').each(function(index, value) {
			if ($(this).val() != '') {
				var type = $(this).attr('id');
				var point = $(this).val();
				$.ajax({
        			url: '/_php/reviews.php',
    	    		type: 'post',
	        		data: {
    	    			action: 'insert_review', 
	   	     			id: id,
	   	     			point: point,
	   	     			type: type
	        		},
        			success: function(response) { 
        				if (response > 0) {
        					$('.rate_'+ type.replace('_marker','').toLowerCase() +'-value').html(response+' / 5');
        					$('#review_form_wrapper').html('<div id="review_form_content"><div class="popup_close">x</div>' + jsLang.REVIEW_THNX + '</div>');
        				}
        				else {
        					$('#review_form_wrapper').html('<div id="review_form_content"><div class="popup_close">x</div>' + jsLang.REVIEW_ERROR + '</div>');
        				}
					}
				});
			}
		});	
	}); 
	
	//Deals on startpage - administration
	//Change type
	$('.deal_prev_types').click(function() {
		var type = $(this).attr('id');
		var obj = '.deal_prev_types#'+type;
		$('.deal_prev_types').each(function() {
			$(this).prop('checked', false);
		});
		$(obj).prop('checked', true);
		$.ajax({ 
			url: '/_php/deals.php',
        	type: 'post',
        	data: {
        		action: 'admin_types', 
        		type: type
        	},
        	success: function(response) { 
        		$('.current').html(response);
        	}
        });
	});
	//Update
	$('#startpage_deal').click(function() {
		var val = $(this).parent().find('#deal').val();
		var space = val.indexOf(' ');
		var id = val.substr(0, space).trim();
		var type = 0;
		$('.deal_prev_types').each(function() {
			if ($(this).is(':checked')) {
				type = $(this).attr('id');
			}
		});
		if (type > 0) {
			$.ajax({
    	    	url: '/_php/deals.php',
    	    	type: 'post',
       			data: {
        			action: 'startpage', 
        			id: id,
        			type: type
        		},
        		success: function(response) {
        			$('.current').html(response);
        		},
        		error: function() {
        			alert('Fel');
        		}
        	});
        }
	});
	
	//Registration
	
	
	$('#member_password_submit').click(function() {
		var newPass = $('#member_password').val();
		var target = $('#register_form').attr('data-target');
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'changeMemberPass', 
        		pass: newPass
        	},
        	success: function(response) { 
        		$('#reg_form_content').html(response);
	        	$('#new_member_password_wrapper').hide();
	        	setTimeout(
  					function() {
	        			window.location = target;
	        	  }, 300);
        	}
        });
	});
	
	
	$('.register_submit_button').click(function() {
		var email = $('#member_email').val();
		var target = $('#register_form').attr('data-target');
		if (!validateMail(email)) {
			$('#error_message').html(jsLang.EMAIL_INCORRECT); 
	       	$('.error_message_wrapper').center(); 
	       	$('.error_message_wrapper').show(); 
	       	return false;	
		}
		$.ajax({ 
			url: '/_php/forms.management.php',
        	type: 'post',
        	data: {
        		action: 'registerMember', 
        		email: email,
        		target: target
        	},
        	success: function(response) { 
        		$('.register_startpage').fadeOut(300);
        		
        		$('.register_startpage').fadeIn(300);
        		setTimeout(
  					function() {
	        		$('#reg_form_content').html(response);
	        		if (response.indexOf('!!!') >= 0) {
	        			$('#new_member_password_wrapper').fadeIn(300);
	        			$('#new_member_email_wrapper').hide();
	        		}
	        	  }, 300);
        		return false;
        	}
        });
        return false;
	});
	
	
	$('#member_email').focus(function() {
		$(this).val(''); 
	});
	
	$('#deal_confirmation_form input[type=text]').focus(function() {
		$(this).val('');
	});
	$('#deal_confirmation_form input[type=email]').focus(function() {
		$(this).val('');
	});
	
//Confirming deal/shop/destination	
	$('#deal_confirmed_submit').click(function() { 
		//alert(jsLang.FIRST_NAME);
		var msg = '';
		if (($('#firstname').val().toLowerCase() == jsLang.FIRST_NAME.toLowerCase()) || ($('#firstname').val() == '')) {
			msg = msg + jsLang.FIRST_NAME  + ' ' + jsLang.IS_MISSING + '<br />';
		}
		if (($('#lastname').val().toLowerCase() == jsLang.LAST_NAME.toLowerCase()) || ($('#lastname').val() == '')) {
			msg = msg + jsLang.LAST_NAME  + ' ' + jsLang.IS_MISSING + '<br />';
		}
		if (($('#phone').val().toLowerCase() == jsLang.PHONE.toLowerCase()) || ($('#phone').val() == '')) {
			msg = msg + jsLang.PHONE  + ' ' + jsLang.IS_MISSING + '<br />';
		}
		else {
			var valid = validatePhone($('#phone').val());
			if (!valid) {
				msg = msg + jsLang.PHONE  + ' ' + jsLang.IS_NOT_CORRECT + '<br />';
			}
		}
		if (($('#email').val().toLowerCase() == jsLang.EMAIL.toLowerCase()) || ($('#email').val() == '')) {
			msg = msg + jsLang.EMAIL  + ' ' + jsLang.IS_MISSING + '<br />';
		}
		else {
			var valMail = validateMail($('#email').val());
			if (!valMail) {
				msg = msg + jsLang.EMAIL + ' ' + jsLang.IS_NOT_CORRECT + '<br />';
			}
		}
		if(!$('#confirm_pdata').is(':checked')) {
			msg = msg + jsLang.CONFIRM_PDATA + '<br />';
		}
		if(!$('#confirm_agree').is(':checked')) {
			msg = msg + jsLang.ACCEPT_UNCHECKED + '<br />';
		}
		
		if (msg.length > 0) { 
			//alert (msg); //MESSAGEHERE
			$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
			$('body').css({'overflow':'hidden'});
	       	$('#error_message').html(msg); 
	       	$('.error_message_wrapper').center(); 
	       	$('.error_message_wrapper').show(); 
			return false;
		}
		else {
			$.ajax({ 
			url: '/_php/deals.php',
        	type: 'post',
        	data: {
        		action: 'closeDealConfirm', 
        		fname: $('#firstname').val(),
        		lname: $('#lastname').val(),
        		phone: $('#phone').val(),
        		email: $('#email').val()
        	},
        	success: function(response) { 
        		$('#content').html(response);
        		/*$('.register_startpage').fadeOut(300);
        		
        		$('.register_startpage').fadeIn(300);
        		setTimeout(
  					function() {
	        		$('#reg_form_content').html(response);
	        		if (response.indexOf('!!!') >= 0) {
	        			$('#new_member_password_wrapper').fadeIn(300);
	        			$('#new_member_email_wrapper').hide();
	        		}
	        	  }, 300);*/
        		return false;
        	}
        });
			return true;
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
		
});


	
