<?php
//getContentFromDB($source, $db_server, $db_user, $db_pass, $db_name, $sql, $data)
//getContentFromURL($key, $value)
//saveImageToDisc($img)
//resizeDLImage($file)
include($_SERVER['DOCUMENT_ROOT'].'/_php/SimpleImage.php');

class contentImport {
	
	public static function getContentFromDB($source, $db_server, $db_user, $db_pass, $db_name, $sql, $data) { 
		$link = mysql_connect($db_server, $db_user, $db_pass);
		if (!$link) {
			$log = date('Y-m-d H:i:s') . "\t" . 'No database connection established (' . $source . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . 'log' , $log , FILE_APPEND );
	    	die();
		}
		$db = mysql_select_db($db_name, $link) or die ();
		$result = mysql_query($sql);
		if (!$result) {
			$log = date('Y-m-d H:i:s') . "\t" . 'No database connection established (Invalid query: ' . mysql_error() . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . '.log' , $log , FILE_APPEND );
		    die();
		}
		$i = 0;
		while ($row = mysql_fetch_assoc($result)) { 
			switch ($source) {
				case 'Golfbladet':
					$content['id'] = $row['article_id'];
					$content['title'] = $row['article_title'];
					$content['summary'] = $row['article_summary'];
					$content['body'] = $row['article_body'];
					$content['url'] = $row['article_url'];
					$content['created'] = $row['article_created'];
					$content['changed'] = $row['article_changed'];
					$summary = $content['summary'];
					if (strpos($summary, '<img')) {
						$start = strpos($summary, 'src="')+5;
						$end = strpos($summary, '" ', $start);
						$img = substr($summary, $start, ($end - $start)); 
						$img = htmlentities($img, ENT_QUOTES, "UTF-8");
						$url = 'http://golfbladet.se' . $img;
					}
					break;
			}
			$image = self::saveImageToDisc($url);
			$data = $content;
			unset($content);
			$data['image'] = $image;
			$data['source'] = $source;
			$returndata[] = $data;
		}
		return $returndata;
	}
	
	public static function getContentFromURL($url, $relative_links, $handler) {	
		$text = file_get_contents($url);
		if (strpos($text, '<img')) {
			$start = strpos($text, '<img'); 
			$end = strpos($text, '>', $start);
			$tag = substr($text, $start, ($end - $start)+1); 
			$tag = urldecode($tag);
			if (!strpos($tag, '"')) {
				$strlimiter = "'";
			}
			else {
				$strlimiter = '"';
			}
			$tagstart = strpos($tag, 'src='.$strlimiter)+5;
			$tagend = strpos($tag, $strlimiter, $tagstart+5);
			$img = substr($tag, $tagstart, ($tagend - $tagstart)); 
			if (strpos($img, '?')) {
				$img = substr($img, 0, strpos($img, '?'));
			}
			if ($relative_links) {
				$img = $url . $img;
			}
			$img = htmlentities($img, ENT_QUOTES, "UTF-8");
		}
		$content = substr($text, strpos($text, $handler) + strlen($handler));
		$data['url'] = $img;
		$data['content'] = substr(strip_tags($content, '<h1><h2><p>'), strpos($content, '>')+1, 500) . '...';
		return $data;
	}

	public static function saveImageToDisc($url) {
		$ext = substr($url, strrpos($url, '.'));
		$filename = date('Ymdhis') . strtolower($ext);
		$file = file($url);
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/_app/_temp/' . $filename, $file);
		sleep(1);
		if (is_file($_SERVER['DOCUMENT_ROOT'].'/_app/_temp/' . $filename)) { 
			list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/_app/_temp/' . $filename);
			if ($width > 0) {
				$result = self::resizeDLImage($filename);
			}
			else {
				$log = date('Y-m-d H:i:s') . "\t" . 'Temp file empty (' . $url . ')' . "\n";
				file_put_contents ( '_logs/' . date('Y-m-d') . 'log' , $log , FILE_APPEND );
				$result = false;
			}
		}
		else {
			$log = date('Y-m-d H:i:s') . "\t" . 'No temp file in temp directory (' . $url . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . 'log' , $log , FILE_APPEND );
			$result = false;
		}
		return $result;
	}

	public static function resizeDLImage($file) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$result = true;
		$max_width = constant('APP_IMAGE_MAX_WIDTH');
		$temproot = $_SERVER['DOCUMENT_ROOT'].'/_app/_temp/';
		$temppath = $temproot.$file;
		$saveroot = $_SERVER['DOCUMENT_ROOT'].'/_app/_images/';
		$savepath = $saveroot.$file;
		if (is_file($temppath)) { 
			list($width, $height) = getimagesize($temppath);
			if ($width > $max_width) { 
				$image = new SimpleImage();
				$image->load($temppath);
				$image->resizeToWidth($max_width);
				$image->save($savepath);
			}
			else {
				$image = new SimpleImage();
				$image->load($temppath);
				$image->save($savepath);
			}
		}
		if (is_file($savepath)) {
			unlink($temppath);
			$result = $file;
		}
		else {
			$log = date('Y-m-d H:i:s') . "\t" . 'No temp file to resize (' . $file . ')' . "\n";
			file_put_contents ( '_logs/' . date('Y-m-d') . '.log' , $log , FILE_APPEND );
			$result = false;
		}
		return $result;
	}

}





























?>