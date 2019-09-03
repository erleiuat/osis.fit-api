<?php

class Exercise extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "exercise";
    private $t_bodypart = "bodypart";
    private $t_use_bodypart = "exercise_uses_bodypart";
    private $v_use_bodypart = "v_exercise_bodypart";
    private $v_search = "v_exercise_search";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = [
        'id', 'public', 'title', 'description', 
        'type', 'calories', 'repetitions', 'bodyparts'
    ];

    public $id;
    public $public;

    public $title;
    public $description;
    public $type;
    public $calories;
    public $repetitions;
    public $bodyparts;

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
        
        if(count($this->bodyparts) > 0){

            $i = 0;
            $values = [];

            $sql = "
                INSERT INTO ".$this->t_use_bodypart." 
                (`exercise_id`, `bodypart_id`) VALUES 
            ";

            foreach ($this->bodyparts as $bodypart) {

                array_push($values, $this->id, $bodypart);
                if ($i > 0) $sql .= ", ";
                $sql .= "(?, ?)";
                $i++;

            }
            $sql .= ";";
            
            $stmt = $this->db->prepare($sql);
            for ($x = 0; $x < $i*2; $x++) {
                $stmt->bindValue($x+1, $values[$x]);
            }

            $this->db->execute($stmt);
            
        }
        
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

        $where = ['exercise_id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->v_use_bodypart, $where);
        $bodyparts = [];

        foreach ($result as $val) {
            array_push($bodyparts, (object) $val);
        }

        $this->set([
            "bodyparts" => $bodyparts
        ]);

        return $this;

    }

    public function find($query, $bodyparts, $account_id, $public) {

        $where = '
        `account_id` = :account_id AND
        `query` LIKE :query
        ';

        if ($public) $where .= ' AND `public` IS TRUE';

        $stmt = $this->db->prepare("
            SELECT 
            id, title, description, user, account_id, 
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
                "id" => $obj['account_image_id'],
                "name" => $obj['account_image_name'],
                "mime" => $obj['account_image_mime'],
                "full" => $obj['account_image_full'],
                "small" => $obj['account_image_small'],
                "lazy" => $obj['account_image_lazy'],
                "large" => null,
                "medium" => null
            ]);
        }

        return [
            "id" => $obj['id'],
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
            "type" => $obj->type,
            "calories" => (double) $obj->calories,
            "repetitions" => (double) $obj->repetitions,
            "bodyparts" => $obj->bodyparts,
        ];
        
    }
    
}