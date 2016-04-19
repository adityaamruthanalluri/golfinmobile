<?php
/*
getReviewResult($parent, $type)
reviewForm($parent)
getTopReviews();
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/SV.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'insert_review':
			$types = $db->get('golfcourse_facilities');
			foreach ($types as $type) {
				$type_title = str_replace(' ', '', $type['facility_title']) . '_marker';
				if ($type_title == $_POST['type']) {
					$type_id = $type['facility_id'];
				}
			}
			session_start();
			$data = array(
					'review_course' => $_POST['id'],
					'review_points' => $_POST['point'],
					'review_type' => $type_id,
					'review_user' => $_SESSION['userid'],
					'review_source' => 1,
					'review_date' => date('Y-m-d H:i:s')
				);
			$db->where('review_user', $_SESSION['userid']);
			$db->where('review_source', 1);
			$db->where('review_type', $type_id);
			$db->where('review_course', $_POST['id']);
			$old_entry = $db->getOne('course_reviews'); 
			if ($old_entry) { 
				$file_content = $old_entry['review_id'] . "\t" . $old_entry['review_course'] . "\t" . $old_entry['review_points'] . "\t" . $old_entry['review_type'] . "\t" . $old_entry['review_user']  . "\t" . $old_entry['review_source'] . "\t" . date('Y-m-d H:i:s') . "\n"; 
				file_put_contents ( 'logfiles/old_reviews.log', $file_content , FILE_APPEND );
				$db->where('review_id', $old_entry['review_id']);
				$result = $db->delete('course_reviews');
			}
			$result = $db->insert('course_reviews', $data); 
			if ($result) {
				$points = Reviews::getReviewResult($_POST['id'], $type_id);
				echo $points;
			}
			else {
				echo 0;
			}
			die();
			break;
	}
}

class Reviews {

	public static function getReviewResult($parent, $type) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$sql = '
				SELECT 
					count(review_id) AS total,
					SUM(review_points) AS points
				FROM 
					course_reviews 
				WHERE 
					review_course = ' . $parent . '
				AND review_type = ' . $type; 
		$result = $db->rawQuery($sql);
		if ($result[0]['points'] > 0) {
			$rev_average = $result[0]['points'] / $result[0]['total'];
		}
		else {
			$rev_average = 0;
		}
		return number_format($rev_average, 1);
	}
	
	public static function reviewForm($parent) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('course_id', $parent);		
		$golfcourse = $db->getOne('golfcourses');
		$gc_name = $golfcourse['course_name'];
		$facs = explode(',', $golfcourse['course_facilities']);
		foreach ($facs as $key => $value) {
			$db->where('facility_id', $value);
			$db->where('facility_reviewable', 1);
			$result = $db->getOne('golfcourse_facilities');
			if ($result) {
				$review_items[] = $result;
			}
		}
		//$review_items contains reviewable facilities
		$html = '
				<div id="review_form_wrapper" class="popup review_form_wrapper">
					<div id="review_form_content" class="' . $parent . '">
						<div class="popup_close">x</div>
			';
		foreach ($review_items as $item) {
			$html .= '
						<div id="review_' . str_replace(' ', '', $item['facility_title']) . ' class="review_item">
							<div class="review_title">
								' . constant('RATE')
				;
			if ($_SESSION['site_langiage'] == 'SV') {
				$html .= ' ' . constant('ON');
			}
			$html .= ' ' . $gc_name . '
							</div>
							<div class="review_points review_form_background">
				';
			for ($i=0;$i<5;$i++) {
				$html .= '
								<div id="' . str_replace(' ', '', $item['facility_title']) . '_review_point_' . ($i+1) . '" class="review_point">
									<img src="/_icons/review_point.png" height="20" id="' . str_replace(' ', '', $item['facility_title']) . '_image_point_' . ($i+1) . '" class="review_point_img" />
								</div>
					';
			}			
			$html .= '
								<input type="hidden" id="' . str_replace(' ', '', $item['facility_title']) . '_marker" class="point_marker" value="" />
							</div>
							<div class="review_form_layer"></div>
							<div class="clear_both"></div>
						</div>
						
			';
		}				
		$html .= '
						
						<div id="review_submit" class="submit_button" data-revid="' . $parent . '">' . strtoupper(constant('SEND')) . '</div>
					</div>
				</div>
				
			';
		return $html;
	}
	
	public static function getTopReviews() {
		//echo '<br><br><br><br>';
		
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		//Get highest id
		$db->orderBy('course_id', 'DESC');
		$top_id = $db->getOne('golfcourses', array('course_id'));
		$top_id = $top_id['course_id'];
		//Get reviews
		$cols = array(
				'sum(review_points) AS points',
				'review_course'
			);
				
		$db->where('review_course <= ' . $top_id);
		$db->where('review_type', 1);
		$db->orderBy('review_points');
		$db->groupBy('review_course');
		$result = $db->get('course_reviews', null, $cols);
		//Get result
		foreach ($result as $key => $value) {
			$sql = '
					SELECT COUNT(`review_course`) as votes
					FROM `course_reviews` 
					WHERE `review_course` = ' . $value['review_course'] . '
					AND review_type = 1	
				';
			$votes = $db->rawQuery($sql); 
			$db->where('course_id', $value['review_course']);
			$course = $db->getOne('golfcourses', array('course_name', 'course_description', 'course_sponsor'));
			$db->where('offer_account', 2);
			$db->where('offer_owner', $value['review_course']);
			$offer = $db->get('offers');
			//echo count($offer) . '<br><br>';
			if ($course['course_name'] != '') {
				$review['course_id'] = $value['review_course'];
				$review['course_name'] = $course['course_name'];
				$review['course_description'] = $course['course_description'];
				$review['course_sponsor'] = $course['course_sponsor'];
				if (count($offer) > 0) {
					$review['offers'] = 1;
				}
				else {
					$review['offers'] = 0;
				}
				$review['votes'] = $votes[0]['votes'];
				$review['sum'] = number_format(($value['points'] / $votes[0]['votes']), 1, '.', ' ');
				$reviews[] = $review;
			}
		}
		foreach ($reviews as $key => $row) {
	    	$sum[$key] = $row['sum'];
		}
		array_multisort($sum, SORT_DESC, $reviews); 
		return ($reviews);
	}

}


?>