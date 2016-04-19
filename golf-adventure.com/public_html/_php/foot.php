<?php
$settings = Settings::getSettings();

$footer = '
		<div id="footer_content">
			<div id="adventure">
				<a href="http://test.skiinginmobile.com" title="Skiing adventures" target="_blank"><img src="/_icons/skiing_Adventure_transprancy.png" /></a>
			</div>
			<div id="footer_top">
				<div id="footer_menu">
					<a href="/about-golfinmobile" title="' . constant('ADVERTISE_FORM') . '">' . constant('ADVERTISE_FORM') . '</a>
				</div>
				<div id="footer_logo">
					<a href="http://www.westindiatech.com" target="_blank" />
						<img src="/_icons/westindia_technology.png" />
					</a>
				</div>
			</div>
			<div class="clear_both"></div>
			<div id="footer_bottom">
				<div id="footer_copyright">
					' . str_replace('[YEAR]', date('Y'), $settings['setting_footer_copyright']) . '
				</div>
				<div id="footer_credits">
					' . $settings['setting_footer_credits'] . '
				</div>
			<div class="clear_both"></div>
		</div>
	';
?>