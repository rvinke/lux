<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Models\Lux;

$app->get('/', function() use ($app) {
    return $app->welcome();
});


$app->post('/lux', function() use ($app) {
    $lux = new Lux();

    $lux->lux = \Illuminate\Support\Facades\Input::get('lux_value');

    $lux->save();
});

$app->get('/lux', function() use ($app) {
    $lux = Lux::orderBy('id', 'desc')->first();

    echo $lux->lux;
});

$app->get('/all', function() use ($app) {
    $luxes = Lux::orderBy('id', 'desc')->limit(100)->get();

    foreach($luxes as $lux){
        echo $lux->created_at.': '.$lux->lux.'<br />';
    }
});