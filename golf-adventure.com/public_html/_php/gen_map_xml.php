<?php
session_start();
$_SESSION['site_language'] = 'EN';
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php'); 
//require_once($_SERVER['DOCUMENT_ROOT'].'/_php/db.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/reviews.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');

// Create connection
$conn = mysqli_connect($server, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



/**************************************************/
/*$sql = "SHOW COLUMNS FROM golfcourses";
$sql = "select * FROM golfcourses";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
	echo '<pre>'; print_r($row);die;
	//echo '<br/>'. $row['Field'];
}
die;*/
/***************************************************/


$center_lat = 59.329323;
$center_lng = 18.068581;
$radius = 1000;

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

$lat = $center_lat;
$lng = $center_lng;
$multiplier = 112.12; // use 69.0467669 if you want miles
$distance = $radius; 

header("Content-type: text/xml");

//Golf Courses:
$query = "SELECT 
			course_id,
			course_name,
			course_street,
			course_zip,
			course_cityid,
			course_districtid,
			course_phone_information, 
			course_description,
			course_url,
			course_lng,
			course_lat,
			course_facilities,
			course_sponsor,
			course_restaurantconn,
			course_hotelconn
		FROM 
			golfcourses 
		WHERE
			course_status = 1"; 
$result = mysqli_query($conn, $query);  
// Iterate through the rows, adding XML nodes for each
while($row = mysqli_fetch_assoc($result)) {  
	$coursecity = Common::getCity($row['course_cityid']);
	$coursedistrict = Common::getDistrict($row['course_districtid']);
	$image = Common::getImage('/_golfcourses/images/', $row['course_id']);
	if ($image != '0') {
		$image_elem = $image;
	}
	else {
		$image_elem = '';
	}
	$parent = $row['course_id'];
	$facilities_array = explode(',', $row['course_facilities']);
	$i = 1;
	foreach ($facilities_array as $facility) {
		$db->where('facility_id', $facility);
		$fac = $db->getOne('golfcourse_facilities'); 
		if ($fac['facility_prio'] == 1) {
			switch ($fac['facility_id']) {
				case 1:
					$rating = Reviews::getReviewResult($parent, $fac['facility_id']) . ' / ' . '5';
					$facility_value .= '
							<div class="facility_icon" id="facility_icon_' . $i . '" style="float:left;">
								<img src="/_icons/' . $fac['facility_image'] . '" height="30" />
								' . $rating . '
							</div>
						';
					break;
				default:
					$rating = '';
					break;
			}
			$i++;
		}
	}
	$node = $dom->createElement("marker"); 
	$id = utf8_encode($row['course_id']);
	$name = utf8_encode($row['course_name']); 
	$address = utf8_encode($row['course_street']);
	$zip = utf8_encode($row['course_zip']);
	$city = $coursecity;
	$district = $coursedistrict;
	$phone = utf8_encode($row['course_phone_information']);
	$desc = utf8_encode($row['course_description']);
	$url = utf8_encode($row['course_url']);
	$img = $image_elem;
	$lat = $row['course_lat'];
	$lng = $row['course_lng'];
	
	//Deals?
	$deals = 0;
	$query_deals = '
			SELECT 
				COUNT(*) as deals_num
			FROM
				deals
			WHERE 
				deal_owner = ' . $row['course_id'] . '
			AND 
				deal_status = 1
		';
	$result4 = mysqli_query($conn, $query_deals);
	while($row4 = mysqli_fetch_assoc($result4)) { 
		
		if ($row4['deals_num'] > 0) {
			$deals = 1; 
		}
	}
	
	$type = 'course';
	if ($row['course_hotelconn'] == 1 && $row['course_restaurantconn'] == 1) {
		$type = 'course-r-h';
	}
	elseif ($row['course_restaurantconn'] == 1 && $row['course_hotelconn'] == 0) {
		$type = 'course-r';
	}
	elseif ($row['course_hotelconn'] == 1 && $row['course_restaurantconn'] == 0) {
		$type = 'course-h';
	}

	if ($_SESSION['admin_level'] > 0) {
		$details = constant('READ_MORE');
		$login = 1;
	}
	else {
		$details = '<div>
					' . constant('MAP_NOLOGIN') . '
				</div>';
		$login = 0;
	}
	$newnode = $parnode->appendChild($node);
	$newnode->setAttribute("id", $id);
	$newnode->setAttribute("name", $name);
	$newnode->setAttribute("address", $address);
	$newnode->setAttribute("zip", $zip);
	$newnode->setAttribute("city", $city);
	$newnode->setAttribute("district", $district);
	$newnode->setAttribute("phone", $phone);
	$newnode->setAttribute("desc", $desc);
	$newnode->setAttribute("url", $url);
	$newnode->setAttribute("img", $img);
	$newnode->setAttribute("lat", $lat);
	$newnode->setAttribute("lng", $lng); 
	$newnode->setAttribute("type", $type);
	$newnode->setAttribute("facilities", $facility_value);
	$newnode->setAttribute("addons", $addons_value);
	$newnode->setAttribute("details", $details);
	$newnode->setAttribute("login", $login);
	$newnode->setAttribute("offers", $deals);
	$facility_value = '';
	$addons_value = '';
	$rate = '';
}

//beds:
$query = "SELECT 
			bed_id,
			bed_name,
			bed_street,
			bed_zip,
			bed_cityid,
			bed_phone, 
			bed_description,
			bed_url,
			bed_lng,
			bed_lat,
			bed_sponsor,
			bed_gcconn,
			bed_restaurantconn
		FROM 
			beds 
		WHERE
			bed_status = 1";
$result3 = mysqli_query($conn, $query);
// Iterate through the rows, adding XML nodes for each
while($row3 = mysqli_fetch_assoc($result3)) { 
	$bedcity = Common::getCity($row['bed_cityid']);
	$image = Common::getImage('/_beds/images/', $row3['bed_id']);
	if ($image != '0') {
		$image_elem = $image;
	}
	else {
		$image_elem = '';
	}
	
	$node = $dom->createElement("marker"); 
	$id = utf8_encode($row3['bed_id']);
	$name = utf8_encode($row3['bed_name']);
	$address = utf8_encode($row3['bed_street']);
	$zip = utf8_encode($row3['bed_zip']);
	$city = $coursecity;
	$phone = utf8_encode($row3['bed_phone']);
	$desc = utf8_encode($row3['bed_description']);
	$url = utf8_encode($row3['bed_url']);
	$img = $image_elem;
	$lat = $row3['bed_lat'];
	$lng = $row3['bed_lng'];
	
	if ($row3['bed_sponsor'] == 1) {
		$type = 'sbed';
		if ($row3['bed_gcconn'] == 1) {
			$type = 'sbed-conn';
		}
	}
	else {
		$type = 'bed';
		if ($row3['bed_restaurantconn'] == 1 && $row3['bed_gcconn'] == 1) {
			$type = 'bed-gc-r';
		}
		elseif ($row3['bed_gcconn'] == 1 && $row3['bed_restaurantconn'] == 0) {
			$type = 'bed-gc';
		}
		elseif ($row3['bed_restaurantconn'] == 1 && $row3['bed_gcconn'] == 0) {
			$type = 'bed-r';
		}
	}
	if ($_SESSION['admin_level'] > 0) {
		$details = constant('READ_MORE');
		$login = 1;
	}
	else {
		$details = '<div>
					' . constant('MAP_NOLOGIN') . '
				</div>';
		$login = 0;
	}
	$newnode = $parnode->appendChild($node);
	$newnode->setAttribute("id", $id);
	$newnode->setAttribute("name", $name);
	$newnode->setAttribute("address", $address);
	$newnode->setAttribute("zip", $zip);
	$newnode->setAttribute("city", $city);
	$newnode->setAttribute("district", null);
	$newnode->setAttribute("phone", $phone);
	$newnode->setAttribute("desc", $desc);
	$newnode->setAttribute("url", $url);
	$newnode->setAttribute("img", $img);
	$newnode->setAttribute("lat", $lat);
	$newnode->setAttribute("lng", $lng); 
	$newnode->setAttribute("type", $type);
	$newnode->setAttribute("facilities", $facility_value);
	$newnode->setAttribute("addons", $addons_value);
	$newnode->setAttribute("details", $details);
	$newnode->setAttribute("login", $login);
	$newnode->setAttribute("offers", 0);
	$facility_value = '';
	$addons_value = '';
	$rate = '';
}

//restaurants:
$query = "SELECT 
			restaurant_id,
			restaurant_name,
			restaurant_street,
			restaurant_zip,
			restaurant_cityid,
			restaurant_phone, 
			restaurant_description,
			restaurant_url,
			restaurant_lng,
			restaurant_lat,
			restaurant_gcconn,
			restaurant_hotelconn,
			restaurant_sponsor
		FROM 
			restaurants 
		WHERE
			AND restaurant_status = 1"; 
$result2 = mysqli_query($conn, $query);
// Iterate through the rows, adding XML nodes for each
if ($result2) {
while($row2 = mysqli_fetch_assoc($result2)) { 
	$restaurantcity = Common::getCity($row['restaurant_cityid']);
	$image = Common::getImage('/_restaurants/images/', $row2['restaurant_id']);
	if ($image != '0') {
		$image_elem = $image;
	}
	else {
		$image_elem = '';
	}
	
	$node = $dom->createElement("marker"); 
	$id = utf8_encode($row2['restaurant_id']);
	$name = utf8_encode($row2['restaurant_name']);
	$address = utf8_encode($row2['restaurant_street']);
	$zip = utf8_encode($row2['restaurant_zip']);
	$city = $coursecity;
	$phone = utf8_encode($row2['restaurant_phone']);
	$desc = utf8_encode($row2['restaurant_description']);
	$url = utf8_encode($row2['restaurant_url']);
	$img = $image_elem;
	$lat = $row2['restaurant_lat'];
	$lng = $row2['restaurant_lng'];
	
	if ($row2['restaurant_sponsor'] == 1) {
		$type = 'srestaurant';
		if ($row2['restaurant_gcconn'] == 1) {
			$type = 'srestaurant-conn';
		}
	}
	else {
		$type = 'restaurant';
		if ($row2['restaurant_hotelconn'] == 1 && $row2['restaurant_gcconn'] == 1) {
			$type = 'restaurant-gc-h';
		}
		elseif ($row2['restaurant_gcconn'] == 1 && $row2['restaurant_hotelconn'] == 0) {
			$type = 'restaurant-gc';
		}
		elseif ($row2['restaurant_hotelconn'] == 1 && $row2['restaurant_gcconn'] == 0) {
			$type = 'restaurant-h';
		}
	}
	if ($_SESSION['admin_level'] > 0) {
		$details = constant('READ_MORE');
		$login = 1;
	}
	else {
		$details = '<div>
					' . constant('MAP_NOLOGIN') . '
				</div>';
		$login = 0;
	}
	$newnode = $parnode->appendChild($node);
	$newnode->setAttribute("id", $id);
	$newnode->setAttribute("name", $name);
	$newnode->setAttribute("address", $address);
	$newnode->setAttribute("zip", $zip);
	$newnode->setAttribute("city", $city);
	$newnode->setAttribute("district", null);
	$newnode->setAttribute("phone", $phone);
	$newnode->setAttribute("desc", $desc);
	$newnode->setAttribute("url", $url);
	$newnode->setAttribute("img", $img);
	$newnode->setAttribute("lat", $lat);
	$newnode->setAttribute("lng", $lng); 
	$newnode->setAttribute("type", $type);
	$newnode->setAttribute("facilities", $facility_value);
	$newnode->setAttribute("addons", $addons_value);
	$newnode->setAttribute("details", $details);
	$newnode->setAttribute("login", $login);
	$newnode->setAttribute("offers", 0);
	$facility_value = '';
	$addons_value = '';
	$rate = '';
}
}

//echo $dom->saveXML();
$dom->save($_SERVER['DOCUMENT_ROOT'] . '/_xml/map_data.xml');

$log = date('Y-m-d H:i:s') . "\t" . 'File updated' . "\n";
file_put_contents ( 'logfiles/map.log' , $log , FILE_APPEND );

?>
