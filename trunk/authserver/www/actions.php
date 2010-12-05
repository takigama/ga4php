<?php 
require_once("../lib/authClient.php");

$myAC = new GAAuthClient();

session_start();

if(isset($_SESSION["loggedin"])) if($_SESSION["loggedin"]) $loggedin = true;
else $loggedin = false;

if(isset($_REQUEST["action"])) {
	switch($_REQUEST["action"]) {
		case "login":
			$username = $_REQUEST["username"];
			$password = $_REQUEST["password"];
			
			if($myAC->authUserPass($username, $password)) {
				$_SESSION["loggedin"] = true;
				$_SESSION["username"] = $username;
				header("Location: index.php");
			} else {
				header("Location: index.php?message=loginfail");
			}
			
			exit(0);
			break;
		case "logout":
			$_SESSION["loggedin"] = false;
			$_SESSION["username"] = "";
			header("Location: index.php");
			exit(0);
			break;
		case "createuser":
			$username = $_REQUEST["username"];
			$myAC->addUser($username);
			header("Location: index.php");
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
		case "getotk":
			$username = $_REQUEST["username"];
			$otk = $myAC->getOtkPng($username);
			header("Content-type: image/png");
			echo $otk;
			exit(0);
			break;
	}
}
?>