<?php
/*
displayCourseForm($id = null)
displayCourseList()
displayCourseView($id)
getCourses($id)
getCourseTypes($type_id)
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/reviews.php');

class Courses {

	public static function displayCourseForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] < 201) {
			$db->where('course_administrator', $_SESSION['userid']);
			$course = $db->getOne('golfcourses'); 
			$owner = $course['course_administrator'];
		}
		if (isset($id)) {
			$db->where('course_id', $id);
			$result = $db->getOne('golfcourses');
			/*if ($_SESSION['admin_level'] < 201) {
				if ($owner != $result['course_administrator']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$db->where('club_id', $result['course_parent_club']);
			$p_club = $db->getOne('golfclubs');
			if ($p_club['club_name'] != '') {
				$span_val_club = $p_club['club_name'];
			}
			else {
				$span_val_club = constant('NO_PARENT_CLUB_LISTED');
			}*/
			$db->where('user_id', $result['course_administrator']);
			$admin = $db->getOne('users');
			if ($admin['user_last_name'] != '') {
				$span_val_adm = $admin['user_first_name'] . ' ' . $admin['user_last_name'];
			}
			else {
				$span_val_adm = constant('NO_ADMINISTRATOR_LISTED');
			}
			
			//$type_id = $result['course_type'];
			//$district = $result['course_districtid'];
			$city = $result['course_cityid'];
			$region = $result['course_regionid'];
			$country = $result['course_countryid'];
			$street = $result['course_street'];
			$zip = $result['course_zip'];
			//$chosen_facilities = $result['course_facilities'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$account_id = null;
			//$type_id = 0;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
			if ($_SESSION['admin_level'] < 201) {
				$city = $club['club_cityid'];
				$region = $club['club_regionid'];
				$country = $club['club_countryid'];
				$result['course_street'] = $club['club_street'];
				$result['course_zip'] = $club['club_zip'];
			}
			//$result['course_parent_club'] = 0;
			$span_val_adm = constant('NO_ADMINISTRATOR_LISTED');
			$span_val_club = constant('NO_PARENT_CLUB_LISTED');
		}
		
		/*$course_types = $db->get('golfcourse_types', null, array('course_type_id','course_type_title')); 
		$db->where('course_id', $id);
		$choosen_types = $db->get('golfcourse_type_rel');
		foreach ($choosen_types as $value) {
			$ch_types[] = $value['type_id'];
		}
		//Holes
		$holes_dd = '
				<select name="course_no_of_holes" id="name="course_no_of_holes">
					<option value="0">--- ' . constant('CHOOSE') . ' ' . strtolower(constant('NO_OF_HOLES')) . ' ---</option>
					<option value="9"
			';
		if ($result['course_no_of_holes'] == '9') {
			$holes_dd .= ' selected="selected"';
		}
		$holes_dd .= '
					>9</option>
					<option value="18"
			';
		if ($result['course_no_of_holes'] == '18') {
			$holes_dd .= ' selected="selected"';
		}
		$holes_dd .= '
				>18</option>
				
				<option value="27"
			';
		if ($result['course_no_of_holes'] == '27') {
			$holes_dd .= ' selected="selected"';
		}
		$holes_dd .= '
				>27</option>
				
					<option value="36"
			';
		if ($result['course_no_of_holes'] == '36') {
			$holes_dd .= ' selected="selected"';
		}
		$holes_dd .= '
					>36</option>
				</select>
			'; 
		$districts_dd = self::getDistrictDropdown($district, $country);
		$facilities = $db->get('golfcourse_facilities');
		*/
		
		$data = array(
				'table' => 'golfcourses',
				'identifier' => 'course_id',
				'table_prefix' => 'course_',
				'required' => array (
						'course_name', 
						'course_street', 
						'course_zip', 
						'course_cityid', 
						'course_regionid', 
						'course_countryid', 
						'course_email_primary',
						'course_phone_information'
					),
				'post_id' => $id,
				/*'course_parent_club' => array(
						'span_id' => 'parent_club',
						'span_val' => $span_val_club,
						'ac_var' => 'golfclub',
						'button_id' => 'course_club_select'
					),*/
					
				'course_administrator' => array(
						'span_id' => 'course_admin',
						'span_val' => $span_val_adm,
						'ac_var' => 'golfcourse_admin',
						'button_id' => 'course_admin_select'
					),
					
				'on_off' => array('course_status', 'course_sponsor','course_restaurantconn','course_hotelconn'),
				'course_id' => $id,
				'course_countryid' => $country,
				'course_regionid' => $region,
				'course_cityid' => $city,
				//'course_districtid' => $districts_dd,
				//'type' => $course_types,
				//'facilities' => $facilities,
				//'chosen_course_types' => $ch_types,
				//'chosen_facilities' => $chosen_facilities,
				'short_desc' => array('course_description'),
				'course_no_of_holes' => $holes_dd,
				'course_owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
		if ($_SESSION['admin_level'] == 200) {
			$data['return_uri'] = '/admin/';
		}
		else {
			$data['return_uri'] = '/admin/golfcourses/';
		}
		$fields['course_name'] = 'text';
		//$fields['course_parent_club'] = 'autofill';
		$fields['course_image'] = 'file';
		//$fields['course_type'] = 'multiple_check';
		//$fields['course_no_of_holes'] = 'dropdown';
		//$fields['course_par'] = 'text';
		//$fields['course_length_yel'] = 'text';
		//$fields['course_length_red'] = 'text';
		$fields['course_description'] = 'textarea';
		$fields['course_long_description'] = 'large_editor';
		//$fields['course_greenfee'] = 'textarea';
		
		$fields['course_restaurantconn'] = 'check';
		$fields['course_hotelconn'] = 'check';
		
		//$fields['course_facilities'] = 'multiple_check';
		$fields['course_countryid'] = 'location';
		$fields['course_regionid'] = 'location';
		$fields['course_cityid'] = 'location';
		//$fields['course_districtid'] = 'dropdown';  
		$fields['course_street'] = 'text';
		$fields['course_zip'] = 'text';
		$fields['course_lat'] = 'text';
		$fields['course_lng'] = 'text';
		$fields['course_phone_information'] = 'text';
		//$fields['course_phone_booking'] = 'text';
		//$fields['course_phone_mobile'] = 'text';
		$fields['course_email_primary'] = 'email';
		//$fields['course_email_alt1'] = 'email';
		//$fields['course_email_alt2'] = 'email';
		if ($_SESSION['admin_level'] > 200) {
			$fields['course_administrator'] = 'autofill';
		}
		$fields['course_url'] = 'text';
		if ($_SESSION['admin_level'] > 200) {
			$fields['course_sponsor'] = 'check';
			$fields['course_status'] = 'check'; 
		}
		
		/*if ($_SESSION['admin_level'] > 200) {
			$fields['course_owner'] = 'text';
		}
		else {
			$fields['course_owner'] = 'hidden';
		}*/
		$html = Forms::Form($data, $fields, $result);
		return $html;
	}

	public static function displayCourseList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		if (isset($_GET['f'])) {
			switch ($_GET['f']) {
				case '1':
					$filter = 'AND course_status = 1';
					$link_f = '?f=1';
					$db->where('course_status', 1);
					break;
				case '2':
					$filter = 'AND course_status = 0';
					$link_f = '?f=2';
					$db->where('course_status', 0);
					break;
				default:
					$filter = '';
					$link_f = '?f=0';
					break;
			}
		}
		else {
			$link_f = '?f=0';
		}
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
		if ($_SESSION['admin_level'] < 201) {
			$db->where('course_administrator', $_SESSION['userid']);
		}
		$total = $db->get('golfcourses');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('RESTAURANTS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('GOLFCOURSE')) . '
						</div>
						<div class="form_input">
							<input type="text" id="golfcourse" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="golfcourse_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="golfcourse_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
					<div id="hits_info_container">
						' . constant('TOTAL_HITS') . ' <strong>' . count($total) . '</strong> | ' . constant('DISPLAYING') . ' ' . mb_strtolower(constant('HIT'), 'utf-8') . ' <strong>' . ($offset + 1) . ' - ' . ($offset + $limit) . '</strong>
					</div>
					<div id="sort_panel">
						<div id="sort_order">
						';
			if (isset($_GET['so'])) { 
				$link_so = '&so=' . $_GET['so']; 
				switch ($_GET['so']) {
					case '1': 
						$sortorder = 'course_name COLLATE utf8_swedish_ci ASC';
						$link_out = constant('LATEST');
						$so_info = constant('SORTING_BY') . ' <strong>' . constant('BY_NAME') . '</strong>';						
						if ($_GET['a'] == 'so') {
							$link_so = '&so=2';
						}
						else {
							$link_so = '&so=' . $_GET['so'];
						}
						break;
					case '2': 
						$sortorder = 'course_created DESC';
						$link_out = constant('BY_NAME');
						$so_info = constant('SORTING_BY') . ' <strong>' . constant('LATEST') . '</strong>';
						if ($_GET['a'] == 'so') {
							$link_so = '&so=1';
						}
						else {
							$link_so = '&so=' . $_GET['so'];
						}
						break;
				} 
			}
			else {
				$sortorder = 'course_created DESC';
				$link_so = '&so=1';
				$link_out = constant('BY_NAME');
				$so_info = constant('SORTING_BY') . ' <strong>' . constant('LATEST') . '</strong>';
			}
			$html .= '
					<img src="/_icons/icon_sort.png" title="' . constant('SORT_ORDER') . '" />
					' . $so_info . ' &raquo;
					<strong><a href="' . $link_f . $link_so . '&a=so">' . constant('SORT_BY') . ' ' . $link_out . '</a></strong>
				';
			$html .= '
				</div>
				<div id="filter">
					<img src="/_icons/filter_icon.png" title="' . constant('FILTER_BY') . '" />
					' . constant('FILTER_BY') . ': <strong><a href="?f=1&so=' . $_GET['so'] . '&a=f"';
			if ($_GET['f'] == '1') {
				$html .= ' class="active_link"';
			}
			$html .= '>' . constant('PUBLISHED') . '</a> | <a href="?f=2&so=' . $_GET['so'] . '&a=f"';
			if ($_GET['f'] == '2') {
				$html .= ' class="active_link"';
			}
			$html .= '>' . constant('NOT_PUBLISHED') . '</a> | <a href="?f=0&so=' . $_GET['so'] . '&a=f"';
			if ($_GET['f'] == '0' || !isset($_GET['f'])) {
				$html .= ' class="active_link"';
			}
			$html .= '>' . constant('ALL') . '</a></strong>
			';
		}
		else {
			$sortorder = 'course_created DESC';
			$link_get = '?so=1';
			$link_out = constant('BY_NAME');
			$html = '';
		}
		$html .= '
					</div>
					<div class="clear_both"></div>
				</div>
			';
			if ($_SESSION['admin_level'] < 201) {
				$db->where('club_gkadministrator', $_SESSION['userid']);
				$result = $db->getOne('golfclubs');
				$golfclub = $result['golf_id'];
				$db->where('course_owner', $golfclub);
			}
			$query = '
					SELECT 
						course_id,
						course_name,
						course_cityid,
						course_countryid,
						course_street,
						
						course_lng,
						course_lat,
						course_status,
						course_created,
						course_changed
					FROM 
						golfcourses 
					WHERE
						1 = 1
				';
			if ($_SESSION['admin_level'] < 201) {
				$query .= '
					AND 
						course_administrator = ' . $_SESSION['userid']
					;
			}
			$query .= 
					$filter .
					'
					ORDER BY 
						' . $sortorder . '
					LIMIT ' . $limit . '
					OFFSET ' . $offset . '
				';
			$golfcourses = $db->rawQuery($query);
			foreach ($golfcourses as $golfcourse) {
				$city = Common::getCity($golfcourse['course_cityid']);
				$country = Common::getCountry($golfcourse['course_countryid']);
				$db->where('club_id', $golfcourse['course_parent_club']);
				$p_club = $db->getOne('golfclubs');
				if ($p_club['club_name'] != '') {
					$parent_club = $p_club['club_name'];
				}
				else {
					$parent_club = '-';
				}
				$column = array(
						'ID' => $golfcourse['course_id'],
						'STATUS' => $golfcourse['course_status'],
						'NAME' => $golfcourse['course_name'], 
						'STREET' => substr($golfcourse['course_street'], 0, 20),
						'CITY' => $city,
						'COUNTRY' => $country,
						
						'LOCATION' => $golfcourse['course_lng'] . ' : ' . $golfcourse['course_lat'],
						'CREATED' => substr($golfcourse['course_created'], 0, 13),
						'CHANGED' => substr($golfcourse['course_changed'], 0, 13)
					);
				$columns[] = $column; 
				$parent_club = '';
			}
			$data = array(
					'numeric' => array('ID'),
					'bool' => array('STATUS'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'golfcourses',
					'icon' => array('STATUS'),
					'identifier' => 'course_id',
					'linked' => array('NAME'),
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
		}
		return $html;
	}
	
	public static function displayCourseView($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('course_id', $id);
		$golfcourse = $db->getOne('golfcourses');
		$db->where('course_id', $id);
		$types = $db->get('golfcourse_type_rel');
		$course_type = '';
		foreach ($types as $type) {
			$db->where('course_type_id', $type['type_id']);
			$type_title = $db->getOne('golfcourse_types');
			$course_type .= constant('GOLFCOURSE_TYPE_' . strtoupper($type_title['course_type_title'])) . ', ';
		}
		$course_type = substr($course_type, 0, -2);
		$db->where('CityId', $golfcourse['course_cityid']);
		$city = $db->getOne('app_cities');
		$course_city = $city['City'];
		
		$db->where('CountryId', $golfcourse['course_countryid']);
		$city = $db->getOne('app_countries');
		$course_country = $city['Country'];
		
		$image = Common::getImage('/_golfcourses/images/', $golfcourse['course_id']);
		if ($image != '0') {
			$image_elem = '<img src="' . $image . '" title="' . $golfcourse['course_name'] . '" />';
			
		}
		else {
			$image_elem = '';
		}
		$facilities_array = explode(',', $golfcourse['course_facilities']);
		foreach ($facilities_array as $facility) {
			$db->where('facility_id', $facility);
			$result = $db->getOne('golfcourse_facilities', null, 'facility_title');
			if ($result['facility_prio'] == 1) {
				$facility_value .= '<img src="/_icons/' . $result['facility_image'] . '"> ';
			}
			elseif ($result['facility_prio'] == 2) {
				$addon_value .= '<img src="/_icons/facility_' . $result['facility_image'] . '"> ';
			}
		}
		// Club Partners
		$db->join('golfclubs', 'club_id=conn_obj_id');
		$db->where('conn_gc_id', $id);
		$db->where('conn_type',1);
		$club_partners = $db->get('golfcourse_connections', null, array('conn_obj_id', 'club_name','club_url'));
		$clubpartners = '';
		foreach ($club_partners as $club_partner) {
			if (substr($club_partner['club_url'], 0, 7) != 'http://') {
				$clp_url = 'http://' . $club_partner['club_url'];
			}
			else {
				$clp_url = $club_partner['club_url'];
			}
			$clubpartners .= '
					<div class="club_partner">
						<a href="' . $clp_url . '" target="_blank">' . $club_partner['club_name'] . '</a>
					</div>
				';
		}
		//Course Partners
		$db->join('golfcourses', 'course_id=conn_obj_id');
		$db->where('conn_gc_id', $id);
		$db->where('conn_type',2);
		$course_partners = $db->get('golfcourse_connections', null, array('conn_obj_id', 'course_name','course_url'));
		$coursepartners = '';
		foreach ($course_partners as $course_partner) {
			if (substr($course_partner['course_url'], 0, 7) != 'http://') {
				$clp_url = 'http://' . $course_partner['course_url'];
			}
			else {
				$clp_url = $course_partner['course_url'];
			}
			$coursepartners .= '
					<div class="course_partner">
						<a href="' . $clp_url . '" target="_blank">' . $course_partner['course_name'] . '</a>
					</div>
				';
		}
		//Recommended restaurants
		$db->join('restaurants', 'restaurant_id=conn_obj_id');
		$db->where('conn_gc_id', $id);
		$db->where('conn_type',3);
		$restaurant_partners = $db->get('golfcourse_connections', null, array('conn_obj_id', 'restaurant_name','restaurant_url'));
		$restaurantpartners = '';
		foreach ($restaurant_partners as $restaurant_partner) {
			if (substr($restaurant_partner['restaurant_url'], 0, 7) != 'http://') {
				$clp_url = 'http://' . $restaurant_partner['restaurant_url'];
			}
			else {
				$clp_url = $restaurant_partner['restaurant_url'];
			}
			$restaurantpartners .= '
					<div class="restaurant_partner">
						<a href="' . $clp_url . '" target="_blank">' . $restaurant_partner['restaurant_name'] . '</a>
					</div>
				';
		}
		//Recommended accomodations
		$db->join('beds', 'bed_id=conn_obj_id');
		$db->where('conn_gc_id', $id);
		$db->where('conn_type',4);
		$accomodation_partners = $db->get('golfcourse_connections', null, array('conn_obj_id', 'bed_name','bed_url'));
		$accomodationpartners = '';
		foreach ($accomodation_partners as $accomodation_partner) {
			if (substr($accomodation_partner['bed_url'], 0, 7) != 'http://') {
				$clp_url = 'http://' . $accomodation_partner['bed_url'];
			}
			else {
				$clp_url = $accomodation_partner['bed_url'];
			}
			$accomodationpartners .= '
					<div class="accomodation_partner">
						<a href="' . $clp_url . '" target="_blank">' . $accomodation_partner['bed_name'] . '</a>
					</div>
				';
		}
		
		$html = '
				<div id="article" class="golfcourse">
					<h1>' . $golfcourse['course_name'] . '</h1>
					<div id="article_body">
						<p>
							' . $image_elem . '
						</p>
						<p>
							' . $golfcourse['course_long_description'] . '
						</p>
					</div>
					
				</div>
				<div id="course_info_wrapper">
					<div id="course_info_content">
						<h1 class="course_info">
							' . constant('CONTACT_INFORMATION') . ':
						</h1>
						<div class="course_info_label">
							' . constant('ADDRESS') . ':
						</div>
						<div class="course_info_value">
							' . $golfcourse['course_street'] . '<br />
							' . $golfcourse['course_zip'] . ' ' . $course_city . '<br />
							' . $course_country . '
						</div>
						<div class="clear_both"></div>
						<div class="course_info_label">
							' . constant('INFO_PHONE') . ':
						</div>
						<div class="course_info_value">
							' . $golfcourse['course_phone_information'] . '
						</div>
						<div class="clear_both"></div>
			';
		if ($golfcourse['course_phone_booking'] != '') {
			$html .= '
						<div class="course_info_label">
							' . constant('RESERVE_BOOKING_PHONE') . ':
						</div>
						<div class="course_info_value">
							' . $golfcourse['course_phone_booking'] . '
						</div>
						<div class="clear_both"></div>
				';
		}
		if ($golfcourse['course_phone_mobile'] != '') {
			$html .= '
						<div class="course_info_label">
							' . constant('MOBILE') . ':
						</div>
						<div class="course_info_value">
							' . $golfcourse['course_phone_mobile'] . '
						</div>
						<div class="clear_both"></div>
				';
		}
		$html .= '
						<div class="course_info_label">
							' . constant('EMAIL') . ':
						</div>
						<div class="course_info_value">
							<a href="mailto:' . $golfcourse['course_email_primary'] . '">' . $golfcourse['course_email_primary'] . '</a>
						</div>
						<div class="clear_both"></div>
						<div class="course_info_label">
							' . constant('URL') . ':
						</div>
						<div class="course_info_value">
							<a href="' . $golfcourse['course_url'] . '" target="_blank">' . $golfcourse['course_url'] . '</a>
						</div>
						<div class="clear_both"></div>
						<div class="course_info_label">
							' . constant('GPS') . ':
						</div>
						<div class="course_info_value">
							' . $golfcourse['course_lng'] . ' : ' . $golfcourse['course_lat'] . '
						</div>
						<div class="clear_both"></div>
						
					</div>
			';
		
		if (strlen($clubpartners) > 0) {
			$html .= '
					<div id="club_partner_content">
						<h1 class="course_info">
							' . constant('CLUB_PARTNERS') . '
						</h1>
						<div class="course_info_label">
							' . $clubpartners . ' 
						</div>
					</div>
				';
		}
		if (strlen($clubpartners) > 0) {
			$html .= '
					<div id="course_partner_content">
						<h1 class="course_info">
							' . constant('COURSE_PARTNERS') . '
						</h1>
						<div class="course_info_label">
							' . $coursepartners . ' 
						</div>
					</div>
		';
		}
		if (strlen($clubpartners) > 0) {
			$html .= '
					<div id="restaurant_partner_content">
						<h1 class="course_info">
							' . constant('RECOMMENDED_RESTAURANTS') . '
						</h1>
						<div class="course_info_label">
							' . $restaurantpartners . ' 
						</div>
					</div>
				';
		}
		if (strlen($clubpartners) > 0) {
			$html .= '
					<div id="accomodation_partner_content">
						<h1 class="course_info">
							' . constant('RECOMMENDED_ACCOMODATIONS') . '
						</h1>
						<div class="course_info_label">
							' . $accomodationpartners . ' 
						</div>
					</div>
				';
		}
		$html .= '
				</div>
			';
		return $html;
	}
	
	public static function getCourses($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('course_id', $id); 
		$golfcourses = $db->getOne('golfcourses');
		return $golfcourses;
	}
	
	public static function getCourseDetails($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$course = self::getCourses($id);
		$html = '';
		if ($course['course_lng'] < 0.0000001 || $course['course_lat'] < 0.0000001) {
			$html .= '
					<div class="warning">
						<p>' . constant('NO_COURSE_GPS') . '</p>
					</div>
				';
		}
		if ($course['course_phone_mobile'] == '') {
			$html .= '
					<div class="warning">
						<p>' . constant('NO_COURSE_MOBILE') . '</p>
					</div>
				';
		}
		if ($course['course_zip'] == '' || $course['course_street'] == '') {
			$html .= '
					<div class="warning">
						<p>' . constant('NO_COURSE_ADDRESS') . '</p>
					</div>
				';
		}
			$image = Common::getImage('/_golfcourses/images/', $id);
			if ($image == '0') {
				$image = '/_users/images/user_' . $_SESSION['admin_level'] . '.png';
			}
			if ($course['course_street'] != '') {
				$street = $course['course_street'];
			}
			else {
				$street = '-';
			}
			if ($course['course_zip'] != '') {
				$zip = $course['course_zip'];
			}
			else {
				$zip = '-';
			}
			if ($course['course_city'] != '') {
				$city = $course['course_city'];
			}
			else {
				$city = '-';
			}
			
			if ($course['course_phone_information'] != '') {
				$phone = $course['course_phone_information'];
			}
			else {
				$phone = '-';
			}
			
			if ($course['course_phone_mobile'] != '') {
				$mobile = $course['course_phone_mobile'];
			}
			else {
				$mobile = '-';
			}
			
			if ($course['course_email_primary'] != '') {
				$email = $course['course_email_primary'];
			}
			else {
				$email = '-';
			}
			
			$html .= '
					<div class="user_image">
						<img src="' . $image . '" />
					</div>
					<div class="user_data">
						<h3>Course details</h3>
						<div class="user_label">
							' . constant('STREET') . '
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
						</div>
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
							<input type="button" value="' . constant('CHANGE') . '" class="update_course submit_button" id="' . $_SESSION['userid'] . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
			
		return $html;
	}
	
	public static function getCourseTypes($type_id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$types = $db->get('golfcourse_types');
		foreach ($types as $type) {
			$value = array(
					$type['course_type_id'] => 'GOLFCOURSE_TYPE_'.$type['course_type_title']
				);
			$values[] = $value;
		}
		$data = array(
				'table' => 'course_types',
				'name' => 'course_type',
				'title' => 'course_type',
				'id' => $type_id
			);
		$dd = Forms::getDropdown($data, $values, $type_id);
		return $dd;
	}
	
	public static function displayCourseByRating() {
		$reviews = Reviews::getTopReviews(10);
		//print_r($reviews);
		$html = '
				<h1>' . constant('COURSE_BY_RATING') . '</h1>
				<div class="review_wrapper">
			';
		$i = 0;
		foreach ($reviews as $review) {
			if ($i > 10) {
				continue;
			}
			$i++;
			$html .= '
					<div class="review'
				;
			if ($review['course_sponsor']) {
				$html .= ' is_sponsor';
			}
			if ($review['offers']) {
				$html .= ' has_offer';
			}
			$html .= '
						">
						<div class="review_place">
							' . $i . '.
						</div>
						<div class="review_course title">
							' . $review['course_name'] . ' ' . $review['offers'] . '
						</div>
						<div class="review_sum">
							' . $review['sum'] . ' / 5
						</div>
						<div class="review_votes">
							(' . $review['votes'] . ' ' . strtolower(constant('VOTES')) . ')
						</div>
						<div class="clear_both"></div>
						<div class="review_place">
							&nbsp;
						</div>
						<div class="review_course">
							' . $review['course_description'] . '
						</div>
						<div class="review_sum">
							&nbsp;
						</div>
						<div class="review_votes">
							&nbsp;
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		$html .= '
				</div>
			';
		return $html;
	}
	
	public static function getCourse($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('course_id', $id);
		return $db->getOne('golfcourses');
	}
	
	public static function getDistrictDropdown($id = null, $country = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$dd = '
				<select name="course_districtid" id="course_districtid" class="district_select">
			';
		if (isset($country)) {
			$db->where('district_countryid', $country);
			$districts = $db->get('districts');
			$dd .= '
					<option value="0">--- ' . constant('CHOOSE') . ' ' . constant('DISTRICT') . ' ---</option>
				';
			foreach ($districts as $district) {
				$dd .= '
						<option value="' . $district['district_id'] . '"';
				if ($id == $district['district_id']) {
					$dd .= ' selected="selected"';
				}
				$dd .= '
						>' .  $district['district_name'] . '</option>
					';
			}
		
		}
		else {
			$dd .= '
					<option value="0">--- ' . constant('CHOOSE') . ' ' . constant('COUNTRY') . ' ' . constant('FIRST') . ' ---</option>
				';
		}
		$dd .= '
				</select>
			';
		return $dd;
	}

}

?>