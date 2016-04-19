<?php
//displayConnectionForm($action)
//displayOffer($page, $id = null)
//displayOfferForm($id = null)
//displayOfferList()
//getOfferType($type_id)
//getOffertypeDropdown($type_id)
//matchOffers($id)
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/clubs.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST)) {
	switch ($_POST['action']) {
		case 'update_article_offers': 
			$article = $_POST['parent'];
			$offers = explode(',', substr($_POST['posts'], 0, -2));
			$db->where('ao_article_id', $_POST['article']);
			$db->delete('article_offers');
			$i = 0;
			foreach ($offers as $offer) {
				if ($offer > 0) {
					$i++;
					$data = array('ao_article_id' => $article,
							'ao_offer_id' => $offer,
							'ao_place' => $i
						);
					$result = $db->insert('article_offers', $data);
				}
			}
			break;
		case 'get_articles_offers':
			$cols = array('ao_article_id',
					'ao_offer_id',
					'ao_place',
					'offer_title'
				);
			$db->where('ao_article_id', $_POST['article']);
			$db->join('offers',' offers.offer_id=article_offers.ao_offer_id', 'LEFT');
			$db->orderBy('ao_place', 'ASC');
			$result = $db->get('article_offers', null, $cols);
			if (is_array($result)) {
				$html = '';
				foreach ($result as $item) {
					$html .= '
							<div id="post_' . $item['ao_offer_id'] . '" class="choosen_post">
								<span id="' . $item['ao_place'] . '" class="conn_place">' . $item['ao_place'] . '</span>' . $item['offer_title'] . '<span id="delete_'  . $item['ao_offer_id'] . '" class="delete_conn_post">X</span>
							</div>
						';
				}
				echo $html;
				die();
			}
			break;
		case 'update_startpage_offers':
			$db->delete('startpage_offers');
			$offers = explode(',', substr($_POST['posts'], 0, -2));
			$i = 0;
			foreach ($offers as $offer) {
				if ($offer > 0) {
					$i++;
					$data = array('so_offer_id' => $offer,
							'so_place' => $i
						);
					$result = $db->insert('startpage_offers', $data);
				}
			}
			echo $result;
			break;
		case 'update_sidebar_offers':
			$db->delete('sidebar_offers');
			$offers = explode(',', substr($_POST['posts'], 0, -2));
			$i = 0;
			foreach ($offers as $offer) {
				if ($offer > 0) {
					$i++;
					$data = array('sb_offer_id' => $offer,
							'sb_place' => $i
						);
					$result = $db->insert('sidebar_offers', $data);
				}
			}
			echo $result;
			break;
	}
}

class Offers {

	public static function displayConnectionForm($action) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		switch ($action) {
			case '1':
				$db->join('offers',' offers.offer_id=startpage_offers.so_offer_id', 'LEFT');
				$db->orderBy('so_place', 'ASC');
				$result = $db->get('startpage_offers', null, $cols);
				foreach ($result as $item) {
					$offers .= '
						<div id="post_' . $item['so_offer_id'] . '" class="choosen_post">
							<span id="' . $item['so_place'] . '" class="conn_place">' . $item['so_place'] . '</span>' . $item['offer_title'] . '<span id="delete_'  . $item['so_offer_id'] . '" class="delete_conn_post">X</span>
						</div>
					';
				}
				$data['action'] = '';
				$data['save_action'] = 'update_startpage_offers';
				$data['post_id'] = 'offer';
				$data['post'] = constant('OFFER');
				$data['posts'] = constant('OFFERS');
				$data['has_parent'] = false;
				$data['parent_id'] = '';
				$data['parent'] = constant('STARTPAGE');
				$data['parents'] = '';
				$data['parent_url'] = '_/php/offers';
				$data['post_list'] = $offers;
				break;
			case '2':
				$data['action'] = 'get_articles_offers';
				$data['save_action'] = 'update_article_offers';
				$data['post_id'] = 'offer';
				$data['post'] = constant('OFFER');
				$data['posts'] = constant('OFFERS');
				$data['has_parent'] = true;
				$data['parent_id'] = 'article';
				$data['parent'] = constant('ARTICLE');
				$data['parents'] = constant('ARTICLES');
				$data['parent_url'] = '_/php/offers';
				$data['post_list'] = null;
				break;
			case '3':
				$db->join('offers',' offers.offer_id=sidebar_offers.sb_offer_id', 'LEFT');
				$db->orderBy('sb_place', 'ASC');
				$result = $db->get('sidebar_offers', null, $cols);
				foreach ($result as $item) {
					$offers .= '
						<div id="post_' . $item['sb_offer_id'] . '" class="choosen_post">
							<span id="' . $item['sb_place'] . '" class="conn_place">' . $item['sb_place'] . '</span>' . $item['offer_title'] . '<span id="delete_'  . $item['sb_offer_id'] . '" class="delete_conn_post">X</span>
						</div>
					';
				}
				$data['action'] = '';
				$data['save_action'] = 'update_sidebar_offers';
				$data['post_id'] = 'offer';
				$data['post'] = constant('OFFER');
				$data['posts'] = constant('OFFERS');
				$data['has_parent'] = false;
				$data['parent_id'] = '';
				$data['parent'] = constant('SIDEBAR');
				$data['parents'] = '';
				$data['parent_url'] = '_/php/offers';
				$data['post_list'] = $offers;
				break;
		}
		$html = Forms::connectionForm($data);
		return $html;
	}

	public static function displayOffer($offer, $data, $uri) { 
		Navigation::addHit('offers', $offer['offer_id']);
		$html = '
				<div class="offer_wrapper">
					<span class="offer_indicator">[' . constant('AD') . ']</span>
					<a href="/_php/redirect.php/?url=' . $offer['offer_url'] . '&offer=' . $offer['offer_id'] . '" target="_blank">
						<div class="offer_image">
							<img src="' . $offer['offer_image'] . '" class="offer" />
						</div>
					</a>
				</div>
			';
		return $html;
	}
	
	public static function displayOffers($type, $max = null, $page = null) {  
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($_POST['category'])) {
			$db->where('category_title_' . $_SESSION['site_language'], $_POST['category']);
			$cat = $db->getOne('categories');
			$cat_id = $cat['category_id'];
		}
		//Homes
				$cols = Array('category_id');
				$db->where('category_parent', 8);
				$offer_categories_homes = $db->get('categories', null, $cols);
				foreach ($offer_categories_homes as $key => $value) {
					$categories_homes[] = $value['category_id'];
				}
		//Jobs
				$cols = Array('category_id');
				$db->where('category_parent', 12);
				$offer_categories_jobs = $db->get('categories', null, $cols);
				foreach ($offer_categories_jobs as $key => $value) {
					$categories_jobs[] = $value['category_id'];
				}
		//Golf Offers
			$cols = Array('category_id');
				$db->where('category_parent', 19);
				$offer_categories_golf_offers = $db->get('categories', null, $cols);
				foreach ($offer_categories_golf_offers as $key => $value) {
					$categories_golf_offers[] = $value['category_id'];
				}
		//Shop
				$cols = Array('category_id');
				$db->where('category_parent <> 12 AND category_parent <> 8 AND category_id <> 1');
				$offer_categories_shop = $db->get('categories', null, $cols);
				foreach ($offer_categories_shop as $key => $value) {
					$categories_shop[] = $value['category_id'];
				}
		switch ($type) {
			case '/homes':
				$page_title = constant('HOMES');
				$db->where('offer_category', $categories_homes, 'IN');
				break;
			case '/jobs':
				$page_title = constant('JOBS');
				$db->where('offer_category', $categories_jobs, 'IN');
				break;
			case '/golf-offers': 
				$page_title = constant('GOLF_OFFERS');
				$db->where('offer_category', $categories_golf_offers, 'IN');
				if (isset($_GET['id'])) {
					$db->where('offer_owner', $_GET['id']);
				} 
				break;
			default:
				$page_title = constant('SHOP');
				$db->where('offer_category', $categories_homes, 'NOT IN');
				$db->where('offer_category', $categories_jobs, 'NOT IN');
				$db->where('offer_category', $categories_golf_offers, 'NOT IN');
				break;
		}
		$settings = Settings::getSettings();
		$offset = $settings['setting_offers_offset']; 
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
		
		//Get total
		if (isset($_POST['category']) && $_POST['category'] != 'All') {
			$db->where('offer_category', $cat_id);
		}
		if (isset($_POST['term']) && strlen($_POST['term']) > 2) {
			$db->where('offer_body_' . $_SESSION['site_language'] . ' LIKE "%'. $_POST['term'] . '%"');
		} 
		switch ($type) {
			case '/homes':
				$page_title = constant('HOMES');
				$db->where('offer_category', $categories_homes, 'IN');
				break;
			case '/jobs':
				$page_title = constant('JOBS');
				$db->where('offer_category', $categories_jobs, 'IN');
				break;
			case '/golf-offers':
				$page_title = constant('GOLF_OFFERS');
				$db->where('offer_category', $categories_golf_offers, 'IN');
				if (isset($_GET['id'])) {
					$db->where('offer_owner', $_GET['id']);
				}
				break;
			default:
				$page_title = constant('SHOP');
				$db->where('offer_category', $categories_homes, 'NOT IN');
				$db->where('offer_category', $categories_jobs, 'NOT IN');
				$db->where('offer_category', $categories_golf_offers, 'NOT IN');
				break;
		} 
		$db->where('offer_status', 1);
		$db->where('NOW() BETWEEN offer_publ_from AND offer_publ_to');
		$db->where ('offer_body_' . $_SESSION['site_language'] . ' != ""');
		$db->orderBy('offer_publ_from', 'DESC'); 
		$totals = $db->get('offers', $max); 
		$total = count($totals); 
		//Get page
		switch ($type) {
			case '/homes':
				$page_title = constant('HOMES');
				$db->where('offer_category', $categories_homes, 'IN');
				break;
			case '/jobs':
				$page_title = constant('JOBS');
				$db->where('offer_category', $categories_jobs, 'IN');
				break;
			case '/golf-offers':
				$page_title = constant('GOLF_OFFERS');
				$db->where('offer_category', $categories_golf_offers, 'IN');
				if (isset($_GET['id'])) {
					$db->where('offer_owner', $_GET['id']);
				}
				break;
			default:
				$page_title = constant('SHOP');
				$db->where('offer_category', $categories_homes, 'NOT IN');
				$db->where('offer_category', $categories_jobs, 'NOT IN');
				$db->where('offer_category', $categories_golf_offers, 'NOT IN');
				break;
		}
		if (isset($_POST['category']) && $_POST['category'] != 'All') {
			$db->where('offer_category', $cat_id);
		} 
		//echo '<br><br><br><br>' . $_POST['term'];
		if (isset($_POST['term'])) { 
			$db->where('(offer_body_' . $_SESSION['site_language'] . ' LIKE "%'. $_POST['term'] . '%" OR offer_image_text_' . $_SESSION['site_language'] . ' LIKE "%'. $_POST['term'] . '%")'); 
		}
		if ($_GET['tab'] == 'company') {
			$db->where('offer_company_ad', 1);
		}
		if ($_GET['tab'] == 'private') {
			$db->where('offer_company_ad', 0);
		} 
		$db->where('NOW() BETWEEN offer_publ_from AND offer_publ_to');
		$db->where ('offer_body_' . $_SESSION['site_language'] . ' != ""');
		$db->orderBy('offer_publ_from', 'DESC');
		$offers = $db->get('offers', array($offset, $limit)); 
		if ($_SERVER['REQUEST_URI'] != '/') {
			$sql = '
						SELECT 
							t1.category_title_' . $_SESSION['site_language'] . ' AS lev1, 
							t2.category_title_' . $_SESSION['site_language'] . ' as lev2, 
							t3.category_title_' . $_SESSION['site_language'] . ' as lev3
						FROM 
							categories AS t1
							LEFT JOIN categories AS t2 ON t2.category_parent = t1.category_id
							LEFT JOIN categories AS t3 ON t3.category_parent = t2.category_id
						WHERE 
							t1.category_id = 1
				';
			switch ($type) {
				case '/homes':
					$sql .= ' AND ( t1.category_id IN (' . implode(',', $categories_homes) . ')
							 OR t2.category_id IN (' . implode(',', $categories_homes) . ')
							  OR t3.category_id IN (' . implode(',', $categories_homes) . '))';
					break;
				case '/jobs':
					$sql .= ' AND (
								t1.category_id IN (' . implode(',', $categories_jobs) . ')
							  OR
							    t2.category_id IN (' . implode(',', $categories_jobs) . ')
							  OR 
							  	t3.category_id IN (' . implode(',', $categories_jobs) . ') 
							  )';
					break;
				case '/golf-offers':
					//echo '<br><br><br><br>';print_r($categories_golf_offers);
					$sql .= ' AND (
								t1.category_id IN (' . implode(',', $categories_golf_offers) . ')
							  OR
							    t2.category_id IN (' . implode(',', $categories_golf_offers) . ')
							  OR 
							  	t3.category_id IN (' . implode(',', $categories_golf_offers) . ') 
							  )';
					break;
				default:
					$sql .= ' AND (
								t1.category_id NOT IN (' . implode(',', $categories_homes) . ')
							  AND
								t2.category_id NOT IN (' . implode(',', $categories_homes) . ')
							  AND 
							  	t3.category_id NOT IN (' . implode(',', $categories_homes) . ')
							  )';
					$sql .= ' AND (
								t1.category_id NOT IN (' . implode(',', $categories_jobs) . ')
							  AND 
								t2.category_id NOT IN (' . implode(',', $categories_jobs) . ')
							  AND
								t3.category_id NOT IN (' . implode(',', $categories_jobs) . ')
								)';
					$sql .= ' AND (
								t1.category_id NOT IN (' . implode(',', $categories_golf_offers) . ')
							  AND 
								t2.category_id NOT IN (' . implode(',', $categories_golf_offers) . ')
							  AND
								t3.category_id NOT IN (' . implode(',', $categories_golf_offers) . ')
								)';
					break;
			}
			$sql .= '
						ORDER BY t2.category_title_' . $_SESSION['site_language'] . ', t3.category_title_' . $_SESSION['site_language']
							
				;
			//echo $sql;
			$categories = $db->rawQuery($sql);
			
			$category_list = '<div id="category_list">';
			if (isset($_POST['category']) && $_POST['category'] != 'All') {
				$category_list .= '<div class="category-item" id="' . $category['category_title'] . '" data-translate="' . constant('ALL') . '">' . constant('ALL') . '</div>';
			}
			foreach ($categories as $category) {
				if ($present_cat != $category['lev2']) {
					$present_cat = $category['lev2'];
					$category_list .= '<div class="category-item lev2" id="' . $category['lev2'] . '" data-translate="' . $category['lev2'] . '">' . $category['lev2'] . '</div>';
				}
				$category_list .= '<div class="category-item lev3" id="' . $category['lev3'] . '" data-translate="' . $category['lev3'] . '">' . $category['lev3'] . '</div>';
			}
			$category_list .= '</div>';
			if (isset($_POST['category'])) {
				$category_choice = constant(strtoupper(str_replace(' ', '_', $_POST['category'])));
				$category_class = $_POST['category'];
			}
			else {
				$category_choice = constant('ALL');
				$category_class = 'All';
			}
			if (isset($_POST['term'])) {
				$search_choice = $_POST['term'];
			}
			else {
				$search_choice = '';
			}
			$html = '
					<h1>' . $page_title . '</h1>
					<div id="offer_searchform">
						<div id="os_categories" class="' . $category_class . '">
							' . $category_choice . '
						</div>
						<div id="os_searchtext" contenteditable="true">
							' . $search_choice . '
						</div>
						<div id="SZoomIT_button">
							<img src="/_icons/search_button.png" />
						</div>
					</div>
				';
			if ($type != '/golf-offers') {
				$html .= '
					<div id="offer_tabs">
						<div class="offer_tab';
				if (!isset($_GET['tab']) || $_GET['tab'] == 'all') {
					$html .= ' active_tab';
				}
				$html .= '">
							<a href="?tab=all" alt"' . constant('ALL') . ' ' . constant('OFFERS') . '">' . constant('ALL') . ' ' . constant('OFFERS') . '</a>
						</div>
						<div class="offer_tab
					';
				if ($_GET['tab'] == 'private') {
					$html .= ' active_tab';
				}
				$html .= '">
							<a href="?tab=private" alt"' . constant('PRIVATE1') . ' ' . constant('OFFERS') . '">' . constant('PRIVATE1') . ' ' . constant('OFFERS') . '</a>
						</div>
						<div class="offer_tab
					';
				if ($_GET['tab'] == 'company') {
					$html .= ' active_tab';
				}
				$html .= '">
							<a href="?tab=company" alt"' . constant('COMPANY') . ' ' . constant('OFFERS') . '">' . constant('COMPANY') . ' ' . constant('OFFERS') . '</a>
						</div>
					</div>
					';
			}
			$html .= '
					' . $category_list
				;
		}
		$html .= '
				<div id="offer">
					<div id="offers_wrapper">
				';
		$i = 0;
		foreach ($offers as $offer) {
		
			Navigation::addHit('offers', $offer['offer_id']);
			$offer_image_text = '';
			$image = Common::getImage('/_offers/images/', $offer['offer_id']);
			if ($image != '0') {
				$image = '<img src="' . $image . '" />';
			}
			else {
				$image = '';
			}
			if ($offer['offer_company_ad'] == 1) {
				$offer_type = '<span class="right company_offer ' . $_SESSION['site_language'] . '">' . constant('TYPE_COMPANY') . '</span>';
			}
			elseif ($offer['offer_lastminute'] == 1) {
				$offer_type = '<span class="right last_minute ' . $_SESSION['site_language'] . '">' . constant('LAST_MINUTE') . '</span>';
			}
			else {
				$offer_type = '';
			}
			if ($offer['offer_image_text_' . $_SESSION['site_language']] != '') {
				$offer_image_text = $offer['offer_image_text_' . $_SESSION['site_language']];
			}
			$db->where('category_id', $offer['offer_category']);
			$category = $db->getOne('categories', array('category_title_' . $_SESSION['site_language']));
			
			$offer_cat = $category['category_title_' . $_SESSION['site_language']];
			$html .= '
					<div class="offer_wrapper">
						<div class="offer_content">
							<div class="offer_image">
								' . $image . '
							</div>
							<div class="offer_content_wrapper">
								<div class="breadcrum">
									' . $page_title . ' &raquo; ' . $offer_cat . $offer_type . '
								</div>
								<div id="user_contact_' . $offer['offer_owner'] . '" class="offer_user_info"></div>	
				';
			if ($offer_image_text != '') {
				$html .= '
								<div class="offer_image_text">
									' . $offer_image_text . '
								</div>
					';
			}
			$html .= '
								<div class="offer_text">
									' . $offer['offer_body_' . $_SESSION['site_language']] . '
				';
			if ($offer['offer_url_' . $_SESSION['site_language']] != '') {
				$html .= '<a href="/_php/redirect.php/?url=' . $offer['offer_url' . '_' . $_SESSION['site_language']] . '&offer=' . $offer['offer_id'] . '" target="_blank" title="' . constant('READ_MORE') . '">' . constant('READ_MORE') . '</a>';
			}
			if ($offer['offer_show_contact']) {
				$html .= '<br /><span class="show_contact_info" data-userid="' . $offer['offer_owner'] . '">' . constant('CONTACT_ME') . '</span>';
			}
			$html .= '
								</div>
				';
			if ($offer['offer_price'] != '') {
				$html .= '
								<div class="clear_both"></div>
								<div class="offer_price">
									' . $offer['offer_price'] . '
								</div>
					';
			}	
			$html .= '
								
							</div>
							<div class="clear_both"></div>
						</div>
					</div>
				';
			$i++;
			if ($i % 3 == 0) {
				$html .= '<div class="clear_both"></div>';
			}
		}
		if (substr($uri, -1) == '/') {
			$URI = substr($_SERVER['REQUEST_URI'], 0, -1);
		}
		else {
			$URI = $_SERVER['REQUEST_URI'];
		}
		$html .= '
				</div>
				<div class="clear_both"></div>
			</div>
			';	
		if (count($totals) > $limit) { 
			$html .= Navigation::buildPageNavigation($URI, null, $page, count($totals), $limit);
		}
		return $html;
	}

	public static function displayOfferForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		
		if (isset($id)) {
			$db->where('offer_id', $id);
			$result = $db->getOne('offers');
			if ($_SESSION['admin_level'] < 299) {
				if ($_SESSION['userid'] != $result['offer_owner']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$cat_id = $result['offer_category'];
			$type_id = $result['offer_company_ad'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$account_id = null;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		if ($_SESSION['admin_level'] < 400) {
			$user_account_type = $_SESSION['useraccount'];
		}
		$sql = '
						SELECT 
							t1.category_title_' . $_SESSION['site_language'] . ' AS lev1, 
							t2.category_title_' . $_SESSION['site_language'] . ' as lev2, 
							t3.category_title_' . $_SESSION['site_language'] . ' as lev3,
							t2.category_id as id2,
							t3.category_id as id3
						FROM 
							categories AS t1
							LEFT JOIN categories AS t2 ON t2.category_parent = t1.category_id
							LEFT JOIN categories AS t3 ON t3.category_parent = t2.category_id
						WHERE 
							t1.category_id = 1
						ORDER BY 
							t2.category_title_' . $_SESSION['site_language'] . ', t3.category_title_' . $_SESSION['site_language']
			;
		$categories = $db->rawQuery($sql);
		foreach ($categories as $category) {
			if ($present_cat != $category['lev2']) {
				$present_cat = $category['lev2'];
				if ($category['lev2'] != '') {
					$value[$category['id2']] = strtoupper($category['lev2']);
				}
			}
			if ($category['lev3'] != '') {
				$value[$category['id3']] = '&raquo; ' . $category['lev3'];
			}
			$values[] = $value;
			unset($value);
		}
		$data['id'] = $cat_id;
		$data['name'] = 'offer_category';
		$data['title'] = 'choose_category';
		$cat_dd = Forms::getDropdown($data, $values);
		$data = array(
				'table' => 'offers',
				'identifier' => 'offer_id',
				'table_prefix' => 'offer_',
				'return_uri' => '/admin/offers/',
				'column' => 'offer_id',
				'datetime' => array ('offer_created','offer_changed'),
				'required' => array ('offer_image','offer_accepted'),
				'on_off' => array('offer_show_contact', 'offer_status', 'offer_prioritized','offer_startpage','offer_company_ad', 'offer_lastminute', 'offer_accepted'),
				'offer_category' => $cat_dd,
				'offer_owner' => $_SESSION['userid'],
				'offer_status' => 1,
				'offer_account' => $user_account_type,
				'post_id' => $id,
				'submit' => $submit,
				'db-action' => $db_action
			);
			//$fields['offer_publ_from'] = 'date';
			//$fields['offer_publ_to'] = 'date';
			
			$fields ['offer_category'] = 'dropdown';
			$fields['offer_title'] = 'text';
			$fields['offer_image'] = 'file';
			$fields['offer_image_text_EN'] = 'text';
			$fields['offer_image_text_SV'] = 'text';
			$fields['offer_body_EN'] = 'text';
			$fields['offer_body_SV'] = 'text';
			$fields['offer_url_EN'] = 'text';
			$fields['offer_url_SV'] = 'text';
			$fields['offer_price'] = 'text';
			$fields['offer_company_ad'] = 'check';
			$fields['offer_show_contact'] = 'check';
			$fields['offer_lastminute'] = 'check';
			$fields['offer_status'] = 'hidden';
			
			
			
			if ($_SESSION['admin_level'] > 300) {
				$data['offer_accepted'] = 1;
				//$fields['offer_startpage'] = 'check';
				//$fields['offer_prioritized'] = 'check';
				$fields['offer_account'] = 'text';
				$fields['offer_owner'] = 'text';
				$fields['offer_accepted'] = 'hidden';
			}
			else {
				$fields['offer_account'] = 'hidden';
				$fields['offer_accepted'] = 'check';
			}
			$html = Forms::Form($data, $fields, $result);
		return $html;
	}

	public static function displayOfferList() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 300) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('OFFER')) . '
						</div>
						<div class="form_input">
							<input type="text" id="offer" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="offer_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="offer_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		else {
			$html = '
					<div class="form_row border_bottom admin_menu_bar">
						<a href="/admin/offers/update/">' . constant('CREATE') . ' ' . constant('OFFER') . '</a>
					</div>
				';
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
		$total = $db->get('offers');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('OFFERS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if ($_SESSION['admin_level'] < 300) {
				switch ($_SESSION['useraccount']) {
					case 1:
						$db->where('offer_account', 1);
						break;
					case 2:
						$db->where('offer_account', 2);
						break;
					case 3:		
						$db->where('offer_account', '3'); 
						break;
				}
				$db->where('offer_owner', $_SESSION['userid']);
			}
			$db->orderBy('offer_id');
			$offers = $db->get('offers', array($offset, $limit));
			foreach ($offers as $offer) {
				switch ($offer['offer_account']) {
					case 1:
						$db->where('user_id', $offer['offer_owner']);
						$user = $db->getOne('users', array('user_first_name', 'user_last_name'));
						$owner = 'User: ' . $user['user_first_name'] . ' ' . $user['user_last_name'];
						break;
					case 2:
						$db->where('course_id', $offer['offer_owner']);
						$user = $db->getOne('golfcourses', array('course_name'));
						$owner = 'Course: ' . $user['course_name'];
						break;
					case 3:
						$db->where('shop_id', $offer['offer_owner']);
						$user = $db->getOne('shops', array('shop_name'));
						$owner = 'Shop: ' . $user['shop_name'];
						break;
				}
				
				$db->where('offer_clicks_offer', $offer['offer_id']);
				$clicks = $db->get('offer_clicks');
				$column['ID'] = $offer['offer_id'];
				if ($_SESSION['admin_level'] > 300) {
					$column['OFFER_OWNER'] = $owner;
				}
				$column['TITLE'] = $offer['offer_title']; 
				$column['HITS'] = $offer['offer_hits'];
				$column['CLICKS'] = count($clicks);
				$column['PUBL_FROM'] = $offer['offer_publ_from'];
				$column['PUBL_TO'] = $offer['offer_publ_to'];
				$column['CREATED'] = $offer['offer_created'];
				$column['CHANGED'] = $offer['offer_changed'];
				$columns[] = $column; 
				$owner = null;
			}
			$data = array(
					'numeric' => array('ID', 'HITS', 'CLICKS'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'offers',
					'identifier' => 'offer_id',
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
	
	public static function getOffers($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('offer_owner', $id); 
		$offers = $db->get('offers');
		return $offers;
	}
	
	public static function getOfferType($type_id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('offer_type_id', $type_id);
		$type = $db->getOne('offer_types');
		return $type['offer_type_title'];
	}
	
	public static function getOffertypeDropdown($type_id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$types = $db->get('offer_types');
		foreach ($types as $type) {
			$value = array(
					$type['offer_type_id'] => 'OFFER_TYPE_'.$type['offer_type_title']
				);
			$values[] = $value;
		}
		$data = array(
				'table' => 'offer_types',
				'name' => 'offer_type',
				'title' => 'offer_type',
				'id' => $type_id
			);
		$dd = Forms::getDropdown($data, $values, $type_id);
		return $dd;
	}
	
	public static function matchOffers($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$cols = array(
				'offer_type',
				'offer_image',
				'offer_body',
				'offer_url'
			);
		$db->where('offer_owner', $id);
		$offers = $db->get('offers', null, $cols); 
		foreach ($offers as $offer) {
			$html .= self::displayOffer($offer, $data, $uri);
		}
		return $html;
	}

}

?>