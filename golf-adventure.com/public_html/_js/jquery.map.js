//Multi language
//Clear map input on key down

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
	
	//Clear map input on key down
	$('#addressInput').focus(function() { 
		$(this).val('');
	});
	
});