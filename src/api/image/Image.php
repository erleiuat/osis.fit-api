<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "image";
    private $t_sizes = "image_sizes";
    private $v_info = "v_image";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = [
        'id', 'name', 'mime',
        'full', 'large', 'medium', 'small', 'lazy'
    ];

    public $id;
    public $name;
    public $mime;

    public $full;
    public $large;
    public $mediun;
    public $small;
    public $lazy;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'account_id' => $this->account->id, 
            'name' => null,
            'mime' => null
        ], (array) $this->getObject());

        $this->db->makeInsert($this->t_main, $vals);
        $this->id = $this->db->conn->lastInsertId();        

        $this->db->makeInsert($this->t_sizes, ["image_id" => $this->id]);

        return $this;
        
    }

    public function setSizes($sizes) {

        $where = ['image_id' => $this->id];
        $changed = $this->db->makeUpdate($this->t_sizes, $sizes, $where);

        if ($changed !== 1) throw new ApiException(500, 'sizes_saving_error', get_class($this));
        
        foreach($sizes as $key => $size){
            $this->$key = $size;
        }

        return $this;

    }

    public function read($id = false) {
        if(!$id) $id = $this->id;

        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->v_info, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set($result[0]);
        $this->stampAccess();
        return $this;

    }

    public function stampAccess($id = false, $stamp = false) {

        if(!$id) $id = $this->id;
        if(!$stamp) $stamp = date('Y-m-d H:i:s', time());

        $where = ['id' => $this->id];
        $params = ["access_stamp" => $stamp];
        $changed = $this->db->makeUpdate($this->t_main, $params, $where);

        if ($changed !== 1) throw new ApiException(500, 'sizes_saving_error', get_class($this));
    
    }

    public function delete($id = false) {

        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $changed = $this->db->makeDelete($this->t_main, $where);

        if ($changed < 1) throw new ApiException(404, 'item_not_found', get_class($this));
        else if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));
        return $this;

    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = (array) $this;
        else if (is_object($obj)) $obj = (array) $obj;

        $url = Env_img::url."/".Env_img::folder;
        $folder = hash('ripemd160', $this->account->id);
        $path = $url."/".$folder."/".$obj['name'];

        $files = [
            "full" => null,
            "large" => null,
            "medium" => null,
            "small" => null,
            "lazy" => null
        ];

        $original = $path."/original";
        $current = $original;
        foreach ($files as $key => $value) {
            if($obj[$key]) $current = $path."/".$key;
            $files[$key] = $current;
        }
        $files['original'] = $original;

        return (object) [
            "id" => (int) $obj['id'],
            "name" => $obj['name'],
            "mime" => $obj['mime'],
            "path" => $files
        ];
        
    }
    
}