<?php

class FeedbackF {

	public static function feedback() {
		$html = '
				<div>
					<img src="/_icons/Feedback.png" />
				</div>
			';
		return $html;
	}
	
	public static function feedbackForm() {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$data = array(
				'table' => 'feedback_entries',
				'identifier' => 'fe_id',
				'table_prefix' => 'fe_',
				'return_uri' => '/feedback/thankyou',
				'required' => array (
						'fe_sender', 
						'fe_sender_mail', 
						'fe_sender_topic', 
						'fe_sender_text'
					),
				'submit' => constant('SEND'),
				'db-action' => 'insert'
			);
		$fields['fe_sender'] = 'text';
		$fields['fe_sender_mail'] = 'email';
		$fields['fe_sender_phone'] = 'text';
		$fields['fe_sender_text'] = 'textarea';
		return constant('FEEDBACK_FORM') . Forms::Form($data, $fields, null);
	}
	
	public static function feedbackThnx() {
		return 'Thank you for your input';
	}

}

?>