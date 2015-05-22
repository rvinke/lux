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
use Khill\Lavacharts\Lavacharts;


$app->get('/', function() use ($app) {
    return $app->welcome();
});


$app->get('/luxStore/{lux_value}', function($lux_value) use ($app) {
    $lux = new Lux();

    $lux->lux = $lux_value;

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

$app->get('/chart', function() use ($app) {


    $luxes = Lux::orderBy('id', 'desc')->limit(100)->get();

    $lava = new Lavacharts;

    $lx = $lava->DataTable();

    $lx->addDateColumn('Date')
        ->addNumberColumn('Lux');

    foreach($luxes as $lux){
        $lx->addRow(array($lux->created_at, $lux->lux));
    }

    $linechart = $lava->LineChart('Lux')
        ->dataTable($lx)
        ->title('Lichtsterkte');
    echo '<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script><body>';
    echo $lava->jsapi();
    echo '<div id="temps_div"></div>';
    echo $linechart->render('temps_div');
    echo '</body></html>';



});