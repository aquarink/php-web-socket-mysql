<?php

require 'vendor/autoload.php';

use React\Socket\ConnectionInterface;
use React\EventLoop\Factory as ReactLoop;
use React\Socket\Server as ReactSocket;

// React Mysql
use React\MySQL\Factory as MysqlFactory;
use React\MySQL\QueryResult;

$loop = ReactLoop::create();
$socket = new ReactSocket(8089, $loop);


class Socket {
    
}



$loop->run();
