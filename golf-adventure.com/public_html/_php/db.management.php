<?php 
/*
executeInsert($data, $values)
executeNestedInsert($data, $values)
executeUpdate($data, $values)
executeDelete($data)
executeNestedDelete($data)
getPrefix($table)
*/

session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/accounts.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/tags.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/map.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/categories.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/mail.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/users.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/content_import.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');

include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');

$db = new MysqliDb($server, $user, $password, $database); 
$settings = Settings::getSettings();

//For downloading images to table-specific folders and name after id
$app_file_tables = array(
		'bazaar',
		'golfclubs',
		'golfcourses',
		'app_events',
		'app_articles',
		'restaurants',
		'beds',
		'deals',
		'deal_suppliers',
		'offers',
		'users',
		'rewards',
		'shops'
	);
	
$xml_update_tables = array(
		'beds',
		'golfcourses',
		'offers',
		'restaurants'
	);

//Password recovery
if (isset($_POST['do_password']) && $_POST['do_password']==1) {
	$result = Users::executePasswordRecovery($_POST['email']);
	header('Location: /change-password');
}

//Form calls
if (isset($_SESSION) && isset($_POST['data'])) { 
	session_start();
	$data = ($_SESSION[$_POST['data']]); 
	foreach ($_POST as $key => $value) {
		if ($key != 'data') {
			if (strpos($key, '_image')) {
				$server = str_replace('www.', '', $_SERVER['SERVER_NAME']); 
				$value = str_replace('www.', '', $value);
				$value = str_replace('http://' . $server, '', $value);
			}
			if (strpos($key, '_lng') || strpos($key, '_lat')) {
				$value = str_replace(',', '.', $value);
			}
			$values[$key] = $value;
		}
	} 
	//Categories:
	if (isset($data['categories'])) {
		$categories = $values[$data['table_prefix'] . 'categories'];
		unset($values[$data['table_prefix'] . 'categories']);
	}
	//Domain-handeling:
	$domain = $_SERVER['SERVER_NAME'];
	if (strpos($domain, 'www') > -1) {
		$domain = str_replace('www.', '', $domain);
	} 
	//Offers:
	if (array_key_exists('offer_image', $values)) {
		$values['offer_image'] = str_replace($domain, '', $values['offer_image']);
		$values['offer_image'] = str_replace('http://', '', $values['offer_image']);
	}
	//Deals
	if ($data['table'] == 'deals') {
		switch ($values['deal_limiter']) {
			case '1':
				$deal_startdate = $values['deal_start'];
				$deal_enddate = $values['deal_end'];
				break;
			case '2':
				$deal_buysnum = $values['deal_buys'];
				break;
		}
		unset($values['deal_start']);
		unset($values['deal_end']);
		unset($values['deal_buys']);
		unset($values['date']);
		unset($values['time']);
	}
	//Additional fields:
	if (is_array($data['additional_fields'])) {
		foreach ($data['additional_fields'] as $key => $value) {
			$values[$key] = $value;
		}
	} 
	//Tags:
	if (isset($values[$data['table_prefix'] . 'tags'])) {
		$tags = explode(',', $values[$data['table_prefix'] . 'tags']);
		unset($values[$data['table_prefix'] . 'tags']);
	}
	
	//Courses:
	if ($data['table'] == 'golfcourses') {
		$values['course_facilities'] = implode(',', $values['course_facilities']);
		$db->where('course_id', $data['post_id']);
		$db->delete('golfcourse_type_rel');
		foreach ($values['course_type'] as $type) {
			$cols = array(
				'course_id' => $data['post_id'],
				'type_id' => $type 
			);
			$db->insert('golfcourse_type_rel', $cols);
			unset($values['course_type']);
		}
	}
	
	//Articles:
	if ($data['table'] == 'articles') {
		$values['article_lang'] = $_SESSION['site_language'];
		unset($values['post_id']);		
	}
	
	switch ($data['db-action']) { 
		case 'insert':  
			//Courses:
			if ($data['table'] == 'golfcourses') {
				foreach ($values['course_type'] as $type) {
					$cols = array(
							'course_id' => $value['course_owner'],
							'type_id' => $type 
						);
					$db->insert('golfcourse_type_rel', $cols);
					unset($values['course_type']);
				}
			}
			
			//Users
			if ($data['table'] == 'users') {
				if (isset($values['user_password'])) {				
					$user_pass = $values['user_password'];
					$values['user_password'] = Accounts::encryptPassword($values['user_password']);
					$log = date('Y-m-d H:i:s') . "\t" . 'User added ' . $_POST['email'] . "\t" . 'Password: ' . $values['user_password'] . "\t" . 'Enc_pw: ' . $crypted_pw . "\n";
					file_put_contents ( 'logfiles/login.log' , $log , FILE_APPEND );
					$_SESSION['new_user_email'] = $values['user_email']; 
				}
			}
			
			//Newsletter
			if ($data['table'] == 'newsletters') {
				$nl_categories = $values['newsletter_nl_cats'];
				unset($values['newsletter_nl_cats']);
			}
			
			//Offers:
			if ($data['table'] == 'offers') {
				$values['offer_publ_from'] = date('Y-m-d');
				if ($values['offer_lastminute'] == 'on') {
					$pto = strtotime('+ 3 days');
				}
				else {
					$pto = strtotime('+ 30 days');
				}
				$values['offer_publ_to'] = date('Y-m-d', $pto);
			}
			
			//Articles for app
			if ($data['table'] == 'app_articles') {
				$values['article_owner'] = $_SESSION['userid']; 
				unset($values['article_image']);
			}
			
			//Images for app articles
			if (in_array($data['table'], $app_file_tables)) { 
				if ($_FILES['filesToUpload']['name'] != '') { 
					$image = $_FILES['filesToUpload'];
					if (!move_uploaded_file($_FILES["filesToUpload"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"])) {
	        			die( "Sorry, there was an error uploading your file to ".$_SERVER['DOCUMENT_ROOT'].'_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"] );
	    			}
				}
			} 
			$result = Database_management::executeInsert($data, $values, $xml_update_tables); 
			if ($result > 0) {
				if ($_FILES['filesToUpload']['name'] != '') {
					if (in_array($data['table'], $app_file_tables)) {
						if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"])) {
							$ext = substr($_FILES["filesToUpload"]["name"],(strrpos($_FILES["filesToUpload"]["name"], '.')+1));
							rename($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"],$_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$result.'.' . $ext); 
							if ($data['table'] == 'offers') {
								$offer_image = '/_' . $data['table'] . '/images/'.$result.'.' . $ext;
								$offer_update = array('offer_image' => $offer_image);
								echo $offer_image . ' ' . $result;
								$db->where('offer_id', $result); 
								if (!$db->update('offers', $offer_update)) { 
									die('Problem adding image');	
								}
							}
						}
					}
				}
				unset($values['filesToUpload']);
				if ($data['table'] == 'deals') {  
					switch ($values['deal_limiter']) {
						case '1': 
							$ddata = array(
									'start' => $deal_startdate,
									'end' => $deal_enddate,
									'deal_id' => $result
								);
							$db->insert('deal_time', $ddata);
							break;
						case '2':
							$ddata = array(
									'deal_buys' => $deal_buysnum,
									'deal_id' => $result
								);
							$db->insert('deal_buys', $ddata);
							break;
					}
				}
				if ($data['table'] == 'app_articles') {
					if ($_SESSION['admin_level'] < 201) {
						$db->where('club_gkadministrator', $values['article_owner']);
						$source = $db->getOne('golfclubs');
						$source = $source['club_name'];
					}
					else {
						$source = 'GolfinMobile';
					}
					$indata = array('article_id' => $result, 'article_source' => $source);
					$db->where('id', $result);
					$db->update($data['table'], $indata);
				}
				if ($data['table'] == 'companies') {
					$cols = array(
						'cu_company_id' => $result,
						'cu_user_id' => $_SESSION['userid'],
						'cu_admin' => 1
					);
					$result = $db->insert('company_users', $cols);
					$db->where('user_id', $_SESSION['userid']);
					$level = $db->getOne('users');
					if ($level['user_account_type'] == 1) { 
						$data = array('user_account_type' => 2);
						$db->where('user_id', $_SESSION['userid']);
						$db->update('users', $data);
					}
				}
				if ($settings['setting_send_after_insert']==1 && $data['table']=='users') { 
					$db->where('account_id', $values['user_account_type']);
					$account = $db->getOne('accounts');
					$account = $account['account_type'];
					$maildata = array(
							'subject' => constant('WELCOME') . ' ' . constant('TO') . ' ' . $settings['website_title'],
							'from' => $settings['setting_default_email'],
							'to' => $values['user_email'],
							'template' => 'new_user',
							'message' =>  constant('HELLO') . ' ' . $values['user_first_name'] . '!<br /><br />' . constant('YOU_HAVE_A_NEW_ACCOUNT') . ' ' . constant(strtoupper($account)) . ' ' . constant('IN_OUR_SYSTEM') . ' ',
							'user_name' => $values['user_email'],
							'password' => $user_pass
						);
					$mailsent = MailManagement::sendMail($maildata); 
					if (!$mailsent) {
						die('Mail error');
					}
				}
				if ($data['table']=='feedback_entries') {
					$maildata = array(
							'subject' => 'New Feedback Entry',
							'from' => $values['fe_sender_mail'],
							'to' => $settings['setting_default_email'],
							'template' => 'message',
							'message' =>  $values['fe_sender_text']
						);
					$mailsent = MailManagement::sendMail($maildata); 
					if (!$mailsent) {
						die('Mail error');
					}
				}
				if (isset($categories)) {
					Categories::updateCategoryRel($categories, $data['table_prefix'], $result);
				}
				if (isset($tags_list)) {
					Tags::insertTags($tags, $data['table_prefix'], $result);
				} 
				header('Location: ' . $data['return_uri']);
			}
			break;
		case 'nested_insert': 
			$result = Database_management::executeNestedInsert($data, $values);
			if ($result > 0) {
				if (isset($categories)) {
					Categories::updateCategoryRel($categories, $data['table_prefix'], $result);
				}
				if (isset($tags)) {
					Tags::insertTags($tags, $data['table_prefix'], $result);
				} 
				if (isset($data['search_fields'])) {
					foreach ($data['search_fields'] as $key => $value) {
						$search[$key] = $values[$value];
					}
					unset($data['additional_fields']);
				}
			}
			else {
				die('Nested not inserted ' . $data['return_uri']);
			}
			header('Location: ' . $data['return_uri']);
			break;
		case 'update': 
			if ($data['table'] == 'app_articles') {
				if (strpos($values['article_image'], '_files')) {
					$url = 'http://' . $_SERVER['SERVER_NAME'].$values['article_image'];
					$result = contentImport::saveImageToDisc($url);
					$values['article_image'] = $result;
				}
			}
			if ($data['table'] == 'companies') {
				if($_SESSION['admin_level'] > 300) {
					$company_owner = explode(':', $values['company_owner']);
					$company_owner = (int)$company_owner[0];
					$cols = array(
							'cu_company_id' => $data['post_id'],
							'cu_user_id' => $company_owner,
							'cu_admin' => 1
						);
					unset($values['company_owner']);
				}
			}
			if (in_array($data['table'], $app_file_tables)) { 
				if ($_FILES['filesToUpload']['name'] != '') {
					//Remove previously uploaded image, the extensions can be different!
					$old_img = Common::getImage('/_' . $data['table'] . '/images/', $data['post_id']);
					if ($old_img != '0') {
						unlink($_SERVER['DOCUMENT_ROOT'].$old_img);
					}
					//Handle new image
					$image = $_FILES['filesToUpload'];
					if (!move_uploaded_file($_FILES["filesToUpload"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"])) {
	        			die("Sorry, there was an error uploading your file.");
	    			}
	    			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"])) {
						$ext = substr($_FILES["filesToUpload"]["name"],(strrpos($_FILES["filesToUpload"]["name"], '.')+1));
						rename($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"],$_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$data['post_id'].'.' . $ext); 
					}
				}
			}
			$result = Database_management::executeUpdate($data, $values, $xml_update_tables);  
			if ($result) {
				if ($data['table'] == 'companies') {
					if ($_SESSION['admin_level'] > 300) {
						$db->where('cu_admin', 1);
						$db->where('cu_company_id', $data['post_id']);
						$db->update('company_users', array('cu_admin' => 0));
						$db->where('cu_user_id', $company_owner);
						$db->where('cu_company_id', $data['post_id']);
						$result = $db->getOne('company_users');
						if (count($result) > 0) {
							$db->where('cu_user_id', $company_owner);
							$db->where('cu_company_id', $data['post_id']);
							$db->update('company_users', array('cu_admin' => 1));
						}
						else {
							$db->insert('company_users', $cols);
						}
					}
				}
				if (isset($categories)) {
					Categories::updateCategoryRel($categories, $data['table_prefix'], $data['post_id']);
				}
				if (isset($tags_list)) {
					Tags::updateTags($tags, $data['table_prefix'], $data['post_id']);
				} 
				if ($data['table'] == 'deals') {
					switch ($values['deal_limiter']) {
						case 1:
							$time = array(
									'start' => $deal_startdate,
									'end' => $deal_enddate
								);
							$db->where('deal_id', $data['post_id']);
							$db->update('deal_time', $time);
							break;
						case 2:
							$buys = array(
									'deal_buys' => $deal_buysnum
								);
							$db->where('deal_id', $data['post_id']);
							$db->update('deal_buys', $buys);
							break;
					}
				}
				header('Location: ' . $data['return_uri']);
			}
			break;
		case 'nested_update': 
			$result = Database_management::executeUpdate($data, $values);
			if ($result) {
				if (isset($categories)) {
					Categories::updateCategoryRel($categories, $data['table_prefix'], $data['post_id']);
				}
				if (isset($tags)) {
					Tags::updateTags($tags, $data['table_prefix'], $data['post_id']);
				}
			}
			else {
				die($data['table'] . ' not updated');
			} 
			header('Location: ' . $data['return_uri']);
			break;
		case 'registration': 
			$account_type = $values['user_account_type'];
			switch ($values['user_account_type']) {
				case 1:
					$data['table'] = 'users';
					$user_name = $values['user_first_name'] . ' ' . $values['user_last_name'];
					$mail_to = $values['user_email'];
					break;
				case 2: 
					$data['table'] = 'golfcourses';
					$values['course_name'] = $values['user_last_name'];
					unset($values['user_first_name']);
					unset($values['user_last_name']);
					$values['course_email_primary'] = $values['user_email'];
					$mail_to = $values['user_email'];
					unset($values['user_email']);
					$values['course_reward'] = $values['user_reward'];
					unset($values['user_reward']);
					unset($values['user_account_type']);
					$user_name = $values['course_name']; 
					break;
				case 3:
					$data['table'] = 'shops';
					$values['shop_name'] = $values['user_last_name'];
					unset($values['user_first_name']);
					unset($values['user_last_name']);
					$values['shop_email'] = $values['user_email'];
					$mail_to = $values['user_email'];
					unset($values['user_email']);
					$values['shop_reward'] = $values['user_reward'];
					unset($values['user_reward']);
					unset($values['user_account_type']);
					$user_name = $values['shop_name'];
					break;
			}
			$result = Database_management::executeInsert($data, $values); 
			if ($result > 0) { 
				if (isset($data['company_id'])) {
					$comp = array(
							'cu_company_id' => $data['company_id'],
							'cu_user_id' => $result,
							'cu_admin' => 0
						);
					$codata = array(
							'table' => 'company_users'
						);
					$result = Database_management::executeInsert($codata, $comp); 
				}
				$ver_code = Users::getVerificationCode($result, $mail_to, $account_type);
				$maildata = array(
						'subject' => constant('WELCOME') . ' ' . constant('TO') . ' ' . $settings['website_title'],
						'from' => $settings['setting_default_email'],
						'to' => $mail_to,
						'template' => 'new_user_registration',
						'user_name' => $user_name,
						'vercode' => $ver_code
					);
				$mailsent = MailManagement::sendMail($maildata); 
				if (!$mailsent) {
					die('Mail error');
				}
			}
			header('Location: ' . $data['return_uri']);
			break;
		case 'update_password': 
			$password_enc =  Accounts::encryptPassword($values['password']);
			switch ($data['table']) {
				case 'users': 
					$pwd['user_password'] = $password_enc;
					$pwd['user_verified'] = 1;
					break;
				case 'golfcourses': 
					$pwd['course_password'] = $password_enc;
					$pwd['course_verified'] = 1;
					break;
				case 'shops': 
					$pwd['shop_password'] = $password_enc;
					$pwd['shop_verified'] = 1;
					break;
			}
			unset($values['password_confirm']);
			$db->where($data['identifier'], $data['post_id']);
			$db->update($data['table'],$pwd);
			header('Location: ' . $data['return_uri']);
			break;
	}
}
if (isset($_GET['data'])) { 
	session_start();
	$data = ($_SESSION[$_GET['data']]); 
	$prefix = Database_management::getPrefix($data['table']);
	switch ($data['db_action']) {
		case 'delete':
			$result = Database_management::executeDelete($data);
			if (!$result) {
				die('Post in ' . $data['table'] . ' not deleted');
			}
			else {
				if (isset($data['categories'])) {
					Categories::deleteCategoryRel($data['table_prefix'], $data['id']);
				}
				if (isset($data['tags'])) {
					Tags::deleteTags($prefix, $data['id']);
				}
				if (in_array($data['table'], $app_file_tables)) {
					$path = $_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/';
					$image = Common::getImage($path, $data['id']);
					if ($image != 0 && file_exists($image)) {
						unlink($image);
					}
				}
			}
			if ($data['table'] == 'deals') {
				$db->where('deal_id', $data['id']);
				$db->delete('deal_buys');
				$db->where('deal_id', $data['id']);
				$db->delete('deal_time');
			}
			header('Location: ' . $data['return_uri']);
			break;
		case 'nested_delete':
			$result = Database_management::executeNestedDelete($data, $xml_update_tables); 
			if (isset($data['categories'])) {
				Categories::deleteCategoryRel($prefix, $data['id']); 
			}
			if (isset($data['tags'])) {
				Tags::deleteTags($prefix, $data['id']);
			}
			if ($result) {}
			else {
				die('Post in ' . $data['table'] . ' not deleted');
			}
			header('Location: ' . $data['return_uri']);
			break;
	}

}

//Ajax calls
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
	if (isset($_POST['ajax-action'])) {
		$action = $_POST['ajax-action'];
	}
	elseif (isset($_POST['action'])) {
		$action = $_POST['action'];
	}
	else {
		$indata = json_decode(file_get_contents("php://input"));
		$action = $indata->ajax-action;	
	}
	switch ($action) {
		case 'save_setting': 
			switch ($_POST['src']) {
				case 'setting_match_offset':
				case 'setting_search_offset':
				case 'setting_admin_offset':
					if (!is_int((int)$_POST['val']) || (int)$_POST['val'] > 100) {
						echo constant('VALUE_BELOW_100');
						die();
					}
					break;
				case 'setting_default_language':
					include($_SERVER['DOCUMENT_ROOT'].'/_config/config.php');
					if (!preg_match("#^[A-Z]+$#", $_POST['val']) || !in_array($_POST['val'], $allowed_languages) || strlen($_POST['val'])!=2) {
						echo constant('LANGUAGE_CODE_INCORRECT');
						die();
					}
					break;
			}
			$col = array($_POST['src'] => strip_tags($_POST['val']));
			$db->where('setting_id', 1);
			if ($db->update('settings', $col)) {
				echo 1;
			}
			else {
				echo constant('SETTING_NOT_SAVED');die();
			}
			break;
		case 'ajax_insert':
			foreach ($indata as $key => $value) {
				$str .= $key . ' => ' . $value . '\n';
				if ($key == 'table') {
					$table = $value;
				}
				elseif ($key != 'action') {
					$data[$key] = $value;
				}
			}
			$data[$table . '_created'] = date('Y-m-d h:i:s');
			$result = $db->insert($table, $data);
			echo $result; die();
			break;
		case 'addCity':
			$country =  $_POST['country'];
			$region = $_POST['region'];
			$city = strip_tags($_POST['city']);
			
			$db->where('CountryID', $country);
			$db->where('RegionID', $region);
			$db->where('City', $city);
			$check = $db->getOne('app_cities');
			if (count($check) == 0) {
				$data = array(
						'CountryID' => $country,
						'RegionID' => $region,
						'City' => $city
					);
				$result = $db->insert('app_cities', $data);
				echo $result;
				die();
			}
			else {
				echo $check['CityId'];
				die();
			}
			break;
		case 'addDistrict':
			$country =  $_POST['country'];
			$district = strip_tags($_POST['district']);
			
			$db->where('district_countryid', $country);
			$db->where ('district_name', $district);
			$check = $db->getOne('districts');
			
			if (count($check) == 0) {
				$data = array(
						'district_countryid' => $country,
						'district_name' => $district
					);
				$result = $db->insert('districts', $data);
				echo $result;
				die();
			}
			else {
				echo $check['district_id'];
				die();
			}
			break;
	}
}

class Database_management {

	public static function executeInsert($data, $values, $xml_update_tables) { 
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$prefix = self::getPrefix($data['table']); 
		foreach ($values as $key => $value) {
			if ($value=='on' && in_array($key, $data['on_off'])) {
				$value = 1;
			}
			if ($key != 'filesToUpload') {
				$insert[$key] = $value;
			}
		}
		if (isset($data['additional_fields']) && is_array($data['additional_fields'])) { 
			foreach ($data['additional_fields'] as $key => $value) {
				$insert[$key] = $value;
			}
		}
		$insert[$prefix . 'created'] = date('Y-m-d H:i:s'); 
		$result = $db->insert($data['table'], $insert); 
		if ($result) {
			//Common::pingAzure(); 
			return $result;
		}
		else { 
			return 0;
		}
	}
	
	public static function executeNestedInsert($data, $values) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$table = $data['table'];
		$prefix = Database_management::getPrefix($table);
		//Räknar ut level + om parent har barn
		if (is_numeric($values[$prefix . 'parent_after'])) { 
			$has_children = true;
			$db->where($prefix . 'id', $values[$prefix . 'parent']);
			$result = $db->getOne($table);
			$level = $result[$prefix . 'lvl'] + 1;
		}
		else { 
			$has_children = false;
			$db->where($prefix . 'id', $values[$prefix . 'parent']);
			$result = $db->getOne($table);
			$level = $result[$prefix . 'lvl'] + 1;
		}
		if ($has_children) {
			$select = $prefix . 'rgt ';	
			$post_id = $values[$prefix . 'parent_after'];
		}
		else {
			$select = $prefix . 'lft';
			$post_id = $values[$prefix . 'parent'];
		}
		if (isset($data['id'])) {
			$values[$prefix . 'id, '] = $data['id'];
		}
		$sql = '
				SELECT
					' . $select . ' 
				FROM 
					' . $table . '
				WHERE 
					' . $prefix .'id = ' . $post_id . ';
			'; 
		$result = $db->rawQuery($sql); 
		if (strpos($select, 'rgt') !== false) {
			$myright = $result[0][$prefix . 'rgt'];
		}
		else {
			$myright = $result[0][$prefix . 'lft'];
		}
		$sql = '
				UPDATE
					' . $table . '
				SET 
					' . $prefix . 'rgt = ' . $prefix . 'rgt + 2 WHERE ' . $prefix . 'rgt > ' . $myright . ';
			';
		$db->rawQuery($sql);
		$sql = '
				UPDATE
					' . $table . '
				SET 
					' . $prefix . 'lft = ' . $prefix . 'lft + 2 WHERE ' . $prefix . 'lft > ' . $myright . ';
			';
		$db->rawQuery($sql); 
		foreach ($data['on_off'] as $bool) {
			if ($values[$bool] == 'on') {
				$values[$bool] = 1;
			}
			else {
				$values[$bool] = 0;
			}
		}
		$values[$prefix . 'lft'] = $myright+1;
		$values[$prefix . 'rgt'] = $myright+2;
		$values[$prefix . 'lvl'] = $level;
		$values[$prefix . 'created'] = date('Y-m-d H:i:s');
		unset($values[$prefix . 'parent_after']);
		$result = $db->insert($data['table'],$values);
		if ($result) {
			//Common::pingAzure();
			return $result;
		}
		else {
			return 0;
		}
	}
	
	public static function executeUpdate($data, $values, $xml_update_tables) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$prefix = Database_management::getPrefix($data['table']);
		if (is_array($data['on_off'])) {
			foreach ($data['on_off'] as $key => $value) {
				if ($values[$value] == 'on') {
					$values[$value] = 1;
				}
				else {
					$values[$value] = 0;
				}
			}
		}
		$values[$prefix.'changed'] = date('Y-m-d H:i:s');
		unset($values['filesToUpload']);
		$db->where($prefix . 'id', $data['post_id']); 
		if ($db->update($data['table'], $values)) { 
			if(mysqli_connect_errno()) { 
				die("Database connection failed: " . mysqli_connect_error() . "(" . mysqli_connect_errno() . ")");
			} 
			//Common::pingAzure(); 
			
			
			return true; 
		}
		else {
			if(mysqli_connect_errno()) { 
				die("Database connection failed: " . mysqli_connect_error() . "(" . mysqli_connect_errno() . ")");
			}
			return false; 
		}
	}
	
	public static function executeDelete($data, $xml_update_tables) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$app_file_tables = array(
				'ads',
				'deals',
				'deal_suppliers',
				'events',
				'locations',
				'trades',
				'users'
			); 
		$db->where($data['column'], $data['id']);
		if ($db->delete($data['table'])) { 
			//Common::pingAzure();
			if ($data['table'] == 'locations') {
				//include('gen_map_xml.php');
			}
			if (in_array($data['table'], $app_file_tables)) {
				$image = Common::getImage('/_images/' . $data['table'] . '/', $data['id']); 
				if ($image != '0') { 
					$image = $_SERVER['DOCUMENT_ROOT'].$image; 
					unlink($image);
				}
			}
			return true;
		}
		else {
			return false;
		}
	} 
	
	public static function executeNestedDelete($data) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$prefix = Database_management::getPrefix($data['table']);
		$sql = '
				SELECT ' . $prefix . 'lft, ' . $prefix . 'rgt, ' . $prefix . 'rgt - ' . $prefix . 'lft + 1 as width
				FROM ' . $data['table'] . '
				WHERE ' . $data['column'] . ' = ' . $data['id']
			;
		$result = $db->rawQuery($sql);
		$left = $result[0][$prefix.'lft'];
		$right = $result[0][$prefix.'rgt'];
		$width = $result[0]['width'];
		$sql = '
				DELETE FROM ' . $data['table'] . ' WHERE ' . $data['column'] . ' = ' . $data['id'] . '
			';
		$result = $db->rawQuery($sql);
		$sql = '
				UPDATE ' . $data['table'] . ' SET ' . $prefix . 'rgt = ' . $prefix . 'rgt - ' . $width . ' WHERE ' . $prefix . 'rgt > ' . $right;
		$result = $db->rawQuery($sql);
		$sql = '
				UPDATE ' . $data['table'] . ' SET ' . $prefix . 'lft = ' . $prefix . 'lft - ' . $width . ' WHERE ' . $prefix . 'lft > ' . $right;
		$result = $db->rawQuery($sql);
		if (is_array($result)) {
			//Common::pingAzure();
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function getPrefix($table) { 
        switch ($table) {
        	case 'accounts':
            	$prefix = 'account_';
                break;
            case 'app_articles':
            	$prefix = 'article_';
            	break;
            case 'app_events':
            	$prefix = 'event_';
            	break;
            case 'articles':
                $prefix = 'article_';
                break;
            case 'beds':
                $prefix = 'bed_';
                break;
            case 'categories':
                $prefix = 'category_';
                break;
            case 'cities':
                $prefix = '';
                break;
            case 'countries':
                $prefix = '';
                break;
            case 'companies':
                $prefix = 'company_';
                break;
            case 'company_users':
				$prefix = 'cu_';
				break;
			case 'deals':
				$prefix = 'deal_';
				break;
			case 'deal_supplier_types':
				$prefix = 'dst_';
				break;
			case 'deal_suppliers':
				$prefix = 'supplier_';
				break;
			case 'feedback_entries':
				$prefix = 'fe_';
				break;
			case 'golfclubs':
				$prefix = 'club_';
				break;
			case 'golfcourses':
				$prefix = 'course_';
				break;
			case 'golfcourse_facilities':
				$prefix = 'facility_';
				break;
			case 'keywords':
				$prefix = 'keyword_';
				break;
			case 'languages':
				$prefix = '';
				break;
			case 'matches':
				$prefix = 'match_';
				break;
			case 'newsletters':
				$prefix = 'newsletter_';
				break;
			case 'newsletter_categories_rel':
				$prefix = 'nl_';
				break;
			case 'offers':
				$prefix = 'offer_';
				break;
			case 'restaurants':
				$prefix = 'restaurant_';
				break;
			case 'slideshow':
				$prefix = 'slideshow_';
				break;
			case 'users':
				$prefix = 'user_';
				break;
			case 'match_ads':
				$prefix = 'match_ad_';
				break;
			case 'bazaar':
				$prefix = 'bazaar_';
				break;
			case 'rewards':
				$prefix = 'reward_';
				break;
			case 'shops':
				$prefix = 'shop_';
				break;
		}
		return $prefix;
	}
	
}

?>