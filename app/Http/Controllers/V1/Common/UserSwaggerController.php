<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;

class UserSwaggerController extends Controller
{
		
	/* *********************************************************************
	*	USER PROFILE 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/profile",
	*	operationId="api.v1.user.profile",
	*	tags={"User"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns profile details of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	USER PROFILE UPDATE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/profile",
	*	operationId="api.v1.user.profile.update",
	*	tags={"User"},
	*	description="User Profile Update",
	*	@OA\RequestBody(
	*		description="User Profile Update",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserProfileUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserProfileUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserProfileUpdate", 
	*	@OA\Property(property="first_name", type="string", example="User" ),
	*	@OA\Property(property="last_name", type="string", example="Demo" ),
	*	@OA\Property(property="mobile", type="integer", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="user@demo.com" ),
	*	@OA\Property(property="language", type="string", enum={"en", "ar"}  ),
	*	@OA\Property(property="country_id", type="string", example="231" ),
	*	@OA\Property(property="city_id", type="string", example="48294" ),
	*	@OA\Property(property="picture", type="string", format="binary", example="" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserProfileUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserProfileUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="first_name", type="string"),
	*		@OA\Property(property="last_name", type="string"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="country_id", type="integer"),
	*		@OA\Property(property="city_id", type="integer"),
	*		@OA\Property(property="picture", type="string"),
	*		@OA\Property(property="language", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER CHANGE PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/password",
	*	operationId="api.v1.user.password.update",
	*	tags={"User"},
	*	description="User password Update",
	*	@OA\RequestBody(
	*		description="User Profile Update",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserPasswordUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Password changed successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserPasswordUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserPasswordUpdate", 
	* 	required={"old_password","password","password_confirmation"}, 
	*	@OA\Property(property="old_password", type="string", example="123456" ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="password_confirmation", type="string", example="1234567" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserPasswordUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserPasswordUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="old_password", type="string"),
	*		@OA\Property(property="password", type="string"),
	*		@OA\Property(property="password_confirmation", type="string"))
	*	}
	* )
	*/


	/* *********************************************************************
	*	USER UPDATE LANGUAGE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/updatelanguage",
	*	operationId="api.v1.user.updatelanguage.update",
	*	tags={"User"},
	*	description="User Language Update",
	*	@OA\RequestBody(
	*		description="User Language Update",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserLanguageUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Language updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserLanguageUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserLanguageUpdate", 
	* 	required={"language"}, 
	*	@OA\Property(property="language", type="string", example="en" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserLanguageUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserLanguageUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="language", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER MENUS 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/menus",
	*	operationId="api.v1.user.menus",
	*	tags={"User"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns menus available of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*   USER COUNTRIES
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/countries",
	*	operationId="/user/countries",
	*	tags={"User"},
	*	description="User countries",
	*	@OA\RequestBody(
	*		description="User countries",
	*		@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/UserCountriesInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns user countries",
	*		@OA\JsonContent(ref="#/components/schemas/UserCountries")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	* 	security={ {"User": {}} },
	*	)
	*/
	/**
	*@OA\Schema(schema="UserCountries", 
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="UserCountriesInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserCountries"),
	*	@OA\Schema(
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER REASONS FOR ADMIN SERVICES
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/reasons?type={type}",
	*	operationId="api.v1.user.reasons",
	*	tags={"User"},	
	*	@OA\Parameter(
	*		name="type",
	*		in="path",
	*		description="ADMIN SERVICE NAME (TRANSPORT / SERVICE / ORDER)",
	*		required=true,
	*		@OA\Schema(type="string", example="TRANSPORT")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns user reasons for admin services",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER NOTIFICATIONS 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/notification",
	*	operationId="api.v1.user.notification",
	*	tags={"User"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns notification of logged in user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*   USER DEVICE TOKEN
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/device_token",
	*	operationId="api.v1.user.devicetoken.update",	
	*	tags={"User"},
	*	description="User Update device token",
	*	@OA\RequestBody(
	*		description="User device token Input to update in db",
	*		@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/UserDeviceTokenInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserDeviceToken")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	*	)
	*/
	/**
	*@OA\Schema(schema="UserDeviceToken", 
	*	@OA\Property(property="device_token", type="string"))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="UserDeviceTokenInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserDeviceToken"),
	*	@OA\Schema(
	*		@OA\Property(property="device_token", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER CARD GET
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/card",
	*	operationId="api.v1.user.card",
	*	tags={"User Card"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns card details of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	USER CARD ADD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/card",
	*	operationId="api.v1.user.card.add",
	*	tags={"User Card"},
	*	description="User card Add",
	*	@OA\RequestBody(
	*		description="User Profile Add",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UsercardUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Add card for user",
	*		@OA\JsonContent(ref="#/components/schemas/UserCardUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserCardUpdate", 
	* 	required={"stripe_token"}, 
	*	@OA\Property(property="stripe_token", type="string", example="" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UsercardUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserCardUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="stripe_token", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER CARD DELETE
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/user/card/{id}",
	*	operationId="api.v1.user.card.id",
	*	tags={"User Card"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="user card id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="card deleted successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER WALLET DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/wallet",
	*	operationId="api.v1.user.wallet",
	*	tags={"User Wallet"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns wallet transactions of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER ADD MONEY
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/add/money",
	*	operationId="api.v1.user.add.money",
	*	tags={"User Wallet"},
	*	description="User Add Money",
	*	@OA\RequestBody(
	*		description="User Add Money",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserAddMoneyInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Saving money in user wallet",
	*		@OA\JsonContent(ref="#/components/schemas/UserAddMoney")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserAddMoney", 
	* 	required={"amount","card_id","user_type"}, 
	*	@OA\Property(property="amount", type="integer", example="1000" ),
	*	@OA\Property(property="card_id", type="integer",example="1",description="selected card id" ),
	*	@OA\Property(property="payment_mode", type="string", example="card" ),
	*	@OA\Property(property="user_type", type="string", example="user" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserAddMoneyInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserAddMoney"),
	*	@OA\Schema(
	*		@OA\Property(property="amount", type="integer"),
	*		@OA\Property(property="card_id", type="integer"),
	*		@OA\Property(property="payment_mode", type="string"),
	*		@OA\Property(property="user_type", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER LIST ADDRESS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/address",
	*	operationId="api.v1.user.address",
	*	tags={"User Manage Address"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns addresses of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	USER ADD ADDRESS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/address/add",
	*	operationId="api.v1.user.add.address",
	*	tags={"User Manage Address"},
	*	description="User Add Address",
	*	@OA\RequestBody(
	*		description="User Add Address",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserAddAddressInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Address added to user",
	*		@OA\JsonContent(ref="#/components/schemas/UserAddAddress")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserAddAddress", 
	* 	required={"address_type","latitude","longitude","map_address"}, 
	*	@OA\Property(property="address_type", type="string", example="Other" ),
	*	@OA\Property(property="landmark", type="string",example="Prestige Palladium" ),
	*	@OA\Property(property="flat_no", type="string", example="1" ),
	*	@OA\Property(property="street", type="string", example="Anna salai" ),
	*	@OA\Property(property="latitude", type="string", example="9.123" ),
	*	@OA\Property(property="longitude", type="string", example="9.123" ),
	*	@OA\Property(property="map_address", type="string", example="Prestige, Anna salai, chennai" ),
	*	@OA\Property(property="title", type="string", example="" ))
	* 
	*/ 
	/**
	* @OA\Schema(
	*	schema="UserAddAddressInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserAddAddress"),
	*	@OA\Schema(
	*		@OA\Property(property="address_type", type="string"),
	*		@OA\Property(property="landmark", type="string"),
	*		@OA\Property(property="flat_no", type="string"),
	*		@OA\Property(property="street", type="string"),
	*		@OA\Property(property="latitude", type="string"),
	*		@OA\Property(property="longitude", type="string"),
	*		@OA\Property(property="map_address", type="string" ),
	*		@OA\Property(property="title", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER UPDATE ADDRESS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/address/update",
	*	operationId="api.v1.user.update.address",
	*	tags={"User Manage Address"},
	*	description="User Update Address",
	*	@OA\RequestBody(
	*		description="User Update Address",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserUpdateAddressInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Address added to user",
	*		@OA\JsonContent(ref="#/components/schemas/UserUpdateAddress")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserUpdateAddress", 
	* 	required={"id","address_type","latitude","longitude","map_address"}, 
	*	@OA\Property(property="id", type="integer", example="4" ),
	*	@OA\Property(property="address_type", type="string", example="Other" ),
	*	@OA\Property(property="landmark", type="string",example="Prestige Square" ),
	*	@OA\Property(property="flat_no", type="string", example="1" ),
	*	@OA\Property(property="street", type="string", example="Anna salai" ),
	*	@OA\Property(property="latitude", type="string", example="9.123" ),
	*	@OA\Property(property="longitude", type="string", example="9.123" ),
	*	@OA\Property(property="map_address", type="string", example="Prestige, Anna salai, chennai" ),
	*	@OA\Property(property="_method", type="string", example="PATCH" ))
	* 
	*/ 
	/**
	* @OA\Schema(
	*	schema="UserUpdateAddressInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserUpdateAddress"),
	*	@OA\Schema(
	*		@OA\Property(property="id", type="integer", example="4" ),
	*		@OA\Property(property="address_type", type="string"),
	*		@OA\Property(property="landmark", type="string"),
	*		@OA\Property(property="flat_no", type="string"),
	*		@OA\Property(property="street", type="string"),
	*		@OA\Property(property="latitude", type="string"),
	*		@OA\Property(property="longitude", type="string"),
	*		@OA\Property(property="map_address", type="string" ),
	*		@OA\Property(property="_method", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER ADDRESS DELETE
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/user/address/{id}",
	*	operationId="api.v1.user.address.id",
	*	tags={"User Manage Address"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="user address id",
	*		required=true,
	*		@OA\Schema(type="integer", example="6")),
	*	@OA\Response(
	*		response="200",
	*		description="Address deleted successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER PROMOCODES 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/promocode/{adminService}",
	*	operationId="api.v1.user.promocode.serviceName",
	*	tags={"User"},	
	*	@OA\Parameter(
	*		name="adminService",
	*		in="path",
	*		description="admin service name. TRANSPORT / ORDER / SERVICE",
	*		required=true,
	*		@OA\Schema(type="string", example="SERVICE")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns user available promocodes for required service type",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER CHANGE CITY
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/city",
	*	operationId="api.v1.user.city.change",
	*	tags={"User"},
	*	description="User Change City",
	*	@OA\RequestBody(
	*		description="User Change City",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserChangeCityInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Address added to user",
	*		@OA\JsonContent(ref="#/components/schemas/UserChangeCity")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserChangeCity", 
	* 	required={"city_id"}, 
	*	@OA\Property(property="city_id", type="integer", example="4" ))
	* 
	*/ 
	/**
	* @OA\Schema(
	*	schema="UserChangeCityInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserChangeCity"),
	*	@OA\Schema(
	*		@OA\Property(property="city_id", type="integer", example="18422" ))
	*	}
	* )
	*/

	/* *********************************************************************
	*	USER DISPUTE LIST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/{adminService}/dispute",
	*	operationId="api.v1.user.adminService.dispute",
	*	tags={"Dispute List"},	
	*	@OA\Parameter(
	*		name="adminService",
	*		in="path",
	*		description="admin service name. ride / services / order",
	*		required=true,
	*		@OA\Schema(type="string", example="services")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns user's Dispute list",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
}