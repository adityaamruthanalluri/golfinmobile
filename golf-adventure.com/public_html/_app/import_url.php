<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/content_import.php');
/*Call Externall URLs
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
?>