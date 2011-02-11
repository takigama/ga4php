<?php

require_once("../lib/gaasdClient.php");

$myga = new GAASClient();

global $argv;

function usage()
{
	global $argv;
	echo "Usage: ".$argv[0]." command [options]\n";
	echo "\nCommands:\n\tinit AD user password domain clientgroup admingroup - init for active directory installation\n";
	echo "\tinit IN user password - init for internal database\n";
	echo "\tstatus - return the status of the server\n";
	echo "\tsetadlogin username password domain\n";
	echo "\tsetclientgroup groupname - change the group membership requirements for client's with AD\n";
	echo "\tsetadmingroup groupname - change the group membership requirements for admin's with AD\n";
	echo "\tprovisionuser username [HOTP|TOTP] [KEY]- provision the user \"username\"\n";
	echo "\n";
	exit(0);
}

if($argc < 1) {
	usage();
}

switch($argv[1]) {
	case "status":
		$ret = $myga->MSG_STATUS();
		echo "Status: $ret\n";
		break;
	case "init":
		if($argv[2] == "AD") {
			if($argc < 7) usage();
		}
		$ret = $myga->MSG_INIT_SERVER("AD", $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
		if($ret) {
			echo "initialising server succeeded\n";
		} else {
			echo "initialising server failed\n";
		}
		break;
	case "setadlogin":
		$ret = $myga->MSG_SET_AD_LOGIN($argv[2], $argv[3], $argv[4]);
		if($ret) {
			echo "Resetting AD login details succeeded\n";
		} else {
			echo "Resetting AD login details failed\n";
		}
		break;
	case "setclientgroup":
		$ret = $myga->MSG_SET_CLIENT_GROUP($argv[2]);
		if($ret) {
			echo "Resetting AD client group details succeeded\n";
		} else {
			echo "Resetting AD client group details failed\n";
		}
		break;
	case "setadmingroup":
		$ret = $myga->MSG_SET_ADMIN_GROUP($argv[2]);
		if($ret) {
			echo "Resetting AD admin group details succeeded\n";
		} else {
			echo "Resetting AD admin group details failed\n";
		}
		break;
	default:
		echo "No such command, ".$argv[1]."\n";
		usage();
		
}

?>
