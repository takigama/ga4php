<?php
// a test of binding
$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($res, "127.0.0.1", 10056);

$t = socket_listen($res);

// do sockets work the way i would like?
while(true) {
	$res2 = socket_accept($res);
	
	$real_str = "";
	$continue = true;
	while($continue) {
		socket_recv($res2, $str, 10, 0);
		$real_str .= $str;
		echo "got a bit: $str\n";
		
		if(preg_match("/.*\:EOD/", $real_str)) {
			echo "we have a full str: $real_str\n";
			$continue = false;
			break;
		}
	}
	echo "no longer in continue\n";
}
?>