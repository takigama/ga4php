<?php


/*
 * TODO's:
 * Implement TOTP fully
 * Error checking, lots of error checking
 * have a way of encapsulating token data stright into a single field so it could be added
 *    in some way to a preexisting app without modifying the DB as such... or by just adding
 *    a single field to a user table...
 * Remove all reliance on the SQLite database. Data should come from the encasultating application
 *    which will be expected to provde two function calls where it can get/store data - DONE
 */

/*
 * I am an idiot.
 * For some reason i had it in my head to pass static functions into the construct
 * of the class in order to hand data going in/out
 * when in reality i should be making the data in/out func's abstract
 * and getting implementors to extend the class
 * 
 * For now im going to keep implementing it this way and thus my class will
 * forever be an example of poor design choices. It'll change it very shortly though 
 */

/*
 * The way we should really be doing things is to have an array that encapsulates "normal" data (or a class?)
 * and then just manipulate it, then use a checkin function to push the data base into the db...
 */
class GoogleAuthenticator {
	
	// first we init google authenticator by passing it a filename
	// for its sqlite database.
	// $getDataFunction expects 1 argument which defines what data it wants
	// and can be "userlist" or "usertoken:username"
	// $putDataFunciton expects 2 arguments, $1 is data type, $2 is the data
	// $1 can be "changetoken:username", "removetoken:username", $2 is the token
	// data in some encoded form
	// tokenDATA will be like HOTP;KEY;CID where CID is the current counter value
	// why encode like this? i cant think of a good reason tbh, i should probably just
	// use php's arry encoder functions  
	function __construct($getDataFunction, $putDataFunction) {
		$this->putDataFunction = $putDataFunction;
		$this->getDataFunction = $getDataFunction;
	}
	
	// this could get ugly for large databases.. we'll worry about that if it ever happens.
	function getUserList() {
		$func = $this->getDataFunction;
		return $func("userlist", "");
	}
	
	// set the token type the user it going to use.
	// this defaults to HOTP - we only do 30s token
	// so lets not be able to set that yet
	function setupTokenType($username, $tokentype) {
		if($tokentype!="HOTP" and $tokentype!="TOTP") {
			$errorText = "Invalid Token Type";
			return false;
		}
		
		$put["username"] = $username;
		$put["tokentype"] = $tokentype;
		$func = $this->putDataFunction;
		$func("settokentype", $put);
		
		return true;	
	}
	
	
	// create "user" with insert
	function setUser($username, $key = "", $ttype="HOTP") {
		if($key == "") $key = $this->createBase32Key();
		$hkey = $this->helperb322hex($key);
		
		$token["username"] = $username;
		$token["tokenkey"] = $hkey;
		$token["tokentype"] = $ttype;
		
		$func = $this->putDataFunction;
		$func("setusertoken", $token);
		
		return $key;
	}
	
	
	// sets the key for a user - this is assuming you dont want
	// to use one created by the application. returns false
	// if the key is invalid or the user doesn't exist.
	function setUserKey($username, $key) {
		// consider scrapping this
	}
	
	
	// have user?
	function userExists($username) {
		// need to think about this
	}
	
	
	// self explanitory?
	function deleteUser($username) {
		$func = $this->putDataFunction;
		$func("deleteusertoken", $username);
	}
	
	// user has input their user name and some code, authenticate
	// it
	function authenticateUser($username, $code) {
		
		$func = $this->getDataFunction;
		$tokendata = $func("gettoken", $username);
		
		// TODO: check return value
		$ttype = $tokendata["tokentype"];
		$tlid = $tokendata["tokencounter"];
		$tkey = $tokendata["tokenkey"];
		switch($ttype) {
			case "HOTP":
				$st = $tlid;
				$en = $tlid+20;
				for($i=$st; $i<$en; $i++) {
					$stest = $this->oath_hotp($tkey, $i);
					//error_log("code: $code, $stest, $tkey, $tid");
					if($code == $stest) {
						$tokenset["username"] = $username;
						$tokenset["tokencounter"] = $i;
						
						$func = $this->putDataFunction;
						$func("settokencounter", $tokenset);
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
				//error_log("kmac: $t_now, $t_ear, $t_lat, $t_st, $t_en");
				for($i=$t_st; $i<=$t_en; $i++) {
					$stest = $this->oath_hotp($tkey, $i);
					//error_log("code: $code, $stest, $tkey\n");
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
		// for HOTP tokens we start at x and go to x+20
		
		// for TOTP we go +/-1min TODO = remember that +/- 1min should
		// be changed based on stepping if we change the expiration time
		// for keys
		
		//		$this->dbConnector->query('CREATE TABLE "tokens" ("token_id" INTEGER PRIMARY KEY AUTOINCREMENT,"token_key" TEXT NOT NULL, "token_type" TEXT NOT NULL, "token_lastid" INTEGER NOT NULL)');
		
		$func = $this->getDataFunction;
		$tokendata = $func("gettoken", $username);
		
		// TODO: check return value
		$ttype = $tokendata["tokentype"];
		$tlid = $tokendata["tokencounter"];
		$tkey = $tokendata["tokenkey"];
		
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
							$tokenset["username"] = $username;
							$tokenset["tokencounter"] = $i+1;
						
							$func = $this->putDataFunction;
							$func("settokencounter", $tokenset);
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
		if($toktype == "hotp") {
			$url = "otpauth://$toktype/$user?secret=$key&counter=1";
		} else {
			$url = "otpauth://$toktype/$user?secret=$key";
		}
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
	private $getDatafunction;
	private $putDatafunction;
	private $errorText;
}
?>