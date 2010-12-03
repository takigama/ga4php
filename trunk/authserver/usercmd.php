<?php
/*
 * 
 * 
 * This file is designed as a "script" extension to freeradius (or some such tool) for radius authentication.
 * Also provided is a simple web interface for managing users in freeradius.
 * 
 * The simple web interface should also provide a mechanism for configuring freeradius itself
 * 
 */

require_once("lib/authClient.php");

$myAC = new GAAuthClient();

/*
define("MSG_AUTH_USER_TOKEN", 1);
define("MSG_ADD_USER_TOKEN", 2);
define("MSG_DELETE_USER", 3);
define("MSG_AUTH_USER_PASSWORD", 4);
define("MSG_SET_USER_PASSWORD", 5);
define("MSG_SET_USER_REALNAME", 6);
define("MSG_SET_USER_TOKEN", 7);
define("MSG_SET_USER_TOKEN_TYPE", 8);

 */
if(!isset($argv[1])) {
	echo "Usage: ".$argv[0]." command username [args]\n";
	echo "\tadd: add <username> - returns token code url\n";
	echo "\tauth: auth <username> <passcode> - returns 0/1 for pass/fail\n";
	echo "\tdelete: delete <username> - deletes user\n";
	echo "\tauthpass: authpass <username> <password> - returns 0/1 for pass/fail\n";
	echo "\tsetpass: setpass <username> <password> - sets a password for a user (x to remove pass)\n";
	echo "\tsetname: setname <username> <realname> - sets the real name for a user\n";
	echo "\tsettoken: settoken <username> <tokenkey> - sets the key (hex) for a token\n";
	echo "\tsettype: settype <username> <tokentype> - sets a token type for a user\n";
	return 0;	
}

switch($argv[1]) {
	case "auth":
		if($myAC->authUser($argv[2], $argv[3])==1) {
			echo "Pass!";
		} else {
			echo "Fail!";
		}
		break;
	case "add":
		$myAC->addUser($argv[2]);
		break;
	case "delete":
		break;
	case "authpass":
		break;
	case "setpass":
		break;
	case "setname":
		break;
	case "settoken":
		break;
	case "settype":
		break;
}
?>