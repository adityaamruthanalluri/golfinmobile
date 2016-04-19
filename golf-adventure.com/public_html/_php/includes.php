<?php 
session_start();
//Settings
include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
//Language
if (!isset($_SESSION['site_language'])) {
	$topdom = substr($_SERVER['SERVER_NAME'], strrpos($_SERVER['SERVER_NAME'], '.'));
	if ($topdom == 'se') {
		$_SESSION['site_language'] = 'SV';
	}
	else {
		$_SESSION['site_language'] = 'EN';
	}
}

require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php'); 
//Database
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/db.management.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
//Devices
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/Mobile_Detect.php');
//Administration
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/accounts.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/apps.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/admin.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/categories.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/forms.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/lists.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/clubs.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/courses.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/restaurants.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/beds.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/keywords.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/tags.php');
//Content
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/feedback.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/search.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/head.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/header.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/slideshow.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/deals.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/events.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/map.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/match.php'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/menu.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/navigation.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/newsletter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/foot.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/startpage.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/rewards.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/shops.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/articles.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/users.php');
if (array_key_exists('offers', $admin_head_categories)) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/_php/offers.php');
}
if ($use_sidebar) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/_php/sidebar.php');
}

?>