<?php

require_once("../lib/lib.php");
require_once("tokenstore.php");

$ga = new GoogleAuthenticator("myGetData", "mySetData");

if(isset($_REQUEST["action"])) {
	switch($_REQUEST["action"]) {
		case "destroy":
			unlink("/tmp/gadata.sqlite");
			break;
	}
}

// create/connect a db
global $dbobject;
$dbobject = false;
if(file_exists("/tmp/gadata.sqlite")) {
	try {
		$dbobject = new PDO("sqlite:/tmp/gadata.sqlite");
	} catch(PDOException $exep) {
		error_log("execpt on db open");
	}
} else {
	try {
		$dbobject = new PDO("sqlite:/tmp/gadata.sqlite");
	} catch(PDOException $exep) {
		error_log("execpt on db open");
	}
	$sql = 'CREATE TABLE "users" ("users_id" INTEGER PRIMARY KEY AUTOINCREMENT,"users_username" TEXT,"users_tokentype" TEXT,"users_tokenkey" TEXT,"users_tokencounter" integer);';
	$dbobject->query($sql);
}


?>
<html>
<h1>Example Page for GA4PHP</h1>
<a href="index.php">home</a><br>

<?php
if(isset($_REQUEST["action"])) {
	switch($_REQUEST["action"]) {
		case "createuser":
			$username = $_REQUEST["username"];
			$pr = preg_match('/^[a-zA-Z0-9@\.]+$/',"$username");
			echo "<hr>";
			if(strlen($username)<3) {
				echo "<font color=\"red\">Sorry, username must be at least 3 chars</font>";
			} else if($pr<1) {
				echo "<font color=\"red\">Sorry, username can only contain a-z, A-Z, 0-9 @ and .</font>";
			} else {
				//$key = $ga->setUser($username, "", $ttype);
				//$keyinhex = $ga->helperb322hex($key);
				//$url = urlencode($ga->createURL($username, $key, $ttype));
				//echo "QRCode for user \"$username\" is <img src=\"http://chart.apis.google.com/chart?cht=qr&chl=$url&chs=120x120\"> or type in $key (google authenticator) or $keyinhex (for most other otp's)";
				$sql = "insert into users values (NULL, '$username', 'TOTP', 'ASDF', '0')";
				$dbobject->query($sql);
			}
			echo "<hr>";
			break;
		case "provisiontoken":
			$username = $_REQUEST["username"];
			$ttype = $_REQUEST["ttype"];
			$key = $ga->setUser($username, "", $ttype);
			$keyinhex = $ga->helperb322hex($key);
			$url = urlencode($ga->createURL($username, $key, $ttype));
			echo "QRCode for user \"$username\" is <img src=\"http://chart.apis.google.com/chart?cht=qr&chl=$url&chs=420x420\"> or type in $key (google authenticator) or $keyinhex (for most other otp's), $ttype";
			break;
		case "authuser":
			$username = $_REQUEST["username"];
			$code = $_REQUEST["code"];
			if($ga->authenticateUser($username, $code)) {
				echo "<font color=\"green\">Passed!</font>";
			} else {
				echo "<font color=\"red\">Failed!</font>";
			}
			break;
		case "resync":
			$username = $_REQUEST["username"];
			$code1 = $_REQUEST["code1"];
			$code2 = $_REQUEST["code2"];
			if($ga->resyncCode($username, $code1, $code2)) {
				echo "<font color=\"green\">Passed!</font>";
			} else {
				echo "<font color=\"red\">Failed!</font>";
			}
			break;
		default:
			// do nothing
	}
}

?>
<h2>Destroy the DB</h2>
<a href="index.php?action=destroy">This is UNDOABLE - but this is a test system, so you dont care</a>
<h2>Create a User:</h2>
<form method="post" action="index.php?action=createuser">
Username: <input type="text" name="username"><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
<h2>Provision Token</h2>
<form method="post" action="index.php?action=provisiontoken">
Username: <select name="username">
<?php
$res = $ga->getUserList();
foreach($res as $row) {
	echo "<option value=\"".$row."\">".$row."</option>";
}
?>
</select><br>
Type: <select name="ttype"><option value="HOTP">HOTP</option><option value="TOTP">TOTP</option></select><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
<h2>Test Token</h2>
<form method="post" action="index.php?action=authuser">
Username: <select name="username">
<?php
$res = $ga->getUserList();
foreach($res as $row) {
	echo "<option value=\"".$row."\">".$row."</option>";
}
?>
</select><br>
Code: <input type="text" name="code"><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
<h2>Resync Code (only valid for HOTP codes)</h2>
<form method="post" action="index.php?action=resync">
Username: <select name="username">
<?php
$res = $ga->getUserList();
foreach($res as $row) {
	echo "<option value=\"".$row."\">".$row."</option>";
}
?>
</select><br>
Code one: <input type="text" name="code1"><br>
Code two: <input type="text" name="code2"><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
</html>