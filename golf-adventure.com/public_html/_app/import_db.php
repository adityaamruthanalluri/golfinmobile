<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/content_import.php');
$db = new MysqliDb($server, $user, $password, $database);
$db->where('app_settings_id', 1);
$app_settings = $db->getOne('app_settings');
//Golfbladet:
$source = 'Golfbladet';
$db_server = 'mysql15.citynetwork.se';
$db_user = '135416-qn77030';
$db_pass = 'Fh0,kN4aY0';
$db_name = '135416-golfbladet';
$sql = '
		SELECT 
			article_id, 
			article_title, 
			article_summary, 
			article_body, 
			article_url,
			article_created, 
			article_changed 
		FROM 
			articles
		WHERE article_created > "' . $app_settings['art_last_update'] . '"
	';
$cols = array(
		'id' => 'article_id',
		'title' => 'article_title',
		'summary' => 'article_summary',
		'body' => 'article_body',
		'url' => 'article_url',
		'created' => 'article_created',
		'changed' => 'article_changed'
	);
$returndata = contentImport::getContentFromDB($source, $db_server, $db_user, $db_pass, $db_name, $sql, $cols);
if (count($returndata) > 0) {
	foreach ($returndata as $data) {
		$insert = array(
				'article_id' => $data['id'],
				'article_title' => strip_tags(utf8_encode($data['title']),'<p><h1><h2><a>'),
				'article_summary' => strip_tags(utf8_encode($data['summary']),'<p><h1><h2><a>'),
				'article_body' => strip_tags(utf8_encode($data['body']),'<p><h1><h2><a>'),
				'article_created' => $data['created'],
				'article_changed' => $data['changed'],
				//'article_image' => $data['image'],
				'article_source' => $data['source']
			);
		$result = $db->insert('app_articles', $insert);
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_app_articles/images/'.$_FILES["filesToUpload"]["name"])) {
			$ext = substr($_FILES["filesToUpload"]["name"],(strrpos($_FILES["filesToUpload"]["name"], '.')+1));
			rename($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$_FILES["filesToUpload"]["name"],$_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'.$result.'.' . $ext); 
		}
		if (!$result) {
			$log = date('Y-m-d H:i:s') . "\t" . 'Didn´t save in database table app_articles (' . $data['source'] . ':' . $data['id'] . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . '.log' , $log , FILE_APPEND );
		}
		else {
			$db->update('app_settings', array('art_last_update' => date('Y-m-d H:i:s')));
			$log = date('Y-m-d H:i:s') . "\t" . 'Successfully imported from (' . $data['source'] . ':' . $data['id'] . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . '.log' , $log , FILE_APPEND );
		}
	}
}
/*else {
	$log = date('Y-m-d H:i:s') . "\t" . 'Successfully run - No new items.' . "\n";
	file_put_contents ( '_logs/' . date('Y-m-d') . '.log' , $log , FILE_APPEND );
}*/
?>