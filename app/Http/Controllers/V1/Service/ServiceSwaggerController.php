<?php

namespace App\Http\Controllers\V1\Service;

use App\Http\Controllers\Controller;

class ServiceSwaggerController extends Controller
{

	/* *********************************************************************
	*	CHOOSE CATEGORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/service_category",
	*	operationId="apiv1.user.service_category",
	*	tags={"Service"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns Service Main Categories",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	CHOOSE SUB CATEGORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/service_sub_category/{id}",
	*	operationId="apiv1.user.service_sub_category",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Service Category ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	USER ACCOUNT PROVIDER LIST REVIEW
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/review/{id}",
	*	operationId="apiv1.user.review.id",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Provider ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns provider reviews",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	CHOOSE SERVICE
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/services/{id}/{ids}",
	*	operationId="apiv1.user.services",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Category ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Parameter(
	*		name="ids",
	*		in="path",
	*		description="Sub Category ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	AVAILABLE PROVIDERS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/list",
	*	operationId="apiv1.user.store.cusines",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="id",
	*		in="query",
	*		description="Service ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Parameter(
	*		name="lat",
	*		in="query",
	*		description="Latitude",
	*		required=true,
	*		@OA\Schema(type="string", example="13.0389694")
	*	),
	*	@OA\Parameter(
	*		name="long",
	*		in="query",
	*		description="Longitude",
	*		required=true,
	*		@OA\Schema(type="string", example="80.2095246"  )
	*	),
	*	@OA\Parameter(
	*		name="name",
	*		in="query",
	*		description="Provider Name",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	CREATE REQUEST
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/send/request",
	*	operationId="apiv1.user.service.send.request",
	*	tags={"Service"},
	*	description="Add to Cart",
	*	@OA\RequestBody(
	*		description="Add to Cart",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/CreateServiceInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/CreateService")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="CreateService", 
	*	required={"service_id", "s_latitude", "s_longitude", "payment_mode", "id"},
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="service_id", type="integer" ),
	*	@OA\Property(property="s_latitude", type="string", example="13.0389694" ),
	*	@OA\Property(property="s_longitude", type="string", example="80.2095246" ),
	*	@OA\Property(property="payment_mode", type="string", example="CASH", enum={"CASH", "CARD"} ),
	*	@OA\Property(property="schedule_date", type="string" ),
	*	@OA\Property(property="schedule_time", type="string" ),
	*	@OA\Property(property="use_wallet", type="integer" ),
	*	@OA\Property(property="promocode_id", type="integer" ),
	*	@OA\Property(property="allow_description", type="string"),
	*	@OA\Property(property="allow_image", type="string" ),
	*	@OA\Property(property="price", type="string" ),
	*	@OA\Property(property="quantity", type="string" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="CreateServiceInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/CreateService"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer", description="Provider ID"),
	*			@OA\Property(property="service_id", type="integer", description="Service ID"),
	*			@OA\Property(property="s_longitude", type="string", description="User Latitude" ),
	*			@OA\Property(property="s_latitude", type="string", description="User Longitude" ),
	*			@OA\Property(property="payment_mode", type="string" ),
	*			@OA\Property(property="schedule_date", type="string" ),
	*			@OA\Property(property="schedule_time", type="string" ),
	*			@OA\Property(property="use_wallet", type="string" ),
	*			@OA\Property(property="promocode_id", type="integer" ),
	*			@OA\Property(property="allow_description", type="string"),
	*			@OA\Property(property="allow_image", type="string"),
	*			@OA\Property(property="price", type="string"),
	*			@OA\Property(property="quantity", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	CHECK REQUEST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/service/check/request",
	*	operationId="apiv1.user.service.check",
	*	tags={"Service"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	CHECK REQUEST BY ID
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/service/request/{id}",
	*	operationId="apiv1.user.service.request.id",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="service request id",
	*		required=true,
	*		@OA\Schema(type="integer")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	CANCEL REQUEST
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/cancel/request",
	*	operationId="apiv1.user.service.cancel.request",
	*	tags={"Service"},
	*	description="Cancel Service",
	*	@OA\RequestBody(
	*		description="Cancel Service",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserCancelServiceInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserCancelService")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserCancelService", 
	*	required={"id", "reason"},
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="reason", type="string" ),
	*	@OA\Property(property="cancel_reason_opt", type="string"))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserCancelServiceInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserCancelService"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer", description="service request id"),
	*			@OA\Property(property="reason", type="string", description="reasons list select dropdown value"),
	*			@OA\Property(property="cancel_reason_opt", type="string", description="optional. when choosing 'others' in cancel_reason this box will appear" ))
	*	}
	*)
	*/

	/* *********************************************************************
	*	UPDATE PAYMENT METHOD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/update/payment",
	*	operationId="apiv1.user.service.update.payment",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="Update Payment Method",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/ServiceUpdatePaymentInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ServiceUpdatePayment")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ServiceUpdatePayment", 
	*	required={"id", "payment_mode"}, 
	*	@OA\Property(property="id", type="string", example="13.0389694" ),
	*	@OA\Property(property="payment_mode", type="string", example="CARD", enum={"CASH", "CARD"}  ),
	*	@OA\Property(property="card_id", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ServiceUpdatePaymentInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ServiceUpdatePayment"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="payment_mode", type="string"),
	*			@OA\Property(property="card_id", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	PAYMENT
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/payment",
	*	operationId="apiv1.user.service.payment",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="User Payment",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/UserServicePaymentInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UserServicePayment")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserServicePayment", 
	*	required={"id"}, 
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="tips", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserServicePaymentInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserServicePayment"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
	*			@OA\Property(property="tips", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/rate",
	*	operationId="apiv1.user.service.rate",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="User Rating",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/UserServiceRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UserServiceRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserServiceRating", 
	*	required={"id", "rating"}, 
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="rating", type="integer" ),
	*	@OA\Property(property="comment", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserServiceRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserServiceRating"),
	*		@OA\Schema(
	*		required={"id", "rating"}, 
	*		@OA\Property(property="id", type="string"),
	*		@OA\Property(property="rating", type="integer"),
	*		@OA\Property(property="comment", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	CHECK STATUS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/check/serve/request",
	*	operationId="apiv1.provider.serve.check.request",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="latitude",
	*		in="query",
	*		description="Provider Current Latitude",
	*		required=true,
	*		@OA\Schema(type="string", example="13.0389694")),
	*	@OA\Parameter(
	*		name="longitude",
	*		in="query",
	*		description="Provider Current Longitude",
	*		required=true,
	*		@OA\Schema(type="string", example="80.2095246")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	CANCEL SERVICE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/cancel/serve/request",
	*	operationId="apiv1.provider.cancel.serve.request",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="Cancel Ride",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/ProviderCancelServiceInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderCancelService")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderCancelService", 
	*	required={"id", "admin_service", "reason"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="admin_service", type="string", example="SERVICE", enum={"TRANSPORT", "ORDER","SERVICE"} ),
	*	@OA\Property(property="cancel", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderCancelServiceInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderCancelService"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
	*			@OA\Property(property="admin_service", type="string"),
	*			@OA\Property(property="cancel", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	UPDATE SERVICE
	**********************************************************************/


	/**
	*@OA\Post(
	*	path="/api/v1/provider/update/serve/request",
	*	operationId="apiv1.provider.update.serve.request",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="Update Status",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/UpdateServiceRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UpdateServiceRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UpdateServiceRequest", 
	*	required={"_method", "id", "status"}, 
	*	@OA\Property(property="_method", type="string", default="PATCH" ),
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="status", type="string", example="ARRIVED", enum={"STARTED", "ARRIVED", "PICKEDUP", "DROPPED", "COMPLETED", "PAYMENT"} ),
	*	@OA\Property(property="otp", type="string", example="" ),
	*	@OA\Property(property="before_picture", type="string", format="binary" ),
	*	@OA\Property(property="extra_charge", type="string" ),
	*	@OA\Property(property="extra_charge_notes", type="string" ),
	*	@OA\Property(property="after_picture", type="string", format="binary" ),
	*	@OA\Property(property="distance", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UpdateServiceRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UpdateServiceRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="_method", type="string"),
	*			@OA\Property(property="id", type="string", description="Service ID"),
	*			@OA\Property(property="status", type="string"),
	*			@OA\Property(property="otp", type="string", description="During PICKEDUP"),
	*			@OA\Property(property="before_picture", type="string", description="During PICKEDUP"),
	*			@OA\Property(property="extra_charge", type="string", description="During DROPPED"),
	*			@OA\Property(property="extra_charge_notes", type="string", description="Service ID", description="During DROPPED"),
	*			@OA\Property(property="after_picture", type="string", description="Service ID", description="During DROPPED"),
	*			@OA\Property(property="distance", type="string", description="During DROPPED")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/rate/serve",
	*	operationId="apiv1.provider.rate.serve",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/ProviderServiceRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderServiceRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderServiceRating", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="rating", type="string", example="5" ),
	*	@OA\Property(property="comment", type="string", example="Test" ),
	*	@OA\Property(property="admin_service", type="string", example="SERVICE", enum={"TRANSPORT", "ORDER","SERVICE"}  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderServiceRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderServiceRating"),
	*		@OA\Schema(
	*			required={"id", "rating", "admin_service"}, 
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="rating", type="integer"),
	*			@OA\Property(property="comment", type="string"),
	*			@OA\Property(property="admin_service", type="string" )
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	USER TRIPS HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/trips-history/service?",
	*	operationId="api.v1.user.trips.history.service",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="limit",
	*		in="query",
	*		description="limit",
	*		required=false,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Parameter(
	*		name="offset",
	*		in="query",
	*		description="offset",
	*		required=false,
	*		@OA\Schema(type="integer", example="0")),
	*	@OA\Parameter(
	*		name="type",
	*		in="query",
	*		description="past / current / history . if past, 'CANCELLED','COMPLETED'. if history, 'SCHEDULED' else all current requests.",
	*		required=false,
	*		@OA\Schema(type="string", example="past")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Transport Service list history of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER TRIPS HISTORY DETAIL VIEW
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/trips-history/service/{id}",
	*	operationId="api.v1.user.trips.history.service.id",
	*	tags={"Service"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Service request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular Service history detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	PROVIDER TRIPS HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/history/service?",
	*	operationId="api.v1.provider.history.service",
	*	tags={"Service"},
	*	@OA\Parameter(
	*		name="limit",
	*		in="query",
	*		description="limit",
	*		required=false,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Parameter(
	*		name="offset",
	*		in="query",
	*		description="offset",
	*		required=false,
	*		@OA\Schema(type="integer", example="0")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Service ride list history of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER TRIPS HISTORY DETAIL VIEW
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/history/service/{id}",
	*	operationId="api.v1.provider.history.service.id",
	*	tags={"Service"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Service request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular Service history detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


    /* *********************************************************************
	*	USER DISPUTE SAVE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/service/dispute",
	*	operationId="api.v1.user.service.dispute",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="User Service Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/UserServiceDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserServiceDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserServiceDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="user" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserServiceDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserServiceDispute"),
	*		@OA\Schema(
	*			required={"id", "dispute_type", "user_id","provider_id","dispute_name"}, 
	*			@OA\Property(property="id", type="integer",description="Service request id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="provider_id", type="integer" ),
	*           @OA\Property(property="dispute_name", type="string" ),
	*			@OA\Property(property="comments", type="string")
	*			
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	PROVIDER DISPUTE SAVE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/history-dispute/service",
	*	operationId="api.v1.provider.service.dispute",
	*	tags={"Service"},
	*	@OA\RequestBody(
	*		description="Provider Service Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/ProviderServiceDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderServiceDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderServiceDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="provider" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderServiceDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderServiceDispute"),
	*		@OA\Schema(
	*			required={"id", "dispute_type", "user_id","provider_id","dispute_name"}, 
	*			@OA\Property(property="id", type="integer",description="Service request id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="provider_id", type="integer" ),
	*           @OA\Property(property="dispute_name", type="string" ),
	*			@OA\Property(property="comments", type="string")
	*			
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER DISPUTE STATUS DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/service/disputestatus/{id}",
	*	operationId="api.v1.user.services.disputestatus.id",
	*	tags={"Service"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="service request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular service Dispute status detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	PROVIDER DISPUTE STATUS DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/service/disputestatus/{id}",
	*	operationId="api.v1.provider.services.disputestatus.id",
	*	tags={"Service"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="service request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular service Dispute detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/

}