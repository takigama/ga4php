<?php 

// get out master library for gaasd daemon
require_once("../lib/gaasdLib.php");

// first we want to fork into the background like all good daemons should
//$pid = pcntl_fork();


// uncomment this bit and comment the fork above to stop it going into the background
$pid = 0;

if($pid == -1) {
	// we failed to fork, oh woe is me
} else if($pid) {
	// i am the parent, i shall leave
	//echo "i am a parent, i leave\n";
	exit(0);
} else {
	// here is where i need to swithc to TCP network protocol stuff
	// i must bind 127.0.0.1 though.
	// what i want to happen is this:
	// 1) server receives connection
	// 2) server forks off process to process connection
	// 3) main server continues.
	// a forked process thingy should be fully self contained and capable of dealing
	// with "problems", i.e. the parent doesnt want to have to clean up children
	
	// Here goes the tcp equivalent
	global $TCP_PORT_NUMBER;
	$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_bind($res, "127.0.0.1", $TCP_PORT_NUMBER);
	socket_listen($res);

	while(true) {
		$data_socket = socket_accept($res);
		// now i fork
		$forked = pcntl_fork();
		
		// TODO: DEAL WITH THIS PROPERLY
		if($forked == -1) {
			echo "Failed to fork\n";
		} else if(!$forked) {
			// I am the child, i process the request
			// all the shit down below goes in here
			$recvd = "";
			$continue = true;
			while($continue) {
				$size = socket_recv($data_socket, $recvd_a, 1024, 0);
				$recvd .= $recvd_a;
				if(preg_match("/.*\:EOD$/", $recvd)) {
					// we have a full string... break out
					$continue = false;
					break;
				}
			}
			
			$xps = explode(":", $recvd);
			$component =  unserialize(base64_decode($xps[1]));
			$msg_type = $component["type"];
			$msg = $component["data"];

			$data_returned = processMessage($msg_type, $msg);
			
			$d_comp["type"] = $msg_type;
			$d_comp["data"] = $data_returned;
			
			$realdata_returning = "AS:".base64_encode(serialize($d_comp)).":EOD";
			
			socket_send($data_socket, $realdata_returning, strlen($realdata_returning), 0);
			socket_close($data_socket);
		}
	}
}

function processMessage($msg_type, $msg)
{
	global $MESSAGES;

	$function = $MESSAGES[$msg_type]."_server";
	
	if(function_exists($function)) {
		return $function($msg);
	} else {
		error_log("Call to undefined function! $function\n");
		return false;
	}
	
}


?>