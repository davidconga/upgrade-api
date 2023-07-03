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
	
	$app->get('/shoptype', 'V1\Order\Provider\OrderController@shoptype'); 
	$app->get('/check/order/request', 'V1\Order\Provider\OrderController@index');
	$app->post('/update/order/request', 'V1\Order\Provider\OrderController@updateOrderStaus');
	$app->patch('/update/order/request', 'V1\Order\Provider\OrderController@updateOrderStaus');
	$app->post('/cancel/order/request', 'V1\Order\Provider\OrderController@createDispute');
	$app->post('/rate/order', 'V1\Order\Provider\OrderController@rate');
	$app->get('/history/order', 'V1\Order\Provider\OrderController@historyList');
	$app->get('/history/order/{id}', 'V1\Order\Provider\OrderController@getOrderHistorydetails');
	$app->get('/order/disputestatus/{id}', 'V1\Order\Provider\OrderController@getOrderRequestDispute');
	$app->post('/history-dispute/order', 'V1\Order\Provider\OrderController@saveOrderRequestDispute');
	$app->get('/getdisputedetails', 'V1\Order\Provider\OrderController@getdisputedetails');
	$app->get('/order/dispute', 'V1\Order\Provider\OrderController@getdisputedetails');
});