<?php

class Training extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "training";
    private $t_uses_e = "training_uses_exercise";
    private $v_search = "v_training_search";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'public', 'title', 'description', 'exercises'];

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
            'public' => null,
            'description' => null
        ], (array) $this->getObject());
        $this->db->makeInsert($this->t_main, $vals);
        $this->id = $this->db->conn->lastInsertId();      
        
        if(count($this->exercises) > 0){

            $sql = "
            INSERT INTO ".$this->t_uses_e." 
            (`training_id`, `exercise_id`, `repetitions`) VALUES 
            ";

            $i = 0;
            $values = [];
            foreach ($this->exercises as $exercise) {
                array_push($values, [$this->id, $exercise->id, $exercise->repetitions]);
                if ($i > 0) $sql .= ", ";
                $sql .= "(?, ?, ?)";
                $i++;
            }

            $stmt = $this->db->prepare($sql);
            for ($x = 0; $x < $i; $x++) {
                $stmt->bindValue($x*3+1, $values[$x][0]);
                $stmt->bindValue($x*3+2, $values[$x][1]);
                $stmt->bindValue($x*3+3, $values[$x][2]);
            }
            $this->db->execute($stmt);
            
        }
        
        return $this;

    }

    public function read($id = false) {
        
        $where = ['id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        if ($result[0]["account_id"] !== $this->account->id) {
            if ($result[0]["public"] === false) throw new ApiException(401, 'item_not_public', get_class($this));
            else {
                // TODO?
            }
        }

        $this->set($result[0]);

        $where = ['training_id' => $this->id];
        $result = $this->db->makeSelect($this->t_uses_e, $where);

        $arr = [];
        foreach ($result as $value) {
            array_push($arr, ["id" => (int)$value['exercise_id'], "repetitions" => (int)$value['repetitions']]);
        }
        $this->exercises = $arr;

        return $this;

    }

    public function edit($id = false) {

        $where = ['account_id' => $this->account->id, 'id' => ($id ?: $this->id)];
        $vals = Core::mergeAssign([
            'title' => null,
            'public' => null,
            'description' => null
        ], (array) $this->getObject());
        
        $changed = $this->db->makeUpdate($this->t_main, $vals, $where);
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));
        
        $where = ['training_id' => $this->id];
        $changed = $this->db->makeDelete($this->t_uses_e, $where);

        if(count($this->exercises) > 0){

            $sql = "
            REPLACE INTO ".$this->t_uses_e." 
            (`training_id`, `exercise_id`, `repetitions`) VALUES 
            ";

            $i = 0;
            $values = [];
            foreach ($this->exercises as $exercise) {
                array_push($values, [$this->id, $exercise->id, $exercise->repetitions]);
                if ($i > 0) $sql .= ", ";
                $sql .= "(?, ?, ?)";
                $i++;
            }

            $stmt = $this->db->prepare($sql);
            for ($x = 0; $x < $i; $x++) {
                $stmt->bindValue($x*3+1, $values[$x][0]);
                $stmt->bindValue($x*3+2, $values[$x][1]);
                $stmt->bindValue($x*3+3, $values[$x][2]);
            }
            $this->db->execute($stmt);
            
        }

        return $this;

    }

    public function find($query, $account_id, $public) {

        $where = '`query` LIKE :query';
        if ($public) $where .= ' AND `account_id` != :account_id AND `public` IS TRUE';
        else $where .= ' AND `account_id` = :account_id';

        $stmt = $this->db->prepare("
            SELECT 
            id, title, user, account_id, description,
            account_image_id, account_image_name, account_image_mime, 
            account_image_full, account_image_small, account_image_lazy 
            FROM 
        ".$this->v_search." WHERE ".$where);

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

    public function getSearchObject($obj, $own = true, $Image = false) {
        
        if (!$obj) $obj = $this;
        else if (is_object($obj)) $obj = (array) $obj;

        $img = false;
        if ($obj['account_image_name'] && $Image) {
            $img = $Image->getObject([
                "account_id" => $obj['account_id'],
                "id" => (int) $obj['account_image_id'],
                "name" => $obj['account_image_name'],
                "mime" => $obj['account_image_mime'],
                "full" => $obj['account_image_full'],
                "small" => $obj['account_image_small'],
                "lazy" => $obj['account_image_lazy'],
                "large" => null,
                "medium" => null
            ]);
        }

        return (object) [
            "id" => (int) $obj['id'],
            "title" => $obj['title'],
            "description" => $obj['description'],
            "user" => $obj['user'],
            "image" => $img
        ];
        
    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        return (object) [
            "id" => (int) $obj->id,
            "public" => (int) $obj->public,
            "title" => $obj->title,
            "description" => $obj->description,
            "exercises" => $obj->exercises
        ];
        
    }
    
}