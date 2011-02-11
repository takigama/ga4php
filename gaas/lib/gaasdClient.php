<?php


require_once("globalLib.php");
require_once("gaasClientMessages.php");

// I am the gaasd client.. i know all, i see all... I am the "only" way to interact with the gaasd server.

class GAASClient {
	
	// the main send/receive functions. Communicates with gaasd
	// we always expect one send followed by one receive message
	function sendReceive($message_type, $message)
	{
		// yeah... this is totally gunna work
		global $TCP_PORT_NUMBER;
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$res = socket_connect($socket, "127.0.0.1", $TCP_PORT_NUMBER);
		if(!$res) {
			socket_close($socket);
			return false;
		}
		
		$msg["type"] = $message_type;
		$msg["data"] = $message;
		
		$datacomp = base64_encode(serialize($msg));
		$tosend = "AC:$datacomp:EOD";
		
		socket_send($socket, $tosend, strlen($tosend), 0);
		
		// get up to one meg of data - this is bad... i can feel this function
		// hurting alot
		// TODO FIX THIS - its garbage code... im not really sure how to handle this really
		// we need to read back as AS:data:EOD - i think it now does.. i hope, tho we need
		// timeouts now.
		$recvd = "";
		$continue = true;
		while($continue) {
			$size = socket_recv($socket, $recvd_a, 1024, 0);
			$recvd .= $recvd_a;
			if(preg_match("/.*\:EOD$/", $recvd)) {
				// we have a full string... break out
				$continue = false;
				break;
			}
		}
		
		
		// first check we got something that makes sense
		if(preg_match("/^AS:.*:EOD/", $recvd) < 1) {
			socket_close($socket);
			echo "Returned data is not in right format\n";
			// we have a problem jim
			return false;
		}
		
		$xps = explode(":", $recvd);
		
		$component =  unserialize(base64_decode($xps[1]));
		
		if($component["type"] != $message_type) {
			echo "Message type was not the same as original message\n";
			// we have a problem jim
			socket_close($socket);
			return false;
		}
		
		socket_close($socket);
		
		return $component["data"];
	}
	
	// this is one thing i love about php... how truely dynamic it can be in very easy to do ways.
	// im not entirely sure what im doing with this bit yet
	function __call($func, $params)
	{
		// im a little stuck here.
		//  want messages to be defineable in terms of plugins
		// but i dont think this is the way to do it
		global $MESSAGES;
		$st_defined = constant($func);
		//echo "func is $st_defined\n";
		$function_send = $MESSAGES[$st_defined]."_clientsend";
		$function_recv = $MESSAGES[$st_defined]."_clientrecv";
		//echo "real function is $function_send, $function_recv\n";
		
		if(function_exists($function_send)) {
			$fromsend = $this->sendReceive($st_defined, $function_send($params));
			if(function_exists($function_recv)) {
				return $function_recv($fromsend);
			} else return $fromsend;
		} else {
			error_log("Function, $function does not exist!");
		}
	}
}

?>