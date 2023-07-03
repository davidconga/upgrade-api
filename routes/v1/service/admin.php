<?php

$router->group(['middleware' => 'auth:admin'], function ($app) {

    $app->group(['prefix'=>'service'], function($app){
        // SERVICE MAIN CATEGORIES
        $app->get('/categories', 'V1\Service\Admin\ServiceCategoryController@index');
         $app->get('/categorieslist', 'V1\Service\Admin\ServiceCategoryController@indexlist');

        $app->post('/categories', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceCategoryController@store']);

        $app->get('/categories/{id}', 'V1\Service\Admin\ServiceCategoryController@show');

        $app->patch('/categories/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceCategoryController@update']);

        $app->delete('/categories/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceCategoryController@destroy']);

        $app->get('/categories/{id}/updateStatus', 'V1\Service\Admin\ServiceCategoryController@updateStatus');

        // SERVICE SUB CATEGORIES
        $app->get('/categories-list', 'V1\Service\Admin\ServiceSubCategoryController@categoriesList');

        $app->get('/subcategories', 'V1\Service\Admin\ServiceSubCategoryController@index');

        $app->post('/subcategories', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceSubCategoryController@store']);

        $app->get('/subcategories/{id}', 'V1\Service\Admin\ServiceSubCategoryController@show');

        $app->patch('/subcategories/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceSubCategoryController@update']);

        $app->delete('/subcategories/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServiceSubCategoryController@destroy']);

        $app->get('/subcategories/{id}/updateStatus', 'V1\Service\Admin\ServiceSubCategoryController@updateStatus');

        // SERVICES
        $app->get('/subcategories-list/{categoryId}', 'V1\Service\Admin\ServicesController@subcategoriesList');

        $app->get('/listing', 'V1\Service\Admin\ServicesController@index');

        $app->post('/listing', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServicesController@store']);

        $app->get('/listing/{id}', 'V1\Service\Admin\ServicesController@show');

        $app->patch('/listing/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServicesController@update']);

        $app->delete('/listing/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServicesController@destroy']);

        $app->get('/listing/{id}/updateStatus', 'V1\Service\Admin\ServicesController@updateStatus');

        $app->get('/get-service-price/{id}', 'V1\Service\Admin\ServicesController@getServicePriceCities'); 

        $app->post('/pricings', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\ServicesController@servicePricePost']);

        $app->get('/pricing/{service_id}/{city_id}', 'V1\Service\Admin\ServicesController@getServicePrice');
        // Dispute
        $app->post('dispute-service-search', 'V1\Service\User\ServiceController@searchServiceDispute');

        $app->get('/requestdispute', 'V1\Service\Admin\RequestDisputeController@index');

        $app->post('/requestdispute', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\RequestDisputeController@store']);

        $app->get('/requestdispute/{id}', 'V1\Service\Admin\RequestDisputeController@show');

        $app->patch('/requestdispute/{id}', ['middleware' => 'demo', 'uses' => 'V1\Service\Admin\RequestDisputeController@update']);

        $app->get('disputelist', 'V1\Service\Admin\RequestDisputeController@dispute_list');
        //request history
        $app->get('/requesthistory', 'V1\Service\User\ServiceController@requestHistory');

        $app->get('/requestschedulehistory', 'V1\Service\User\ServiceController@requestScheduleHistory');

        $app->get('/requesthistory/{id}', 'V1\Service\User\ServiceController@requestHistoryDetails');

        $app->get('/servicedocuments/{id}', 'V1\Service\User\ServiceController@webproviderservice');

        $app->get('/Servicedashboard/{id}', 'V1\Service\Admin\ServicesController@dashboarddata');

        $app->get('/requestStatementhistory', 'V1\Service\User\ServiceController@requestStatementHistory');
    
    });

    $app->get('user-search', 'V1\Common\User\HomeController@search_user');

    $app->get('provider-search', 'V1\Common\Provider\HomeController@search_provider');

     $app->get('getservicecity', 'V1\Service\User\ServiceController@getcity');
    
});
