<?php 

require_once("globalLib.php");
require_once("gaasdMessages.php");

// messy
require_once(dirname(__FILE__)."/../../lib/ga4php.php");

// first we check if our db exists, if not, we're not inited
$initState = false;
$backEnd = "";
global $initState, $backEnd;
if(file_exists($BASE_DIR."/gaas/gaasd/gaasd.sqlite")) {
	// then we check if the config vars we need exist in the db
	$backEndType = confGetVal("backend");
	
	echo "backend type is $backEndType\n";
	
	if($backEndType == "AD") {
		echo "init state should be true\n";
		$backEnd = "AD";
		
		// TODO: we should now check all vars are set, but for now this will surfice
		$initState = true;
	}

	if($backEndType == "internal") {
		$backEnd = "IN";
		$initState = true;
	}
}

// have a gloval db handle so we dont have to keep opening the db all the time
// this may go away when we consider the implications for a parallel gaasd
$DB_HANDLE = false;
global $DB_HANDLE;


// a function to create our db
// TODO: error checking
function createDB()
{
	$dbobject = false;
	global $BASE_DIR, $initState, $backEnd;
	try {
		$dbobject = new PDO("sqlite:$BASE_DIR/gaas/gaasd/gaasd.sqlite");
	} catch(PDOException $exep) {
		error_log("execpt on db open");
		return false;
	}
	
	// users_tokendata is used by ga4php, users_otk is the qrcode data link if needed, 
	// tokentype is the software/hardware token types
	$sql = 'CREATE TABLE "users" ("users_id" INTEGER PRIMARY KEY AUTOINCREMENT,"users_username" TEXT, "users_realname" TEXT, "users_password" TEXT, "users_tokendata" TEXT, "users_otk" TEXT, "user_enabled" TEXT, "users_tokentype" TEXT);';
	$dbobject->query($sql);
	$sql = 'CREATE TABLE "config" ("conf_id" INTEGER PRIMARY KEY AUTOINCREMENT,"conf_name" TEXT, "conf_value" TEXT);';
	$dbobject->query($sql);
	$sql = 'CREATE TABLE "radclients" ("rad_id" INTEGER PRIMARY KEY AUTOINCREMENT,"rad_name" TEXT, "rad_ip" TEXT, "rad_secret" TEXT, "rad_desc" TEXT);';
	$dbobject->query($sql);
	$sql = 'CREATE TABLE "hardwaretokens" ("tok_id" INTEGER PRIMARY KEY AUTOINCREMENT,"tok_name" TEXT, "tok_key" TEXT, "tok_type" TEXT);';
	$dbobject->query($sql);
	
	return true;
}

// a function to get the database
function getDB()
{
	$dbobject = false;
	global $BASE_DIR, $DB_HANDLE;
	if($DB_HANDLE != false) return $DB_HANDLE;
	if(file_exists("$BASE_DIR/gaas/gaasd/gaasd.sqlite")) {
		try {
			$dbobject = new PDO("sqlite:$BASE_DIR/gaas/gaasd/gaasd.sqlite");
		} catch(PDOException $exep) {
			error_log("execpt on db open");
			return false;
		}
	} else {
		return false;
	}
	
	$DB_HANDLE = $dbobject;
	return $dbobject;
}


function confDelVar($varname)
{
	$db = getDB();
	
	$sql = "delete from config where conf_name='$varname'";
	$db->query($sql);
	
	return true;
}

// a funciton to deal with Config Vars
function confGetVal($varname)
{
	$db = getDB();
	
	$sql = "select conf_value from config where conf_name='$varname'";
	
	$result = $db->query($sql);
	
	if(!$result) return false;
	
	$val = "";
	foreach($result as $row) {
		$val = $row["conf_value"];
	}

	// TOTALLY GUNNA WORK!
	return $val;
}

// and a function to put vars
function confSetVal($varname, $value)
{
	$db = getDB();
	
	$sql = "delete from config where conf_name='$varname'";
	$db->query($sql);
	
	$sql = "insert into config values (NULL, '$varname','$value')";
	$db->query($sql);
	
	// TODO: do all this better
	return true;
}

// now we define our extended class
class gaasdGA extends GoogleAuthenticator
{
	
	function getData($username) {
		//echo "called into getdata\n";
		
		// get our database connection
		$dbObject = getDB();
		
		// set the sql for retreiving the data
		$sql = "select users_tokendata from users where users_username='$username'";
		
		// run the query
		$result = $dbObject->query($sql);
		
		// check the result
		//echo "next1\n";
		if(!$result) return false;
		
		// now just retreieve all the data (there should only be one, but whatever)
		//echo "next2\n";
		$tokendata = false;
		foreach($result as $row) {
			$tokendata = $row["users_tokendata"];
		}

		//echo "next3, $username, $tokendata\n";
		// now we have our data, we just return it. If we got no data
		// we'll just return false by default
		return $tokendata;
		
		// and there you have it, simple eh?
	}
	
	
	function putData($username, $data) {
		// get our database connection
		$dbObject = getDB();
		
		// we need to check if the user exists, and if so put the data, if not create the data
		$sql = "select * from users where users_username='$username'";
		$res = $dbObject->query($sql);
		if($res->fetchColumn() > 0) {
			// do update
			//error_log("doing userdata update");
			$sql = "update users set users_tokendata='$data' where users_username='$username'";
		} else {
			// do insert
			//error_log("doing user data create");
			$sql = "insert into users values (NULL, '$username', '', '', '$data', '')";
		}
		
		if($dbObject->query($sql)) {
			return true;
		} else {
			return false;
		}

	}
	
	function getUsers() {
		// get our database connection
		$dbObject = getDB();
		
		// now the sql again
		$sql = "select users_username from users";
		
		// run the query
		$result = $dbObject->query($sql);
		
		// iterate over the results - we expect a simple array containing
		// a list of usernames
		$i = 0;
		$users = array();
		foreach($result as $row) {
			$users[$i] = $row["username"];
			$i++;
		}
		
		// now return the list
		return $users;
	}
}
?>