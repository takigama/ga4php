<?php

require_once("lib.php");

class GAAuthClient {
	
	function setUserToken($username, $token) {
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
		
	}
	
	function setUserPass($username, $password) {
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
		
	}
	
	function authUserPass($username, $password) {
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
		
	}
	
	function deleteUser($username) {
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
		
	}
	
	function setUserRealName($username, $realname) {
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
		
	}
	
	function authUserToken($username, $passcode) {
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
		
		return $msg;
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
		
		return $msg;
		
	}

	function setTokenType($username, $tokentype) {
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
		
		
	}
}

?>