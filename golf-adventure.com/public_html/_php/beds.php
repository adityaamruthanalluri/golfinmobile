<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');

class Beds {

	public static function displayBedForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] < 201) {
			$db->where('club_gkadministrator', $_SESSION['userid']);
			$club = $db->getOne('golfclubs'); 
			$owner = $club['club_id'];
		}
		if (isset($id)) {
			$db->where('bed_id', $id);
			$result = $db->getOne('beds');
			if ($_SESSION['admin_level'] < 201) {
				if ($owner != $result['bed_owner']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$type_id = $result['bed_type'];
			$city = $result['bed_cityid'];
			$region = $result['bed_regionid'];
			$country = $result['bed_countryid'];
			$street = $result['bed_street'];
			$zip = $result['bed_zip'];
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
				$result['bed_street'] = $club['club_street'];
				$result['bed_zip'] = $club['club_zip'];
			}
		}
		$type_dd = self::getBedTypeDropdown($type_id);
		$data = array(
				'table' => 'beds',
				'identifier' => 'bed_id',
				'table_prefix' => 'bed_',
				'required' => array (
						'bed_name', 
						'bed_street', 
						'bed_zip', 
						'bed_cityid', 
						'bed_regionid', 
						'bed_countryid', 
						'bed_email', 
						'bed_phone', 
						'bed_type'
					),
				'post_id' => $id,
				'on_off' => array('bed_status', 'bed_sponsor','bed_gcconn', 'bed_restaurantconn'),
				'bed_id' => $id,
				'bed_countryid' => $country,
				'bed_regionid' => $region,
				'bed_cityid' => $city,
				'short_desc' => array('bed_description'),
				'bed_type' => $type_dd,
				'bed_owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
		if ($_SESSION['admin_level'] > 200) {
			$data['return_uri'] = '/admin/beds/';
			$fields['bed_owner'] = 'text';
			$fields['bed_lng'] = 'text';
			$fields['bed_lat'] = 'text';
			$fields['bed_sponsor'] = 'check';
		}
		else {
			$data['return_uri'] = '/admin/';
			$fields['bed_owner'] = 'hidden';
		}
		$fields['bed_type'] = 'dropdown';
		$fields['bed_gcconn'] = 'check';
		$fields['bed_restaurantconn'] = 'check';
		$fields['bed_name'] = 'text';
		$fields['bed_image'] = 'file';
		$fields['bed_description'] = 'textarea';
		$fields['bed_countryid'] = 'location';
		$fields['bed_regionid'] = 'location';
		$fields['bed_cityid'] = 'location';
		$fields['bed_street'] = 'text';
		$fields['bed_zip'] = 'text';
		$fields['bed_phone'] = 'text';
		$fields['bed_email'] = 'email';
		$fields['bed_url'] = 'text';
		if ($_SESSION['admin_level'] > 200) {
			$fields['bed_owner'] = 'text';
			$fields['bed_status'] = 'check';
		}
		else {
			$fields['bed_owner'] = 'hidden';
		}
		$html = Forms::Form($data, $fields, $result);
		return $html;
	
	}

	public static function displayBedList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('BED')) . '
						</div>
						<div class="form_input">
							<input type="text" id="bed" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="bed_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="bed_view"  value="' . constant('VIEW') . '" />
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
		$total = $db->get('beds');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('BEDS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if ($_SESSION['admin_level'] < 201) { 
				$db->where('club_gkadministrator', $_SESSION['userid']);
				$result = $db->getOne('golfclubs');
				$golfclub = $result['golf_id'];
				$db->where('bed_owner', $golfclub);
			}
			$db->orderBy('bed_name', 'ASC');
			$beds = $db->get('beds', array($offset, $limit));
			foreach ($beds as $bed) {
				$city = Common::getCity($bed['bed_cityid']);
				$country = Common::getCountry($bed['bed_countryid']);
				$column = array(
						'ID' => $bed['bed_id'],
						'NAME' => $bed['bed_name'], 
						'STREET' => $bed['bed_street'],
						'CITY' => $city,
						'COUNTRY' => $country,
						'LOCATION' => $bed['bed_lng'] . ' : ' . $bed['bed_lat'],
						'CREATED' => $bed['bed_created'],
						'CHANGED' => $bed['bed_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'beds',
					'identifier' => 'bed_id',
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
	
	public static function displayBedView($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('bed_id', $id);
		$bed = $db->getOne('beds'); 
		$image = Common::getImage('/_beds/images/', $bed['bed_id']);
		if ($image != '0') {
			$image_elem = '<img src="' . $image . '" title="' . $bed['bed_name'] . '" />';
			
		}
		else {
			$image_elem = '';
		}
		
		$html = '
				<div id="article" class="bed">
					<div id="article_wrapper">
						<h1>' . $bed['bed_name'] . '</h1>
						' . $image_elem . '
					</div>
				</div>
				<div id="bed_info_wrapper">
					<div id="bed_info_content">
						
					</div>
				</div>
			';
		return $html;
	}
	
	public static function getBeds($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('bed_owner', $id); 
		$beds = $db->get('beds');
		return $beds;
	}
	
	public static function getBedTypeDropdown($type_id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$types = $db->get('bed_types');
		foreach ($types as $type) {
			$value = array(
					$type['bed_type_id'] => 'BED_TYPE_'.$type['bed_type_title']
				);
			$values[] = $value;
		}
		$data = array(
				'table' => 'bed_types',
				'name' => 'bed_type',
				'title' => 'bed_type',
				'id' => $type_id
			);
		$dd = Forms::getDropdown($data, $values, $type_id);
		return $dd;
	}
				

}

?>