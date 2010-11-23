<?php 
/*
 * This example shows how you might store user data directly into AD.
 * AD has several attributes you can use for storing your own data, and
 * thats what we use
 * 
 * This is only the beginning code, 
 */

// set these
$host = ""; // for eg "1.2.3.4"
$binduser = ""; // for eg "administrator"
$bindpass = ""; // for eg "password"
$basecn = ""; // for eg "CN=users, DC=google, dc=com"

//require our GoogleAuthenticator sub classed class
require_once("extend.php");
$myga = new myGA();

// this is here so i can keep my atributes somewhere in the tree and not have them float around on git/svn
if(file_exists("../../../../.dontappearingitandsvn.php")) require_once("../../../../.dontappearingitandsvn.php");

$error = false;

// first, lets bind our AD with out management creds
error_log("host is $host");
$dsconnect = ldap_connect("$host", 389);

// we mark it global so we can get it in our class
global $dsconnect, $host, $binduser, $bindpass, $basecn;

if(!$dsconnect) {
	$error = true;
	$errorText = "Can't Connect to AD";
}
$ldapbind = ldap_bind($dsconnect, "$binduser", "$bindpass");
?>
<html>
<H1>Welcome to GA4PHP Talking to Active Directory</H1>

<?php
if($error) {
	echo "<font color=\"red\">$errorText</font><br>";
}
?>

Our user list within AD:
<table border="1">
<tr><th>Name</th><th>Login Name</th></tr>
<?php 
	$sr = ldap_search($dsconnect, "$basecn", "objectclass=user");
	$info = ldap_get_entries($dsconnect, $sr);
	//$info["extensionattribute2"] = "-----";
	
	
	//print_r($info);
	$i = 0;
	foreach($info as $key => $val) {
		//echo "$key is ".$val["distinguishedname"][0]."\n";
		if($val["distinguishedname"][0] != "") {
			$user[$i]["dn"] = $val["distinguishedname"][0];
			$user[$i]["acn"] = $val["samaccountname"][0];
			$user[$i]["cn"] = $val["cn"][0];
		}

		$i ++;
		//return 0;
	}
	
	foreach($user as $value) {
		$cn = $value["cn"];
		$un = $value["acn"];
		echo "<tr><td>$cn</td><td>$un</td></tr>";
	}
?>



</table>
testing administrator<br>
<?php
if($myga->hasToken("administrator")) {
	echo "administrator has a token<br>";
} else {
	echo "administrator has no token, setting one<br>";
	$myga->setUser("administrator");
}
?>
</html>