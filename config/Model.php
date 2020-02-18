<?php
use voku\db\DB;

class Model {
    
    protected $database;

    public function __construct()
    {
        echo "DB Ready \n";
    }

    public function insert_logWsClients($id_ws, $user_token, $game_id) {
        $db = DB::getInstance('localhost', 'root', '', 'momoka_db');

        $data = [
            'id_ws'         => $id_ws,
            'user_token'    => $user_token,
            'game_id'       => $game_id,
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->insert('log_ws_clients', $data);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function check_userLog($id_ws) {
        $db = DB::getInstance('localhost', 'root', '', 'momoka_db');

        $query = $db->query("SELECT id, game_id FROM log_ws_clients WHERE id_ws = '".$id_ws."' LIMIT 1");
        $user  = $query->fetchAll();
        if(count($user) > 0) {
            return $user;
        } else {
            return array();
        }
    }

    public function get_roomUsers($game_id) {
        $db = DB::getInstance('localhost', 'root', '', 'momoka_db');

        $query = $db->query("SELECT lws.id, lws.id_ws, usr.name FROM log_ws_clients lws INNER JOIN user usr ON usr.token = lws.user_token WHERE lws.game_id = '".$game_id."' AND lws.id_ws != 0");
        $user  = $query->fetchAll();
        if(count($user) > 0) {
            return $user;
        } else {
            return array();
        }
    }

    public function update_LogWs($id, $id_ws)
    {
        $db = DB::getInstance('localhost', 'root', '', 'momoka_db');

        $where = [
            'id'    => $id,
            'id_ws' => $id_ws,
        ];

        $update = [
            'id_ws'         => 0,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->update('log_ws_clients', $update, $where);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function check_user($user_token) {
        $db = DB::getInstance('localhost', 'root', '', 'momoka_db');

        $query = $db->query("SELECT id FROM user WHERE token = '".$user_token."' LIMIT 1");
        $user  = $query->fetchAll();
        if(count($user) > 0) {
            return $user;
        } else {
            return array();
        }
    }
}