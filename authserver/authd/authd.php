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
				echo "Call to auth user token\n";
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
				echo "Call to add user token\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, false);	
				} else {
					$username = $msg["username"];				
					global $myga;
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, $myga->setUser($username));
				}
				break;
			case MSG_DELETE_USER:
				echo "Call to del user\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_DELETE_USER, false);	
				} else {
					$username = $msg["username"];				
					global $myga;
					msg_send($cl_queue, MSG_DELETE_USER, $myga->deleteUser($username));
				}
				break;
			case MSG_AUTH_USER_PASSWORD:
				// TODO
				echo "Call to auth user pass\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				
				$username = $msg["username"];
				$password = $msg["password"];
				$sql = "select users_password from users where users_username='$username'";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				$pass = "";
				foreach($res as $row) {
					$pass = $row["users_password"];
				}
				
				// TODO now do auth
				$ourpass = hash('sha512', $password);
				echo "ourpass: $ourpass\nourhash: $pass\n";
				if($ourpass == $pass) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, true);
					
				} else {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					
				}
				
				break;
			case MSG_SET_USER_PASSWORD:
				echo "Call to set user pass\n";
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					break;
				}
				
				$username = $msg["username"];
				$password = $msg["password"];
				
				$pass = hash('sha512', $password);
				
				$dbo = getDatabase();
				$sql = "update users set users_password='$pass' where users_username='$username'";
				
				$dbo->query($sql);

				msg_send($cl_queue, MSG_SET_USER_REALNAME, true);
				
				
				// these are irrelavent yet
				// TODO now set pass
				break;
			case MSG_SET_USER_REALNAME:
				echo "Call to set user realname\n";
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
				$sql = "update users set users_realname='$realname' where users_username='$username'";
				echo "sql: $sql\n";
				$dbo = getDatabase();
				
				$dbo->query($sql);

				msg_send($cl_queue, MSG_SET_USER_REALNAME, true);
				
				// TODO now set real name
				break;
			case MSG_SET_USER_TOKEN:
				// TODO
				echo "Call to set user token\n";
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
				echo "Call to set user token type\n";
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
			case MSG_GET_USERS:
				// TODO this needs to be better
				$sql = "select * from users";
				
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				
				$users = "";
				$i = 0;
				foreach($res as $row) {
					$users[$i]["username"] = $row["users_username"];
					$users[$i]["realname"] = $row["users_realname"];
					if($row["users_password"]!="") {
						$users[$i]["haspass"] = true;
					} else {
						$users[$i]["haspass"] = false;
					}
					echo "user: ".$users[$i]["username"]." has tdata: \"".$row["users_tokendata"]."\"\n";
					if($row["users_tokendata"]!="") {
						$users[$i]["hastoken"] = true;
					} else {
						$users[$i]["hastoken"] = false;
					}
					$i++; 
				}
				msg_send($cl_queue, GET_USERS, $users);
				
				// TODO now set token 
				break;
				
		}		
	}	
}

?>