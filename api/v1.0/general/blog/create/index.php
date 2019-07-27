<?php

define('PROCESS', "General/Blog/Create"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Article.php';
$_Article = new Article($_DBC);

// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['url', 'title']);

    Sec::permit($auth->level, ['moderator', 'admin']);

    $_Article->user_id = $auth->id;   
    $_Article->url = Validate::string($data->url, 1);    
    $_Article->title = Validate::string($data->title, 1);

    $_Article->publication_date = ( isset($data->publicationDate) ? Validate::date($data->publicationDate) : NULL );
    $_Article->content = ( isset($data->content) ? Validate::string($data->content) : NULL );
    $_Article->language = ( isset($data->language) ? Validate::string($data->language) : NULL );
    $_Article->color = ( isset($data->color) ? Validate::string($data->color) : NULL );
    $_Article->dark = ( isset($data->dark) ? Validate::string($data->dark) : NULL );
    $_Article->description = ( isset($data->description) ? Validate::string($data->description) : NULL );
    $_Article->img_url = ( isset($data->img_url) ? Validate::string($data->img_url) : NULL );
    $_Article->img_lazy = ( isset($data->img_lazy) ? Validate::string($data->img_lazy) : NULL );
    $_Article->img_phrase = ( isset($data->img_phrase) ? Validate::string($data->img_phrase) : NULL );

    if(isset($data->keywords) && count($data->keywords) > 0 ){
        $str = implode(", ", $data->keywords);
        $_Article->keywords = Validate::string($str);
    } else {
        $_Article->keywords = NULL;
    }

    $_Article->create();
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();