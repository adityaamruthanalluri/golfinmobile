<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/_php/forms.management.php');


Class Bazaar {

	public static function displayBazaar() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$html = '';
		if (isset($_GET['form'])) {
			$html .= self::displayBazaarForm();
		}
		else {
			$html .= self::displayBazaarAds();
		}
		return $html;
	}
	
	public static function displayBazaarAds() {
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (!isset($_SESSION['bazar_user'])) {
			$db->orderBy('bazaar_id', 'DESC');
			$ads = $db->get('bazaar', 4);
		}
		foreach ($ads as $ad) {
			$html .= '
				<div id="ads_wrapper">
					<div class="ad_wrapper">
						<div class="ad_content">
							<h2>' . $ad['bazaar_title'] . '</h2>
							<img src="/_bazaar/adimages/ad-' . $ad['bazaar_id'] . '.png" />
							<p>' . $ad['bazaar_desc'] . '</p>
							<p>' . $ad['bazaar_name'] . '</p>
				';
			if ($ad['bazaar_contactinfo'] != 1) {
				$html .= '<p>' . $ad['bazaar_email'] . '</p>';
			}
			if ($ad['bazaar_contactinfo'] != 2) {
				$html .= '<p>' . $ad['bazaar_phone'] . '</p>';
			}
			$html .= '
							<p>' . $ad['bazaar_deliveryinfo'] . '</p>
						</div>
					</div>
				</div>
				';
		}	
		if (!isset($_SESSION['bazar_user'])) {
			$html .= '
					<div id="bazaar_login">
						Login to view more ads and create your own app &raquo;
					</div>
				';
		}
		else {
			$html .= '
					<div id="bazaar_menu_bar">
						Create your own ad &raquo;
				';
		}
		return $html;
	}
	
	public static function displayBazaarForm ($id = null) {
		if (isset($id)) {
		
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'bazaar',
				'identifier' => 'bazaar_id',
				'table_prefix' => 'bazaar_',
				'return_uri' => '/shop/',
				'column' => 'bazaar_id',
				'datetime' => array ('bazaar_created','bazaar_changed'),
				'required' => array ('bazaar_title','bazaar_desc','bazaar_name','bazaar_email', 'bazaar_phone','bazaar_contactinfo'),
				'post_id' => $id,
				'default_value' => 3,
				'submit' => $submit,
				'db-action' => $db_action
			);
		$fields['bazaar_image'] = 'file';
		$fields['bazaar_title'] = 'text';
		$fields['bazaar_desc'] = 'textarea';
		$fields['bazaar_name'] = 'text';
		$fields['bazaar_email'] = 'email';
		$fields['bazaar_phone'] = 'text';
		$fields['bazaar_contactinfo'] = 'multiple_radio';
		$fields['bazaar_deliveryinfo'] = 'text';
		$html = '
				<div id="article">
					' . Forms::Form($data, $fields, $result) . '
				</div>
				<div id="bazaar_preview">
					Preview:
					<div id="image">
						<img id="myImg" src="#" alt="your image" />
						<div id="ptitle"></div>
						<div id="pdesc"></div>
						<div id="pname"></div>
						<div id="pemail"></div>
						<div id="pphone"></div>
						<div id="pdelinfo"></div>
					</div>
				</div>
			';
		return $html;	
	}
	
	
	
	
	public static function resizeBazaarImage($file) {
		include($_SERVER['DOCUMENT_ROOT'].'/_php/SimpleImage.php');
			
	}
	
}



?>