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
		case "destroy":
			unlink("/tmp/gaexpage.db");
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
Type (ignored for now): <select name="ttype"><option value="HOTP">HOTP</option><option value="TOTP">TOTP</option></select><br>
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