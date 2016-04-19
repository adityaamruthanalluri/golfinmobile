<?php
//Settings
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
$settings = Settings::getSettings();
$css = '';
//Login menu
if ($use_login_menu) {
	$css .= '
			#logo {
				float: left;
				width: 40%;
			}
			#main_menu_wrap {
				width: 25%;
			}
		';	
}
else {
	$css .= '
			#logo {
				width: 100%;
			}
		';
}
//Startpage sidebar 
if ($use_startpage_sidebar) {
	$css .= '
			#article_summaries {
				border-right: 1px solid #000;
				padding: 0 10px 0 0;
				width: 630px;
			}
		';
}
else {
	$css .= '
			#article_summaries {
				width: 100%;
			}
		';
}
//Startpage
switch ($startpage_summaries_cols) {
	case 1:
		
		break;
	case 2:
		
		break;
	case 3:
		$css .= '
				#article_summaries {
					overflow: hidden;
				}
				.article_summary {
					float: left;
					margin: 1% 1% 0 0;
					width: 32.6%;
				}
				.boxright {
					margin: 1% 0 0 0;
				}
			';
		break;
}
//Sidebar 
if ($use_sidebar) {
	$css .= '
			#wrapper {
				width: 1270px;
			}
			#page_wrapper {
				border-right: 1px solid #000;
			}
			#page {
				border-right: 1px solid #000;
			}
			/*** Sidebar ***/
			#sidebar {
				margin: 0 10px;
				padding: 15px 0 0 0;
			}
			#sidebar img {
				max-width: 100%;
			}
			.sidebar_header {
				color: #666666;
				font-size: 10px;
			}
		';
}
else {
	$css .= '
			#wrapper {
				width: 940px;
			}
			#page_wrapper {
				border-right: none;
			}
		';
}
//Search
if ($settings['setting_article_archive']) {
	$css .= '
			#header {
				height: 120px;
			}
			#logo {
				margin: 20px 0 0 0;
			}
			#logo,
			#payoff {
				float: left;
				width: 70%;
			}
			#login_menu {
				right: 255px !important;
			}
			#searchform {
				position: absolute;
				right: 0;
				top: 0;
			}
		';
}


echo '<style>' . $css . '</style>';

?>