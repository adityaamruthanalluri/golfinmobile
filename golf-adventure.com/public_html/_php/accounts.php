<?php
/*
encryptPassword($pwd)
getAccountsDropdown($id)
getAccountTitle($level)
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/settings.php');



class Accounts {
	
	public static function encryptPassword($pwd) { 
		$key = sha1('ThereWillBeBlood');
		$hpwd = hash_hmac('sha512', $pwd, $key);
		return $hpwd;
	}
	
	public static function getAccountsDropdown($id = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$accounts = $db->get('accounts');
		foreach ($accounts as $account) {
			$value = array(
					$account['account_id'] => 'ACCOUNT_'.$account['account_type']
				);
			$values[] = $value;
		}
		$data = array(
				'table' => 'accounts',
				'name' => 'user_account_type',
				'title' => 'account_type',
				'id' => $id
			);
		$dd = Forms::getDropdown($data, $values);
		return $dd;
	}

	public static function getAccountTitle($level) {
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		$db->where('account_id', $level);
		$title = $db->getOne('accounts');
		return $title['account_type'];
	}
	
}

?>