<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/Model.php';

//Ratchet
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

$port = 8085;

class Socket implements MessageComponentInterface {
    
    protected $ws_client;

    public function __construct()
    {
        echo "Engine Ready\n";
    }

    ////// OPEN
    public function onOpen(ConnectionInterface $interface) {
        $model = new Model();
        // STORE NEW CLIENT TO WS
        $this->ws_client[$interface->resourceId] = $interface;

        // QUERY STRING BUT NO PARAM
        $get_qs = $interface->httpRequest->getUri()->getQuery();
        if($get_qs == '') {
            // PUSH
            $send = array(
                'code'  => 404,
                'datas' => array(),
                'msg'   => 'Parameter not found'
            );
        } else {
            $qs = explode('&', $get_qs);
            if(count($qs) == 2) {
                $data_qs = array(
                    'game_id'       => $qs[0],
                    'user_token'    => $qs[1],
                );

                

                // CHECK TOKEN
                $check_token = $model->check_user($qs[1]);
                if(count($check_token) > 0) {
                    $exec = $model->insert_logWsClients($interface->resourceId, $qs[1], $qs[0]);
                    if($exec) {
                        $send = array(
                            'code'  => 200,
                            'datas' => array(
                                'id'    => $interface->resourceId,
                            ),
                            'msg'   => 'You are connected'
                        );
                    } else {
                        $send = array(
                            'code'  => 404,
                            'datas' => array(),
                            'msg'   => 'Save logs failed'
                        );
                    }
                } else {
                    $send = array(
                        'code'  => 404,
                        'datas' => array(),
                        'msg'   => 'User not found'
                    );
                }
            } else {
                $send = array(
                    'code'  => 404,
                    'datas' => array(),
                    'msg'   => 'Invalid parameters'
                );
            }
        }

        $this->ws_client[$interface->resourceId]->send(json_encode($send));
    }

    public function onMessage(ConnectionInterface $interface, $msg) {
        //Async Mysql
        $result = $db_connect->query("SELECT * FROM banner WHERE id = ".$msg." LIMIT 1");
       
        $dataUsers = array();
        $users  = $result->fetchAll();
        foreach($users as $key => $val) {
            $dataUsers[$key] = $val;
        }

        print_r(json_encode($dataUsers)."\n");

        $this->users[$interface->resourceId]->send(json_encode($dataUsers));
    }

    public function onClose(ConnectionInterface $interface) {
        $model = new Model();
        $check_user_log = $model->check_userLog($interface->resourceId);
        if(count($check_user_log) > 0) {
            foreach ($check_user_log as $key => $val) {
                $updateLog = $model->update_LogWs($val->id, $interface->resourceId);
                if($updateLog) {
                    $send = array(
                        'code'  => 200,
                        'datas' => array(),
                        'msg'   => $val->name.' Leave Game'
                    );

                    $list_user_in_room = $model->get_roomUsers($val->game_id);
                    foreach ($list_user_in_room as $keyUsr => $valUsr) {
                        $this->ws_client[$valUsr->id_ws]->send(json_encode($send)); 
                    }
                } else {
                    $send = array(
                        'code'  => 404,
                        'datas' => array(),
                        'msg'   => 'Failed to update log'
                    );

                    $this->ws_client[$interface->resourceId]->send(json_encode($send)); 
                }
            }
        } else {
            $send = array(
                'code'  => 404,
                'datas' => array(),
                'msg'   => 'User not found'
            );

            $this->ws_client[$interface->resourceId]->send(json_encode($send));
        }
    }

    public function onError(ConnectionInterface $interface, \Exception $e) {
        echo "$e \n";
        $interface->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket()
        )
    ),
    $port
);

$server->run();