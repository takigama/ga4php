<?php

if(file_exists("config.php")) {
	require_once("config.php");
} else {
	// config file doesnt exist, we must abort sensibly
}

// get out master library for ga4php
require_once("../lib/lib.php");

	
//exit(0);
// first we want to fork into the background like all good daemons should
//$pid = pcntl_fork();
$pid = 0;

if($pid == -1) {
	
} else if($pid) {
	// i am the parent, i shall leave
	echo "i am a parent, i leave\n";
	exit(0);
} else {
	global $MSG_QUEUE_KEY_ID_SERVER, $MSG_QUEUE_KEY_ID_CLIENT;
	
	$cl_queue = msg_get_queue($MSG_QUEUE_KEY_ID_CLIENT, 0666 | 'IPC_CREAT');
	$sr_queue = msg_get_queue($MSG_QUEUE_KEY_ID_SERVER, 0666 | 'IPC_CREAT');

	$myga = new gaasGA();
	global $myga;
	
	
	print_r($myga);
	
	while(true) {
		msg_receive($sr_queue, 0, $msg_type, 16384, $msg);
		print_r($msg);
		switch($msg_type) {
			case MSG_AUTH_USER_TOKEN:
				// minimal checking, we leav it up to authenticateUser to do the real
				// checking
				if(!isset($msg["user"])) $msg["user"] = "";
				if(!isset($msg["passcode"])) $msg["passcode"] = "";
				$username = $msg["user"];
				$passcode = $msg["passcode"];
				global $myga;
				msg_send($cl_queue, MSG_AUTH_USER_TOKEN, $myga->authenticateUser($username, $passcode));
				break;
			case MSG_ADD_USER_TOKEN:
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, false);	
				} else {
					$username = $msg["username"];				
					global $myga;
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, $myga->setUser($username));
				}
				break;
			case MSG_DELETE_USER:
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_DELETE_USER, false);	
				} else {
					$username = $msg["username"];				
					global $myga;
					msg_send($cl_queue, MSG_DELETE_USER, $myga->deleteUser($username));
				}
			case MSG_AUTH_USER_PASSWORD:
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				
				$username = $msg["username"];
				$sql = "select users_password from users where username='$username'";
				$dbo = getDatabase();
				
				
				// TODO now do auth
				
				break;
			case MSG_SET_USER_PASSWORD:
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					break;
				}
				
				// these are irrelavent yet
				// TODO now set pass
				break;
			case MSG_SET_USER_REALNAME:
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_REALNAME, false);
					break;
				}
				if(!isset($msg["realname"])) {
					msg_send($cl_queue, MSG_SET_USER_REALNAME, false);
					break;
				}
				
				$username = $msg["username"];
				$realname = $msg["realname"];
				$sql = "update set users_realnemd='$realname' where username='$username'";
				$dbo = getDatabase();
				
				$dbo->query($sql);

				msg_send($cl_queue, MSG_SET_USER_REALNAME, true);
				
				// TODO now set real name
				break;
			case MSG_SET_USER_TOKEN:
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN, false);
					break;
				}
				if(!isset($msg["tokenstring"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN, false);
					break;
				}
				
				global $myga;
				msg_send($cl_queue, MSG_SET_USER_TOKEN, $myga->setUserKey($username, $passcode));
				
				// TODO now set token 
				break;			
			case MSG_SET_USER_TOKEN_TYPE:
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, false);
					break;
				}
				if(!isset($msg["tokentype"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, false);
					break;
				}
				
				$username = $msg["username"];
				$tokentype = $msg["tokentype"];
				global $myga;
				msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, $myga->setTokenType($username, $tokentype));
				
				// TODO now set token 
				break;			
				
		}		
	}	
}

?>