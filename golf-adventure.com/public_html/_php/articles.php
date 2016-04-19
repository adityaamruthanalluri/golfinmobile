<?php
/*
displayArticle($article)
displayArticleArchive()
displayArticleForm($id = null)
displayArticleList()
displayArticleMenu()
displayArticleSummaries()
displayNestedArticleSelect($parent, $after, $level)
getNestedArticleDropdown($id = null)
*/
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/forms.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'getChildren':
			$db->where('article_id', $_POST['id']);
			$result = $db->getOne('articles');
			$level = $result['article_lvl'] + 1;
			$html = Articles::displayNestedArticleSelect($_POST['id'], null, $level);
			echo $html;
			break;
		case 'cleanUrl':
			$uri = $_POST['title'];
			$cleanUrl = Common::cleanUrl($uri);
			$post_id = $_POST['post_id'];
			$parent = $_POST['parent'];
			if ($post_id > 0) {
				$db->where('article_id', $post_id);
				$old_url = $db->getOne('articles');
				$old_parent = substr( $old_url['article_url'], 0, strrpos($old_url['article_url'], '/') );
				$new_url = $old_parent . '/' . $cleanUrl;
			}
			else {
				$parent = $_POST['parent'];
				$cols = array('article_title','article_parent','article_url');
				$db->where('article_id', $parent);
				$result = $db->getOne('articles', $cols);
				$article_parent_url = $result['article_url'];
				$new_url = $article_parent_url . '/' . $cleanUrl;
			}
			$params[] = $new_url;
			$sql = 'SELECT article_title FROM articles WHERE article_url = ? ORDER BY article_title';
			$arts = $db->rawQuery($sql, $params);
			$num_arts = count($arts);
			if ($num_arts > 0) {
				$art_num = $num_arts + 1;
				$new_url .= '-' . $art_num;
			}
			echo $new_url;
			break;
		case 'update_startpage_dontmiss':
			$db->delete('articles_dontmiss');
			$articles = explode(',', substr($_POST['posts'], 0, -2));
			$i = 0;
			foreach ($articles as $article) {
				if ($article> 0) {
					$i++;
					$data = array('ad_article_id' => $article,
							'ad_place' => $i
						);
					$result = $db->insert('articles_dontmiss', $data);
				}
			}
			echo $result;
			break;
		case 'update_startpage_recommend':
			$db->delete('article_recommend');
			$articles = explode(',', substr($_POST['posts'], 0, -2));
			foreach ($articles as $article) {
				if ($article> 0) {
					$data = array('ar_article_id' => $article);
					$db->where('ar_id = 1');
					$db->update('article_recommend', $data);
				}
			}
			echo $result;
			break;
		case 'article_lang_rel':
			$exists = false;
			foreach ($site_languages as $language) {
				$db->where($language, $_POST['parent']);
				$result = $db->getOne('article_lang_rel');
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
				$db->insert('article_lang_rel', $data);
			}
			echo $_POST['parent'] . '->' . $_POST['child'] . ' = ' . $_POST['plang'] . ' => ' . $_POST['clang'];
			break;
	}
}

class Articles {
	
	public static function displayArticle($article, $top = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$new_body = $article['article_body'];
		if (array_key_exists('offers', $admin_head_categories)) {
			$db->orderBy('ao_place', 'ASC');
			$db->where('ao_article_id', $article['article_id']);
			$offers = $db->get('article_offers');
			$offers_count = count($offers); 
			$text_length = strlen($article['article_body']); 
			$offer_divider = $text_length/($offers_count + 1); 
			$offer_divider = (int)$offer_divider; 
			$start = $offer_divider;
			$n = 0;
			for ($i=0;$i<$offers_count;$i++) {
				$offer_html = '
							<div class="article_banner clear_both">
						';
					$offer_html .= Offers::getOffers('startpage', $offers[$n]['ao_offer_id']);
					$offer_html .= '
							</div>
						';
				$insert = strpos($new_body, '</p>', $start); 
				$new_body = substr_replace($new_body, $offer_html, $insert+5, 0);
				$start += $offer_divider + strlen($offer_html);
				$n++;
			}
		}
		if (strpos($new_body, '[')) {
			$start = strpos($new_body, '[') + 1;
			$end = strpos($new_body, ']') - $start;
			$insert_obj = substr($new_body, $start, $end);
			switch ($insert_obj) {
				case 'contact-form':
					$input = Forms::contactForm() ;
					break;
			}
		$new_body = str_replace('['.$insert_obj.']', $input, $new_body);
		}
		$info = self::displayArticleInfo($article['article_id']);
		Navigation::addHit('articles', $article['article_id']);
		$html = '
				<div id="article">
					<div id="article_wrapper" class="startpage_article_' . $article['article_id']
			;
		if (isset($top)) {
			$html .= ' top_article';
		}
		$html .= '">
						<h1>' . $article['article_title'] . '</h1>
						<p>
							<em class="publish_date">' . substr($article['article_created'], 0, 10) . '</em>
						</p>
						<div class="article_body">
							' . $new_body . '
						</div>
					</div>
				</div>
			';
		return $html;
	}
	
	public static function displayChronicles($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('article_title', 'Chronicles');
		$chronicles = $db->getOne('articles');
		$chronicles_lft = $chronicles['article_lft'];
		$chronicles_rgt = $chronicles['article_rgt'];
		
		if (isset($id)) {
			$db->where('article_id', $id);
			$choosen_article = $db->getOne('articles');
			$article = self::displayArticle($choosen_article);
		}
		else {
			$db->where('article_lft BETWEEN ' . ($chronicles_lft+1) . ' AND ' . ($chronicles_rgt-1));
			$db->orderBy('article_created', 'DESC');
			$latest_article = $db->getOne('articles');
			$article = self::displayArticle($latest_article);
			$article = str_replace('<div id="article_info">
					&nbsp;
				</div>', '', $article);
		}
		$arccols = array(
				'article_id',
				'article_title',
				'article_summary',
				'article_created'
			);
		$db->where('article_lft BETWEEN ' . ($chronicles_lft+1) . ' AND ' . ($chronicles_rgt-1));
		$db->orderBy('article_created', 'DESC');
		$chronicle_archive = $db->get('articles', null, $arccols);
		$html = '
					' . $article . '
				</div>
				<div id="chronicle_info">
					<h3>' . constant('CHRONICLE_ARTCHIVE') . '</h3>
			';
		foreach ($chronicle_archive as $item) {
			$html .= '
					<div class="chronicle_summary">
						<div class="chronicle_summary_date">
							' . substr($item['article_created'], 0, 10) . ' 
						</div>
						<div class="chronicle_summary_title">
							<a href="' . $_SERVER['REQUEST_URI'] . '/?id=' . $item['article_id'] . '" title="' . $item['article_title'] . '">' . mb_strtoupper($item['article_title'], 'utf-8') . '</a>
						</div>
						<div class="chronicle_summary_sum">
							' . $item['article_summary'] . ' 
						</div>
					</div>
				';
		}
		
		
		
		return $html;
	}
	
	public static function displayArticleArchive() {
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
			$query = $_GET['query'];
		}
		else {
			$page = 1;
			$query = $_POST['searchform_input'];
		}
		if (isset($_GET) || isset($_POST)) { 
			$search_hits = Search::getSearchResults($query, $page, 'articles');
		}
		else {
			$search_hits = Search::getSearchResults(null, $page, 'articles');
		}
		$summary = $search_hits['summary'];
		$html = '
				<div id="article_header">
					<h1>' . constant('ARCHIVE') . '</h1>
					'  . $summary . '
				</div>
				<div id="article">
			';
		if (count($search_hits['html'])) {
			foreach ($search_hits['html'] as $result) {
				$html .= $result;
			}
		}
		$html .= '
				</div>
				<div id="article_info">
					&nbsp;
				</div>
			';
			
		return $html;
	}
	
	public static function displayArticleStartpage() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('article_startpage > 0');
		$db->where('article_lang', $_SESSION['site_language']);
		$db->orderBy('article_startpage','ASC');
		$articles = $db->get('articles');
		$html = '';
		$i = 0;
		foreach ($articles as $article) {
			if ($i == 0) {
				$top = 1;
			}
			else {
				$top = NULL;
			}
			$i++;
			$html .= self::displayArticle($article, $top);
		}
		return ($html);
	}
	
	public static function displayArticleForm($id = null) { 
		if ($_SESSION['admin_level'] < 201) {
			return Common::displayUnauthorized();
			die();
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (isset($id)) {
			$col = array('article_parent','article_lvl');
			$db->where('article_id', $id);
			$result = $db->getOne('articles');
			$parent = $result['article_parent'];
			$level = $result['article_lvl'];
			$lang = $result['article_lang'];
			$owner_id = $result['article_author'];
			$submit = constant('UPDATE');
			$db_action = 'nested_update';
			$db->where('article_category_rel_article_id', $id);
			$art_cats = $db->get('article_categories_rel', null, array('article_category_rel_id')); 
			foreach ($art_cats as $value) {
				$article_cats[] = $value['article_category_rel_id'];
			}
			$startpage_position = $result['article_startpage'];
			$col = array('tag_title');
			$db->where('article_tag_rel_article_id', $id);
			$db->join('tags', 'article_tag_rel_id = tag_id', 'LEFT');
			$tags = $db->get('article_tags_rel', null, $col);
			foreach ($tags as $item) {
				$tagstr .= $item['tag_title'] . ', ';
			}
			$tags = substr($tagstr, 0, -2);
			$result['article_tags'] = $tags;
		}
		else {
			$owner_id = $_SESSION['userid'];
			$submit = constant('CREATE');
			$db_action = 'nested_insert';
			$article_cats[] = '-';
			$result = null;
			$startpage_position = 0;
		}
		$article_dd = self::getNestedArticleDropdown($id);
		$settings = Settings::getSettings();
		$startpage_summaries = $settings['setting_startpage_summaries'];
		$owner['id'] = $owner_id;
		$owner['table'] = 'users';
		//$owner['name'] = 'article_author';
		$owner['title'] = 'AUTHOR';
		/*$db->where('user_account_type >= 3');
		$db->orderBy('user_last_name');
		$authors = $db->get('users');
		foreach ($authors as $author) {
			$users[$author['user_id']] = $author['user_first_name'] . ' ' . $author['user_last_name'];
		}
		$authors_array[] = $users;
		$article_author = Forms::getDropDown($owner, $authors_array, $owner_id , true);*/
		$cols = array('article_startpage');
		$db->where('article_startpage > 0');
		$db->where('article_lang', $_SESSION['site_language']);
		$startpage_articles = $db->get('articles', null, $cols);
		foreach ($startpage_articles as $item) {
			$startpage_items[] = $item['article_startpage'];
		}
		$data = array(
				'table' => 'articles',
				'identifier' => 'article_id',
				'table_prefix' => 'article_',
				'return_uri' => '/admin/articles/',
				'column' => 'article_id',
				'on_off' => array('article_status','article_in_menu'),
				'datetime' => array ('article_created','article_changed'),
				'required' => array ('article_title','article_summary','article_body','article_url'),
				'article_parent' => $article_dd,
				'startpage_items' => $startpage_items,
				'startpage_position' => $startpage_position,
				'startpage_summaries' => $startpage_summaries,
				'post_id' => $id,
				'parent' => $parent,
				'level' => $level,
				'categories' => $categories,
				'article_categories' => $article_cats,
				'tags' => $tags,
				'submit' => $submit,
				'db-action' => $db_action
			);
		if (!isset($id)) { 
			$fields['article_parent'] = 'nested_dropdown';
		}
		else { 
			$db->where($_SESSION['site_language'], $lang);
			$taken_lang = $db->getOne('article_lang_rel');
			if ($taken_lang) {
				foreach ($taken_lang as $key => $value) {
					if ($value != 0) {
						$active[] = $key;
					}
				}
			}
			foreach ($site_languages as $lang) {
				$lang_all[] = $lang;
			}
			$data ['rel_language'] = array (
									'this' => $_SESSION['site_language'],
									'all' => $lang_all,
									'taken' => array($active)
								);
			$fields['post_id'] = 'hidden';
			$fields['rel_language'] = 'rel_language';
			$result['post_id'] = $result['article_id'];
		}
		$fields['article_title'] = 'text';
		$fields['article_menu_title'] = 'text';
		$fields['article_summary'] = 'small_editor';
		$fields['article_body'] = 'large_editor';
		$fields['article_tags'] = 'text';
		$fields['article_url'] = 'text';
		//$fields['article_author'] = 'dropdown';
		//$fields['article_categories'] = 'multiple_check';
		$fields['article_in_menu'] = 'check';
		$fields['article_status'] = 'check';
		$fields['article_startpage'] = 'multiple_radio';
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayArticleInfo($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->join('users', 'user_id=article_author', 'LEFT');
		$db->where('article_id', $id);
		$article = $db->getOne('articles');
		if ($article) {
			$author = $article['user_first_name'] . ' ' . $article['user_last_name'];
			$author_image = $article['user_image'];
			$author_email = $article['user_email'];
			
			$html = '
					<div class="author_presentation">
						<div class="image">
							<img src="' . $author_image . '" />
						</div>
						<div class="author">
							<strong>' . $author . '</strong><br />
							' . $author_email . '<br />
						</div>
					</div>
				';
		}
		$db->join('categories', 'category_id=article_category_rel_id');
		$db->where('article_category_rel_article_id', $id);
		$categories = $db->get('article_categories_rel');
		if ($categories) {
			$html .= '
					<div class="categories">
						<div class="info_title">
							' . constant('CATEGORIES') . '
						</div>
				';
			foreach ($categories as $category) {
				$html .= '
						<div class="category">
							' . $category['category_title'] . '
						</div>
					';
			}
			$html .= '
						<div class="clear_both"></div>
					</div>
				';
		}
		$db->join('tags', 'tag_id=article_tag_rel_id');
		$db->where('article_tag_rel_article_id', $id);
		$tags = $db->get('article_tags_rel');
		if (count($tags) > 1) {
			$html .= '
					<div class="tags">
						<div class="info_title">
							' . constant('TAGS') . '
						</div>
				';
			foreach ($tags as $tag) {
				$html .= '
						<div class="tag">
							' . $tag['tag_title'] . '
						</div>
					';
			}
			$html .= '
						<div class="clear_both"></div>
					</div>
				';
		}
		return $html;
	}
	
	public static function displayArticleList() {
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
		$total = $db->get('articles');
		if (count($total) == 0) {
			$html = '
					<p>' . constant('ARTICLES_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$sql = '
						SELECT 
							CONCAT( REPEAT("&raquo;&nbsp;", COUNT(parent.article_title) - 2), node.article_title) AS name,
							node.article_id,
							node.article_lang,
							node.article_lvl,
							node.article_lft,
							node.article_rgt,
							node.article_author,
							node.article_summary,
							node.article_url,
							node.article_hits,
							node.article_in_menu,
							node.article_status,
							node.article_startpage,
							node.article_created,
							node.article_changed
						FROM articles AS node,
	        				articles AS parent
						WHERE node.article_lft BETWEEN parent.article_lft AND parent.article_rgt
						AND node.article_lft > 1
						AND node.article_lang = "' . $_SESSION['site_language'] . '"
						GROUP BY node.article_id
						ORDER BY node.article_lft
						LIMIT ' . $limit . '
						OFFSET ' . $offset . '
				';
			$articles = $db->rawQuery($sql);
			foreach ($articles as $article) {
				$cols = array('user_first_name', 'user_last_name');
				$db->where('user_id', $article['article_author']);
				$author = $db->getOne('users', $cols);
				$column = array(
						'ID' => $article['article_id'],
						'ARTICLE' => substr($article['name'], 0, 60), 
						'LANGUAGE' => $article['article_lang'],
						'HITS' => $article['article_hits'],
						'STATUS' => $article['article_status'],
						'IN_MENU' => $article['article_in_menu'],
						'ON_STARTPAGE' => $article['article_startpage'],
						'CREATED' => $article['article_created'],
						'CHANGED' => $article['article_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID', 'HITS'),
					'datetime' => array('CREATED', 'CHANGED'),
					'bool' => array('STATUS', 'IN_MENU', 'ON_STARTPAGE'),
					'ajax-edit' => array('STATUS', 'IN_MENU', 'ON_STARTPAGE'),
					'icon' => array('HITS', 'STATUS', 'IN_MENU', 'ON_STARTPAGE'),
					'table' => 'articles',
					'identifier' => 'article_id',
					'linked' => array('ARTICLE'),
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
	
	public static function displayArticleMenu() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		
		//if (isset($_SESSION['userid'])) {
			
			
			//Golf Deals
			$i++;
			$item[$i]['id'] = 999999;
			$item[$i]['level'] = 1;
			$item[$i]['parent'] = 0;
			$item[$i]['menu_item'] = '<a href="/' . mb_strtolower(constant('GOLFDEALS')) . '/" class="golf-offers_menu_item';
			if ($_SERVER['REQUEST_URI'] == '/' . mb_strtolower(constant('GOLFDEALS')) . '/') { 
				$item[$i]['menu_item'] .= ' active_menu_link';
			}
			$item[$i]['menu_item'] .= '">' . constant('GOLF_DEALS') . '</a>';
			
			//Shop
			$i++;
			$item[$i]['id'] = 999999;
			$item[$i]['level'] = 1;
			$item[$i]['parent'] = 0;
			$item[$i]['menu_item'] = '<a href="/' . mb_strtolower(constant('SHOP')) . '/" class="shop_menu_item';
			if ($_SERVER['REQUEST_URI'] == '/' . mb_strtolower(constant('SHOP')) . '/') { 
				$item[$i]['menu_item'] .= ' active_menu_link';
			}
			$item[$i]['menu_item'] .= '">' . mb_strtolower(constant('SHOP')) . '</a>';
			
			//Destinations
			$i++;
			$item[$i]['id'] = 999999;
			$item[$i]['level'] = 1;
			$item[$i]['parent'] = 0;
			$item[$i]['menu_item'] = '<a href="/' . mb_strtolower(constant('DESTINATIONS')) . '/" class="shop_menu_item';
			if ($_SERVER['REQUEST_URI'] == '/' . mb_strtolower(constant('DESTINATIONS')) . '/') { 
				$item[$i]['menu_item'] .= ' active_menu_link';
			}
			$item[$i]['menu_item'] .= '">' . constant('DESTINATIONS') . '</a>';
			
			/**Feedback
			$i++;
			$item[$i]['id'] = 999999;
			$item[$i]['level'] = 1;
			$item[$i]['parent'] = 0;
			$item[$i]['menu_item'] = '<a href="/' . mb_strtolower(constant('FEEDBACK')) . '/" class="feedback_menu_item';
			if ($_SERVER['REQUEST_URI'] == '/' . mb_strtolower(constant('FEEDBACK')) . '/') { 
				$item[$i]['menu_item'] .= ' active_menu_link';
			}
			$item[$i]['menu_item'] .= '">' . constant('FEEDBACK') . '</a>';
			}**/
			
			//Articles
			$i++;
			$sql = '
					SELECT 
						node.article_menu_title AS name,
						node.article_title,
						node.article_id,
						node.article_parent,
						node.article_lvl,
						node.article_url
					FROM articles AS node,
		        		articles AS parent
					WHERE node.article_lft BETWEEN parent.article_lft AND parent.article_rgt
					AND node.article_lft > 1
					AND node.article_in_menu = 1
					AND node.article_status = 1
					AND node.article_lang = "' . $_SESSION['site_language'] . '"
					GROUP BY node.article_id
					ORDER BY node.article_lft
				'; 
			$articles = $db->rawQuery($sql);
			if (count($articles) > 0) { 
				foreach ($articles as $article) { 
					if (strlen($article['name']) != 0) { 
						$name = $article['name'];
					}
					else {
						$name = $article['article_title'];
					}
					$item[$i]['id'] = $article['article_id'];
					$item[$i]['level'] = $article['article_lvl'];
					$item[$i]['parent'] = $article['article_parent'];
					$item[$i]['menu_item'] = '<a href="' . $article['article_url'] . '" id="menu_item_' . $name . '"';
					if ($_SERVER['REQUEST_URI'] == $article['article_url']) {
						$item[$i]['menu_item'] .= 'class="active_menu_link"';
					}
					$item[$i]['menu_item'] .= '>' . $name . '</a>';
					$i++;
				}
				if ($site_article_archive) { 
					$item[$i]['id'] = 999999;
					$item[$i]['level'] = 1;
					$item[$i]['parent'] = 0;
					$item[$i]['menu_item'] = '<a href="/' . mb_strtolower(constant('ARCHIVE')) . '/" class="archive_menu_item';
					if ($_SERVER['REQUEST_URI'] == '/' . mb_strtolower(constant('ARCHIVE')) . '/') { 
						$item[$i]['menu_item'] .= ' active_menu_link';
					}
					$item[$i]['menu_item'] .= '">' . constant('ARCHIVE') . '</a>';
				}
			}
		
			//Log in / My Page
			/*session_start();
			if (isset($_SESSION['userid'])) {
				$db->where('user_id', $_SESSION['userid']);
				$user = $db->getOne('users');
				$user_email = $user['user_email'];
				$i++;
				$item[$i]['id'] = 999998;
				$item[$i]['level'] = 1;
				$item[$i]['parent'] = 0;
				$item[$i]['menu_item'] = '<a href="/admin/" class="mypage_menu_item';
				if ($_SERVER['REQUEST_URI'] == '/admin/') { 
					$item[$i]['menu_item'] .= ' active_menu_link';
				}
				$item[$i]['menu_item'] .= '"><i class="fa fa-user"></i> ' . $user_email . '</a>';
				$i++;
				$item[$i]['id'] = 999999;
				$item[$i]['level'] = 2;
				$item[$i]['parent'] = 999998;
				$item[$i]['menu_item'] = '<a href="/admin/" class="mypage_menu_item';
				$item[$i]['menu_item'] .= '">' . constant('MY_PAGE') . '</a>';
				$i++;
				$item[$i]['id'] = 999999;
				$item[$i]['level'] = 2;
				$item[$i]['parent'] = 999998;
				$item[$i]['menu_item'] = '<a class="lightbox mypage_menu_item" id="logout';
				$item[$i]['menu_item'] .= '">' . constant('LOGOUT') . '</a>';
				
				
				
				
				
			}
			else {
				$i++;
				$item[$i]['id'] = 999999;
				$item[$i]['level'] = 1;
				$item[$i]['parent'] = 0;
				$item[$i]['menu_item'] = '<span id="login" class="lightbox login_menu_item">' . constant('ALREADY_LOG_IN') . '</span>';
			}*/
			
			
			$html = Menu::buildMenu($item, 0);
		
		return $html;	
	}
	
	public static function displayArticleSummaries() { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		if (array_key_exists('offers', $admin_head_categories)) {
			$db->orderBy('so_place', 'ASC');
			$offers = $db->get('startpage_offers'); 
		}
		$cols = array(
				'article_title',
				'article_summary',
				'article_body',
				'article_url'
			);
		$db->where('article_status', 1);
		$db->where('article_startpage > 0');
		$db->where('article_lang', $_SESSION['site_language']);
		$placed_articles = $db->get('articles'); //print_r($placed_articles[0]);die();
		$db->where('article_startpage > 0');
		$db->where('article_status', 1);
		$db->where('article_lang', $_SESSION['site_language']); 
		$db->orderBy('article_created', 'DESC');
		$recent_articles = $db->get('articles', $settings['setting_startpage_summaries'] - count($placed_articles));
		$n = 0;
		for ($i=0;$i<$settings['setting_startpage_summaries'];$i++) {
			$entered = false; 
			foreach ($placed_articles as $key => $value) {
				if ($value['article_startpage'] == ($i + 1)) { 
					$articles[$i] = $placed_articles[$key];
					$entered = true;
				}
			}
			if (!$entered) {
				$articles[$i] = $recent_articles[$n]; 
				$n++;
			}
		}
		$i = 0;
		$n = 0;
		foreach ($articles as $article) { 
			/*if ($article['article_body'] != '') { 
				$start = strpos($article['article_summary'], '<img');
				$end = strpos($article['article_summary'], '>', $start);
				$img = substr($article['article_summary'], $start, ($end - $start)+1); 
				if (strlen($img) > 10) {
					$text = str_replace($img, '', $article['article_summary']);
				}
			}
			else {
				$img = $article['article_summary'];
				$text = '';
			}*/
			$text = $article['article_summary'];
			if (strpos($text, '[')) {
				$start = strpos($text, '[');
				$end = strpos($text, ']');
				$action = substr($text, $start+1, $end - ($start+1));
				switch ($action) {
					case 'appRegForm':
						$insert = Forms::appRegistrationForm();
						$text = substr_replace($text, $insert, $start, $end-$start+1); 
						break;
				}
				
				
			}
			$i++;
			if ($i % $startpage_summaries_cols == 0) {
				$class = ' boxright pos_' . $i;
			}
			else {
				$class = ' pos_' . $i;
			}
			$html .= '
					<div class="article_summary' . $class . '">
				';
			if (strlen($img) > 10) {
				$html .= $img;
			}
			$html .= '
						<h1>
				';
			if ($article['article_body'] != '') { 	
				$html .= '<a href="' . $article['article_url'] . '">';
			}
			$html .= $article['article_title'];
			if ($article['article_body'] != '') { 	
				$html .= '</a>';
			}
			$html .= '</h1>
				
						<div class="article_summary_content">
							' . $text . '
				';
			if ($article['article_body'] != '') { 	
				$html .= '
							<div class="read_more right">
									<a href="' . $article['article_url'] . '">' . constant('READ_MORE') . '</a>
							</div>
					';
			}
			$html .= '
							<div class="clear_both"></div>
						</div>
					
						<div class="clear_both"></div>
					</div>
				';
			if (($i % $startpage_summaries_cols == 0) && ($i < $settings['setting_startpage_summaries']) && ($n < count($offers)) && (array_key_exists('offers', $admin_head_categories))) {
				$html .= '
						<div class="startpage_banner clear_both">
					';
				$html .= Offers::getOffers('startpage', $offers[$n]['so_offer_id']);
				$html .= '
						</div>
					';
				$n++;
			}
			$html .= '
					
				';
		}
		return $html;
	}
	
	public static function displayConnectionForm($action) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		switch ($action) {
			case '1':
				$db->join('articles',' articles.article_id=articles_dontmiss.ad_article_id', 'LEFT');
				$db->orderBy('ad_place', 'ASC');
				$result = $db->get('articles_dontmiss', null, $cols);
				foreach ($result as $item) {
					$articles .= '
						<div id="post_' . $item['ad_article_id'] . '" class="choosen_post">
							<span id="' . $item['ad_place'] . '" class="conn_place">' . $item['ad_place'] . '</span>' . $item['article_title'] . '<span id="delete_'  . $item['ad_article_id'] . '" class="delete_conn_post">X</span>
						</div>
					';
				}
				$data['action'] = '';
				$data['save_action'] = 'update_startpage_dontmiss';
				$data['post_id'] = 'article';
				$data['post'] = constant('DONTMISS');
				$data['posts'] = constant('DONTMISS');
				$data['has_parent'] = false;
				$data['parent_id'] = '';
				$data['parent'] = constant('SIDEBAR');
				$data['parents'] = '';
				$data['parent_url'] = '_/php/articles';
				$data['post_list'] = $articles;
				break;
			case '2':
				$db->join('articles',' articles.article_id=article_recommend.ar_article_id', 'LEFT');
				$result = $db->get('article_recommend', null, $cols);
				foreach ($result as $item) {
					$articles .= '
						<div id="post_' . $item['ar_article_id'] . '" class="choosen_post">
							' . $item['article_title'] . '<span id="delete_'  . $item['ar_article_id'] . '" class="delete_conn_post">X</span>
						</div>
					';
				}
				$data['action'] = '';
				$data['save_action'] = 'update_startpage_recommend';
				$data['post_id'] = 'article';
				$data['post'] = constant('RECOMMEND');
				$data['posts'] = constant('RECOMMEND');
				$data['has_parent'] = false;
				$data['parent_id'] = '';
				$data['parent'] = constant('SIDEBAR');
				$data['parents'] = '';
				$data['parent_url'] = '_/php/articles';
				$data['post_list'] = $articles;
				break;
		}
		$html = Forms::connectionForm($data);
		return $html;
	}
		
	public static function displayNestedArticleSelect($parent, $after, $level) { 
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$sql = '
				SELECT node.article_title as name,
						node.article_id
				FROM 
					articles AS node,
        			articles AS parent
				WHERE node.article_lft BETWEEN parent.article_lft+1 AND parent.article_rgt
        		AND parent.article_id = ' . $parent . '
        		AND node.article_lvl = ' . $level . '
        		AND node.article_lvl = ' . $level . '
        		AND node.article_lang = "' . $_SESSION['site_language'] . '"
				ORDER BY node.article_lft;
			';
		$result = $db->rawQuery($sql);
		if ($result) {
			$html = '
					<select name="article_parent_after">
						<option value="on_top">' . constant('ON_TOP') . '</option>
				';
			foreach ($result as $article) { 
				$html .= '<option value="' . $article['article_id'] . '"';
				if ($article['article_id'] == $after) {
					$html .= ' selected="selected"';
				}
				$html .= '>' . $article['name'] . '</option>';
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
	
	public static function displayRecommend() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$cols = array(
				'article_url'
			);
		$db->join('articles', 'article_id=article_category_rel_article_id', 'LEFT');
		$db->where('article_category_rel_id', 6);
		$db->orderBy('article_created', 'DESC');
		$article = $db->getOne('article_categories_rel', $cols);
		$recommend = '
				<div id="recommend_wrapper">
					<a href="' .$article['article_url'] . '">
						<div id="recommend_header">
							
						</div>
						<div class="clear_both"></div>
					</a>
				</div>
			';
		return $recommend;
	}
	
	public static function getNestedArticleDropdown($id = null) {
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$sql = '
				SELECT 
					CONCAT( REPEAT("--", COUNT(parent.article_title) - 1), node.article_title) AS name,
					node.article_id
				FROM articles AS node,
        			articles AS parent
				WHERE node.article_lft BETWEEN parent.article_lft AND parent.article_rgt
				AND node.article_lang = "' . $_SESSION['site_language'] . '"
				GROUP BY node.article_id
				ORDER BY node.article_lft;
			';
		$articles = $db->rawQuery($sql);
		return ($articles);
	}

}
?>