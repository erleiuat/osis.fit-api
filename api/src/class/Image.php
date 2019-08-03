<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "image";
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

        $vals = Core::mergeAssign([
            'user_id' => $this->user->id, 
            'name' => null,
            'mime' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);

        $this->id = $this->db->conn->lastInsertId();        
        return $this;
        
    }

    public function read($id = false) {
        if(!$id) $id = $this->id;

        $where = ['user_id' => $this->user->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set($result[0]);
        return $this;

    }

    public function delete($id = false) {

        $where = ['user_id' => $this->user->id, 'id' => ($id ?: $this->id)];
        $changed = $this->db->makeDelete($this->t_main, $where);

        if ($changed < 1) throw new ApiException(404, 'item_not_found', get_class($this));
        else if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));
        return $this;

    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        $url = Env::api_static_url."/".Env::api_name;
        $folder = hash('ripemd160', $this->user->id);
        $path = $url."/".$folder;
        $file = $obj->name.".".$obj->mime;

        return (object) [
            "id" => (int) $obj->id,
            "name" => $obj->name,
            "mime" => $obj->mime,
            "fullPath" => $path."/".$file,
            "lazyPath" => $path."/lazy/".$file
        ];
        
    }
    
}