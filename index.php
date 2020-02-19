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
        $model = new Model();
        echo "Engine Ready\n";

        
        $model->reset_LogWs();
        echo "Reset Log Client\n";

        $model->reset_LogWsPlay();
        echo "Reset Log Playing\n";

        $model->reset_LogWsMesin();
        echo "Reset Log Playing\n";
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
                // $qs[0] // game_id,
                // $qs[1] // user_token,
                
                // CHECK GAME
                $check_game = $model->check_game($qs[0]);
                if(count($check_game) > 0) {
                    // CHECK USER TOKEN
                    $check_user_token = $model->check_user($qs[1]);
                    if(count($check_user_token) > 0) {
                        $exec = $model->insert_logWsClients($interface->resourceId, $qs[1], $qs[0]);
                        if($exec) {
                            $button = false;
                            $check_playing = $model->check_playing($qs[0]);
                            if(count($check_playing) == 0) {
                                $button = true;
                            } else {
                                foreach ($check_playing as $keyCplay => $valCplay) {
                                    if($valCplay->id_ws == $interface->resourceId) {
                                        $button = true;
                                    }
                                }
                            }

                            $send = array(
                                'button_play'   => $button,
                            );

                            // SELF
                            $this->ws_client[$interface->resourceId]->send(json_encode($send));


                            // ROOM DATA
                            $room_data = $model->get_roomData($qs[0]);
                            if(count($room_data) > 0) {
                                $new_user_data = array();
                                foreach ($room_data as $keyRoom => $valRoom) {
                                    foreach ($check_user_token as $keyUser => $valUser) {
                                        $broadcast = array(
                                            'watch' => array(
                                                'name'   => $valUser->name,
                                                'avatar' => 'https://www.svgrepo.com/show/160232/avatar.svg?si='.$valUser->name,
                                            ),
                                        );

                                        $this->ws_client[$valRoom->id_ws]->send(json_encode($broadcast));
                                    }
                                }
                            } else {
                                echo "No one in room ".$qs[0]."\n";
                            }
                        } else {
                            $send = array(
                                'code'  => 404,
                                'datas' => array(),
                                'msg'   => 'Save logs failed'
                            );

                            $this->ws_client[$interface->resourceId]->send(json_encode($send));
                        }
                    } else {
                        $send = array(
                            'code'  => 404,
                            'datas' => array(),
                            'msg'   => 'User not found'
                        );

                        $this->ws_client[$interface->resourceId]->send(json_encode($send));
                    }
                } else {
                    $send = array(
                        'code'  => 404,
                        'datas' => array(),
                        'msg'   => 'Game not found'
                    );

                    $this->ws_client[$interface->resourceId]->send(json_encode($send));
                }
            } else {
                $send = array(
                    'code'  => 404,
                    'datas' => array(),
                    'msg'   => 'Invalid parameters'
                );

                $this->ws_client[$interface->resourceId]->send(json_encode($send));
            }
        }
    }

    public function onMessage(ConnectionInterface $interface, $message) {
        $model = new Model();

        $request = json_decode($message);
        if(json_last_error() === JSON_ERROR_NONE) {
            foreach ($request as $action => $data) {
                if($action == 'chat') {

                    $interface_user_data = $model->check_userIdWs($interface->resourceId, $data->room);
                    if(count($interface_user_data) > 0) {
                        foreach ($interface_user_data as $keyIface => $valIface) {

                            $game_id    = $data->room;
                            $mssg       = $data->mssg;

                            $insert_chat_log = $model->insert_logWsChats($interface->resourceId, $valIface->user_token, $game_id, $mssg);

                            // pusg config
                            $list_user_in_room = $model->get_roomData($game_id);
                            foreach ($list_user_in_room as $keyRoom => $valRoom) {

                                $user_token = $model->check_user($valRoom->user_token);
                                if(count($user_token) > 0) {
                                    foreach ($user_token as $keyUsr => $valUsr) {
                                        $broadcast = array(
                                            'chat'  => array(
                                                'yes'   => true,
                                                'from'  => $valUsr->name,
                                                'mssg'  => $mssg,
                                            ),
                                        );

                                        $this->ws_client[$valRoom->id_ws]->send(json_encode($broadcast));
                                    }
                                } else {
                                    echo "User not found 1 \n";
                                }
                            }
                        }
                    } else {
                        echo "string 1\n";
                    }
                } elseif($action == 'play') {

                    $interface_user_data = $model->check_userIdWs($interface->resourceId, $data->room);
                    if(count($interface_user_data) > 0) {
                        foreach ($interface_user_data as $keyIface => $valIface) {

                            $game_id    = $data->room;

                            $check_playing = $model->check_playing($game_id);
                            if(count($check_playing) > 0) {
                                $button = false;
                                foreach ($check_playing as $keyCplay => $valCplay) {
                                    if($valCplay->id_ws == $interface->resourceId) {
                                        $button = true;
                                    }
                                }

                                $send = array(
                                    'button_play'   => $button,
                                );

                                // SELF
                                $this->ws_client[$interface->resourceId]->send(json_encode($send));
                            } else {
                                $insert_play_log = $model->insert_logWsPlay($interface->resourceId, $valIface->user_token, $game_id);

                                // pusg config
                                $list_user_in_room = $model->get_roomData($game_id);

                                $send = array(
                                    'button_play'   => true,
                                );

                                // SELF
                                $this->ws_client[$interface->resourceId]->send(json_encode($send));


                                foreach ($list_user_in_room as $keyRoom => $valRoom) {

                                    $user_token = $model->check_user($valRoom->user_token);
                                    if(count($user_token) > 0) {

                                        foreach ($user_token as $keyUsr => $valUsr) {
                                            if($valRoom->id_ws != $interface->resourceId) {
                                                $broadcast = array(
                                                    'button_play'   => false,
                                                );

                                                // BROADCAST
                                                $this->ws_client[$valRoom->id_ws]->send(json_encode($broadcast));
                                            }
                                        }
                                    } else {
                                        echo "User not found 2 \n";
                                    }
                                }
                            }
                        }
                    } else {
                        echo "string 2\n";
                    }

                } elseif($action == 'control') {
                    $game_id    = 1;
                    // $game_id    = $data->room;
                    $cmd       = "broadcast: ".$data->cmd;

                    $check_comming = $model->check_logWsMesin($game_id);
                    if(count($check_comming) > 0) {

                        foreach ($check_comming as $keyMsn => $valMsn) {
                            $id_ws_mesin = $valMsn->id_ws;

                            // PERINTAH KE MESIN
                            $this->ws_client[$id_ws_mesin]->send(json_encode($cmd));
                        }

                    } else {
                        echo "Mesin tidak ada \n";
                    }
                } else {
                    echo "string ujung \n";
                }
            } // end foreach
        } else {
            // Perintah iregular untuk user, harusnya untuk mesin komunikasi
            // echo "Komunikasi mesin";
            if($message == "*1234*mulai*") {
                // CHECK ID MESIN

                // MASUKAN KEDALAM LOG
                // CONTOH 1234 = 1
                $game_id = 1;
                $check_comming = $model->check_logWsMesin($game_id);
                if(count($check_comming) == 0) {
                    $model->insert_logWsMesin($interface->resourceId, $game_id);
                }
            } elseif($message == "*1234*x*0") {

                $check_user_play = $model->check_on_playing_idWs($interface->resourceId);
                if(count($check_user_play) > 0) {
                    foreach ($check_user_play as $keyCUP => $valCUP) {
                        $model->end_outLogWsPlay($interface->resourceId, $valCUP->user_token, $valCUP->game_id);

                        $send = array(
                            'button_play'   => false,
                        );

                        // SELF
                        $this->ws_client[$interface->resourceId]->send(json_encode($send));
                    }
                } else {
                    echo "check_user_play \n";
                }
            } else {
                echo "Deep \n";
            }
        }

    }

    public function onClose(ConnectionInterface $interface) {
        $model = new Model();
        $check_user_log = $model->check_userLog($interface->resourceId);
        if(count($check_user_log) > 0) {
            foreach ($check_user_log as $key => $val) {

                $check_on_play = $model->check_on_playing($interface->resourceId, $val->user_token, $val->game_id);
                if(count($check_on_play) > 0) {
                    $model->end_outLogWsPlay($interface->resourceId, $val->user_token, $val->game_id);
                }


                $updateLog = $model->update_LogWs($val->id, $interface->resourceId);
                if($updateLog) {

                    foreach ($model->check_user($check_user_log[0]->user_token) as $keyUsrSc => $valUsrSc) {
                        $list_user_in_room = $model->get_roomData($val->game_id);
                        foreach ($list_user_in_room as $keyUsr => $valUsr) {
                            if($valUsr->id_ws != $interface->resourceId) {
                                $broadcast = array(
                                    'code'  => 200,
                                    'datas' => array(),
                                    'msg'   => $valUsrSc->name.' Leave Game'
                                );

                                $this->ws_client[$valUsr->id_ws]->send(json_encode($broadcast));                                  
                            }
                        }
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