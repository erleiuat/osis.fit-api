<?php

class Exercise extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "exercise";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'public', 'title', 'description', 'type', 'calories', 'repetitions'];

    public $id;
    public $public;

    public $title;
    public $description;
    public $type;
    public $calories;
    public $repetitions;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'account_id' => $this->account->id, 
            'title' => null,
            'description' => null,
            'type' => null,
            'calories' => null,
            'repetitions' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);

        $this->id = $this->db->conn->lastInsertId();        
        return $this;

    }

    public function read($id = false) {
        
        $where = ['id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        if ($result[0]["public"] === false) {
            if ($result[0]["account_id"] !== $this->account->id) throw new ApiException(401, 'item_not_public', get_class($this));
        }

        $this->set($result[0]);
        return $this;

    }

    public function readByDate($from = false, $to = false) {

        if(!$from) $from = '1990-01-01';
        if(!$to) $to = date('Y-m-d', time());

        // TODO ? makeSelect with "between"
        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            account_id = :account_id AND
            stamp >= CONCAT(:from, ' 00:00:00') AND 
            stamp <= CONCAT(:to, ' 23:59:59')
        ");

        $this->db->bind($stmt, 
            ['account_id', 'from', 'to'],
            [$this->account->id, $from, $to]
        )->execute($stmt);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            "id" => (int) $obj->id,
            "public" => (int) $obj->public,
            "title" => $obj->title,
            "description" => $obj->description,
            "type" => $obj->type,
            "calories" => (double) $obj->calories,
            "repetitions" => (double) $obj->repetitions
        ];
        
    }
    
}