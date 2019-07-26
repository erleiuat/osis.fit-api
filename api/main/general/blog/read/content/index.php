<?php

define('PROCESS', "General/Blog/Read/Content"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Article.php';
$_Article = new Article($_DBC);

// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth(false);
    $_LOG->user_id = ($authUser ? $authUser->id : 'none');
    $data = Core::getData(['url']);

    $_Article->url = Validate::string($data->url,1,60);
    $_Article->publication_date = date('Y-m-d', time());

    $_REP->addContent("article", $_Article->readArticle());
    
} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync(); /* End Async-Request */

// -------------- AFTER RESPONSE -------------
$_LOG->write();