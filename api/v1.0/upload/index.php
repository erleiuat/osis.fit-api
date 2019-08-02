<?php

define('PROCESS', "Upload"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $img = new Bulletproof\Image($_FILES);
    if(!$img["image"]) throw new ApiException(403, 'upload_image_missing');
    
    $folder = hash('ripemd160', $sec->id);
    $path = Env::api_static_path."/".Env::api_name."/".$folder;
    if (!is_dir($path."/lazy/")) mkdir($path."/lazy/", 0777, true);

    $img->setLocation($path);
    $uFull = $img->upload();
    if(!$uFull) throw new ApiException(500, 'upload_error_full', $img->getError());

    $uLazy = $path."/lazy/".$uFull->getName().".".$uFull->getMime();
    if(!copy($uFull->getFullPath(), $uLazy)) throw new ApiException(500, 'upload_error_lazy_copy');
    
    bulletproof\utils\resize(
        $uLazy, 
        $img->getMime(),
        $img->getWidth(),
        $img->getHeight(),
        300, 300,
        true
    );

    include_once LOCATION . 'src/Image.php'; /* Load Image-Methods */
    $Image = new Image($_DBC, $sec);
    $Image->set([
        'name' => $uFull->getName(),
        'mime' => $uFull->getMime()
    ])->create();

    $_REP->addData($Image->id, "id");
    $_REP->addData($Image->getObject(), "object");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();