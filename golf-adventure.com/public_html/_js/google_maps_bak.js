

//<![CDATA[
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

    var customIcons = {
      restaurant: {
        icon: '/_icons/location_restaurant_small.png'
      },
      bed: {
        icon: '/_icons/location_accommodation_small.png'
      },
      club: {
      	icon: '/_icons/location_golfclub_small.png'
      },
      sclub: {
      	icon: '/_icons/location_spons_golfclub_small.png'
      },
      course: {
      	icon: '/_icons/location_golfclub_small.png'
      },
      scourse: {
      	icon: '/_icons/location_spons_golfclub_small.png'
      },
      srestaurant: {
      	icon: '/_icons/location_spons-restaurant_small.png'
      },
      sbed: {
      	icon: '/_icons/location_spons-accommodation_small.png'
      }
    }; 
	
    function load() { 
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(62.522874, 15.658942),
        zoom: 5,
        mapTypeId: 'roadmap'
      });
      var infoWindow = new google.maps.InfoWindow;

      // Change this depending on the name of your PHP file
      downloadUrl('/_php/gen_map_xml.php', function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker"); 
        var marker_list = [];
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
			//alert(img);
		  html = "";
		   if (facilities != null) {
	          html = html + '<div class="facilities_wrap">' + facilities + '<div class="clear_both"></div></div>';
	      }
          if (img != null && img != 0) {    
          	html = html +  "<div class='gm_img'><img src='" + img + "' title='" + name + "' class='googlemapimg' /></div><br>";
          }
          
          html = html + "<h3>" + name + ", " + city + "<br /><img src='/_icons/phone-20.png' /> " + phone + "</h3> <div class='gm_address'>" + address + ", " + zip + " " + city + "</div>";
          if (url != null) {
          	html = html + "<div class='gm_url'><a href='" + url + "'>" + url + "</a></div>";
          }
          if (desc != null) {
          	html = html + "<div class='gm_desc'>" + desc + "</div>";
          }
          
          if (addons != null) {
	          html = html + '<div class="addons">' + addons + '</div>';
	      } 
	      if (login == 1) {
	          html = html + "<div><a href='/golfcourses/?gcid=" + id + "' class='right'> "+details+"</a></div>";
	      }
	      else {
	      	html = html + details;
	      }
          
          	
          
          
          var icon = customIcons[type] || {}; 
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: icon.icon
          });
          
          bindInfoWindow(marker, map, infoWindow, html);
          if (type.substring(0, 1) != 's') {
          	//alert(name);
          
          	marker_list.push(marker);
          }
        }
        var markerCluster = new MarkerClusterer(map, marker_list);
      });
    }

    function bindInfoWindow(marker, map, infoWindow, html) {
      google.maps.event.addListener(marker, 'click', function() {
        infoWindow.setContent(html);
        infoWindow.open(map, marker);
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
