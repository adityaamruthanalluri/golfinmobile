<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');

class Restaurants {

	public static function displayRestaurantForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] < 201) {
			$db->where('club_gkadministrator', $_SESSION['userid']);
			$club = $db->getOne('golfclubs'); 
			$owner = $club['club_id'];
		}
		
		if (isset($id)) {
			$db->where('restaurant_id', $id);
			$result = $db->getOne('restaurants');
			if ($_SESSION['admin_level'] < 201) {
				if ($owner != $result['restaurant_owner']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$type_id = $result['restaurant_type'];
			$city = $result['restaurant_cityid'];
			$region = $result['restaurant_regionid'];
			$country = $result['restaurant_countryid'];
			$street = $result['restaurant_street'];
			$zip = $result['restaurant_zip'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$account_id = null;
			$type_id = 0;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
			if ($_SESSION['admin_level'] < 201) {
				$city = $club['club_cityid'];
				$region = $club['club_regionid'];
				$country = $club['club_countryid'];
				$result['restaurant_street'] = $club['club_street'];
				$result['restaurant_zip'] = $club['club_zip'];
			}
		}
		$type_dd = self::getRestaurantTypeDropdown($type_id);
		$data = array(
				'table' => 'restaurants',
				'identifier' => 'restaurant_id',
				'table_prefix' => 'restaurant_',
				'required' => array (
						'restaurant_name', 
						'restaurant_street', 
						'restaurant_zip', 
						'restaurant_cityid', 
						'restaurant_regionid', 
						'restaurant_countryid', 
						'restaurant_email', 
						'restaurant_phone', 
						'restaurant_type'
					),
				'post_id' => $id,
				'on_off' => array('restaurant_status', 'restaurant_sponsor', 'restaurant_gcconn', 'restaurant_hotelconn'),
				'restaurant_id' => $id,
				'restaurant_countryid' => $country,
				'restaurant_regionid' => $region,
				'restaurant_cityid' => $city,
				'short_desc' => array('restaurant_description'),
				'restaurant_type' => $type_dd,
				'restaurant_owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
		
		if ($_SESSION['admin_level'] > 200) {
			$fields['restaurant_owner'] = 'text';
			$fields['restaurant_lng'] = 'text';
			$fields['restaurant_lat'] = 'text';
			$fields['restaurant_sponsor'] = 'check';
			$data['return_uri'] = '/admin/restaurants/';
		}
		else {
			$data['return_uri'] = '/admin/';
			$fields['restaurant_owner'] = 'hidden';
		}
		$fields['restaurant_type'] = 'dropdown';
		$fields['restaurant_gcconn'] = 'check';
		$fields['restaurant_hotelconn'] = 'check';
		$fields['restaurant_name'] = 'text';
		$fields['restaurant_image'] = 'file';
		$fields['restaurant_description'] = 'textarea';
		$fields['restaurant_countryid'] = 'location';
		$fields['restaurant_regionid'] = 'location';
		$fields['restaurant_cityid'] = 'location';
		$fields['restaurant_street'] = 'text';
		$fields['restaurant_zip'] = 'text';
		$fields['restaurant_phone'] = 'text';
		$fields['restaurant_email'] = 'email';
		$fields['restaurant_url'] = 'text';
		if ($_SESSION['admin_level'] > 200) {
			$fields['restaurant_status'] = 'check';
			$fields['restaurant_owner'] = 'text';
		}
		else {
			$fields['restaurant_owner'] = 'hidden';
		}
		$html = Forms::Form($data, $fields, $result);
		return $html;
	
	}

	public static function displayRestaurantList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('RESTAURANT')) . '
						</div>
						<div class="form_input">
							<input type="text" id="restaurant" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="restaurant_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="restaurant_view"  value="' . constant('VIEW') . '" />
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
		$total = $db->get('restaurants');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('RESTAURANTS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if ($_SESSION['admin_level'] < 201) {
				$db->where('club_gkadministrator', $_SESSION['userid']);
				$result = $db->getOne('golfclubs');
				$golfclub = $result['club_id'];
				$db->where('restaurant_owner', $golfclub);
			}
			$db->orderBy('restaurant_name', 'ASC');
			$restaurants = $db->get('restaurants', array($offset, $limit));
			foreach ($restaurants as $restaurant) {
				$city = Common::getCity($restaurant['restaurant_cityid']);
				$country = Common::getCountry($restaurant['restaurant_countryid']);
				$column = array(
						'ID' => $restaurant['restaurant_id'],
						'NAME' => $restaurant['restaurant_name'], 
						'STREET' => $restaurant['restaurant_street'],
						'CITY' => $city,
						'COUNTRY' => $country,
						'LOCATION' => $restaurant['restaurant_lng'] . ' : ' . $restaurant['restaurant_lat'],
						'CREATED' => $restaurant['restaurant_created'],
						'CHANGED' => $restaurant['restaurant_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'restaurants',
					'identifier' => 'restaurant_id',
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
	
	public static function displayRestaurantView($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('restaurant_id', $id);
		$restaurant = $db->getOne('restaurants'); 
		$image = Common::getImage('/_restaurants/images/', $restaurant['restaurant_id']);
		if ($image != '0') {
			$image_elem = '<img src="' . $image . '" title="' . $restaurant['restaurant_name'] . '" />';
			
		}
		else {
			$image_elem = '';
		}
		
		$html = '
				<div id="article" class="restaurant">
					<div id="article_wrapper">
						<h1>' . $restaurant['restaurant_name'] . '</h1>
						' . $image_elem . '
					</div>
				</div>
				<div id="restaurant_info_wrapper">
					<div id="restaurant_info_content">
						
					</div>
				</div>
			';
		return $html;
	}
	
	public static function getRestaurants($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('restaurant_owner', $id); 
		$restaurants = $db->get('restaurants');
		return $restaurants;
	}
	
	public static function getRestaurantTypeDropdown($type_id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$types = $db->get('restaurant_types');
		foreach ($types as $type) {
			$value = array(
					$type['restaurant_type_id'] => 'RESTAURANT_TYPE_'.$type['restaurant_type_title']
				);
			$values[] = $value;
		}
		$data = array(
				'table' => 'restaurant_types',
				'name' => 'restaurant_type',
				'title' => 'restaurant_type',
				'id' => $type_id
			);
		$dd = Forms::getDropdown($data, $values, $type_id);
		return $dd;
	}
				

}

?>