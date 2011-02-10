<?php

require_once("../lib/gaasdClient.php");

$myga = new GAASClient();

global $argv;

function usage()
{
	global $argv;
	echo "Usage: ".$argv[0]." command [options]\n";
	echo "\nCommands:\n\tinit AD user password domain clientgroup admingroup\n";
	echo "\tinit IN user password\n";
	echo "\n";
	exit(0);
}

if($argc < 1) {
	usage();
}

switch($argv[1]) {
	case "init":
		if($argv[2] == "AD") {
			if($argc < 7) usage();
		}
		$ret = $myga->MSG_INIT_SERVER("AD", $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
		if($ret) {
			echo "initialising server succeeded\n";
		} else {
			echo "initialising server failed\n";
		}
		break;
	default:
		echo "No such command, ".$argv[1]."\n";
		usage();
		
}

?>
