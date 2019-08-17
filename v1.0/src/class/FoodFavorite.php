<?php

class FoodFavorite extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food_favorite";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'image', 'title', 'amount', 'calories_per_100', 'total'];

    public $id;
    public $image;
    public $title;

    public $amount;
    public $calories_per_100;
    public $total;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'account_id' => $this->account->id, 
            'id' => null,
            'image' => null,
            'title' => null,
            'amount' => null,
            'calories_per_100' => null,
            'total' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);

        return $this;

    }

    public function read($id = false) {
        
        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set(Core::mergeAssign([
            'id' => null, 
            'image' => null, 
            'title' => null,
            'amount' => null,
            'calories_per_100' => null,
            'total' => null
        ], $result[0]));

        return $this;

    }

    public function readAll() {
        
        $where = ['account_id' => $this->account->id];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) < 1) throw new ApiException(204, 'no_items_found', get_class($this));
        return $result;

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
            "id" => $obj->id,
            "image" => $obj->image,
            "title" => $obj->title,
            "amount" => (double) $obj->amount,
            "calories_per_100" => (double) $obj->calories_per_100,
            "total" => (double) $obj->total
        ];
        
    }
    
}