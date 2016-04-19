<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

class Shops {

	public static function displayShopForm($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('shop_id', $id);
			$result = $db->getOne('shops');
			$city = $result['shop_cityid'];
			$region = $result['shop_regionid'];
			$country = $result['shop_countryid'];
			$street = $result['shop_street'];
			$zip = $result['shop_zip'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'shops',
				'identifier' => 'shop_id',
				'table_prefix' => 'shop_',
				'return_uri' => '/admin/shops/',
				'column' => 'shop_id',
				'post_id' => $id,
				'required' => array (
						'shop_name', 
						'shop_street', 
						'shop_zip', 
						'shop_cityid', 
						'shop_regionid', 
						'shop_countryid', 
						'shop_email',
						'shop_phone',
						'shop_mobile'
						
					),
				'on_off' => array('shop_status'),
				'shop_countryid' => $country,
				'shop_regionid' => $region,
				'shop_cityid' => $city,
				'submit' => $submit,
				'db-action' => $db_action
			);
		$fields['shop_name'] = 'text';
		$fields['shop_image'] = 'file';
		$fields['shop_countryid'] = 'location';
		$fields['shop_regionid'] = 'location';
		$fields['shop_cityid'] = 'location';
		$fields['shop_street'] = 'text';
		$fields['shop_zip'] = 'text';
		$fields['shop_phone'] = 'text';
		$fields['shop_mobile'] = 'text';
		$fields['shop_email'] = 'email';
		$fields['shop_url'] = 'text';
		$fields['shop_status'] = 'check';
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayShopList($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
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
		$total = $db->get('shops');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('REWARDS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			
			$db->orderBy('shop_name', 'ASC');
			$shops = $db->get('shops', array($offset, $limit));
			foreach ($shops as $shop) {
				$column = array(
						'ID' => $shop['shop_id'],
						'SHOP' => $shop['shop_name'], 
						'CREATED' => $shop['shop_created'],
						'CHANGED' => $shop['shop_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'shops',
					'identifier' => 'shop_id',
					'linked' => array('SHOP'),
					'db_action' => 'delete'
				);
			$html = Lists::createList($columns, $data);
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
	
	public static function getShop($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('shop_id', $id); 
		$shop = $db->getOne('shops');
		$shop['shop_city'] = Common::getCity($shop['shop_cityid']);
		$shop['shop_region'] = Common::getRegion($shop['shop_regionid']);
		$shop['shop_country'] = Common::getCountry($shop['shop_countryid']);
		return $shop;
	}
	
	public static function getShopDetails($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$shop = self::getShop($id);
		$html = '';
		if ($shop['shop_mobile'] == '') {
			$html .= '
					<div class="warning">
						<p>' . constant('NO_SHOP_MOBILE') . '</p>
					</div>
				';
		}
		$image = Common::getImage('/_shops/images/', $id);
			if ($image == '0') {
				$image = '/_users/images/user_' . $_SESSION['admin_level'] . '.png';
			}
			if ($shop['shop_street'] != '') {
				$street = $shop['shop_street'];
			}
			else {
				$street = '-';
			}
			if ($shop['shop_zip'] != '') {
				$zip = $shop['shop_zip'];
			}
			else {
				$zip = '-';
			}
			if ($shop['shop_city'] != '') {
				$city = $shop['shop_city'];
			}
			else {
				$city = '-';
			}
			
			if ($shop['shop_country'] != '') {
				$country = $shop['shop_country'];
			}
			else {
				$city = '-';
			}
			
			if ($shop['shop_phone'] != '') {
				$phone = $shop['shop_phone'];
			}
			else {
				$phone = '-';
			}
			
			if ($shop['shop_mobile'] != '') {
				$mobile = $shop['shop_mobile'];
			}
			else {
				$mobile = '-';
			}
			
			if ($shop['shop_email'] != '') {
				$email = $shop['shop_email'];
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
							' .constant('COUNTRY') . '
						</div>
						<div id="user_name" class="user_value">
							' . $country . '
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
							<input type="button" value="' . constant('CHANGE') . '" class="update_shop submit_button" id="' . $_SESSION['userid'] . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		return $html;
	}

}



?>