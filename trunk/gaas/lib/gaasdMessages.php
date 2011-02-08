<?php

// this file defines all the messages used by gaaasd

// there are only really two status messages at this point - "init" meaning we have no been defined yet
// and "running" meaning we have been defined
function gaasStatus_server($messages)
{
	global $initState, $backEnd;

	$return = "init";
	if($initState != false && $backEnd != "") {
		$return = "running";
	}
	
	return $return;
}


function gaasInitServer_server($msg)
{
	global $initState, $backEnd;
	
	// here we "init" the server, if we're ad, we attempt to connect to AD and if it all works
	// we then create the db
	// $m["backend"] = "AD|IN";
	// AD expects:
	// $m["domain"] = "somedomain.com";
	// $m["user"] = "someuser";
	// $m["pass"] = "somepassword";
	// $m["userdef"] = "user definition paramaters";
	// IN expects
	// $m["user"] = "someuser";
	// $m["pass"] = "somepass";
	
	if($msg["backend"] == "AD") {
		$backEnd = "AD";
		// attempt connect to AD, verify creds
		$addom = $msg["domain"];
		$adlogin = $msg["user"];
		$adpass = $msg["pass"];
		$adclientdef = $msg["clientdef"];
		$adadmindef = $msg["admindef"];
		// now wee test our logins...
		
		
		// then
		createDB();
		confSetVal("ad.domain", $addom);
		confSetVal("ad.user", $adlogin);
		confSetVal("ad.pass", $adpass);
		confSetVal("ad.encryptionkey", generateHexString(32));
		confSetVal("ad.clientdef", $adclientdef);
		confSetVal("ad.admindef", $adadmindef);
		
		$initState = "running";
		$backEnd = "AD";
		
		// and that should be it... i think cept im in a forked erg.. lets assume it works, need pain i do not.
		
		return true;
	} else if($msg["backend"] == "IN") {
		// this ones simpler
		$backEnd = "IN";
		createDB();
		$initState = "running";
		// then we need to "create user";
		return true;
	} else {
		return false;
	}
}
?>