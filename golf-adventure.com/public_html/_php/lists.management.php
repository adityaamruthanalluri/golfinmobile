<?php

class Lists {

	public static function createList($columns, $data) { 
		$URI = Common::getURI();
		if (count($columns) > 0) {
			$html = '';
			$html .= '
					<table cellspacing="0" cellpadding="0" class="admintable">
								<tr>
				';
			foreach ($columns[0] as $key => $value) {
				$class = '';
				if (is_array($data['icon']) && in_array($key, $data['icon'])) {
					$header = '<img src="/_icons/icon_' . strtolower($key) . '.png" title="' . constant(strtoupper($key)) . '"/>';
					$class = 'icon';
				}
				else {
					$header = constant($key);
				}
				$html .= '
									<th class="' . $class . '">
										' . $header . '
									</th>
					';
			}
			$html .= '
									<th class="tbl_edit"><img src="/_icons/edit.png" alt="' . constant('CHANGE') . '" /></th>
									<th class="tbl_delete red">&nbsp;&#x2717</th>
								</tr>
					';
			foreach ($columns as $column) { 
				$id = $column['ID'];
				$get_data = array(
						'db_action' => $data['db_action'],
						'table' => $data['table'],
						'column' => $data['identifier'],
						'return_uri' => $_SERVER['REQUEST_URI'],
						'id' => $id
					);
				if ($data['table'] == 'articles') {
					$get_data['categories'] = 1;
					$get_data['tags'] = 1;
				}
				session_start();
				if (isset($_SESSION['userid'])) {
					$array_name = $_SESSION['userid'] . '-' . date('y-m-d-h-i-s') . $id;
					$_SESSION[$array_name] = $get_data;
				}
				else {
					die('Unauthorized!');
				}
				$html .= '
								<tr>
					';
				foreach ($column as $key => $value) { 
					$class = '';
					if (is_array($data['numeric']) && in_array($key, $data['numeric'])) {
						$class .= ' numeric ';
					}
					if (is_array($data['icon']) && in_array($key, $data['icon'])) {
						$class .= ' icon tbl_edit ';
					}
					if (is_array($data['ajax_edit']) && in_array($key, $data['ajax_edit'])) {
						$class .= ' ajax_edit ';
					}
					if (is_array($data['datetime']) && in_array($key, $data['datetime'])) {
						$value = substr($value, 0, -3);
						$class .= ' numeric ';
					}
					if (is_array($data['date']) && in_array($key, $data['date'])) {
						$value = substr($value, 0, -9);
						$class .= ' numeric ';
					}
					if (is_array($data['bool']) && in_array($key, $data['bool'])) { 
						$class .= ' bool ';
						if ($value) {
							$value = '
										<div class="list_bool green ' . $data['table'] . '" id="' . $id . '">
											&#x2713;
										</div>
								';
						}
						else {
							$value = '
										<div class="list_bool red ' . $data['table'] . '" id="' . $id . '">
											&#x2717;
										</div>
												';
						}
					}
					$class = substr($class, 0, -1);
					$html .= '
									<td class="' . $class . '">
						';
					if (is_array($data['linked']) && in_array($key, $data['linked'])) { 
						$html .= '<a href="' . $URI . '/update/?id=' . $id . '" title="' . constant('CHANGE') . '">';
					}
					if ($value != '') {
						$html .= $value;
					}
					else {
						$html .= 'N/A';
					}
					if (is_array($data['linked']) && in_array($key, $data['linked'])) {
						$html .= '</a>';
					}
					$html .= '
									</td>
						';
				}
				$html .= '							
									<td class="tbl_edit"><a href="' . $URI . '/update/?id=' . $id . '" title="' . constant('CHANGE') . '"><img src="/_icons/edit.png" /></a></td>
									<td class="tbl_delete"><a href="/_php/db.management.php/?data=' . $array_name . '" class="link_delete red" title="' . constant('DELETE') . '">&nbsp;&#x2717</a></td>
								</tr>
						';
			}
			unset($get_data);
			$html .= '</table>';
		}
		else {
			$html = '
					<p>' . constant('NO_' . strtoupper($data['table'])) . '</p>
				';
		}
		return $html;
	}
	
	public static function listAutosearch($prefix, $searchid, $view = false) {
		$html = '
				<div class="autosearch_row border_bottom">
					<div class="autosearch_input">
			';
		if ($view) {
			$html .= '
						<input type="button" id="' . $prefix . 'view" value="' . constant('VIEW') . '" />
				';
		}
		$html .= '
						<input type="button" id="' . $prefix . 'edit" value="' . constant('EDIT') . '" />
						<input type="text" id="' . $searchid . '" class="conn_auto ui-autocomplete-input" autocomplete="off">
					</div>
					<div class="clear_both"></div>
				</div>
			';
		return $html;
	}
	
}

?>