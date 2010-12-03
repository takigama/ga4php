<?php

require_once("lib.php");

class GAAuthClient {
	
	function setUserToken($username, $token) {
		
	}
	
	function setUserPass($username, $password) {
		
	}
	
	function authUserPass($username, $password) {
		
	}
	
	function deleteUser($username) {
		
	}
	
	function setUserRealName($username, $realname) {
		
	}
	
	function authUser($username, $passcode) {
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
		
		
		$message["user"] = $username;
		$message["passcode"] = $passcode;
		
		msg_send($sr_queue, MSG_AUTH_USER, $message, true, true, $msg_err);
		echo "message sent\n";
		
		msg_receive($cl_queue, 0, $msg_type, 16384, $msg);
		echo "message received?\n";
		print_r($msg);
		
		return false;
	}
	
	function addUser($username) {
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
		
		
		$message["username"] = $username;
		
		msg_send($sr_queue, MSG_ADD_USER, $message, true, true, $msg_err);
		echo "message sent\n";
		
		msg_receive($cl_queue, 0, $msg_type, 16384, $msg);
		echo "message received?\n";
		print_r($msg);
		
		return false;
		
	}
}

?>