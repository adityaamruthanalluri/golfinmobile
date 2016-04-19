<?php
$title = Common::getTitle();
$meta = Common::getMeta();
$head = '
<head>
	<title>
		' . $title . '
	</title>
	<!--Metadata-->
	<meta charset="utf-8">
	<meta name="description" content="' . $meta[0] . '" />
	<meta name="keywords" content="' . $meta[1] . '" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<!--CSS-->
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
	<link rel="stylesheet" type="text/css" href="/_js/fancyBox/source/jquery.fancybox.css" media="screen" />
	<link href="/_css/slideshow.css" rel="stylesheet" type="text/css" />
	<link href="/_css/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link href="/_css/forms.css" rel="stylesheet" type="text/css" />
	<link href="/_css/navigation.css" rel="stylesheet" type="text/css" />
';
if ($deviceType == 'tablet') {
	$head .= '<link href="/_css/tablet.css" rel="stylesheet" type="text/css" />';
}
if ($deviceType == 'phone') {
	$head .= '<link href="/_css/phone.css" rel="stylesheet" type="text/css" />';
}
if (array_key_exists('newsletters', $admin_head_categories)) {
	$head .= '<link href="/_css/newsletter.css" rel="stylesheet" type="text/css" />';
}
include($_SERVER['DOCUMENT_ROOT'].'/_config/dynstyle.php');
$head .= '				
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/_js/fancyBox/source/jquery.fancybox.pack.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js" type="text/javascript"></script>
	<script type="text/javascript" src="//cdn.jsdelivr.net/jquery.marquee/1.3.1/jquery.marquee.min.js"></script>
	<!--script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.js"></script-->
	<!--Internal javascripts-->
	<script type="text/javascript" src="/_js/jquery.common.js"></script>
	<script type="text/javascript" src="/_js/jquery.forms.js"></script>
	<script type="text/javascript" src="/_js/jquery.lang.js"></script>
	<script type="text/javascript" src="/_js/jquery.login.js"></script>
	<script type="text/javascript" src="/_js/google_maps.js"></script>
	<script type="text/javascript" src="/_js/jquery.menus.js"></script>
	<script type="text/javascript" src="/_js/jquery.newsletter.js"></script>
	<script type="text/javascript" src="/_js/jquery.popups.js"></script>
	<script type="text/javascript" src="/_js/jquery.registration.js"></script>
	<script type="text/javascript" src="/_js/jquery.settings.js"></script>
	<script type="text/javascript" src="/_js/slideshow.js"></script>
    <script type="text/javascript" src="/_js/tinymce/js/tinymce/tinymce.min.js"></script>
	<script type="text/javascript" src="/_js/tiny-mce-init.js"></script>
	<script type="text/javascript" src="/_js/google_analytics.js"></script>
	
	<script type="text/javascript" src="/_js/markerclusterer.js"></script>
	
</head>
';
?>

