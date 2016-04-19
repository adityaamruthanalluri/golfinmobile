<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');

class Sidebar {

	public static function getContent($uri) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->orderBy('sb_place', 'ASC');
		$offers = $db->get('sidebar_offers');
		foreach ($offers as $offer) {
			$sidebar_offers[] = Offers::getOffers('sidebar', $offer['sb_offer_id']);
		}
		
		$cols = array(
				'article_title',
				'article_summary',
				'article_url'
			);
		$html = '
				<div id="sidebar_wrapper">
					<div id="sidebar_content">
					 ' . $sidebar_offers[0]
			;
		for ($i=1;$i<count($sidebar_offers);$i++) {
			$html .= $sidebar_offers[$i];
		}
		$html .= '
					</div>
				</div>
			';
		
		return $html;
	}

}

?>