<?php 
require_once("../gaas/lib/globalLib.php");

// function userInGroup($user, $domain, $adlogin, $adpass, $group)
$ret = getUsersInGroup($argv[1], $argv[2], $argv[3], $argv[4]);

print_r($ret);
?>