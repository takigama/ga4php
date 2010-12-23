<?php
// a test of binding
$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($res, "127.0.0.1", 10051);
while(true) {
        $t = socket_listen($res);
        $res2 = socket_accept($res);
        echo "I went past listen\n";
        $i = pcntl_fork();
        if($i == -1) echo "Failed to fork\n";
        else if (!$i) {
                // i am a child
                echo "Child processing\n";
                while(true) {
                        socket_send($res2, "stuff\n", 6, 0);
                        $str = "";
                        echo "Child wait data\n";
                        $k = socket_recv($res2, $str, 16, MSG_WAITALL);
                        echo "Child got data\n";
                        socket_send($res2, $str, $k, 0);
                }
        }
}

?>
