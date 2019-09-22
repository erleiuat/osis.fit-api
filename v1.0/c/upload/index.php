<?php

define('PROCESS', "Upload"); /* Name of this Process */
define('ROOT', "../../../src/"); /* Path to root */      
define('REC', "../../src/"); /* Path to classes of current version */ /* Path to root */        


require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */
require_once ROOT . 'Image.php'; /* Load Image-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $Image = new Image($_DBC, $sec);
    $img = new Bulletproof\Image($_FILES);
    if(!$img["image"]) throw new ApiException(403, 'img_upload_image_missing');
    
    $img->setSize(Env_img::size["min"], Env_img::size["max"]);
    $img->setDimension(10000, 10000); 
    $img->setMime(['jpeg', 'png']);
    
    $dir = date('Y_m_d_H_i_s');
    $path = ROOT ."/". Env_img::path."/".Env_img::folder."/".hash('ripemd160', $sec->id)."/".$dir;
    
    if(!mkdir($path, 0777, true)) throw new ApiException(403, 'dir_creation_failed');

    $img->setName("original");
    $img->setLocation($path);

    if (!$img->upload()) {
        if($img->getSize() >= Env_img::size["max"]){
            throw new ApiException(500, 'img_upload_error_full', 'file_too_large');
        } else {
            throw new ApiException(500, 'img_upload_error_full', $img->getError());
        }
    }
    
    $mime = $img->getMime();
    $original = $path."/original";
    unset($img);

    rename ($original.".".$mime, $original);
    //if ($mime === 'jpeg') Img::correctOrientation($original);
    
    $full = Img::generate($original, Env_img::full, $mime);
    Img::generate($full, Env_img::lazy);

    $Image->set([
        'name' => $dir,
        'mime' => $mime
    ])->create();

    $Image->setSizes([
        'full' => true, 
        'lazy' => true
    ]);
    
    $_REP->addData((int) $Image->id, "id");
    $_REP->addData($Image->getObject(), "item");

} catch (\Exception $e) {
    Core::processException($_REP, $_LOG, $e); 
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);
unset($sec);
unset($_REP); // TODO Unset All method
unset($original);
unset($mime);
unset($dir);
unset($path);

// -------------- AFTER RESPONSE -------------

try {

    $done = [];
    
    Img::generate($full, Env_img::large);
    $done["large"] = true;

    Img::generate($full, Env_img::medium);
    $done["medium"] = true;

    Img::generate($full, Env_img::small);
    $done["small"] = true;

} catch (\Exception $e) {
    $_LOG->addInfo($e);
}

$Image->setSizes($done);

$_LOG->write();