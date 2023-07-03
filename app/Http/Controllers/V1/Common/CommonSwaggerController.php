<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;

class CommonSwaggerController extends Controller
{
	/* *********************************************************************
	*	BASE
	**********************************************************************/
	/**
	*@OA\Post(
	*	path="/base",
	*	operationId="/base",
	*	tags={"Base"},
	*	@OA\Parameter(
	*	name="salt_key",
	*	in="query",
	*	description="The salt key in query",
	*	required=true,
	*	@OA\Schema(type="string", example="MQ==")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/Base")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."))
	*/
	/**
	*@OA\Schema(schema="Base", 
	*	@OA\Property(property="statusCode", type="string", example="200" ),
	*	@OA\Property(property="title", type="string", example="OK" ),
	*	@OA\Property(property="message", type="string", example="" ),
	*	@OA\Property(property="responseData", type="object", 
	*		@OA\Property(property="base_url", type="string", example="http://127.0.0.1:8001/api/v1" ),
	*		@OA\Property(property="services", type="object", 
	*			type="array",
	*			@OA\Items(
	*				@OA\Property(property="id", type="string", example="1"),
	*				@OA\Property(property="admin_service", type="string", example="TRANSPORT"),
	*				@OA\Property(property="display_name", type="string", example="TRANSPORT"),
	*				@OA\Property(property="base_url", type="string", example="http://127.0.0.1:8001/api/v1"),
	*				@OA\Property(property="status", type="integer", example="1")
	*			)
	*		),
	*		@OA\Property(property="appsetting", type="object", example="http://127.0.0.1:8001/api/v1" ),
	*		@OA\Property(property="error", type="array", 
	*			@OA\Items(
	*				@OA\Property(property="message", type="string", example="Oops! Something went wrong.")
	*		) ),
	*	))
	*
	*/


	/* *********************************************************************
	*	USER LOGIN
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/login",
	*	operationId="/user/login",
	*	tags={"Authentication"},
	*	description="User Login",
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/UserLoginInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserLogin")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."))
	*/
	/**
	*@OA\Schema(schema="UserLogin", 
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9952199521" ),
	*	@OA\Property(property="email", type="string", example="hemamalini@appoets.com" ),
	*	@OA\Property(property="device_type", type="string", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="device_token", type="string"),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"FACEBOOK", "GOOGLE", "MANUAL"}  ),
	*	@OA\Property(property="password", type="string", example="1234567" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserLoginInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserLogin"),
	*		@OA\Schema(
	*			@OA\Property(property="email", type="string"),
	*			@OA\Property(property="country_code", type="integer"),
	*			@OA\Property(property="mobile", type="integer"),
	*			@OA\Property(property="password", type="string"),
	*			@OA\Property(property="device_type", type="string"),
	*			@OA\Property(property="device_token", type="string"),
	*			@OA\Property(property="login_by", type="string"),
	*			@OA\Property(property="salt_key", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER SIGNUP
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/signup",
	*	operationId="/user/signup",
	*	tags={"Authentication"},
	*	description="User Signup",
	*	@OA\RequestBody(
	*		description="User Signup",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserSignupInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserSignup")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."))
	*/
	/**
	* @OA\Schema(schema="UserSignup", 
	*	@OA\Property(property="first_name", type="string", example="User" ),
	*	@OA\Property(property="last_name", type="string", example="Demo" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="user@demo.com" ),
	*	@OA\Property(property="gender", type="string", example="MALE", enum={"MALE", "FEMALE", "OTHER"} ),
	*	@OA\Property(property="device_type", type="string", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="device_token", type="string"),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"FACEBOOK", "GOOGLE", "MANUAL"}  ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="country_id", type="string", example="231" ),
	*	@OA\Property(property="city_id", type="string", example="48294" ),
	*	@OA\Property(property="picture", type="string", format="binary", example="" ),
	*	@OA\Property(property="social_unique_id", type="string", example="" ),
	*	@OA\Property(property="referral_code", type="string", example="" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserSignupInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserSignup"),
	*	@OA\Schema(
	*		@OA\Property(property="first_name", type="string"),
	*		@OA\Property(property="last_name", type="string"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="country_code", type="integer"),
	*		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="gender", type="string"),
	*		@OA\Property(property="device_type", type="string"),
	*		@OA\Property(property="device_token", type="string"),
	*		@OA\Property(property="login_by", type="string"),
	*		@OA\Property(property="password", type="string"),
	*		@OA\Property(property="country_id", type="integer"),
	*		@OA\Property(property="city_id", type="integer"),
	*		@OA\Property(property="picture", type="string"),
	*		@OA\Property(property="social_unique_id", type="string"),
	*		@OA\Property(property="referral_code", type="string"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	* )
	*/
	/* *********************************************************************
	*	USER SOCIAL LOGIN
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/social/login",
	*	operationId="/user/social/login",
	*	tags={"Authentication"},
	*	description="User Social Login",
	*	@OA\RequestBody(
	*		description="User Social Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserSocialLoginInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserSignup")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."))
	*/
	/**
	* @OA\Schema(schema="UserSocialLogin", 
	*	@OA\Property(property="first_name", type="string", example="User" ),
	*	@OA\Property(property="last_name", type="string", example="Demo" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="user@demo.com" ),
	*	@OA\Property(property="gender", type="string", example="MALE", enum={"MALE", "FEMALE", "OTHER"} ),
	*	@OA\Property(property="device_type", type="string", example="" ),
	*	@OA\Property(property="device_token", type="string", example="" ),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="country_id", type="string", example="231" ),
	*	@OA\Property(property="city_id", type="string", example="48294" ),
	*	@OA\Property(property="picture", type="string", format="binary", example="" ),
	*	@OA\Property(property="social_unique_id", type="string", example="" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserSocialLoginInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserSocialLogin"),
	*	@OA\Schema(
	*		@OA\Property(property="first_name", type="string"),
	*		@OA\Property(property="last_name", type="string"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="country_code", type="integer"),
	*		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="gender", type="string"),
	*		@OA\Property(property="device_type", type="string"),
	*		@OA\Property(property="device_token", type="string"),
	*		@OA\Property(property="login_by", type="string"),
	*		@OA\Property(property="country_id", type="integer"),
	*		@OA\Property(property="city_id", type="integer"),
	*		@OA\Property(property="picture", type="string"),
	*		@OA\Property(property="social_unique_id", type="string"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	* )
	*/
	/* *********************************************************************
	*	USER FORGOT PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/forgot/otp",
	*	operationId="/user/forgot/otp",
	*	tags={"Authentication"},
	*	description="User Forgot Password",
	*	@OA\RequestBody(
	*		description="User Forgot Password",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserForgotInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserForgot")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."
	*	))
	*/
	/**
	* @OA\Schema(schema="UserForgot", 
	*	@OA\Property(property="email", type="string", example="user@demo.com" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="account_type", type="string", example="mobile", enum={"mobile", "email"} ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="UserForgotInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserForgot"),
	*	@OA\Schema(
	*		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="country_code", type="integer"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="account_type", type="string"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER RESET PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/reset/otp",
	*	operationId="/user/reset/otp",
	*	tags={"Authentication"},
	*	description="User Reset Password",
	*	@OA\RequestBody(
	*	description="User Reset Password",
	*		@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/UserResetInput")
	*		)),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserReset")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied.",
	*		@OA\JsonContent()),
	* )
	*/
	/**
	*@OA\Schema(schema="UserReset", 
	*	@OA\Property(property="username", type="string", example="9944332211" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="otp", type="string", example="" ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="password_confirmation", type="string", example="123456" ),
	*	@OA\Property(property="account_type", type="string", example="mobile", enum={"mobile", "email"} ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="UserResetInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserReset"),
	*	@OA\Schema(
	*		@OA\Property(property="username", type="string",description="if account_type is mobile, username is mobile number. If account_type is email, username is email id."),
	*		@OA\Property(property="country_code", type="integer"),
	*		@OA\Property(property="otp", type="string"),
	*		@OA\Property(property="password", type="string"),
	*		@OA\Property(property="password_confirmation", type="string"),
	*		@OA\Property(property="account_type", type="string"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*   USER VERIFY 
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/verify",
	*	operationId="/user/verify",
	*	tags={"Authentication"},
	*	description="User Verify",
	*	@OA\RequestBody(
	*		description="User Verify",
	*		@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/UserVerifyInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserVerify")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."
	*	))
	*/
	/**
	*@OA\Schema(schema="UserVerify", 
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="user@demo.com" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="UserVerifyInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserVerify"),
	*	@OA\Schema(
	*		@OA\Property(property="country_code", type="integer"),
	*		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*   USER REFRESH 
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/refresh",
	*	operationId="/user/refresh",
	*	tags={"Authentication"},
	*	description="User Refresh",
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application"),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*		security={ {"User": {}} })
	*/
	/**
	*@OA\Schema(schema="UserRefresh", 
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserRefreshInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserRefresh"),
	*		@OA\Schema(
	*			@OA\Property(property="Authorization", type="integer")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*   PROVIDER LOGIN
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/login",
	*	operationId="/provider/login",
	*	tags={"Authentication"},
	*	description="Provider Login",
	*	@OA\RequestBody(
	*	description="Provider Login",
	*	@OA\MediaType(
	*		mediaType="multipart/form-data",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderLoginInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderLogin")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."
	*	),
	*)
	*/
	/**
	*@OA\Schema(schema="ProviderLogin", 
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="provider@demo.com" ),
	*	@OA\Property(property="gender", type="string", example="MALE", enum={"MALE", "FEMALE", "OTHER"} ),
	*	@OA\Property(property="device_type", type="string", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="device_token", type="string"),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"FACEBOOK", "GOOGLE", "MANUAL"}  ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderLoginInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderLogin"),
	*		@OA\Schema(
	*			@OA\Property(property="email", type="string"),
	*			@OA\Property(property="country_code", type="integer"),
	*			@OA\Property(property="mobile", type="integer"),
	*			@OA\Property(property="password", type="string"),
	*			@OA\Property(property="device_type", type="string"),
	*			@OA\Property(property="device_token", type="string"),
	*			@OA\Property(property="login_by", type="string"),
	*			@OA\Property(property="salt_key", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*   PROVIDER SIGNUP
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/signup",
	*	operationId="/provider/signup",
	*	tags={"Authentication"},
	*	description="Provider Signup",
	*	@OA\RequestBody(
	*		description="Provider Signup",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderSignupInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderSignup")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied.",
	*		@OA\JsonContent()
	*	),
	*)
	*/
	/**
	*@OA\Schema(schema="ProviderSignup", 
	*	@OA\Property(property="first_name", type="string", example="User" ),
	*	@OA\Property(property="last_name", type="string", example="Demo" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="provider@demo.com" ),
	*	@OA\Property(property="gender", type="string", example="MALE", enum={"MALE", "FEMALE", "OTHER"} ),
	*	@OA\Property(property="device_type", type="string", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="device_token", type="string"),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"FACEBOOK", "GOOGLE", "MANUAL"}  ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="country_id", type="string", example="231" ),
	*	@OA\Property(property="city_id", type="string", example="48294" ),
	*	@OA\Property(property="picture", type="string", format="binary", example="" ),
	*	@OA\Property(property="social_unique_id", type="string", example="" ),
	*	@OA\Property(property="referral_code", type="string", example="" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderSignupInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/ProviderSignup"),
	*	@OA\Schema(
	*		@OA\Property(property="first_name", type="string"),
	*		@OA\Property(property="last_name", type="string"),
	*		@OA\Property(property="mobile", type="integer"),
	*		@OA\Property(property="country_code", type="integer"),
	* 		@OA\Property(property="email", type="string"),
	*		@OA\Property(property="gender", type="string"),
	*		@OA\Property(property="device_type", type="string"),
	*		@OA\Property(property="device_token", type="string"),
	*		@OA\Property(property="login_by", type="string"),
	*		@OA\Property(property="password", type="string"),
	*		@OA\Property(property="country_id", type="integer"),
	*		@OA\Property(property="city_id", type="integer"),
	*		@OA\Property(property="picture", type="string"),
	* 		@OA\Property(property="social_unique_id", type="string"),
	*		@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*   PROVIDER FORGOT PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/forgot/otp",
	*	operationId="/provider/forgot/otp",
	*	tags={"Authentication"},
	*	description="Provider Forgot Password",
	*	@OA\RequestBody(
	*		description="Provider Forgot Password",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\JsonContent(ref="#/components/schemas/ProviderForgotInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderForgot")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."
	*	),
	*)
	*/
	/**
	*@OA\Schema(schema="ProviderForgot", 
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="mobile", type="string", example="9944332211" ),
	*	@OA\Property(property="email", type="string", example="provider@demo.com" ),
	*	@OA\Property(property="account_type", type="string", example="mobile", enum={"mobile", "email"} ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderForgotInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderForgot"),
	*		@OA\Schema(
	*			@OA\Property(property="email", type="string"),
	*			@OA\Property(property="country_code", type="integer"),
	*			@OA\Property(property="mobile", type="integer"),
	*			@OA\Property(property="account_type", type="string"),
	*			@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*   PROVIDER RESET PASSWORD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/reset/otp",
	*	operationId="/provider/reset/otp",
	*	tags={"Authentication"},
	*	description="Provider Reset Password",
	*	@OA\RequestBody(
	*		description="Provider Reset Password",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/ProviderResetInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderReset")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied.",
	*		@OA\JsonContent()
	*	),
	*)
	*/
	/**
	*@OA\Schema(schema="ProviderReset", 
	*	@OA\Property(property="username", type="string", example="User" ),
	*	@OA\Property(property="country_code", type="string", example="91" ),
	*	@OA\Property(property="email", type="string", example="provider@demo.com" ),
	*	@OA\Property(property="otp", type="string" ),
	*	@OA\Property(property="device_type", type="string", example="" ),
	*	@OA\Property(property="device_token", type="string", example="" ),
	*	@OA\Property(property="login_by", type="string", example="MANUAL", enum={"ANDROID", "IOS", "MANUAL"}  ),
	*	@OA\Property(property="password", type="string", example="123456" ),
	*	@OA\Property(property="password_confirmation", type="string", example="123456" ),
	*	@OA\Property(property="account_type", type="string", example="mobile", enum={"mobile", "email"} ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="ProviderResetInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderReset"),
	*		@OA\Schema(
	*			@OA\Property(property="username", type="string",description="if account_type is mobile, username is mobile number. If account_type is email, username is email id."),
	*			@OA\Property(property="country_code", type="integer"),
	*			@OA\Property(property="otp", type="string"),
	*			@OA\Property(property="password", type="string"),
	*			@OA\Property(property="password_confirmation", type="string"),
	*			@OA\Property(property="account_type", type="string",description="mobile / email. If mobile, send country_code also"),
	*			@OA\Property(property="salt_key", type="string"))
	*	}
	*)
	*/

	

	/* *********************************************************************
	*	PROMOCODE LIST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/promocode/{service}",
	*	operationId="apiv1.user.promocode.service",
	*	tags={"Common"},
	*	@OA\Parameter(
	*		name="service",
	*		in="path",
	*		description="Admin service",
	*		required=true,
	*		@OA\Schema(type="string", example="transport", enum={"TRANSPORT", "ORDER", "SERVICE"})),
	*		@OA\Response(
	*			response="200",
	*			description="Returns available services, providers and promocodes",
	*			@OA\JsonContent()
	*		),
	*		@OA\Response(
	*			response="422",
	*			description="Error: Unprocessable entity. When required parameters were not supplied.",
	*	),
	*	security={ {"User": {}} })
	*/

	/* *********************************************************************
	*	CHECK REQUEST
	**********************************************************************/
	/**
	*@OA\Get(
	*	path="/api/v1/provider/check/request",
	*	operationId="apiv1.provider.check.request",
	*	tags={"Common"},
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
	*		description="Returns settings for the application",
	*		@OA\JsonContent()),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/

	/* *********************************************************************
	*	ACCEPT REQUEST
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/accept/request",
	*	operationId="apiv1.provider.accept.request",
	*	tags={"Common"},
	*	description="Accept Request",
	*	@OA\RequestBody(
	*		description="Accept Request",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/AcceptRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/AcceptRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="AcceptRequest", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT", enum={"TRANSPORT", "ORDER","SERVICE"} ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="AcceptRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/AcceptRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string", description="Request ID"),
	*			@OA\Property(property="admin_service", type="string", description="Admin Service"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	REJECT REQUEST
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/cancel/request",
	*	operationId="apiv1.provider.cancel.request",
	*	tags={"Common"},
	*	description="Cancel Request",
	*	@OA\RequestBody(
	*		description="Cancel Request",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/CancelRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/CancelRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="CancelRequest", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT", enum={"TRANSPORT", "ORDER","SERVICE"} ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="CancelRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/CancelRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string", description="Request ID"),
	*			@OA\Property(property="admin_service", type="string", description="Admin Service"))
	*	}
	*)
	*/


	/* *********************************************************************
	*	USER ONGOING SERVICES
	**********************************************************************/
	/**
	*@OA\Get(
	*	path="/api/v1/user/ongoing",
	*	operationId="api.v1.user.ongoing",
	*	tags={"Common"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent()),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	
	/* *********************************************************************
	* CHAT SAVE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/chat",
	*	operationId="api.v1.add.chat",
	*	tags={"Chat"},
	*	description="User Chat Save",
	*	@OA\RequestBody(
	*		description="User Chat Save",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\JsonContent(ref="#/components/schemas/UserAddChatInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Chat message saved",
	*		@OA\JsonContent(ref="#/components/schemas/UserAddChat")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="UserAddChat", 
	* 	required={"id","user_id","provider_id","map_address"}, 
	*	@OA\Property(property="id", type="integer", example="1",description="Request Id" ),
	*	@OA\Property(property="salt_key", type="string", example="MQ==" ),
	*	@OA\Property(property="admin_service", type="string",enum={"TRANSPORT","ORDER","SERVICE"} ),
	*	@OA\Property(property="type", type="string", example="user" ),
	*	@OA\Property(property="user_name", type="string", example="Hema" ),
	*	@OA\Property(property="provider_name", type="string", example="Malini" ),
	*	@OA\Property(property="message", type="string", example="Hi, can you update when will you reach?" ))
	* 
	*/ 
	/**
	* @OA\Schema(
	*	schema="UserAddChatInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/UserAddChat"),
	*	@OA\Schema(
	*		@OA\Property(property="id", type="string"),
	*		@OA\Property(property="admin_service", type="string"),
	*		@OA\Property(property="salt_key", type="string"),
	*		@OA\Property(property="type", type="string"),
	*		@OA\Property(property="user_name", type="string"),
	*		@OA\Property(property="provider_name", type="string"),
	*		@OA\Property(property="message", type="string"))
	*	}
	* )
	*/

}