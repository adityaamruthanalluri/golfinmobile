//Multi language

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

	$('.language_choice').click(function() {
		var lang = $(this).attr('id');
		var url = $(this).attr('data-url'); 
		$.ajax({
        	url: '/_php/common.php',
        	type: 'post',
        	data: {
        		action: 'toggle_lang', 
        		lang: lang,
        		url: url
        	},
        	success: function(response) { 
        		if (response == '00000') {
        			$(".no_translation").center();
        			$(".no_translation").show();
        			setTimeout(function (){
						$(".no_translation").hide();
						window.location = '/';
				    }, 2000);
        		}
        		else {
	        		window.location = response;
	        	}
        	}
        });
	});
	
	$('body').on('click', '#conn_lang img', function() { 
		var type = $(this).parents().attr('class');
		switch (type) {
			case 'articles':
				var obj = '#article_lang_rel';
				break;
			case 'categories':
				var obj = '#category_lang_rel';
				break;
		}
		//alert(obj);
		var id = $(this).attr('data-id');
		var lang = $(this).attr('src').substr(13, 2);
		var parent = $('#main_lang img').attr('data-plang');
		if ($(this).attr('class').indexOf('active') >= 0) {
			alert(jsLang.LANG_TAKEN);
		}
		else { 
			$.ajax({
        		url: '/_php/common.php',
        		type: 'post',
        		data: {
        			action: 'conn_rel_lang', 
        			lang: lang
        		},
        		success: function() {
					$('#plang').val(parent);
					$('#clang').val(lang);
					$('#id').val(id);
		    		$(obj).center();
		    		$(obj).show();
		    	}
		    });
		} 
	});
	
	$('#article_lang_rel .submit_button').click(function() { 
		var child = $('#article').val();
		child = child.substr(0, child.indexOf(':'));
		child = $.trim(child);
		var parent = $('#parentid').val();
		var plang = $('#plang').val();
		var clang = $('#clang').val();
		$.ajax({
        	url: '/_php/articles.php',
        	type: 'post',
        	data: {
        		action: 'article_lang_rel', 
        		parent: parent,
        		child: child,
        		plang: plang,
        		clang: clang
        	},
        	success: function(response) { alert(response);
				$('#conn_lang img').each(function() {
					if ( $(this).attr('src').indexOf(clang) > -1) {
						$(this).addClass('active');
					}
				});
				$('#article_lang_rel').toggle();
        	}
        });
	});
	
	$('#category_lang_rel .submit_button').click(function() {
		var child = $('#category').val();
		child = child.substr(0, child.indexOf(':'));
		child = $.trim(child);
		var parent = $('#parentid').val();
		var plang = $('#plang').val();
		var clang = $('#clang').val();
		$.ajax({
        	url: '/_php/categories.php',
        	type: 'post',
        	data: {
        		action: 'category_lang_rel', 
        		parent: parent,
        		child: child,
        		plang: plang,
        		clang: clang
        	},
        	success: function(response) { 
				$('#conn_lang img').each(function() {
					if ( $(this).attr('src').indexOf(clang) > -1) {
						$(this).addClass('active');
					}
				});
				$('#category_lang_rel').toggle();
        	}
        });
	});

});