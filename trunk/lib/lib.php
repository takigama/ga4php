<?php

/*
 * TODO's:
 * Implement TOTP fully
 * Error checking, lots of error checking
 */

class GoogleAuthenticator {
	
	// first we init google authenticator by passing it a filename
	// for its sqlite database.
	function __construct($file) {
		if(file_exists($file)) {
			try {
				$this->dbConnector = new PDO("sqlite:$file");
			} catch(PDOException $exep) {
				$this->errorText = $exep->getMessage();
				$this->dbConnector = false;
			}			
		} else {
			$this->setupDB($file);
		}
		
		$this->dbFile = $file;
	}
	
	// creates the database (tables);
	function setupDB($file) {
		
		try {
			$this->dbConnector = new PDO("sqlite:$file");
		} catch(PDOException $exep) {
			$this->errorText = $exep->getMessage();
			$this->dbConnector = false;
		}			
	
		// here we create some tables and stuff
		$this->dbConnector->query('CREATE TABLE "users" ("user_id" INTEGER PRIMARY KEY AUTOINCREMENT,"user_name" TEXT NOT NULL,"user_tokenid" INTEGER)');
		$this->dbConnector->query('CREATE TABLE "tokens" ("token_id" INTEGER PRIMARY KEY AUTOINCREMENT,"token_key" TEXT NOT NULL, "token_type" TEXT NOT NULL, "token_lastid" INTEGER NOT NULL)');
	}
	
	// creates "user" in the database and returns a url for
	// the phone. If user already exists, this returns false
	// if any error occurs, this returns false
	function setupUser($username, $tokentype="HOTP") {
		$key = $this->createBase32Key();
		
		// sql for inserting into db
		$key = $this->createUser($username, $key, $tokentype);
		return $key;
	}
	
	
	// this could get ugly for large databases.. we'll worry about that if it ever happens.
	function getUserList() {
		$res = $this->dbConnector->query("select user_name from users");
		$i = 0;
		$ar = array();
		foreach($res as $row) {
			//error_log("user: ".$row["user_name"]);
			$ar[$i] = $row["user_name"];
			$i++;
		}
		
		return $ar;
	}
	
	// set the token type the user it going to use.
	// this defaults to HOTP - we only do 30s token
	// so lets not be able to set that yet
	function setupTokenType($username, $tokentype) {
		if($tokentype!="HOTP" and $tokentype!="TOTP") {
			$errorText = "Invalid Token Type";
			return false;
		}
		
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		
		// TODO MUST DO ERROR CHECK HERE, this line could be lethal
		$sql = "update tokens set token_type='$tokentype' where token_id='$tid'";
		
		return true;	
	}
	
	
	// create "user" with insert
	function createUser($username, $key, $ttype="HOTP") {
		// sql for inserting into db
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);

		//if($res) if($res->fetchCount()>0) {
			//$this->errorText = "User Already Exists, $username";
			//return false;
		//}
		
		// and finally create 'em
		$hkey = $this->helperb322hex($key);
		$this->dbConnector->query("insert into tokens values (NULL, '$hkey', '$ttype', '0')");
		$id = $this->dbConnector->lastInsertID();
		$this->dbConnector->query("insert into users values (NULL, '$username', '$id')");

		return $key;
	}
	
	// Replcate "user" in the database... All this really
	// does is to replace the key for the user. Returns false
	// if the user doesnt exist of the key is poop
	function replaceUser($username) {
		$key = $this->createBase32Key();
		
		// delete the user - TODO, clean up auth tokens
		$sql = "delete from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		// sql for inserting into db - just making sure.
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);

		if($res->fetchCount()>0) {
			$this->errorText = "User Already Exists, $username";
			return false;
		}
		
		// and finally create 'em
		$this->dbConnector->query("insert into tokens values (NULL, '$key', '0')");
		$id = $this->dbConnector->lastInsertID();
		$this->dbConnector->query("insert into users values (NULL, '$username', '$id')");

		$url = $this->createURL($username, $key);
		
		return $url;
	}
	
	// sets the key for a user - this is assuming you dont want
	// to use one created by the application. returns false
	// if the key is invalid or the user doesn't exist.
	function setUserKey($username, $key) {
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		
		// TODO MUST DO ERROR CHECK HERE, this line could be lethal
		$sql = "update tokens set token_key='$key' where token_id='$tid'";
		
		return true;
	}
	
	
	// have user?
	function userExists($username) {
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		$tid = -1;
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		if($tid == -1) return false;
		else return $tid;
	}
	
	
	// self explanitory?
	function deleteUser($username) {
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		
		// TODO MUST DO ERROR CHECK HERE, this line could be lethal
		$sql = "delete from tokens where token_id='$tid'";
		$this->dbConnector->query($sql);
		
		$sql = "delete from users where user_name='$username'";
		$this->dbConnector->query($sql);
	}
	
	// user has input their user name and some code, authenticate
	// it
	function authenticateUser($username, $code) {
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		$tid = -1;
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		// for HOTP tokens we start at x and go to x+20
		
		// for TOTP we go +/-1min TODO = remember that +/- 1min should
		// be changed based on stepping if we change the expiration time
		// for keys
		
		//		$this->dbConnector->query('CREATE TABLE "tokens" ("token_id" INTEGER PRIMARY KEY AUTOINCREMENT,"token_key" TEXT NOT NULL, "token_type" TEXT NOT NULL, "token_lastid" INTEGER NOT NULL)');
		
		$sql = "select * from tokens where token_id='$tid'";
		$res = $this->dbConnector->query($sql);
		
		$tkey = "";
		$ttype = "";
		$tlid = "";
		foreach($res as $row) {
			$tkey = $row["token_key"];
			$ttype = $row["token_type"];
			$tlid = $row["token_lastid"];	
		}
		
		switch($ttype) {
			case "HOTP":
				$st = $tlid;
				$en = $tlid+20;
				for($i=$st; $i<$en; $i++) {
					$stest = $this->oath_hotp($tkey, $i);
					error_log("code: $code, $stest, $tkey, $tid");
					if($code == $stest) {
						$sql = "update tokens set token_lastid='$i' where token_id='$tid'";
						$this->dbConnector->query($sql);
						return true;
					}
				}
				return false;
				break;
			case "TOTP":
				$t_now = time();
				$t_ear = $t_now - 45;
				$t_lat = $t_now + 60;
				$t_st = ((int)($t_ear/30));
				$t_en = ((int)($t_lat/30));
				error_log("kmac: $t_now, $t_ear, $t_lat, $t_st, $t_en");
				for($i=$t_st; $i<=$t_en; $i++) {
					$stest = $this->oath_hotp($tkey, $i);
					error_log("code: $code, $stest, $tkey\n");
					if($code == $stest) {
						return true;
					}
				}
				break;
			default:
				echo "how the frig did i end up here?";
		}
		
		return false;

	}
	
	// this function allows a user to resync their key. If too
	// many codes are called, we only check up to 20 codes in the future
	// so if the user is at 21, they'll always fail. 
	function resyncCode($username, $code1, $code2) {
		// here we'll go from 0 all the way thru to 200k.. if we cant find the code, so be it, they'll need a new one
		$sql = "select * from users where user_name='$username'";
		$res = $this->dbConnector->query($sql);
		
		$tid = -1;
		foreach($res as $row) {
			$tid = $row["user_tokenid"];	
		}
		
		// for HOTP tokens we start at x and go to x+20
		
		// for TOTP we go +/-1min TODO = remember that +/- 1min should
		// be changed based on stepping if we change the expiration time
		// for keys
		
		//		$this->dbConnector->query('CREATE TABLE "tokens" ("token_id" INTEGER PRIMARY KEY AUTOINCREMENT,"token_key" TEXT NOT NULL, "token_type" TEXT NOT NULL, "token_lastid" INTEGER NOT NULL)');
		
		$sql = "select * from tokens where token_id='$tid'";
		$res = $this->dbConnector->query($sql);
		
		$tkey = "";
		$ttype = "";
		$tlid = "";
		foreach($res as $row) {
			$tkey = $row["token_key"];
			$ttype = $row["token_type"];
			$tlid = $row["token_lastid"];	
		}
		
		switch($ttype) {
			case "HOTP":
				$st = 0;
				$en = 200000;
				for($i=$st; $i<$en; $i++) {
					$stest = $this->oath_hotp($tkey, $i);
					//echo "code: $code, $stest, $tkey\n";
					if($code1 == $stest) {
						$stest2 = $this->oath_hotp($tkey, $i+1);
						if($code2 == $stest2) {
							$sql = "update tokens set token_lastid='$i' where token_id='$tid'";
							$this->dbConnector->query($sql);
							return true;
						}
					}
				}
				return false;
				break;
			case "TOTP":
				break;
			default:
				echo "how the frig did i end up here?";
		}
		
		return false;
		
	}
	
	// gets the error text associated with the last error
	function getErrorText() {
		return $this->errorText;
	}
	
	// create a url compatibile with google authenticator.
	function createURL($user, $key,$toktype = "HOTP") {
		// oddity in the google authenticator... hotp needs to be lowercase.
		$toktype = strtolower($toktype);
		$url = "otpauth://$toktype/$user?secret=$key";
		//echo "url: $url\n";
		return $url;
	}
	
	// creeates a base 32 key (random)
	function createBase32Key() {
		$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
		$key = "";
		for($i=0; $i<16; $i++) {
			$offset = rand(0,strlen($alphabet)-1);
			//echo "$i off is $offset\n";
			$key .= $alphabet[$offset];
		}
		
		return $key;
	}
		
	
	function helperb322hex($b32) {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

        $out = "";
        $dous = "";

        for($i = 0; $i < strlen($b32); $i++) {
        	$in = strrpos($alphabet, $b32[$i]);
        	$b = str_pad(base_convert($in, 10, 2), 5, "0", STR_PAD_LEFT);
            $out .= $b;
            $dous .= $b.".";
        }

        $ar = str_split($out,20);

        //echo "$dous, $b\n";

        //print_r($ar);
        $out2 = "";
        foreach($ar as $val) {
                $rv = str_pad(base_convert($val, 2, 16), 5, "0", STR_PAD_LEFT);
                //echo "rv: $rv from $val\n";
                $out2 .= $rv;

        }
        //echo "$out2\n";

        return $out2;
	}
	
	function helperhex2b32($hex) {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

        $ar = str_split($hex, 5);

        $out = "";
        foreach($ar as $var) {
                $bc = base_convert($var, 16, 2);
                $bin = str_pad($bc, 20, "0", STR_PAD_LEFT);
                $out .= $bin;
                //echo "$bc was, $var is, $bin are\n";
        }

        $out2 = "";
        $ar2 = str_split($out, 5);
        foreach($ar2 as $var2) {
                $bc = base_convert($var2, 2, 10);
                $out2 .= $alphabet[$bc];
        }

        return $out2;
	}
	
	function oath_hotp($key, $counter)
	{
		$key = pack("H*", $key);
	    $cur_counter = array(0,0,0,0,0,0,0,0);
	    for($i=7;$i>=0;$i--)
	    {
	        $cur_counter[$i] = pack ('C*', $counter);
	        $counter = $counter >> 8;
	    }
	    $bin_counter = implode($cur_counter);
	    // Pad to 8 chars
	    if (strlen ($bin_counter) < 8)
	    {
	        $bin_counter = str_repeat (chr(0), 8 - strlen ($bin_counter)) . $bin_counter;
	    }
	
	    // HMAC
	    $hash = hash_hmac ('sha1', $bin_counter, $key);
	    return str_pad($this->oath_truncate($hash), 6, "0", STR_PAD_LEFT);
	}
	
	function oath_truncate($hash, $length = 6)
	{
	    // Convert to dec
	    foreach(str_split($hash,2) as $hex)
	    {
	        $hmac_result[]=hexdec($hex);
	    }
	
	    // Find offset
	    $offset = $hmac_result[19] & 0xf;
	
	    // Algorithm from RFC
	    return
	    (
	        (($hmac_result[$offset+0] & 0x7f) << 24 ) |
	        (($hmac_result[$offset+1] & 0xff) << 16 ) |
	        (($hmac_result[$offset+2] & 0xff) << 8 ) |
	        ($hmac_result[$offset+3] & 0xff)
	    ) % pow(10,$length);
	}
	
	
	// some private data bits.
	private $errorText;
	private $dbFile;
	private $dbConnector;
}
?>