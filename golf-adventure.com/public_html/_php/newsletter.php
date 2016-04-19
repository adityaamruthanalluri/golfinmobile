<?php
//displayCategories()
//displayForm()
//displayNewsletterForm()
//displayNewsletterList()
//newsletterSubscribers()
//sendNewsletter($id);
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/mail.management.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
//Language
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/SV.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
$db = new MysqliDb($server, $user, $password, $database);
$settings = Settings::getSettings();

if (isset($_POST['action']) && $_POST['action'] == 'newsletter') {
	//print_r($_POST);die();
	//Save to database 
	$data = array('newsletter_sub_email' => $_POST['subscriber_email']);
	$result = $db->insert('newsletter_subscribers', $data);
	if (!$result) {
		die('Database error');
	}
	//Send to admin
	$maildata = array(
			'subject' => constant('NEW_SUBSRIBER'),
			'from' => $settings['setting_default_email'],
			'to' => $settings['setting_default_email'],
			'template' => 'message',
			'message' =>  constant('NEW_SUBSRIBER') . ': ' . $_POST['subscriber_email'],
		);
	$mailsent = MailManagement::sendMail($maildata); 
	if (!$mailsent) {
		die('Mail error sending confirmation to admin');
	}
	//Send to new subscriber
	$maildata = array(
			'subject' => constant('NEW_SUBSRIBER'),
			'from' => $settings['setting_default_email'],
			'to' => $_POST['subscriber_email'],
			'template' => 'message',
			'message' =>  constant('WELCOPMENEW_SUBSRIBER'),
		);
	$mailsent = MailManagement::sendMail($maildata); 
	if (!$mailsent) {
		die('Mail error sending to new subscriber');
	}
	else {
		header('Location: http://' . $_SERVER['SERVER_NAME']);
	}
}

if (isset($_POST['action']) && $_POST['action'] == 'send_newsletter') {
	$message = Newsletter::sendNewsletter($_POST['id']);
	$numSent = $message[0];
	$numFailed = count($message[1]);
	$failed = $message[1];
	$html = '
			<strong>' . constant('SENT_NEWSLETTERS') . ': ' . $numSent . '<br />
			' . constant('FAILED_NEWSLETTERS') . ': ' . $numFailed . ':</strong><br />
		';
	foreach ($failed as $fail) {
		$log = date('Y-m-d H:i:s') . "\t" . 'newsletter: ' . $_POST['id'] . "\t" . $fail . "\n";
		file_put_contents ( 'logfiles/' . date('Y-m-d') . '_newsletter_fails.log' , $log , FILE_APPEND );
		$db->where('newsletter_sub_email', $fail);
		$db->delete('newsletter_subscribers');
		$data = array('newsletter_sent' => date('Y-m-d H:i:s'));
		$db->where('newsletter_id', $_POST['id']);
		$db->update('newsletters', $data);
		$html .= $fail . '<br />';
	}
	echo $html;	
}

class Newsletter {

	public static function displayForm() {
		$html = '
				<div id="newsletter_subscribe" class="lightbox">
					<i class="fa fa-envelope" title="' . constant('NEWSLETTER_FORM'). '"></i>
				</div>
			';
		return $html;
	}
	
	public static function displayNewsletterForm() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$nl_cats = $newsletter_categories;
		if (isset($id)) {
			$db->where('newsletter_id', $id);
			$result = $db->getOne('newsletters');
			$submit = constant('UPDATE');
			$db_action = 'update';
		}
		else {
			$submit = constant('CREATE');
			$db_action = 'insert';
			$result = null;
		}
		$data = array(
				'table' => 'newsletters',
				'identifier' => 'newsletter_id',
				'table_prefix' => 'newsletter_',
				'return_uri' => '/admin/newsletters/',
				'column' => 'newsletter_id',
				'datetime' => array ('newsletter_created','newsletter_changed'),
				'required' => array ('newsletter_title', 'newsletter_body'),
				'post_id' => $id,
				'categories' => $nl_cats,
				'submit' => $submit,
				'db-action' => $db_action
			);
			$fields = array(
					'newsletter_title' => 'text',
					'newsletter_body' => 'large_editor',
					'newsletter_categories' => 'multiple_check'
				);
		return Forms::Form($data, $fields, $result);
	}
	
	public static function displayNewsletterList() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
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
		$total = $db->get('newsletters');
		if (count($total) == 0) {
			$html .= '
					<p>' . constant('NEWSLETTERS_NOT_FOUND') . '</p>
				';
			return $html;
		}
		else { 
			$db->orderBy('newsletter_created', 'DESC');
			$newsletters = $db->get('newsletters');
			foreach ($newsletters as $newsletter) {
				$categories = '';
				$db->where('newsletter_category_rel_newsletter_id', $newsletter['newsletter_id']);
				$cats = $db->get('newsletter_categories_rel');
				
				foreach ($cats as $cat) {
					foreach ($newsletter_categories as $nl_cat) {
						if ($nl_cat['nl_cat_id'] == $cat['newsletter_category_rel_id']) {
							$categories .= substr($nl_cat['nl_cat_title'], 0, 5) . ' ';
						}
					}
					
				}
				if ($newsletter['newsletter_sent'] == '0000-00-00 00:00:00') {
					$send_status = '<a id="send_newsletter" class="' . $newsletter['newsletter_id'] . '">' . constant('SEND_NEWSLETTER') . '</a>';
				}
				else {
					$send_status = constant('SENT') . ' ' . $newsletter['newsletter_sent'];
				}
				$column = array(
						'ID' => $newsletter['newsletter_id'],
						'TITLE' => substr($newsletter['newsletter_title'], 0, 50), 
						'CATEGORY' => $categories,
						'SEND_STATUS' => $send_status,
						'CREATED' => $newsletter['newsletter_created'],
						'CHANGED' => $newsletter['newsletter_changed']
					);
				$columns[] = $column; 
			}
			$data = array(
					'numeric' => array('ID'),
					'datetime' => array('SENT','CREATED', 'CHANGED'),
					'table' => 'newsletters',
					'identifier' => 'newsletter_id',
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
			return $html;
		}
	}
	
	public static function newsletterSubscribers() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		
	}
	
	public static function sendNewsletter($id) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php');
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$settings = Settings::getSettings();
		//Get newsletter
		$db->where('newsletter_id', $id);
		$newsletter = $db->getOne('newsletters');
		$title = $newsletter['newsletter_title'];
		$body = $newsletter['newsletter_body'];
		$body = str_replace('src="/', 'src="http://' . $_SERVER['SERVER_NAME'] . '/', $body);
		$body = str_replace('../../..', 'http://' . $_SERVER['SERVER_NAME'], $body);
		//Get categories
		$db->where('newsletter_category_rel_newsletter_id', $id);
		$categories = $db->get('newsletter_categories_rel');
		foreach ($categories as $category) {
			$cats[] = $category['newsletter_category_rel_id'];
		}
		//Get recipients
		$recipients = array();
		foreach ($cats as $cat) {
			switch ($cat) {
				case 1:
					$result = $db->get('newsletter_subscribers');
					foreach ($result as $email) {
						$recipients[] .= $email['newsletter_sub_email'];
					}
					break;
				case 2:
					$db->where('user_account_type', 1);
					$result = $db->get('users');
					foreach ($result as $email) {
						$recipients[] .= $email['user_email'];
					}
					break;
				case 3:
					$db->where('user_account_type', 2);
					$result = $db->get('users');
					foreach ($result as $email) {
						$recipients[] .= $email['user_email'];
					}
					break;
				case 4:
					$db->where('match_owner <> 0');
					$db->join('users', 'users.user_id=matches.match_owner', 'LEFT');
					$db->groupBy('match_owner');
					$result = $db->get('matches');
					foreach ($result as $email) {
						$recipients[] .= $email['user_email'];
					}
			}
		}
		$recipients = array_filter($recipients);
		$recipients = array_unique($recipients, SORT_LOCALE_STRING);
		
		$data['template'] = 'newsletter';
		$data['to'] = $recipients;
		$data['subject'] = $title;
		$data['from'] = $settings['setting_default_email'];
		$data['content'] = $body;
		$result = MailManagement::sendMail($data);
		
		foreach ($recipients as $key => $value) {
			if (!in_array($value, $result[1])) {
				$log = date('Y-m-d H:i:s') . "\t" . 'newsletter: ' . $id . "\t" . $value . "\n";
				file_put_contents ( 'logfiles/' . date('Y-m-d') . '_newsletter_sends.log' , $log , FILE_APPEND );
			}
		}
		
		return $result;
	}
	
}

?>