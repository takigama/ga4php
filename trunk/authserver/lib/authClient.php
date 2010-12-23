<?php

require_once("lib.php");

class GAAuthClient {
	
	// this functiuon will now act as our generic send/recieve client funciton
	// im doing this because im going to move from ipc messaging to a tcp connection
	// shortly and i want to encapsulate the send/receive behaviour
	function sendReceive($message_type, $message) {
		global $MSG_QUEUE_KEY_ID_SERVER, $MSG_QUEUE_KEY_ID_CLIENT;
		
		
		if(!msg_queue_exists($MSG_QUEUE_KEY_ID_SERVER)) {
			return false;
		}

		if(!msg_queue_exists($MSG_QUEUE_KEY_ID_CLIENT)) {
			return false;
		}
		// TODO we need to setup a client queue sem lock here
		
		$cl_queue = msg_get_queue($MSG_QUEUE_KEY_ID_CLIENT);
		$sr_queue = msg_get_queue($MSG_QUEUE_KEY_ID_SERVER);
		
		msg_send($sr_queue, $message_type, $message, true, true, $msg_err);
		msg_receive($cl_queue, 0, $msg_type, 131072, $msg);
		
		return $msg;
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
