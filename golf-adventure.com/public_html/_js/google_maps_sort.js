//<![CDATA[

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

/****************************************/
var styles = [[{
        url: 'http://golfinmobile.wpwm.se/_icons/greenClusterIcon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 500,
        textColor: '#000000',
        textSize: 12, 
        backgroundPosition:'center 6px'
        
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/greenClusterIcon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 500,
        textColor: '#000000',
        textSize: 12,  //anchorText: [-35,-40]
        backgroundPosition:'center 6px'
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/greenClusterIcon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 500,
        textColor: '#000000',
        textSize: 12,// anchorText: [-35,-40]
        backgroundPosition:'center 6px'
      }]];
var stylesRst = [[{
        url: 'http://golfinmobile.wpwm.se/_icons/yellowClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 300,
        textColor: '#ffbb00',
        textSize: 12, 
        backgroundPosition:'center 6px',
        iconAnchor:[-5,0]
        
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/yellowClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 300,
        textColor: '#ffbb00',
        textSize: 12,  //anchorText: [-35,-40]
        backgroundPosition:'center 6px',
        iconAnchor:[-5,0]
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/yellowClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 300,
        textColor: '#ffbb00',
        textSize: 12,// anchorText: [-35,-40]
        backgroundPosition:'center 6px',
        iconAnchor:[-5,0]
      }]];
var stylesH = [[{
        url: 'http://golfinmobile.wpwm.se/_icons/blueClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 400,
        textColor: '#3399ff',
        textSize: 12, 
        backgroundPosition:'center 6px',
        iconAnchor:[25,0]
        
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/blueClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 400,
        textColor: '#3399ff',
        textSize: 12,  //anchorText: [-35,-40]
        backgroundPosition:'center 6px',
        iconAnchor:[25,0]
      }, {
        url: 'http://golfinmobile.wpwm.se/_icons/blueClustericon.png',
        height: 60,
        width: 50,
        anchor: [0, 0],
        zIndex: 400,
        textColor: '#3399ff',
        textSize: 12,// anchorText: [-35,-40]
        backgroundPosition:'center 6px',
        iconAnchor:[25,0]
      }]];
/****************************************/
var customIcons = {
	'restaurant': {
    	icon: '/_icons/marker_restaurant.png'
    },
    'bed': {
    	icon: '/_icons/marker_accomodation.png'
    },
    'club': {
      	icon: '/_icons/marker_golfclub.png'
    },
    'course': {
      	icon: '/_icons/marker_golfclub.png'
    },
    'ocourse': {
    	icon: '/_icons/marker_offers.png'
    },
    'course-h': {
    	icon: '/_icons/marker_course_hotel.png'
    },
    'bed-gc': {
    	icon: '/_icons/marker_course_hotel.png'
    },
    'course-r': {
    	icon: '/_icons/marker_course_restaurant.png'
    },
    'restaurant-gc': {
    	icon: '/_icons/marker_course_restaurant.png'
    },
    'restaurant-gc-h': {
    	icon: '/_icons/marker_course_hotel_restaurant.png'
    },
    'bed-gc-r': {
    	icon: '/_icons/marker_course_hotel_restaurant.png'
    },
    'course-r-h': {
    	icon: '/_icons/marker_course_hotel_restaurant.png'
    }
    
}; 
	var map;
	//var marker_list = [];
	var getallMarkersTglLabels = []; // to manage show hide markers labels
	
	var markerClusterGC, markerClusterHotel, markerClusterRst; 
	var markersArrGC = [];
	var markersArrHotel = [];
	var markersArrRst = [];
	
	
function load() { 
	map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(62.522874, 15.658942),
        zoom: 5,
        mapTypeId: 'roadmap'
    });
    
    
    var infoWindow = new google.maps.InfoWindow;
    // Change this depending on the name of your PHP file
    downloadUrl('/_php/gen_map_xml.php', function(data) { 
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker"); 
        
        for (var i = 0; i < markers.length; i++) { 
	    	var id = markers[i].getAttribute("id"); 
          	var name = markers[i].getAttribute("name"); 
          	var address = markers[i].getAttribute("address");
          	var zip = markers[i].getAttribute("zip");
          	var city = markers[i].getAttribute("city");
          	var phone = markers[i].getAttribute("phone");
          
          	var desc = markers[i].getAttribute("desc");
          	var url = markers[i].getAttribute("url");
          	var img = markers[i].getAttribute("img"); 
          	var facilities = markers[i].getAttribute("facilities");
          	var addons = markers[i].getAttribute("addons");
          	var rate = markers[i].getAttribute("rate");
          
          	var type = markers[i].getAttribute("type");
          	var details =  markers[i].getAttribute("details");
          	var point = new google.maps.LatLng(
            parseFloat(markers[i].getAttribute("lat")),
            parseFloat(markers[i].getAttribute("lng")));
			
			/*if(type == 'course-r-h' || type =='bed-gc-r' || type == 'restaurant-gc-h') {
				console.log(type);
			}else{ continue; }*/
			
			
		  	html = "";
		   	if (facilities != null) {
	        	html = html + '<div class="facilities_wrap">' + facilities + '<div class="clear_both"></div></div>';
	      	}
          	if (img != null && img != 0) {    
          		html = html +  "<div class='gm_img'><img src='" + img + "' title='" + name + "' class='googlemapimg' /></div><br>";
          	}
          	html = html + "<h3>" + name + ", " + city + "<br /><img src='/_icons/phone-20.png' /> " + phone + "</h3> <div class='gm_address'>" + address + ", " + zip + " " + city + "</div>";
          	if (url != null) {
          		html = html + "<div class='gm_url'><a href='" + url + "'>" + url + " &raquo;</a></div>";
          	}
          	if (desc != null) {
          		html = html + "<div class='gm_desc'>" + desc + "</div>";
          	}
          	if (addons != null) {
	        	html = html + '<div class="addons">' + addons + '</div>';
	      	} 
	      	if (details != null) {
	          	//html = html + "<div><a href='/golfcourses/?gcid=" + id + "' class='right'> "+details+"</a></div>";
	      	}
          	
          	var icon = customIcons[type] || {}; 
          	if (map.getZoom() > 6) {
    			LC = 'maplabels';
    		}
    		else {
    			LC = 'maplabels2';
    		}
    		if (type == 'ocourse') {
				var labelCont = name + '<br>' + jsLang.SEE_COURSE_OFFERS + ' &raquo;';
			}
			else {
				var labelCont = name;
			}
			//labelCont = 'ÄÄÄ'; 
          	/*var marker = new MarkerWithLabel({
            	map: map,
            	position: point,
            	id: id,
            	labelContent: labelCont,
    			labelAnchor: new google.maps.Point(15, 15),
    			labelInBackground: false,
    			labelClass: LC,
            	icon: icon.icon
          	});
          	*/
          	// markersArrHotel.push(marker);
          	
          	
          	if(type == 'course-r-h' || type =='bed-gc-r' || type == 'restaurant-gc-h') {
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrGC.push(marker);
					
				var marker = new MarkerWithLabel({
						map: map,
						position: point,
						id: id,
						labelContent: labelCont,
						labelAnchor: new google.maps.Point(15, 15),
						labelInBackground: false,
						zIndex: 500,
						labelClass: LC,
						icon: icon.icon
					});
				markersArrRst.push(marker);
				
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});	
				markersArrHotel.push(marker);
			}
			else if(type == 'course-h' || type =='bed-gc') {
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				
				markersArrGC.push(marker);
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrHotel.push(marker);
			}
			else if(type == 'course-r' || type ==  'restaurant-gc'){
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrGC.push(marker);
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrRst.push(marker);
			}
			else if(type == 'restaurant') {
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 460,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrRst.push(marker);
			}
			else if(type == 'bed') {
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 480,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrHotel.push(marker);
			}
			else if(type == 'course') {
				var marker = new MarkerWithLabel({
					map: map,
					position: point,
					id: id,
					labelContent: labelCont,
					labelAnchor: new google.maps.Point(15, 15),
					labelInBackground: false,
					zIndex: 500,
					labelClass: LC,
					icon: icon.icon
				});
				markersArrGC.push(marker);
			}
			else{ continue;
			
			}
          	
          	bindInfoWindow(marker, map, infoWindow, html);
          	
          	getallMarkersTglLabels.push(marker);
        }
        
		/********************/
		markerClusterHotel   = new MarkerClusterer(map, markersArrHotel, {
				maxZoom: 15,styles: stylesH[0] 
		});
		markerClusterRst   = new MarkerClusterer(map, markersArrRst, {
				maxZoom: 15,styles: stylesRst[0] 
		});
		markerClusterGC   = new MarkerClusterer(map, markersArrGC, {
				maxZoom: 15,styles: styles[0] 
		});
		/**THIS**/
		setTimeout(function(){ 
			$('body').find('.gmnoprint').css({'opacity':'0.9'}); 
			$('body').find('.maplabels2').css({'opacity':'0.9'}); 
		}, 4000);
		//$('body').find('.gmnoprint').css({'opacity':'0.9'});
		/**THIS**/
		/********************/
		
        google.maps.event.addListener(marker, "click", function (e) { alert(e); });
        
        //setTimeout(function(){
		google.maps.event.addListener(map, 'zoom_changed', function() { 
    		if(map.getZoom() <= 4){
				if(getallMarkersTglLabels.length > 0){ 
					for(i in getallMarkersTglLabels){

						getallMarkersTglLabels[i].labelVisible = false; 
					}
				}
			}else{
				if(getallMarkersTglLabels.length > 0){ 
					for(i in getallMarkersTglLabels){
						
						getallMarkersTglLabels[i].labelVisible = true; 
					}
				}
			}
			//START
			setTimeout(function(){ 
				$('body').find('.gmnoprint').css({'opacity':'0.9'}); 
				$('body').find('.maplabels2').css({'opacity':'0.9'}); 
			}, 1500);
			//END
	    });  
    });
}

function toggleAllLabels(){
	console.log('In toggleAllLabels');
	$('body').find('.maplabels2').hide();
}
function eraseMarkers() {
	for (i = 0; i < locations.length; i++) { 
	//alert('*'):
        marker[i].setVisible(false);
	}
}

function showMarkers() {
    for (i = 0; i < locations.length; i++) { 
        marker[i].setVisible(true);
    }
}

function getMarkers() {
    for (i = 0; i < locations.length; i++) { 
        marker[i] = new MarkerWithLabel({
          	position: new google.maps.LatLng(locations[i][1], locations[i][2]),
            	draggable: false,
              	map: map,
              	labelContent: locations[i][3],
              	labelAnchor: new google.maps.Point(30, 0),
              	labelClass: "labels", // the CSS class for the label
              	labelStyle: {opacity: 0.75}
        });
	}
    eraseMarkers();
}

function hideMarkerLabels() {
	//alert('HIDE');
	
//	$('.maplables2').addClass('maplabels');
//	$('.maplables').removeClass('maplabels2');
}

function bindInfoWindow(marker, map, infoWindow, html) {
    google.maps.event.addListener(marker, 'mouseover', function() {
        infoWindow.setContent(html);
        infoWindow.open(map, marker);
    });
    
    google.maps.event.addListener(marker, 'click', function() {
    	if(marker.labelContent.indexOf('<br>') !== -1) {
	        window.location ='/golf-offers/?id='+marker.id;
	    }
    });     
}

function downloadUrl(url, callback) {
   	var request = window.ActiveXObject ?
       	new ActiveXObject('Microsoft.XMLHTTP') :
       	new XMLHttpRequest;

   	request.onreadystatechange = function() {
     	if (request.readyState == 4) {
       		request.onreadystatechange = doNothing;
       		callback(request, request.status);
      	}
   	};

   	request.open('GET', url, true);
  	request.send(null);
}

function doNothing() {}

//]]>
