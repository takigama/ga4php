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
	
	
	/// ok, this is just testing stuff... create queue
	global $MSG_QUEUE_KEY_ID_SERVER, $MSG_QUEUE_KEY_ID_CLIENT;
	
	
	
	$cl_queue = msg_get_queue($MSG_QUEUE_KEY_ID_CLIENT, 0666 | 'IPC_CREAT');
	$sr_queue = msg_get_queue($MSG_QUEUE_KEY_ID_SERVER, 0666 | 'IPC_CREAT');

	$myga = new gaasGA();
	global $myga;
	
	
	print_r($myga);
	
	while(true) {
		msg_receive($sr_queue, 0, $msg_type, 16384, $msg);
		echo "Got message $msg_type\n";
		print_r($msg);
		switch($msg_type) {
			case MSG_AUTH_USER:
				echo "got auth message, $msg\n";
				$username = $msg["user"];
				$passcode = $msg["passcode"];
				global $myga;
				msg_send($cl_queue, MSG_AUTH_USER, $myga->authenticateUser($username, $passcode));
				break;
			case MSG_ADD_USER:
				echo "add user\n";
				$username = $msg["username"];
				global $myga;
				msg_send($cl_queue, MSG_ADD_USER, $myga->setUser($username));
				break;
			case MSG_DELETE_USER:
				break;
			default:
				echo "um??\n";
				
		}		
		echo "Back to wait\n";
	}	
}

?>