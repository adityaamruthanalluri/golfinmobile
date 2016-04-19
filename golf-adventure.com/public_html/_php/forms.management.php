<?php 
/*
Form($data, $fields, $values = null)
getDropdown($data, $values, $id = null)

appRegistrationForm()
contactForm()
displayLoginForm()
displayPasswordForm()
displayPasswordRecoveryForm($user_id)
languageForm()
shareForm()

connectionForm($data)
adminLanguageForm($type)
*/
session_start(); 
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/courses.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/users.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/accounts.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/mail.management.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);

//Ajax calls
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'populateRegions': 
			$db->where('CountryId', $_POST['countryid']);
			$db->orderBy('Region', 'ASC');
			$data = $db->get('app_regions');
			$name = $_POST['prefix'].'regionid';
			$identifier = 'RegionId';
			$class = 'RegionSelect';
			$title = 'Region';
			$constant = 'CHOOSE_REGION';
			$id = null;
			$select = Forms::locationDropdown($data, $name, $identifier, $class, $title, $constant, $id);
			echo $select; die();
			break;
		case 'populateCities': 
			$db->where('RegionId', $_POST['regionid']);
			$db->orderBy('City', 'ASC');
			$data = $db->get('app_cities');
			$name = $_POST['prefix'].'cityid';
			$identifier = 'CityId';
			$class = 'CitySelect';
			$title = 'City';
			$constant = 'CHOOSE_CITY';
			$id = null;
			$select = Forms::locationDropdown($data, $name, $identifier, $class, $title, $constant, $id);
			echo $select; die();
			break;
			case 'populateDistricts': 
			$select = Courses::getDistrictDropdown(null, $_POST['countryid']);
			echo $select; die();
			break;
		case 'tell_a_friend':
			$data = array('sender' => $_POST['sender_email'], 'reciever' => $_POST['reciever_email'], 'date' => date('Y-m-d H:i'));
			$result = $db->insert('share_emails', $data);
			if (!$result) {
				echo 'Database Error';die();
			}
			$message = constant('HELLO') . ' ' . $_POST['reciever_name'] . '!';
			$message .= constant('YOUR_FRIEND') . ' ' . $_POST['sender_name'] . ' ' . constant('WANTS_YOU_TO_KNOW') . ':<p>';
			$message .= $_POST['message'] . '</p>';
			$message .= '<p>' . constant('VISIT_US_AT') . ' ' . $_SERVER['SERVER_NAME'] . '' . $_SERVER['REQUEST_URI'] . '</p>';
			$maildata = array(
					'subject' => constant('SUBJ_TELL_A_FRIEND'),
					'from' => $_POST['sender_email'],
					'to' => $_POST['reciever_email'],
					'template' => 'message',
					'message' =>  $message,
				);
			$mailsent = MailManagement::sendMail($maildata); 
			if (!$mailsent) {
				die('Mail error sending tell a friend');
			}
			else {
				echo 'success';
			}
			
			die();
			break;
		case 'addConnection':
			$type = $_POST['type']; //1 = golf club, 2 = golf course, 3 = restaurant, 4 = accomodation
			switch ($type) {
				case 'club':
					$conn_type = 1;
					break;
				case 'course':
					$conn_type = 2;
					break;
				case 'restaurant':
					$conn_type = 3;
					break;
				case 'accomodation':
					$conn_type = 4;
					break;
			}
			$db->where('conn_gc_id', $_POST['parent']);
			$db->where('conn_type', $conn_type);
			$db->where('conn_obj_id',$_POST['choice']);
			$test = $db->getOne('golfcourse_connections');
			if (!$test) {
				$data = array(
						'conn_gc_id' => $_POST['parent'],
						'conn_type' => $conn_type,
						'conn_obj_id' => $_POST['choice']
					);
				$result = $db->insert('golfcourse_connections', $data);
				if ($result) {
					echo $result;
				}
				else {
					echo 'Error';
				}
			}
			else {
				echo 'Dubbel';
			}
			die();
			break;
		case 'registerMember':
			$db->where('user_email', $_POST['email']);
			$check = $db->getOne('users');
			if (count($check) > 0) {
				echo '
						<h2>' . constant('EMAIL_EXIST') . '</h2>
						<h3>' . constant('TRY_AGAIN_OR_LOGIN') . '</h3>
					';
				die();
			}
			else {
				$settings = Settings::getSettings();
				$mail_to = $_POST['email'];
				
				
				$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    			$pass = array(); //remember to declare $pass as an array
    			$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    			for ($i = 0; $i < 8; $i++) {
    			    $n = rand(0, $alphaLength);
    			    $pass[] = $alphabet[$n];
    			}
    			$password = implode($pass); 
				
				
			
				
				
				$encPWD = Accounts::encryptPassword($password);
				
				$data = array(
						'user_email' => $_POST['email'],
						'user_password' => $encPWD,
						'user_verified' => 1,
						'user_account_type' => 1,
						'user_created' => date('Y-m-d H:i')
					);
				$result = $db->insert('users', $data);
				if ($result) {
					
					$log = date('Y-m-d H:i:s') . "\t" . 'Member added ' . $_POST['email'] . "\t" . 'Password: ' . $password . "\t" . 'Enc_pw: ' . $encPWD . "\n";
					file_put_contents ( 'logfiles/login.log' , $log , FILE_APPEND );
					
					$ver_code = Users::getVerificationCode($result, $mail_to);
					$maildata = array(
							'subject' => constant('WELCOME') . ' ' . constant('TO') . ' ' . $settings['website_title'],
							'from' => $settings['setting_default_email'],
							'to' => $mail_to,
							'message' => constant('NEW_MEMBER_MAIL'),
							'template' => 'new_user_registration',
							'vercode' => $ver_code
						);
					$mailsent = MailManagement::sendMail($maildata); 
					if (!$mailsent) {
						die('Mail error');
					}
					
					session_start();
					$_SESSION['admin_level'] = 100;
					$_SESSION['userid'] = $result;
					
					echo 	'<center><img src="/_icons/logotype.png" title="SkiinginMobile logotype" /></center>
							<p>' . constant('REGISTER_SUCCESS') . '!!!</p>
							<p>' . constant('YOUR_TEMP_PASSWORD') . '<div id="temp_pwd">' . $password . '</div></p>
							<p><a href="' . $_POST['target'] . '" title="' . constant('VIEW_TARGET') . '">' . constant('VIEW_TARGET') . '</a></p>
						';
				}
				else {
					echo 'Nej' . $result;
				}
			}
			die();
			break;
		case 'changeMemberPass':
			
			
			
			$encPWD = Accounts::encryptPassword($_POST['pass']);
			$data = array(
						'user_password' => $encPWD
					);
			$db->where('user_id', $_SESSION['userid']);
			$result = $db->update('users', $data);
			if ($result) {
				echo constant('YOUR_PASSWORD_HAS_BEEN_CHANGED');
			}
			else {
				echo 'Password not chnaged';
			}
			die();
			break;
	}
}

Class Forms {
	
	public static function Form($data, $fields, $values = null) { 
		session_start();
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		//For downloading images to table-specific folders and name after id
		$app_file_tables = array(
				'bazaar',
				'golfclubs',
				'golfcourses',
				'app_events',
				'app_articles',
				'restaurants',
				'beds',
				'deals',
				'deal_suppliers',
				'offers',
				'users',
				'rewards',
				'shops'
			);
		$array_name = $_SESSION['userid'] . '-' . date('y-m-d-h-i-s');
		$_SESSION[$array_name] = $data;
		$prefix = Database_management::getPrefix($data['table']); 
		$html = '
					<form name="' . $data['table'] . '" method="post" action="/_php/db.management.php" id="' . $data['table'] . '" class="admin_form"
			';
		if (in_array($data['table'], $app_file_tables)) {
			$html .= ' enctype="multipart/form-data"';
		}					
		$html .= '			
					>
						<input type="hidden" name="data" value="' . $array_name . '" />
			';
		$image_id = $data['post_id']; 
		if (isset($fields['post_id'])) { 
			$html .= '
						<input type="hidden" name="post_id" id="post_id" value="' . $values['post_id'] . '" />
				';
			unset($fields['post_id']);
		}
		foreach ($fields as $key => $value) {
			$editor = false;
			if (!is_array($value) && strpos($value, 'editor')) {
				$editor = true;
			}
			if ($value == 'hidden') {
				$html .= '
						<input type="hidden" name="' . $key . '" id="' . $key . '" value="' . $data[$key] . '" />
					';
			}
			else {
				$html .= '
							<div class="form_row clear_both ' . $key
					; 
				if (is_array($data['hide_row']) && in_array($key, $data['hide_row']) && !in_array($key, $data['hide_row_choice'])) {
					$html .= ' hidden';
				}	
				$html .= '">
								<div class="form_title" id="' . $data['table'] . 'form_title_' . $key . '">
									' . constant(strtoupper(str_replace($prefix, '' , $key))) . '
					';
				if (is_array($data['required']) && in_array($key, $data['required'])) {
					$html .= '
									<span class="req_icon">*</span>';
				}
				if (str_replace($prefix, '' , $key) == 'cityid') {
					$html .= ' <span class="form_title_extra extra_city"><span class="form_title_link" id="add_city">' . constant('ADD_CITY') . '</span></span>';
				}
				if (str_replace($prefix, '' , $key) == 'districtid') {
					$html .= ' <span class="form_title_extra extra_district"><span class="form_title_link" id="add_district">' . constant('ADD_DISTRICT') . '</span></span>';
				}
				if (is_array($data['short_desc']) && in_array($key, $data['short_desc'])) {
					$settings = Settings::getSettings(); 
					$max_chars = '';
					$added_chars = strlen(utf8_decode($values[$key])); 
					$html .= '
							<div class="max_chars" data-maxchars="' . $settings['setting_shortdesc_charlen'] .'">
								<span class="usedchars">' . $added_chars . '</span> / ' . $settings['setting_shortdesc_charlen'] .'
							</div>
						';
				}
				if (defined(strtoupper(str_replace($prefix, '' , $key)) . '_HELPTEXT')) {
					$html .= '
							<div class="helptext">
								' . constant(strtoupper(str_replace($prefix, '' , $key)) . '_HELPTEXT') . '
							</div>		
						';
				}
				$html .= '
								</div>
								<div class="form_input
					';
				if ($value == 'nested_dropdown' || $value == 'dropdown' || $value == 'location') {
					$html .= '
									selectbox
						';
				}
				$html .= '
								" id="form_' . $key . '">
					'; 
				switch ($value) {
					case 'text':
					case 'email':
					case 'password': 
						$html .= '
									<input type="' . $value . '" name="' . $key . '" id="' . $key . '" class="' . $value . ' ' . str_replace($prefix, '', $key) . '
							';
						if (is_array($data['required']) && in_array($key, $data['required'])) {
							$html .= '
										required" data-errormsg="' . constant(strtoupper(str_replace($prefix, '', $key))) . '
										';
						}
						$html .= '"';
						if (is_array($fields['inactive']) && in_array($key, $fields['inactive'])) {
							$html .= ' disabled="disabled" ';
						}
						$html .= '
								 	value="' . $values[$key] . '" />
							';
						break;
					case 'autofill':
						$html .= '
									<span class="autofill_value" id="' . $data[$key]['span_id'] . '">' . $data[$key]['span_val'] . '</span>
									<input type="text" id="' . $data[$key]['ac_var'] . '" class="conn_auto ui-autocomplete-input" autocomplete="off">
									<input type="button" id="' . $data[$key]['button_id'] . '" value="' . constant('CHOOSE') . '" data-spanid="' . $data[$key]['span_id'] . '_span" />
									<input type="hidden" name="' . $key . '" id="' . $key . '" value="' . $values[$key] . '" class="autofill_id" />
							';
						break;
					case $editor:
						$html .= '
									<textarea name="' . $key . '" id="' . $key . '" class="
								';
							if ($value != 'textarea') {
								$html .= '
											editor ' . $value . '
									';
							}
							if (is_array($data['required']) && in_array($key, $data['required'])) {
								$html .= '
											required
										';
							}
							$html .= '"';
							if (is_array($data['required']) && in_array($key, $data['required'])) {
								$html .= '
											data-errormsg="' . constant(strtoupper(str_replace($prefix, '', $key))) . '"
									';
							}
							$html .= '
										>
											' . $values[$key] . '
										</textarea>
							';
						break;
					case 'check':
						$html .= '
										<input type="checkbox" name="' . $key . '" id="' . $key . '"
							';
						if ($values[$key] == 1) {
							$html .= '
											checked="checked"';
						}
						$html .= ' 
										/>
										<label class="choice" for="private">
											
										</label>
							';
						break;
					
					
					case 'location': 
						switch ($key) {
							case $prefix.'countryid':
								$db->orderBy('Country', 'ASC');
								$countries = $db->get('app_countries');
								$name = $prefix.'countryid';
								$identifier = 'CountryId';
								$class = 'CountrySelect';
								$title = 'Country';
								$constant = 'CHOOSE_COUNTRY';
								$id = $data[$prefix.'countryid'];
								$html .= self::locationDropdown($countries, $name, $identifier, $class, $title, $constant, $id);
								break;
							case $prefix.'regionid':
								$name = $prefix.'regionid';
								$identifier = 'RegionId';
								$class = 'RegionSelect';
								$title = 'Region';
								if ($data[$prefix.'countryid'] > 0) {
									$db->where('CountryId', $data[$prefix.'countryid']);
									$db->orderBy('Region', 'ASC');
									$regions = $db->get('app_regions');
									$constant = 'CHOOSE_REGION';
									$id = $data[$prefix.'regionid']; 
								}
								else {
									$regions = array();
									$constant = 'CHOOSE_COUNTRY_FIRST';
									$id = 0;
								}
								$html .= self::locationDropdown($regions, $name, $identifier, $class, $title, $constant, $id);
								break;
							case $prefix.'cityid':
								$name = $prefix.'cityid';
								$identifier = 'CityId';
								$class = 'CitySelect';
								$title = 'City';
								if ($data[$prefix.'regionid'] > 0) {
									$db->where('RegionId', $data[$prefix.'regionid']);
									$db->orderBy('City', 'ASC');
									$cities = $db->get('app_cities');
									$constant = 'CHOOSE_CITY';
									$id = $data[$prefix.'cityid']; 
								}
								else {
									$cities = array();
									$constant = 'CHOOSE_REGION_FIRST';
									$id = 0;
								}
								$html .= self::locationDropdown($cities, $name, $identifier, $class, $title, $constant, $id);
								break;
						}
						break;
					case 'rel_language':
						$html .= '
								<div id="main_lang">
									<img src="/_icons/lang_' . $data['rel_language']['this'] . '.jpg" data-plang="' . $data['rel_language']['this'] . '" />
								</div>
								<div id="conn_lang" class="' . $data['table'] . '">
							';
						foreach ($data['rel_language']['all'] as $lang) {
							if ($lang != $data['rel_language']['this']) {
								if (is_array($data['rel_language']['taken'][0]) && in_array($lang, $data['rel_language']['taken'][0])) {
									$class = 'active';
								}
								else {
									$class = '';
								}
								$html .= '
										<img src="/_icons/lang_' . $lang . '.jpg" class="' . $class . '" data-id="' . $values['post_id'] . '" />
									';
							}
						}
						$html .= '
								</div>
								' . self::adminLanguageForm($data['table'])
							;
						break;					
					case 'multiple_check': 
						$type = str_replace($prefix, '', $key);
						switch ($type) {
							case 'categories':
								if ($data['table'] == 'articles') {
									$mc_id = 'category_id';
									$mc_title = 'category_title';
									$mc_array = $prefix . 'categories';
								}
								elseif ($data['table'] == 'newsletters') {
									$mc_id = 'nl_cat_id';
									$mc_title = 'nl_cat_title';
									$mc_array = 'newsletter_categories_rel'; 
								}
								break;
							case 'type':
								$mc_id = 'course_type_id';
								$mc_title = 'course_type_title';
								$mc_array = 'chosen_course_types';
								break;
							case 'facilities':
								$mc_id = 'facility_id';
								$mc_title = 'facility_title';
								$data['chosen_facilities'] = explode(',', $data['chosen_facilities']);
								$mc_array = 'chosen_facilities';
								break;
						}
						$i = 0;
						foreach ($data[$type] as $item) { 
							$title = $item[$mc_title];
							$html .= '
										<input type="checkbox" name="' . $key . '[]" id="' . $key . '" class="multicheck" value="' . $item[$mc_id] . '"
								';
							if (is_array($data[$mc_array]) && in_array($item[$mc_id], $data[$mc_array])) {
								$html .= '
												checked="checked"';
							}
							$html .= ' 
											/>
											<label class="choice" for="private">
												' . $title . '
											</label>
								';
							$i++;
							if ($i % 5 == 0) {
								$html .= '<br />';
								
							}
						}
						break;
					case 'multiple_radio': 
						$type = str_replace($prefix, '', $key); 
						$settings = Settings::getSettings(); 
						switch ($type) {
							case 'startpage':
								$labelVal = Array();
								$mr_id = 'startpage_position'; 
								$mr_array = 'startpage_items'; 
								$mr_q = $settings['setting_startpage_summaries'];
								$labelVal[0] = constant('NOT_ON_START');
								for ($i=1;$i<=$mr_q;$i++) {
									$labelVal[$i] = constant('POSITION');
								}
								break;
							case 'owner_type':
								$labelVal = Array();
								$mr_id = 'match_owner_type'; 
								$mr_array = 'match_owner_types'; 
								$mr_q = count($data['match_owner_types']);
								for ($i=1;$i<=$mr_q;$i++) { 
									$labelVal[$i] = constant($data['match_owner_types'][$i-1]['match_owner_title']);
								}
								break;
							case 'contactinfo':
								$labelVal = Array();
								$mr_id = 'default_value';
								$mr_q = 3;
								$labelVal[1] = constant('PHONE');
								$labelVal[2] = constant('EMAIL');
								$labelVal[3] = constant('BOTH');
								break;
							case 'company_size':
								$labelVal = Array();
								$mr_id = 'user_company_size_value';
								$mr_q = 6;
								$labelVal[1] = '1';
								$labelVal[2] = '2 - 5';
								$labelVal[3] = '6 - 20';
								$labelVal[4] = '21 - 50';
								$labelVal[5] = '51 - 100';
								$labelVal[6] = '> 100';
								break;
							case 'account_type':
								$labelVal = Array();
								$mr_id = 'user_account_type';
								$mr_q = 3;
								$labelVal[1] = constant('WEB_USER');
								$labelVal[2] = constant('GOLFCOURSE');
								$labelVal[3] = constant('SHOP');
								break;
							case 'reward':
								$labelVal = Array();
								$db->where('reward_status', 1);
								$rewards = $db->get('rewards');
								$mr_id = 'user_reward';
								$mr_q = count($rewards);
								$i = 0;
								foreach ($rewards as $reward) {
									$labelVal[$i] = $reward['reward_title_' . $_SESSION['site_language']];
									$i++;
								}
								break;
							case 'limiter':
								$labelVal = Array();
								$mr_id = 'deal_limiter';
								$mr_q = 2;
								$labelVal[1] = constant('TIME');
								$labelVal[2] = constant('BUYS');
								break;
						}
						for ($i=0;$i<=$mr_q;$i++) { 
							if (array_key_exists($i, $labelVal)) {
								$class = '';
								if (is_array($data[$mr_array]) && in_array($i, $data[$mr_array]) && $data[$mr_id] != $i) {
									$class = ' disabled';
								}
								if ($data[$mr_id] == $i) {
									$class .= ' checked';
								}
								$label = $labelVal[$i];
								$html .= '
										<div class="multiple_radio">
											<input type="radio" name="' . $key . '" class="' . $key . ' multiradio' .  $class . '" value="' . $i . '" id="' . $i . '"
									';
								if ($data[$mr_id] == $i) {
									$html .= '
												checked="checked"';
								}
								elseif (is_array($data[$mr_array]) && in_array($i, $data[$mr_array])) {
									$html .= '
												disabled="disabled"
										';
								}
								$html .= ' 
											/>
											<label class="choice" for="private">
												' . $label
									;
								if ($type == 'startpage' && $i != 0) {
									$html .= ' ' . $i;
								}
								$html .= '
											</label>
										</div>
									';
								if ($i % $startpage_summaries_cols == 0) {
									$html .= '<div class="clear_both"></div>';
								}
							}
						}	
						break;			
					case 'image':  
						
							if ($key=='') {
								$image = '<img id="image_preview" src="" style="display:none;" title="No image choosen" />';
								$choice = constant('CHOOSE');
							}
							else {
								$exts = array('jpg', 'jpeg', 'png', 'bmp', 'gif');
								foreach ($exts as $ext) {
									if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'. $image_id . '.' . $ext)) {
										$src = '/_' . $data['table'] . '/images/'. $image_id . '.'.$ext;
									}
								}
								if ($data['table'] == 'app_articles') {
									$image = '<img id="image_preview" src="' . $src . '" style="max-width:130px;max-height:88px" />';
								}
								else {
									$image = '<img id="image_preview" src="' . str_replace('_files', '_thumbs' , $values[$key]) . '" style="max-width:130px;max-height:88px" />';
								}
								$choice = constant('CHANGE');
							}
							$html .= '
										<div class="image_thmb">
											' . $image . '
										</div>
										<div class="input-append">
										    <input name="' . $key . '" id="fieldID" type="text" class="form_image
								';
							if (is_array($data['required']) && in_array($key, $data['required'])) {
								$html .= '
											required
										';
							}
							$html .= '"
								';
							$html .= 'value="' . $values[$key] . '"';
							$html .= '/>
											<a href="/_filemanager/dialog.php?type=1&field_id=fieldID" class="btn iframe-btn" type="button">' . $choice . ' ' . strtolower(constant('IMAGE')) . '</a>
										</div>
								';
						break;
					case 'date':
						$html .= '
									<input type="text" name="' . $key . '" id="' . $key . '" class="' . $value . ' ' . str_replace($prefix, '', $key) . ' datepicker
							';
						if (is_array($data['required']) && in_array($key, $data['required'])) {
							$html .= '
										required" data-errormsg="' . constant(strtoupper(str_replace($prefix, '', $key))) . '
										';
						}
						$html .= '"';
						if (is_array($fields['inactive']) && in_array($key, $fields['inactive'])) {
							$html .= ' disabled="disabled" ';
						}
						$html .= '
								 	value="' . $values[$key] . '" />
							';
						break;
					case 'datetime':
						$datetime = explode(' ', $values[$key]);
						$date = $datetime[0];
						$time = $datetime[1];
						$html .= '
									<input type="text" name="date" class="' . $value . ' ' . str_replace($prefix, '', $key) . ' datepicker date
							';
						if (is_array($data['required']) && in_array($key, $data['required'])) {
							$html .= '
										required" data-errormsg="' . constant(strtoupper(str_replace($prefix, '', $key))) . '
										';
						}
						$html .= '"';
						if (is_array($fields['inactive']) && in_array($key, $fields['inactive'])) {
							$html .= ' disabled="disabled" ';
						}
						$html .= '
								 	value="' . $date . '" />
								 	<select name="time" class="datepicker time">
								 ';
						for ($i=0;$i<25;$i++) {
							$otime = '';
							if ($i < 10) {
								$otime .= '0';
							}
							$otime .= $i . ':00:00';
							$html .='<option value="' . $otime . '"';
							if ($otime == $time) {
								$html .= 'selected="selected"';
							}
							$html .= '>' . substr($otime, 0, 5) . '</option>';
						}
						$html .= '
									</select>
									<input type="hidden" name="' . $key . '" class="' . $key . ' datetime" value="' . $values[$key] . '" />
							';
						break;
					case 'dropdown':
						$html .= $data[$key];
						break;
					case 'textarea':
						if (is_array($data['short_desc']) && in_array($key, $data['short_desc'])) {
							$sdclass = ' class="shortdesc"';
						}
						else {
							$sdclass = '';
						}
						$html .= '<textarea name="' . $key . '" id="' . $key . '"' . $sdclass . '>' . $values[$key] . '</textarea>';
						break;
					case 'file':
						$exts = array('jpg', 'jpeg', 'png', 'bmp', 'gif');
						foreach ($exts as $ext) {
							if (file_exists($_SERVER['DOCUMENT_ROOT'].'/_' . $data['table'] . '/images/'. $image_id . '.' . $ext)) {
								$src = '/_' . $data['table'] . '/images/'. $image_id . '.'.$ext;
							}
						}
						if ($src == '') {
							$src = '#';
						}
						$html .= '<img id="myImg" src="' . $src . '" alt="No image">&nbsp;&nbsp;<input type="file" name="filesToUpload" id="filesToUpload" value="ABC">';
						break;
					case 'nested_dropdown': 
						$html .= '
										<select name="' . $key . '" id="nested_under">
											<option value="-" selected="selected">--- ' . constant('CHOOSE') . ' ' . mb_strtolower(constant('PARENT'), 'UTF-8') . ' ---</option>
											<option value="0">' . constant('TOP_LEVEL') . '</option>
							';
						foreach ($data[$key] as $item) { 
							if ($item[$prefix . 'id'] == 0) {
								$name = constant(strtoupper($item['name']));
							}
							else {
								$name = $item['name'];
							}
							$html .= '
											<option value="' . $item[$prefix . 'id'] . '"
								'; 
							if ($item[$prefix . 'id'] == $values[$key] && $values[$key]!=0) {
								$html .= 'selected="selected"';
							}
							$html .= '
											>' . $name . '</option>
								';
						}
						$html .= '
										</select>
									</div>
								</div>
								<div class="form_row clear_both">
									<div class="form_title">
										' . constant('PLACE_AFTER') . '
									</div>	
									<div class="form_input selectbox"  id="subcategories_dropdown">
									';
						if (isset($id)) {
							 $html .= $after_select;
						}
						else {
							$html .= '
										<select name="' . $key . '_after" id="nested_under">
											<option value="0">--- ' . constant('CHOOSE') . ' "' . constant('PLACE_UNDER') . '" ' . constant('FIRST') . '
										</select>
								';
						}
						break;
				}
				$html .= '
								</div>
							</div>
							';
			}
		}
		$html .= '
						<div class="form_row">
							<div class="form_title">&nbsp;</div>
							<div class="form_input">
							<input type="submit" value="' . $data['submit'] . '" class="submit_button" />
						</div>
					</div>
				</form>
			';
		return $html;
	}
	
	public static function locationDropdown($data, $name, $identifier, $class, $title, $constant, $id = null) { 
		$html .= '
				<select name="' . $name . '" id="' . $name . '" class="' . $class . '">
					<option value="0">--- ' . constant($constant) . ' ---</option>
			';
		
		foreach ($data as $item) { 
			$html .= '
					<option value ="' . $item[$identifier] . '"
				';
			if ($id == $item[$identifier]) {
					$html .= ' selected="selected"';
			}
			$html .= '
					>' . $item[$title] . '</option>
				';
		}
		$html .= '</select>';
		return $html;
	}
	
	public static function getDropdown($data, $values, $id = null, $non_constant = false) { //print_r($values);
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$prefix = Database_management::getPrefix($data['table']);//echo $prefix; die();
		$dd = '
				<select name="' . $data['name'] . '" id="' . $data['name'] . '" 
					class="selectbox" data-errormsg="' . constant(strtoupper($data['name'])) . '">
					<option value="0">--- ' . constant(strtoupper($data['title'])) . ' ---</option>
			';
		foreach ($values as $value) {
			foreach ($value as $key => $value) { 
				if (defined(strtoupper($value))) {
					$option = constant(strtoupper($value));
				}
				else {
					$option = $value;
				}
			
				$dd .= '
						<option value="' . $key . '"
					';
				if ($key == $data['id']) {
					$dd .= ' selected="selected"';
					}
				if ($non_constant) {
					$dd .= '>' . $value . '</option>';
				}
				else {
					$dd .= '>' . $option . '</option>';
				}
			}
		}
		$dd .= '
				</select>
			';
		return $dd;
	}
	
	public static function appRegistrationForm() {
		$html = '
				<div id="appreg_form" class="ajax_form_container">
					<div id="appreg_form_info">
						<h1>' . constant('APPREG_FORM_TEXT') . '</h1>
						<p>' . constant('APPREG_FORM_INFO') . '</p>
					</div>
					<div id="appreg_form_name" class="form_box ajax_form_input" contenteditable="true">
						' . constant('NAME') . '
					</div>
					<div id="appreg_form_e-mail" class="form_box ajax_form_input" contenteditable="true">
						' . constant('EMAIL') . '
					</div>
					<div id="appreg_form_submit" class="form_box ajax_form_submit">
						' . constant('SEND') . '
					</div>
					<div class="clear_both"></div>
				</div>
			';
		return $html;
	}
	
	public static function contactForm() {
		$html = '
				<div id="contact_form">
					<div id="contact_form_content">
						<div id="contact_form_inner">
							<div id="contact_form_info">

							</div>
							<div id="contact_form_name" class="form_box contact_form_input" contenteditable="true">
								' . constant('NAME') . '
							</div>
							<div id="contact_form_phone" class="form_box contact_form_input" contenteditable="true">
								' . constant('PHONE') . '
							</div>
							<div id="contact_form_email" class="form_box form_email contact_form_input" contenteditable="true">
								' . constant('EMAIL') . '
							</div>
							<div id="contact_form_subject" class="form_box form_subject contact_form_input" contenteditable="true">
								' . constant('SUBJECT') . '
							</div>
							<div id="contact_form_message" class="form_box form_text contact_form_input" contenteditable="true">
								' . constant('MESSAGE') . '
							</div>
							<div id="contact_form_cancel" class="submit_button">
								' . strtoupper(constant('CANCEL')) . '
							</div>
							<div id="contact_form_submit" class="submit_button">
								' . strtoupper(constant('SEND')) . '
							</div>
							<div class="clear_both"></div>
						</div>
					</div>
					<div class="clear_both"></div>
				</div>
			';
		return $html;
	}
	
	public static function displayLoginForm() {
		$html = '
					<div class="popup login_wrapper">
						<div id="login_form" class="form_wrapper">
							<div class="popup_close">x</div>
							<input type="hidden" name="do_login" value="1" />
							<div class="form_row clear_both">
								<div id="reg_form_content">
									<!---center><img src="/_icons/logotype.png" title="SkiinginMobile logotype" /></center--->
									' . constant('LOGIN_FORM_INTRO') . '
								</div>
								<div class="form_title">
									' . constant('EMAIL') . '
								</div><!--.title-->
								<div class="form_input">
									<input type="email" id="login_email" class="required login_email" data-errormsg="' . constant('EMAIL') . '" />
								</div>
							</div>
							<div class="form_row clear_both">
								<div class="form_title">
									' . constant('PASSWORD') . '
								</div>
								<div class="form_input">
									<input type="password" id="login_password" class="required" data-errormsg="' . constant('PASSWORD') . '" />
								</div>
							</div>
							
							<div class="form_row clear_both">
								<div class="form_title">
									
								</div>
								<div class="form_input">
									<input type="submit" id="login_submit" class="submit_button" value="' . constant('LOG_IN') . '" />
								</div>
							</div>
							
							<div class="form_row clear_both">
								<div id="forgot_password">
									' . constant('FORGOT_PASSWORD') . '
								</div>
							</div>
						</div>
					</div>
			';
		return $html;
	}
	
	public static function displayPasswordForm() {
		$html .= '
					<div class="popup password_wrapper">
						<div id="password_form" class="form_wrapper">
							<div class="popup_close">x</div>
							<div>' . constant('EMAIL_RECOVERY_FORM') . '</div>
							<form name="password_form" method="post" action="/_php/db.management.php">
								<div id="password_recovery">
									<input type="hidden" name="do_password" value="1" />
									<div class="form_row clear_both">
										<div class="form_title">
											' . constant('EMAIL') . '
										</div>
										<div class="form_input">
											<input type="email" name="email" id="email" class="required password_email" data-errormsg="' . constant('EMAIL') . '" />
										</div>
									</div>
									<div class="form_row clear_both">
										<div class="form_title">
											<div>
												&nbsp;
											</div>
										</div>
										<div class="form_input">
											<input type="submit" name="submit" id="submit" class="submit_button" value="' . constant('SEND') . '" />
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				';
		return $html;
	}
	
	public static function displayPasswordRecoveryForm($result) {
		session_start();
		$data = array(
					'table' => $result['table'],
					'identifier' => $result['identifier'],
					'return_uri' => '/passwordrecovery/?dbu=1',
					'post_id' => $result['id'],
					'db-action' => 'update_password'
			);
		
		$array_name = 'pwrec-' . date('y-m-d-h-i-s');
		$_SESSION[$array_name] = $data;
		$html = '
			<div id="article">
				<div id="password_recovery_form">
					<form name="password_recovery_form" method="post" action="/_php/db.management.php" id="admin_form">
						<input type="hidden" name="data" value="' . $array_name . '" />
						<div class="form_row clear_both">
							<div class="form_title">
								' . constant('PASSWORD') . '<span class="req_icon">*</span>
							</div>
							<div class="form_input">
								<input type="password" name="password" id="password" class="required" data-errormsg="' . constant('PASSWORD') . '" />
							</div>
						</div>
						<div class="form_row clear_both">
							<div class="form_title">								
								' . constant('PASSWORD_CONFIRM') . '<span class="req_icon">*</span>
							</div>
							<div class="form_input">
								<input type="password" name="password_confirm" id="password_confirm" class="required" data-errormsg="' . constant('PASSWORD_CONFIRM') . '" />
							</div>
						</div>
						<div class="form_row clear_both">
							<div class="form_title">
								&nbsp;
							</div>
							<div class="form_input">
								<input type="submit" name="submit" id="submit" class="submit_button" value="' . constant('SEND') . '" />
							</div>
						</div>
					</form>
				</div>
			</div>
			';
		return $html;
	}
	
	public static function languageForm() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		$html = '
				<div id="language_form">
			';
		foreach ($site_languages as $language) {
			$html .= '
					<span id="' . $language . '" class="language_choice
				';
			if ($_SESSION['site_language'] == $language) {
				$html .= ' language_choosen';
			} 
			$html .= '" data-url="' . $_SERVER['REQUEST_URI'] . '">
						<img src="/_icons/lang_' . $language . '.jpg" title="' . constant('LANGUAGE_CHANGE') . ' ' . $language . '" />
					</span>
				';
		}
		$html .= '
				</div>
				<div class="no_translation">
					' . constant('NO_TRANSLATION_AVAILABLE') . '
				</div>
			';
		return $html;
	}
	
	public static function shareForm() {
		$html = '
				<div id="share" class="lightbox">
					<i class="fa fa-share-square-o" title="' . constant('SHARE_FORM') . '"></i>
				</div>
			';
			
		return $html;
	}
	
	public static function connectionFormGolfbladet($data) {
		$html = '
					<h1>' . $data['parent'] . ' &raquo; ' . $data['posts'] . '</h1>
						<div id="admin_left_wrapper">
			';
		if ($data['has_parent']) {
			$html .= '
							<div class="form_container">
								<div class="form_header conn_header">
									' . constant('CHOOSE') . ' ' . constant(strtoupper($data['parent'])) . '
								</div>
								<div class="form_row">
									<div class="form_title">
										' . $data['parent'] . '
									</div>
									<div class="form_input">
										<input type="text" class="conn_parent_auto"  id="' . $data['parent_id'] . '" />
									</div>
								</div>
								<div class="form_row">
									<div class="form_title">
										&nbsp;
									</div>
									<div class="form_input">
										<input type="button" class="submit_button" id="connChooseParent" name="connChooseParent" value="' . constant('CHOOSE') . '" data-url="' . $data['parent_url'] . '" data-action="' . $data['action'] . '" />
									</div>
								</div>
							</div>
						</div>
						<div id="admin_right_wrapper">
							<div class="form_container">
								<div class="conn_header">
									' . constant(strtoupper($data['parent'])) . '
								</div>
								<div id="conn_title">
								</div>
							</div>
						</div>
						<div class="clear"></div>
							<div id="admin_left_wrapper">
				';
		}
		$html .= '
								<div class="form_container">
									<div class="form_row">
										<div class="form_header conn_header">
											' . constant('CHOOSE') . ' ' . $data['post'] . '
										</div>
									</div>
									<div class="form_row">
										<div class="form_title">
											' . $data['post'] . '
										</div>
										<div class="form_input">
											<input type="text" class="conn_auto" id="' . $data['post_id'] . '" />
										</div>
									</div>
									<div class="form_row">
										<div class="form_title">
											&nbsp;
										</div>
										<div class="form_input">
											<input type="button" class="submit_button" id="connChoosePost" value="' . constant('ADD') . ' &raquo;" />
										</div>
									</div>
								</div>
							</div>
							<div id="admin_right_wrapper">
								<div class="form_row">
									<div class="conn_header">
										' . $data['posts'] . '
									</div>
									<div class="form_row">
										<div id="conn_posts">
											 ' . $data['post_list'] . '
										</div>
									</div>
								</div>
								<div class="form_row">
									<input type="button" class="submit_button conn_submit_button" id="conn_save" value="' . constant('SAVE') . ' "data-url="' . $data['parent_url'] . '" data-action="' . $data['save_action'] . '"
			';
		if (isset($data['company'])) {
			$html .= '
										data-company="' . $data['company'] . '"
				';
		}
		$html .= '
									 />
								</div>
							</div>

			';
		return $html;
	}
	
	public static function adminLanguageForm($type) {
		switch ($type) {
			case 'articles':
				$div_id = 'article_lang_rel';
				$choice = strtolower(constant('ARTICLE'));
				$title = constant('ARTICLE');
				$input_id = 'article';
				break;
			case 'categories':
				$div_id = 'category_lang_rel';
				$choice = strtolower(constant('CATEGORY'));
				$title = constant('CATEGORY');
				$input_id = 'category';
				break;
		}
		$html = '
				<div id="' . $div_id . '" class="form_container">
					<div>
						<div class="popup_close">x</div>
							<strong>' . constant('CHOOSE') . ' ' . $choice . '</strong>
							<div class="form_row">
								<div class="form_title">
									' . $title . '
								</div>
								<div class="form_input">
									<input type="hidden" id="plang" value="" />
									<input type="hidden" id="clang" value="" />
									<input type="hidden" id="parentid" value="' . $_GET['id'] . '" />
									<input type="text"  id="' . $input_id . '" class="conn_auto" />
									<div class="submit_button">
										' . constant('CHOOSE') . '
									</div>
								</div>					
							</div>
						</div>
					<div class="clear_both"></div>
				</div>
			';
		return $html;
	}
	
	public static function connectionForm($type) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('course_id', $_GET['parent']);
		$parent = $db->getOne('golfcourses', 'course_name');
		$parent = $parent['course_name'];
		switch ($type) {
			case 'club':
				$search_title = constant('GOLFCLUB');
				$search_id = 'golfclub';
				$list_title = constant('GOLF_CLUB_PARTNERS');
				$conn_type = 1;
				$conn_title = 'club_name';
				$db->where('conn_gc_id', $_GET['parent']);
				$db->where('conn_type', $conn_type);
				$db->join('golfclubs', 'club_id=conn_obj_id', "LEFT");
				$conns = $db->get('golfcourse_connections');
				break;
			case 'course':
				$search_title =  constant('GOLFCOURSE');
				$search_id = 'golfcourse';
				$list_title = constant('GOLF_COURSE_PARTNERS');
				$conn_type = 2;
				$conn_title = 'course_name';
				$db->where('conn_gc_id', $_GET['parent']);
				$db->where('conn_type', $conn_type);
				$db->join('golfcourses', 'course_id=conn_obj_id', "LEFT");
				$conns = $db->get('golfcourse_connections');
				break;
			case 'restaurant':
				$search_title =  'Restaurant';
				$search_id = 'restaurants';
				$list_title = constant('RECOMMEDNDED') . ' ' . mb_strtolower(constant('RESTAURANTS'), 'UTF-8');
				$conn_type = 3;
				$conn_title = 'restaurant_name';
				$db->where('conn_gc_id', $_GET['parent']);
				$db->where('conn_type', $conn_type);
				$db->join('restaurants', 'restaurant_id=conn_obj_id', "LEFT");
				$conns = $db->get('golfcourse_connections');
				break;
			case 'bed':
				$search_title =  constant('BED');
				$search_id = 'bed';
				$list_title = constant('RECOMMEDNDED') . ' ' . mb_strtolower(constant('BEDS'), 'UTF-8');
				$conn_type = 4;
				$conn_title = 'bed_name';
				$db->where('conn_gc_id', $_GET['parent']);
				$db->where('conn_type', $conn_type);
				$db->join('beds', 'bed_id=conn_obj_id', "LEFT");
				$conns = $db->get('golfcourse_connections');
				break;
		}
		if ($search_title == 'Accomodation') {
			$add_new_link = 'bed';
		}
		else {
			$add_new_link = $search_title;
		}
		$html = '
				<div id="connection_form_' . $search_title . '">
					<div class="connection_form_wrapper">
						<div class="form_row">
							<div class="form_title">
								' . constant('SEARCH') . ' ' . mb_strtolower($search_title, 'UTF-8') . '
							</div>
							<div class="form_input">
								<input type="text" id="' . $search_id . '" class="conn_auto ui-autocomplete-input" autocomplete="off" />
								<input type="button" id="' . $search_id . '_conn_add" value="' . constant('ADD') . ' &raquo;" data-parent="' . $_GET['parent'] . '" />
							</div>
							<div class="clear:both"></div>
						</div>
						<div class="form_row">
							<div class="form_title">
								' . constant('COULD_NOT_FIND') . ' ' . mb_strtolower($search_title, 'UTF-8') . '?
							</div>
							<div class="form_input">
								<a href="/admin/' . mb_strtolower(str_replace(' ','',$add_new_link), 'UTF-8') . 's/update/"><input type="button" value="' . constant('ADD') . ' ' . constant('NEW') . ' ' . mb_strtolower($search_title, 'UTF-8') . ' &raquo;" /></a>
							</div>
						</div>
						
						<div class="form_row">
							<div class="connection_list" id="' . $search_id . '">
								<h2>' . $list_title . ' ' . constant('FOR') . ' ' . $parent . '</h2>
								<table cellspacing="0" cellpadding="0" class="admintable">
									<tr>
										<th>
											' . constant('NAME') . '
										</th>
										<th class="tbl_delete red">
											&nbsp;✗
										</th>
									</tr>
			';
		foreach ($conns as $conn) {
			$get_data = array(
					'db_action' => 'delete',
					'table' => 'golfcourse_connections',
					'column' => 'conn_id',
					'return_uri' => $_SERVER['REQUEST_URI'],
					'id' => $conn['conn_id']
				);
			session_start();
			if (isset($_SESSION['userid'])) {
				$array_name = $_SESSION['userid'] . '-' . date('y-m-d-h-i-s') . $conn['conn_id'];
				$_SESSION[$array_name] = $get_data;
			}
			else {
				die('Unauthorized!');
			}
			$html .= '
									<tr>
										<td>
											' . $conn[$conn_title] . '
										</td>
										<td class="tbl_delete">
											<a href="/_php/db.management.php/?data=' . $array_name . '" class="link_delete red" title="Delete">&nbsp;✗</a>
										</td>
									</tr>
				';
		}
		$html .= '
								</table>
							</div>
							<div class="clear:both"></div>
						</div>
					</div>
				</div>
			';
		return $html;
	}
	
}

?>