<?php
//displaySearchForm($action, $class)
//getSearchResults($table, $sort)

require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');

class Search {
	
	public static function displaySearchForm($action, $class) {
		$html = '
				<div id="searchform">
					<form action="/' . mb_strtolower(constant('ARCHIVE'), 'utf-8') . '/" method="post" name="searchform">
						<div id="search_input">
							<input type="text" name="searchform_input" id="archive" value="' . strtoupper(constant('TEXT_SEARCHBOX')) . '" autocomplete="off" class="' . $class . '" />
							<input type="submit" value="" />
						</div><!--#search_submit-->
						<div class="SZoomIT_credits clear_both">Search powered by <a href="http://www.szoomit.com/" target="_blank"><img src="/_icons/szoomit_logotype_20.png" width="74" height="20"></a></div>
					</form>
				</div><!--#searchform-->
			';
		return $html;
	}
	
	public static function getSearchResults($query = null, $page, $table) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		$limit = $settings['setting_archive_offset'];
		$offset = ($page * $limit) - $limit;
		$all_categories = Categories::getActiveCategories($table); 
		if (isset($_GET['searchform_cat'])) {
			$all_cats = $_GET['searchform_cat'];
		}
		elseif (isset($_POST['searchform_cat'])) {
			$all_cats = $_POST['searchform_cat'];
		}
		else {
			foreach ($all_categories as $key => $value) {
				$all_cats[] = $key;
			}
		}
		if ($table == 'article') {
			$cols = array('article_category_rel_article_id');
		}
		elseif ($table == 'matches') {
			$cols = array('match_category_rel_match_id');
		}
		if (isset($_POST['searchform_cat'])) {
			foreach ($_POST['searchform_cat'] as $cat) {
				if ($table == 'article') {
					$db->orWhere('article_category_rel_id', $cat);
				}
				elseif ($table == 'matches') {
					$db->orWhere('match_category_rel_id', $cat);
				}
			}
			if ($table == 'article') {
				$categories = $db->get('article_categories_rel', null, $cols);
			}
			elseif ($table == 'matches') {
				$categories = $db->get('match_categories_rel', null, $cols);
			}
			foreach ($categories as $category) {
				if ($table == 'article') {
					$cats[] = $category['article_category_rel_article_id'];
				}
				elseif ($table == 'article') {
					$cats[] = $category['match_category_rel_match_id'];
				}
			}
			$cats = array_unique($cats);
			$categories = implode(', ', $cats);
			if ($table == 'article') {
				$cat_sql = 'AND article_id IN (' . $categories . ')';
			}
			elseif ($table == 'matches') {
				$cat_sql = 'AND match_id IN (' . $categories . ')';
			}
		}
		else {
			$cat_sql = '';
		}
		if (isset($query) && $query!=null) {
			$queryArray = explode(' ', $query);
			foreach ($queryArray as $item) {
				$params .= '+' . $item . '* ';
			}
			$params = array(
					substr($params, 0, -1)
				);
			if ($table == 'articles') {
				$q = 'MATCH(article_title, article_menu_title, article_summary, article_body) AGAINST(? IN BOOLEAN MODE )';
			}
			elseif ($table == 'matches') {
				$q = 'MATCH(match_title, match_short_desc, match_long_desc) AGAINST(? IN BOOLEAN MODE )';
			}
		}
		else {
			$q = '1=1';
			$query = null;
		}
		$sql = '
				SELECT DISTINCT
					SQL_CALC_FOUND_ROWS
			';
		if ($table == 'articles') { 
			$sql .= '
						article_url,
						article_title,
						article_summary,
						article_body,
						article_created,
						article_changed
					FROM 
						articles
					WHERE 
						' . $q . '
					AND article_lft > 1
					AND article_status = 1
					AND article_body != ""
						' . $cat_sql . '
					AND article_lang = "' . $_SESSION['site_language'] . '"
					ORDER BY article_rank DESC, article_created DESC
				';
		}
		elseif ($table == 'matches') {
			$sql .= '
						match_url,
						match_matchurl,
						match_title,
						match_image,
						match_short_desc,
						match_long_desc,
						match_created,
						match_changed
					FROM 
						matches
					WHERE 
						' . $q . '
					AND match_status = 1
					AND match_long_desc != ""
						' . $cat_sql . '
					ORDER BY match_title ASC, match_created ASC, match_rank DESC
				';
		}
		$sql .= '
				LIMIT ' . $limit . '
				OFFSET ' . $offset
			; 
		$results = $db->rawQuery($sql, $params); 
		$sql = 'SELECT FOUND_ROWS()';
		$count = $db->rawQuery($sql); 
		$total = $count[0]['FOUND_ROWS()'];
		foreach ($results as $item) { 
			if ($table == 'articles') {
				$title = $item['article_title'];
				$image = null;
				$description = $item['article_summary'];
				$url = $item['article_url'];
				$created = $item['article_created'];
				$changed = $item['article_changed'];
			}
			elseif ($table == 'matches') {
				$title = $item['match_title']; 
				$image = $item['match_image'];
				$description = $item['match_short_desc'];
				$url = '/' . strtolower(constant('MATCHES')) . $item['match_matchurl'];
				$created = $item['match_created'];
				$changed = $item['match_changed'];
			}
			$desc = substr(strip_tags($description), 0, 340);
			str_replace('å', '&aring;', $desc);
			str_replace('Å', '&Aring;', $desc);
			str_replace('ä', '&auml;', $desc);
			str_replace('Ä', '&Auml;', $desc);
			str_replace('ö', '&ouml;', $desc);
			str_replace('Ö', '&Ouml;', $desc);
			if (strlen($item['article_summary']) > strlen($desc)) {
				$desc .= '...';
			}
			$html = '
					<div class="search_hit" class="clear_both">
						<div class="search_hit_content">
							<h2 class="search"><a href="http://' . $_SERVER['SERVER_NAME'] . $url . '">' . $title . '</a></h2>
							<p><a href="http://' . $_SERVER['SERVER_NAME'] . $url . '" class="search_link">http://' . $_SERVER['SERVER_NAME'] . $url. '</a></p>
							<div class="serch_summary">
				';
			if ($image != '') {
				$html .= '
								<div class="search_summary_image">
									<img src="' . $image . '" title="' . $title . '" />
								</div>
					';
			}
			$html .= '
								<div class="search_summary_text">
									' . strip_tags($desc) . '
								</div>
							</div>
							<div class="clear_both"></div>	
							<p class="published"><em>' . constant('PUBLISHED') . ': ' . substr($created, 0, -3)
				;
			if ($item['article_changed'] != '0000-00-00 00:00:00') {
				$html .= '
							- ' . constant('CHANGED') . ': ' . substr($changed, 0, -3)
					;
			}
			$html .= '
							</em></p>
						</div><!--.search_hit_content-->
					</div><!--.search_hit-->
				';
			$result[] = $html;
		}
		if ($total > $limit) {
			$result[] = Navigation::buildPageNavigation($_SERVER['REQUEST_URI'], $query, $page, $total, $limit);
		}
		$return['summary'] = '
					<div id="search_summary">
			';
		if ($offset + $limit <= $total) {
			$offset_stop = $offset + $limit;
		}
		else {
			$offset_stop = $total;
		}
		if (count($results) > 1) {
			$hits = ($offset + 1) . ' - ' . $offset_stop;
		}
		else {
			$hits = $offset_stop;
		}
		if (count($results) > 0) {
			if (count($result) == 1) {
				$hit_indicator = mb_strtolower(constant('SEARCH_HIT'), 'UTF-8');
			}
			else {
				$hit_indicator = mb_strtolower(constant('SEARCH_HITS'), 'UTF-8');
			}
			foreach ($all_cats as $cat_id) {
				$db->where('category_id', $cat_id);
				$cat_name = $db->getOne('categories');
				$category_titles .= '<strong>' . mb_strtolower($cat_name['category_title'], 'UTF-8') . '</strong>, ';
			} 
			$category_titles = substr($category_titles, 0, -2);
			if (count($all_cats) > 1) {
				$category_titles = Common::strReplaceLast(',', ' ' . mb_strtolower(constant('AND'), 'UTF-8'), $category_titles);
			}
			if (count($all_cats)==1) {
				$category_indicator = constant('IN') . ' ' . mb_strtolower(constant('THE_CATEGORY'), 'UTF-8') . ' ' . $category_titles;
			}
			elseif (count($all_cats) < count($all_categories)) {
				$category_indicator = constant('IN') . ' ' . mb_strtolower(constant('THE_CATEGORIES'), 'UTF-8') . ' ' . $category_titles;
			}
			else {
				$category_indicator = mb_strtolower(constant('IN'), 'UTF-8') . ' ' . mb_strtolower(constant('ALL'), 'UTF-8') . ' ' . mb_strtolower(constant('CATEGORIES'), 'UTF-8');
			}
			$return['summary'] .= '
						' . constant('SHOWING') . ' ' . mb_strtolower(constant('SEARCH_HIT'), 'UTF-8') . ' ' . $hits . ' ' . constant('OF') . ' ' . mb_strtolower(constant('TOTALLY'), 'UTF-8') . '
						' . ($total) . ' ' . $hit_indicator . ' ' . $category_indicator
				;
		}
		else {
			$return['summary'] .= constant('NO_HITS_FOUND');
		}
		if (isset($query) && $query!=null) {
			$return['summary'] .= ' ' . mb_strtolower(constant('ON'), 'UTF-8') . ' <span class="search_query">&quot;' . $query . '&quot</span>';
			}
		$return['summary'] .= '.
					</div>
			';
		$return['html'] = $result;
		return $return;
	}
	
}