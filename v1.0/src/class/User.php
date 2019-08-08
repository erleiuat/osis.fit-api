<?php

class User extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_detail = "user_detail";
    private $t_aim = "user_aim";
    private $v_info = "v_user_info";

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
    public function create() {

        $unique = uniqid('', true);
        $time = date('Y_m_d_H_i_s', time());
        $id = hash('ripemd160', $time.':'.$unique);

        $vals = [
            'id' => $id,
            'mail' => $this->user->mail, 
            'level' => $this->user->level,
        ];
        $result = $this->db->makeInsert($this->t_main, $vals);

        if ($result !== 1) throw new ApiException(500, 'user_create_error', get_class($this));
        
        $this->user->id = $id;

        $vals = Core::mergeAssign([
            'user_id' => $this->user->id, 
            'firstname' => null,
            'lastname' => null
        ], (array) $this->getObject());
        $result = $this->db->makeInsert($this->t_detail, $vals);
        
        if ($result !== 1) throw new ApiException(500, 'detail_create_error', get_class($this));
        
        $vals = ['user_id' => $this->user->id];
        $result = $this->db->makeInsert($this->t_aim, $vals);
        
        if ($result !== 1) throw new ApiException(500, 'aim_create_error', get_class($this));

        return $this;

    }

    public function read($id = false) {

        $where = ['id' => ($id ?: $this->user->id)];
        $result = $this->db->makeSelect($this->v_info, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set($result[0]);
        return $this;

    }

    public function edit() {

        $where = ['user_id' => $this->user->id];

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