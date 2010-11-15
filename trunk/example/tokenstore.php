<?php
// define our data-set functions
function mySetData($query, $data) {
	global $dbobject;
	
//	echo "called set data: $query<pre>";
//	print_r($query);
//	print_r($data);
//	echo "</pre>";
	
	switch($query) {
		case "settokentype":
			$ttype = $data["tokentype"];
			$tuser = $data["username"];
			$res = $dbobject->query("update users set users_tokentype='$ttype' where users_username='$tuser'");
			break;
		case "setusertoken":
			$ttype = $data["tokentype"];
			$tkey = $data["tokenkey"];
			$tuser = $data["username"];
			
			// dont really care if it does or not
			$res = $dbobject->query("delete from users where users_username = '$tuser'");
			
			$sql = "insert into users values (NULL, '$tuser', '$ttype', '$tkey', '0')";
			error_log("would call: $sql");
			$res = $dbobject->query($sql);
			break;
		case "deleteusertoken":
			$res = $dbobject->query("delete from users where users_username = '$data'");
			break;
		case "settokencounter":
			$tcount = $data["tokencounter"];
			$tuser = $data["username"];
			$res = $dbobject->query("update users set users_tokencounter='$tcount' where users_username='$tuser'");
			break;
		default:
			// do nothing
	}
}

function myGetData($query, $data) {
	//echo "called get data:<pre>";
	//print_r($query);
	//print_r($data);
	//echo "</pre>";
	global $dbobject;
	
	switch($query) {
		case "userlist":
			$sql = "select users_username from users";
			$res = $dbobject->query($sql);
			$i = 0;
			$names[0] = "";
			foreach($res as $row) {
				//error_log("got username, ".$row["users_username"]);
				$names[$i] = $row["users_username"];
				$i++;
			}
			return $names;
			break;
		case "gettoken":
			$sql = "select * from users where users_username='$data'";
			$res = $dbobject->query($sql);
			$i = 0;
			$token = "";
			foreach($res as $row) {
				$token["tokentype"] = $row["users_tokentype"];
				$token["tokenkey"] = $row["users_tokenkey"];
				$token["tokencounter"] = $row["users_tokencounter"];
			}
			return $token;
		default:
			// nothing
	}
}

?>