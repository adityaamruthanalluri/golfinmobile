<?php
/*************
Handles deals 

displayDealForm($id = null)
displayDealList()
getDealDetails($id)
displayDeal($id)
displayDealSummary($id)
displayDeals()
displayConnectionForm($action)

displaySupplierForm($id = null)
displaySupplierList()

*************/
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/navigation.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/map.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/mail.management.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$settings = Settings::getSettings();

if (isset($_POST['action'])) {
	$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	if ($isAjax) {
		$db = new MysqliDb($server, $user, $password, $database);
		switch ($_POST['action']) {
			case 'startpage':
				$db->where('dealType_id', $_POST['type']);
				$type = $db->getOne('deal_types');
				$data = array('deal_id' => $_POST['id']);
				$db->where('id', $_POST['type']);
				$db->update('deal_startpage', $data);
				$db->where('id', $_POST['type']);
				$result = $db->getOne('deal_startpage');
				$html = '<h4 id="prev_deal_title">' . constant($type['deal_type_startpage_constant']) . '</h4>' . Deals::displayDealSummary($result['deal_id']);
				echo $html;
				die();
				break;
			case 'admin_types':
				$db->where('dealType_id', $_POST['type']);
				$type = $db->getOne('deal_types');
				$db->where('id', $_POST['type']);
				$deal = $db->getOne('deal_startpage');
				$html = '<h4 id="prev_deal_title">' . constant($type['deal_type_startpage_constant']) . '</h4>' . Deals::displayDealSummary($deal['deal_id']);
				echo $html;
				die();
				break;
			case 'closeDealConfirm':
				$deal_id = $_SESSION['deal_id_confirming'];
				unset($_SESSION['deal_id_confirming']);
				//Update user data
				$fname = $_POST['fname'];
				$lname = $_POST['lname'];
				$phone = $_POST['phone'];
				$email = $_POST['email']; 
				$data = array(
						'user_first_name' => $fname,
						'user_last_name' => $lname,
						'user_phone' => $phone,
						'user_email' => $email
					);
				$db->where('user_id', $_SESSION['userid']); 
				$result = $db->update('users', $data);
				
				//Save confirmed deal
				$deal = Deals::getDealDetails($deal_id);
				$data2 = array(
						'dc_supplier' => 1,
						'dc_customer' => $_SESSION['userid'],
						'dc_dealid' => $deal_id,
						'dc_price' => $deal['deal_price'],
						'dc_currency' => $deal['deal_currency'],
						'dc_datetime' => date('Y-m-d H:i:s')
					);
				$db->insert('deals_confirmed', $data2);
				//Add 1 buy to deals tablett
				$db->where('deal_id', $deal_id);
				$add_buy = $db->getOne('deals');
				$buys = $add_buy['deal_buys_num'];
				$buys = array('deal_buys_num' => ($buys + 1));
				$db->where('deal_id', $deal_id);
				$db->update('deals', $buys);
				
				//Create html 
				$html = Deals::closeDeal($deal_id, true);
				
				$mailcontent = str_replace('<img src="', '<img src="http://' . $_SERVER['SERVER_NAME'], $html); 
				
				
				//Send mail
				$data['subject'] = constant('DEAL_CONF_RECIEPT');
				$data['from'] = $settings['setting_default_email'];
				$data['to'] = $email;
				$data['template'] = 'message';
				$data['message'] = $mailcontent;
				$mailsent = MailManagement::sendMail($data); 
				
				echo $html;
				
				die();
				break;
		}
	}
}

class Deals {
	public static function displayDealForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('deal_id', $id);
			$db->join('deal_suppliers', 'deal_owner=supplier_id', 'LEFT');
			$result = $db->getOne('deals');
			if ($_SESSION['admin_level'] < 201) {
				if ($owner != $result['deal_owner']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			if ($result['deal_limiter'] == 1) {
				$db->where('deal_id', $id);
				$datetime = $db->getOne('deal_time');
				$result['deal_start'] = $datetime['start'];
				$result['deal_end'] = $datetime['end'];
			}
			if ($result['deal_limiter'] == 2) {
				$db->where('deal_id', $id);
				$buys = $db->getOne('deal_buys');
				$result['deal_buys'] = $buys['deal_buys'];
			}
			if ($result['deal_type'] == 3) {
				$movie = $result['deal_movie'];
			}
			else {
				$movie = '';
			}
			$span_val_owner = $result['supplier_name'];
			$deal_limiter = $result['deal_limiter'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$movie = '';
			$span_val_owner = constant('DEAL_NO_OWNER');
			$deal_limiter = null;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$db->orderBy('dealType_title', 'ASC');
		$types_result = $db->get('deal_types', null, array('dealType_id', 'dealType_title'));
		foreach ($types_result as $type) {
			$types[$type['dealType_id']] = $type['dealType_title'];
		}
		$data['id'] = $result['deal_type'];
		$data['name'] = 'deal_type';
		$data['title'] = 'choose_type';
		$data['table'] = 'deal_types';
		$type_values[] = $types;
		$type_dd = Forms::getDropdown($data, $type_values, $result['deal_type']);
		$db->where('CurrencyCode != ""');
		$db->groupBy('CurrencyCode');
		$db->orderBy('Currency', 'ASC');
		$currencies_result = $db->get('app_countries', null, array('Currency', 'CurrencyCode'));
		foreach ($currencies_result as $currency) {
			$currencies[$currency['CurrencyCode']] = $currency['Currency'];
		}
		$data['id'] = $result['deal_currency'];
		$data['name'] = 'deal_currency';
		$data['title'] = 'choose_currency';
		$data['table'] = 'deals';
		$currency_values[] = $currencies;
		$currency_dd = Forms::getDropdown($data, $currency_values, $id);
		$data = array(
				'table' => 'deals',
				'identifier' => 'deal_id',
				'table_prefix' => 'deal_',
				'return_uri' => '/admin/deals/',
				'column' => 'deal_id',
				'required' => array('deal_type', 'deal_title', 'deal_original_price', 'deal_price', 'deal_limiter', 'deal_owner'),
				'datetime' => array ('deal_created','deal_changed'),
				'on_off' => array('deal_status'),
				'post_id' => $id,
				'deal_type' => $type_dd,
				'deal_limiter' => $deal_limiter,
				'deal_currency' => $currency_dd,
				'deal_owner' => array(
						'span_id' => 'deal_owner',
						'span_val' => $span_val_owner,
						'ac_var' => 'deal_suppliers',
						'button_id' => 'deal_owner_select'
					),
				'hide_row' => array('deal_start','deal_end','deal_buys'),
				'submit' => $submit,
				'db-action' => $db_action
			);
		switch ($deal_limiter) {
			case 1:
				$data['hide_row_choice'] = array('deal_start','deal_end');
				break;
			case 2:
				$data['hide_row_choice'] = array('deal_buys');
				break;
			default:
				$data['hide_row_choice'] = array();
				break;
		}
		$fields['deal_type'] = 'dropdown';
		$fields['deal_title'] = 'text';
		$fields['deal_image'] = 'file';
		$fields['deal_movie'] = 'text';
		$fields['deal_description'] = 'small_editor';
		$fields['deal_terms'] = 'small_editor';
		$fields['deal_original_price'] = 'text';
		$fields['deal_price'] = 'text';
		$fields['deal_currency'] = 'dropdown';
		$fields['deal_url'] = 'text';
		$fields['deal_limiter'] = 'multiple_radio';
		$fields['deal_start'] = 'datetime';
		$fields['deal_end'] = 'datetime';
		$fields['deal_buys'] = 'text';
		$fields['deal_status'] = 'check';
		if ($_SESSION['admin_level'] > 200) {
			$fields['deal_owner'] = 'autofill';
		}
		else {
			$fields['deal_owner'] = 'hidden';
		}
		$html = Forms::Form($data, $fields, $result);
		return $html;
	}
	
	public static function displayDealList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = Lists::listAutosearch('deal_', 'deal', true);
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
		$total = $db->get('deals');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('DEALS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if (($offset + $limit) > count($total)) {
				$last_post = count($total);
			}
			else {
				$last_post = ($offset + $limit);
			}
			$html .= '
					<div id="hits_info_container">
						' . constant('TOTAL_HITS') . ' ' . count($total) . ' | ' . constant('SHOWING') . ' ' . ($offset + 1) . ' - ' . $last_post . '
					</div>
				';
			if (isset($_GET['so'])) {
				switch ($_GET['so']) {
					case '1';
						$sortorder = 'deal_title';
						$sortarrow = 'ASC';
						$link_get = '?so=2';
						$link_out = constant('LATEST');
						break;
					case '2':
						$sortorder = 'deal_created';
						$sortarrow = 'DESC';
						$link_get = '?so=1';
						$link_out = strtolower(constant('TITLE'));
						break;
				}
			}
			else {
				$sortorder = 'deal_created';
				$sortarrow = 'DESC';
				$link_get = '?so=1';
				$link_out = constant('BY_NAME');
			}
			
			$html .= '
					<div id="sort_order">
						<img src="/_icons/icon_sort.png" title="' . constant('SORT_ORDER') . '" />
						' . constant('SORT_BY') . '
						<a href="' . $link_get . '">' . $link_out . '</a>
					</div>
				';
			$db->orderBy($sortorder, $sortarrow);
			$deals = $db->get('deals');
			foreach ($deals as $deal) {
				if ($deal['deal_owner'] > 0) {
					$deal_owner = self::getSupplier($deal['deal_owner']);
					$owner = $deal_owner['supplier_name'];
				}
				else {
					$owner = constant('ADMIN');
				}
				$deal_details = self::getDealDetails($deal['deal_id']);
				$column['ID'] = $deal['deal_id'];
				$column['TYPE'] = $deal['deal_type'];
				$column['TITLE'] = $deal['deal_title'];
				$column['SHITS'] = $deal['deal_shits'];
				$column['HITS'] = $deal['deal_hits'];
				$column['CLICKS'] = $deal['deal_clicks'];
				$column['CURRENCY'] = $deal['deal_currency'];
				if ($_SESSION['admin_level'] > 200) {
					$column['OWNER'] = $owner;
				}
				switch ($deal['deal_limiter']) {
					case 1:
						$deal_limiter = constant('TIME');
						break;
					case 2:
						$deal_limiter = constant('BUYS');
						break;
					default:
						$deal_limiter = 'N/A';
						break;
				}
				$column['DEAL_TYPE'] = $deal_limiter;
				$column['REMAINING'] = $deal_details['end_info'];
				
				$column['CREATED'] = $deal['deal_created'];
				$column['CHANGED'] = $deal['deal_changed'];

				
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID', 'SHITS', 'HITS', 'CLICKS', 'CURRENCY'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'deals',
					'identifier' => 'deal_id',
					'linked' => array('TITLE'),
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
	
	public static function getDealDetails($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('deal_id', $id);
		$deal = $db->getOne('deals'); 
		switch ($deal['deal_limiter']) {
			case 1:
				$db->where('deal_id', $id);
				$time = $db->getOne('deal_time');
				$start = strtotime($time['start']); //Start date
				$future = strtotime($time['end']); //End date.
				$timefromdb = strtotime(date('Y-m-d')); //Today
				if ($timefromdb > $future || $timefromdb < $start) {
					return false;
				}
				$timeleft = $future-$timefromdb;
				$daysleft = round((($timeleft/24)/60)/60); 
				$deal_end_info = $daysleft . ' ' . strtoupper(constant('DAYS')) . ' ' . strtoupper(constant('LEFT'));
				$deal_end_count = $daysleft;
				$total_time = $future - $start;
				$deal_left_percentage = 100 - (($daysleft / round((($total_time/24)/60)/60)) * 100);
				break;
			case '2':
				$db->where('deal_id', $id);
				$buys = $db->getOne('deal_buys');
				$buys_num = ($buys['deal_buys'] - $deal['deal_buys_num']);
				$deal_end_info = $buys_num . ' ' . strtoupper(constant('OF')) . ' ' . $buys['deal_buys'] . ' ' . strtoupper(constant('LEFT'));
				$deal_end_count = $buys_num;
				if ($buys_num == 0) {
					$deal_left_percentage = 100;
				}
				else {
					$percentage = ((int)$buys_num / (int)$buys['deal_buys']); 
					$deal_left_percentage = 100 - ($percentage * 100);
				}
				break;
		}
		$db->where('dealType_id', $deal['deal_type']);
		$dealtype = $db->getOne('deal_types');
		$deal['type_txt'] = $dealtype['dealType_title'];
		$deal['type'] = $deal['deal_type'];
		$deal['end_info'] = $deal_end_info;
		$deal['end_count'] = $deal_end_count;
		$deal['image'] = Common::getImage('/_deals/images/', $id);
		$deal['title'] = $deal['deal_title'];
		$deal['movie'] = $deal['deal_movie'];
		$deal['orgprice'] = $deal['deal_original_price'];
		$deal['price'] = $deal['deal_price'];
		$deal['description'] = $deal['deal_description'];
		$deal['terms'] = $deal['deal_terms'];
		$deal['procentage'] = $deal_left_percentage;
		if (defined($deal['deal_currency'])) {
			$deal['currency'] = constant($deal['deal_currency']);
		}
		else {
			$deal['currency'] = $deal['deal_currency'];
		}
		$procentage = 100 - (($deal['deal_price'] / $deal['deal_original_price']) * 100);
		if ($procentage == '100') {
			$procentage_deal = strtoupper(constant('FREE'));
		}
		else {
			$procentage_deal = number_format((float)$procentage, 0) . '%';
		}
		$deal['procentage_deal'] = $procentage_deal;
		if (strlen($deal['deal_url']) > 9) {
			$deal_url = '<a href="' . $deal['deal_url'] . '" target="_blank" title="' . constant('READ_MORE') . '">' . $deal['deal_url'] . '</a>';
		}
		else {
			$deal_url = '';
		}
		$deal['url'] = $deal_url;
		$db->where('supplier_id', $deal['deal_owner']);
		$supplier = $db->getOne('deal_suppliers');
		$deal['supplier'] = $supplier['supplier_name'];
		if (Common::getImage('/_deal_suppliers/images/', $deal['deal_owner']) != '0') {
			$deal['supplierimage'] = '<img src="' . Common::getImage('/_deal_suppliers/images/', $deal['deal_owner']) . '" title="' . $supplier['supplier_name'] . '" height="30" />';
		}
		else {
			$deal['supplierimage'] = '';
		}
		return $deal;
	}
	
	public static function displayDeal($id) {
		session_start();
		$deal = self::getDealDetails($id);
		if (!isset($_SESSION['userid']) && $deal['deal_type'] != 3) {
			return Common::display401();
			die();
		}
		if ($deal) {
			Navigation::addHit('deals', $id, 'hits'); 
		}
		switch ($deal['type']) {
			case 1:
				$deal_middle = $deal['end_info'];
				break;
			case 2:
				$deal_middle = mb_strtoupper(constant('RECOMMENDED_PRICE'),'utf-8') . ': ' . $deal['orgprice'] . ' ' . $deal['currency'];
				break;
			case 3:
				$deal_middle = $deal['end_info'];
				break;
		}
		$html = '
				<div class="deal_wrapper deal_details">
					<div class="deal_image">
						<img src="' . $deal['image'] . '" />
						<div class="deal_title">
							<h2>' . $deal['title'] . '</h2>
						</div>
					</div>
			';
		if ($deal['deal_type'] != 3) {
			$html .= '
					<div class="deal_row clear_both">
						<div class="deal_left green third">
							' . $deal['procentage_deal'] . '
						</div>
						<div class="deal_middle third">
							' . $deal_middle . '
						</div>
						<div class="deal_right third red">
							<span class="strikethrough">' . $deal['deal_original_price'] . ' ' . $deal['currency'] . '</span><br />
							' . $deal['price'] . ' ' . $deal['currency'] . '
						</div>
						<div class="right">
							<a href="?' . strtolower(str_replace(' ', '-', constant('CLOSE_DEAL'))) . '=' . $id . '" title="' . constant('CLOSE_DEAL') . '"><input type="button" value="' . strtoupper(constant('CLOSE_DEAL')) . '" /></a>
						</div>

					</div>
				';
		}
		if ($deal['supplier'] != '') {
			$html .= '
					<div class="deal_supplier clear_both">
						' . constant('SUPPLIED_BY') . ':
						' . $deal['supplierimage'] . '
						' . $deal['supplier'] . '
					</div>
				';
		}
		if ($deal['description'] != '') {
		$html .= '
					<div class="deal_description clear_both">
						<h3>' . constant('DESCRIPTION') . '</h3>
						' . $deal['description'] . '
					</div>
			';
		}
		if ($deal['terms'] != '') {
			$html .= '
					<div class="deal_terms">
						<h3>' . constant('TERMS') . '</h3>
						' . $deal['terms'] . '
					</div>
				';
		}
		if ($deal['url'] != '') {
			$html .= '
					<div class="left">
						' . constant('MORE_INFORMATION') . ': ' . $deal['url'] . '
					</div>
				';
		}
		$html .= '
				</div>
			';
		return $html;
	} 
	
	public static function displayDealSummary($id, $start = false) { 
		$deal = self::getDealDetails($id); 
		if (!$deal) {
			return '';
		}
		if (strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
			Navigation::addHit('deals', $id, 'shits'); 
		}
		switch ($deal['deal_type']) {
			case 1:
				$landing_page = str_replace(' ', '', strtolower(constant('GOLF_DEALS')));
				$deal_image_info = '<div class="right deal_image_info">
										<input type="button" value="' . constant('DEAL') . ': ' . $deal['procentage_deal'] . '" />
									</div>';
				$link_title = 'View deal';
				$percentage = $deal['procentage'];
				$deal_bottom_left = $deal['end_info'];
				$deal_bottom_right = '<span class="strikethrough">' . $deal['deal_original_price'] . ' ' . $deal['currency'] . '</span><br />
									 ' . $deal['price'] . ' ' . $deal['currency'];
				break;
			case 2:
				$landing_page = str_replace(' ', '', strtolower(constant('SHOP')));
				$percentage = 100;// - (($deal['price'] / $deal['orgprice']) * 100);
				//$deal['procentage'];
				$deal_image_info = '';
				/*<div class="right deal_image_info">
										<input type="button" value="' . constant('SHOP') . ': ' . $deal['price']  . ' ' . $deal['currency'] . '" />
									</div>';*/
				$link_title = 'View product';
				$deal_bottom_left = '&nbsp'; //mb_strtoupper(constant('RECOMMENDED_PRICE'),'utf-8') . ': ' . $deal['orgprice'] . ' ' . $deal['currency'];
				break;
			case 3:
				$landing_page = str_replace(' ', '', strtolower(constant('DESTINATIONS')));
				$deal_image_info = '';
				$link_title = 'View destination';
				$percentage = 100;
				if ($deal['url'] != '' && $deal['url'] != 'http://' && !$start) {
					$deal_bottom_left = $deal['url'] . ' &raquo'; //strtoupper(constant('FROM')) . ' ' . $deal['price'] . ' ' . $deal['currency'];
				}
				else {
					$deal_bottom_left = '&nbsp;';
				}
				break;
		}
		if ($start) {
			$link_title = 'View more';
			$page_link = '/' . $landing_page . '/' . '';
		}
		else {
			//$button_text = constant('READ_MORE');
			if (!isset($_SESSION['admin_level']) && $deal['deal_type'] != 3) {			
				$page_link = '#" class="doRegister" data-target="' . '/' . $landing_page . '/' . '?id=' . $id;
			}
			else {
				$page_link = $deal['deal_url'];
			}
		}
		
		
		//
		//
		
			$html .= '
					<div class="deal_summary_wrapper border">
						<div class="deal_inner">
				';
			if ($deal['movie'] == '' || $start) {
				$html .= '
							<a href="' . $page_link . '" title="' . $link_title . '">
								<div class="deal_image">
									<img src="' . $deal['image'] . '" />
									' . $deal_image_info  . '
									<div class="deal_title">
										' . $deal['deal_title'] . '
									</div>
								</div>
							</a>
					';
			}
			else {
				$html .= '
							<div class="deal_image">
								<div style="overflow:hidden;height:263px;width:351px;">
									<div id="youtube_canvas" style="height:263px;width:351px;">
										<iframe style="height:263px;width:351px;border:0;" frameborder="0" src="' . $deal['movie'] . '?hl=en&amp;autoplay=0&amp;cc_load_policy=0&amp;loop=0&amp;iv_load_policy=1&amp;fs=0&amp;showinfo=1"></iframe>
									</div>
									<a class="youtube-embed-code" href="http://www.tubeembed.com" id="get-youtube-data">tubeembed</a>
									<style>#youtube_canvas img{max-width:none!important;background:none!important}</style>
								</div>
									' . $deal_image_info  . '
								<div class="deal_title">
									' . $deal['deal_title'] . '
								</div>
							</div>
					';
			}
			$html .= '
							
							<div class="deal_row clear_both">
								<div class="deal_sum_bottom">
									' . $deal_bottom_left . '
								</div>
								<div class="feeder_wrapper">
									<div class="feeder_feed" style="width: ' . $percentage . '%"></div>
								</div>
							</div>
				';
			/*if ($start) {	
				$html .= '
							<div class="right deal_link">
								<a href="/' . str_replace(' ', '', strtolower(constant('GOLF_DEALS'))) . '/" title="' . constant('READ_MORE') . '">' . constant('READ_MORE') . ' &raquo;</a>
							</div>
						
					';
			}*/
			$html .= '	
						</div>
					</div>
			';
		
			
		return $html;
	}
	
	public static function displayDeals($type, $deviceType, $owner = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		$offset = $settings['setting_deals_offset']; 
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
		//Count deals 
		
		$db->where('deal_type', $type);
		$db->where('deal_status', 1);
		if (isset($owner)) {
			$db->where('deal_owner', $owner);
		}
		$totals = $db->get('deals'); 
		$total = count($totals);
		
		//Display Deals
		$html = '<h1>' . $title . '</h1>'; 
		$db->where('id', $type);
		$fdeal = $db->getOne('deal_startpage');
		$first_deal = $fdeal['deal_id'];
		
		$html .= self::displayDealSummary($first_deal);
		
		
		
		$db->where('deal_type', $type);
		$db->where('deal_status', 1);
		if (isset($owner)) {
			$db->where('deal_owner', $owner);
		}
		$db->orderBy('deal_id', 'DESC');
		$deals = $db->get('deals', array($offset, $limit));
		switch ($type) {
			case 1:
				$title = constant('GOLF_DEALS');
				break;
			case 2:
				$title = constant('SHOP');
				break;
			case 3:
				$title = constant('DESTINATIONS');
				break;
		}
		
		foreach ($deals as $item) { 
			if ($item['deal_id'] != $first_deal) {
				$html .= self::displayDealSummary($item['deal_id']);
			}
		}
		if (count($totals) > $limit) { 
			$html .= Navigation::buildPageNavigation($URI, null, $page, count($totals), $limit);
		}
		/*require_once($_SERVER['DOCUMENT_ROOT'].'/_php/Mobile_Detect.php');
		if ($type == 3) {
			if ($deviceType != 'phone') {
				$map_image = Map::displayMap();
				$html .= '
						<div id="start_image">
							' . $map_image . '
						</div>
					';
			}
			else {
				$html .= '
							<a href="/mobilemap" title="' . constant('CHECK_OUT_MAP') . '">
								<img src="/_icons/map_icon.png" style="width:100%;" />
							</a>
						';
			}
		}*/
		
		
		
		return $html;
	}
	
	public static function closeDeal($id, $confirmed = false) { 
		if (!isset($_SESSION['userid'])) {
			return Common::display401();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		Navigation::addHit('deals', $id, 'clicks'); 
		
		//Get user
		$db->where('user_id', $_SESSION['userid']);
		$user = $db->getOne('users');
		$user_firstname = $user['user_first_name'];
		$user_lastname = $user['user_last_name'];
		$user_phone = $user['user_phone'];
		$user_email = $user['user_email'];
		if ($user_firstname == '') {
			$user_firstname = constant('FIRST_NAME');
		}
		if ($user_lastname == '') {
			$user_lastname = constant('LAST_NAME');
		}
		if ($user_phone == '') {
			$user_phone = constant('PHONE');
		}
		$deal = self::displayDealSummary($id, false); 
		$deal_info = self::getDealDetails($id);
		
		$type_txt = $deal_info['type_txt'];
		$type = $deal_info['deal_type'];
		$title = $deal_info['deal_title'];
		$price = $deal_info['deal_price'];
		$currency = $deal_info['deal_currency'];
		if ($confirmed) {
			//Create QR code
			include($_SERVER['DOCUMENT_ROOT'].'/_php/qrcode/phpqrcode/qrlib.php'); 
	    	$tempDir = $_SERVER['DOCUMENT_ROOT'].'/_php/qrcode/temp/'; 
 			//Create QR code
    		$codeContents = '
SKIINGINMOBILE ' . $type_txt . ' item # ' . $id . '
' . strtoupper($title) . '
' . constant('PRICE') . ': ' . $price . '  ' . $currency . '
' . constant('NAME') . ': ' . $user_firstname . ' ' . $user_lastname . '
' . constant('EMAIL') . ': ' . $user_email . '
' . constant('PHONE') . ': ' . $user_phone . '

Date: ' . date('Y-m-d H:i:s') . '
    		';
    		$qrfile = date('Y-m-d H:i:s') . '_' . $id . '.png';
			QRcode::png($codeContents, $tempDir.$qrfile, QR_ECLEVEL_L, 3); 
			$qr_image =  '<img src="/_php/qrcode/temp/'.$qrfile.'" />'; 
			$qr_text = constant('QR_INFO');
		}
		else {
			$qr_image =  '<img src="/_icons/qrcode-ex.png" />'; 
			$qr_text = constant('QR_UNCONFIRMED');
		}
 		
 		
 		//Display deal summary
		
		session_start();
		$_SESSION['deal_id_confirming'] = $id;
		
		$html = '
				<div id="deal_reciept_wrapper">
					<div id="deal_reciept">
						<div class="reciept_header">
							<h1>' . mb_strtoupper(constant('WE_GOT_A_DEAL'), 'utf-8') . '</h1>
							<img src="/_icons/logotype.png" title="SkiinginMobile Deal" />
						</div>
						<div class="column">
							' . $deal . '
						</div>
						<div class="column">
							<div id="qr_code">
								' . $qr_image . '
							</div>
							<div class="qr_info">
								' . $qr_text . '
							</div>
						</div>
						<div id="deal_confirmation_form">
							<h2>' . constant('PERSONAL_INFORMATION') . '</h2>
			';
		if (!$confirmed) {
			$html .= '
							
							<div class="form_input">
								<input type="text" name="firstname" id="firstname" value="' . $user_firstname . '" />
							</div>
							<div class="form_input">
								<input type="text" name="lastname" id="lastname" value="' . $user_lastname . '" />
							</div>
							<div class="form_input">
								<input type="text" name="phone" id="phone" value="' . $user_phone . '" />
							</div>
							<div class="form_input">
								<input type="email" name="email" id="email" value="' . $user_email . '" />
							</div>
							<div class="form_input">
								<input type="checkbox" name="confirm_pdata" id="confirm_pdata"  />
								<label class="choice" for="private">
									' . constant('CONFIRM_PDATA') . '
								</label>
							</div>
							<div class="form_input">
								<input type="checkbox" name="confirm_agree" id="confirm_agree"  />
								<label class="choice" for="private">
									' . constant('CONFIRM_AGREEMENT') . '
								</label>
							</div>
							<div class="form_input">
								<input type="submit" id="deal_confirmed_submit" value="' . constant('SUBMIT') . '" />
							</div>
				';
			}
			else {
				$html .= '
							<div class="form_input">
								' . $user_firstname . ' ' . $user_lastname . '
							</div>
							<div class="form_input">
								' . $user_phone . '
							</div>
							<div class="form_input">
								' . $user_email . '
							</div>
					';
			}
			$html .= '
						</div>
					</div>
					<div class="clear: both"></div>
				</div>
			';
		
		
		
		return $html;
	}
	
	public static function displayConnectionForm($action) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		//Types
		$types = $db->get('deal_types');
		$type_form .= '';
		foreach ($types as $type) {
			$type_form .= '
					<input type="checkbox" name="' . $type['dealType_title'] . '" id="' . $type['dealType_id'] . '" class="deal_prev_types"
				';
			if ($type['dealType_id'] == 1) {
				$type_form .= '
						checked="checked"';
			}
			$type_form .= ' 
					/>
					<label class="choice" for="private">
						' . $type['dealType_title'] . '
					</label>
				';
		}
		//Deal to preview
		$db->where('id', 1);
		$result = $db->getOne('deal_startpage');
		$html = '
				<h3>' . constant('CURRENT') . '</h3>
				<div class="current">
					<h4 id="prev_deal_title">' . constant('STARTPAGE_GOLFDEAL') . '</h4>
					<div id="prev_deal_content">
						' . self::displayDealSummary($result['deal_id']) . '
					</div>
				</div>
				<div class="change_form">
					' . $type_form . '
					<h3>' . constant('CHOOSE') . ' ' . constant('DEAL') . '</h3>
					<input type="button" id="startpage_deal" value="' . constant('CHOOSE') . '" />
					<input type="text" id="deal" class="conn_auto ui-autocomplete-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
				</div>
			';
		return $html;
	}
	
	public static function confirmedDeals() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			/*$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('DEAL')) . '
						</div>
						<div class="form_input">
							<input type="text" id="deal_confirmed" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="deal_confirmed_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="deal_confirmed_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		else {
			$html = '';
			*/
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
		$total = $db->get('deals_confirmed');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('DEALS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$db->orderBy('dc_id', 'DESC');
			$deals = $db->get('deals_confirmed');
			foreach ($deals as $deal) {
				$db->where('supplier_id', $deal['dc_supplier']);
				$supp = $db->getOne('deal_suppliers');
				$supplier = $supp['supplier_name'] . ', (' . $deal['dc_supplier'] . ')';
				
				$db->where('user_id', $deal['dc_customer']);
				$cust = $db->getOne('users');
				$customer = $cust['user_first_name'] . ' ' . $cust['user_last_name'] . ', (' . $deal['dc_customer'] . ')';
				
				$db->where('deal_id', $deal['dc_dealid']);
				$dealen = $db->getOne('deals');
				$thedeal = $dealen['deal_title'] . ', (' . $dealen['deal_id'] . ')';
				
				$column['ID'] = $deal['dc_id'];
				$column['SUPPLIER'] = $supplier;
				$column['CUSTOMER'] = $customer;
				$column['DEAL'] = $thedeal;
				$column['PAID'] = $deal['dc_status'];
				$column['PRICE'] = $deal['dc_price'] . ' ' . $deal['dc_currency'];
				$column['CREATED'] = $deal['dc_datetime'];
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED'),
					'bool' => array('PAID'),
					'ajax-edit'  => array('PAID'),
					'table' => 'deals_confirmed',
					'identifier' => 'dc_id',
					'db_action' => '-'
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
	
	public static function displaySupplierForm($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('supplier_id', $id);
			$result = $db->getOne('deal_suppliers');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$result['supplier_type'] = 0;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		
		$db->orderBy('dst_type', 'ASC');
		$types_result = $db->get('deal_supplier_types', null, array('dst_id', 'dst_type'));
		foreach ($types_result as $type) {
			$types[$type['dst_id']] = $type['dst_type'];
		}
		$data['id'] = $result['supplier_type'];
		$data['name'] = 'supplier_type';
		$data['title'] = 'choose_type';
		$data['table'] = 'deal_supplier_types'; 
		$types_list = array($types);
		$type_dd = Forms::getDropdown($data, $types_list, $result['supplier_type']);//print_r($types);die();
		$data = array(
				'table' => 'deal_suppliers',
				'identifier' => 'supplier_id',
				'table_prefix' => 'dsupplier_',
				'return_uri' => '/admin/suppliers/',
				'column' => 'supplier_id',
				'required' => array('supplier_type', 'supplier_name', 'supplier_email', 'supplier_phone'),
				'datetime' => array ('supplier_created','supplier_changed'),
				'post_id' => $id,
				'supplier_type' => $type_dd,
				'submit' => $submit,
				'db-action' => $db_action
			);
		$fields = array(
				'supplier_type' => 'dropdown',
				'supplier_name' => 'text',
				'supplier_image' => 'file',
				'supplier_email' => 'email',
				'supplier_phone' => 'text'
			);
		$html = Forms::Form($data, $fields, $result);
		return $html;
	}
	
	public static function displaySupplierList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = Lists::listAutosearch('deal_suppliers', 'deal_suppliers', true);
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
		$total = $db->get('deal_suppliers');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('SUPPLIERS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if (($offset + $limit) > count($total)) {
				$last_post = count($total);
			}
			else {
				$last_post = ($offset + $limit);
			}
			$html .= '
					<div id="hits_info_container">
						' . constant('TOTAL_HITS') . ' ' . count($total) . ' | ' . constant('SHOWING') . ' ' . ($offset + 1) . ' - ' . $last_post . '
					</div>
				';
			if (isset($_GET['so'])) {
				switch ($_GET['so']) {
					case '1';
						$sortorder = 'supplier_name';
						$sortarrow = 'ASC';
						$link_get = '?so=2';
						$link_out = constant('LATEST');
						break;
					case '2':
						$sortorder = 'supplier_created';
						$sortarrow = 'DESC';
						$link_get = '?so=1';
						$link_out = strtolower(constant('BY_NAME'));
						break;
				}
			}
			else {
				$sortorder = 'supplier_created';
				$sortarrow = 'DESC';
				$link_get = '?so=1';
				$link_out = constant('BY_NAME');
			}
			
			$html .= '
					<div id="sort_order">
						<img src="/_icons/icon_sort.png" title="' . constant('SORT_ORDER') . '" />
						' . constant('SORT_BY') . '
						<a href="' . $link_get . '">' . $link_out . '</a>
					</div>
				';
			$db->orderBy($sortorder, $sortarrow);
			$suppliers = $db->get('deal_suppliers');
			foreach ($suppliers as $supplier) {
				$db->where('dst_id', $supplier['supplier_type']);
				$supplier_type = $db->getOne('deal_supplier_types');
				$column['ID'] = $supplier['supplier_id'];
				$column['NAME'] = $supplier['supplier_name'];
				$column['TYPE'] = constant($supplier_type['dst_type']);
				$column['CREATED'] = $supplier['supplier_created'];
				$column['CHANGED'] = $supplier['supplier_changed'];
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'deal_suppliers',
					'identifier' => 'supplier_id',
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
			return $html;
		}
	}
	
	public static function getSupplier($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('supplier_id', $id);
		$supplier = $db->getOne('deal_suppliers');
		return $supplier;
	}
	
}