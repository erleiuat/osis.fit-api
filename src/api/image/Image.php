<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "image";
    private $t_sizes = "image_sizes";
    private $v_info = "v_image_info";

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
    
    public $upload_stamp;
    public $access_stamp;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vals = Core::mergeAssign([
            'user_id' => $this->user->id, 
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

        $where = ['user_id' => $this->user->id, 'id' => ($id ?: $this->id)];
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

        $where = ['user_id' => $this->user->id, 'id' => ($id ?: $this->id)];
        $changed = $this->db->makeDelete($this->t_main, $where);

        if ($changed < 1) throw new ApiException(404, 'item_not_found', get_class($this));
        else if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));
        return $this;

    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = (array) $this;
        else if (is_object($obj)) $obj = (array) $obj;

        $url = Env_img::url."/".Env_img::folder;
        $folder = hash('ripemd160', $this->user->id);
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

    public function getInfo($path){
        $info = getimagesize($path);
        return [
            "w" => $info[0],
            "h" => $info[1]
        ];
    }

    public function generate($original, $params, $mime = 'jpeg', $path = false){

        if(!$path) $path = dirname($original);
        $path = $path ."/". $params['name'];

        if (!copy($original, $path)) throw new ApiException(500, 'img_copy', 'full');

        if ($mime === 'png') $this->convertToJPG($path);

        $info = $this->getInfo($path);

        $maxH = $maxW = false;
        if($info['w'] > $info['h']){
            if ($info['w'] > $params['w']) $maxW = $params['w'];
            else $maxW = $info['w'];
        } else {
            if ($info['h'] > $params['h']) $maxH = $params['h'];
            else $maxH = $info['h'];
        }

        $tmpImg = imagecreatefromjpeg($path);
        $this->createClone(
            $tmpImg, 
            [
                "w" => $info['w'], 
                "h" => $info['h']
            ], 
            [
                "w" => $maxW, 
                "h" => $maxH
            ],
            $path
        );
        imagedestroy($tmpImg);

        return $path;

    }

    public static function createClone($img, $info, $clone, $path) {


        if ($clone["h"] === false) {
            $clone["h"] = (int) ($clone["w"] / $info["w"] * $info["h"]);
        } 
        
        if ($clone["w"] === false) {
            $clone["w"] = (int) ($clone["h"] / $info["h"] * $info["w"]);
        }

        if (!isset($clone["q"])) $clone["q"] = 100;

        $tmpImg = imagecreatetruecolor($clone["w"], $clone["h"]);

        imagecopyresampled(
            $tmpImg, $img, 0,0,0,0,
            $clone["w"],
            $clone["h"],
            $info["w"],
            $info["h"]
        );

        imagejpeg($tmpImg, $path, $clone["q"]);
        imagedestroy($tmpImg);

    }

    public static function convertToJPG($file) {
        $image = imagecreatefrompng($file);
        $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, TRUE);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);
        imagejpeg($bg, $file, 100);
        imagedestroy($bg);
    }

    public static function correctOrientation($filename) {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) $img = imagerotate($img, $deg, 0);
                    imagejpeg($img, $filename, 100);
                }
            }
        }
    }
    
}