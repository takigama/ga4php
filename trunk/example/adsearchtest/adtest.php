<?php

if($argc < 4) {
	echo "usage: ".$argv[0]. " domain user password admingroup\n";
	return 0;
}

$addom = $argv[1];
$adlogin = $argv[2];
$adpass = $argv[3];
$adgroup = $argv[4];

$servers = dns_get_record("_gc._tcp.$addom");
if(count($servers)<1) {
	echo "AD servers cant be found, fail!\n";
}

echo count($servers)." AD servers returned, using ".$servers[0]["target"]."\n";

// we should check all servers, but lets just go with 0 for now
$cnt = ldap_connect($servers[0]["target"], $servers[0]["port"]);
echo "Connected\n";
$bind = ldap_bind($cnt, "$adlogin@$addom", "$adpass");
if($bind) {
	echo "Bind passed\n";
} else {
	echo "Bind Failed\n";
}

$ars = explode(".", $addom);

$tcn = "";
foreach($ars as $val) {
	$tcn .= "DC=$val,";
}

$basecn = preg_replace("/,$/", "", $tcn);

//$sr = ldap_search($cnt, "$basecn", "(&(objectclass=person)(memberof=*Administrators*))");
//$sr = ldap_search($cnt, "$basecn", "(CN=CN=Administrators,CN=Builtin,DC=syd,DC=sententia,DC=com,DC=au)");
$sr = ldap_search($cnt, "$basecn", "(&(objectclass=group)(CN=$adgroup))");
$info = ldap_get_entries($cnt, $sr);

if($info["count"] < 1) {
	echo "Couldn't find a matching group\n";
	return 0;
} else {
	echo "Found a group, ".$info[0]["cn"][0]."\n";
	echo "With a description of, ".$info[0]["description"][0]."\n";
	echo "and a dn of, ".$info[0]["dn"]."\n";
}

//echo "info:\n";
//print_r($info);
echo "Users in this group:\n";
// this is the MS way of dealing with nested groups, much less painful then the possible alternatives
$sr = ldap_search($cnt, "$basecn", "(&(objectCategory=user)(memberof:1.2.840.113556.1.4.1941:=".$info[0]["dn"]."))");
$info = ldap_get_entries($cnt, $sr);
foreach($info as $kpot => $lpot) {
	//print_r($kpot);
	//print_r($lpot);
	if(isset($lpot["samaccountname"])) {
		echo "User: ".$lpot["samaccountname"][0]."\n";
	}
	//echo "User: ".$kpot["samaaccountname"][0]."\n";
	//echo "$kpot, $lpot\n";
	//return 0;
}
?>