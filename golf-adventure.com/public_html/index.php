<?php 
session_start();
if (!isset($_SESSION['userid'])) {
	//$_SESSION['userid'] = 99999;
	//$_SESSION['admin_level'] = 100;
}
include($_SERVER['DOCUMENT_ROOT'].'/_php/includes.php');
Navigation::addLog(); 
$content = Navigation::pageNavigation($logged_in); 
$sidebar = ''; //Sidebar::getContent($_SERVER['REQUEST_URI']);
if ($_SERVER['REQUEST_URI'] == '/') {
	$slideshow = Slideshow::displaySlideshow();
	$start_banner = Startpage::startBanner($deviceType);
	//$start_offers = Startpage::startOffers();
	//$start_partners = Startpage::startPartner();
	//$start_welcome = Startpage::startWelcome();
	$class = 'class="startpage"';
}
elseif (substr($_SERVER['REQUEST_URI'], 0, 6) == '/admin') {
	$class = 'class="administration"';
}
elseif (substr($_SERVER['REQUEST_URI'], 0, 10) == '/mobilemap') {
	$class = 'class="mobilemap"';
}
else {
	$class = '';
} 
?>
<!doctype html> 
<?php
echo $head;
?>
<html>
	<body onload="load()" <?php echo $class ?>>
		<div class="popup error_message_wrapper">
			<div id="error_message_content" class="form_wrapper">
				<div class="popup_close">x</div>
				<div class="error_message_title"><?php echo constant('ERROR') ?></div>
				<div id="error_message"></div>
			</div>
		</div>
		<div id="forms_bkg"></div>
		<?php 
						/*if ($deviceType=='phone') {
							$app_dl .= '
									<div id="app_dl_wrapper">
										<div id="app_dl">
											<div class="popup_close">x</div>
								';
							if( $detect->isiOS() ){
 								$app_dl .= '<img src="/_files/Apps/iPhone_app_p-_AppStore.png" alt"Go to Applestore" width="145" height="40" />';
							}
							if( $detect->isAndroidOS() ){
 								$app_dl .= '<img src="/_files/Apps/Android_App_p-_Google_Play.png" width="145" height="40" alt"Go to Google Play" />';
							}
							$app_dl .= '
											</div>
										</div>
								';
						}
						echo $app_dl;*/
					?>
			
		<?php echo $header; 
			/**if (!isset($_SESSION['userid']) && $class != 'class="administration"' && substr($_SERVER['REQUEST_URI'], 0, 17) != '/passwordrecovery') {
				echo '<div id="register_bkg">';
				echo Users::displayRegisterForm();
				echo '</div>';
			}**/
		
			if ($_SERVER['REQUEST_URI'] == '/') { //print_r($_SESSION);
		?>
				<div id="start_banner">
				<?php echo $start_banner ?>
				</div>
		<?php

			}
		?>
		<div class="clear_both"></div>
		<div id="wrapper">
			<div id="page_wrapper">
				<div id="page">
					<div id="content">
						<?php
							
							echo $content;
						?>
					</div>
					<div class="clear_both"></div>
					<div id="footer">
						<?php echo $footer ?>
					</div>
				</div>
				<?php
					if ($use_sidebar) {
						echo '<div id="sidebar">' . $sidebar . '</div>';
					}
				?>
				<div class="clear_both"></div>
			</div>
			<div class="clear_both"></div>
		</div>
		<div id="phonedummy"></div>
		<div id="tabletdummy"></div>
	</body>
</html>