<?php
use voku\db\DB;

class Model {
    
    private $host;
    private $username;
    private $password;
    private $database;

    public function __construct()
    {
        $this->host         = 'localhost';
        $this->username     = 'root';
        $this->password     = '';
        $this->database     = 'momoka_db';
    }

    public function insert_logWsClients($id_ws, $user_token, $game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

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
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT id, game_id, user_token FROM log_ws_clients WHERE id_ws = '".$id_ws."' LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function get_roomData($game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT id_ws, user_token FROM log_ws_clients WHERE game_id = '".$game_id."' AND id_ws <> 0");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function reset_LogWs() {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $where = [
            'id_ws <> ' => 0,
            'updated_at IS ' => NULL,
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

    public function update_LogWs($id, $id_ws)
    {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

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
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM user WHERE token = '".$user_token."' LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function check_userIdWs($id_ws, $game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM log_ws_clients WHERE id_ws = '".$id_ws."' AND game_id = '".$game_id."' LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function check_game($game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT id FROM game WHERE id = '".$game_id."' LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function check_playing($game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM log_ws_play WHERE game_id = '".$game_id."' AND is_play = 1 LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function insert_logWsChats($id_ws, $user_token, $game_id, $chat) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $data = [
            'id_ws'         => $id_ws,
            'user_token'    => $user_token,
            'game_id'       => $game_id,
            'chat'          => $chat,
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->insert('log_ws_chats', $data);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function insert_logWsPlay($id_ws, $user_token, $game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $data = [
            'id_ws'         => $id_ws,
            'user_token'    => $user_token,
            'game_id'       => $game_id,
            'is_play'       => 1,
            'start_play'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->insert('log_ws_play', $data);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function end_logWsPlay($id_ws, $user_token, $game_id)
    {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $where = [
            'id_ws'         => $id_ws,
            'user_token'    => $user_token,
            'game_id'       => $game_id,
        ];

        $update = [
            'is_play'       => 0,
            'end_play'      => date('Y-m-d H:i:s'),
        ];

        $query = $db->update('log_ws_play', $update, $where);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function reset_LogWsPlay()
    {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $where = [
            'end_play IS ' => NULL,
        ];

        $update = [
            'is_play'     => 0,
            'end_play'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->update('log_ws_play', $update, $where);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function check_on_playing($id_ws, $user_token, $game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM log_ws_play WHERE id_ws = '".$id_ws."' AND user_token = '".$user_token."' AND game_id = '".$game_id."' AND is_play = 1 LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function check_on_playing_idWs($id_ws) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM log_ws_play WHERE id_ws = '".$id_ws."' AND is_play = 1 LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function end_outLogWsPlay($id_ws, $user_token, $game_id)
    {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $where = [
            'id_ws'         => $id_ws,
            'user_token'    => $user_token,
            'game_id'       => $game_id,
        ];

        $update = [
            'is_play'     => 0,
            'end_play'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->update('log_ws_play', $update, $where);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function check_logWsMesin($game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $query = $db->query("SELECT * FROM log_ws_mesin WHERE id_ws <> 0 AND game_id = '".$game_id."' AND refused_at IS NULL LIMIT 1");
        $data  = $query->fetchAll();
        if(count($data) > 0) {
            return $data;
        } else {
            return array();
        }
    }

    public function insert_logWsMesin($id_ws, $game_id) {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $data = [
            'id_ws'         => $id_ws,
            'game_id'       => $game_id,
            'connected_at'  => date('Y-m-d H:i:s'),
        ];

        $query = $db->insert('log_ws_mesin', $data);
        if($query) {
            return true;
        } else {
            return false;
        }
    }

    public function reset_LogWsMesin()
    {
        $db = DB::getInstance($this->host, $this->username, $this->password, $this->database);

        $where = [
            'refused_at IS ' => NULL,
        ];

        $update = [
            'id_ws'         => 0,
            'refused_at'    => date('Y-m-d H:i:s'),
        ];

        $query = $db->update('log_ws_mesin', $update, $where);
        if($query) {
            return true;
        } else {
            return false;
        }
    }
}