<?php 
if($argc < 4) {
	echo "usage: ".$argv[0]. " domain user password usertocheck\n";
	return 0;
}

$addom = $argv[1];
$adlogin = $argv[2];
$adpass = $argv[3];
$usertocheck = $argv[4];

$servers = dns_get_record("_gc._tcp.$addom");
if(count($servers)<1) {
	echo "AD servers cant be found, fail!\n";
}

echo count($servers)." AD servers returned, using ".$servers[0]["target"]."\n";

// we should check all servers, but lets just go with 0 for now
$cnt = ldap_connect($servers[0]["target"], $servers[0]["port"]);
ldap_set_option($cnt, LDAP_OPT_PROTOCOL_VERSION, 3);
echo "Connected\n";
$bind = ldap_bind($cnt, "$adlogin", "$adpass");
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

// first, find the dn for our user
$sr = ldap_search($cnt, "$basecn", "(&(objectclass=user)(samaccountname=$usertocheck))");
$info = ldap_get_entries($cnt, $sr);
//print_r($info);
$usercn=$info[0]["dn"];


//exit(0);

$basecn = preg_replace("/,$/", "", $tcn);
$sr = ldap_search($cnt, "$basecn", "(&(objectCategory=group)(member:1.2.840.113556.1.4.1941:=$usercn))");
$fil = "(&(objectCategory=group)(member:1.2.840.113556.1.4.1941:=$usercn))";
$info = ldap_get_entries($cnt, $sr);
echo "groups for this user, $fil\n";
//print_r($info);
foreach($info as $kpot => $lpot) {
	//print_r($kpot);
	//print_r($lpot);
	if(isset($lpot["cn"])) {
		echo "Group: ".$lpot["cn"][0]."\n";
	}
	//echo "User: ".$kpot["samaaccountname"][0]."\n";
	//echo "$kpot, $lpot\n";
	//return 0;
}




?>