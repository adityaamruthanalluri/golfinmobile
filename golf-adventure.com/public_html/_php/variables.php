<?php
//Logged in?
if (isset($_SESSION['userid'])) {
	$logged_in = true;
}
//Device?
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$scriptVersion = $detect->getScriptVersion();

?>