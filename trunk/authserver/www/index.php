<?php 

require_once("user_actions.php");

?>
<html>
<h1>Welcome to the GAAS User Self Admin Site</h1>
<?php
if(isset($_REQUEST["message"])) {
	echo "<font color=\"red\"><i>Login Failure</i></font>";
} 

if(!$loggedin) {
?>
<form method="post" action="?action=login">
Username: <input type="text" name="username"><br>
Token Code: <input type="text" name="tokencode"><br>
<input type="submit" value="Login">
</form>
</html>
<?php
	exit(0); 
} else {
?>

Hi user
</html>

<?php 
}
?>

