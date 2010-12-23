<?php
$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_connect($res, "127.0.0.1", 10056);

$str = "asdflkjahsdfkjhlaskdfhlaskjdflkasdjfh:EOD";
socket_send($res, $str, strlen($str), 0);
echo "sent\n";

sleep(100000);

?>