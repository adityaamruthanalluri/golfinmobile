//Multi language
//Settings

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
	
	//Settings
	$('body').on('click', '.setting_save', function() { 
    	var val = $(this).prev('td').html();
    	var src = $(this).attr('id');
    	var saved = $(this).prev('td');
    	if ($('.red').length > 0) {
	    	$.ajax({ 
	        	url: '/_php/db.management.php',
	        	type: 'post',
	    		data: {
	    			val: val,
	    			src: src,
	    			'ajax-action': 'save_setting'
	       		},
	       		success: function(response) { 
	       			if (response != 1) {
	       				$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
						$('body').css({'overflow':'hidden'});
	       				$('#error_message').html(response);
	       				$('.error_message_wrapper').center();
	       				$('.error_message_wrapper').show();
	       			}
	       			else {
		       			saved.removeClass('red');
		       		}
	       		},
	       		error: function() {
	       			//alert(jsLang.SETTING_NOT_SAVED);
	       			$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
					$('body').css({'overflow':'hidden'});
			       	$('#error_message').html('****'+jsLang.SETTING_NOT_SAVED);
	    		   	$('.error_message_wrapper').center();
			       	$('.error_message_wrapper').show();
	       		}
	       	});
	    }
    });
    
    $('.setting_change').keydown(function() { 
    	$(this).addClass('red');
    });
    
    $(window).bind('beforeunload', function(){
    	if (window.location.pathname=='/admin/settings/' && $('.red').length > 0) {
	  		return jsLang.LEAVE_PAGE_SETTINGS_NOT_SAVED;
	  	}
	});
	
});