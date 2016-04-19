//Multi language
//Newsletter

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

	//Newsletter
	$('#send_newsletter').click(function() {
		id = $(this).attr('class');
		$.ajax({
        	url: '/_php/newsletter.php',
        	type: 'post',
        	data: {
        		action: 'send_newsletter', 
        		id: id
        	},
        	success: function(response) {
        		$(this).html($.datepicker.formatDate('yyyy-mm-dd'), new Date());
        		$('#forms_bkg').css({ opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
        		$('body').css({'overflow':'hidden'});
				$('.error_message_title').html(jsLang.RESULT);
   				$('#error_message').html(response);
   				$('.error_message_wrapper').center();
   				$('.error_message_wrapper').show();
        	}
        });        	
	});
});