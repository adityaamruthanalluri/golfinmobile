<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/db.management.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');

// Create connection
$conn = mysqli_connect($server, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

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

$query = "SELECT 
			club_id,
			club_name,
			club_street,
			club_zip,
			club_city, 
			club_lng,
			club_lat,
			(SQRT(POW((club_lat - " . $lat . "), 2) + POW((club_lng - " . $lng . "), 2)) * " . $multiplier . ") AS distance 
		FROM 
			golfclubs 
		WHERE
			POW((club_lat - $lat), 2) + POW((club_lng - $lng), 2) < POW((" . ($distance / $multiplier) . "), 2) 
			AND club_sponsor = 1
		ORDER BY 
			distance ASC"; 

$result = mysqli_query($conn, $query);

//print_r($result);

//header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while($row = mysqli_fetch_assoc($result)) { 
  $node = $dom->createElement("marker"); 
  $name = utf8_encode($row['club_name']);
  $address = utf8_encode($row['club_street']);
  $zip = utf8_encode($row['club_zip']);
  $city = utf8_encode($row['club_city']);
  $lat = $row['club_lat'];
  $lng = $row['club_lng'];
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("name", $name);
  //$newnode->setAttribute("address", $address);
  //$newnode->setAttribute("zip", $zip);
  //$newnode->setAttribute("city", $city);
  $newnode->setAttribute("lat", $lat);
  $newnode->setAttribute("lng", $lng); 
  $newnode->setAttribute("type", 'club');
}
echo htmlentities($dom->saveXML());
?>