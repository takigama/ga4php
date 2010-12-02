<?php

if(file_exists("config.php")) require_once("config.php")
else {
	// config file doesnt exist, we must abort sensibly
}

// get out master library for ga4php
require_once("../lib/lib.php");


// first we want to fork into the background like all good daemons should
$pid = pcntl_fork();

if($pid == -1) {
	
} else if($pid) {
	// i am the parent, i shall leave
	exit(0);
} else {
	// i am the child, begin me up
}

?>