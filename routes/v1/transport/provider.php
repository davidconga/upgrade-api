<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->group(['middleware' => 'auth:provider'], function($app) {
	
	$app->get('/check/ride/request', 'V1\Transport\Provider\TripController@index');

	$app->patch('/update/ride/request', 'V1\Transport\Provider\TripController@update_ride');

	$app->post('/cancel/ride/request', 'V1\Transport\Provider\TripController@cancel_ride');

	$app->post('/rate/ride', 'V1\Transport\Provider\TripController@rate');

    $app->post('/transport/payment', 'V1\Transport\User\RideController@payment');

	$app->get('/history/transport', 'V1\Transport\Provider\TripController@trips');
	$app->get('/history/transport/{id}', 'V1\Transport\Provider\TripController@gettripdetails');
	$app->get('/ride/disputestatus/{id}', 'V1\Transport\Provider\TripController@get_ride_request_dispute');
	$app->post('/history-dispute/transport', 'V1\Transport\Provider\TripController@ride_request_dispute');
	 
	// $app->post('/ride_request_dispute/{id}', 'V1\Transport\Provider\TripController@ride_request_dispute');
	// $app->get('/get_ride_request_dispute/{id}', 'V1\Transport\Provider\TripController@get_ride_request_dispute');
	$app->get('/ride/dispute', 'V1\Transport\User\HomeController@getdisputedetails');
	$app->get('/ridetype', 'V1\Transport\Provider\HomeController@ridetype');


	$app->post('/waiting', 'V1\Transport\Provider\TripController@waiting');

	
});