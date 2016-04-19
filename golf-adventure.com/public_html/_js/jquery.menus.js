//Multi language
//Dropdown menus
//Main menu toggle - for small devices
//Menu button toggle - for small devices
//Admin Menu Toggle

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
	
	//Dropdown menus
	$('.menucontainer li').hover(
		function(){
        $(this).children('ul').hide();
        $(this).children('ul').slideDown('fast');
    },
    function () {
        $('ul', this).slideUp('fast');            
    });
    
    /*Main menu toggle - for small devices
	$("#mobile_menu_button").click(function() { alert('Ä');
		$("#main_menu_wrapper").toggle();
		$("#admin_menu_wrapper").toggle();
	});*/
	
	//Menu button toggle - for small devices
	$('#mobile_menu_button').click(function() { 
		$('.menu').toggle();
	});
	
	//Admin Menu Toggle
	$('.admin_menu_toggle').click(function() { 
		$('.admin_menu_item').toggle();
	});
	
});