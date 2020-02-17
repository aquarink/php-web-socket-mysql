<?php

require __DIR__ . '/vendor/autoload.php';

//Ratchet
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

// DB
use voku\db\DB;


class Socket implements MessageComponentInterface {
    
    protected $clients;
    private $subscriptions;
    private $users;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
        $this->users = [];
        echo "__construct \n";
    }

    ////// OPEN
    public function onOpen(ConnectionInterface $interface) {
        // STORE NEW CLIENT TO WS
        // STORE NEW CLIENT
        $this->users[$interface->resourceId] = $interface;
        echo "Open \n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        //Async Mysql
        $db = DB::getInstance('localhost', 'root', '', 'wktpanel_db');
        $result = $db->query("SELECT * FROM kategori_tb WHERE id = ".$msg." LIMIT 1");
       
        $dataUsers = array();
        $users  = $result->fetchAll();
        foreach($users as $key => $val) {
            $dataUsers[$key] = $val;
        }

        print_r(json_encode($dataUsers)."\n");

        $this->users[$from->resourceId]->send(json_encode($dataUsers));
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Close \n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "$e \n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket()
        )
    ),
    8085
);

echo "Socket On 8085 \n";
$server->run();