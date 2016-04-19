<?php

class Slideshow {

	public static function displaySlideshow() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->orderBy('slideshow_image', 'ASC');
		$images = $db->get('slideshow');
		$html = '
				<div id="slideshow_container">
					<img class="phone-shell" src="/_files/Slideshow/phone-shell.png" />
					<ul>
			';
		foreach ($images as $image) {
			$html .= '
						<li>
							<img src="' . $image['slideshow_image'] . '" />
						</li>
				';
		}
		$html .= '
					</ul>
					<span class="button prevButton"></span>
					<span class="button nextButton"></span>
				</div>	
		 	';
		 return $html;
	}
	
	public static function displaySlideshowForm($id = null) {
		if ($_SESSION['admin_level'] < 201) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$db->where('slideshow_id', $id);
			$result = $db->getOne('slideshow');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'slideshow',
				'identifier' => 'slideshow_id',
				'table_prefix' => 'category_',
				'return_uri' => '/admin/slideshow/',
				'column' => 'slideshow_id',
				'datetime' => array ('slideshow_created','slideshow_changed'),
				'required' => array ('slideshow_image'),
				'post_id' => $id,
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields = array(
					'slideshow_image' => 'image',
				);
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displaySlideshowList() {
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
		$total = $db->get('categories');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('SLIDESHOW_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$slides = $db->get('slideshow');
			foreach ($slides as $slide) {
				$column = array(
						'ID' => $slide['slideshow_id'],
						'IMAGE' => $slide['slideshow_image'], 
						'CREATED' => $slide['slideshow_created'],
						'CHANGED' => $slide['slideshow_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'slideshow',
					'identifier' => 'slideshow_id',
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