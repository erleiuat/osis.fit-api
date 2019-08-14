<?php

class User extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";

    /* ----------- PUBLIC PARAMS ---------- */
    protected $keys = [
        'firstname', 'lastname', 'birthdate', 'height', 'gender',
        'aim_weight', 'aim_date'
    ];

    public $firstname;
    public $lastname;
    public $birthdate;
    public $height;
    public $gender;

    public $aim_weight;
    public $aim_date;

    /* ----------------- METHODS ---------------- */
    public function create($firstname, $lastname) {

        $vals = [
            'account_id' => $this->account->id, 
            'firstname' => $firstname,
            'lastname' => $lastname
        ];
        
        $result = $this->db->makeInsert($this->t_main, $vals);
        
        if ($result !== 1) throw new ApiException(500, 'user_create_error', get_class($this));
        
        return $this;

    }

    public function read($id = false) {

        $where = ['account_id' => ($id ?: $this->account->id)];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set($result[0]);
        return $this;

    }

    public function edit() {

        $where = ['account_id' => $this->account->id];

        $params = Core::mergeAssign([ 
            'firstname' => null,
            'lastname' => null,
            'gender' => null,
            'height' => null,
            'birthdate' => null,
        ], (array) $this->getObject());
        $changed = $this->db->makeUpdate($this->t_detail, $params, $where);

        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        $params = [ 
            'weight' => $this->aim_weight,
            'date' => $this->aim_date
        ];
        $changed = $this->db->makeUpdate($this->t_aim, $params, $where);
        
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }
    
    public function getObject($obj = false) {

        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        return (object) [
            "firstname" => $obj->firstname,
            "lastname" => $obj->lastname,
            "birthdate" => $obj->birthdate,
            "height" => ($obj->height ? (double) $obj->height : null),
            "gender" => $obj->gender,
            "aims" => [
                "weight" => ($obj->aim_weight ? (double) $obj->aim_weight : null),
                "date" => $obj->aim_date,
            ]
        ];
        
    }
    
}