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
		$be = confGetVal("backend");
		if($be == "AD") {
			$dom = confGetVal("ad.domain");
			$user = confGetVal("ad.user");
			$client = confGetVal("ad.clientdef");
			$admin = confGetVal("ad.admindef");
			$return .= " - AD integrated to $dom, GAASD Username: $user, Clients Group: $client, Admins Group: $admin";		
		} else {
			$return .= " - internal database";
		}
		
	}
	
	
	
	return $return;
}


function gaasInitServer_server($msg)
{
	global $initState, $backEnd;
	
	error_log("Init server called\n");
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
	echo "initstate is $initState";
	if($initState) {
		echo "true\n";
	} else {
		echo "false\n";
	}
	if($initState) {
		error_log("init server called when server already init'd\n");
		return false;
	}
	
	if($msg["backend"] == "AD") {
		$backEnd = "AD";
		// attempt connect to AD, verify creds
		$addom = $msg["domain"];
		$adlogin = $msg["user"];
		$adpass = $msg["pass"];
		$adclientdef = $msg["clientdef"];
		$adadmindef = $msg["admindef"];
		
		// now wee test our logins...
		// first look up the domain name stuff
		$servers = dns_get_record("_gc._tcp.$addom");
		if(count($servers)<1) {
			echo "AD servers cant be found, fail!\n";
		}
		
		// we should check all servers, but lets just go with 0 for now
		$res =  adTestLogin($addom, $adlogin, $adpass);
		if(!$res) {
			return false;
		}
		
		
		// then
		createDB();
		confSetVal("ad.domain", $addom);
		confSetVal("ad.user", $adlogin);
		confSetVal("ad.pass", $adpass);
		confSetVal("ad.encryptionkey", generateHexString(32));
		confSetVal("ad.clientdef", $adclientdef);
		confSetVal("ad.admindef", $adadmindef);
		confSetVal("backend", "AD");
		
		$initState = true;
		$backEnd = "AD";
		
		// and that should be it... i think cept im in a forked erg.. lets assume it works, need pain i do not.
		return true;
	} else if($msg["backend"] == "IN") {
		// this ones simpler
		$backEnd = "IN";
		createDB();
		
		// create the user in the db
		$username = $msg["user"];
		$password = $msg["pass"];
		
		$myga = new gaasdGA();
		$myga->setUser($username);
		
		if($password == "") $pass = "";
		else $pass = hash('sha512', $password);
		
		$db = getDB();
		$db->query($sql = "update users set users_password='$pass' where users_username='$username'");
		
		$initState = "running";
		return true;
	} else {
		return false;
	}
}


function gaasSetADLogin_server($msg)
{
	global $initState, $backEnd;
	
	if($initState != "running") {
		return "not in running init state";
	}
	
	if($backEnd != "AD") {
		return "not setup as AD client";
	}
	
	$addom = $msg["domain"];
	$adlogin = $msg["user"];
	$adpass = $msg["pass"];
	
	$res = adTestLogin($addmo, $adlogin, $adpass);
	if($res != 0) {
		return "not able to connect to AD with given cred's";
	}
	
	confSetVal("ad.domain", $addom);
	confSetVal("ad.user", $adlogin);
	confSetVal("ad.pass", $adpass);
	
	return true;
	
}

function gaasSetAdminGroup_server($msg)
{
	if(confGetVal("backend") == "AD") {
		confSetVal("ad.admindef", $msg["admingroup"]);
	} else return false;
	
	return true;
}

function gaasSetClientGroup_server($msg)
{
	if(confGetVal("backend") == "AD") {
		confSetVal("ad.clientdef", $msg["clientgroup"]);
	} else return false;
	
	return true;
}

function gaasProvisionUser_server($msg)
{
	
	// function userInGroup($user, $domain, $adlogin, $adpass, $group)
	if(confGetVal("backend") == "AD") {
		userInGroup($msg["username"], confGetVal("ad.domain"), confGetVal("ad.user", $adlogin), confGetVal("ad.pass"), confGetVal("ad.clientdef"));
	} else {
		// internal db
	}
	
	
	return true;
}

?>