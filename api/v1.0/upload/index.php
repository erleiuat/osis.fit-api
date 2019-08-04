<?php

define('PROCESS', "Upload"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */
include_once LOCATION . 'src/Image.php'; /* Load Image-Methods */

// ------------------ SCRIPT -----------------
try {

    // INIT
    $sec = Sec::auth($_LOG);
    $Image = new Image($_DBC, $sec);
    $img = new Bulletproof\Image($_FILES);
    if(!$img["image"]) throw new ApiException(403, 'img_upload_image_missing');
    
    $img->setSize(100, 500 * 1000000); // Min. 100 Byte, Max. 500 MegaByte (0.5 GB)
    $img->setDimension(10000, 10000); 
    $img->setMime(['jpeg', 'png']);
    
    $name = date('Y_m_d_H_i_s-').$img->getName();
    $path = Env::api_static_path."/".Env::api_name."/".hash('ripemd160', $sec->id)."/".$name;
    if (!is_dir($path)) mkdir($path, 0777, true);
    
    $img->setName("original");
    $img->setLocation($path);
    if (!$img->upload()) throw new ApiException(500, 'img_upload_error_full', $img->getError());

    $mime = $img->getMime();
    $original = $path."/original.".$mime;
    if ($mime === 'jpeg') $Image->correctOrientation($original);

    $Image->set([
        'name' => $name,
        'mime' => $mime
    ])->create();

    $_REP->addData((int) $Image->id, "id");
    $_REP->addData($Image->getObject(), "item");

} catch (\Exception $e) {
    Core::processException($_REP, $_LOG, $e); 
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------

unset($img);
unset($sec);
unset($_REP);

try {

    $done = [
        "full" => true,
        "xl" => false,
        "lg" => false,
        "md" => false,
        "sm" => false,
        "xs" => false,
        "lazy" => false
    ];

    $origin = $path."/origin.jpeg";
    if (!copy($original, $origin)) throw new ApiException(500, 'img_copy', 'origin');
    if ($mime === 'png') $Image->convertToJPG($origin);

    list($oWidth, $oHeight, $type, $attr) = getimagesize($origin);
    if($oWidth > $oHeight){
        $maxWidth = ($oWidth > 2500 ? 2500 : $oWidth);
        $maxHeight = false;
    } else {
        $maxHeight = ($oHeight > 2500 ? 2500 : $oHeight);
        $maxWidth = false;
    }

    $tmpOrigin = imagecreatefromjpeg($origin);
    $Image->createClone(
        $tmpOrigin, [$oWidth, $oHeight], 
        [$maxWidth, $maxHeight],
        $origin
    );
    imagedestroy($tmpOrigin);

    list($oW, $oH, $type, $attr) = getimagesize($origin);

    $origin = imagecreatefromjpeg($origin);

    $versions = [
        ['xl', ($oW < 2400 ? $oW : 2400), 86],
        ['lg', ($oW < 1800 ? $oW : 1800), 88],
        ['md', ($oW < 1200 ? $oW : 1200), 92],
        ['sm', ($oW < 900 ? $oW : 900), 95],
        ['xs', ($oW < 600 ? $oW : 600), 100],
        ['lazy', ($oW < 500 ? $oW : 500), 70]
    ];

    foreach ($versions as $v) {
        $Image->createClone(
            $origin, [$oW, $oH], 
            [$v[1], false, $v[2]], 
            $path."/".$v[0].".jpeg"
        );
        $done[$v[0]] = true;
    }

} catch (\Exception $e) {
    $_LOG->addInfo($e);
}

$Image->setSizes($done);

$_LOG->write();