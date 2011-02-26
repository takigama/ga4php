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
		confSetVal("defaulttokentype", "TOTP");
		
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
	echo "in provision user\n";
	print_r($msg);
	$dttype = confGetVal("defaulttokentype");
	if($dttype != "HOTP" && $dttype != "TOTP") {
		echo "default token type not set, setting to TOTP\n";
		confSetVal("defaulttokentype", "TOTP");
		$dttype = "TOTP";
	}
	if($msg["tokentype"] == "") {
		$ttype = confGetVal("defaulttokentype");
	} else {
		$ttype = $msg["tokentype"];
	}
	if($ttype != "HOTP" && $ttype != "TOTP") {
		echo "using default token type, $dttype because user entered value of $ttype doesnt make sense\n";
		$ttype = $dttype;
	}
	$tkey = $msg["tokenkey"];
	if(confGetVal("backend") == "AD") {
		if(userInGroup($msg["username"], confGetVal("ad.domain"), confGetVal("ad.user"), confGetVal("ad.pass"), confGetVal("ad.clientdef"))) {
			$myga = new gaasdGA();
			
			// TODO - figure out how to deal with the token origin - i.e. software/hardware
			if($msg["origin"] == "hardware") {
				echo "want a hardware token, but i dont know how to do this yet\n";
			} else {
				echo "using software token\n";
				$myga->setUser($msg["username"], $ttype, "", $tkey);
			}
		} else {
			echo "User not in client group\n";
		}
	} else {
		// internal db
	}
	
	
	return true;
}

// TODO error check/ value check
function gaasAddHardwareToken_server($msg)
{
	$tokenid = $msg["tokenid"];
	$tokenkey = $msg["tokenkey"];
	$tokentype = strtoupper($msg["tokentype"]);
	
	if($tokentype != "HOTP" && $tokentype != "TOTP") {
		echo "invalid token type from hardware entry\n";
		return false;
	}
	//"hardwaretokens" ("tok_id" INTEGER PRIMARY KEY AUTOINCREMENT,"tok_name" TEXT, "tok_key" TEXT, "tok_type" TEXT);';
	print_r($msg);
	$db = getDB();
	$sql = "insert into hardwaretokens values (NULL, '$tokenid', '$tokenkey', '$tokentype')";
	echo "Sql is $sql\n";
	$ret = $db->query($sql);
	if($ret) return true;
	else return false;
	
}


function gaasGetHardwareTokens_server($msg)
{
	$db = getDB();
	
	$sql = "select tok_name, tok_type from hardwaretokens";
	$ret = $db->query($sql);
	
	$toks = "";
	$i = 0;
	foreach($ret as $row) {
		$toks[$i]["name"] = $row["tok_name"];
		$toks[$i]["type"] = $row["tok_type"];
		$i++;
	}
	
	return $toks;
}


function gaasAssignToken_server($msg)
{
	if(!isset($msg["tokenid"])) return false;
	
	$tokenid = $msg["tokenid"];
	
	// now, we check the username is in the client gorup
	if(confGetVal("backend") == "AD") {
		if(userInGroup($msg["username"], confGetVal("ad.domain"), confGetVal("ad.user"), confGetVal("ad.pass"), confGetVal("ad.clientdef"))) {
			$myga = new gaasdGA();
			
			$sql = "select * from hardwaretokens"; // where tok_name='$tokenid'";
			echo "yes, i am here $sql\n";
			$db = getDB();
			$ret = $db->query($sql);
			$tok_key = "";
			$tok_type = "";
			if(!$ret) {
				echo "got a token assignment for an invalid name\n";
				print_r($msg);
				return false;
			} else {
				// we have something
				echo "i am here?\n";
				foreach($ret as $row) {
					echo "got a row\n";
					print_r($row);
					$tok_key = $row["tok_key"];
					$tok_type = $row["tok_type"];
				}
			}
			
			if($tok_type == "" || $tok_key == "") {
				echo "error in token data from hardware token in DB\n";
			}
			
			echo "and here too, $tok_type, $tok_key\n";
			if(!$myga->setUser($msg["username"], $tok_type, "", $tok_key)) {
				print_r($msg);
				echo "errror assigning token?\n";
			}
		} else return false;
	}
	
	// then we assign to the user
}

function gaasGetUsers_server($msg)
{
	$haveTokens = $msg["havetokens"];
	$userPatter = $msg["userpattern"];
	$group = $msg["group"];
	
	if(confGetval("backend") == "AD") {
		$adgroup = "";
		if($group == "admin") {
			$adgroup = confGetVal("ad.admindef");
		} else {
			$adgroup = confGetVal("ad.clientdef");
		}
		$addom = confGetVal("ad.domain");
		$aduser = confGetVal("ad.user");
		$adpass = confGetVal("ad.pass");
		//echo "using group $adgroup for $group\n";
		
		$users = getUsersInGroup($addom, $aduser, $adpass, $adgroup);
		foreach($users as $user => $real) {
			hasToken($user);
		}
	} else {
		// internal db
	}
	return $users;
}

function gaasDeleteUser_server($msg)
{
	$username = $msg["username"];
	$db = getDB();
	if($db->query("delete from users where users_username='$username'")) {
		return true;
	} else return false;
	
}
?>