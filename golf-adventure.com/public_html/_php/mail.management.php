<?php
//sendMail($data)
//generateMail() 
//generateMailBody($data)
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/_swift/lib/swift_required.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');
//Language
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/_lang/' . $_SESSION['site_language'] . '.php');
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'contact_us':
			$settings = Settings::getSettings();
			$maildata = array(
					'subject' => constant('CONTACT_FORM_SUBMITTED'),
					'from' => strip_tags($_POST['email']),
					'to' => $settings['setting_default_email'],
					'template' => 'contact_form',
					'subject' =>  $_POST['subject'],
					'message' =>  $_POST['message'],
					'name' => $_POST['name'],
					'phone' => $_POST['phone']
				);
			$mailsent = MailManagement::sendMail($maildata); 
			if (!$mailsent) {
				echo('Mail error');
				die();
			}
			else {
				$maildata['subject'] = constant('CONTACT_FORM_SUBMITTED_SUBJ');
				$maildata['to'] = strip_tags($_POST['email']);
				$maildata['from'] = $settings['setting_default_email'];
				$maildata['template'] = 'contact_form_thnx';
				$mailsent = MailManagement::sendMail($maildata); 
				if (!$mailsent) {
					echo('Mail error');
					die();
				}
				else {
					echo constant('CONTACT_FORM_WEB_THNX');
				}
			}
			break;
	}
}

class MailManagement {

	public static function sendMail($data) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/site_config.php'); 
		$content = self::generateMail($data);
		if (!is_array($data['to'])) {
			$recipients = array($data['to']);
		}
		else {
			$recipients = $data['to'];
		}
		$transport = Swift_SmtpTransport::newInstance($mail_transport['server'], $mail_transport['port'])
			->setUsername($mail_transport['user'])
			->setPassword($mail_transport['password'])
		;
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance()
			->setSubject($data['subject'])
			->setFrom($data['from'])
			->setBody($content)
		;
		$message->setContentType("text/html");
		$failedRecipients = array();
		$numSent = 0;
		foreach ($recipients as $address => $name) { 
			if (is_int($address)) {
				$message->setTo($name);
			}
			else {
				$message->setTo(array($address => $name));
			}
			$numSent += $mailer->send($message, $failedRecipients);
		}
		$result = array($numSent, $failedRecipients);
		return $result;
	}
	
	public static function generateMail($data) {
		session_start();
		$css = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/_css/mail.css', FILE_USE_INCLUDE_PATH);
		$content = self::generateMailBody($data);
		$html = '
				<html>
					<head>
						<style>
							' . $css . '
						</style>
					</head>
					<body>
						<div id="logotype">
							<a href="http://' . $_SERVER['SERVER_NAME'] . '">
								<img src="http://' . $_SERVER['SERVER_NAME'] . '/_icons/logotype.png" />
							</a>
						</div>
						<div id="content">
							' . $content . '
						</div>
						<div id="footer">
							' . constant('MAIL_SENT_FROM') . ' http://' . $_SERVER['SERVER_NAME'] . '
							<p>&copy ' . Date("Y") . ' - ' . constant('ALL_RIGHTS_RESERVED') . '</p>
						</div>
					</body>
				</html>
			 ';
		 return $html;
	}
	
	
//Templates
	function generateMailBody($data) { 
		$html = '
				<div>
			';
		switch ($data['template']) {
			case 'new_user':
				$html .= '
						<h1>' . constant('WELCOME') . '!</h1>
						<p>' . $data['message'] . '</p>
						<p>' . constant('LOGIN_INFORMATION') . ':</p>
						<p>' . constant('LOGIN_ON') . ': <a href="http://' . $_SERVER['SERVER_NAME'] . '/admin/">http://' . $_SERVER['SERVER_NAME'] . '/admin/</a><br />
					';
				break;
			case 'password_recovery':
				$html .= '
						<h1>' . constant('PASSWORD_RECOVERY_TITLE') . '!</h1>
						<p>' . $data['message'] . '</p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_TEXT') . '</p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_LINK') . ': <a href="http://' . $_SERVER['SERVER_NAME'] . '/passwordrecovery/?code=' . $data['vercode'] . '">http://' . $_SERVER['SERVER_NAME'] . '/passwordrecovery/?code=' . $data['vercode'] . '</a></p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_INFO') . '</p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_GOODBYE') . '</p>
					';
				break;
			case 'new_user_registration':
				$html .= '
						<h1>' . constant('WELCOME') . '!</h1>
						' . $data['message'] . '
						<p>' . constant('PASSWORD_NEW_VERIFICATION_MAIL_TEXT') . '</p>
						<p>' . constant('PASSWORD_NEW_VERIFICATION_MAIL_LINK') . ': <a href="http://' . $_SERVER['SERVER_NAME'] . '/passwordrecovery/?code=' . $data['vercode'] . '">http://' . $_SERVER['SERVER_NAME'] . '/passwordrecovery/?code=' . $data['vercode'] . '</a></p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_INFO') . '</p>
						<p>' . constant('PASSWORD_VERIFICATION_MAIL_GOODBYE') . '</p>
					';
				break;
			case 'newsletter':
				$html .= $data['content'];
				return $html;
				break;
			case 'message':
				$html = '
						<p>' . $data['message'] . '</p>
					';
				break;
			case 'contact_form':
				$html = '
						<p>' . constant('CONTACT_FORM_SUBMISSION') . '</p>
						<p>' . constant('NAME') . ': ' . $data['name'] . '<br />
						' . constant('PHONE') . ': ' . $data['phone'] . '<br />
						' . constant('EMAIL') . ': <a href="mailto' . $data['from'] . '">' . $data['from'] . '</a><br />
						' . constant('MESSAGE') . ':<br />' . $data['message'] . '
						</p>
							
					';
				break;
			case 'contact_form_thnx':
				$html = '
						<p>' . constant('CONTACT_FORM_THNX') . '</p>
						<p>' . constant('NAME') . ': ' . $data['name'] . '<br />
						' . constant('PHONE') . ': ' . $data['phone'] . '<br />
						' . constant('EMAIL') . ': <a href="mailto' . $data['to'] . '">' . $data['to'] . '</a><br />
						' . constant('MESSAGE') . ':<br />' . $data['message'] . '
						</p>
							
					';
				break;
		}
		$html .= '
				</div>
			'; 
		return $html;
	}
	
	public static function testMailService() {
		$settings = Settings::getSettings();
		$mailok = false;
		$maildata = array(
				'subject' => 'Mailtest',
				'from' => $settings['setting_default_email'],
				'to' => $settings['setting_default_email'],
				'template' => 'message',
				'message' =>  'Testing mail service'
			);
		$mailsent = self::sendMail($maildata);
		if ($mailsent) {
			$mailok = true;
		}
		return $mailok;
	}

}

?>