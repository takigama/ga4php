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

if(!isset($argv[1])) {
	echo "Usage: ".$argv[0]." add|auth username passcode\n";
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
}
?>