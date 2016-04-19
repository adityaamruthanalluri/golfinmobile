<?php
//displayStartpage($deviceType)
//startBanner()
//startMarbella()
//startPartner()
//startWelcome()
//startImage()
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');

class Startpage {
	
	/*** Startpage ***/
	public static function displayStartpage($deviceType) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		
		$deal = $db->get('deal_startpage');
		$i = 0;
		foreach ($deal as $item) {
			$i++;
			$deals[$i] = Deals::displayDealSummary($item['deal_id'], true);
			switch ($i) {
				case 1:
					$db->where('dealType_id', $i);
					$type = $db->getOne('deal_types');
					$deal_titles[$i] = '<h3>' . constant($type['deal_type_startpage_constant']) . '</h3>';
					break;
				case 2:
					$db->where('dealType_id', $i);
					$type = $db->getOne('deal_types');
					$deal_titles[$i] = '<h3>' . constant($type['deal_type_startpage_constant']) . '</h3>';
					break;
				case 3:
					$db->where('dealType_id', $i);
					$type = $db->getOne('deal_types');
					$deal_titles[$i] = '<h3>' . constant($type['deal_type_startpage_constant']) . '</h3>';
					break;
			}
		}
		$html = '
				<div id="startpage_deal_wrapper">		
					<div id="deal_of_the_week" class="deal_summary_wrapper">			
						' . $deal_titles[1] . '
						' . $deals[1] . '
					</div>
					<div id="shop_of_the_week" class="deal_summary_wrapper">
						' . $deal_titles[2] . '
						' . $deals[2] . '
					</div>
					<div id="destination_of_the_week" class="deal_summary_wrapper">
						' . $deal_titles[3] . '
						' . $deals[3] . '
					</div>
				</div>
			';
		/*QUIZ
		$html .= '
				<div clas="clear_both"></div>
				<div id="quiz_wrapper">
					' . Common::startpageQuiz() . '
				</div>
			';*/
		$html .= '
				<div id="article_summaries">
					' . Articles::displayArticleSummaries() . '
					<div class="clear_both"></div>
				</div>
			';
		if ($use_startpage_sidebar) {
			$html .= '
				<div id="startpage_extra">
					
					<div class="clear_both"></div>
				</div>
			';
		}
		return $html;
	}
	
	public static function startBanner($deviceType) {
		$settings = Settings::getSettings();
		$slideshow = Slideshow::displaySlideshow();
		$html = '
				<div class="start_banner_content">
			';
		//$html .= $slideshow;
		$html .= '
					<!--img src="/_icons/GOOGLEMAP.png" title="View destinations and deals" class="app_phone" id="app-map" />
					<img src="/_icons/IPHONE.png" title="View destinations and deals" class="app_phone" id="app-deals" />
					<div class="store_wrap">
						<a href=""><img src="/_files/Apps/iPhone_app_p-_AppStore.png" alt"Go to Applestore" width="145" height="40" class="left" /></a>
						<a href="https://play.google.com/store/apps/details?id=com.Sweden.Carribean.golfinmobile&hl=en" target="_blank"><img src="/_files/Apps/Android_App_p-_Google_Play.png" width="145" height="40" alt"Go to Google Play" class="right" /></a>
					</div-->
					<div class="banner_payoff">
						
					</div>
			';
		
		if ($deviceType != 'phone') {
			$html .= '
					<div class="richter">
						<span class="richter_content">
							<a href="' . $settings['setting_startpage_payoff_link'] . '">' .  $settings['setting_startpage_payoff_text'] . '</a>
						</span>
					</div>
			';
		}
		else {
			/*$html .= '
					<div class="app_payoff">
						<span class="app_payoff_content">
							<a href="' . $settings['setting_startpage_payoff_link'] . '" target="_blank">' . constant('START_BANNER_PAYOFF') . '</a>
						</span>
						<div class="clear_both"></div>
					</div>
			';*/
		}
		$html .= '
				</div>
			';
		return $html;
	}
	
	/*public static function startPartner() {
		$i = 0;
		if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/_partners')) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$logos[] = $entry;
					$i++;
				 }
    		}
			closedir($handle);
		}
		$width = 100/$i;
		$html = '
				<div class="start_partners">
					<div class="start_partners_wrapper">
			';
		foreach ($logos as $logo) {
			$html .= '
						<div class="start_partner" style="width: ' . $width . '%;">
							<img src="/_partners/' . $logo . '" />
						</div>
				';
		}
		$html .= '
					</div>
					<div class="clear_both"></div>
				</div>
			';
		return $html;
	}*/
	
	public static function startWelcome() {
		$html = '
				<div class="start_welcome_content">
					' . constant('START_WELCOME_PAYOFF') . '
				</div>
			';
		return $html;
	}
	
	public static function startImage() {
		$html = '<center><img src="/_files/Start_Banner/start_map.jpg" /></center>';
		return $html;
	}
	
}

?>