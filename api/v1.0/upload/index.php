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
    
    $mime = $img->getMime();
    $name = $img->getName();
    
    $folder = hash('ripemd160', $sec->id);
    $path = Env::api_static_path."/".Env::api_name."/".$folder."/".$name;

    if (!is_dir($path)) mkdir($path, 0777, true);
    
    $img->setLocation($path);
    $uFULL = $img->upload();
    if (!$uFULL) throw new ApiException(500, 'img_upload_error_full', $img->getError());
    $current = $path."/".$name.".".$mime;
    if ($mime === 'jpeg') $Image->correctOrientation($current);

    $width = $img->getWidth();
    $height = $img->getHeight();

    $Image->set([
        'name' => $name,
        'mime' => $mime
    ])->create();

    $_REP->addData((int) $Image->id, "id");
    $_REP->addData($Image->getObject(), "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);
// -------------- AFTER RESPONSE -------------
    
try {

    $done = [
        "xl" => false,
        "lg" => false,
        "md" => false,
        "sm" => false,
        "xs" => false,
        "lazy" => false,
    ];

    $origin = $path."/origin.jpeg";
    if (!copy($current, $origin)) throw new ApiException(500, 'img_copy', 'xl');
    if ($mime === 'png') $Image->convertToJPG($origin);

    $current = $path."/xl.jpeg";
    if (!copy($origin, $current)) throw new ApiException(500, 'img_copy', 'xl');

    try {
        bulletproof\utils\resize(
            $current, 'jpeg',
            $width, $height,
            2000, 2000,
            true, true, false,
            ['jpeg' => ['fallback' => 80]]
        );
    } catch (\Exception $e) {
        throw new ApiException(500, 'img_resize_fail:xl:', $e->getMessage());
    }

    $done["xl"] = true;

    $versions = [
        ['lg', 1800, 1800],
        ['md', 1200, 1200],
        ['sm', 900, 900],
        ['xs', 600, 600],
    ];

    // Create Versions
    foreach ($versions as $v) {
        try {
            $new = $path."/".$v[0].".jpeg";
            if (!copy($origin, $new)) throw new ApiException(500, 'img_copy', $v[0]);
            bulletproof\utils\resize(
                $new, 'jpeg',
                $width, $height,
                $v[1], $v[2],
                true, true, false,
                ['jpeg' => ['fallback' => 80]]
            );
            $done[$v[0]] = true;
        } catch (\Exception $e) {
            throw new ApiException(500, 'img_resize_fail:'.$v[0].':', $e->getMessage());
        }
    }

    // Create Lazy
    $new = $path."/lazy.jpeg";
    if (!copy($origin, $new)) throw new ApiException(500, 'img_copy', 'lazy');
    bulletproof\utils\resize(
        $new, 'jpeg',
        $width, $height,
        300, 300, true,
        ['jpeg' => ['fallback' => 50]]
    );

    $done["lazy"] = true;

} catch (\Exception $e) {
    $_LOG->addInfo($e);
    print_r($e);
    die();
}

$done["full"] = true;
$Image->setSizes($done);

//Core::endAsync($_REP);
$_LOG->write();