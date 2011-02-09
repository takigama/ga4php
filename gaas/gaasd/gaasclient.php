<?php

require_once("../lib/gaasdClient.php");

$myga = new GAASClient();

$myga->MSG_INIT_SERVER("AD", "user", "password", "domain", "cdef", "adef");

?>
