<?php

class Keywords {

	public static function displayKeywordForm($id = null) {
		if ($_SESSION['admin_level'] < 201) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('keyword_id', $id);
			$result = $db->getOne('keywords');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'keywords',
				'identifier' => 'keyword_id',
				'table_prefix' => 'keyword_',
				'return_uri' => '/admin/keywords/',
				'column' => 'keyword_id',
				'required' => array ('keyword_keyword'),
				'post_id' => $id,
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields = array(
					'keyword_keyword' => 'text',
					'keyword_rank' => 'text',
				);
		return Forms::Form($data, $fields, $result);
	}

	public static function displayKeywordList() {
		if ($_SESSION['admin_level'] < 201) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
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
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		else {
			$page = 1;
		}
		$limit = $offset;
		$offset = ($page * $offset) - $offset;
		$total = $db->get('keywords');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('KEYWORDS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$db->orderBy('keyword_keyword');
			$keywords = $db->get('keywords');
			foreach ($keywords as $keyword) {
				$column = array(
						'ID' => $keyword['keyword_id'],
						'KEYWORD' => $keyword['keyword_keyword'], 
						'RANK' => $keyword['keyword_rank'], 
						'CREATED' => $keyword['keyword_created'],
						'CHANGED' => $keyword['keyword_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID', 'RANK'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'keywords',
					'identifier' => 'keyword_id',
					'linked' => array('KEYWORD'),
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
	
	

}

?>