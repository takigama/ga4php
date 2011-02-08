<?php

// the global lib sets alot of global variables, its fairly unexciting
$BASE_DIR = realpath(dirname(__FILE__)."/../../");
global $BASE_DIR;

// the tcp port number we use for comms
$TCP_PORT_NUMBER = 21335;
global $TCP_PORT_NUMBER;

// the messages structure, used to extend gaas if needed
define("MSG_AUTH_USER_TOKEN", 1);
define("MSG_ADD_USER_TOKEN", 2);
define("MSG_DELETE_USER", 3);
define("MSG_AUTH_USER_PASSWORD", 4);
define("MSG_SET_USER_PASSWORD", 5);
define("MSG_SET_USER_REALNAME", 6);
define("MSG_SET_USER_TOKEN", 7);
define("MSG_SET_USER_TOKEN_TYPE", 8);
define("MSG_GET_USERS", 9);
define("MSG_GET_OTK_PNG", 10);
define("MSG_GET_OTK_ID", 11);
define("MSG_DELETE_USER_TOKEN", 12);
define("MSG_SYNC_TOKEN", 13);
define("MSG_GET_TOKEN_TYPE", 14);
define("MSG_GET_RADIUS_CLIENTS", 15);
define("MSG_REMOVE_RADIUS_CLIENT", 16);
define("MSG_ADD_RADIUS_CLIENT", 17);
define("MSG_STATUS", 18);
define("MSG_INIT_SERVER", 19);

// the gaasd call's $MESSAGE[<MSG>]_server() for the server side
// and $MESSAGE[<msg>]_client() for the client side 

$MESSAGES[MSG_AUTH_USER_TOKEN] = "gaasAuthUserToken";
$MESSAGES[MSG_ADD_USER_TOKEN] = "gaasAddUserToken";
$MESSAGES[MSG_DELETE_USER] = "gaasDeleteUser";
$MESSAGES[MSG_AUTH_USER_PASSWORD] = "gaasAuthUserPass";
$MESSAGES[MSG_SET_USER_PASSWORD] = "gaasSetUserPass";
$MESSAGES[MSG_SET_USER_REALNAME] = "gaasSetUserRealName";
$MESSAGES[MSG_SET_USER_TOKEN] = "gaasSetUserToken";
$MESSAGES[MSG_SET_USER_TOKEN_TYPE] = "gaasSetUserTokenType";
$MESSAGES[MSG_GET_USERS] = "gaasGetUsers";
$MESSAGES[MSG_GET_OTK_PNG] = "gaasGetOTKPng";
$MESSAGES[MSG_GET_OTK_ID] = "gaasGetOTKID";
$MESSAGES[MSG_DELETE_USER_TOKEN] = "gaasDeleteUserToken";
$MESSAGES[MSG_SYNC_TOKEN] = "gaasSyncToken";
$MESSAGES[MSG_GET_TOKEN_TYPE] = "gaasGetTokenType";
$MESSAGES[MSG_GET_RADIUS_CLIENTS] = "gaasGetRadiusClients";
$MESSAGES[MSG_REMOVE_RADIUS_CLIENT] = "gaasRemoveRadiusClient";
$MESSAGES[MSG_ADD_RADIUS_CLIENT] = "gaasAddRadiusClient";
$MESSAGES[MSG_STATUS] = "gaasStatus";
$MESSAGES[MSG_INIT_SERVER] = "gaasInitServer";
global $MESSAGES;

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