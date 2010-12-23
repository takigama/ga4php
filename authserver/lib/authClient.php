<?php

require_once("lib.php");

class GAAuthClient {
	
	// this functiuon will now act as our generic send/recieve client funciton
	// im doing this because im going to move from ipc messaging to a tcp connection
	// shortly and i want to encapsulate the send/receive behaviour
	// things we need to add here are:
	// 1) a way of saying "more data coming" cause getusers wont fit into one message
	// 2) timeouts and locking
	
	// io think this function should now "work" more or less as is
	function sendReceive($message_type, $message) {
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
			// we have a problem jim
			return false;
		}
		
		$xps = explode(":", $recvd);
		
		$component =  unserialize(base64_decode($xps[1]));
		
		if($component["type"] != $message_type) {
			// we have a problem jim
			socket_close($socket);
			return false;
		}
		
		socket_close($socket);
		
		return $component["data"];
	}
	
	function addRadiusClient($clientname, $clientip, $clientsecret, $clientdesc) {
	
		$message["clientname"] = $clientname;
		$message["clientsecret"] = $clientsecret;
		$message["clientip"] = $clientip;
		$message["clientdescription"] = $clientdesc;
		
		return $this->sendReceive(MSG_ADD_RADIUS_CLIENT, $message);
	}

	function deleteRadiusClient($clientname) {
		$message["clientname"] = $clientname;
		
		return $this->sendReceive(MSG_REMOVE_RADIUS_CLIENT, $message);
		
	}
	
	function getRadiusClients() {
		return $this->sendReceive(MSG_GET_RADIUS_CLIENTS, "");
	}
	
	
	function syncUserToken($username, $tokenone, $tokentwo) {
		$message["username"] = $username;
		$message["tokenone"] = $tokenone;
		$message["tokentwo"] = $tokentwo;

		return $this->sendReceive(MSG_SYNC_TOKEN, $messgae);
	}
	
	function getUserTokenType($username) {
		$message["username"] = $username;

		return $this->sendReceive(MSG_GET_TOKEN_TYPE, $message);		
	}
	
	function setUserToken($username, $token) {
		$message["username"] = $username;
		$message["tokenstring"] = $token;
		
		return $this->sendReceive(MSG_GET_USER_TOKEN, $message);		
	}
	
	function setUserPass($username, $password) {
		$message["username"] = $username;
		$message["password"] = $password;
		
		return $this->sendReceive(MSG_SET_USER_PASSWORD, $message);
	}
	
	function getOtkID($username) {
		$message["username"] = $username;

		return $this->sendReceive(MSG_GET_OTK_ID, $message);
	}
	
	function getOtkPng($username, $otk) {
		$message["otk"] = $otk;
		$message["username"] = $username;

		return $this->sendReceive(MSG_GET_OTK_PNG, $message);
	}
	
	function authUserPass($username, $password) {
		$message["username"] = $username;
		$message["password"] = $password;
		
		return $this->sendReceive(MSG_AUTH_USER_PASSWORD, $message);		
	}
	
	function deleteUser($username) {
		$message["username"] = $username;
		
		return $this->sendReceive(MSG_DELETE_USER, $message);
	}
	
	function setUserRealName($username, $realname) {
		$message["username"] = $username;
		$message["realname"] = $realname;
		
		return $this->sendReceive(MSG_SET_USER_REALNAME, $message);		
	}
	
	function getUsers() {
		return $this->sendReceive(MSG_GET_USERS, "");
	}
	
	function authUserToken($username, $passcode) {
		$message["username"] = $username;
		$message["passcode"] = $passcode;
		
		return $this->sendReceive(MSG_AUTH_USER_TOKEN, $message);
	}
	
	function deleteUserToken($username) {
		$message["username"] = $username;
		
		return $this->sendReceive(MSG_DELETE_USER_TOKEN, $message);
	}
	
	function addUser($username, $tokentype="", $hexkey="") {
		$message["username"] = $username;
		if($tokentype!="") $message["tokentype"] = $tokentype;
		if($hexkey!="") $message["hexkey"] = $hexkey;
		
		return $this->sendReceive(MSG_ADD_USER_TOKEN, $message);
	}

	function setUserTokenType($username, $tokentype) {
		$message["username"] = $username;
		$message["tokentype"] = $tokentype;
		
		return $this->sendReceive(MSG_SET_USER_TOKEN_TYPE, $message);
	}
}

?>
