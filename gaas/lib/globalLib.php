<?php

// the global lib sets alot of global variables, its fairly unexciting
$BASE_DIR = realpath(dirname(__FILE__)."/../../");
global $BASE_DIR;

// the tcp port number we use for comms
$TCP_PORT_NUMBER = 21335;
global $TCP_PORT_NUMBER;




// the messages structure, used to extend gaas if needed
define("MSG_STATUS", 18);
define("MSG_INIT_SERVER", 19);
define("MSG_SET_AD_LOGIN", 20);
define("MSG_SET_CLIENT_GROUP", 21);
define("MSG_SET_ADMIN_GROUP", 22);


// the gaasd call's $MESSAGE[<MSG>]_server() for the server side
// and $MESSAGE[<msg>]_client() for the client side 
$MESSAGES[MSG_STATUS] = "gaasStatus";
$MESSAGES[MSG_INIT_SERVER] = "gaasInitServer";
$MESSAGES[MSG_SET_AD_LOGIN] = "gaasSetADLogin";
$MESSAGES[MSG_SET_CLIENT_GROUP] = "gaasSetClientGroup";
$MESSAGES[MSG_SET_ADMIN_GROUP] = "gaasSetAdminGroup";
global $MESSAGES;







function adTestLogin($domain, $user, $password)
{
	$servers = dns_get_record("_gc._tcp.$domain");
	if(count($servers)<1) {
		echo "AD servers cant be found for $domain, fail!\n";
	}
	
	echo count($servers)." AD servers returned, using ".$servers[0]["target"]."\n";
	
	// we should check all servers, but lets just go with 0 for now
	$cnt = ldap_connect($servers[0]["target"], $servers[0]["port"]);
	echo "Connected\n";
	$bind = ldap_bind($cnt, "$user@$domain", "$password");
	if($bind) {
		echo "login has succeeded\n";
		return true;
	} else {
		echo "login has failed\n";
		return false;
	}	
}

function getADGroups($domain, $user, $password)
{
	$servers = dns_get_record("_gc._tcp.$domain");
	if(count($servers)<1) {
		echo "AD servers cant be found for $domain, fail!\n";
	}
	
	echo count($servers)." AD servers returned, using ".$servers[0]["target"]."\n";
	
	// we should check all servers, but lets just go with 0 for now
	$cnt = ldap_connect($servers[0]["target"], $servers[0]["port"]);
	echo "Connected\n";
	$bind = ldap_bind($cnt, "$user@$domain", "$password");
	if(!$bind) {
		echo "login has failed\n";
		return false;
	}	

	$ars = explode(".", $addom);
	
	$tcn = "";
	foreach($ars as $val) {
		$tcn .= "DC=$val,";
	}
	
	$basecn = preg_replace("/,$/", "", $tcn);
	
	$sr = ldap_search($cnt, "$basecn", "(objectclass=group)");
	$info = ldap_get_entries($cnt, $sr);
	
	if($info["count"] < 1) {
		echo "Couldn't find a matching group\n";
		return 0;
	} else {
		echo "Found a group, ".$info[0]["cn"][0]."\n";
		echo "With a description of, ".$info[0]["description"][0]."\n";
		echo "and a dn of, ".$info[0]["dn"]."\n";
	}
	
	return $info;
}

function generateRandomString($len)
{
	$str = "";
	$strpos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	for($i=0; $i<$len; $i++) {
		$str .= $strpos[rand(0, strlen($strpos)-1)];
	}
	
	return $str;
}

function generateHexString($len)
{
	$str = "";
	$strpos = "0123456789ABCDEF";
	
	for($i=0; $i<$len; $i++) {
		$str .= $strpos[rand(0, strlen($strpos)-1)];
	}
	
	return $str;
}


?>