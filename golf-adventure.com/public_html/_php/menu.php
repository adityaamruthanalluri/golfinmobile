<?php 
//displayMainMenu($deviceType)
//buildMenu($a, $level)
//displayLoginMenu()
//displayAdminSubMenu($type)

require_once($_SERVER['DOCUMENT_ROOT'].'/_php/db.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/newsletter.php');

Class Menu {
	
	public static function displayMainMenu($deviceType) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$menu = '';
		$menu .= Articles::displayArticleMenu();  

		if (isset($_SESSION['userid']) && $_SESSION['admin_level'] > 100) {
			$adminmenu = Admin::displayAdminMenu($_SESSION['admin_level']);
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		if (count($site_languages) > 1) {
			$lang = Forms::languageForm(); 
		}
		else {
			$lang = '';
		}
		//Log in / My Page
			session_start();
			if (isset($_SESSION['userid'])) {
				$db->where('user_id', $_SESSION['userid']);
				$user = $db->getOne('users');
				$user_email = $user['user_email'];
				$login_link = '
				<div id="login_menu" class="menu loginmenu">
					<ul class="menucontainer menuparent_0">
						<li class="menuitem_999998 menulevel_1">
							<a href="/admin/" class="mypage_menu_item
					';
				if ($_SERVER['REQUEST_URI'] == '/admin/') { 
					$login_link .= ' active_menu_link';
				}
				$login_link .= '
							">
								<i class="fa fa-user"></i> ' . $user_email . '
							</a>
						
					
				';
				$login_link .= '
						<ul menucontainer menuparent_999998>
							<li class="menulevel_2">
								<a href="/admin/" class="mypage_menu_item">' . constant('MY_PAGE') . '</a>
							</li>
							<li class="menulevel_2">
								<a class="lightbox mypage_menu_item" id="logout">' . constant('LOGOUT') . '</a>
							</li>
						</li>
					</ul>
				</ul>
			</div>
				';
			}
			else {
				$login_link = '
				<div id="login_menu" class="menu loginmenu">
					<ul class="menucontainer menuparent_0">
						<li class="menulevel_1">
							<span id="login" class="lightbox login_menu_item">' . constant('ALREADY_LOG_IN') . '</span>
						</li>
					</ul>
				</div>
				';
			}
		$newsletter = Newsletter::displayForm();
		$share = Forms::shareForm();
		$html = '
				<div id="main_menu_wrapper" class="menu">
					<div id="lang_form">
			';		
		$html .= '
					</div>
					<div id="main_menu">
			';
		$html .= '
						' . $menu  . '
						
					</div><!--#main_menu--->
					' . $login_link . '
					<div class="clear_both"></div>
			';
		if ($deviceType != 'phone') {
			$html .= '
						<div class="nl_share">
							' . $newsletter . '
							' . $share . '
							<a href="https://www.facebook.com/skiinginmobile/" title="' . constant('VISIT_ON_FACEBOOK') . '" target="_blank">
								<i class="fa fa-facebook-square"></i>
							</a>
							<a href="https://www.linkedin.com/grps/GolfInMobile-8414826/about?" title="' . constant('VISIT_ON_LINKEDIN') . '"  target="_blank">
								<i class="fa fa-linkedin-square"></i>
							</a>
							</div>
						</div>
				';
		}
		$html .= '
					<div class="clear_both"></div>
			
				</div><!--#main_menu_wrapper--->
			';	
		if (isset($_SESSION['userid'])) {	
			$html .= '
				<div id="admin_menu_wrapper" class="menu">
					<div id="admin_menu">
						' . $adminmenu . '
					</div><!--#admin_menu--->
				</div><!--#admin_menu_wrapper--->
				';
		}
		return $html;
	}
		
	public static function buildMenu($a, $level) { 
		$r = '' ;
		foreach ( $a as $i ) {
			if ($i['parent'] == $level ) {
				$zindex = $i['level'] * 1000;
				$r = $r . '<li class="menuitem_' . $i['id'] . ' menulevel_' . $i['level'] . '">' . $i['menu_item'] . self::buildMenu($a, $i['id']) . '</li>';
			}
		}
		return ($r==''?'':'<ul class="menucontainer menuparent_'. $level . '" style="z-index:' . $zindex . '">'. $r . '</ul>');
	}
		
		
	public static function displayLoginMenu() {
		if ($deviceType == 'phone') {
			if (isset($_SESSION['userid'])) {
				$html = '
						<ul>
							<li class="login_link" id="logout">
								' . constant('LOGOUT') . '
							</li><!--.login_link"-->
						<ul>
					';
			}
			else {
				$html .= '
						<ul>
							<li class="lightbox" id="login">
								' . constant('LOGIN_LINK') . '
							</li><!--.login_link"-->
							<li class="lightbox" id="register">
								' . constant('REGISTER_LINK') . '
							</li><!--.login_link"-->
						<ul>
					';
			}
		}
		else {
			/*if (isset($_SESSION['userid'])) {
				$html .= '
						<div class="lightbox" id="logout">
							' . constant('LOGOUT') . '
						</div><!--.lightbox"-->
						<div class="" id="admin_link">
							<a href="/admin">' . constant('MY_PAGE') . '</a>
						</div><!--.lightbox"-->
					';
			}
			else {
				$html .= '
						<div class="lightbox" id="login">
							' . constant('LOGIN_LINK') . '
						</div><!--.lightbox"-->
						<div class="lightbox" id="register">
							' . constant('REGISTER_LINK') . '
						</div><!--.lightbox"-->
					';
			}*/
		}
		return $html;
	}
	
	public static function displayAdminSubMenu($type) { 
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$html = '
					<h1>' . constant(strtoupper($type[0])) . '</h1>
			';
		if ($_SESSION['admin_level'] > 300) {
			$html .= '
					<div class="admin_menu_bar">
						<a href="/admin/' . $type[0] . '/">' . constant('LIST') . ' ' . mb_strtolower(constant(strtoupper($type[0])),'UTF-8') . '</a>
						<a href="/admin/' . $type[0] . '/update/">' . constant('CREATE') . ' ' . mb_strtolower(constant(strtoupper($type[1])),'UTF-8') . '</a>
				';
			if (isset($type[2])) {
				foreach ($type[2] as $key => $value) {
					$html .= '
							<a href="/admin/' . $type[0] . '/update/?' . $value[0] . '=' . ($key + 1) . '">' . constant(strtoupper($value[0])) . ' &raquo; ' . mb_strtolower(constant($value[1]),'UTF-8') . '</a>
						';
				}
			}
			$html .= '			
					</div>
				';
		}
		return $html;		
	}
	
}