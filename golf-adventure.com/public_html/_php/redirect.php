<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
if (isset($_GET['offer'])) {
	$offer = $_GET['offer'];
	$page = str_replace('http://' . $_SERVER['SERVER_NAME'], '', $_SERVER['HTTP_REFERER']);
	if (strpos($page, 'ttp://')) {
		$uri = str_replace('www.', '',$_SERVER['SERVER_NAME']);
		$page = str_replace('http://' . $uri, '', $_SERVER['HTTP_REFERER']);
	}
}
if (isset($_POST['offer'])) {
	$offer = $_POST['offer'];
	$page = $_POST['page'];
}

$data = array(
		'offer_clicks_offer' => $offer,
		'offer_clicks_page' => $page,
		'offer_clicks_date' => date('Y-m-d H:i:s')
	);
$result = $db->insert('offer_clicks', $data);
$db->where('offer_clicks_offer', $_GET['offer']);
$clicks = $db->get('offer_clicks');
$data = array(
		'offer_clicks' => count($clicks)
	);
$db->where('offer_id', $_GET['offer']);
$db->update('offers', $data);
if ($result && isset($_GET['offer'])) {
	header("Location: " . $_GET['url']);
}
else {
	echo 'Error';
}


?>