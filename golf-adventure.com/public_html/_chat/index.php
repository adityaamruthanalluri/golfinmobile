<?php 
    session_start();
    //echo $_GET['usertype'] . '--';
	//require($_SERVER['DOCUMENT_ROOT'].'/_php/users.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'].'/_php/users.php');
	if (isset($_SESSION['userid'])) {
		$user = Users::getUser($_SESSION['userid']);
		$_SESSION['chat_userid'] = $user['user_first_name'] . ' ' . $user['user_last_name'] . ' ' . $user['user_id'];
	}
	elseif ($_GET['usertype']==='appuser') {
		$user = Users::getAppUser($_GET['userid']);
		$_SESSION['chat_userid'] = $user['app_user_fname'] . ' ' . $user['app_user_lname'] . ' ' . $user['app_user_id'];
	}
	
    if (!isset($_SESSION['chat_userid'])): 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <title>Chat2</title>
    
    <link rel="stylesheet" type="text/css" href="main.css" />
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js?ver=1.3.2" type="text/javascript"></script>
    <script type="text/javascript" src="check.js"></script>
</head>

<body>

    <div id="page-wrap"> 
    
    	<div id="header">
        	<h1><a href="/examples/Chat2/">Chat v2</a></h1>
        </div>
        
        <h1>You have to log in to enter the chat</h1>
    	
        <div id="status">
        	<?php if (isset($_GET['error'])): ?>
        		<!-- Display error when returning with error URL param? -->
        	<?php endif;?>
        </div>
        
    </div>
    
</body>

</html>

<?php 
    else:
        require_once("chatrooms.php");
    endif; 
?>