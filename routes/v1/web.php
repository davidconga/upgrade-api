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

$router->get('/', function () use ($router) {
	//return $router->app->version();
    return view('index');
}); 

$router->post('verify', 'LicenseController@verify');


$router->post('base', 'V1\Common\CommonController@base');
$router->get('cmspage/{type}', 'V1\Common\CommonController@cmspagetype');

$router->group(['prefix' => 'api/v1'], function ($app) {

	$app->post('user/appsettings', 'V1\Common\CommonController@base');

	$app->post('provider/appsettings', 'V1\Common\CommonController@base');

	$app->get('countries', 'V1\Common\CommonController@countries_list');

	$app->get('states/{id}', 'V1\Common\CommonController@states_list');

	$app->get('cities/{id}', 'V1\Common\CommonController@cities_list');

	$app->post('/{provider}/social/login', 'V1\Common\SocialLoginController@handleSocialLogin');

	$app->post('/chat', 'V1\Common\CommonController@chat');

	$app->post('/provider/update/location', 'V1\Common\Provider\HomeController@update_location');

});

$router->get('/send/{type}/push', 'V1\Common\SocialLoginController@push');

$router->get('v1/docs', ['as' => 'swagger-v1-lume.docs', 'middleware' => config('swagger-lume.routes.middleware.docs', []), 'uses' => 'V1\Common\SwaggerController@docs']);

$router->get('/api/v1/documentation', ['as' => 'swagger-v1-lume.api', 'middleware' => config('swagger-lume.routes.middleware.api', []), 'uses' => 'V1\Common\SwaggerController@api']);
