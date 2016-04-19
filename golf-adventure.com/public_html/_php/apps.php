<?php
//displayAppArticleForm($id = null)
//displayAppArticleList()
//previewAppArticle()
//getPreviewContent($id)
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/articles.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'preview_app':
			echo Apps::getAppArticlePreviewContent($_POST['id']);
			break;
	}
}


class Apps {

	public static function displayAppArticleForm($id = null) {
		if ($_SESSION['admin_level'] < 99) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('id', $id);
			$result = $db->getOne('app_articles');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'app_articles',
				'identifier' => 'id',
				'table_prefix' => 'article_',
				'return_uri' => '/admin/apps_articles/',
				'column' => 'id',
				'post_id' => $id,
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields['article_title'] = 'text';
			$fields['article_image'] = 'file';
			$fields['article_summary'] = 'small_editor';
			$fields['article_body'] = 'large_editor';
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayAppArticleList() {
		if ($_SESSION['admin_level'] < 99) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('APPS_ARTICLE')) . '
						</div>
						<div class="form_input">
							<input type="text" id="app_article" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="app_article_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="app_article_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		else {
			$html = '';
		}
		$settings = Settings::getSettings();
		$html .= self::previewAppArticle();
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
		$page = 1;
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		$limit = $offset; 
		$offset = ($page * $offset) - $offset; 
		if ($_SESSION['admin_level'] < 300) {
			$db->where('article_owner', $_SESSION['userid']);
		}
		$total = $db->get('app_articles'); 
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('APP_ARTICLE_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$db->orderBy('id', 'DESC');
			if ($_SESSION['admin_level'] < 300) {
				$db->where('article_owner', $_SESSION['userid']);
			}
			$app_articles = $db->get('app_articles',Array ($offset, $limit));
			foreach ($app_articles as $app_article) {
				$column = array(
						'ID' => $app_article['id'],
						'ARTICLE_ID' => $app_article['article_id'], 
						'TITLE' => $app_article['article_title'], 
						'SOURCE' => $app_article['article_source'],
						'STATUS' => $app_article['article_published'],
						'PREVIEW' => '<img src="/_icons/icon_preview.png" class="preview preview_app" id="' . $app_article['id'] . '" />',
						'CREATED' => $app_article['article_created'],
						'CHANGED' => $app_article['article_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID', 'ARTICLE_ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'bool' => array('STATUS'),
					'icon' => array('STATUS', 'PREVIEW'),
					'table' => 'app_articles',
					'identifier' => 'id',
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
	
	public static function previewAppArticle() {
		$html = '
				<div class="popup" id="preview_app_wrapper">
					<div id="preview_app_form" class="">
						<div class="popup_close">x</div>
						<div id="preview_app_content"></div>
					</div>
				</div>
			';
		return $html;
	}
	
	public static function getAppArticlePreviewContent($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('id', $id);
		$item = $db->getOne('app_articles');
		$exts = array('jpg', 'jpeg', 'png', 'bmp', 'gif');
		foreach ($exts as $ext) {
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_app_articles/images/'. $id . '.' . $ext)) {
				$src = '/_app_articles/images/'. $id . '.'.$ext;
			}
		}
		$html = '
				<div id="preview_app_container">
					<h1>' . $item['article_title'] . '</h1>
						<div class="preview_img clear_both">
							<img src="' . $src . '" />
						</div>
						<p class="bold">' . strip_tags($item['article_summary']) . '</p>
						' . $item['article_body'] . '
				</div>
			';
		return $html;
	}
	
	public static function getAppUser($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('app_user_id', $_SESSION['app_users']);
		$user = $db->getOne('users');
		return $user;
	}
	
}