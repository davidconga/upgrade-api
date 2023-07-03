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

	//For category service
	$app->get('/store/list/{id}', 'V1\Order\User\HomeController@store_list');
	$app->get('/store/cusines/{id}', 'V1\Order\User\HomeController@cusine_list');
	$app->get('/store/details/{id}', 'V1\Order\User\HomeController@store_details');
	//address
	$app->post('/store/address/add', 'V1\Common\User\HomeController@addmanageaddress');
	$app->get('/store/useraddress', 'V1\Order\User\HomeController@useraddress');
	$app->delete('/store/address/{id}', 'V1\Common\User\HomeController@deletemanageaddress');
	$app->get('/store/address/{id}', 'V1\Common\User\HomeController@editmanageaddress');
	//addons
	$app->get('/store/cart-addons/{id}', 'V1\Order\User\HomeController@cart_addons');
	$app->get('/store/show-addons/{id}', 'V1\Order\User\HomeController@show_addons');
	$app->post('/store/addcart', 'V1\Order\User\HomeController@addcart');
	$app->post('/store/removecart', 'V1\Order\User\HomeController@removecart');
	$app->get('/store/cartlist', 'V1\Order\User\HomeController@viewcart');
	$app->get('/store/promocodelist', 'V1\Order\User\HomeController@promocodelist');
	$app->post('/order/cancel/request', 'V1\Order\User\HomeController@cancelOrder');
	$app->post('/store/checkout', 'V1\Order\User\HomeController@checkout');
	$app->get('/store/check/request', 'V1\Order\User\HomeController@status');

	$app->get('/store/order/{id}', 'V1\Order\User\HomeController@orderdetails');
	$app->post('/store/order/rating', 'V1\Order\User\HomeController@orderdetailsRating');

	$app->get('/trips-history/order', 'V1\Order\User\HomeController@tripsList');
	$app->get('/trips-history/order/{id}', 'V1\Order\User\HomeController@getOrderHistorydetails');
	$app->get('/upcoming/trips/order', 'V1\Order\User\HomeController@tripsUpcomingList');
	$app->get('/upcoming/trips/order/{id}', 'V1\Order\User\HomeController@orderdetails');

	$app->get('/order/dispute', 'V1\Order\Provider\OrderController@getUserdisputedetails');
	$app->get('/order/search/{id}', 'V1\Order\User\HomeController@search');
	$app->get('/getUserdisputedetails', 'V1\Order\Provider\OrderController@getdisputedetails'); 
	$app->post('/order/dispute', 'V1\Order\User\HomeController@order_request_dispute');
	$app->get('/order/disputestatus/{id}', 'V1\Order\User\HomeController@get_order_request_dispute');
});