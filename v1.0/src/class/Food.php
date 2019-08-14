<?php

class Food extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'image', 'title', 'amount', 'calories_per_100'];

    public $id;
    public $image;

    public $title;
    public $amount;
    public $calories_per_100;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'account_id' => $this->account->id, 
            'image_id' => (isset($this->image->id) ? $this->image->id : null),
            'title' => null,
            'amount' => null,
            'calories_per_100' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);

        $this->id = $this->db->conn->lastInsertId();        
        return $this;

    }

    public function read($id = false) {
        
        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set(Core::mergeAssign([
            'id' => null, 
            'image' => $result[0]['image_id'], 
            'title' => null,
            'amount' => null, 
            'calories_per_100' => null
        ], $result[0]));

        return $this;

    }

    public function readAll() {
        
        $where = ['account_id' => $this->account->id];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) < 1) throw new ApiException(204, 'no_items_found', get_class($this));
        return $result;

    }

    public function edit($id = false) {
        
        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $params = Core::mergeAssign([ 
            'image_id' => (isset($this->image->id) ? $this->image->id : null),
            'title' => null,
            'amount' => null,
            'calories_per_100' => null
        ], (array) $this->getObject());
        
        $changed = $this->db->makeUpdate($this->t_main, $params, $where);
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }

    public function delete($id = false) {

        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $changed = $this->db->makeDelete($this->t_main, $where);

        if ($changed < 1) throw new ApiException(404, 'item_not_found', get_class($this));
        else if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));
        return $this;

    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        return (object) [
            "id" => (int) $obj->id,
            "title" => $obj->title,
            "amount" => (double) $obj->amount,
            "calories_per_100" => (double) $obj->calories_per_100,
            "image" => (isset($obj->image) ? $obj->image : false)
        ];
        
    }
    
}