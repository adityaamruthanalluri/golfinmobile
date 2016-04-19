<?php
//pageNavigation($logged_in)
//buildPageNavigation($URI, $query, $page, $total, $offset, $type = NULL, $category = NULL)
//addHit($table, $id)

require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/db.management.php');

class Navigation {
	
	public static function pageNavigation($logged_in) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$URI = Common::getURI();
		/*** ADMIN ***/
		if (substr($URI, 0, 6) == '/admin') { 
			/*** Handle admin ***/
			if ($logged_in){
				$admin = explode('/', $URI);
				switch ($admin[2]) {
					case '':
						return Users::greetUser();
						break;
					case 'apps_articles':
						$type = array(
								'apps_articles',
								'apps_article'
							);
							
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['apps'])) {
								return $menu . Apps::displayAppArticleForm($_GET['apps']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Apps::displayAppArticleForm($id);
							}
						}
						else {
							return $menu . Apps::displayAppArticleList();
						}
						break;

					case 'articles':
						$type = array(
								'articles',
								'article'
							);
						if ($_SESSION['admin_level'] > 499) {
							if ($dontmiss_articles) {
								$extras[] = array('articles', 'DONTMISS');
							}
							if ($ext_article) {
								$extras[] = array('articles', 'RECOMMEND');
							}
							if (is_array($extras)) {
								$type[] = $extras;
							}
						}
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['articles'])) {
								return $menu . Articles::displayConnectionForm($_GET['articles']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Articles::displayArticleForm($id);
							}
						}
						else {
							return $menu . Articles::displayArticleList();
						}
						break;
					case 'beds':
						$type = array(
								'beds',
								'bed'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['events'])) {
								return $menu . Offers::displayConnectionForm($_GET['events']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Beds::displayBedForm($id);
							}
						}
						else {
							return $menu . Beds::displayBedList();
						}
						break;
					case 'categories':
						$type = array(
								'categories',
								'category'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Categories::displayCategoryForm($id);
						}
						else {
							return $menu . Categories::displayCategoryList();
						}
						break;
					case 'companies':
						$type = array(
								'companies',
								'company'
							);
						if ($_SESSION['companyadmin'] == 1) {
							$type[] = array(
									array('companyuser', 'USER')
								);
						}
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['companyuser'])) { 
								return $menu . Companies::displayConnectionForm($_GET['companyuser']);
							}
							else {
								$id = $_GET['id']; 
								return $menu . Companies::displayCompanyForm($id);
							}
						}
						else {
							return $menu . Companies::displayCompanyList();
						}
						break;
					
					case 'deals':
						$type = array(
								'deals',
								'deal'
							);
						if ($_SESSION['admin_level'] > 499) {
							$type[] = array(
									array('deals', 'STARTPAGE'),
									array('deals', 'CONFIRMED')
								);
						}
						$menu = Menu::displayAdminSubMenu($type); 
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['deals'])) {
								switch ($_GET['deals']) {
									case '1': 
										return $menu . Deals::displayConnectionForm($_GET['deals']);
										break;
									case '2':
										return $menu . Deals::confirmedDeals();
										break;
								}
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Deals::displayDealForm($id);
							}
						}
						else {
							return $menu . Deals::displayDealList();
						}
						break;	
						
					case 'events':
						$type = array(
								'events',
								'event'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['events'])) {
								return $menu . Offers::displayConnectionForm($_GET['events']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Events::displayEventForm($id);
							}
						}
						else {
							return $menu . Events::displayEventList();
						}
						break;
					
					case 'golfclubs': 
						$type = array(
								'golfclubs',
								'golfclub'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							} 
							return $menu . Clubs::displayClubForm($id);
						}
						else {
							return $menu . Clubs::displayClubList();
						}
						break;
					case 'golfcourses': 
						$type = array(
								'golfcourses',
								'golfcourse'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							} 
							return $menu . Courses::displayCourseForm($id);
						}
						elseif (isset($admin[3]) && $admin[3]=='connections') {
							$type = $_GET['type'];
							return $menu . Forms::connectionForm($type);
						}
						else {
							return $menu . Courses::displayCourseList();
						}
						break;
						
					case 'keywords':
						$type = array(
								'keywords',
								'keyword'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Keywords::displayKeywordForm($id);
						}
						else {
							return $menu . Keywords::displayKeywordList();
						}
						break;
					case 'suppliers':
						$type = array(
								'suppliers',
								'supplier'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Deals::displaySupplierForm($id);
						}
						else {
							return $menu . Deals::displaySupplierList();
						}
						break;
					case 'newsletters':
						$type = array(
								'newsletters',
								'newsletter'
							);
						$type[] = array(
									array('newsletters', 'SUBSCRIBERS')
								);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['newsletters'])) { 
								switch ($_GET['newsletters']) {
									case 1:
										return $menu;
										break;
								}
							}
							else {
								$id = $_GET['id']; 
							}
							return $menu . Newsletter::displayNewsletterForm($id);
						}
						else {
							return $menu . Newsletter::displayNewsletterList();
						}
						break;
					case 'offers':
						$type = array(
								'offers',
								'offer'
							);
						/*if ($_SESSION['admin_level'] > 499) {
							$type[] = array(
									array('offers', 'STARTPAGE'),
									array('offers', 'ARTICLES'),
									array('offers', 'SIDEBAR')
								);
						}*/
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['offers'])) {
								return $menu . Offers::displayConnectionForm($_GET['offers']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Offers::displayOfferForm($id);
							}
						}
						else {
							return $menu . Offers::displayOfferList();
						}
						break;
					case 'restaurants':
						$type = array(
								'restaurants',
								'restaurant'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['events'])) {
								return $menu . Offers::displayConnectionForm($_GET['events']);
							}
							else { 
								$id = $_GET['id']; 
								return $menu . Restaurants::displayRestaurantForm($id);
							}
						}
						else { 
							return $menu . Restaurants::displayRestaurantList();
						}
						break;
					case 'rewards':
						$type = array(
								'rewards',
								'reward'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Rewards::displayRewardForm($id);
						}
						else {
							return $menu . Rewards::displayRewardList();
						}
						break;
					case 'settings':
						return Settings::displaySettingList();
						break;
					
					case 'shops':
						$type = array(
								'shops',
								'shop'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Shops::displayShopForm($id);
						}
						else {
							return $menu . Shops::displayShopList();
						}
						break;
					case 'slideshow': 
						$type = array(
								'slideshow',
								'slideshow'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Slideshow::displaySlideshowForm($id);
						}
						else {
							return $menu . Slideshow::displaySlideshowList();
						}
						break;
					case 'users':
						$type = array(
								'users',
								'user'
							);
						$menu = Menu::displayAdminSubMenu($type);
						if (isset($admin[3]) && $admin[3]=='update') {
							if (isset($_GET['id'])) { 
								$id = $_GET['id']; 
							}
							else {
								$id = NULL;
							}
							return $menu . Users::displayUserForm($id, $_GET['action']);
						}
						else {
							return $menu . Users::displayUserList();
						}
						break;
					default:
						return Users::greetUser();
						break;
				}
				
			}
			else {
				return Common::display401();
			}
		}
		/*** PUBLIC ***/
		$URL = explode('/', substr($URI, 1));
		switch ($URL[0]) {
			case mb_strtolower(constant('MATCHES'), 'utf-8'):
				if (isset($URL[1])) {
					$match = $URL[1];
				}
				else {
					$match = null;
				}
				return Match::displayMatchPage($match);
				break;
			
		}
		switch ( $URI ) {
			case '/':
				return Startpage::displayStartpage($deviceType);
				break;
			case '/' . mb_strtolower(constant('ARCHIVE'), 'utf-8'):
				return Articles::displayArticleArchive();
				break;
			case '/beds': 
				if (isset($_GET['bid'])) {
					return Beds::displayBedView($_GET['bid']);
				}
				break;
			case '/change-password':
			case '/create-password':
				if (isset($_GET['email'])) {
					return Common::displayPasswordRecoveryInfo('nomail');
				}
				else {
					return Common::displayPasswordRecoveryInfo('form');
				}
				break;
			case '/chronicles': 
				if (isset($_GET['id'])) {
					$id = $_GET['id'];
				}
				else {
					$id = null;
				}
				return Articles::displayChronicles($id);
				break;
			case '/' . str_replace(' ', '', strtolower(constant('GOLF_DEALS'))): 
				if (isset($_GET['id'])) {
					return Deals::displayDeal($_GET['id']);
				}
				elseif (isset($_GET['o'])) {
					return Deals::displayDeals(1, $deviceType,$_GET['o']);
				}
				elseif (isset($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))])) {
					return Deals::closeDeal($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))]);
				}
				else {
					return Deals::displayDeals(1, $deviceType);
				}
				break;
			case '/feedback/thankyou':
				return FeedbackF::feedbackThnx();
				break;
			case '/feedback':
				return FeedbackF::feedbackForm();
				break;
			case '/golfclubs':
				if (isset($_GET['gcid'])) {
					return Clubs::displayClubView($_GET['gcid']);
				}
				break;
			case '/golfcourses':
				if (isset($_GET['gcid'])) {
					return Courses::displayCourseView($_GET['gcid']);
				}
				break;
			case '/mobilemap':
				if ($deviceType == 'phone' || $deviceType == 'tablet') {
					return Map::displayMap();
				}
				else {
					return Common::displayUnauthorized();
				}
				break;
			case '/' . strtolower(str_replace(' ','-', constant('COURSE_BY_RATING'))):
				return Courses::displayCourseByRating();
				break;
			case '/' . strtolower(constant('OFFERS')):
				return Offers::displayOffers(2);
				break;
			
			case '/passwordrecovery':
				if (isset($_GET['code'])) {
					$result = Common::verifyPasswordRecovery($_GET['code']);
				}
				elseif (isset($_GET['dbu'])) {
					$result = 'success';
				}
				return Common::displayPasswordRecoveryInfo($result);
				break;
			case '/restaurants': 
				if (isset($_GET['rid'])) {
					return Restaurants::displayRestaurantView($_GET['rid']);
				}
				break;
			case '/' . str_replace(' ', '', strtolower(constant('SHOP'))): 
				if (isset($_GET['id'])) {
					return Deals::displayDeal($_GET['id']);
				}
				elseif (isset($_GET['o'])) {
					return Deals::displayDeals(2, $deviceType, $_GET['o']);
				}
				elseif (isset($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))])) { 
					return Deals::closeDeal($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))]);
				}
				else {
					return Deals::displayDeals(2, $deviceType);
				}
				break;
			case '/' . str_replace(' ', '', strtolower(constant('DESTINATIONS'))): 
				if (isset($_GET['id'])) {
					return Deals::displayDeal($_GET['id']);
				}
				elseif (isset($_GET['o'])) {
					return Deals::displayDeals(3, $deviceType, $_GET['o']);
				}
				elseif (isset($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))])) { 
					return Deals::closeDeal($_GET[strtolower(str_replace(' ', '-', constant('CLOSE_DEAL')))]);
				}
				else {
					return Deals::displayDeals(3, $deviceType);
				}
				break;
			case '/' . constant('GOLFDEALS'):
				return Deals::displayDeals();
				break;
			default:
				if ($URI != '/db.management.php') {
					include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
					$db = new MysqliDb($server, $user, $password, $database);
					$db->where ("article_url", $URI);
					$article = $db->getOne ("articles");
					if (is_array($article) ) {
						return Articles::displayArticle($article);
					}
					else {
						return Common::display404();
					}
				}
		}
	}
	
	public static function buildPageNavigation($URI, $query, $page, $total, $offset, $type = NULL, $category = NULL) { 
			$settings = Settings::getSettings();
			if (isset($_GET['sortorder'])) {
				$sortorder = '&sortorder=' . $_GET['sortorder'];
			}
			else {
				$sortorder = '';
			}
			if (!isset($_GET['so'])) {
				$so = '&so=1';
			}
			else {
				$so = '';
			}
			if (isset($_GET['f'])) {
				$f = '&f=' . $_GET['f'];
			}
			else {
				$f = '';
			}
			$URI = Common::getURI();
			
			$next_page = $page + 1;
			$back_page = $page - 1;
			$start = $page-3;
			if ($start < 1) {
				$start = 1;
			}
			$stop = $page+3;
			$center_nav = '<a href="' . $URI . '/?query=' . urlencode($query) . '&page=1' . $sortorder . $so . $f;
			if (isset($type)) {
				$center_nav .= '&type=' . $type;
			}
			if (isset($category)) {
				$center_nav .= '&category=' . $category;
			}
			if (isset($_GET['so'])) {
				$center_nav .= '&so=' . $_GET['so'];
			}
			$center_nav .= '"> 1 </a> ... |';
			for ($i=$start;$i<=$stop;$i++) {
				if ($i != $page) {
					if ($i < ceil($total/$offset)) {
						$center_nav .= '<a href="' . $URI . '/?query=' . urlencode($query) . '&page=' . $i;
						if (isset($type)) {
							$center_nav .= '&type=' . $type;
						}
						if (isset($category)) {
							$center_nav .= '&category=' . $category;
						}
						if (isset($_GET['so'])) {
							$center_nav .= '&so=' . $_GET['so'];
						}
						$center_nav .= $sortorder . $so . $f . '"> ' . $i . ' </a>';
					}
				}
				else {
					$center_nav .= ' <strong>' . $i . '</strong> ';
				}
				if ($i < ceil($total/$offset) && $i>0) {
					$center_nav .= '|';
				}
			}
			$center_nav .= ' ... <a href="' . $URI . '/?query=' . urlencode($query) . '&page=' . ceil($total/$offset);
						if (isset($type)) {
							$center_nav .= '&type=' . $type;
						}
						if (isset($category)) {
							$center_nav .= '&category=' . $category;
						}
						if (isset($_GET['so'])) {
							$center_nav .= '&so=' . $_GET['so'];
						}
						$center_nav .= $sortorder . $so . $f . '"> ' . ceil($total/$offset) . ' </a>';
			$html = '
						<div class="page_navigation_wrapper">
							<div class="navarr navarr_back">
				';
			if ($page > 1) {
				$html .= '
								<a href="' . $URI . '/?query=' . urlencode($query) . '&page=' . $back_page
					;
				if (isset($type)) {
					$html .= '&type=' . $type;
				}
				if (isset($category)) {
					$html .= '&category=' . $category;
				}
				if (isset($_GET['so'])) {
					$html .= '&so=' . $_GET['so'];
				}
						$html .= $sortorder . $so . $f . '">&laquo; ' . constant('PAGE_BACK') . '</a>
					';
			}
			else {
				$html .= '&nbsp;';
			}
			$html .= '
							</div>
							<div class="page_navigation_center">
								<div class="nav_center">
									' . $center_nav . '
								</div>
							</div>
							<div class="navarr navarr_forward">
				';
			if ($page*$offset < $total) {
				$html .= '
								<a href="' . $URI . '/?query=' . urlencode($query) . '&page=' . $next_page;
				if (isset($type)) {
					$html .= '&type=' . $type;
				}
				if (isset($category)) {
					$html .= '&category=' . $category;
				}
				if (isset($_GET['so'])) {
					$html .= '&so=' . $_GET['so'];
				}
				$html .= $sortorder . $so . $f . '">' . constant('PAGE_NEXT') . ' &raquo; </a>';
			}
			else {
				$html .= '&nbsp;';
			}
			$html .= '
							</div>
						</div><!--.page_navigation_wrapper-->
				';
			return $html;
		}
		
	public static function addHit($table, $id, $column = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$prefix = Database_management::getPrefix($table);
		$col = $prefix . 'id';
		if (isset($column)) {
			$column = $column;
		}
		else {
			$column = 'hits';
		}
		$hits_col = $prefix . $column;
		$db->where($col, $id);
		$hits = $db->getOne($table);
		$hits = $hits[$hits_col] + 1;
		$data = array (
				$hits_col => $hits
			);
		$db->where($col, $id);
		$db->update($table, $data);
	}
	
	public static function addLog() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database); 
		if (!isset($_SESSION['visitorid'])) {
			//$browser = get_browser();
			$data = array(
					'visit_date' => date('Y-m-d H:i:s'),
					'visit_url' => $_SERVER['REQUEST_URI'],
					'visit_ip' => $_SERVER['REMOTE_ADDR']
					/*,
					'visit_platform' => $browser->platform,
					'visit_platformver' => $browser->platform_version,
					'visit_browser' => $browser->browser,
					'visit_browserver' => $browser->version
					*/
				);
			$result = $db->insert('visits', $data);
			$_SESSION['visitorid'] = $result;
		}
		$hit_data = array(
				'pagehit_visitid' => $_SESSION['visitorid'],
				'pagehit_date' => date('Y-m-d H:i:s'),
				'pagehit_url' => $_SERVER['REQUEST_URI']
			); 
		$result = $db->insert('page_hits', $hit_data); 
		$sql = '
				DELETE FROM page_hits
				WHERE pagehit_url LIKE "%.js"
				OR pagehit_url LIKE "%.png"
				OR pagehit_url LIKE "%.ico"
				OR pagehit_url LIKE "%.jpg"
				OR pagehit_url LIKE "%.shtml"
				OR pagehit_url LIKE "%.txt"
				OR pagehit_url LIKE "%_css"
			';
		$db->rawQuery($sql);
		$sql = '
				DELETE FROM visits
				WHERE visit_url LIKE "%.js"
				OR visit_url LIKE "%.png"
				OR visit_url LIKE "%.ico"
				OR visit_url LIKE "%.jpg"
				OR visit_url LIKE "%.shtml"
				OR visit_url LIKE "%.txt"
				OR visit_url LIKE "%_css"
			';
		$db->rawQuery($sql);
	}
	
}

?>