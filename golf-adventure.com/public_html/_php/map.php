<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');

class Map {

	public static function displayMap() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$html = '
				<div class="googlemap">
					<div id="map_title">
						<h1>' . constant('FIND_YOUR_GOLFCOURSE') . '</h1>
						<p>' . constant('MAP_INSTRUCTIONS') . '</p>
					</div>
			';
		$html .= '
					<div id="map_canvas"></div>
				</div>
			'; 
		
		return $html;
	}
	
	public static function displayMapA() {
		$html .= '
					<div id="map_canvas"></div>
			'; 
			return $html;
	}
	
	public static function displayMapB() {
		$html .= '
				<div id="map"></div>
							'; 
			return $html;
	}

	public static function getGPScoordinats($address) {
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = json_decode(curl_exec($ch), true);
//		echo ($response['results'][0]['geometry']['location']['lat'] . '<br>');


		if ($response['status'] != 'OK') {
			return false;
		} else {
	    	$gps['lat'] = $response['results'][0]['geometry']['location']['lat'];
	    	$gps['lng'] = $response['results'][0]['geometry']['location']['lng'];
		}
		return $gps;
	}	

}
?>