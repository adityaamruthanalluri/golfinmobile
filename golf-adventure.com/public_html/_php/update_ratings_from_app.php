<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

$app_ratings = $db->get('app_ratings');

$i = 0;
$n = 0;

foreach ($app_ratings as $rate) { 
	$id = $rate['iID'];
	if ($rate['iGolfClub'] == 0) {
		$db->where('iID', $id);
		$db->delete('app_ratings');
		continue;
	}
	$user = $rate['iUserID'];	
	$data = array(
			'review_user' => $rate['iUserID'],
			'review_source' => 2,
			'review_date' => date('Y-m-d h:i'),
			'review_course' => $rate['iGolfClub']
		);

	//1. Golfcourse rating
	$data['review_type'] = 1;
	$data['review_points'] = $rate['iRating_GolfCourt'];
	$db->where('review_type', 1);
	$db->where('review_source', 2);
	$db->where('review_course', $rate['iGolfClub']);
	$db->where('review_user', $user);
	$old = $db->getOne('course_reviews');
	if ($old) {
		$n++;
		$db->where('review_id', $old['review_id']);
		$db->delete('course_reviews');
	}
	if ($rate['iRating_GolfCourt'] != 0) {
		$i++;
		$result = $db->insert('course_reviews', $data);
	}
	
	//2. Restaurant rating
	$data['review_type'] = 2;
	$data['review_points'] = $rate['iRating_Food'];
	$db->where('review_type', 2);
	$db->where('review_source', 2);
	$db->where('review_course', $rate['iGolfClub']);
	$db->where('review_user', $user);
	$old = $db->getOne('course_reviews');
	if ($old) {
		$n++;
		$db->where('review_id', $old['review_id']);
		$db->delete('course_reviews');
	}
	if ($rate['iRating_Food'] != 0) {
		$i++;
		$result = $db->insert('course_reviews', $data);
	}
	
	//3. Accomodation rating
	$data['review_type'] = 3;
	$data['review_points'] = $rate['iRating_Housing'];
	$db->where('review_type', 3);
	$db->where('review_source', 2);
	$db->where('review_course', $rate['iGolfClub']);
	$db->where('review_user', $user);
	$old = $db->getOne('course_reviews');
	if ($old) {
		$n++;
		$db->where('review_id', $old['review_id']);
		$db->delete('course_reviews');
	}
	if ($rate['iRating_Housing'] != 0) {
		$i++;
		$result = $db->insert('course_reviews', $data);
	}
	
	// Delete post from app_ratings
	$db->where('iID', $id);
	$db->delete('app_ratings');
}
$file_content = date('Y-m-d H:i')  . "\t" . $i . ' inserted'  . "\t" . $n . ' removed' . "\r\n";
file_put_contents ( 'logfiles/reviews.log', $file_content , FILE_APPEND );

?>