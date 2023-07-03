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


$router->group(['middleware' => 'auth:user'], function($app) {

	$app->get('/transport/services', 'V1\Transport\User\RideController@services');

	$app->post('/transport/estimate', 'V1\Transport\User\RideController@estimate');

	$app->post('/transport/send/request', 'V1\Transport\User\RideController@create_ride');

	$app->get('/transport/check/request', 'V1\Transport\User\RideController@status');

	$app->get('/transport/request/{id}', 'V1\Transport\User\RideController@checkRide');

	$app->post('/transport/cancel/request', 'V1\Transport\User\RideController@cancel_ride');

	$app->post('/transport/extend/trip', 'V1\Transport\User\RideController@extend_trip');

	$app->post('/transport/rate', 'V1\Transport\User\RideController@rate'); 

    $app->post('/transport/payment', 'V1\Transport\User\RideController@payment');

    $app->post('/transport/update/payment', 'V1\Transport\User\RideController@update_payment_method');

    // $app->get('/trips', 'V1\Transport\User\HomeController@trips');
	// $app->get('/trips/{id}', 'V1\Transport\User\HomeController@gettripdetails');
	$app->get('/trips-history/transport', 'V1\Transport\User\HomeController@trips');
	$app->get('/trips-history/transport/{id}', 'V1\Transport\User\HomeController@gettripdetails');
	// $app->get('/upcoming/trips/transport', 'V1\Transport\User\HomeController@upcoming_trips');
	$app->get('/upcoming/trips/transport/{id}', 'V1\Transport\User\HomeController@getupcomingtrips');
	// $app->get('/upcoming/trips', 'V1\Transport\User\HomeController@upcoming_trips');
	// $app->get('/upcoming/trips/{id}', 'V1\Transport\User\HomeController@getupcomingtrips');
	$app->post('/ride/dispute', 'V1\Transport\User\HomeController@ride_request_dispute');
	$app->post('/ride/lostitem', 'V1\Transport\User\HomeController@ride_lost_item');
	$app->get('/ride/dispute', 'V1\Transport\User\HomeController@getUserdisputedetails');
	$app->get('/ride/disputestatus/{id}', 'V1\Transport\User\HomeController@get_ride_request_dispute');
	$app->get('/ride/lostitem/{id}', 'V1\Transport\User\HomeController@get_ride_lost_item');
	
});