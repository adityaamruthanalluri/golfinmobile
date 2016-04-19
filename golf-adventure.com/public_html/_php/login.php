<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/MysqliDb.php');
include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_php/accounts.php');
$db = new MysqliDb($server, $user, $password, $database);
// prevent direct access 
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
} 
session_start();
if (isset($_POST['email']) && $_POST['email']!=''){$email = $_POST['email'];}
if (isset($_POST['pass']) && $_POST['pass']!=''){$password = $_POST['pass'];}
if (isset($_POST['account']) && $_POST['account']!=''){$account = $_POST['account'];}
$table = 'users';
$col = 'user';
$db->where ('user_email', $email);
$password_enc = Accounts::encryptPassword($password);
$column = $col . '_email'; 
//echo $table; die();

$login = $db->getOne ($table); 
if ($login[$col . '_password'] == $password_enc) { 
	if ($login[$col . '_verified'] == 1) { 
		$db->where('account_id',$login['user_account_type']);
		$result = $db->getOne('accounts'); 
		$_SESSION['admin_level'] = $result['account_admin_level'];
		$_SESSION['userid'] = $login[$col.'_id']; 
		if ($result['account_admin_level'] > 299) {
			$subfolder = '';
			$_SESSION["RF"]["subfolder"] = $subfolder;
		}
		$sql[$col . '_last_login'] = Date("Y-m-d H:i:s");
		$db->where($col . '_id', $_SESSION['userid']);
		$db->update($table,$sql);
		$log = date('Y-m-d H:i:s') . "\t" . 'User ' . $user['user_id'] . ' logged in' . "\n";
		file_put_contents ( 'logfiles/login.log' , $log , FILE_APPEND );
		echo '0';
	}
	else {
		$log = date('Y-m-d H:i:s') . "\t" . 'Not verified ' . $_POST['email'] . "\t" . ' tried to log in' . "\n";
		file_put_contents ( 'logfiles/login.log' , $log , FILE_APPEND );
		echo '1';
	}
}
else {
	$log = date('Y-m-d H:i:s') . "\t" . 'Password error' . "\t" . $_POST['email'] . ' tried to log in' . "\t" . $password_enc . "\n";
	file_put_contents ( 'logfiles/login.log' , $log , FILE_APPEND );
	echo '2';
}

?>