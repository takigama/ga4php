<?php

require_once("../gaas/lib/gaasdLib.php");

$backEnd = "IN";
createDB();

function grs()
{
	$str = "";
	$strpos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	for($i=0; $i<10; $i++) {
		$str .= $strpos[rand(0, strlen($strpos)-1)];
	}
	
	return $str;
}

for($i = 0; $i < 20; $i++) {
	$grs = grs();
	confPutVar("val$i", $grs);
	echo "set $i to $grs\n";
}

for($i = 0; $i < 20; $i++) {
	$value = confGetVar("val$i");
	echo "Value for $i is $value\n";
}
?>