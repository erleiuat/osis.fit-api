<?php

class User extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_detail = "user_detail";

    private $v_user = "v_user";

    /* ----------- PUBLIC PARAMS ---------- */
    protected $keys = [
        'firstname', 'lastname', 'image', 
        'birthdate', 'height', 'gender', 'pal',
        'aim_weight', 'aim_date'
    ];

    public $firstname;
    public $lastname;
    public $image;
    
    public $birthdate;
    public $height;
    public $gender;
    public $pal;

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
        
        $vals = [
            'account_id' => $this->account->id
        ];
        $result = $this->db->makeInsert($this->t_detail, $vals);
        if ($result !== 1) throw new ApiException(500, 'user_create_error', get_class($this));
        
        return $this;

    }

    public function read($id = false) {

        $where = ['account_id' => ($id ?: $this->account->id)];
        $result = $this->db->makeSelect($this->v_user, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set(Core::mergeAssign([
            'firstname' => null, 
            'lastname' => null, 
            'image' => $result[0]['image_id'],
            'birthdate' => null, 
            'height' => null, 
            'gender' => null, 
            'pal' => null, 
            'aim_weight' => null, 
            'aim_date' => null
        ], $result[0]));

        return $this;

    }

    public function editProfile() {

        $where = ['account_id' => $this->account->id];
        
        $params = Core::mergeAssign([ 
            'image_id' => (isset($this->image->id) ? $this->image->id : null),
            'firstname' => null,
            'lastname' => null
        ], (array) $this->getObject());

        $changed = $this->db->makeUpdate($this->t_main, $params, $where);
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }

    public function editAims() {

        $where = ['account_id' => $this->account->id];

        $params = Core::mergeAssign([
            'aim_weight' => $this->aim_weight,
            'aim_date' => $this->aim_date
        ], (array) $this->getObject());

        $changed = $this->db->makeUpdate($this->t_detail, $params, $where);
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }

    public function editMetabolism() {

        $where = ['account_id' => $this->account->id];

        $params = Core::mergeAssign([
            'gender' => null,
            'height' => null,
            'birthdate' => null,
            'pal' => null
        ], (array) $this->getObject());

        $changed = $this->db->makeUpdate($this->t_detail, $params, $where);
        if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }
    
    public function getObject($obj = false) {

        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        return (object) [
            "image" => (isset($obj->image) ? $obj->image : false),
            "firstname" => $obj->firstname,
            "lastname" => $obj->lastname,
            "birthdate" => $obj->birthdate,
            "height" => ($obj->height ? (double) $obj->height : null),
            "gender" => $obj->gender,
            "pal" => $obj->pal,
            "aims" => [
                "weight" => ($obj->aim_weight ? (double) $obj->aim_weight : null),
                "date" => $obj->aim_date,
            ]
        ];
        
    }
    
}