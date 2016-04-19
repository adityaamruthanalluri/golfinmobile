var map,placeservice, infowindow, bounds;
var markersArr = [];
function initMap() {
	infowindow = new google.maps.InfoWindow();
	bounds = new google.maps.LatLngBounds();
	
	map = new google.maps.Map(document.getElementById('map_canvas'), {
	  //center: {lat: 62.85294449461765, lng: 16.050727562500015},
	  center: new google.maps.LatLng(62.85294449461765, 16.050727562500015),
	  zoom: 7
	});
	
	placeservice = new google.maps.places.PlacesService(map);
	
	/*share location icon on map.*/
	/*var DivShareLoc = document.createElement('div');
	DivShareLoc.innerHTML = '<div style="margin-top:10px;" ><img src="images/marker.png"></div>';
	DivShareLoc.index = 1;
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(DivShareLoc);*/
		
	google.maps.event.addListenerOnce(map, 'idle', function(){
		var input = (document.getElementById("search"));
		
		var autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo('bounds', map);
	});
	
	/*address search on map.*/
	var DivAddress = document.createElement('div');
	DivAddress.innerHTML = '<div style="margin-top:10px;" ><input type="text" name="search" id="search" placeholder="Address Please.." style="height:22px;width:200px;"></div>';
	DivAddress.index = 1;
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(DivAddress);
		
	google.maps.event.addListenerOnce(map, 'idle', function(){
		var input = (document.getElementById("search"));
		
		var autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo('bounds', map);
		
		google.maps.event.addListener(autocomplete, 'place_changed', function() {
			var place = autocomplete.getPlace();
			
			//console.log(place.geometry.location);
			if (place.geometry.viewport) {
			  map.fitBounds(place.geometry.viewport);
			  //map.setZoom(10);
			} else {
			  map.setCenter(place.geometry.location);
			  //map.setZoom(10);  
			}
			
			setTimeout(function(){
				getResults();
			},200);
			
		});
	});

	getResults();
	setTimeout(function(){ shareLocation(); },600);
	
}

function shareLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        alert('Geolocation is not supported by this browser !!');
    }
}

function showPosition(position) {
	var getLocation = new google.maps.LatLng(parseFloat(position.coords.latitude), parseFloat(position.coords.longitude));
	
	map.setZoom(12);
	map.setCenter(getLocation);
	
	getResults();
}

function getResults(){
	bounds = new google.maps.LatLngBounds();
	clearMarkers();
	var center = map.getCenter();
	
	var request = {
			location: center,
			radius: 100*1000,//10000,
			keyword: 'golf course'
	};
		
	
	placeservice.radarSearch(request, callbackRadar);
}

function callbackRadar(results, status, pagination){ 
	if (status == google.maps.places.PlacesServiceStatus.OK) { 
		
		for (var i = 0; i < results.length; i++) {
			createMarker(results[i]);
		}
		map.fitBounds(bounds);
		
		setTimeout(function(){ map.setZoom(4);},300);
	}else{
		console.log(status);
	}
}

function createMarker(place) {
	var placeLoc = place.geometry.location;
    var marker = new google.maps.Marker({
      map: map,
      position: place.geometry.location,
      reference: place.reference
    });
	bounds.extend(place.geometry.location);
	markersArr.push(marker);

    google.maps.event.addListener(marker, 'click', function() {
		var getPlaceRef = this.reference;
		var that = this;
		var request = { reference: getPlaceRef };
		placeservice.getDetails(request, function(details, status) {
			console.log(details);
			var photo = typeof details.photos !== 'undefined'  ? details.photos[0].getUrl({'maxWidth': 300, 'maxHeight': 200}): '' ;
			
			var html = '<div style="font-size: 15px;">';
				if(photo != ''){
					html += 	'<div style="text-align: center;"><img src="'+photo+'"></div>';
				}
				
				html += 	'<div style="color:#4285F4;font-weight:bold;font-size:22px;">'+details.name+'</div>';
				html += 	'<div style="">'+details.formatted_address+'</div>';
				if(details.rating != undefined){
					html += 	'<div style="">Rating: '+details.rating+'</div>';
				}
				if(details.formatted_phone_number != undefined){
					html += 	'<div style="">Ph No.: '+details.formatted_phone_number+'</div>';
				}
				if(details.website != undefined){
					html += 	'<div style="">Website : <a href="'+details.website+'" target="_blank">Click Here</a></div>';
				}
				
				html += '</div>';
			
			infowindow.setContent(html);
			infowindow.open(map, that);
		});
    });
}

function clearMarkers(){
	if(markersArr.length > 0){
		for(i in markersArr){
			markersArr[i].setMap(null);
		}
		markersArr = [];
	}
}

$(document).ready(function(){
	initMap();
});
