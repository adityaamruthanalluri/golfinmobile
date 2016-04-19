<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');

class Settings {

	public static function displaySettingList() {
		if ($_SESSION['admin_level'] < 99) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$ignore = array(
						'setting_id'
					);
		$settings = $db->getOne('settings');
		$html = '
					<h1>' . constant('SETTINGS') . '</h1>
					<table border="0" cellpadding="0" cellspacing="0" class="settingtable">
						<tr>
							<th>' . constant('SETTING') . '</th>
							<th>' . constant('VALUE') . '</th>
							<th class="tbl_save"><img src="/_icons/save.png" /></th>
						</tr>
			';
		foreach ($settings as $key => $value) {
			if (!in_array($key, $ignore)) {
				if ($value == -1) {
					$html .= '
						</table>
						<table border="0" cellpadding="0" cellspacing="0" class="settingtable" id="' . $key . '">
							<tr>
								<th colspan="2">' . constant(strtoupper($key)) . '</th>
								<th class="tbl_save"><img src="/_icons/save.png" /></th>
							</tr>
						';
				}
				else {
					$html .= '
							<tr id="' . $key . '">
								<td>' . constant(strtoupper($key)) . '</td>
								<td class="setting_change';
					if (is_numeric($value)) {
						$html .= ' numeric';
					}
					$html .= '" contenteditable="true">' . $value . '</td>
								<td class="setting_save" id="' . $key . '"><img src="/_icons/save.png" /></td>
							</tr>
						';
				}
			}
		}
		$html .= '
					</table>
			';
		return $html;
	}
	
	public static function getSettings($cols = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = $db->getOne('settings', $cols);
		return ($settings);
	}

	public static function getTableColumns($action, $class, $id = NULL) {
		$data['table'] = 'settings';
			$data['identifier'] = 'settings_id';
			$data['tableclass'] = $class;
			switch ($action) {
				case 'list':
					$data['use_id'] = 'setting_id';
					break;
				case 'form':
					$data['name'] = 'settings';
					$data['use_table'] = 'settings';
					$data['return_uri'] = '/admin/settings/';
					$data['ignore'] = array(
								'setting_id'
							);
					$data['text'] = array(
								'setting_match_limit',
								'setting_match_offset'
							);
					break;
			}
	}
}

?>