<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/articles.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/offers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
if (isset($_GET['action'])) {
	echo 'Start<br>';
	switch ($_GET['action']) {
		case 'getArticles': 
			$db->where('article_body <> ""');
			$db->where('article_status', 1);
			$db->orderBy('article_created', 'DESC');
			$articles = $db->get('articles', $_GET['n']); 
			for ($i = 0; $i < $_GET['n']; $i++) { 
				$articles[$i]['article_summary'] = str_replace('/_files', 'http://' . $_SERVER['SERVER_NAME'] . '/_files', $articles[$i]['article_summary']);
				$articles[$i]['article_body'] =    str_replace('/_files', 'http://' . $_SERVER['SERVER_NAME'] . '/_files', $articles[$i]['article_body']);
			}
			echo json_encode($articles);
			break;
		case 'getOffers': 
			$db->orderBy('offer_created', 'DESC');
			$db->where('offer_status', 1);
			$offers = $db->get('offers', $_GET['n']); 
			for ($i = 0; $i < $_GET['n']; $i++) { 
				if ($offers[$i]['offer_image'] != '') {
					$offers[$i]['offer_image'] = 'http://' . $_SERVER['SERVER_NAME'] . $offers[$i]['offer_image'];
				}
			}
			echo json_encode($offers);
			break;
	}

}

?>