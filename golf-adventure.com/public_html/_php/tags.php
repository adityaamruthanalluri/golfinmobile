<?php
//insertTags($tags_list, $result, $data['table_prefix'])
//removeTags($data['post_id'])
//updateTags($tags)

class Tags {
	
	public static function insertTags($tags, $prefix, $id) { 
		foreach ($tags as $key => $value) {
			$tags[$key] = mb_strtolower(trim($value), 'UTF-8');
		}
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		foreach ($tags as $tag) {
			$db->where('tag_title', trim($tag));
			$tag_exists = $db->getOne('tags');
			if ($tag_exists) {
				$update = array('tag_count' => $tag_exists['tag_count']+1);
				$db->where('tag_id', $tag_exists['tag_id']);
				$db->update('tags', $update);
				$tag_id = $tag_exists['tag_id'];
			}
			else {
				$insert = array('tag_title' => mb_strtolower(trim($tag), 'UTF-8'), 'tag_count' => 1);
				$result = $db->insert('tags', $insert);
				$tag_id = $result;
			}
			$insert = array(
					$prefix . 'tag_rel_id' => $tag_id,
					$prefix . 'tag_rel_' . $prefix . 'id' => $id
				);
			$result = $db->insert($prefix . 'tags_rel', $insert);
		}
	}
	
	public static function updateTags($new_tags, $prefix, $id) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		foreach ($new_tags as $key => $value) {
			$new_tags[$key] = mb_strtolower(trim($value), 'UTF-8');
		}
		$cols = array(
				'tag_title',
				'tag_id',
				'tag_count'
			);
		$db->where($prefix . 'tag_rel_' . $prefix . 'id', $id);
		$db->join('tags', $prefix . 'tag_rel_id = tag_id', 'LEFT');
		$old_tags = $db->get($prefix . 'tags_rel');
		if ($old_tags) { 
			foreach ($old_tags as $old_tag) {
				if (!in_array($old_tag['tag_title'], $new_tags)) { 
					if ($old_tag['tag_count'] == 1) {
						$db->where('tag_id', $old_tag['tag_id']); 
						$db->delete('tags');
					}
					else {
						$update = array('tag_count' => $old_tag['tag_count']-1); 
						$db->where('tag_id', $old_tag['tag_id']);
						if (!$db->update('tags', $update)) {
							die('Error subtrackting tag count');
						}
					}
					$db->where($prefix . 'tag_rel_' . $prefix . 'id', $id); 
					$db->where($prefix . 'tag_rel_id', $old_tag['tag_id']);
					$db->delete($prefix . 'tags_rel');
				}
			}
		}
		foreach ($new_tags as $tag) { 
			$istag = false;
			foreach ($old_tags as $old_tag) {
				if (mb_strtolower(trim($tag), 'UTF-8') == $old_tag['tag_title']) { 
					$istag = true;
				}
			}
			if (!$istag) {
				$inserts[] = mb_strtolower(trim($tag), 'UTF-8');
			}
		}
		if (isset($inserts)) {
			self::insertTags($inserts, $prefix, $id);
		}
	}
	
	public static function deleteTags($prefix, $id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$cols = array(
				'tag_title',
				'tag_id',
				'tag_count'
			);
		$db->where($prefix . 'tag_rel_' . $prefix . 'id', $id);
		$db->join('tags', $prefix . 'tag_rel_id = tag_id', 'LEFT');
		$old_tags = $db->get($prefix . 'tags_rel');
		if ($old_tags) { 
			foreach ($old_tags as $old_tag) { 
				$db->where('tag_id', $old_tag['tag_id']); 
				$tags = $db->getOne('tags');
				if ($tags['tag_count'] <= 1) { 
					$db->where('tag_id', $tags['tag_id']); 
					$db->delete('tags');
				}
				else { 
					$update = array('tag_count' => $old_tag['tag_count']-1); 
					$db->where('tag_id', $tags['tag_id']);
					if (!$db->update('tags', $update)) {
						die('Error subtrackting tag count');
					}
				}
			}
		}
		echo $prefix . 'tag_rel_' . $prefix . 'id = ' . $id . '<br>';
		$db->where($prefix . 'tag_rel_' . $prefix . 'id', $id); 
		$db->delete($prefix . 'tags_rel');
	}
	 
}

?>