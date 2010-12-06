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
Welcome to the Google Authenticator Authentication Server Manager Application<br>
<hr><h2>Users</h2>
<table border="1">
<tr><th>Username</th><th>RealName</th><th>Has Password?</th><th>Has Token?</th><th>One Time Key</th><th>Update</th><th>Delete</th></tr>
<?php
$users = $myAC->getUsers();
foreach($users as $user) {
	$username = $user["username"];
	
	if($user["realname"] == "") $realname = "";
	else $realname = $user["realname"];
	
	if($user["haspass"]) $haspass = "Yes <input type=\"password\" name=\"password\"> <a href=\"?action=deletepass&username=$username\">Delete Password</a>";
	else $haspass = "No <input type=\"password\" name=\"password\">";
	
	if($user["hastoken"]) $hastoken = "Yes";
	else $hastoken = "No";
	
	if($user["otk"]!="") $otk = "<a href=\"?action=getotk&username=$username&otk=".$user["otk"]."\">Get</a>";
	else $otk = "Already Claimed";
	
	$delete = "<a href=\"?action=delete&username=$username\">Delete</a>";
	
	echo "<form method=\"post\" action=\"?action=update&username=$username\"><tr><td>$username</td><td><input type=\"text\" name=\"realname\" value=\"$realname\"></td><td>$haspass</td>";
	echo "<td>$hastoken</td><td>$otk</td><td><input type=\"submit\" value=\"Update\"></td><td>$delete</td><tr></form>";
} 
?>
</table><br>
<form method="post" action="?action=createuser">Create User(s) - Enter a comma seperated list of names: <input type="text" name="username" size="120"> <input type="submit" value="Create"></form>

<?php
if(isset($_REQUEST["action"])) if($_REQUEST["action"] == "getotk") {
	$username = $_REQUEST["username"];
	$otk = $_REQUEST["otk"];
	echo "<hr>Got One Time Key for user $username, this one-time-key can only be retrieved once, after that it is deleted<br>";
	echo "<img src=\"?action=getotkimg&username=$username&otk=$otk\" alt=\"one time key error\"><br>";
} 

?>
<hr><h2>Radius Clients</h2>
Not yet implemented

<hr><a href="?action=logout">Logout</a>

<?php 


} else {
	
	
	
	
	
	
	
	
	
	
	// Login page
?>
<h1>GAAS Manager Login</h1>
<?php
if(isset($_REQUEST["message"])) {
	echo "<font color=\"red\">Login Failed</font>";
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
}
?>