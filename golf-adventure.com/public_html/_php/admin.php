<?php

class Admin {

	public static function displayAdminMenu($level) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (count($admin_categories[$_SESSION['admin_level']]) > 0) {
			foreach ($admin_categories[$_SESSION['admin_level']] as $item) {
				$menu_items[] = $item;
			}
		}
		$html = '<ul>';
		if (count($menu_items) > 0) {
			$i = 0;
			foreach ($menu_items as $item) { 
				$menu_title = constant(strtoupper($item));
				$html .= '
						<li class="admin_menu_item';
				$html .= '">';
				$html .= '<a href="/admin/' . $item . '/" class="white">';
				$html .= $menu_title;
				$html .= '</a>';
				$html .= '</li>';
			}
		}
		$html .= '
					<li class="admin_menu_item"><a href="/admin" class="white">' . constant('MY_PAGE') . '</a></li>
					<li class="lightbox admin_menu_item" id="logout" class="last_item">' . constant('LOGOUT') . '</li>
				</ul>
			';
		return $html;
	}
}

?>


