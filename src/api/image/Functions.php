<?php

class Img {

    public static function getInfo($path){
        $info = getimagesize($path);
        return [
            "w" => $info[0],
            "h" => $info[1]
        ];
    }

    public static function generate($original, $params, $mime = 'jpeg', $path = false){

        if(!$path) $path = dirname($original);
        $path = $path ."/". $params['name'];

        if (!copy($original, $path)) throw new ApiException(500, 'img_copy', 'full');

        if ($mime === 'png') Img::convertToJPG($path);

        $info = Img::getInfo($path);

        $maxH = $maxW = false;
        if($info['w'] > $info['h']){
            if ($info['w'] > $params['w']) $maxW = $params['w'];
            else $maxW = $info['w'];
        } else {
            if ($info['h'] > $params['h']) $maxH = $params['h'];
            else $maxH = $info['h'];
        }

        $tmpImg = imagecreatefromjpeg($path);
        Img::createClone($tmpImg,[
            "w" => $info['w'], "h" => $info['h']
        ],[
            "w" => $maxW, "h" => $maxH
        ], $path);
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