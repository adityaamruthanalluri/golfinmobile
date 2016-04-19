<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/content_import.php');
/*Call URLs
$urls = array(
			array('http://www.svd.se', false, '<div class="newsarticle'),
			array('http://www.aftonbladet.se', false, '<div class="abRow'),
			array('http://www.golfbladet.se', true, '<div id="article_summaries')
);
foreach ($urls as $url) {
	$data = contentImport::getContentFromURL($url[0], $url[1], $url[2]); 
	$img = contentImport::saveImageToDisc($data['url']);
	$data['img'] = $img;
	echo '<div style="width:400px"><img src="/_app/_images/' . $data['img'] . '" /><br>';
	echo utf8_decode($data['content']) . '<br>-----<br></div>';
}*/
//Call DBs 
//Golfbladet
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
			article_created, 
			article_changed 
		FROM 
			articles
		WHERE article_created > "2015-03-01"
		AND article_id = 70
	';
$cols = array(
		'id' => 'article_id',
		'title' => 'article_title',
		'summary' => 'article_summary',
		'body' => 'article_body',
		'created' => 'article_created',
		'changed' => 'article_changed'
	);
$data = contentImport::getContentFromDB($source, $db_server, $db_user, $db_pass, $db_name, $sql, $cols);
echo '<img src="/_app/_images/' . $data['image'] . '" /><br><br>';
print_r($data);


?>