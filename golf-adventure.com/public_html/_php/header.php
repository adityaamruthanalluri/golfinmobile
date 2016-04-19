<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/forms.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/menu.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/newsletter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/slideshow.php');
$settings = Settings::getSettings();
$newsletter = Newsletter::displayForm();
$share = Forms::shareForm();
$login = '
		<span class="lightbox" id="login">
			<i class="fa fa-sign-in"></i>
		</span><!--.login_link"-->
	';	
$menu = Menu::displayMainMenu($deviceType);
if (!isset($_SESSION['userid']) && $use_login_menu) {
	$login_menu = Menu::displayLoginMenu();
}
else {
	$login_menu = '';
}
if (is_file($_SERVER['DOCUMENT_ROOT'] . '/_icons/logotype.png')) {
	$logo_image = 'logotype.png';
}
else {
	$logo_image = 'logotype_' . strtolower($_SESSION['site_language']) . '.png';
}
$hidden_forms = Common::hiddenForms();
if ($deviceType != 'phone') {
	$header = '
		<div id="header_wrapper">
			<div id="header"> 
		';
	$header .= '
				<div id="logo">
					<a href="/" class="home">
						<img src="/_icons/' . $logo_image . '" />
					</a>
				</div>
		';
	if ($use_login_menu) {
		$header .= '
			<div id="login_menu">
				' . $login_menu . ' 
			</div>
			
				' . $menu
			;
	}
	if ($settings['setting_article_archive']) {
		$header .= $searchform;
	}
	$header .= '
				<div class="clear_both"></div>
			</div>
		</div>
		' . $hidden_forms
		;
}
else {
	$header = '
			<div id="mobile_header" class="clear_both">
				<div id="logo">
					<a href="/" class="home">
						<img src="/_icons/' . $logo_image . '" />
					</a>
				</div>
				<div class="nl_share">
					' . $login . '
					' . $newsletter . '
					' . $share . '
				</div>
				' . $menu . '
				<div id="mobile_menu_button">&#9776;</div>
			</div>
			<div class="clear_both">&nbsp;</div>
		' . $hidden_forms
		;
}
?>