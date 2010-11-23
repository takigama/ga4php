<?php 

// just trying to test how extensionAttributes work in AD. they seem to be exactly what we're looking for in terms of a place to plonk our data
$ds = ldap_connect("------", 389);
if($ds) {
	$r = ldap_bind($ds, "-----", "-----");
	if($r) {
		echo "r is r\n";
	} else {
		echo "r is not r\n";
	}
	
	//$sr = ldap_search($ds, "CN=administrator, CN=Users, ----", "objectclass=*");
	
	//if($sr) {
//		echo "sr is sr\n";
	//}
	
	//$info = ldap_get_entries($ds, $sr);
	$info["extensionattribute2"] = "-----";
	
	ldap_modify($ds, "CN=administrator, CN=Users, dc=safeneter, dc=int", $info);
	
	//print_r($info);
}

?>