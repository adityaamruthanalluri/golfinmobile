<?php 
//getTitle()
//getMeta()
//getURI()
//cleanUrl($str, $replace=array(), $delimiter='-')
//rss($rss_url)
//hiddenForms()
/*** Registration and password ***/
//displayPasswordRecoveryInfo($status = NULL)
//displayRegistrationInfo($action)
//verifyPasswordRecovery($vercode)
/*** Locations ***/
//getCity($id)
//getRegion($id)
//getCountry($id)
//getDistrict($id)
/*** Image check ***/
//getImage($path, $id)
/*** Strings ***/
//strReplaceLast($search, $replace, $subject)
/*** Display errors ***/
//display401()
//display404()
setlocale(LC_ALL, 'en_US.UTF8'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/forms.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/mail.management.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
$db = new MysqliDb($server, $user, $password, $database);

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'toggle_lang':
			session_start();
			$lang_change = array(
					array(
						'EN' => '/offers/',
						'SV' => '/erbjudanden/'
					),
					array(
						'EN' => '/shop/',
						'SV' => '/shop/'
					),
					array(
						'EN' => '/feedback/',
						'SV' => '/feedback/'
					)
				);
			$defined = false;
			if ($_POST['url'] != '/' && strpos($_POST['url'], 'admin') === false) {
				foreach ($lang_change as $item) {
					if (in_array($_POST['url'], $item)) {
						$location = $item[$_POST['lang']];
						$defined = true;
					}
				}
				if (!$defined) {
					$cols = array('article_id');
					//Hämta nuvarande artikels id
					$db->where('article_url', $_POST['url']);
					$old = $db->getOne('articles', $cols);
					$id = $old['article_id'];
					//Kolla i article_lang_rel om gamla id:t finns
					$db->where($_SESSION['site_language'], $id);
					$db->where($_SESSION['site_language'], $id);
					$new = $db->getOne('article_lang_rel');
					if (count($new) > 0) {
						//Om det gör det, hämta url till motsvarande artikel
						$new_id = $new[$_POST['lang']];
						$cols = array('article_url');
						$db->where('article_id', $new_id);
						$url = $db->getOne('articles'); 
						$location = $url['article_url'];
					}
					else {
						$location = '00000';
					}
				}
			}
			else {
				$location = $_POST['url'];
				if (strpos($location, 'update')) {
					$location = substr($location, 0, strpos($location, '/update'));
				}
			}			
			$_SESSION['site_language'] = $_POST['lang'];
			echo $location;
			die();
			break;
		case 'get_lang':
			session_start();
			echo $_SESSION['site_language'];
			die();
			break;
		case 'conn_rel_lang':
			session_start();
			$_SESSION['conn_rel_lang'] = $_POST['lang'];
			break;
		case 'toggle_status':
			switch ($_POST['table']) {
				case 'articles':
					$a = 'article_id';
					$b ='article_published';
					break;
				case 'golfcourses':
					$a = 'course_id';
					$b = 'course_status';
					break;
				case 'deals_confirmed':
					$a = 'dc_id';
					$b = 'dc_status';
					break;
			}
			$db->where($a, $_POST['id']);
			$result = $db->getOne($_POST['table']);
			$status = $result[$b];
			if ($status) {
				$status = 0;
			}
			else {
				$status = 1;
			}
			$db->where($a, $_POST['id']);
			$data = array($b => $status);
			$result = $db->update($_POST['table'], $data);
			echo ($status);
			break;
	}
}

Class Common {

/*** IP Location **/
	public static function getLocation($ip) {
		ini_set("allow_url_fopen", "On");
		//$ip = $_SERVER['REMOTE_ADDR'];
		$details = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip={$ip}"));
		//$details = explode(':', $details);
		return $details;
	}

/*** Head ***/
	
	public static function getTitle() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$cols = array(
			'website_title'
		);
		$settings = Settings::getSettings($cols);
		$title = $settings['website_title'];
		$URI = self::getURI(); 
		$uri = explode('/', substr($URI, 1));
		if ($uri[0] == 'admin') { 
			for ($i=0;$i<count($uri);$i++) {
				if ($uri[$i] != '') {
					$title .= ' &raquo; ' . constant(mb_strtoupper($uri[$i], 'UTF-8'));
				}
			}
			return $title;
		}
		if ($URI != '/' ) {
			for ($i=0;$i<count($uri);$i++) {
				$cols = array('article_title');
				$db->where('article_id > 0');
				$db->where('article_title <> "TOP_LEVEL"');
				$db->where('article_url LIKE "%' . $uri[$i] . '"');
				$result = $db->getOne('articles');
				if ($uri[$i] == mb_strtolower(constant('ARCHIVE'))) {
					$url = constant('ARCHIVE');
				}
				else {
					if ($result) {
						$url = $result['article_title'];
					}
					else {
						$url = constant(mb_strtoupper($uri[$i], 'UTF-8'));
					}
				}
				if ($uri!='/') {
					$title .= ' &raquo; ' . $url;
				}
			}
			return $title;
		}
		return $title;
	}
	
	public static function getMeta() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$uri = self::getURI(); 
		$thisuri = $uri;
		$uri = explode('/', substr($uri, 1));
		$uri = $uri[count($uri)-1];
		if ($uri == 'golfcourses') {
			$cols = array('course_no_of_holes', 'course_phone_information', 'course_description');
			$db->where('course_id', $_GET['gcid']);
			$result = $db->getOne('golfcourses', $cols);
			$meta[0] = $result['course_no_of_holes'] . ' ' . constant('HOLES') . '. Phone: ' . $result['course_phone_information'] . '. ' . $result['course_description'];
		}
		elseif ($uri == 'offers') {
			$meta[0] = 'Offers from golf clubs and courses.';
			$meta[1] = 'offers, golf courses, golf clubs';
		}
		elseif ($uri == 'erbjudanden') {
			$meta[0] = 'Erbjudanden från golfbanor och golfklubbar.';
			$meta[1] = 'erbjudanden, golfbanor, golfklubbar';
		}
		elseif ($uri == 'shop') {
			if ($_SESSION['site_language'] == 'EN') {
				$meta[0] = 'Buy and sell with GreenBay Shop. Avertise your goods. Buy new golf equiptment.';
				$meta[1] = 'buy and sell, buy, sell, golf equiptment, golf clubs, greenbay shop';
			}
			if ($_SESSION['site_language'] == 'SV') {
				$meta[0] = 'Köp och sälj. Annonsera ut dina golfprylar. Köp ny golfutrustning.';
				$meta[1] = 'köp och sälj, köp, sälj, golfutrustning, golfklubbor, greenbay shop';
			}
		}
		elseif ($uri == 'feedback') {
			if ($_SESSION['site_language'] == 'EN') {
				$meta[0] = 'Give us feedback. We appreciate your input.';
				$meta[1] = 'feedback, golfinmobile, opinion';
			}
			if ($_SESSION['site_language'] == 'SV') {
				$meta[0] = 'Ge oss feedback på golfinmobile.com. Vi uppskattar din åsikt.';
				$meta[1] = 'feedback, golfinmobile, åsikt';
			}
		}
		
		
		
		else {
			$cols = array('article_id', 'article_summary');
			$db->where('article_url', '/' . $uri);
			$db->where('article_lang', $_SESSION['site_language']);
			$result = $db->getOne('articles');
			if (strlen($result['article_summary']) > 0) {
				$meta[0] = strip_tags($result['article_summary']);
			}
			elseif (strlen($result['article_body']) > 0) {
				$meta[0] = strip_tags($result['article_body']);
			}
			if ($thisuri != '/') {
				$cols = array('tag_title');
				$db->join('tags', 'tag_id=article_tag_rel_id', 'LEFT');
				$db->where('article_tag_rel_article_id', $result['article_id']);
				$tags = $db->get('article_tags_rel', null, $cols); 
				$tags = array_unique($tags);
				if ($tags) {
					foreach ($tags as $tag) {
						$keywords .= $tag['tag_title'] . ', ';
					}
					$meta[1] = substr($keywords, 0, -2);
				}
			}
		}
		if (!isset($meta[0])) {
			$cols = array(
				'default_meta_desc'
			);
			$settings = Settings::getSettings($cols);
			$meta[0] = $settings['default_meta_desc'];
		}
		if (!isset($meta[1])) {
			$cols = array(
				'default_meta_keywords'
			);
			$settings = Settings::getSettings($cols);
			$meta[1] = $settings['default_meta_keywords'];
		}
		return $meta;
	}
	
/*** Navigation ***/
	public static function getURI() {
		$URI = $_SERVER['REQUEST_URI'];
		if (strlen($URI)>2 && strpos($URI, '?')) {
				$URI = substr($URI, 0, strpos($URI, '?'));
		}
		if (strrpos($URI, '/')+1 == strlen($URI) && strlen($URI) > 1 ) {
			$URI = substr($URI, 0, strlen($URI)-1);
		}
		return $URI;
	}
	
/*** Clean URL ***/	
	public static function cleanUrl($str, $replace=array(), $delimiter='-') {
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		return $clean;
	}

/*** RSS ***/
	public static function rss($rss_url) {
		ini_set("allow_url_fopen", TRUE);
		$feed = implode(file($rss_url));
		$x = new SimpleXmlElement($feed);
		$html = '<ul>';
		foreach($x->channel->item as $entry) {
			$html.= '
					<li>
						<span class="ab-banner">Senaste nytt från</span> <img src="/_icons/aftonbladet.png" /> <a href="' . $entry->link . '" title="' . $entry->title . '" target="_blank">' . $entry->title . '</a>
					</li>
				';
		}
		$html .= '</ul>';
		return $html;
	}

/*** Forms ***/
	public static function hiddenForms() {
		session_start();
		if (!isset($_SESSION['userid'])) {
			$html .= Users::displayRegisterForm();
			$html .= Forms::displayLoginForm() . Forms::displayPasswordForm();
		}
		else {
			$html .= '
					<div class="popup logout_wrapper">
						<div id="logout_message">
							' . constant('LOGOUT_MESSAGE') . '
						</div>
					</div>
				';
		}
		if ($use_contact_form) {
			$html .= Forms::contactForm();
		}
		$html .= '
					<div id="newsletter_form_wrapper" class="popup newsletter_subscribe_wrapper">
						<div id="newsletter_form" class="form_wrapper">
							<div class="popup_close">x</div>
							<div class="form_label">' . constant('SUBSCRIBE_TO_NEWSLETTER') . '</div>
							<form name="newsletter" id="newsletter" class="form_input" action="/_php/newsletter.php" method="post">
								<input type="text" name="subscriber_email" id="subscriber_email" class="required email" data-errormsg="' . constant('NEWSLETTER_FIELD_EMPTY') . '" />
								<input type="hidden" name="action" value="newsletter" />
								<input type="submit" class="submit_button" value="' . constant('SEND') . '" />
							</form>
						</div>
					</div>
					<div id="share_form_wrapper" class="popup share_wrapper">
					<div id="share_form" class="form_wrapper">
						<div class="popup_close">x</div>
						<div class="form_label">' . constant('SHARE') . '</div>
						<div id="share_friend">
							<form name="share" id="share">
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('YOUR_NAME') . '<span class="req_icon">*</span>
									</div>
									<div class="form_input">
										<input type="text" name="share_your_name" id="share_your_name" class="required" data-errormsg="' . constant('YOUR_NAME') . '" />
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('YOUR_EMAIL') . '<span class="req_icon">*</span>
									</div>
									<div class="form_input">
										<input type="email" name="share_your_email" id="share_your_email" class="required email" data-errormsg="' . constant('YOUR_EMAIL') . '" />
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('FRIENDS_NAME') . '<span class="req_icon">*</span>
									</div>
									<div class="form_input">
										<input type="text" name="share_friends_name" id="share_friends_name" class="required" data-errormsg="' . constant('FRIENDS_NAME') . '" />
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('FRIENDS_EMAIL') . '<span class="req_icon">*</span>
									</div>
									<div class="form_input">
										<input type="email" name="share_friends_email" id="share_friends_email" class="required email" data-errormsg="' . constant('FRIENDS_EMAIL') . '" />
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('MESSAGE') . '
									</div>
									<div class="form_input">
										<input type="text" name="share_message" id="share_message" />
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_input">
										<input type="submit" class="required submit_button" value="' . constant('SEND') . '" />
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
		';
		return $html;
	}
		
    public static function displayPasswordRecoveryInfo($result = NULL) { 
		$html = '
				<div id="page_wrapper" class="pagecontent_wrapper">
                    <div id="content">
                    	<div id="article">
        	';
        $status = $result['id'];
        if (is_integer($status)) { 
        	$html .= '
        				<h2>' . constant('PASSWORD_RECOVERY_TITLE') . '</h2>
        	    ';
			$html .= Forms::displayPasswordRecoveryForm($result);
        }
        else {
            switch ($result) {
                case 'Registrant error':
                    $html .= '
                         <h1>' . constant('REGISTRATION_ERROR') . '</h1>
                         <p>' . constant('REGISTER_ACCOUNT_ACTIVE') . '</p>
                    	';
                    break;
                case 'Date error':
                    $html .= '
                          <h1>' . constant('PASSWORD_RECOVERY_ERROR') . '</h1>
                          ' . constant('PASSWORD_RECOVERY_CODE_INACTIVE')
                	     ;
                    break;
                case 'Code error':
                    $html .= '
                         <h1>' . constant('PASSWORD_RECOVERY_ERROR') . '</h1>
                         ' . constant('PASSWORD_RECOVERY_CODE_ERROR')
                         ;
                    break;
                case 'success':
                    $html .= '
                          <h1>' . constant('PW_CHANGE_SUCCESS') . '</h1>
                          ' . constant('PW_CHANGE_SUCCESS_TEXT')
                       ;
            	    break;
                case 'form':
                    $html .= '
                         <h1>' . constant('PASSWORD_RECOVERY_TITLE') . '</h1>
                         ' . constant('PASSWORD_MAIL_INFO')
                       ;
                    break;
				case 'nomail':
					$html .= '
						<h1>' . constant('PASSWORD_RECOVERY_NOMAIL_TITLE') . '</h1>
						' . constant('PASSWORD_RECOVERY_NOMAIL_INFO')
					;
					break;
            }
        }
        $html .= '
        					</div>
                        </div>
                    </div>
            ';
        return $html;
    }
        
    public static function displayRegistrationInfo($action) {
		if ($action == 1) {
			$text = constant('REGISTRATION_SUCCEDED');
		}
		elseif ($action == 2) {
			$text = constant('REGISTRATION_ADMIN_SUCCEDED');
		}
		else {
			$text = constant('REGISTRATION_NOT_SUCCESSFUL');
		}
		$html = '
				<div id="page_wrapper" class="pagecontent_wrapper">
					<div id="content">
						<div id="article">
						' . $text . '
						</div>
					</div>
				</div>
			';
		return $html;
	}
        
	public static function verifyPasswordRecovery($vercode) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$vercode = explode('_', $vercode); 
		$reg_id = substr($vercode[0],2);
		$date = $vercode[1];
		$date = substr($date, 0, 2) . '-' . substr($date, 2, 2) . '-' .substr($date, 4, 2); 
		$code = substr($vercode[2], 0);
		
		$date1 = new DateTime(date($date)); 
		$date2 = new DateTime(date('Y-m-d h:i'));
		$interval = date_diff($date1, $date2);
		$date_test = (int)$interval->format('%R%a'); 
		if ($date_test < 1 && $date_test > -1) {
			
					$table = 'users';
					$id = 'user_id';
					$ver = 'user_verified';
					$email = 'user_email';
					
			$db->where($id, $reg_id);
			$registrant = $db->getOne($table);
			if ($registrant) {
				$email_chk = Accounts::encryptPassword($registrant[$email]);
				if ($code == $email_chk) {
					$data = array($ver => 1);
					$db->where($id, $reg_id);
					$db->update($table, $data);
				}
				else {
					return 'Code error';
				}
			}
			else {
				return 'Registrant error';
			}
		}
		else {
			return 'Date error';
		}
		$result = array (
					'id' => (int)$reg_id,
					'table' => $table,
					'identifier' => $id
			);
		return $result;
	}

/*** Locations ***/
	public static function getCity($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('CityId', $id);
		$city = $db->getOne('app_cities');
		if ($city['City'] != '') {
			return $city['City'];	
		}
		else {
			return '-';
		}
	}
	public static function getRegion($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('RegionId', $id);
		$region = $db->getOne('app_regions');
		if ($region['Region'] != '') {
			return $region['Region'];	
		}
		else {
			return '-';
		}
	}
	public static function getCountry($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('CountryId', $id);
		$country = $db->getOne('app_countries');
		if ($country['Country'] != '') {
			return $country['Country'];	
		}
		else {
			return '-';
		}
	}
	public static function getDistrict($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('district_id', $id);
		$district = $db->getOne('districts');
		if ($district['district_name'] != '') {
			return $district['district_name'];	
		}
		else {
			return '-';
		}
	}

/*** Image check ***/

	public static function getImage($path, $id)	{ 
		$exists = false;
		$ext = array('png', 'jpg', 'jpeg', 'bmp', 'gif'); 
		foreach ($ext as $imgext) {
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . $id . '.' . $imgext)) {
				$exists = true;
				$image = $path . $id . '.' . $imgext;
				break;
			}
		}
		if ($exists) {
			return $image;
		}
		else {
			return 0;
		}
	}
	
/*** Ping Azure ***/
	public static function pingAzure() { 
		$settings = Settings::getSettings();
	
		$url = 'https://golfapp.azurewebsites.net/golfservice.svc/syncwithcitynetwork/';
		
		//$url = 'https://golfapp.azurewebsites.net/GolfService.svc';
		
		$ch = curl_init($url);

		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch); 
		if ($response['syncWithCityNetworkResult']) { 
			$log = date('Y-m-d H:i:s') . "\t" . 'AzurePing' . "\t" . $response;
			file_put_contents ( 'logfiles/azureping.log' , $log , FILE_APPEND ) . "\n";	
			sleep(5);
		}
		else { 
			$log = date('Y-m-d H:i:s') . "\t" . 'AzurePing failed'   . "\n";
			file_put_contents ( 'logfiles/azureping.log' , $log, FILE_APPEND );
			$maildata = array(
					'subject' => 'Error!!! - AzurePing',
					'from' => $settings['setting_default_email'],
					'to' => $settings['setting_default_email'],
					'template' => 'message',
					'message' =>  'AzurePing failed:<br><br>' . $response
				);
			$mailsent = MailManagement::sendMail($maildata); 
			if (!$mailsent) {
				die('Mail error');
			}
		}
		return true;
	}

/*** Quiz ***/
	public static function startpageQuiz() {
		for ($i=0;$i<5;$i++) {
			$html .= '
					<div class="sp_quiz_wrapper">
						<div class="quiz_question">
							QUIZ QUESTION?
						</div>
					</div>
				';
		}
		return $html;
	}

/*** Replace last occurance of string in string ***/
	public static function strReplaceLast($search, $replace, $subject) {
		$lenOfSearch = strlen( $search );
		$posOfSearch = strrpos( $subject, $search );
		return substr_replace( $subject, $replace, $posOfSearch, $lenOfSearch );           
	}
		
/*** Errors ***/
	public static function display401() {
		$html = '
				<div id="page_wrapper" class="pagecontent_wrapper">
					<div id="content">
						<h1>' . constant('NOT_AUTHORIZED_TITLE') . '</h1>
						<p><div class="lightbox" id="login">' . constant('PAGE_NOT_AUTHORIZED_TEXT') . '</div></p>
					</div><!--#content-->
				</div><!--#startpage_wrapper-->	
			';
		return $html;
	}

	public static function display404() {
		$html = '
				<div id="page_wrapper" class="pagecontent_wrapper">
					<div id="content">
						<h1>' . constant('PAGE_NOT_FOUND_TITLE') . '</h1>
						<p>' . constant('PAGE_NOT_FOUND_TEXT') . '</p>
					</div><!--#content-->
				</div><!--#startpage_wrapper-->	
			';
		return $html;
	}
	
	public static function displayUnauthorized() {
		$html = '
				<div id="page_wrapper" class="pagecontent_wrapper">
					<div id="content">
						<h1>' . constant('NOT_ALLOWED_TITLE') . '</h1>
						<p>' . constant('NOT_ALLOWED_TEXT') . '</p>
					</div><!--#content-->
				</div><!--#startpage_wrapper-->	
			';
		return $html;
	}

}
?>