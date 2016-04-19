<?php
/*
displayUserLForm($id = null)
displayUserList()
executePasswordRecovery($email)
getVerificationCode($userid, $email, $account_type = null)
getUser($id)
getAppUser($id)
greetUser()
getUserDeatils($id)
displayCompanyForm($id)
*/

session_start();
//Language
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/accounts.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/mail.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/courses.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/statistics.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database); 
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'check_email':
			$db->where('user_email', $_POST['email']);
			$result = $db->getOne('users');
			if (count($result) > 0) {
				echo constant('EMAIL_EXIST');
			}
			else {
				echo 0;
			}
			break;
		case 'getContactInfo':
			$cols = array('user_first_name', 'user_last_name', 'user_email', 'user_phone');
			$db->where('user_id', $_POST['user']);
			$user = $db->getOne('users', $cols);
			$html = 
					$user['user_first_name'] . ' ' . $user['user_last_name'] . '<br />
				';
			if (isset($user['user_phone']) && $user['user_phone'] != '') {
				$html .= '
					' . constant('PHONE') . ': ' . $user['user_phone'] . '<br />
				';
			}
			$html .= 
					constant('EMAIL') . ': <a href="mailto: ' . $user['user_email'] . '">' . $user['user_email'] . '</a>
				';
			echo $html;
			break;
		case 'get_user_level':
			$type = $_SESSION['useraccount'];
			if ($type == 1) {
				$user = Users::getUser($_SESSION['userid']);
				echo json_encode($user['user_id']); 
			}
			else {
				echo json_encode(0);
			}
			break;
	}
	if (isset($_POST['do_password']) && $_POST['do_password']==1) {
		$result = Users::executePasswordRecovery($_POST['email']);
		if ($result) {
			header('Location: /change-password');
		}
		else {
			header('Location: /change-password/?email=0');
		}
	}
}

class Users {

	public static function displayUserForm($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			if ($_SESSION['admin_level'] < 99) {
				if ($_SESSION['userid'] != $id) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$db->where('user_id', $id);
			$result = $db->getOne('users');
			$account_id = $result['user_account_type'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			if ($_SESSION['admin_level'] < 99) {
				return Common::displayUnauthorized();
				die();
			}
			$account_id = null;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result['user_verified'] = 1;
		}
		$user_dd = Accounts::getAccountsDropdown($account_id);
		$data = array(
				'table' => 'users',
				'identifier' => 'user_id',
				'table_prefix' => 'user_',
				'column' => 'user_id',
				'datetime' => array ('user_created','user_changed'),
				'required' => array ('user_account_type', 'user_first_name','user_last_name','user_email','user_password'),
				'on_off' => array('user_verified'),
				'user_account_type' => $user_dd,
				'post_id' => $id,
				'submit' => $submit,
				'db-action' => $db_action
			);
			if ($_SESSION['admin_level'] > 400) {
				$fields['user_account_type'] = 'dropdown';
			}
			$fields['user_first_name'] = 'text';
			$fields['user_last_name'] = 'text';
			$fields['user_image'] = 'file';
			//$fields['user_street'] = 'text';
			//$fields['user_zip'] = 'text';
			$fields['user_phone'] = 'text';
			$fields['user_mobile'] = 'text';
			$fields['user_email'] = 'email';
			if (!isset($id)) {
				$fields['user_password'] = 'password';
				$fields['user_verified'] = 'check';
			}
			if ($_SESSION['admin_level'] > 100) {
				$data['return_uri'] = '/admin/users/';
			}
			else {
				$data['return_uri'] = '/admin/';
			}
			return Forms::Form($data, $fields, $result);
	}
	
	public static function displayUserList() {
		if ($_SESSION['admin_level'] < 201) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('USER')) . '
						</div>
						<div class="form_input">
							<input type="text" id="user" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="user_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="user_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		else {
			$html = '';
		}
		$settings = Settings::getSettings();
		$offset = $settings['setting_admin_offset']; 
		if (strpos($_SERVER['REQUEST_URI'], '?')) {
			$uri = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')); 
		}
		else {
			$uri = $_SERVER['REQUEST_URI'];
		} 
		if (substr($uri, -1) == '/') {
			$uri = substr($uri, 0, -1);
		}
		else {
			$uri = $uri;
		} 
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		else {
			$page = 1;
		}
		$limit = $offset;
		$offset = ($page * $offset) - $offset;
		$total = $db->get('users');
		if (count($total) == 0) {
			return 0;
		}
		else {
			$db->where('user_id > 0');
			$db->orderBy('user_last_name', 'ASC');
			$users = $db->get('users');
			foreach ($users as $user) {
				if ($user['user_city'] != '') {
					$user_city = $user['user_city'];
				}
				else {
					$user_city = '-';
				} 
				$account_id = $user['user_account_type'];
				$column = array(
						'ID' => $user['user_id'],
						'FIRST_NAME' => $user['user_first_name'], 
						'LAST_NAME' => $user['user_last_name'], 
						'EMAIL' => $user['user_email'], 
						'CITY' => $user_city,
						'VERIFIED' => $user['user_verified'],
						'LAST_LOGIN' => $user['user_last_login'],
						'CREATED' => $user['user_created'],
						'CHANGED' => $user['user_changed']
					);
//				$column['ACCOUNT_TYPE'] = constant(strtoupper(Accounts::getAccountTitle($account_id)));
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('LAST_LOGIN', 'CREATED', 'CHANGED'),
					'bool' => array('VERIFIED'),
					'table' => 'users',
					'identifier' => 'user_id',
					'linked' => array('FIRST_NAME', 'LAST_NAME'),
					'db_action' => 'delete'
				);
			$html .= Lists::createList($columns, $data);
			if (substr($uri, -1) == '/') {
				$URI = substr($_SERVER['REQUEST_URI'], 0, -1);
			}
			else {
				$URI = $_SERVER['REQUEST_URI'];
			}
			if (count($total) > $limit) {
				$html .= Navigation::buildPageNavigation($URI, null, $page, count($total), $limit);
			}
			return $html;
		}
	}
	
	public static function executePasswordRecovery($email) { 
		//Skapa verkod för user
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		$user_found = false;
		$account_type = '';
		$db->where('user_email', $email);
		$user = $db->getOne('users'); 
		if ($user) {
			$account_type = 1;
			$user_found = true;
		}
		$db->where('course_email_primary', $email);
		$course = $db->getOne('golfcourses'); 
		if ($course) {
			$account_type = 2;
			$user_found = true;
		}
		$db->where('shop_email', $email);
		$shop = $db->getOne('shops'); 
		if ($shop) {
			$account_type = 3;
			$user_found = true;
		}
		//Skicka mail 
		if ($user_found) {
			$ver_code = self::getVerificationCode($user['user_id'], $email, $account_type); 
			$maildata = array(
					'subject' => constant('WELCOME') . ' ' . constant('TO') . ' ' . $settings['website_title'],
					'from' => $settings['setting_default_email'],
					'to' => $email,
					'template' => 'password_recovery',
					'message' => constant('HELLO') . ' ' . $user['first_name'] . '!<br /><br />' . constant('TO_CHANGE_PASSWORD') . ': ',
					'vercode' => $ver_code
				);
			$mailsent = MailManagement::sendMail($maildata); 
			if (!$mailsent) {
				die('Mail error');
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function displayRegisterForm($id = null) {
		$data['table'] = 'users';
		$data['table_prefix'] = 'user_';
		$data['return_uri'] = '/create-password/';
		$data['required'] = array ('user_first_name', 'user_last_name', 'user_email');
		$data['submit'] = constant('REGISTER_SUBMIT');
		$data['user_account_type'] = 1;
		$data['db-action'] = 'registration';
		//$fields['user_account_type'] = 'multiple_radio';
		//$fields['user_first_name'] = 'text';
		//$fields['user_last_name'] = 'text';
		$fields['member_email'] = 'email';
		//$fields['user_reward'] = 'multiple_radio';
		$location_details = Common::getLocation($_SERVER['REMOTE_ADDR']);
		$country = $location_details->geoplugin_countryName;
		$html = '
				<div class="register_startpage popup">
					<div id="register_form" class="form_wrapper" data-target="">
						<div class="popup_close">x</div>
						<div id="reg_form_content">
							<!---center><img src="/_icons/logotype.png" title="SkiinginMobile logotype" /></center--->
							<!---p>' . constant('REGISTER_FORM_INTRO') . '</p--->
							' . constant('REGISTER_FORM_TITLE') . '
						</div>
						<div id="new_member_email_wrapper">
							<div><input type="email" id="member_email" value="' . constant('YOUR_EMAIL') . '" /></div>
							<div><input type="submit" id="member_email_submit" class="register_submit_button" value="' . constant('REGISTER_SUBMIT') . '" /></div>
						</div>
						<div id="new_member_password_wrapper">	
							<p>' . constant('REG_CHANGE_PWD') . '<br />
							<input type="password" name="member_password" id="member_password"><input type="button" value="'. constant('SAVE') . ' ' . constant('PASSWORD') . '" id="member_password_submit" /></p>
						</div>
						<div class="clear_both"></div>
						<!--div>
							' . constant('ALREADY_MEMBER') . ' ' . constant('LINK_LOG_IN') . '
						</div-->
					</div>
				</div>
			';
		return $html;
	}
	
	public static function getVerificationCode($userid, $email, $account_type = null) { 
		//syntax : <YY><ID>#<encrypted e-mailaddress>
		$vercode_email = Accounts::encryptPassword($email);
		$vercode = date('y') . $userid . '_' . date('ymd') . '_' . $vercode_email;
		return $vercode;
	}
	
	public static function getUser($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('user_id', $id);
		$user = $db->getOne('users');
		return $user;
	}
	public static function getAppUser($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('app_user_id', $id);
		$user = $db->getOne('app_users');
		return $user;
	}
	
	public static function greetUser() {  
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		
		if ($_SESSION['admin_level'] > 400) {
			
			$html .= Statistics::stats();
			
		}
		elseif ($_SESSION['admin_level'] == 100) { 
			$db->where('user_id', $_SESSION['userid']);
			$user = $db->getOne('users');
			if ($user) {
				$html .= '
						<h1>' . constant('WELCOME') . ' ' . $user['user_first_name'] . ' ' . $user['user_last_name'] . '</h1>
						<div class="user_details">
							<h2>' . constant('YOUR_ACCOUNT') . '</h2>
							' . self::getUserDetails($_SESSION['userid']) . '
						</div></div>
					';
			}
			//$html .= Common::display401();
		}
		elseif ($_SESSION['admin_level'] == 200) { 
			$db->where('course_id', $_SESSION['userid']);
			$course = $db->getOne('golfcourses'); 
			if ($course) {
				$html .= '
						<h1>' . constant('WELCOME') . ' ' . $course['course_name'] . '</h1>
						<a href="/admin/golfcourses/connections/?parent=' . $course['course_id'] . '&type=club"><img src="/_icons/golf_club_icon.png" id="conn_club_partners" title="' . constant('GOLF_CLUB_PARTNERS') . '" /></a>
						<a href="/admin/golfcourses/connections/?parent=' . $course['course_id'] . '&type=course"><img src="/_icons/golf_course_icon.png" id="conn_course_partners" title="' . constant('GOLF_COURSE_PARTNERS') . '" data-id="' . $course['course_id'] . '" /></a>
						<a href="/admin/golfcourses/connections/?parent=' . $course['course_id'] . '&type=restaurant"><img src="/_icons/restaurants_icon.png" id="conn_restaurants" title="' . constant('RESTAURANTS') . '" data-id="' . $course['course_id'] . '" /></a>
						<a href="/admin/golfcourses/connections/?parent=' . $course['course_id'] . '&type=bed"><img src="/_icons/beds_icon.png" id="conn_beds" title="' . constant('BEDS') . '" data-id="' . $course['course_id'] . '" /></a>
						<a href="/admin/golfcourses/update/?id=' . $course['course_id'] . '" alt="' . constant('CHANGE') .'"><img src="/_icons/edit.png"></a>
						
						<div class="user_details">
							<h2>' . constant('YOUR_ACCOUNT') . '</h2>
							' . Courses::getCourseDetails($_SESSION['userid']) . '
						</div>
					';
			}
		}
		elseif ($_SESSION['admin_level'] == 250) { 
			$db->where('shop_id', $_SESSION['userid']);
			$shop = $db->getOne('shops');
			if ($shop) {
				$html .= '
						<h1>' . constant('WELCOME') . ' ' . $shop['shop_name'] . '</h1>
						<div class="user_details">
							<h2>' . constant('YOUR_ACCOUNT') . '</h2>
							' . Shops::getShopDetails($_SESSION['userid']) . '
						</div>
					';
			}
		}
		return $html;
	}
	
	public static function getUserDetails($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$user = self::getUser($id);
		$html = '';
		/*if ($user['user_mobile'] == '') {
			$html .= '
					<div class="warning">
						<p>' . constant('NO_USER_MOBILE') . '</p>
					</div>
				';
		}*/
		$image = Common::getImage('/_users/images/', $_SESSION['userid']);
		$comp_size = array('1' => '1', '2' => '2-5', '3' => '6-20', '4' => '21-50', '5' => '51-100', '6' => '>100');
		if ($image == '0') {
			$image = '/_users/images/user_' . $_SESSION['admin_level'] . '.png';
		}
		if ($user['user_first_name'] != '' || $user['user_last_name'] !='') {
			$name = $user['user_first_name'] . ' ' . $user['user_last_name'];
		}
		else {
			$name = '-';
		}
		if ($user['user_street'] != '') {
			$street = $user['user_street'];
		}
		else {
			$street = '-';
		}
		if ($user['user_zip'] != '') {
			$zip = $user['user_zip'];
		}
		else {
			$zip = '-';
		}
		if ($user['user_city'] != '') {
			$city = $user['user_city'];
		}
		else {
			$city = '-';
		}
		
		if ($user['user_phone'] != '') {
			$phone = $user['user_phone'];
		}
		else {
			$phone = '-';
		}
		
		if ($user['user_mobile'] != '') {
			$mobile = $user['user_mobile'];
		}
		else {
			$mobile = '-';
		}
		
		if ($user['user_email'] != '') {
			$email = $user['user_email'];
		}
		else {
			$email = '-';
		}
		if ($user['user_company_name'] != '') {
			$company_name = $user['user_company_name'];
		}
		else {
			$company_name = '-';
		}
		if ($user['user_company_regnr'] != '') {
			$company_regno = $user['user_company_regnr'];
		}
		else {
			$company_regno = '-';
		}
		if ($user['user_company_size'] > 0) {
			$company_size = $user['user_company_size'];
			foreach ($comp_size as $key => $value) {
				if ($key == $user['user_company_size']) {
					$company_size = $value;
				}
			}
		}
		else {
			$company_size = '-';
		}
		
		$html .= '
				<div class="user_image">
					<img src="' . $image . '" />
				</div>
				<div class="user_data">
					<h3>User details</h3>
					<div class="user_label">
						' .constant('NAME') . '
					</div>
					<div id="user_name" class="user_value">
						' . $name . '
					</div>
					<!--div class="user_label">
						' .constant('STREET') . '
					</div>
					<div id="user_name" class="user_value">
						' . $street . '
					</div>
					<div class="user_label">
						' .constant('ZIP') . '
					</div>
					<div id="user_name" class="user_value">
						' . $zip . '
					</div>
					<div class="user_label">
						' .constant('CITY') . '
					</div>
					<div id="user_name" class="user_value">
						' . $city . '
					</div-->
					<div class="user_label">
						' .constant('PHONE') . '
					</div>
					<div id="user_name" class="user_value">
						' . $phone . '
					</div>
					<div class="user_label">
						' .constant('MOBILE') . '
					</div>
					<div id="user_name" class="user_value">
						' . $mobile . '
					</div>
					<div class="user_label">
						' .constant('EMAIL') . '
					</div>
					<div id="user_name" class="user_value">
						' . $email . '
					</div>
					<div class="user_label">
						&nbsp;
					</div>
					<div class="user_value">
						<input type="button" value="' . constant('CHANGE') . '" class="update_account submit_button" id="' . $_SESSION['userid'] . '" />
					</div>
					<div class="clear_both"></div>
					
			';
		/*
		if ($user['user_company_offers'] == 0) {
			$company_offers = '
					<input type="checkbox" name="user_company_offers" id="user_company_offers" />
					<label class="choice" for="private"></label>		
				';
			$company_offers_data = '';
			$submit = constant('CREATE');
			$db_action = 'update';
			$result = null;
			$data['user_company_size_value'] = 200;
		}
		else {
			$company_offers = '';
			$company_offers_data = '
					<div class="user_label">
						' .constant('COMPANY_NAME') . '
					</div>
					<div id="company_name" class="user_value">
						' . $company_name . '
					</div>
					<div class="user_label">
						' .constant('COMPANY_REGNR') . '
					</div>
					<div id="company_regno" class="user_value">
						' . $company_regno . '
					</div>
					<div class="user_label">
						' .constant('COMPANY_SIZE') . '
					</div>
					<div id="company_size" class="user_value">
						' . $company_size . '
					</div>
					<div class="user_value">
						<input type="button" value="' . constant('CHANGE') . '" class="update_company_details submit_button" id="' . $_SESSION['userid'] . '" />
					</div>
					<div class="clear_both"></div>
				';
			$db->where('user_id', $id);
			$result = $db->getOne('users');
			$submit = constant('UPDATE');
			$db_action = 'update';
			$data['user_company_size_value'] = $result['user_company_size'];
		}*/
		$data['table'] = 'users';
		$data['identifier'] = 'user_id';
		$data['table_prefix'] = 'user_';
		$data['return_uri'] = '/admin/';
		$data['column'] = 'user_id';
		$data['company_size'] = $comp_size;
		$data['required'] = array('user_company_name', 'user_company_regnr', 'user_company_size');
		$data['submit'] = $submit;
		$data['db-action'] = $db_action;
		$fields['user_company_name'] = 'text';
		$fields['user_company_regnr'] = 'text';
		$fields['user_company_size'] = 'multiple_radio';
		$company_form = Forms::Form($data, $fields, $result);
		/*if ($user['user_company_offers'] == 0) {
			$html .= '
						<div class="user_label">
							' . constant('WANT_COMPANY_OFFERS') . '
						</div>
						<div id="user_company_offers" class="user_value">
							' . $company_offers . '
						</div>			
				';
		}
		else {
			$html .= '
					<h3>Company details</h3>
				';
		}
		$html .= $company_offers_data;
		$html .= '<div id="company_offers_form">' . $company_form . '</div></div>';*/
		return $html;
	}
	
}

?>