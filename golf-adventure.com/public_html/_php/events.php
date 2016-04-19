<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');

class Events {

	public static function displayEventForm($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] < 201) {
			$db->where('club_gkadministrator', $_SESSION['userid']);
			$club = $db->getOne('golfclubs'); 
			$owner = $club['club_id'];
		}
		else {
			$owner = 0;
		}
		
		if (isset($id)) {
			$db->where('event_id', $id);
			$result = $db->getOne('app_events');
			if ($_SESSION['admin_level'] < 201) {
				if ($owner != $result['event_owner']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$account_id = null;
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'app_events',
				'identifier' => 'event_id',
				'table_prefix' => 'event_',
				'return_uri' => '/admin/events/',
				'column' => 'event_id',
				'datetime' => array ('event_created','event_changed'),
				'on_off' => array('event_status'),
				'post_id' => $id,
				'event_owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields = array(
					'event_start' => 'date',
					'event_end' => 'date',
					'event_title' => 'text',
					'event_image' => 'file',
					'event_description' => 'text',
					'event_url' => 'text',
					'event_status' => 'check'
				);
		if ($_SESSION['admin_level'] > 200) {
			$fields['event_owner'] = 'text';
		}
		else {
			$fields['event_owner'] = 'hidden';
		}
		$html = Forms::Form($data, $fields, $result);
		return $html;
	
	}

	public static function displayEventList() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if ($_SESSION['admin_level'] > 200) {
			$html = '
					<div class="form_row border_bottom">
						<div class="form_title">
							' . constant('SEARCH') . ' ' . strtolower(constant('EVENT')) . '
						</div>
						<div class="form_input">
							<input type="text" id="event" class="conn_auto ui-autocomplete-input" autocomplete="off">
							<input type="button" id="event_edit" value="' . constant('EDIT') . '" />
							<input type="button" id="event_view"  value="' . constant('VIEW') . '" />
						</div>
						<div class="clear_both"></div>
					</div>
				';
		}
		else {
			$html = '';
		}
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
		$total = $db->get('app_events');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('EVENTS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if ($_SESSION['admin_level'] < 201) {
				$db->where('club_gkadministrator', $_SESSION['userid']);
				$result = $db->getOne('golfclubs');
				$golfclub = $result['golf_id'];
				$db->where('event_owner', $golfclub);
			}
			$db->orderBy('event_id');
			$events = $db->get('app_events');
			foreach ($events as $event) {
				if ($event['event_owner'] > 0) {
					$event_owner = Clubs::getClub($event['event_owner']);
					$owner = $event_owner['club_name'];
				}
				else {
					$owner = constant('ADMIN');
				}
				$column['ID'] = $event['event_id'];
				$column['TITLE'] = $event['event_title'];
				if ($_SESSION['admin_level'] > 200) {
					$column['OWNER'] = $owner;
				}
				$column['EVENT_START'] = $event['event_start'];
				$column['EVENT_END'] = $event['event_end'];
				$column['CREATED'] = $event['event_created'];
				$column['CHANGED'] = $event['event_changed'];

				//$column['event_TYPE'] = constant(strtoupper(self::geteventType($column['event_TYPE'])));
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'app_events',
					'identifier' => 'event_id',
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
	
	public static function getEvents($id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('event_owner', $id); 
		$db->where('event_end >= NOW()'); 
		$events = $db->get('app_events');
		return $events;
	}

}




?>