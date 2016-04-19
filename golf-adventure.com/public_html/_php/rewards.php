<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

class Rewards {

	public static function displayRewardForm($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$col = array('category_parent','category_lvl');
			$db->where('reward_id', $id);
			$result = $db->getOne('rewards');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'rewards',
				'identifier' => 'reward_id',
				'table_prefix' => 'reward_',
				'return_uri' => '/admin/rewards/',
				'column' => 'reward_id',
				'short_desc' => array('reward_description_EN', 'reward_description_SV'),
				'post_id' => $id,
				'on_off' => array('reward_status'),
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields['reward_title_EN'] = 'text';
			$fields['reward_title_SV'] = 'text';
			$fields['reward_image'] = 'file';
			$fields['reward_description_EN'] = 'textarea';
			$fields['reward_description_SV'] = 'textarea';
			$fields['reward_status'] = 'check';
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayRewardList($id = null) {
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
		$total = $db->get('rewards');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('REWARDS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			
			$db->orderBy('reward_title_' . $_SESSION['site_language'], 'ASC');
			$rewards = $db->get('rewards', array($offset, $limit));
			foreach ($rewards as $reward) {
				$column = array(
						'ID' => $reward['reward_id'],
						'REWARD' => $reward['reward_title_' . $_SESSION['site_language']], 
						'CREATED' => $reward['reward_created'],
						'CHANGED' => $reward['reward_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'rewards',
					'identifier' => 'reward_id',
					'linked' => array('REWARD'),
					'db_action' => 'delete'
				);
			$html = Lists::createList($columns, $data);
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