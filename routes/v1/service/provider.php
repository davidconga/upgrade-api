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
	$app->get('/providerservice/categories', 'V1\Service\Provider\HomeController@categories');
	$app->post('/providerservice/subcategories', 'V1\Service\Provider\HomeController@subcategories');
	$app->post('/providerservice/service', 'V1\Service\Provider\HomeController@service');
	$app->get('/totalservices', 'V1\Service\Provider\HomeController@totalservices');
	$app->get('/check/serve/request', 'V1\Service\Provider\ServeController@index');
	$app->post('/update/serve/request', 'V1\Service\Provider\ServeController@updateServe');
	$app->patch('/update/serve/request', 'V1\Service\Provider\ServeController@updateServe');
	$app->post('/cancel/serve/request', 'V1\Service\Provider\ServeController@cancelServe');
	$app->post('/rate/serve', 'V1\Service\Provider\ServeController@rate');
	$app->get('/history/service', 'V1\Service\Provider\ServeController@historyList');
	$app->get('/history/service/{id}', 'V1\Service\Provider\ServeController@getServiceHistorydetails');
	$app->get('/service/disputestatus/{id}', 'V1\Service\Provider\ServeController@getServiceRequestDispute');
	$app->post('/history-dispute/service', 'V1\Service\Provider\ServeController@saveServiceRequestDispute');
	// $app->post('/service_request_dispute/{id}', 'V1\Service\Provider\ServeController@saveServiceRequestDispute');
	// $app->get('/get_service_request_dispute/{id}', 'V1\Service\Provider\ServeController@getServiceRequestDispute');
	$app->get('/services/dispute', 'V1\Service\Provider\ServeController@getdisputedetails');
	$app->get('/dispute/service', 'V1\Service\Provider\ServeController@getdisputedetails');
	
	
});