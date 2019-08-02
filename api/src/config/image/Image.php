<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_image";
    private $v_detail = "v_image_detail";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = ['id', 'name', 'mime'];

    public $id;
    public $name;
    public $mime;
    
    public $upload_stamp;
    public $access_stamp;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `name`, `mime`) VALUES 
            (:user_id, :name, :mime);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'name', 'mime'],
            [$this->user->id, $this->name, $this->mime]
        )->execute($stmt);

        $this->id = $this->db->conn->lastInsertId();
        return $this;

    }

    public function delete() {

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main." WHERE 
            id = :id AND 
            user_id = :user_id 
        ");
        $this->db->bind($stmt, 
            ['id', 'user_id'],
            [$this->id, $this->user->id]
        )->execute($stmt);

        if($stmt->rowCount() !== 1) throw new Exception('entry_not_found', 404);
        return $this;

    }

    public function read() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id AND id = :id
        ");
        $this->db->bind($stmt, 
            ['user_id', 'id'],
            [$this->user->id, $this->id]
        )->execute($stmt);

        if ($stmt->rowCount() !== 1) throw new Exception('entry_not_found', 404);

        $img = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->set([
            'id' => $img['id'],
            'name' => $img['name'],
            'mime' => $img['mime']
        ]);

        return $this;

    }

    public function getObject($obj = false) {
        
        if(!$obj) $obj = (array) $this;

        $folder = hash('ripemd160', $this->user->id);
        $url = Env::api_static_url."/".Env::api_name;
        $path = $url."/".$folder;

        return (object) [
            "id" => $obj['id'],
            "name" => $obj['name'],
            "mime" => $obj['mime'],
            "folder" => $folder,
            "url" => $url,
            "fullPath" => $path."/".$obj['name'].".".$obj['mime'],
            "lazyPath" => $path."/lazy/".$obj['name'].".".$obj['mime']
        ];
        
    }
    
}