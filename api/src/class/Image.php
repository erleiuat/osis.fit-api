<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "image";
    private $t_sizes = "image_sizes";
    private $v_info = "v_image_info";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = [
        'id', 'name', 'mime',
        'full', 'xl', 'lg', 'md', 'sm', 'xs', 'lazy'
    ];

    public $id;
    public $name;
    public $mime;

    public $full;
    public $xl;
    public $lg;
    public $md;
    public $sm;
    public $xs;
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
        return $this;
        
    }

    public function setSizes($sizes) {

        $sizes["image_id"] = $this->id;
        $this->db->makeInsert($this->t_sizes, $sizes);
        return $this;

    }

    public function read($id = false) {
        if(!$id) $id = $this->id;

        $where = ['user_id' => $this->user->id, 'id' => ($id ?: $this->id)];
        $result = $this->db->makeSelect($this->v_info, $where);

        if (count($result) !== 1) throw new ApiException(404, 'item_not_found', get_class($this));

        $this->set($result[0]);
        return $this;

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

        $url = Env::api_static_url."/".Env::api_name;
        $folder = hash('ripemd160', $this->user->id);
        $path = $url."/".$folder."/".$obj['name'];

        $files = [
            "xl" => null,
            "lg" => null,
            "md" => null,
            "sm" => null,
            "xs" => null,
            "lazy" => null
        ];

        $original = $path."/original.".$obj['mime'];
        $current = $original;
        foreach ($files as $key => $value) {
            if($obj[$key]) $current = $path."/".$key.".jpeg";
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

    public static function createClone($img, $sizes, $cloneInfo, $destination) {

        if($cloneInfo[1] === false) {
            $cloneInfo[1] = (int) ($cloneInfo[0] / $sizes[0] * $sizes[1]);
        }

        $tmpImg = imagecreatetruecolor($cloneInfo[0], $cloneInfo[1]);

        imagecopyresampled(
            $tmpImg, $img, 0,0,0,0,
            $cloneInfo[0],
            $cloneInfo[1],
            $sizes[0],
            $sizes[1]
        );

        imagejpeg($tmpImg, $destination, $cloneInfo[2]);
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