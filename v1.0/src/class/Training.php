<?php

class Training extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "training";
    private $t_uses_e = "training_uses_exercise";
    private $v_search = "v_training_search";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'public', 'title', 'description'];

    public $id;
    public $public;

    public $title;
    public $description;

    public $exercises;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'account_id' => $this->account->id, 
            'title' => null,
            'description' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);

        $this->id = $this->db->conn->lastInsertId();        
        return $this;

    }

    public function addExercise($id, $repetitions) {

        $vals = [
            'training_id' => $this->id,
            'exercise_id' => $id,
            'repetitions' => $repetitions
        ];

        $this->db->makeInsert($this->t_uses_e, $vals);

    }

    public function read($id = false) {
        
        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));
        $this->set($result[0]);

        $where = ['training_id' => $this->id];
        $result = $this->db->makeSelect($this->t_uses_e, $where);

        $arr = [];
        foreach ($result as $value) {
            array_push($arr, ["id" => $value['exercise_id'], "repetitions" => $value['repetitions']]);
        }
        $this->exercises = $arr;

        return $this;

    }

    public function find($query, $account_id, $public) {

        if (!$public) {
            $where = ' WHERE 
            `account_id` = :account_id AND
            `search` LIKE :query
            ';
        } else {
            $where = ' WHERE 
            `public` IS TRUE AND
            `account_id` != :account_id AND
            `search` LIKE :query
            ';
        }

        $stmt = $this->db->prepare("
            SELECT id, title, description, user FROM 
        ".$this->v_search.$where);

        $this->db->bind($stmt, 
            [':account_id', ':query'], 
            [$account_id, "%".$query."%"]
        );
        
        $this->db->execute($stmt);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            "description" => $obj->description
        ];
        
    }
    
}