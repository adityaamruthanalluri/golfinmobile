<?php 
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'getChildren':
			$db->where('category_id', $_POST['id']);
			$result = $db->getOne('categories');
			$level = $result['category_lvl'] + 1;
			$html = Categories::displayNestedCategorySelect($_POST['id'], null, $level);
			echo $html;
			break;
		case 'category_lang_rel':
			$exists = false;
			foreach ($site_languages as $language) {
				$db->where($language, $_POST['parent']);
				$result = $db->getOne('category_lang_rel');
				if ($result) {
					$exists = true;
					$lang = $language;
				}
			}
			if ($exists) {
				$data = array($_POST['clang'] => $_POST['child']);
				$db->where($_POST['plang'], $_POST['parent']);
				$db->update('article_lang_rel', $data);
			}
			else {
				$data = array($_POST['plang'] => $_POST['parent'], $_POST['clang'] => $_POST['child']);
				$db->insert('category_lang_rel', $data);
			}
			echo $_POST['parent'] . '->' . $_POST['child'] . ' = ' . $_POST['plang'] . ' => ' . $_POST['clang'];
			break;
		}
}

class Categories {

	public static function displayCategoryForm($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$col = array('category_parent','category_lvl');
			$db->where('category_id', $id);
			$result = $db->getOne('categories');
			$parent = $result['category_parent'];
			$level = $result['category_lvl'];
			$submit = constant('UPDATE');
			$db_action = 'nested_update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'nested_insert';
			$result = null;
		}
		$article_dd = self::getNestedCategoryDropdown($id);
		$data = array(
				'table' => 'categories',
				'identifier' => 'category_id',
				'table_prefix' => 'category_',
				'return_uri' => '/admin/categories/',
				'column' => 'article_id',
				'datetime' => array ('article_created','article_changed'),
				'category_parent' => $article_dd,
				'post_id' => $id,
				'parent' => $parent,
				'level' => $level,
				'submit' => $submit,
				'db-action' => $db_action
			);
			if (!isset($id)) {
				$fields['category_parent'] = 'nested_dropdown';
			}
			$fields ['category_title_EN'] = 'text';
			$fields ['category_title_SV'] = 'text';
		return Forms::Form($data, $fields, $result);
	}

	public static function displayCategoryList() {
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
					<p>' . constant('CATEGORIES_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$sql = '
					SELECT 
						CONCAT( REPEAT("&raquo;&nbsp;", COUNT(parent.category_title_EN) - 2), node.category_title_EN) AS name,
							node.category_id,
							node.category_lvl,
							node.category_lft,
							node.category_rgt,
							node.category_created,
							node.category_changed
						FROM categories AS node,
	        				categories AS parent
						WHERE node.category_lft BETWEEN parent.category_lft AND parent.category_rgt
						AND node.category_lft > 1
						GROUP BY node.category_id
						ORDER BY node.category_lft
						LIMIT ' . $limit . '
						OFFSET ' . $offset . '
				';
			$categories = $db->rawQuery($sql);
			foreach ($categories as $category) {
				$column = array(
						'ID' => $category['category_id'],
						'CATEGORY' => $category['name'], 
						'CREATED' => $category['category_created'],
						'CHANGED' => $category['category_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'categories',
					'identifier' => 'category_id',
					'linked' => array('CATEGORY'),
					'db_action' => 'nested_delete'
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
	
	public static function displayNestedCategorySelect($parent, $after, $level, $sel_name = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$sql = '
				SELECT node.category_title_EN as name,
					node.category_id
				FROM 
					categories AS node,
        			categories AS parent
				WHERE node.category_lft BETWEEN parent.category_lft+1 AND parent.category_rgt
        		AND parent.category_id = ' . $parent . '
        		AND node.category_lvl = ' . $level . '
				ORDER BY node.category_lft;
			';
		$result = $db->rawQuery($sql);
		if ($result) {
			$html = '
					<select name="category_parent_after">
						<option value="on_top">' . constant('ON_TOP') . '</option>
				';
			foreach ($result as $category) { 
				$html .= '<option value="' . $category['category_id'] . '"';
				if ($category['category_id'] == $after) {
					$html .= ' selected="selected"';
				}
				$html .= '>' . $category['name'] . '</option>';
			}
			$html .= '
					</select>
				';
		}
		else {
			$html = '<input type="text" disabled="disabled" value="' . constant('NO_SUBCATEGORY') . '" />';
		}
		return $html;
	}
	
	public static function getActiveCategories($table) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$cols = array('category_id', 'category_title_EN');
		if ($table == 'articles') {
			$db->join('categories', 'category_id = article_category_rel_id');
			$db->groupBy('article_category_rel_id');
			$categories = $db->get('article_categories_rel', null, $cols);
		}
		else if ($table == 'matches') {
			$db->join('categories', 'category_id = match_category_rel_id');
			$db->groupBy('match_category_rel_id');
			$categories = $db->get('match_categories_rel', null, $cols);
		}
		foreach ($categories as $category) {
			$active_category[$category['category_id']] = $category['category_title_EN'];
		}
		return $active_category;
	}
	
	public static function getNestedCategoryDropdown($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$sql = '
				SELECT 
					CONCAT( REPEAT("--", COUNT(parent.category_title_EN) - 1), node.category_title_EN) AS name,
					node.category_id
				FROM categories AS node,
        			categories AS parent
				WHERE node.category_lft BETWEEN parent.category_lft AND parent.category_rgt
				GROUP BY node.category_id
				ORDER BY node.category_lft;
			';
		$categories = $db->rawQuery($sql);
		return ($categories);
	}
	
	public static function deleteCategoryRel($prefix, $id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where($prefix . 'category_rel_' . $prefix . 'id', $id);
		$db->delete($prefix . 'categories_rel');
	}
	
	public static function updateCategoryRel($categories, $prefix, $id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where($prefix . 'category_rel_' . $prefix . 'id', $id);
		$db->delete($prefix . 'categories_rel');
		foreach ($categories as $category) { 
			$db->insert($prefix . 'categories_rel', array($prefix . 'category_rel_' . $prefix . 'id' => $id, $prefix . 'category_rel_id' => $category));
		}
	}

}

?>