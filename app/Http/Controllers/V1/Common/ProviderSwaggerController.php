<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;

class ProviderSwaggerController extends Controller
{
		
	/* *********************************************************************
	*	PROVIDER PROFILE 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/profile",
	*	operationId="api.v1.provider.profile",
	*	tags={"Provider"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns profile details of provider",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER PROFILE UPDATE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/profile",
	*	operationId="api.v1.provider.profile.update",
	*	tags={"Provider"},
	*	description="Provider Profile Update",
	*	@OA\RequestBody(
	*		description="Provider Profile Update",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderProfileUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderProfileUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderProfileUpdate", 
	*	@OA\Property(property="first_name", type="string", example="Provider" ),
	*	@OA\Property(property="last_name", type="string", example="Demo" ),
	*	@OA\Property(property="mobile", type="integer", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="provider@demo.com" ),
	*	@OA\Property(property="language", type="string", enum={"en", "ar"}  ),
	*	@OA\Property(property="country_id", type="string", example="231" ),
	*	@OA\Property(property="city_id", type="string", example="48294" ),
	*	@OA\Property(property="picture", type="string", format="binary", example="" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderProfileUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderProfileUpdate"),
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
	*	PROVIDER CHANGE PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/password",
	*	operationId="api.v1.provider.password.update",
	*	tags={"Provider"},
	*	description="Provider Password Update",
	*	@OA\RequestBody(
	*		description="Provider Change password",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderPasswordUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Password changed successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderPasswordUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderPasswordUpdate", 
	* 	required={"old_password","password","password_confirmation"}, 
	*	@OA\Property(property="old_password", type="string", example="123456" ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="password_confirmation", type="string", example="1234567" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderPasswordUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderPasswordUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="old_password", type="string"),
	*		@OA\Property(property="password", type="string"),
	*		@OA\Property(property="password_confirmation", type="string"))
	*	}
	* )
	*/


	/* *********************************************************************
	*	PROVIDER UPDATE LANGUAGE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/updatelanguage",
	*	operationId="api.v1.provider.updatelanguage.update",
	*	tags={"Provider"},
	*	description="Provider Language Update",
	*	@OA\RequestBody(
	*		description="Provider Language Update",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderLanguageUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Language updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderLanguageUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderLanguageUpdate", 
	* 	required={"language"}, 
	*	@OA\Property(property="language", type="string", example="en" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderLanguageUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderLanguageUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="language", type="string"))
	*	}
	* )
	*/



	/* *********************************************************************
	*	PROVIDER NOTIFICATIONS 
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/notification",
	*	operationId="api.v1.provider.notification",
	*	tags={"Provider"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns notification of logged in provider",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*   PROVIDER DEVICE TOKEN
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/device_token",
	*	operationId="api.v1.provider.devicetoken.update",	
	*	tags={"Provider"},
	*	description="Provider device token",
	*	@OA\RequestBody(
	*		description="Provider device token Input to update in db",
	*		@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderDeviceTokenInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderDeviceToken")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	*	)
	*/
	/**
	*@OA\Schema(schema="ProviderDeviceToken", 
	*	@OA\Property(property="device_token", type="string"))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderDeviceTokenInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderDeviceToken"),
	*	@OA\Schema(
	*		@OA\Property(property="device_token", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	PROVIDER CARD GET
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/card",
	*	operationId="api.v1.provider.card",
	*	tags={"Provider Card"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns card details of provider",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/

	/* *********************************************************************
	*	PROVIDER CARD ADD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/card",
	*	operationId="api.v1.provider.card.add",
	*	tags={"Provider Card"},
	*	description="Provider card Add",
	*	@OA\RequestBody(
	*		description="Provider Profile Add",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderCardUpdateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Add card for provider",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderCardUpdate")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderCardUpdate", 
	* 	required={"stripe_token"}, 
	*	@OA\Property(property="stripe_token", type="string", example="" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderCardUpdateInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderCardUpdate"),
	*	@OA\Schema(
	*		@OA\Property(property="stripe_token", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	PROVIDER CARD DELETE
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/provider/card/{id}",
	*	operationId="api.v1.provider.card.id",
	*	tags={"Provider Card"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="provider card id",
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
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER WALLET DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/wallet",
	*	operationId="api.v1.provider.wallet",
	*	tags={"Provider Wallet"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns wallet transactions of provider",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER ADD MONEY
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/add/money",
	*	operationId="api.v1.provider.add.money",
	*	tags={"Provider Wallet"},
	*	description="Provider Add Money",
	*	@OA\RequestBody(
	*		description="Provider Add Money",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderAddMoneyInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Saving money in user wallet",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderAddMoney")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderAddMoney", 
	* 	required={"amount","card_id","user_type"}, 
	*	@OA\Property(property="amount", type="integer", example="1000" ),
	*	@OA\Property(property="card_id", type="integer",example="1",description="selected card id" ),
	*	@OA\Property(property="payment_mode", type="string", example="card" ),
	*	@OA\Property(property="user_type", type="string", example="provider" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderAddMoneyInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderAddMoney"),
	*	@OA\Schema(
	*		@OA\Property(property="amount", type="integer"),
	*		@OA\Property(property="card_id", type="integer"),
	*		@OA\Property(property="payment_mode", type="string"),
	*		@OA\Property(property="user_type", type="string"))
	*	}
	* )
	*/



	/* *********************************************************************
	*	PROVIDER BANK DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/bankdetails/template",
	*	operationId="api.v1.provider.bankdetails.template",
	*	tags={"Provider Bank Detail"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns Bank detail template",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/



	/* *********************************************************************
	*	PROVIDER ADD BANK DETAILS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/addbankdetails",
	*	operationId="api.v1.provider.addbankdetails",
	*	tags={"Provider Bank Detail"},
	*	description="Provider Add Bank Details",
	*	@OA\RequestBody(
	*		description="Provider Add Bank Details",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderAddBankDetailsInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Created Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderAddBankDetails")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderAddBankDetails", 
	* 	required={"bankform_id","keyvalue"}, 
	*	@OA\Property(property="bankform_id[]", type="array", @OA\Items( type="integer") ),
	*	@OA\Property(property="keyvalue[]", type="array", @OA\Items( type="integer")) ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderAddBankDetailsInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderAddBankDetails"),
	*	@OA\Schema(
	*		@OA\Property(property="bankform_id[]", type="array", @OA\Items( type="integer"), example="1" ),
	*		@OA\Property(property="keyvalue[]", type="array", @OA\Items( type="integer"), example="2323232" ))
	*	}
	* )
	*/


	/* *********************************************************************
	*	PROVIDER EDIT BANK DETAILS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/editbankdetails",
	*	operationId="api.v1.provider.editbankdetails",
	*	tags={"Provider Bank Detail"},
	*	description="Provider Add Bank Details",
	*	@OA\RequestBody(
	*		description="Provider Add Bank Details",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderEditBankDetailsInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Updated Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderEditBankDetails")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderEditBankDetails", 
	* 	required={"bankform_id","keyvalue"}, 
	*	@OA\Property(property="bankform_id[]", type="array", @OA\Items( type="integer") ),
	*	@OA\Property(property="keyvalue[]", type="array", @OA\Items( type="integer") ),
	*	@OA\Property(property="id[]", type="array", @OA\Items( type="integer")) ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderEditBankDetailsInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderEditBankDetails"),
	*	@OA\Schema(
	*		@OA\Property(property="bankform_id[]", type="array", @OA\Items( type="integer"), example="1" ),
	*		@OA\Property(property="keyvalue[]", type="array", @OA\Items( type="integer"), example="1" ),
	*		@OA\Property(property="id[]", type="array", @OA\Items( type="integer"), example="2323232" ))
	*	}
	* )
	*/

	/* *********************************************************************
	*	PROVIDER LIST DOCUMENTS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/listdocuments",
	*	operationId="api.v1.provider.listdocuments.list",
	*	tags={"Provider Documents"},
	*	description="Provider Documents list",
	*	@OA\RequestBody(
	*		description="Provider Documents list",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderDocumentsListInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Documents list",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderDocumentsList")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderDocumentsList", 
	* 	required={"type"}, 
	*	@OA\Property(property="type", type="string", example="Transport" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderDocumentsListInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderDocumentsList"),
	*	@OA\Schema(
	*		@OA\Property(property="type", type="string"))
	*	}
	* )
	*/


	/* *********************************************************************
	*	PROVIDER ADD DOCUMENTS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/documents",
	*	operationId="api.v1.provider.documents.add",
	*	tags={"Provider Documents"},
	*	description="Provider Documents add",
	*	@OA\RequestBody(
	*		description="Provider Documents add",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderDocumentsInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Documents created successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderDocuments")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderDocuments", 
	* 	required={"type"}, 
	*	@OA\Property(property="expires_at", type="date", example="2020-12-12" ),
	*	@OA\Property(property="document_id", type="string", example="509" ),
	*	@OA\Property(property="file", type="file" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderDocumentsInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderDocuments"),
	*	@OA\Schema(
	*		@OA\Property(property="expires_at", type="date"),
	*		@OA\Property(property="document_id", type="string"),
	*		@OA\Property(property="file", type="file"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	PROVIDER ADD VEHICLE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/vehicle",
	*	operationId="api.v1.provider.vehicle.add",
	*	tags={"Provider Vehicle"},
	*	description="Provider Vehicle add",
	*	@OA\RequestBody(
	*		description="Provider Vehicle add",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderVehicleInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="created successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderVehicle")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderVehicle", 
	* 	required={"vehicle_id","vehicle_year","vehicle_make","vehicle_model","vehicle_no","admin_service","category_id"}, 
	*	@OA\Property(property="vehicle_id", type="integer", example="1" ),
	*	@OA\Property(property="vehicle_year", type="integer", example="2017" ),
	*	@OA\Property(property="vehicle_make", type="string", example="Swift" ),
	*	@OA\Property(property="vehicle_model", type="integer", example="Dzire" ),
	*	@OA\Property(property="vehicle_no", type="string", example="TN 11 AA 0001" ),
	*	@OA\Property(property="vehicle_color", type="string", example="red" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT" ),
	*	@OA\Property(property="picture", type="file", description="Please Upload RC" ),
	*	@OA\Property(property="picture1", type="file", description="Please Upload Insurance" ),
	*	@OA\Property(property="category_id", type="integer", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderVehicleInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderVehicle"),
	*	@OA\Schema(
	*		@OA\Property(property="vehicle_id", type="integer"),
	*		@OA\Property(property="vehicle_year", type="string"),
	*		@OA\Property(property="vehicle_make", type="string"),
	*		@OA\Property(property="vehicle_model", type="string"),
	*		@OA\Property(property="vehicle_no", type="string"),
	*		@OA\Property(property="vehicle_color", type="string"),
	*		@OA\Property(property="admin_service", type="string"),
	*		@OA\Property(property="picture", type="file"),
	*		@OA\Property(property="picture1", type="file"),
	*		@OA\Property(property="category_id", type="string"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	PROVIDER EDIT VEHICLE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/vehicle/edit",
	*	operationId="api.v1.provider.vehicle.edit",
	*	tags={"Provider Vehicle"},
	*	description="Provider Edit Vehicle",
	*	@OA\RequestBody(
	*		description="Provider Edit Vehicle",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderEditVehicleInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Updated Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderEditVehicle")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderEditVehicle", 
	* 	required={"id","vehicle_id","vehicle_year","vehicle_make","vehicle_model","vehicle_no","admin_service","category_id"}, 
	*	@OA\Property(property="id", type="integer", example="2" ),
	*	@OA\Property(property="vehicle_id", type="integer", example="1" ),
	*	@OA\Property(property="vehicle_year", type="integer", example="2017" ),
	*	@OA\Property(property="vehicle_make", type="string", example="Swift" ),
	*	@OA\Property(property="vehicle_model", type="integer", example="Dzire" ),
	*	@OA\Property(property="vehicle_no", type="string", example="TN 11 AA 0001" ),
	*	@OA\Property(property="vehicle_color", type="string", example="red" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT" ),
	*	@OA\Property(property="picture", type="file", description="Please Upload RC" ),
	*	@OA\Property(property="picture1", type="file", description="Please Upload Insurance" ),
	*	@OA\Property(property="category_id", type="integer", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderEditVehicleInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderEditVehicle"),
	*	@OA\Schema(
	*		@OA\Property(property="id", type="string"),
	*		@OA\Property(property="vehicle_id", type="integer"),
	*		@OA\Property(property="vehicle_year", type="integer"),
	*		@OA\Property(property="vehicle_make", type="string"),
	*		@OA\Property(property="vehicle_model", type="integer"),
	*		@OA\Property(property="vehicle_no", type="string"),
	*		@OA\Property(property="vehicle_color", type="string"),
	*		@OA\Property(property="admin_service", type="string"),
	*		@OA\Property(property="picture", type="file"),
	*		@OA\Property(property="picture1", type="file"),
	*		@OA\Property(property="category_id", type="integer"))
	*	}
	* )
	*/


	/* *********************************************************************
	*	PROVIDER ONLINE STATUS UPDATE
	**********************************************************************/

	
	/**
	*@OA\Get(
	*	path="/api/v1/provider/onlinestatus/{id}",
	*	operationId="api.v1.provider.onlinestatus.id",
	*	tags={"Provider Online"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="provider status. 1 / 0 . 1 for on / 0 for off",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\Response(
	*		response="200",
	*		description="Updated Successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	CLEAR REQUEST PROVIDER
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/city",
	*	operationId="api.v1.provider.city.change",
	*	tags={"Provider Clear Request"},
	*	description="Provider Clear Request",
	*	@OA\RequestBody(
	*		description="Provider Clear Request",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderClearRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns provider profile data",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderClearRequest")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="ProviderClearRequest", 
	* 	required={"provider_id"}, 
	*	@OA\Property(property="provider_id", type="integer", example="4" ))
	* 
	*/ 
	/**
	* @OA\Schema(
	*	schema="ProviderClearRequestInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderClearRequest"),
	*	@OA\Schema(
	*		@OA\Property(property="provider_id", type="integer"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	PROVIDER ADMIN SERVICES
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/adminservices",
	*	operationId="api.v1.provider.adminservices",
	*	tags={"Provider Admin Service"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns admin services of provider",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER DISPUTE LIST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/{adminService}/dispute",
	*	operationId="api.v1.provider.adminService.dispute",
	*	tags={"Dispute List"},	
	*	@OA\Parameter(
	*		name="adminService",
	*		in="path",
	*		description="admin service name. ride / services / order",
	*		required=true,
	*		@OA\Schema(type="string", example="services")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns user Dispute list",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
}