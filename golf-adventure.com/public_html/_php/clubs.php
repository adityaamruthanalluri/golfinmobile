<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/beds.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/events.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/restaurants.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/common.php');


class Clubs {

	public static function displayClubForm($id = null) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		switch ($_SESSION['admin_level']) {
			case 200:
				$form_type = false;
				$form_owner = true;
				$owner_id = $_SESSION['user_id'];
				break;
			default:
				$form_type = true;
				$form_owner = false;
				break;
		}
		if (isset($id)) {
			$db->where('club_id', $id);
			$result = $db->getOne('golfclubs');
			$owner_id = $result['club_gkadministrator'];
			if ($_SESSION['admin_level'] < 201) {
				if ($owner_id != $_SESSION['userid']) {
					return Common::displayUnauthorized();
					die();
				}
			}
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			if (!$form_owner) {
				$owner_id = null;
			}
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		
		$data = array(
				'table' => 'golfclubs',
				'identifier' => 'club_id',
				'table_prefix' => 'club_',
				'required' => array (
						'club_name', 
						'club_street', 
						'club_zip', 
						'club_cityid', 
						'club_regionid', 
						'club_countryid', 
						'club_email', 
						'club_info_phone', 
						'club_short_description', 
						'club_long_description'
					),
				'post_id' => $id,
				'on_off' => array('club_status', 'club_sponsor'),
				'club_id' => $id,
				'club_countryid' => $result['club_countryid'],
				'club_regionid' => $result['club_regionid'],
				'club_cityid' => $result['club_cityid'],
				'short_desc' => array('club_short_description'),
				'owner' => $owner,
				'submit' => $submit,
				'db-action' => $db_action
			);
		if ($_SESSION['admin_level'] == 200) {
			$data['return_uri'] = '/admin/';
		}
		else {
			$data['return_uri'] = '/admin/golfclubs/';
		}
		
		$fields['club_id'] = 'hidden';
		$fields['club_name'] = 'text';
		$fields['club_image'] = 'file';
		
		$fields['club_countryid'] = 'location';
		$fields['club_regionid'] = 'location';
		$fields['club_cityid'] = 'location';
		$fields['club_street'] = 'text';
		$fields['club_zip'] = 'text';
		if ($_SESSION['admin_level'] > 200) {
			$fields['club_lng'] = 'text';
			$fields['club_lat'] = 'text';
		}
		$fields['club_url'] = 'text';
		$fields['club_facebook'] = 'text';
		$fields['club_twitter'] = 'text';
		
		$fields['club_email'] = 'email';
		$fields['club_alt_email1'] = 'email';
		$fields['club_alt_email2'] = 'email';
		
		$fields['club_info_phone'] = 'text';
		$fields['club_reserve_booking_phone'] = 'text';
		$fields['club_mobile'] = 'text';
		$fields['club_skype'] = 'text';
		
		$fields['club_short_description'] = 'textarea';
		//$fields['club_long_description'] = 'large_editor';
		if ($_SESSION['admin_level'] > 200) {
			$fields['club_sponsor'] = 'check';
			$fields['club_status'] = 'check';
			$data['return_uri'] = '/admin/golfclubs/';
		}
		
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayClubList() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$html = '
				<div class="form_row border_bottom">
					<div class="form_title">
						' . constant('SEARCH') . ' ' . strtolower(constant('GOLFCLUB')) . '
					</div>
					<div class="form_input">
						<input type="text" id="golfclub" class="conn_auto ui-autocomplete-input" autocomplete="off">
						<input type="button" id="golfclub_edit" value="' . constant('EDIT') . '" />
						<input type="button" id="golfclub_view"  value="' . constant('VIEW') . '" />
					</div>
					<div class="clear_both"></div>
				</div>
			';
		$settings = Settings::getSettings();
		$offset = $settings['setting_admin_offset']; 
		if (strpos($_SERVER['REQUEST_URI'], '?')) {
			$uri = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')); 
		}
		else {
			$uri = $_SERVER['REQUEST_URI'];
		} 
		if (substr($uri, -1) == '/') {
			$uri = substr($uri, 0, -1);
		}
		else {
			$uri = $uri;
		} 
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		else {
			$page = 1;
		}
		$limit = $offset;
		$offset = ($page * $offset) - $offset;
		$total = $db->get('golfclubs');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('CLUBS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else {
			$db->orderBy('club_name', 'ASC');
			$clubs = $db->get('golfclubs', array($offset, $limit));
			foreach ($clubs as $club) {
				$city = Common::getCity($club['club_cityid']);
				$country = Common::getCountry($club['club_countryid']);
				$column = array(
						'ID' => $club['club_id'],
						'NAME' => $club['club_name'], 
						'STREET' => $club['club_street'],
						'CITY' => $city,
						'COUNTRY' => $country,
						'LOCATION' => $club['club_lng'] . ' : ' . $club['club_lat'],
						'CREATED' => $club['club_created'],
						'CHANGED' => $club['club_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('CREATED', 'CHANGED'),
					'table' => 'golfclubs',
					'identifier' => 'club_id',
					'linked' => array('NAME'),
					'db_action' => 'delete'
				); 
				
			$html .= Lists::createList($columns, $data);
			if (substr($uri, -1) == '/') {
				$URI = substr($_SERVER['REQUEST_URI'], 0, -1);
			}
			else {
				$URI = $_SERVER['REQUEST_URI'];
			}
			if (count($total) > $limit) {
				$html .= Navigation::buildPageNavigation($URI, null, $page, count($total), $limit);
			}
		}
		return $html;
	}
	
	public static function displayClubView($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('club_id', $id);
		$club = $db->getOne('golfclubs'); 
		$image = Common::getImage('/_golfclubs/images/', $club['club_id']);
		if ($image != '0') {
			$image_elem = '<img src="' . $image . '" title="' . $club['club_name'] . '" />';
			
		}
		else {
			$image_elem = '';
		}
		$city = Common::getCity($club['club_cityid']);
		$courses = Courses::getCourses($id);
		if ($courses) {
			$courses_num = count($courses);
		}
		$html = '
				<div id="article" class="golfclub">
					<div id="article_wrapper">
						<h1>' . $club['club_name'] . '</h1>
						' . $image_elem . '
						<div class="gc_info_club">
							<div class="gc_info_club_title">
								' . constant('GOLFCOURSES') . '
							</div>
							<div class="gc_info_club_value">
								' . $courses_num . '
							</div>
							<div class="clear_both"></div>
							<div class="gc_info_club_title">
								' . constant('ADDRESS') . '
							</div>
							<div class="gc_info_club_value">
								' . $club['club_street'] . '<br />
								' . $club['club_zip'] . ' ' . $city . '
							</div>
							<div class="clear_both"></div>
			';
		if ($club['club_reserve_booking_phone'] != '') {
			$html .= '
							<div class="gc_info_club_title">
								' . constant('BOOK_RESERVE') . '
							</div>
							<div class="gc_info_club_value">
								' . $club['club_reserve_booking_phone'] . '
							</div>
							<div class="clear_both"></div>
				';
		}
		$html .= '
							<div class="gc_info_club_title">
								' . constant('CONTACT') . '
							</div>
							<div class="gc_info_club_value">
								' . $club['club_info_phone'] . '<br />
			';
		if ($club['club_mobile'] != '') {
			$html .= $club['club_mobile'] . '<br />';
		}
		$html .= '
								' . $club['club_email'] . '<br />
								<a href="' . $club['club_url'] . '" target="_blank" title="' . constant('LINK_TO') . ' ' . $club['club_name'] . '">' . $club['club_url'] . '</a><br />
			';
		if ($club['club_facebook'] != '') {
			$html .= '<a href="' . $club['club_facebook'] . '" target="_blank" title="' . constant('LINK_FACEBOOK') . '"><img src="/_icons/icon_facebook.png" /></a>';
		}
		if ($club['club_twitter'] != '') {
			$html .= '<a href="' . $club['club_twitter'] . '" target="_blank" title="' . constant('LINK_TWITTE') . '"><img src="/_icons/icon_twitter.png" /></a>';
		}
		if ($club['club_skype'] != '') {
			$html .= '<a href="skype:' . $club['club_skype'] . '?call" target="_blank" title="' . constant('LINK_SKYPE') . '"><img src="/_icons/icon_skype.png" /></a>';
		}
		$html .= '
							</div>
							<div class="clear_both"></div>
						</div>
						<p>' . $club['club_long_description'] . '</p>
					</div>
				</div>
				<div id="gc_info_wrapper">
					<div id="gc_info_content">
			';
		//Golf courses
		$html .= '
					<h1 class="gc_info">' . constant('GC_COURCES') . '</h1>
			';
		if ($courses) {
			foreach ($courses as $course) {
				$course_name = '<h2>' . $course['course_name'] . '</h2>';
				$course_desc = constant('NO_OF_HOLES') . ': ' . $course['course_no_of_holes'] . '<br />' . $course['course_description'];
				$html .= '
						<div class="gc_info_course">
							' . $course_name . '
							<p>' . $course_desc . '</p>
						</div>
					';
			}
		}
		
		//Offers
		$offers = Offers::getOffers($id); 
		if ($offers) {
		$html .= '
					<h1 class="gc_info">' . constant('GC_DEALS') . '</h1>
			';
			foreach ($offers as $offer) {
				$image = Common::getImage('/_offers/images/', $offer['offer_id']);
				if ($image != '0') {
					$image = '<img src="' . $image . '" />';
				}
				else {
					$image = '';
				}
				if ($offer['offer_url'] != '') {
					$offer_link = '<p><a href="' . $offer['offer_url'] . '" title="' . constant('READ_MORE') . '" class="right" target="_blank" />' . constant('READ_MORE') . '</a></p>';
				}
				else {
					$offer_link = '';
				}
				$html .= '
						<div class="gc_info_offer">
							<h2 class="gc_info">' . $offer['offer_title'] . '</h2>
							' . $image . '
							<p>' . strip_tags($offer['offer_body']) . '</p>
							' . $offer_link . '
						</div>
					';
			}
		}
		
		//Events
		$events = Events::getEvents($id); 
		if ($events) {
		$html .= '
					<h1 class="gc_info">' . constant('GC_EVENTS') . '</h1>
			';
			foreach ($events as $event) {
				if ($event['event_start'] == $event['event_end']) {
					$event_date = $event['event_start'];
				}
				else {
					$event_date = $event['event_start'] . ' - ' . $event['event_end'];
				}
				$image = Common::getImage('/_app_events/images/', $event['event_id']);
				if ($image != '0') {
					$image = '<img src="' . $image . '" />';
			}
				else {
					$image = '';
				}
				if ($event['event_url'] != '') {
					$event_link = '<p><a href="' . $event['event_url'] . '" title="' . constant('READ_MORE') . '" class="right" target="_blank" />' . constant('READ_MORE') . '</a></p>';
				}
				else {
					$event_link = '';
				}
				$html .= '
						<div class="gc_info_event">
							<div class="event_dates">' .  $event_date  . '</div>
							<h2 class="gc_info">' . $event['event_title'] . '</h2>
							' . $image . '
							<p>' . strip_tags($event['event_description']) . '</p>
							' . $event_link . '
						</div>
					';
			}
		}
		//Restaurants
		$restaurants = Restaurants::getRestaurants($id);
		if ($restaurants) {
		$html .= '
					<h1 class="gc_info">' . constant('GC_RESTAUTRANTS') . '</h1>
			';
			foreach ($restaurants as $restaurant) {
				$image = Common::getImage('/_restaurants/images/', $restaurant['restaurant_id']);
				if ($image != '0') {
					$image = '<img src="' . $image . '" />';
			}
				else {
					$image = '';
				}
				if ($restaurant['restaurant_url'] != '') {
					$restaurant_link = '<p><a href="' . $restaurant['restaurant_url'] . '" title="' . constant('READ_MORE') . '" class="right" target="_blank" />' . constant('READ_MORE') . '</a></p>';
				}
				else {
					$restaurant_link = '';
				}
				$html .= '
						<div class="gc_info_restaurant">
							<h2 class="gc_info">' . $restaurant['restaurant_name'] . '</h2>
							' . $image . '
							<p>' . strip_tags($restaurant['restaurant_description']) . '</p>
							' . $restaurant_link . '
						</div>
					';
			}
		}
		//Beds
		$beds = Beds::getBeds($id);
		if ($beds) {
		$html .= '
					<h1 class="gc_info">' . constant('GC_BEDS') . '</h1>
			';
			foreach ($beds as $bed) {
				$image = Common::getImage('/_beds/images/', $bed['bed_id']);
				if ($image != '0') {
					$image = '<img src="' . $image . '" />';
			}
				else {
					$image = '';
				}
				if ($bed['bed_url'] != '') {
					$bed_link = '<p><a href="' . $bed['bed_url'] . '" title="' . constant('READ_MORE') . '" class="right" target="_blank" />' . constant('READ_MORE') . '</a></p>';
				}
				else {
					$bed_link = '';
				}
				$html .= '
						<div class="gc_info_bed">
							<h2 class="gc_info">' . $bed['bed_name'] . '</h2>
							' . $image . '
							<p>' . strip_tags($bed['bed_description']) . '</p>
							' . $bed_link . '
						</div>
					';
			}
		}
		
		
		$html .= '
					</div>
				</div>
			';
		return $html;
	}
	
	public static function getClub($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('club_id', $id);
		$club = $db->getOne('golfclubs'); 
		return($club);
	}
	
	public static function getClubOwner($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('club_gkadministrator', $id);
		$club = $db->getOne('golfclubs'); 
		return $club['club_id'];
	}
	
}





?>