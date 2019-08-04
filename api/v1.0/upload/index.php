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
    
    $user = hash('ripemd160', $sec->id);
    $name = date('Y_m_d_H_i_s-').$img->getName();
    $path = Env::api_static_path."/".Env::api_name."/".$user."/".$name;
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

    $versions = [
        ['xl', 2000, 80],
        ['lg', 1800, 80],
        ['md', 1200, 80],
        ['sm', 900, 80],
        ['xs', 600, 10],
        ['lazy', 300, 50]
    ];

    $origin = imagecreatefromjpeg($origin);
    foreach ($versions as $v) {
        $Image->createClone(
            $origin, 
            [$oWidth, $oHeight], 
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