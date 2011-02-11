<?php

require_once("../gaas/lib/globalLib.php");

// function userInGroup($user, $domain, $adlogin, $adpass, $group)
$ret = userInGroup($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);

if($ret) {
	echo "true\n";
} else {
	echo "False\n";
}

?>