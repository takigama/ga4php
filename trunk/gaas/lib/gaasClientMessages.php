<?php

// thie file defines the messages sent too and from the gaas client.
function gaasStatus_clientsend($params)
{
	return $params;
}

function gaasStatus_clientrecv($params)
{
	return $params;
}


// INIT server message
// params are:
// AD: "AD", "user", "pass", "domain", "client def", "admin def"
// IN: "IN", "user", "pass"
function gaasInitServer_clientsend($params)
{
	$msg["backend"] = $params[0];
	$msg["user"] = $params[1];
	$msg["pass"] = $params[2];
	
	if($msg["backend"] == "AD") {
		$msg["domain"] = $params[3];
		$msg["clientdef"] = $params[4];
		$msg["admindef"] = $params[5];
	} else if($msg["backend"] == "IN") {
		// we dont do anything
	} else {
		// invalid backend type
		return false;
	}
	
	return $msg;
}

// pretty simple, it either works or doesnt, we just pass on the result
// im leaving this function here as an example of how you deal
// with data coming back from the server but prior to returning
// to the client. if it just returns the data back to the client
// you doing have to define a recving function
function gaasInitServer_clientrecv($params)
{
	return $params;
}

function gaasSetADLogin_clientsend($params)
{
	$msg["domain"] = $params[2];
	$msg["user"] = $params[0];
	$msg["pass"] = $params[1];
	
	return $msg;
}

function gaasSetClientGroup_clientsend($params)
{
	$msg["clientgroup"] = $params[0];
	return $msg;
}

function gaasSetAdminGroup_clientsend($params)
{
	$msg["admingroup"] = $params[0];
	return $msg;
}

function gaasProvisionUser_clientsend($params)
{
	$msg["username"] = $params[0];
	return $msg;
}

function gaasGetUsers_clientsend($params)
{
	$msg["havetokens"] = false;
	$msg["userpattern"] = "";
	$msg["group"] = "client";
	if(isset($params[0])) {
		if($params[0] == "admin") {
			$msg["group"] = "admin";
		}
	}
	if(isset($params[1])) {
		$msg["userpattern"] = $params[1];
	}
	if(isset($params[2])) {
		if($params[2] == "yes") {
			$msg["havetokens"] = true;
		}
	}
	
	return $msg;
}

?>