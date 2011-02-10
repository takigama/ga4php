<?php

// thie file defines the messages sent too and from the gaas client.
function gaasStatus_clientsend($params)
{
	return $params[0];
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
	echo "backend:\n";
	print_r($params);
	echo "\n";
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
function gaasInitServer_clientrecv($params)
{
	echo "in recv, params\n";
	print_r($params);
	return $params;
}

function gaasSetADLogin_clientsend($params)
{
	
}

function gaasSetADLogin_clientrecv($params)
{
	
}
?>