<?php

echo $_SERVER['SERVER_ADDR'] . '<br /><br />A';
/*
$servername ='tcp:tpyvmfbjop.database.windows.net, 1433';
$username='GolfAppDBLogin';
$password='cashisking2015!';

// Connect to the data source and get a handle for that connection.
$conn = mssql_connect ($servername, $username, $password);

if (!$conn){
      exit("Connection ODBC Failed:" . odbc_errormsg() );
}


try {   
	$conn = new PDO ( "sqlsrv:server = tcp:tpyvmfbjop.database.windows.net,1433; Database = GolfAppDB", "GolfAppDBLogin@tpyvmfbjop", "cashisking2015!");
    $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch ( PDOException $e ) {
	print( "Error connecting to SQL Server." );
	die(print_r($e));
}



function connect()
{
	// DB connection info
	$host = "tpyvmfbjop.database.windows.net,1433";
	$user = "GolfAppDBLogin@tpyvmfbjop";
	$pwd = "cashisking2015!";
	$db = "GolfAppDB";
	try{
		$conn = new PDO( "dblib:host=$host;dbname=$db", $user, $pwd);
		$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	catch(Exception $e){
		die(print_r($e));
	}
	return $conn;
}
$conn = connect();
if($conn === false){
    die(print_r(sqlsrv_errors()));
}
/*

 try {
    $hostname = "tpyvmfbjop.database.windows.net";
    $port = 1433;
    $dbname = "GolfAppDB";
    $username = "GolfAppDBLogin@tpyvmfbjop";
    $pw = "cashisking2015!";
    $dbh = new PDO ("dblib:host=$hostname;dbname=$dbname","$username","$pw");
  } catch (PDOException $e) {
    echo "Failed to get DB handle: " . $e->getMessage() . "\n";
    exit;
  }
  /*$stmt = $dbh->prepare("select name from master..sysdatabases where name = db_name()");
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    print_r($row);
  }
  unset($dbh); unset($stmt);
 
 
 
$conn = new COM ("ADODB.Connection", NULL, CP_UTF8) or die("Cannot start ADO");
$connStr = "PROVIDER=SQLOLEDB;SERVER=tcp:tpyvmfbjop.database.windows.net,1433;UID=GolfAppDBLogin@tpyvmfbjop;PWD=cashisking2015!;DATABASE=GolfAppDB";
$conn->open($connStr); //Open the connection to the database

if (!$conn) {
	echo 'Nix';
}
else {
	echo 'Ja';
}
 $sql = 'SELECT * FROM GolfCourses';
 $res=$conn->execute($sql);
while (!$res->EOF)  //carry on looping through while there are records
{
     $id=$res->Fields('ID')->value;
     echo $id;

}


 
$server = "tpyvmfbjop.database.windows.net,1433";
$user = "GolfAppDBLogin@tpyvmfbjop";
$pwd = "cashisking2015!";
$db = "GolfAppDB";

try{
    $conn = new PDO("dblib:host=$server;Database=$db",$user,$pwd);
    $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(Exception $e){
    die(print_r($e));
}
 
 


# connect to a DSN "MSSQLTest" with a user "cheech" and password "chong" 
$connect = odbc_connect("MSSQLTest", "cheech", "chong"); 

# query the users table for all fields 
$query = "SELECT * FROM users"; 

# perform the query 
$result = odbc_exec($connect, $query); 

# fetch the data from the database 
while(odbc_fetch_row($result)) { 
$field1 = odbc_result($result, 1); 
$field2 = odbc_result($result, 2); 
print("$field1 $field2\n"); 
} 

# close the connection 
odbc_close($connect); 





 
$connection = odbc_connect("Driver={SQL Server Native Client 10.0};Server=$server;Database=$db;", $user, $password);

if(!$connection) {
	echo 'Bä';
}
*/ 

$server = "tcp:tpyvmfbjop.database.windows.net,1433";
$user = "GolfAppDBLogin@tpyvmfbjop";
$pwd = "cashisking2015!";
$db = "GolfAppDB"; 
 
// connect to DSN MSSQL with a user and password
$connect = odbc_connect($server, $user, $pwd) or die
  ("couldn't connect");
odbc_exec($connect, "use ". $db);
$result = odbc_exec($connect, "SELECT CompanyName, ContactName " .
        "FROM Suppliers");
while(odbc_fetch_row($result)){
  print(odbc_result($result, "CompanyName") .
        ' ' . odbc_result($result, "ContactName") . "<br>\n");
}
odbc_free_result($result);
odbc_close($connect); 
 
 
 
 
 
 
 
 
 
?>