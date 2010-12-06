<?php 
require_once("../lib/authClient.php");

$myAC = new GAAuthClient();


$loggedin = false;
session_start();

if(isset($_SESSION["loggedin"])) if($_SESSION["loggedin"]) {
	$loggedin = true;
} else {
	$loggedin = false;
}

if(isset($_REQUEST["action"])) {
	error_log("action set: ".$_REQUEST["action"]);
	switch($_REQUEST["action"]) {
		case "login":
			error_log("being login");
			$username = $_REQUEST["username"];
			$token = $_REQUEST["tokencode"];
			
			if($myAC->authUserToken($username, $token)) {
				
				$_SESSION["loggedin"] = true;
				$_SESSION["username"] = $username;
				header("Location: index.php");
			} else {
				error_log("login failed, $username, $token");
				header("Location: index.php?message=loginfail");
			}
			break;
	}
}
?>