<?php
/*
 * This is the web component of the GA4PHP radius server. This web app should be able to configure freeradius and itself.
 * 
 * This app will try to do the following:
 * 1) initialise tokens
 * 2) pull accounts from some backend (such as AD)
 * 3) allow users to self-enroll.
 * 
 * I wonder if we can store data in the backend database itself? that would be interesting
 * then user admin would be less disconnected. I.e. if a user was deleted from AD, their token
 * data should disappear with them.
 */
require_once("admin_actions.php");

// the logged in component
if($loggedin) {
?>
<h1>GAAS Manager</h1>
Welcome to the Google Authenticator Authentication Server Manager Application - <a href="?showhelp">Show Help</a><br>

<?php 
if(isset($_REQUEST["message"])) {
	echo "<font color=\"green\">".$_REQUEST["message"]."</font>";
} 
if(isset($_REQUEST["error"])) {
	echo "<font color=\"red\">".$_REQUEST["error"]."</font>";
} 


if(isset($_REQUEST["showhelp"])) {
	echo "<hr>";
	?>
On this page, you create users and manage their tokens and passwords. A few notes,<br>
<li> Passwords are *ONLY* for this page, if you assign a password to a user they can login here
and edit anyone, including you
<li> OTK/One-Time-Keys are the QRcode for provisioning a GA token, it can only be viewed once
and once viewed is deleted. If you need a new one, you need to re-create a key.
<li> TOTP tokens are time based tokens that change every 30 seconds, HOTP tokens are event tokens
that change everytime they are used or generated
<li> In the OTK, the "Get (User URL)" link is a link you can send to a user to retrieve their key
	<?php 
} 

if(isset($_REQUEST["edituser"])) {
	$username = $_REQUEST["edituser"];
?>

<h2>Editing user, <?php echo $username ?></h2><br>
<form method="post" action="?action=edituser&username=<?php echo $username ?>">
<input type="hidden" name="original_real" value="<?php echo $_REQUEST["realname"] ?>">
<table>
<tr><td>Real Name:</td><td><input type="text" name="realname" value="<?php echo $_REQUEST["realname"] ?>"></td></tr>
<tr><td>Password:</td><td><input type="password" name="password"></td></tr>
<tr><td>Confirm Password:</td><td><input type="password" name="password_conf"></td></tr>
</table>
<input type="submit" value="Update">
</form>
<?php
if($myAC->getUserTokenType($username)=="HOTP") {
?> 
<form method="post" action="?action=synctoken&username=<?php echo $username?>">
<h3>Resync Tokens</h3>
<table>
<tr><td>Token One</td><td><input type="text" name="tokenone"></td></tr>
<tr><td>Token Two</td><td><input type="text" name="tokentwo"></td></tr>
</table>
<input type="submit" value="Sync">
</form>
<?php
}
?> 

<form method="post" action="?action=customtoken&username=<?php echo $username ?>">
<h3>Custom Tokens</h3><br>
For assiging in a user-created or hardware tokens.<br>
If you assign a token this way, any previous token is removed and forever gone.<br>
Token Key (hex) <input type="text" name="tokenkey"><br>
Token Type 
<select name="tokentype">
<option value="HOTP">HOTP</option>
<option value="TOTP">TOTP</option>
</select><br>
<input type="submit" value="Set">
</form>
<?php
} else if(isset($_REQUEST["editclient"])) {
?>
this page is for editing radius clients, it doesnt exist yet.. What you need to do is delete the client and re-add it... go <a href="admin.php">back</a>
</html>
<?php 
} else {
?>
<hr><h2>Users</h2>
<table border="1">
<tr><th>Username</th><th>RealName</th><th>Has Password?</th><th>Has Token?</th><th>One Time Key</th><th>Delete</th></tr>
<?php
$users = $myAC->getUsers();
foreach($users as $user) {
	$username = $user["username"];
	
	if($user["realname"] == "") $realname = "";
	else $realname = $user["realname"];
	
	if($user["haspass"]) $haspass = "Yes <a href=\"?action=deletepass&username=$username\">Delete Password</a>";
	else $haspass = "No";
	
	if($user["otk"]=="deleted") $otk = "OTK Was Not Picked Up";
	else if($user["otk"]!="") $otk = "<a href=\"?action=getotk&username=$username&otk=".$user["otk"]."\">Get (admin)</a> <a href=\"index.php?gettoken&username=$username&otkid=".$user["otk"]."\">Get (User URL)</a>";
	else $otk = "Already Claimed";
	
	if($user["hastoken"]) $hastoken = "Yes <a href=\"?action=recreatehotptoken&username=$username\">Re-Create (HOTP)</a> <a href=\"?action=recreatetotptoken&username=$username\">Re-Create (TOTP)</a> <a href=\"?action=deletetoken&username=$username\">Delete</a>";
	else {
		$hastoken = "No <a href=\"?action=recreatehotptoken&username=$username\">Create (HOTP)</a> <a href=\"?action=recreatetotptoken&username=$username\">Create (TOTP)</a>";
		if($user["otk"]!="deleted")$otk = "No Token Exists";
	}
	
	$delete = "<a href=\"?action=delete&username=$username\">Delete</a>";
	
	echo "<tr>";
	echo "<td><a href=\"?edituser=$username&realname=$realname\">$username</a></td><td>$realname</td><td>$haspass</td>";
	echo "<td>$hastoken</td><td>$otk</td><td>$delete</td><tr></form>";
}
?>
</table><br>
<form method="post" action="?action=createuser">Create User(s) - Enter a comma seperated list of usernames: <input type="text" name="username" size="120"> <input type="submit" value="Create"></form>

<?php


if(isset($_REQUEST["action"])) if($_REQUEST["action"] == "getotk") {
	$username = $_REQUEST["username"];
	$otk = $_REQUEST["otk"];
	echo "<hr>Got One Time Key for user $username, this one-time-key can only be retrieved once, after that it is deleted<br>";
	echo "<img src=\"?action=getotkimg&username=$username&otk=$otk\" alt=\"one time key error\"><br>";
} 

?>
<hr><h2>Radius Clients</h2>
<table border="1">
<tr><th>Name</th><th>IP Address</th><th>Description</th><th>Delete</th></tr>
<?php
$msg = $myAC->getRadiusClients();
foreach($msg as $client) {
	if($client["desc"]=="")	$desc = "no description set";
	else $desc = $client["desc"];
	$clientname = $client["name"];
	$clientip = $client["ip"];
	echo "<tr><td><a href=\"?editclient=$clientname\">$clientname</a></td><td>$clientip</td><td>$desc</td><td><a href=\"?action=deleteradclient&clientname=$clientname\">Delete</a></td></tr>";
}
?>
</table>
<br>
<h3>Add a Radius Client</h3>
<form method="post" action="?action=addradclient">
<table>
<tr><td>Client Name</td><td><input type="text" name="clientname"></td></tr>
<tr><td>Client IP</td><td><input type="text" name="clientip"></td></tr>
<tr><td>Client Secret</td><td><input type="text" name="clientsecret"></td></tr>
<tr><td>Client Description</td><td><input type="text" name="clientdesc"></td></tr>
</table>
<input type="submit" name="go" value="add">
</form>
<hr><a href="?action=logout">Logout</a> <a href="admin.php">Home</a>

<?php 
} // edit users

} else {
	
	
	
	
	
	
	
	
	
	
	// Login page
?>
<h1>GAAS Manager Login</h1>
<?php
if(isset($_REQUEST["message"])) {
	echo "<font color=\"green\">".$_REQUEST["message"]."</font>";
} 
if(isset($_REQUEST["error"])) {
	echo "<font color=\"red\">".$_REQUEST["error"]."</font>";
} 
?>
<form method="post" action="?action=login">
<table>
<tr><td>Username</td><td><input type="text" name="username"></td></tr>
<tr><td>Password</td><td><input type="password" name="password"></td></tr>
<tr><td><input type="submit" value="Go"></td></tr>
</table>
</form>
<?php
} //loggedin
?>