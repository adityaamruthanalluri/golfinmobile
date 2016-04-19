<?php
session_start();
// contains utility functions mb_stripos_all() and apply_highlight()
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/_php/local_utils.php';
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
// prevent direct access 
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// get what user typed in autocomplete input
$term = trim($_GET['term']); 
$type = $_GET['type'];
$a_json = array();
$a_json_row = array();
 
$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
$json_invalid = json_encode($a_json_invalid);
 
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
 
// SECURITY HOLE ***************************************************************
// allow space, any unicode letter and digit, underscore and dash
if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
  print $json_invalid;
  exit;
}
//print json_encode(array($lang)); exit;
switch ($type) {
	case 'app_article':
		$params = array(
				$term . '%'
			);
		$id = 'id';
		$title = array('article_title');
		$sql = '
					SELECT 
						id,
						article_title
					FROM 
						app_articles
					WHERE 
						article_title LIKE ?
					ORDER BY 
						article_title
					LIMIT 10
				';  
		break;
	case 'archive':
		$lang = $_SESSION['site_language'];
		$title = 'article_title';
		$query = explode(' ', $term);
		foreach ($query as $item) {
			$params .= '+' . $item . '* ';
		}
		$params = array(
				substr($params, 0, -1)
			);
		$sql = '
					SELECT DISTINCT
					SQL_CALC_FOUND_ROWS
					article_url,
					article_title,
					article_menu_title,
					article_summary,
					article_body,
					article_created,
					article_changed
				FROM 
					articles
				WHERE 
					MATCH(article_title, article_menu_title, article_summary, article_body) AGAINST(? IN BOOLEAN MODE )
					AND article_body != ""
					AND article_lang = "' . $lang . '"
				ORDER BY article_rank DESC, article_created DESC
				LIMIT 10
				'; 
		break;
	case 'article':
		$lang = $_SESSION['conn_rel_lang'];
		$params = array(
				$term . '%',
				$term . '%'
			);
		$id = 'article_id';
		$title = array('article_title');
		$sql = '
					SELECT 
						article_id,
						article_title
					FROM 
						articles
					WHERE 
						(article_title LIKE ?
					OR 
						article_menu_title LIKE ?)
					AND 
						article_lang = "' . $lang . '"
					AND 
						article_lft > 1
					LIMIT 10
				'; 
		break;
	case 'bed':
		$params = array(
				$term . '%'
			);
		$id = 'bed_id';
		$title = array('bed_name');
		$sql = '
					SELECT 
						bed_id,
						bed_name
					FROM 
						beds
					WHERE 
						bed_name LIKE ?
					ORDER BY 
						bed_name
					LIMIT 10
				';  
		break;
	case 'category':
		$lang = $_SESSION['conn_rel_lang'];
		$params = array(
				$term . '%'
			);
		$id = 'category_id';
		$title = array('category_title');
		$sql = '
					SELECT 
						category_id,
						category_title
					FROM 
						categories
					WHERE 
						category_title LIKE ?
					AND 
						category_lang = "' . $lang . '"
					AND 
						category_lft > 1
					LIMIT 10
				';  
		break;
	case 'deal':
		$params = array(
				$term . '%'
			);
		$id = 'deal_id';
		$title = array('deal_title');
		$sql = '
					SELECT 
						deal_id,
						deal_title
					FROM 
						deals
					WHERE 
						deal_title LIKE ?
					ORDER BY 
						deal_title
					LIMIT 10
				';  
		break;
	case 'deal_suppliers':
		$params = array(
				$term . '%'
			);
		$id = 'supplier_id';
		$title = array('supplier_name');
		$sql = '
					SELECT 
						supplier_id,
						supplier_name
					FROM 
						deal_suppliers
					WHERE 
						supplier_name LIKE ?
					ORDER BY 
						supplier_name
					LIMIT 10
				';  
		break;
	case 'event':
		$params = array(
				$term . '%'
			);
		$id = 'event_id';
		$title = array('event_title');
		$sql = '
					SELECT 
						event_id,
						event_title
					FROM 
						app_events
					WHERE 
						event_title LIKE ?
					ORDER BY 
						event_title
					LIMIT 10
				';  
		break;
	case 'golfclubs':
		$params = array(
				$term . '%'
			);
		$title = array('club_name');
		$id = 'club_id';
		$sql = '
				SELECT 
					club_id,
					club_name
				FROM
					golfclubs 
				WHERE 
					club_name LIKE ?
				ORDER BY 
					club_name
				LIMIT 10
			';
		break;
	case 'golfcourses':
		$params = array(
				$term . '%'
			);
		$title = array('course_name');
		$id = 'course_id';
		$sql = '
				SELECT 
					course_id,
					course_name
				FROM
					golfcourses 
				WHERE 
					course_name LIKE ?
				ORDER BY 
					course_name
				LIMIT 10
			';
		break;
		
		
	case 'golfcourse_admins':
		$params = array(
				$term . '%',
				$term . '%'
			);
		$title = array('user_first_name', 'user_last_name');
		$id = 'user_id';
		$sql = '
				SELECT 
						user_id,
						user_first_name,
						user_last_name,
						user_account_type
					FROM 
						users
					WHERE 
						user_account_type > 1
					AND
						(user_first_name LIKE ?
					OR 
						user_last_name LIKE ?)
					ORDER BY 
						user_last_name
					LIMIT 10
			';
		break;

	case 'offer':
		$params = array(
				$term . '%'
			);
		$id = 'offer_id';
		$title = array('offer_title');
		$sql = '
					SELECT 
						offer_id,
						offer_title
					FROM 
						offers
					WHERE 
						offer_title LIKE ?
					AND 
						offer_id > 0
					ORDER BY 
						offer_title
					LIMIT 10
				';
		break;
		
	case 'restaurants':
		$params = array(
				$term . '%'
			);
		$title = array('restaurant_name');
		$id = 'restaurant_id';
		$sql = '
				SELECT 
					restaurant_id,
					restaurant_name
				FROM
					restaurants 
				WHERE 
					restaurant_name LIKE ?
				ORDER BY 
					restaurant_name
				LIMIT 10
			';
		break;
		
	case 'company_owner':
		$params = array(
				$term . '%',
				$term . '%'
			);
		$id = 'user_id';
		$title = array('user_first_name', 'user_last_name', 'user_city');
		$sql = '
				SELECT 
					user_id,
					user_first_name,
					user_last_name,
					user_city
				FROM 
					users 
				WHERE 
					user_first_name LIKE ?
				OR 
					user_last_name LIKE ?
				LIMIT 10
			';
		break;
		
	case 'user':
		$params = array(
				$term . '%',
				$term . '%'
			);
		$id = 'user_id';
		$title = array('user_last_name', 'user_first_name');
		$sql = '
					SELECT 
						user_id,
						user_first_name,
						user_last_name
					FROM 
						users
					WHERE 
						user_first_name LIKE ?
					OR 
						user_last_name LIKE ?
					ORDER BY 
						user_last_name
					LIMIT 10
				';
		break;
		
}
$results = $db->rawQuery($sql, $params);
foreach ($results as $row) {
	$result = '';
	for ($i=0;$i<count($title);$i++) {
		$result .= $row[$title[$i]] . ' ';
	}
	if ($type == 'archive') {
		$return_arr[] = $row['article_title'];
	}
	else {
		$return_arr[] = $row[$id] . ' : ' . $result;
	}
}
echo json_encode($return_arr);



?>