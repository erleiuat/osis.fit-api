<?php

define('PROCESS', "Upload"); /* Name of this Process */
define('LOCATION', "../../../"); /* Path to root */      
define('REC', "../../src/class/"); /* Path to classes of current version */ /* Path to root */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */
include_once LOCATION . 'src/Image.php'; /* Load Image-Methods */

// ------------------ SCRIPT -----------------
try {

    $maxFileSize = 15 * 1000000;

    // INIT
    $sec = Sec::auth($_LOG);
    $Image = new Image($_DBC, $sec);
    $img = new Bulletproof\Image($_FILES);
    if(!$img["image"]) throw new ApiException(403, 'img_upload_image_missing');
    
    $img->setSize(50, $maxFileSize); // Min. 50 Byte, Max. 15 MegaByte (0.15 GB)
    $img->setDimension(10000, 10000); 
    $img->setMime(['jpeg', 'png']);
    
    $name = date('Y_m_d_H_i_s-').$img->getName();
    $path = Env_api::static_path."/".Env_api::name."/".hash('ripemd160', $sec->id)."/".$name;
    if (!is_dir($path)) mkdir($path, 0777, true);
    
    $img->setName("original");
    $img->setLocation($path);
    if (!$img->upload()) {
        if($img->getSize() >= $maxFileSize){
            throw new ApiException(500, 'img_upload_error_full', 'file_too_large');
        } else {
            throw new ApiException(500, 'img_upload_error_full', $img->getError());
        }
    }

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

    $done = [];
    
    $full = $path."/full.jpeg";
    if (!copy($original, $full)) throw new ApiException(500, 'img_copy', 'full');
    if ($mime === 'png') $Image->convertToJPG($full);

    list($oWidth, $oHeight, $type, $attr) = getimagesize($full);
    if($oWidth > $oHeight){
        $maxWidth = ($oWidth > 2500 ? 2500 : $oWidth);
        $maxHeight = false;
    } else {
        $maxHeight = ($oHeight > 2500 ? 2500 : $oHeight);
        $maxWidth = false;
    }

    $tmpOrigin = imagecreatefromjpeg($full);
    $Image->createClone(
        $tmpOrigin, [$oWidth, $oHeight], 
        [$maxWidth, $maxHeight],
        $full
    );
    imagedestroy($tmpOrigin);
    list($oW, $oH, $type, $attr) = getimagesize($full);
    $full = imagecreatefromjpeg($full);

    $done['full'] = true;

    $versions = [
        ['large', ($oW < 2400 ? $oW : 2400), 95],
        ['medium', ($oW < 1200 ? $oW : 1200), 80],
        ['small', ($oW < 650 ? $oW : 650), 80],
        ['lazy', ($oW < 300 ? $oW : 300), 60]
    ];

    foreach ($versions as $v) {
        $Image->createClone(
            $full, [$oW, $oH], 
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