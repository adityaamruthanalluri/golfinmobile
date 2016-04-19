<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

$url = 'https://golfapp.azurewebsites.net/golfservice.svc/getgolfcourses/full/2015/';
		
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data=json_decode($response);
foreach ($data as $courses) {
	foreach ($courses as $course) {
		$course_id = $course->mySql_club_id;
		$course_rating = $course->Ratings_Overall;
		$rating = array('course_overall_rate' => $course_rating);
		$db->where('course_id', $course_id);
		$result = $db->update('golfcourses', $rating);
		if (!$result) {
			$log = date('Y-m-d H:i:s') . "\t" . 'Rate Import' . "\t" . 'course_id: ' . $course_id . "\t" . 'course_rate: ' . $course_rating;
			file_put_contents ( 'logfiles/rate_import.log' , $log , FILE_APPEND ) . "\n";	
		}
	}
}


?>