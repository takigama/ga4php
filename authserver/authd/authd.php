<?php

// TODO: SO MUCH ERROR CHECKING ITS NOT FUNNY


// get out master library for ga4php
require_once("../lib/lib.php");

	
//exit(0);
// first we want to fork into the background like all good daemons should
//$pid = pcntl_fork();

// uncomment this bit and comment the fork above to stop it going into the background
$pid = 0;

if($pid == -1) {
	
} else if($pid) {
	// i am the parent, i shall leave
	echo "i am a parent, i leave\n";
	exit(0);
} else {
	// here is where i need to swithc to TCP network protocol stuff
	// i must bind 127.0.0.1 though.
	// what i want to happen is this:
	// 1) server receives connection
	// 2) server forks off process to process connection
	// 3) main server continues.
	// a forked process thingy should be fully self contained and capable of dealing
	// with "problems", i.e. the parent doesnt want to have to clean up children
	global $MSG_QUEUE_KEY_ID_SERVER, $MSG_QUEUE_KEY_ID_CLIENT;
	global $TCP_PORT_NUMBER;
	
	$cl_queue = msg_get_queue($MSG_QUEUE_KEY_ID_CLIENT, 0666 | 'IPC_CREAT');
	$sr_queue = msg_get_queue($MSG_QUEUE_KEY_ID_SERVER, 0666 | 'IPC_CREAT');
	
	// Here goes the tcp equivalent
	/*
	$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_bind($res, "127.0.0.1", 10056);
	socket_listen($res);

	while(true) {
		$data_socket = socket_accept($res);
		// now i fork
		$forked = pcntl_fork();
		
		// TODO: DEAL WITH THIS PROPERLY
		if($forked == -1) {
			echo "Failed to fork\n";
		} else if(!$forked) {
			// I am the child, i process the request
			// all the shit down below goes in here
			$recvd = "";
			$continue = true;
			while($continue) {
				$size = socket_recv($data_socket, $recvd_a, 1024, 0);
				$recvd .= $recvd_a;
				if(preg_match("/.*\:EOD$/", $recvd) {
					// we have a full string... break out
					$continue = false;
					break;
				}
			}

			$myga = new gaasGA();
			
			$xps = explode(":", $recvd);
			$component =  unserialize(base64_decode($xps[1]));
			$msg_type = $component["type"];
			$msg = $component["data"];

			// the switch should now set a $data_returned value that gets bundled up and sent back to the client
			// HERES WHERE THE SWITCH GOES
			// ******
			switch($msg_type) {
				case MSG_GET_RADIUS_CLIENTS:
					$sql = "select * from radclients";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$clients = "";
					$i=0;
					foreach($res as $row) {
						// 		$sql = 'CREATE TABLE "radclients" ("rad_id" INTEGER PRIMARY KEY AUTOINCREMENT,"rad_name" TEXT, "rad_ip" TEXT, "rad_secret" TEXT, "rad_desc" TEXT);';
						$clients[$i]["name"] = $row["rad_name"];
						$clients[$i]["ip"] = $row["rad_ip"];
						$clients[$i]["secret"] = $row["rad_secret"];
						$clients[$i]["desc"] = $row["rad_desc"];
						$i++;
					}
					$data_returned = $clients;
					break;
				case MSG_REMOVE_RADIUS_CLIENT:
					// it should send us a client by rad_name - doesnt work yet
					$client = $msg["clientname"];
					$sql = "delete from radclients where rad_name='$client'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					updateRadius();
					$data_returned = true;
					break;
				case MSG_ADD_RADIUS_CLIENT:
					echo "in addradclient\n";
					$client = $msg["clientname"];
					$clientsecret = $msg["clientsecret"];
					$clientip = $msg["clientip"];
					$clientdesc = $msg["clientdescription"];
					$dbo = getDatabase();
					
					// check for existing clients with same name
					$sql = "select * from radclients where rad_name='$client'";
					echo "doing select, $sql\n";
					$res = $dbo->query($sql);
					if($res->fetchColumn() > 0) {
						$data_returned = "name";
							
					} else {
						// check for existing clients with same ip
						$sql = "select * from radclients where rad_ip='$clientip'";
						$res = $dbo->query($sql);
						echo "doing select, $sql\n";
						if($res->fetchColumn() > 0) {
							$data_returned = "ip";
									
						} else {
							$sql = "insert into radclients values (NULL, '$client', '$clientip', '$clientsecret', '$clientdesc')";
							$res = $dbo->query($sql);
							updateRadius();
							$data_returned = true;
							break;
						}
					}
					break;
				case MSG_DELETE_USER_TOKEN:
					$username = $msg["username"];
					
					$sql = "select users_otk from users where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$otkid = "";
					foreach($res as $row) {
						$otkid = $row["users_otk"];
					}
					if($otkid!="") {
						global $BASE_DIR;
						unlink("$BASE_DIR/authserver/authd/otks/$otkid.png");
					}
					
					$sql = "update users set users_tokendata='',users_otk='' where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					
					$data_returned = true;
					break;
				case MSG_AUTH_USER_TOKEN:
					echo "Call to auth user token\n";
					// minimal checking, we leav it up to authenticateUser to do the real
					// checking
					if(!isset($msg["username"])) $msg["username"] = "";
					if(!isset($msg["passcode"])) $msg["passcode"] = "";
					$username = $msg["username"];
					$passcode = $msg["passcode"];
					global $myga;
					$authval = $myga->authenticateUser($username, $passcode);
					$data_returned = $authval;
					break;
				case MSG_GET_OTK_ID:
					if(!isset($msg["username"])) {
						msg_send($cl_queue, MSG_GET_OTK_ID, false);
					} else {
						$username = $msg["username"];
						$sql = "select users_otk from users where users_username='$username'";
						$dbo = getDatabase();
						$res = $dbo->query($sql);
						$otkid = "";
						foreach($res as $row) {
							$otkid = $row["users_otk"];
						}
						
						if($otkid == "") {
							$data_returned = false;
						} else {
							$data_returned = $otkid;
						}
					}
					break;
				case MSG_GET_OTK_PNG:
					if(!isset($msg["otk"])) {
						msg_send($cl_queue, MSG_GET_OTK_PNG, false);
					} else {
						$otk = $msg["otk"];
						$sql = "select users_username from users where users_otk='$otk'";
						$dbo = getDatabase();
						$res = $dbo->query($sql);
						$username = "";
						foreach($res as $row) {
							$username = $row["users_username"];
						}
						
						if($username == "") {
							$data_returned = false;
							
						} else if($username != $msg["username"]) {
							$data_returned = false;
						} else {
							global $BASE_DIR;
							$hand = fopen("$BASE_DIR/authserver/authd/otks/$otk.png", "rb");
							$data = fread($hand, filesize("$BASE_DIR/authserver/authd/otks/$otk.png"));
							fclose($hand);
							unlink("$BASE_DIR/authserver/authd/otks/$otk.png");
							$sql = "update users set users_otk='' where users_username='$username'";
							$dbo->query($sql);
							error_log("senting otk, fsize: ".filesize("$BASE_DIR/authserver/authd/otks/$otk.png")." $otk ");
							$data_returned = $data;
						}
					}
					
					break;
				case MSG_SYNC_TOKEN:
					if(!isset($msg["username"])) {
						$data_returned = false;
					} else {
						$tokenone = $msg["tokenone"];
						$tokentwo = $msg["tokentwo"];
						
						$data_returned = $myga->resyncCode($msg["username"], $tokenone, $tokentwo);
					}
					
					break;
				case MSG_GET_TOKEN_TYPE:
					if(!isset($msg["username"])) {
						$data_returned = false;
					} else {
						$data_returned = $myga->getTokenType($msg["username"]);
					}
					break;
				case MSG_ADD_USER_TOKEN:
					echo "Call to add user token\n";
					if(!isset($msg["username"])) {
						$data_returned = false;
					} else {
						global $BASE_DIR;
						$username = $msg["username"];
						$tokentype="TOTP";
						if(isset($msg["tokentype"])) {
							$tokentype=$msg["tokentype"];
						}
						$hexkey = "";
						if(isset($msg["hexkey"])) {
							$hexkey = $msg["hexkey"];
						}
						global $myga;
						$myga->setUser($username, $tokentype, "", $hexkey);
						
						$url = $myga->createUrl($username);
						echo "Url was: $url\n";
						if(!file_exists("$BASE_DIR/authserver/authd/otks")) mkdir("$BASE_DIR/authserver/authd/otks");
						$otk = generateRandomString();
						system("qrencode -o $BASE_DIR/authserver/authd/otks/$otk.png '$url'");
						
						$sql = "update users set users_otk='$otk' where users_username='$username'";
						$dbo = getDatabase();
						$res = $dbo->query($sql);
						
						$data_returned = true;
					}
					break;
				case MSG_DELETE_USER:
					echo "Call to del user\n";
					if(!isset($msg["username"])) {
						$data_returned = false;	
					} else {
						$username = $msg["username"];				
						global $myga;
	
						$sql = "select users_otk from users where users_username='$username'";
						$dbo = getDatabase();
						$res = $dbo->query($sql);
						$otkid = "";
						foreach($res as $row) {
							$otkid = $row["users_otk"];
						}
						if($otkid!="") {
							unlink("otks/$otkid.png");
						}
						
	
						$sql = "delete from users where users_username='$username'";
						$dbo = getDatabase();
						$dbo->query($sql);
	
						$data_returned = true;
					}
					break;
				case MSG_AUTH_USER_PASSWORD:
					// TODO
					echo "Call to auth user pass\n";
					if(!isset($msg["username"])) {
						$data_returned = false;
						break;
					}
					if(!isset($msg["password"])) {
						$data_returned = false;
						break;
					}
					
					$username = $msg["username"];
					$password = $msg["password"];
					$sql = "select users_password from users where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$pass = "";
					foreach($res as $row) {
						$pass = $row["users_password"];
					}
					
					// TODO now do auth
					$ourpass = hash('sha512', $password);
					echo "ourpass: $ourpass\nourhash: $pass\n";
					if($ourpass == $pass) {
						$data_returned = true;
						
					} else {
						$data_returned = false;
						
					}
					
					break;
				case MSG_SET_USER_PASSWORD:
					echo "how on earth is that happening Call to set user pass, wtf?\n";
					// TODO
					print_r($msg);
					if(!isset($msg["username"])) {
						$data_returned = false;
						echo "in break 1\n";
						break;
					}
					if(!isset($msg["password"])) {
						$data_returned = false;
						echo "in break 1\n";
						break;
					}
					
					$username = $msg["username"];
					$password = $msg["password"];
					
					echo "would set pass for $username, to $password\n";
					if($password == "") $pass = "";
					else $pass = hash('sha512', $password);
					
					$dbo = getDatabase();
					echo "in set user pass for $username, $pass\n";
					$sql = "update users set users_password='$pass' where users_username='$username'";
					
					$dbo->query($sql);
	
					$data_returned = true;
					
					
					// these are irrelavent yet
					// TODO now set pass
					break;
				case MSG_SET_USER_REALNAME:
					echo "Call to set user realname\n";
					// TODO
					if(!isset($msg["username"])) {
						$data_returned = false;
						break;
					}
					if(!isset($msg["realname"])) {
						$data_returned = false;
						break;
					}
					
					$username = $msg["username"];
					$realname = $msg["realname"];
					$sql = "update users set users_realname='$realname' where users_username='$username'";
					$dbo = getDatabase();
					
					$dbo->query($sql);
	
					$data_returned = true;
					
					// TODO now set real name
					break;
				case MSG_SET_USER_TOKEN:
					// TODO
					echo "Call to set user token\n";
					if(!isset($msg["username"])) {
						$data_returned = false;
						break;
					}
					if(!isset($msg["tokenstring"])) {
						$data_returned = false;
						break;
					}
					
					global $myga;
					$username = $msg["username"];
					$token = $msg["tokenstring"];
					$return = $myga->setUserKey($username, $token);
					$data_returned = $return;
					
					// TODO now set token 
					break;			
				case MSG_SET_USER_TOKEN_TYPE:
					// TODO
					echo "Call to set user token type\n";
					if(!isset($msg["username"])) {
						$data_returned = false;
						break;
					}
					if(!isset($msg["tokentype"])) {
						$data_returned = false;
						break;
					}
					
					$username = $msg["username"];
					$tokentype = $msg["tokentype"];
					global $myga;
					$data_returned = $myga->setTokenType($username, $tokentype);
					
					// TODO now set token 
					break;
				case MSG_GET_USERS:
					// TODO this needs to be better
					$sql = "select * from users order by users_username";
					
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					
					$users = "";
					$i = 0;
					foreach($res as $row) {
						$users[$i]["username"] = $row["users_username"];
						$users[$i]["realname"] = $row["users_realname"];
						if($row["users_password"]!="") {
							$users[$i]["haspass"] = true;
						} else {
							$users[$i]["haspass"] = false;
						}
						echo "user: ".$users[$i]["username"]." has tdata: \"".$row["users_tokendata"]."\"\n";
						if($row["users_tokendata"]!="") {
							$users[$i]["hastoken"] = true;
						} else {
							$users[$i]["hastoken"] = false;
						}
						
						if($row["users_otk"]!="") {
							$users[$i]["otk"] = $row["users_otk"];
						} else {
							$users[$i]["otk"] = "";
						}
						$i++; 
					}
					$data_returned = $users;
					
					// TODO now set token 
					break;
					
			}		
			
			$d_comp["type"] = $msg_type;
			$d_comp["data"] = $data_returned;
			
			$realdata_returning = "AS:".base64_encode(serialize($d_comp)).":EOD";
			
			socket_send($data_socket, $realdata_returning, strlen($realdata_returning), 0);
			socket_close($data_socket);
			
			// now our child exits?
			return 0;
		}
		// otherwise return to the accept loop
	}
	
	*/

	$myga = new gaasGA();
	global $myga;
	
	
	while(true) {
		msg_receive($sr_queue, 0, $msg_type, 16384, $msg);
		echo "got message of type $msg_type\n";
		switch($msg_type) {
			case MSG_GET_RADIUS_CLIENTS:
				$sql = "select * from radclients";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				$clients = "";
				$i=0;
				foreach($res as $row) {
					// 		$sql = 'CREATE TABLE "radclients" ("rad_id" INTEGER PRIMARY KEY AUTOINCREMENT,"rad_name" TEXT, "rad_ip" TEXT, "rad_secret" TEXT, "rad_desc" TEXT);';
					$clients[$i]["name"] = $row["rad_name"];
					$clients[$i]["ip"] = $row["rad_ip"];
					$clients[$i]["secret"] = $row["rad_secret"];
					$clients[$i]["desc"] = $row["rad_desc"];
					$i++;
				}
				msg_send($cl_queue, MSG_GET_RADIUS_CLIENTS, $clients);
				break;
			case MSG_REMOVE_RADIUS_CLIENT:
				// it should send us a client by rad_name - doesnt work yet
				$client = $msg["clientname"];
				$sql = "delete from radclients where rad_name='$client'";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				updateRadius();
				msg_send($cl_queue, MSG_REMOVE_RADIUS_CLIENT, true);
				break;
			case MSG_ADD_RADIUS_CLIENT:
				echo "in addradclient\n";
				$client = $msg["clientname"];
				$clientsecret = $msg["clientsecret"];
				$clientip = $msg["clientip"];
				$clientdesc = $msg["clientdescription"];
				$dbo = getDatabase();
				
				// check for existing clients with same name
				$sql = "select * from radclients where rad_name='$client'";
				echo "doing select, $sql\n";
				$res = $dbo->query($sql);
				if($res->fetchColumn() > 0) {
					msg_send($cl_queue, MSG_ADD_RADIUS_CLIENT, "name");
						
				} else {
					// check for existing clients with same ip
					$sql = "select * from radclients where rad_ip='$clientip'";
					$res = $dbo->query($sql);
					echo "doing select, $sql\n";
					if($res->fetchColumn() > 0) {
						msg_send($cl_queue, MSG_ADD_RADIUS_CLIENT, "ip");
								
					} else {
						$sql = "insert into radclients values (NULL, '$client', '$clientip', '$clientsecret', '$clientdesc')";
						$res = $dbo->query($sql);
						updateRadius();
						msg_send($cl_queue, MSG_ADD_RADIUS_CLIENT, true);
						break;
					}
				}
				break;
			case MSG_DELETE_USER_TOKEN:
				$username = $msg["username"];
				
				$sql = "select users_otk from users where users_username='$username'";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				$otkid = "";
				foreach($res as $row) {
					$otkid = $row["users_otk"];
				}
				if($otkid!="") {
					global $BASE_DIR;
					unlink("$BASE_DIR/authserver/authd/otks/$otkid.png");
				}
				
				$sql = "update users set users_tokendata='',users_otk='' where users_username='$username'";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				
				msg_send($cl_queue, MSG_DELETE_USER_TOKEN, true);
				break;
			case MSG_AUTH_USER_TOKEN:
				echo "Call to auth user token\n";
				// minimal checking, we leav it up to authenticateUser to do the real
				// checking
				if(!isset($msg["username"])) $msg["username"] = "";
				if(!isset($msg["passcode"])) $msg["passcode"] = "";
				$username = $msg["username"];
				$passcode = $msg["passcode"];
				global $myga;
				$authval = $myga->authenticateUser($username, $passcode);
				msg_send($cl_queue, MSG_AUTH_USER_TOKEN, $authval);
				break;
			case MSG_GET_OTK_ID:
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_GET_OTK_ID, false);
				} else {
					$username = $msg["username"];
					$sql = "select users_otk from users where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$otkid = "";
					foreach($res as $row) {
						$otkid = $row["users_otk"];
					}
					
					if($otkid == "") {
						msg_send($cl_queue, MSG_GET_OTK_ID, false);
					} else {
						msg_send($cl_queue, MSG_GET_OTK_ID, $otkid);
					}
				}
				break;
			case MSG_GET_OTK_PNG:
				if(!isset($msg["otk"])) {
					msg_send($cl_queue, MSG_GET_OTK_PNG, false);
				} else {
					$otk = $msg["otk"];
					$sql = "select users_username from users where users_otk='$otk'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$username = "";
					foreach($res as $row) {
						$username = $row["users_username"];
					}
					
					if($username == "") {
						msg_send($cl_queue, MSG_GET_OTK_PNG, false);
					} else if($username != $msg["username"]) {
						msg_send($cl_queue, MSG_GET_OTK_PNG, false);
					} else {
						global $BASE_DIR;
						$hand = fopen("$BASE_DIR/authserver/authd/otks/$otk.png", "rb");
						$data = fread($hand, filesize("$BASE_DIR/authserver/authd/otks/$otk.png"));
						fclose($hand);
						unlink("$BASE_DIR/authserver/authd/otks/$otk.png");
						$sql = "update users set users_otk='' where users_username='$username'";
						$dbo->query($sql);
						error_log("senting otk, fsize: ".filesize("$BASE_DIR/authserver/authd/otks/$otk.png")." $otk ");
						msg_send($cl_queue, MSG_GET_OTK_PNG, $data);
					}
				}
				
				break;
			case MSG_SYNC_TOKEN:
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SYNC_TOKEN, false);
				} else {
					$tokenone = $msg["tokenone"];
					$tokentwo = $msg["tokentwo"];
					
					msg_send($cl_queue, MSG_SYNC_TOKEN, $myga->resyncCode($msg["username"], $tokenone, $tokentwo));
				}
				
				break;
			case MSG_GET_TOKEN_TYPE:
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_GET_TOKEN_TYPE, false);
				} else {
					msg_send($cl_queue, MSG_GET_TOKEN_TYPE, $myga->getTokenType($msg["username"]));
				}
				break;
			case MSG_ADD_USER_TOKEN:
				echo "Call to add user token\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, false);	
				} else {
					global $BASE_DIR;
					$username = $msg["username"];
					$tokentype="TOTP";
					if(isset($msg["tokentype"])) {
						$tokentype=$msg["tokentype"];
					}
					$hexkey = "";
					if(isset($msg["hexkey"])) {
						$hexkey = $msg["hexkey"];
					}
					global $myga;
					$myga->setUser($username, $tokentype, "", $hexkey);
					
					$url = $myga->createUrl($username);
					echo "Url was: $url\n";
					if(!file_exists("$BASE_DIR/authserver/authd/otks")) mkdir("$BASE_DIR/authserver/authd/otks");
					$otk = generateRandomString();
					system("qrencode -o $BASE_DIR/authserver/authd/otks/$otk.png '$url'");
					
					$sql = "update users set users_otk='$otk' where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					
					msg_send($cl_queue, MSG_ADD_USER_TOKEN, true);
				}
				break;
			case MSG_DELETE_USER:
				echo "Call to del user\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_DELETE_USER, false);	
				} else {
					$username = $msg["username"];				
					global $myga;

					$sql = "select users_otk from users where users_username='$username'";
					$dbo = getDatabase();
					$res = $dbo->query($sql);
					$otkid = "";
					foreach($res as $row) {
						$otkid = $row["users_otk"];
					}
					if($otkid!="") {
						unlink("otks/$otkid.png");
					}
					

					$sql = "delete from users where users_username='$username'";
					$dbo = getDatabase();
					$dbo->query($sql);

					msg_send($cl_queue, MSG_DELETE_USER, true);
				}
				break;
			case MSG_AUTH_USER_PASSWORD:
				// TODO
				echo "Call to auth user pass\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					break;
				}
				
				$username = $msg["username"];
				$password = $msg["password"];
				$sql = "select users_password from users where users_username='$username'";
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				$pass = "";
				foreach($res as $row) {
					$pass = $row["users_password"];
				}
				
				// TODO now do auth
				$ourpass = hash('sha512', $password);
				echo "ourpass: $ourpass\nourhash: $pass\n";
				if($ourpass == $pass) {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, true);
					
				} else {
					msg_send($cl_queue, MSG_AUTH_USER_PASSWORD, false);
					
				}
				
				break;
			case MSG_SET_USER_PASSWORD:
				echo "how on earth is that happening Call to set user pass, wtf?\n";
				// TODO
				print_r($msg);
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					echo "in break 1\n";
					break;
				}
				if(!isset($msg["password"])) {
					msg_send($cl_queue, MSG_SET_USER_PASSWORD, false);
					echo "in break 1\n";
					break;
				}
				
				$username = $msg["username"];
				$password = $msg["password"];
				
				echo "would set pass for $username, to $password\n";
				if($password == "") $pass = "";
				else $pass = hash('sha512', $password);
				
				$dbo = getDatabase();
				echo "in set user pass for $username, $pass\n";
				$sql = "update users set users_password='$pass' where users_username='$username'";
				
				$dbo->query($sql);

				msg_send($cl_queue, MSG_SET_USER_REALNAME, true);
				
				
				// these are irrelavent yet
				// TODO now set pass
				break;
			case MSG_SET_USER_REALNAME:
				echo "Call to set user realname\n";
				// TODO
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_REALNAME, false);
					break;
				}
				if(!isset($msg["realname"])) {
					msg_send($cl_queue, MSG_SET_USER_REALNAME, false);
					break;
				}
				
				$username = $msg["username"];
				$realname = $msg["realname"];
				$sql = "update users set users_realname='$realname' where users_username='$username'";
				$dbo = getDatabase();
				
				$dbo->query($sql);

				msg_send($cl_queue, MSG_SET_USER_REALNAME, true);
				
				// TODO now set real name
				break;
			case MSG_SET_USER_TOKEN:
				// TODO
				echo "Call to set user token\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN, false);
					break;
				}
				if(!isset($msg["tokenstring"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN, false);
					break;
				}
				
				global $myga;
				$username = $msg["username"];
				$token = $msg["tokenstring"];
				$return = $myga->setUserKey($username, $token);
				msg_send($cl_queue, MSG_SET_USER_TOKEN, $return);
				
				// TODO now set token 
				break;			
			case MSG_SET_USER_TOKEN_TYPE:
				// TODO
				echo "Call to set user token type\n";
				if(!isset($msg["username"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, false);
					break;
				}
				if(!isset($msg["tokentype"])) {
					msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, false);
					break;
				}
				
				$username = $msg["username"];
				$tokentype = $msg["tokentype"];
				global $myga;
				msg_send($cl_queue, MSG_SET_USER_TOKEN_TYPE, $myga->setTokenType($username, $tokentype));
				
				// TODO now set token 
				break;
			case MSG_GET_USERS:
				// TODO this needs to be better
				$sql = "select * from users order by users_username";
				
				$dbo = getDatabase();
				$res = $dbo->query($sql);
				
				$users = "";
				$i = 0;
				foreach($res as $row) {
					$users[$i]["username"] = $row["users_username"];
					$users[$i]["realname"] = $row["users_realname"];
					if($row["users_password"]!="") {
						$users[$i]["haspass"] = true;
					} else {
						$users[$i]["haspass"] = false;
					}
					echo "user: ".$users[$i]["username"]." has tdata: \"".$row["users_tokendata"]."\"\n";
					if($row["users_tokendata"]!="") {
						$users[$i]["hastoken"] = true;
					} else {
						$users[$i]["hastoken"] = false;
					}
					
					if($row["users_otk"]!="") {
						$users[$i]["otk"] = $row["users_otk"];
					} else {
						$users[$i]["otk"] = "";
					}
					$i++; 
				}
				msg_send($cl_queue, MSG_GET_USERS, $users);
				
				// TODO now set token 
				break;
				
		}		
	}	
}

?>
