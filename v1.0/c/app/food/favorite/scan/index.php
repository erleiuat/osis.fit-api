<?php

define('PROCESS', "App/Food/Favorite/Scan"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody(['code' => ['string', true]]);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $url = "https://world.openfoodfacts.org/api/v0/product/".$data->code.".json";

    $response = file_get_contents($url);

    $response = json_decode($response);

    $_REP->addData($response->product->product_name, "content");

    /*
    $arr = [];

    foreach($response->products as $product){
        if(isset($product->nutriments)) {
            $nut = $product->nutriments;

            $serving_size = 100;
            $energy_unit = null;

            $energy_100g = null;
            $energy = null;
            $energy_serving = null;

            if (isset($product->serving_size)) {
                if ($product->serving_size > 0) $serving_size = intval($product->serving_size);
            }
            if (isset($nut->energy_unit)) $energy_unit = $nut->energy_unit;

            if (isset($nut->energy_100g)) $energy_100g = $nut->energy_100g;
            else break;

            if($energy_unit === "kJ") {
                $energy_100g = round($energy_100g / 4.184, 2);
            }

            $caloriesPer100 = ($energy_100g > 0 ? $energy_100g : null);
            $amount = $serving_size;
            $calories = round($serving_size * ($energy_100g/100), 2);

            if ($calories <= 0) $calories = null;

            $tmp = [
                "id" => $product->code,
                "title" => $product->product_name,
                "image" => (isset($product->image_url) ? $product->image_url : null),
                "caloriesPer100" => $caloriesPer100,
                "amount" => $amount,
                "total" => $calories
            ];

            array_push($arr, $tmp);

        }

    }

    $_REP->addData(count($arr), "total");
    $_REP->addData($arr, "items");

    */

} catch (\Exception $e) { 
    Core::processException($_REP, $_LOG, $e); 
    $_LOG->write();
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------