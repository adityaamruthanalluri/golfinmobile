<?php
//Languages: Set default language on top
session_start();
$site_languages = array(
		'EN',
		'SV'
	);
if (!isset($_SESSION['site_language'])) {
	 $_SESSION['site_language'] = $site_languages[0];
}
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php'); 



//Mail server settings
$smtp_host_ip = gethostbyname('mail.citynetwork.se');
$mail_transport = array(
		'server' => $smtp_host_ip,
		'port' => 587,
		'user' => 'info@golf-adventure.com',
		'password' => '6aNaWruv'
	);
	
//What packages are used and shown in admin menu and their access level
/*
		'golfcourses' => 500,
		'restaurants' => 500,
		'beds' => 500,

*/
$admin_head_categories = array(
		'deals' => 500,
		'suppliers' => 500,
		'articles' => 300,
		'newsletters' => 500,
		'users' => 500,
		'settings' => 500
	);
$admin_categories = array(
		300 => array(
				'deals',
				'articles',
				'newsletters'
			),
		500 => array(
				'deals',
				'articles',
				'users'
			),
		1000 => array(
				'deals',
				'articles',
				'users',
				'settings'
			)
	);
//Use registration and login Menu?
$use_login_menu = true;

//Number of columns for article summaries on startpage. Not neccecery, but good for admin.
$startpage_summaries_cols = 1;

//Use startpage sidebar?
$use_startpage_sidebar = false;

//Use sidebar?
$use_sidebar = false;	

//use contact form?
$use_contact_form = false;

//Use map?
$use_map = false;

//Use archive
$site_article_archive = false;

//Use contact form
$use_contact_form = false;

//App image max width
define('APP_IMAGE_MAX_WIDTH','400');

//Newsletter categories: Needs translation
$newsletter_categories = array(
		array('nl_cat_id' => 1, 'nl_cat_title' => constant('SUBSCRIBERS')),
		array('nl_cat_id' => 2, 'nl_cat_title' => constant('PRIVATES')),
		array('nl_cat_id' => 3, 'nl_cat_title' => constant('COMPANY_USERS'))
	);

/*****************************************
/** Do not change anything below!!!
/*****************************************/

//Device?
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/Mobile_Detect.php');
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$scriptVersion = $detect->getScriptVersion();

session_start();
//Logged in?
if (isset($_SESSION['userid'])) {
	$logged_in = true;
}
else {
	$logged_in = false;
}
?>