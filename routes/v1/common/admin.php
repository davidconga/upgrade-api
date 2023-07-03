<?php

$router->post('/login', 'V1\Common\Admin\Auth\AdminAuthController@login');

$router->post('/refresh', 'V1\Common\Admin\Auth\AdminAuthController@refresh');

$router->post('/forgotOtp', 'V1\Common\Admin\Auth\AdminAuthController@forgotPasswordOTP');
$router->post('/resetOtp', 'V1\Common\Admin\Auth\AdminAuthController@resetPasswordOTP');

$router->group(['middleware' => 'auth:admin'], function ($app) {

    $app->post('/permission_list', 'V1\Common\Admin\Auth\AdminAuthController@permission_list');

    $app->get('/users', 'V1\Common\Admin\Resource\UserController@index');

    $app->post('/users', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\UserController@store']);

    $app->get('/users/{id}', 'V1\Common\Admin\Resource\UserController@show');

    $app->patch('/users/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\UserController@update']);

    $app->delete('/users/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\UserController@destroy']);

    $app->get('/users/{id}/updateStatus', 'V1\Common\Admin\Resource\UserController@updateStatus');

    $app->get('/{type}/logs/{id}', 'V1\Common\CommonController@logdata');

    $app->get('/{type}/wallet/{id}', 'V1\Common\CommonController@walletDetails');

    $app->post('/logout', 'V1\Common\Admin\Auth\AdminAuthController@logout');

    $app->get('/services/main/list', 'V1\Common\CommonController@admin_services');

    $app->get('/services/list/{id}', 'V1\Common\Admin\Resource\ProviderController@provider_services');


    //Document
    $app->get('/document', 'V1\Common\Admin\Resource\DocumentController@index');

    $app->post('/document', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DocumentController@store']);

    $app->get('/document/{id}', 'V1\Common\Admin\Resource\DocumentController@show');

    $app->patch('/document/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DocumentController@update']);

    $app->delete('/document/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DocumentController@destroy']);

    $app->get('/document/{id}/updateStatus', 'V1\Common\Admin\Resource\DocumentController@updateStatus');

    //Notification
    $app->get('/notification', 'V1\Common\Admin\Resource\NotificationController@index');

    $app->post('/notification', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\NotificationController@store']);

    $app->get('/notification/{id}', 'V1\Common\Admin\Resource\NotificationController@show');

    $app->patch('/notification/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\NotificationController@update']);

    $app->delete('/notification/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\NotificationController@destroy']);


    //Reason
    $app->get('/reason', 'V1\Common\Admin\Resource\ReasonController@index');

    $app->post('/reason', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ReasonController@store']);

    $app->get('/reason/{id}', 'V1\Common\Admin\Resource\ReasonController@show');

    $app->patch('/reason/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ReasonController@update']);

    $app->delete('/reason/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ReasonController@destroy']);

    //Fleet
    $app->get('/fleet', 'V1\Common\Admin\Resource\FleetController@index');

    $app->post('/fleet', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\FleetController@store']);

    $app->get('/fleet/{id}', 'V1\Common\Admin\Resource\FleetController@show');

    $app->patch('/fleet/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\FleetController@update']);

    $app->delete('/fleet/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\FleetController@destroy']);

    $app->get('/fleet/{id}/updateStatus', 'V1\Common\Admin\Resource\FleetController@updateStatus');

    $app->post('/card', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\FleetController@addcard']);

    $app->get('card', 'V1\Common\Admin\Resource\FleetController@card');

    $app->post('add/money', 'V1\Common\Admin\Resource\FleetController@wallet');
    // $app->get('wallet', 'V1\Common\Admin\Resource\FleetController@wallet');
    $app->get('adminfleet/wallet', 'V1\Common\Admin\Resource\FleetController@wallet');

    //Dispatcher Panel
    $app->get('/dispatcher/trips', 'V1\Common\Admin\Resource\DispatcherController@trips');

    $app->get('/list', 'V1\Common\Admin\Resource\DispatcherController@providerServiceList');

    //Dispatcher
    $app->get('/dispatcher', 'V1\Common\Admin\Resource\DispatcherController@index');

    $app->post('/dispatcher', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DispatcherController@store']);

    $app->get('/dispatcher/{id}', 'V1\Common\Admin\Resource\DispatcherController@show');

    $app->patch('/dispatcher/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DispatcherController@update']);

    $app->delete('/dispatcher/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DispatcherController@destroy']);

    $app->get('/dispatcher/get/providers', 'V1\Common\Admin\Resource\DispatcherController@providers');

    $app->post('/dispatcher/assign', 'V1\Common\Admin\Resource\DispatcherController@assign');

    $app->post('/dispatcher/ride/request', 'V1\Common\Admin\Resource\DispatcherController@create_ride');

    $app->post('/dispatcher/ride/cancel', 'V1\Common\Admin\Resource\DispatcherController@cancel_ride');

    $app->post('/dispatcher/service/request', 'V1\Common\Admin\Resource\DispatcherController@create_service');

    $app->post('/dispatcher/service/cancel', 'V1\Common\Admin\Resource\DispatcherController@cancel_service');

    $app->get('/fare' , 'V1\Common\Admin\Resource\DispatcherController@fare');

    //Account Manager
    $app->get('/accountmanager', 'V1\Common\Admin\Resource\AccountManagerController@index');

    $app->post('/accountmanager', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AccountManagerController@store']);

    $app->get('/accountmanager/{id}', 'V1\Common\Admin\Resource\AccountManagerController@show');

    $app->patch('/accountmanager/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AccountManagerController@update']);

    $app->delete('/accountmanager/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AccountManagerController@destroy']);
    

    //Promocodes
    $app->get('/promocode', 'V1\Common\Admin\Resource\PromocodeController@index');

    $app->post('/promocode', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PromocodeController@store']);

    $app->get('/promocode/{id}', 'V1\Common\Admin\Resource\PromocodeController@show');

    $app->patch('/promocode/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PromocodeController@update']);

    $app->delete('/promocode/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PromocodeController@destroy']);

    //Geofencing
    $app->get('/geofence', 'V1\Common\Admin\Resource\GeofenceController@index');

    $app->post('/geofence', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\GeofenceController@store']);

    $app->get('/geofence/{id}', 'V1\Common\Admin\Resource\GeofenceController@show');

    $app->patch('/geofence/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\GeofenceController@update']);

    $app->get('/geofence/{id}/updateStatus', 'V1\Common\Admin\Resource\GeofenceController@updateStatus');

    $app->delete('/geofence/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\GeofenceController@destroy']);

    //Dispute
    $app->get('/dispute_list', 'V1\Common\Admin\Resource\DisputeController@index');

    $app->post('/dispute', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DisputeController@store']);

    $app->get('/dispute/{id}', 'V1\Common\Admin\Resource\DisputeController@show');

    $app->patch('/dispute/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DisputeController@update']);

    $app->delete('/dispute/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\DisputeController@destroy']);
    //Provider
    $app->get('/provider', 'V1\Common\Admin\Resource\ProviderController@index');

    $app->post('/provider', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@store']);

    $app->get('/provider/{id}', 'V1\Common\Admin\Resource\ProviderController@show');

    $app->patch('/provider/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@update']);

    $app->delete('/provider/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@destroy']);

    $app->get('/provider/{id}/updateStatus', 'V1\Common\Admin\Resource\ProviderController@updateStatus');
    $app->get('/provider/approve/{id}', 'V1\Common\Admin\Resource\ProviderController@approveStatus');
    $app->get('/provider/zoneapprove/{id}', 'V1\Common\Admin\Resource\ProviderController@zoneStatus');
    $app->post('/provider/addamount/{id}', ['uses' => 'V1\Common\Admin\Resource\ProviderController@addamount', 'middleware' => ['permission:provider-status']]);
   
    //sub admin

    $app->get('/subadminlist/{type}', 'V1\Common\Admin\Resource\AdminController@index');

    $app->post('/subadmin', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@store']);

    $app->get('/subadmin/{id}', 'V1\Common\Admin\Resource\AdminController@show');

    $app->patch('/subadmin/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@update']);

    $app->delete('/subadmin/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\AdminController@destroy']);

    $app->get('/subadmin/{id}/updateStatus', 'V1\Common\Admin\Resource\AdminController@updateStatus');

    

    $app->get('/heatmap', 'V1\Common\Admin\Resource\AdminController@heatmap');


    $app->get('/role_list', 'V1\Common\Admin\Resource\AdminController@role_list');
 
    //cmspages
    $app->get('/cmspage', 'V1\Common\Admin\Resource\CmsController@index');

    $app->post('/cmspage', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@store']);

    $app->get('/cmspage/{id}', 'V1\Common\Admin\Resource\CmsController@show');

    $app->patch('/cmspage/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@update']);

    $app->delete('/cmspage/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CmsController@destroy']);

    //custom push
    $app->get('/custompush', 'V1\Common\Admin\Resource\CustomPushController@index');

    $app->post('/custompush', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@store']);

    $app->get('/custompush/{id}', 'V1\Common\Admin\Resource\CustomPushController@show');

    $app->patch('/custompush/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@update']);

    $app->delete('/custompush/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CustomPushController@destroy']);

    //Provider add vehicle
    $app->get('/ProviderService/{id}', 'V1\Common\Admin\Resource\ProviderController@ProviderService');

    $app->patch('/vehicle_type', 'V1\Common\Admin\Resource\ProviderController@vehicle_type');

    $app->get('/service_on/{id}', 'V1\Common\Admin\Resource\ProviderController@service_on');

    $app->get('/service_off/{id}', 'V1\Common\Admin\Resource\ProviderController@service_off');

    $app->get('/deleteservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ProviderController@deleteservice']);
    //Provider view document
    $app->get('/provider/{id}/view_document', 'V1\Common\Admin\Resource\ProviderController@view_document');

    $app->get('/provider/approve_image/{id}', 'V1\Common\Admin\Resource\ProviderController@approve_image');

    $app->get('/provider/approveall/{id}', 'V1\Common\Admin\Resource\ProviderController@approve_all');

    $app->delete('/provider/delete_view_image/{id}', 'V1\Common\Admin\Resource\ProviderController@delete_view_image');
    //CompanyCountry
    $app->get('/providerdocument/{id}', 'V1\Common\Admin\Resource\ProviderController@providerdocument');

    $app->get('/companycountries', 'V1\Common\Admin\Resource\CompanyCountriesController@index');

    $app->post('/companycountries', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@store']);

    $app->get('/companycountries/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@show');

    $app->patch('/companycountries/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@update']);

    $app->delete('/companycountries/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCountriesController@destroy']);

    $app->get('/companycountries/{id}/updateStatus', 'V1\Common\Admin\Resource\CompanyCountriesController@updateStatus');

    $app->get('/companycountries/{id}/bankform', 'V1\Common\Admin\Resource\CompanyCountriesController@getBankForm');

    $app->post('/bankform', 'V1\Common\Admin\Resource\CompanyCountriesController@storeBankform');

    //country list
    $app->get('/countries', 'V1\Common\Admin\Resource\CompanyCountriesController@countries');
    $app->get('/states/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@states');
    $app->get('/cities/{id}', 'V1\Common\Admin\Resource\CompanyCountriesController@cities');
    $app->get('/company_country_list', 'V1\Common\Admin\Resource\CompanyCountriesController@companyCountries');
    $app->get('/vehicle_type_list', 'V1\Transport\Admin\VehicleController@vehicletype');
    //$app->get('/gettaxiprice/{id}', 'V1\Transport\Admin\VehicleController@gettaxiprice');

    //CompanyCity
    $app->get('/companycityservice', 'V1\Common\Admin\Resource\CompanyCitiesController@index');

    $app->post('/companycityservice', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@store']);

    $app->get('/companycityservice/{id}', 'V1\Common\Admin\Resource\CompanyCitiesController@show');

    $app->patch('/companycityservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@update']);

    $app->delete('/companycityservice/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\CompanyCitiesController@destroy']);

    $app->get('/countrycities/{id}', 'V1\Common\Admin\Resource\CompanyCitiesController@countrycities');
    
    //Account setting details
    $app->get('/profile', 'V1\Common\Admin\Resource\AdminController@show_profile');
    $app->post('/profile', 'V1\Common\Admin\Resource\AdminController@update_profile');

    Route::get('password', 'V1\Common\Admin\Resource\AdminController@password');
    Route::post('password', 'V1\Common\Admin\Resource\AdminController@password_update');

    $app->get('/adminservice', 'V1\Common\Admin\Resource\AdminController@admin_service');
    $app->get('/services/child/list/{id}', 'V1\Common\Admin\Resource\AdminController@child_service');
    $app->get('/heatmap', 'V1\Common\Admin\Resource\AdminController@heatmap');
    $app->get('/godsview', 'V1\Common\Admin\Resource\AdminController@godsview');


    //Admin Seeder
    $app->post('/companyuser', 'V1\Common\Admin\Resource\UserController@companyuser');

    $app->get('/settings', 'V1\Common\Admin\Auth\AdminController@index');

    $app->post('/settings', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Auth\AdminController@settings_store']);

    //Roles   
    $app->get('/roles', 'V1\Common\Admin\Resource\RolesController@index');

    $app->post('/roles', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\RolesController@store']);

    $app->get('/roles/{id}', 'V1\Common\Admin\Resource\RolesController@show');

    $app->patch('/roles/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\RolesController@update']);

    $app->delete('/roles/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\RolesController@destroy']);

    $app->get('/permission', 'V1\Common\Admin\Resource\RolesController@permission');
    //peakhours
    $app->get('/peakhour', 'V1\Common\Admin\Resource\PeakHourController@index');

    $app->post('/peakhour', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PeakHourController@store']);

    $app->get('/peakhour/{id}', 'V1\Common\Admin\Resource\PeakHourController@show');

    $app->patch('/peakhour/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PeakHourController@update']);

    $app->delete('/peakhour/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PeakHourController@destroy']);


    // ratings
    $app->get('/userreview', 'V1\Common\Admin\Resource\AdminController@userReview');

    $app->get('/providerreview', 'V1\Common\Admin\Resource\AdminController@providerReview');

    //Menu
    $app->get('/menu', 'V1\Common\Admin\Resource\MenuController@index');

    $app->post('/menu', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@store']);

    $app->get('/menu/{id}', 'V1\Common\Admin\Resource\MenuController@show');

    $app->patch('/menu/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@update']);

    $app->delete('/menu/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\MenuController@destroy']);

    $app->patch('/menucity/{id}', 'V1\Common\Admin\Resource\MenuController@menucity');
    $app->get('/ride_type', 'V1\Common\Admin\Resource\MenuController@ride_type');
    $app->get('/service_type', 'V1\Common\Admin\Resource\MenuController@service_type');

    $app->get('/order_type', 'V1\Common\Admin\Resource\MenuController@order_type');
    
    // $app->get('/getcity', 'V1\Common\Admin\Resource\MenuController@getcity');
    $app->get('/getCountryCity/{serviceId}/{CountryId}', 'V1\Common\Admin\Resource\MenuController@getCountryCity');
    $app->get('/getmenucity/{id}', 'V1\Common\Admin\Resource\MenuController@getmenucity');
    //payrolls
    $app->get('/zone', 'V1\Common\Admin\Resource\ZoneController@index');

    $app->post('/zone', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@store']);

    $app->get('/zone/{id}', 'V1\Common\Admin\Resource\ZoneController@show');

    $app->patch('/zone/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@update']);

    $app->delete('/zone/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\ZoneController@destroy']);

    $app->get('/zones/{id}/updateStatus', 'V1\Common\Admin\Resource\ZoneController@updateStatus');

    $app->get('/payroll-template', 'V1\Common\Admin\Resource\PayrollTemplateController@index');

    $app->post('/payroll-template', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@store']);

    $app->get('/payroll-template/{id}', 'V1\Common\Admin\Resource\PayrollTemplateController@show');

    $app->patch('/payroll-template/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@update']);

    $app->delete('/payroll-template/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollTemplateController@destroy']);

    $app->get('/payroll-templates/{id}/updateStatus', 'V1\Common\Admin\Resource\PayrollTemplateController@updateStatus');


    $app->get('/payroll', 'V1\Common\Admin\Resource\PayrollController@index');

    $app->post('/payroll', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@store']);

    $app->get('/payroll/{id}', 'V1\Common\Admin\Resource\PayrollController@show');

    $app->patch('/payroll/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@update']);

    $app->delete('/payroll/{id}', ['middleware' => 'demo', 'uses' => 'V1\Common\Admin\Resource\PayrollController@destroy']);

    $app->get('/payrolls/{id}/updateStatus', 'V1\Common\Admin\Resource\PayrollController@updateStatus');
    
    $app->post('/payroll/update-payroll', 'V1\Common\Admin\Resource\PayrollController@updatePayroll');

    $app->get('/zoneprovider/{id}', 'V1\Common\Admin\Resource\PayrollController@zoneprovider');
    $app->get('/payrolls/download/{id}', 'V1\Common\Admin\Resource\PayrollController@PayrollDownload');
    $app->get('/cityzones/{id}', 'V1\Common\Admin\Resource\ZoneController@cityzones');
    $app->get('/zonetype/{id}', 'V1\Common\Admin\Resource\ZoneController@cityzonestype');
    Route::get('bankdetails/template', 'V1\Common\Provider\HomeController@template');
    $app->post('/addbankdetails', 'V1\Common\Provider\HomeController@addbankdetails'); 
    $app->post('/editbankdetails', 'V1\Common\Provider\HomeController@editbankdetails'); 

    $app->get('/provider_total_deatils/{id}', 'V1\Common\Admin\Resource\ProviderController@provider_total_deatils');

     $app->get('/dashboard/{id}', 'V1\Common\Admin\Auth\AdminController@dashboarddata');


     $app->get('/statement/provider', 'V1\Common\Admin\Resource\AllStatementController@statement_provider');
     $app->get('/statement/user', 'V1\Common\Admin\Resource\AllStatementController@statement_user');
     $app->get('/transactions', 'V1\Common\Admin\Resource\AllStatementController@statement_admin');

     //search

      $app->get('/getdata', 'V1\Common\Admin\Resource\AllStatementController@getData');
      $app->get('/getfleetprovider', 'V1\Common\Admin\Resource\AllStatementController@getFleetProvider');
    
});

$router->get('/payrolls/download/{id}', 'V1\Common\Admin\Resource\PayrollController@PayrollDownload');
$router->get('/searchprovider/{id}', 'V1\Common\Admin\Resource\ProviderController@searchprovider');