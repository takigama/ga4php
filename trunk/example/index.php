<?php

require_once("../lib/lib.php");

$ga = new GoogleAuthenticator("/tmp/gaexpage.db");
?>
<html>
<h1>Example Page for GA4PHP</h1>

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
				$url = $ga->setupUser($username);
				echo "QRCode for user \"$username\" is <img src=\"http://chart.apis.google.com/chart?cht=qr&chl=$url&chs=120x120\"> or type in $url (actually its just the code on the end of the url)";
			}
			echo "<hr>";
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
		default:
			// do nothing
	}
}

?>

Create a User:
<form method="post" action="index.php?action=createuser">
Username: <input type="text" name="username"><br>
Type (ignored for now): <select name="ttype"><option value="HOTP">HOTP</option><option value="TOTP">TOTP</option></select><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
<form method="post" action="index.php?action=authuser">
Username: <input type="text" name="username"><br>
Code: <input type="text" name="code"><br>
<input type="submit" name="go" value="go"><br>
</form>
<hr>
</html>