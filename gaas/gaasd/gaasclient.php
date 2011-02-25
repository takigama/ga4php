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
	echo "\tprovisiontoken username [HOTP|TOTP] [KEY]- provision the user \"username\"\n";
	echo "\tassign username tokenid - assign a hardware token to a user\n";
	echo "\taddtoken token_name token_key token_type - adds a hardware token to the DB\n";
	echo "\tgethwtokens - gets a list of hardware tokens by token_name\n";
	echo "\tgetusers [admin|client] [part-of-username] [yes] - get user list with admin or client group, part of a username and return only those with tokens (yes)\n";
	echo "\tdeleteuser username - deletes the key for the specified user\n";
	echo "\n";
	exit(0);
}

if($argc < 2) {
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
	case "assign":
		$username = $argv[2];
		$tokenid = $argv[3];
		$ret = $myga->MSG_ASSIGN_TOKEN($username, $tokenid);
		break;
	case "gethwtokens":
		$ret = $myga->MSG_GET_HARDWARE();
		foreach($ret as $tok) {
			echo "Token, ".$tok["name"]." is of type ".$tok["type"]."\n";
		}
		break;
	case "addtoken":
		$tokenid = $argv[2];
		$tokenkey = $argv[3];
		$tokentype = $argv[4];
		$ret = $myga->MSG_ADD_HARDWARE($tokenid, $tokenkey, $tokentype);
		break;
	case "provisiontoken":
		$username = $argv[2];
		$ttype = "";
		$tkey = "";
		if(isset($argv[3])) $ttype = $argv[3];
		if(isset($argv[4])) $tkey = $argv[4];
		$ret = $myga->MSG_PROVISION_USER($username, $ttype, $tkey);
		break;
	case "getusers":
		$group = "client";
		$partof = "";
		$onlytokens = "no";
		if(isset($argv[2])) $group = $argv[2];
		if(isset($argv[3])) $partof = $argv[3];
		if(isset($argv[4])) $onlytokens = $argv[4];
		$ret = $myga->MSG_GET_USERS($group, $partof, $onlytokens);
		foreach($ret as $user => $real) {
			echo "$real ($user)\n";
		}
		break;
	case "deleteuser":
		$ret = $myga->MSG_DELETE_USER($argv[2]);
		if($ret) {
			echo "Delete user token succeeded\n";
		} else {
			echo "Delete user token failed\n";
		}
		break;
	default:
		echo "No such command, ".$argv[1]."\n";
		usage();
		
}

?>
