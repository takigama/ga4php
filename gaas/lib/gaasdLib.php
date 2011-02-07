<?php 

// first include the ga4php.php file itself
$BASE_DIR = realpath(dirname(__FILE__)."/../../");
global $BASE_DIR;

// messy
require_once(dirname(__FILE__)."/../../lib/ga4php.php");



// first we check if our db exists, if not, we're not inited
$initState = false;
$backEnd = "";
global $initState, $backEnd;
if(file_exists($BASE_DIR."/gaas/gaasd/gaasd.sqlite")) {
	// then we check if the config vars we need exist in the db
	$backEndType = confGetVar("backend");
	
	if($backEndType == "AD") {
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
	
	if($backEnd == "IN") {
		$sql = 'CREATE TABLE "users" ("users_id" INTEGER PRIMARY KEY AUTOINCREMENT,"users_username" TEXT, "users_realname" TEXT, "users_password" TEXT, "users_tokendata" TEXT, "users_otk" TEXT);';
		$dbobject->query($sql);
	}
	
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
function confGetVar($varname)
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
function confPutVar($varname, $value)
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
class gaasGA extends GoogleAuthenticator
{
	
	function getData($username)
	{
	}
	
	
	function putData($username, $data)
	{
	}
	
	
	function getUsers()
	{
	}
}
?>