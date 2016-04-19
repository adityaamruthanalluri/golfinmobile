<?php
/*
displayMatchForm($id = null)
getMatchOwnersDropdown($match_owner_type, $owner_id)
displayMatchList()
displayMatchPage($id = null)
*/




require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/forms.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/SV.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'getOwnerDropdown':
			$match_owners_dd = Match::getMatchOwnersDropdown($_POST['type'], null);
			echo $match_owners_dd;
			die();
			break;
		case 'cleanUrl':
			$uri = $_POST['title'];
			$cleanUrl = Common::cleanUrl($uri);
			echo '/' . trim($cleanUrl);
			die();
			break;
	}
}

class Match {

	public static function displayMatchForm($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		switch ($_SESSION['admin_level']) {
			case 100:
				$form_type = false;
				$form_owner = true;
				$owner_id = $_SESSION['user_id'];
				break;
			default:
				$form_type = true;
				$form_owner = false;
				break;
		}
		if (isset($id)) {
			$db->where('match_id', $id);
			$result = $db->getOne('matches');
			$match_type = $result['match_type'];
			$match_matchurl = $result['match_matchurl'];
			$match_owner_type = $result['match_owner_type'];
			$owner_id = $result['match_owner'];
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$match_type = 1;
			$match_owner_type = 1;
			if (!$form_owner) {
				$owner_id = null;
			}
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$cols = array('match_type_id', 'match_type_title');
		$match_types = $db->get('match_types', null, $cols);
		$cols = array('match_owner_id', 'match_owner_title');
		$match_owner_types = $db->get('match_owners', null, $cols);
		$match_owners_dd = self::getMatchOwnersDropdown($match_owner_type, $owner_id, $_SESSION['admin_level']);
		$data = array(
				'table' => 'matches',
				'identifier' => 'match_id',
				'table_prefix' => 'match_',
				'return_uri' => '/admin/matches/',
				'required' => array ('match_owner', 'match_title', 'match_street', 'match_zip', 'match_city'),
				'post_id' => $id,
				'on_off' => array('match_status'),
				'match_id' => $id,
				'match_owner_type' => $match_owner_type,
				'match_owner_types' => $match_owner_types,
				'match_type' => $match_type,
				'match_types' => $match_types,
				'match_owner' => $match_owners_dd,
				'match_owner_id' => $owner_id,
				'match_matchurl' => $match_matchurl,
				'owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
		$fields['match_matchurl'] = 'hidden';
		$fields['match_id'] = 'hidden';
		if ($form_type) {
			$fields['match_owner_type'] = 'multiple_radio';
		}
		else {
			$fields['match_owner_type'] = 'hidden';
		}
		$fields['match_owner'] = 'dropdown';
		$fields['match_title'] = 'text';
		$fields['match_image'] = 'file';
		$fields['match_street'] = 'text';
		$fields['match_zip'] = 'text';
		$fields['match_city'] = 'text';
		$fields['match_state'] = 'text';
		$fields['match_country'] = 'text';
		
		$fields['match_url'] = 'text';
		$fields['match_email'] = 'text';
		$fields['match_phone'] = 'text';
		
		//$fields['match_short_desc'] = 'small_editor';
		$fields['match_long_desc'] = 'large_editor';
		$fields['match_title'] = 'text';
		$fields['match_title'] = 'text';
		$fields['match_status'] = 'check';
		
		return Forms::Form($data, $fields, $result);
	}
	
	public static function getMatchOwnersDropdown($match_owner_type, $owner_id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$owner['id'] = $owner_id;
		$owner['table'] = 'matches';
		$owner['name'] = 'match_owner';
		$owner['title'] = 'OWNER';
		switch ($match_owner_type) {
			case 1:
				$cols = array('user_id', 'user_first_name', 'user_last_name');
				$db->where('user_id > 0');
				switch ($_SESSION['admin_level']) {
					case 100:
					case 200:
						$db->where('user_id', $_SESSION['userid']);
						$owner['id'] = $_SESSION['userid'];
						break;
				}
				$all_users = $db->get('users', null, $cols);
				foreach ($all_users as $user) {
					$users[$user['user_id']] = $user['user_first_name'] . ' ' . $user['user_last_name'];
				}
				$user_array[] = $users;
				$match_owners_dd = Forms::getDropDown($owner, $user_array, $owner_id , true);
				break;
			case 2:
				switch ($_SESSION['admin_level']) {
					case 100:
					case 200:
						$cols = array('cu_company_id');
						$db->where('cu_user_id', $_SESSION['userid']);
						$co = $db->get('company_users', null, $cols);
						foreach ($co as $c) {
							$cids[] = $c['cu_company_id'];
						}
						$db->where('company_id', $cids, 'IN');
						$owner['id'] = $_SESSION['companyid'];
						break;
				}
				$cols = array('company_id', 'company_name');
				$all_companies = $db->get('companies', null, $cols); 
				foreach ($all_companies as $company) {
					$companies[$company['company_id']] = $company['company_name'];
				}
				$company_array[] = $companies;
				$match_owners_dd = Forms::getDropDown($owner, $company_array, $owner_id , true);
				break;			
		}
		return $match_owners_dd;
	}

	public static function displayMatchList() {  
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
		$total = $db->get('matches');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('MATCHES_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			if($_SESSION['admin_level'] < 300) { 
				$db->where('match_owner', $_SESSION['userid']);
				$db->orWhere('match_owner', $_SESSION['companyid']);
			}
			$db->orderBy('match_title', 'ASC');
			$matches = $db->get('matches', array($offset, $limit));
			foreach ($matches as $match) {
				$column = array(
						'ID' => $match['match_id'],
						'TITLE' => $match['match_title'], 
						'ADDRESS' => $match['match_street'] . ' ' . $match['match_zip'] . ' ' . $match['match_city'],
						'GPS' => round($match['match_lng'], 2) . ':' . round($match['match_lat'], 2),
						'CREATED' => substr($match['match_created'], 0, -5),
						'CHANGED' => substr($match['match_changed'], 0, -5)
					);
				if ($match['match_owner_type'] == 1) {
					$db->where('user_id', $match['match_owner']);
					$owner = $db->getOne('users');
					$column['OWNER'] = $owner['user_first_name'] . ' ' . $owner['user_last_name'] . ' (' . constant('USER') . ')';
				}
				elseif ($match['match_owner_type'] == 2) {
					$table = '';
					$table_prefix = '';
					$db->where('company_id', $match['match_owner']);
					$owner = $db->getOne('companies');
					$column['OWNER'] = $owner['company_name'] . ' (' . constant('COMPANY') . ')';
				}
				else {
					$column['OWNER'] = '-';
				}
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'matches',
					'identifier' => 'match_id',
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

	public static function displayMatchPage($match = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($match)) {
			$db->where('match_matchurl', '/'.$match);
			$result = $db->getOne('matches');
			$html = '
					<div id="match_wrapper">
						<h1>' . $result['match_title'] . '</h1>
						<img src="' . $result['match_image'] . '" title="' . $result['match_title'] . '" />
						' . $result['match_long_desc'] . '
					</div>
					<div id="match_extra">
						<div id="match_contact">
							<div class="match_header">
								' . constant('CONTACT_INFORMATION') . '
							</div>
							<div id="match_address_wrapper">
								<table border="0" cellspacing="0" cellpadding="0">
				';
			if ($result['match_contact'] != '') {
				$html .='
								<tr>
									<td>
										' . constant('CONTACT_PERSON') . ':
									</td>
									<td>
										' . $result['match_contact'] . '
									</td>
								</tr>
								<tr>
									<td>
										' . constant('PHONE') . ':
									</td>
									<td>
										' . $result['match_contact_phone'] . '
									</td>
								</tr>
					';	
			}
			if ($result['match_zip'] != '') {
				$html .='
								<tr>
									<td>
										' . constant('STREET') . ':
									</td>
									<td>
										' . $result['match_street'] . '
									</td>
								</tr>
								<tr>
									<td>
										' . constant('CITY') . ':
									</td>
									<td>
										' . $result['match_zip'] . ' ' . $result['match_city'] . '
									</td>
								</tr>
					';
				if ($result['match_phone'] != '') {
					$html .= '
								<tr>
									<td>
										' . constant('PHONE') . ':
									</td>
									<td>
										' . $result['match_phone'] . '
									</td>
								</tr>
						';
				}
				if ($result['match_email'] != '') {
					$html .= '
								<tr>
									<td>
										' . constant('EMAIL') . ':
									</td>
									<td>
										' . $result['match_email'] . '
									</td>
								</tr>
						';
				}
			}
			$html .= '
							</table>
						</div>
							<div class="contact_information">
							
							</div>
						</div>
					</div>
				';
			
		}
		else {
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
				$query = $_GET['query'];
			}
			else {
				$page = 1;
				$query = $_POST['searchform_input'];
			}
			if (isset($_GET) || isset($_POST)) { 
				$search_hits = Search::getSearchResults($query, $page, 'matches');
			}
			else {
				$search_hits = Search::getSearchResults(null, $page, 'matches');
			}
			$summary = $search_hits['summary'];
			$html = '
					<div id="match_header">
						<h1>' . constant('MATCHES') . '</h1>
						' . $searchform . '
						'  . $summary . '
					</div>
					<div id="match">
				';
			if (count($search_hits['html'])) {
				foreach ($search_hits['html'] as $result) {
					$html .= $result;
				}
			}
			$html .= '
					</div>
					<div id="match_info">
						&nbsp;
					</div>
				';
		}
		return $html;
	}


}

?>