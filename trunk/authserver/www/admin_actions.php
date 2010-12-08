<?php 
require_once("../lib/authClient.php");

$myAC = new GAAuthClient();

session_start();

if(isset($_SESSION["loggedin"])) if($_SESSION["loggedin"]) $loggedin = true;
else $loggedin = false;

if(isset($_REQUEST["action"])) {
	switch($_REQUEST["action"]) {
		case "deleteradclient":
			$clientname = $_REQUEST["clientname"];
			$ret = $myAC->deleteRadiusClient($clientname);
			header("Location: ?message=".urlencode("Client Deleted"));
			exit(0);
			break;
		case "addradclient":
			$clientname = $_REQUEST["clientname"];
			$clientip = $_REQUEST["clientip"];
			$clientsecret = $_REQUEST["clientsecret"];
			$clientdesc = $_REQUEST["clientdesc"];
			$ret = $myAC->addRadiusClient($clientname, $clientip, $clientsecret, $clientdesc);
			if($ret === true) {
				header("Location: ?message=".urlencode("Client Added"));
			} else if ($ret == "name") {
				header("Location: ?error=".urlencode("Client Name Already Existed"));
			} else if ($ret == "ip") {
				header("Location: ?error=".urlencode("Client IP Already Existed"));
			}
			exit(0);
			break;
		case "customtoken":
			$ttype = $_REQUEST["tokentype"];
			$tkey = $_REQUEST["tokenkey"];
			$username = $_REQUEST["username"];
			$ret1 = $myAC->setUserTokenType($username, $ttype);
			$ret2 = $myAC->setUserToken($username, $tkey);
			error_log("got, $ret1, $ret2...");
			break;
		case "synctoken":
			$username = $_REQUEST["username"];
			$tokenone = $_REQUEST["tokenone"];
			$tokentwo = $_REQUEST["tokentwo"];
			$retval = $myAC->syncUserToken($username, $tokenone, $tokentwo);
			error_log("retval: $retval");
			if($retval) {
				header("Location: ?message=".urlencode("token synced"));
				exit(0);
			} else {
				header("Location: ?error=".urlencode("token not synced"));
				exit(0);
			}
			
			break;
		case "recreatehotptoken":
			$username = $_REQUEST["username"];
			$myAC->addUser($username, "HOTP");
			header("Location: ?message=".urlencode("seemed to work?"));
			break;
		case "recreatetotptoken":
			$username = $_REQUEST["username"];
			$myAC->addUser($username, "TOTP");
			header("Location: ?message=".urlencode("seemed to work?"));
			break;
		case "deletetoken":
			$username = $_REQUEST["username"];
			$myAC->deleteUserToken($username);
			header("Location: ?message=".urlencode("seemed to work?"));
			break;
		case "edituser":
			$username = $_REQUEST["username"];
			if($_REQUEST["original_real"] != $_REQUEST["realname"]) {
				$myAC->setUserRealName($username, $_REQUEST["realname"]);
			}
			if($_REQUEST["password"] != "") {
				if($_REQUEST["password"]!=$_REQUEST["password_conf"]) {
					header("Location: ?message=confirmfalse");
				} else {
					$myAC->setUserPass($username, $_REQUEST["password"]);
				}
			}
			break;
		case "login":
			$username = $_REQUEST["username"];
			$password = $_REQUEST["password"];
			
			if($myAC->authUserPass($username, $password)) {
				$_SESSION["loggedin"] = true;
				$_SESSION["username"] = $username;
				header("Location: admin.php");
			} else {
				header("Location: admin.php?error=".urlencode("Login Failed"));
			}
			
			exit(0);
			break;
		case "logout":
			$_SESSION["loggedin"] = false;
			$_SESSION["username"] = "";
			header("Location: admin.php");
			exit(0);
			break;
		case "createuser":
			$username = $_REQUEST["username"];
			$users = explode(",",$username);
			foreach($users as $user) {
				$user = trim($user);
				error_log("createing, $user\n");
				if($user != "" && strlen($user)>2) $myAC->addUser($user);
			}
			header("Location: admin.php");
			exit(0);
			break;
		case "update":
			error_log("would update");
			$err = print_r($_REQUEST, true);
			error_log("req: $err\n");
			$username = $_REQUEST["username"];
			if($_REQUEST["realname"]!="") {
				$myAC->setUserRealName($username, $_REQUEST["realname"]);
			}
			if($_REQUEST["password"]!= "") {
				$myAC->setUserPass($username, $_REQUEST["password"]);
			}
			break;
		case "delete":
			$username = $_REQUEST["username"];
			$myAC->deleteUser($username);
			break;
		case "deletepass":
			$username = $_REQUEST["username"];
			$myAC->setUserPass($username, "");
			break;
		case "getotkimg":
			$otk = $_REQUEST["otk"];
			$username = $_REQUEST["username"];
			error_log("requesting otk, $otk");
			$otk_img = $myAC->getOtkPng($username,$otk);
			header("Content-type: image/png");
			echo $otk_img;
			exit(0);
			break;
	}
}
?>