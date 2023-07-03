<?php

$router->group(['middleware' => 'auth:admin'], function ($app) {

		// vehile

    $app->get('/vehicle', 'V1\Transport\Admin\VehicleController@index');
    $app->get('/vehicle-list', 'V1\Transport\Admin\VehicleController@vehicleList');
    $app->get('/getvehicletype', 'V1\Transport\Admin\VehicleController@getvehicletype');

		$app->post('/vehicle', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleController@store']);

		$app->get('/vehicle/{id}', 'V1\Transport\Admin\VehicleController@show');

		$app->patch('/vehicle/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleController@update']);

		$app->post('/vehicle/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleController@destroy']);

		$app->get('/transport/price/get/{id}', 'V1\Transport\Admin\VehicleController@gettaxiprice');

		$app->get('/vehicle/{id}/updateStatus', 'V1\Transport\Admin\VehicleController@updateStatus');

		$app->get('/comission/{country_id}/{city_id}/{admin_service_id}', 'V1\Transport\Admin\VehicleController@getComission');
		
		$app->get('/gettaxiprice/{id}', 'V1\Transport\Admin\VehicleController@gettaxiprice');

		$app->post('/transport/track/request', 'V1\Transport\User\RideController@track_location');
		

		$app->post('/rideprice', 'V1\Transport\Admin\VehicleController@rideprice');

		$app->get('/rideprice/{ride_delivery_vehicle_id}/{city_id}', 'V1\Transport\Admin\VehicleController@getRidePrice');

		$app->post('/comission', 'V1\Transport\Admin\VehicleController@comission');

		// Lost Item
		$app->get('/lostitem', 'V1\Transport\Admin\LostItemController@index');

		$app->post('/lostitem', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\LostItemController@store']);

		$app->get('/lostitem/{id}', 'V1\Transport\Admin\LostItemController@show');

		$app->patch('/lostitem/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\LostItemController@update']);
		$app->delete('/lostitem/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\LostItemController@destroy']);


		$app->get('usersearch', 'V1\Transport\User\RideController@search_user');

		$app->get('userprovider', 'V1\Transport\User\RideController@search_provider');

		$app->post('ridesearch', 'V1\Transport\User\RideController@searchRideLostitem');

		$app->post('disputeridesearch', 'V1\Transport\User\RideController@searchRideDispute');


		// Ride Request Dispute
		$app->get('/requestdispute', 'V1\Transport\Admin\RideRequestDisputeController@index');

		$app->post('/requestdispute', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\RideRequestDisputeController@store']);

		$app->get('/requestdispute/{id}', 'V1\Transport\Admin\RideRequestDisputeController@show');

		$app->patch('/requestdispute/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\RideRequestDisputeController@update']);

		$app->get('disputelist', 'V1\Transport\Admin\RideRequestDisputeController@dispute_list');
				
		// request history
		$app->get('/requesthistory', 'V1\Transport\User\RideController@requestHistory');
		$app->get('/requestschedulehistory', 'V1\Transport\User\RideController@requestscheduleHistory');
		$app->get('/requesthistory/{id}', 'V1\Transport\User\RideController@requestHistoryDetails');
		$app->get('/requestStatementhistory', 'V1\Transport\User\RideController@requestStatementHistory');

		// vehicle type
		$app->get('/vehicletype', 'V1\Transport\Admin\VehicleTypeController@index');

		$app->post('/vehicletype', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleTypeController@store']);

		$app->get('/vehicletype/{id}', 'V1\Transport\Admin\VehicleTypeController@show');

		$app->patch('/vehicletype/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleTypeController@update']);

		$app->post('/vehicletype/{id}', ['middleware' => 'demo', 'uses' => 'V1\Transport\Admin\VehicleTypeController@destroy']);

		$app->get('/vehicletype/{id}/updateStatus', 'V1\Transport\Admin\VehicleTypeController@updateStatus');
		$app->get('/transportdocuments/{id}', 'V1\Transport\Admin\VehicleTypeController@webproviderservice');

		// statement
		$app->get('/statement', 'V1\Transport\User\RideController@statement');

		// Dashboard

		$app->get('transportdashboard/{id}', 'V1\Transport\Admin\RideRequestDisputeController@dashboarddata');

		 $app->get('gettransportcity', 'V1\Transport\Admin\VehicleController@getcity');



});
