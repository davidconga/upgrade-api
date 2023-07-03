<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    /**
	* @OA\Info(
	*     description="GoX API Documentation.",
	*     version="1.0.0",
	*     title="GoX"
	* )
	*/
  
    /**
     *  @OA\Tag(
     *     name="Authentication",
     *     description="Admin, User and Provider Authentication APIs"
     * )
     * @OA\Tag(
	*     name="Base",
	*     description="Base APIs"
	* )
	* @OA\Tag(
	*     name="Authentication",
	*     description="Authentication APIs"
	* )
	* @OA\Tag(
	*     name="Common",
	*     description="Common APIs"
	* )
	* @OA\Tag(
	*     name="Transport",
	*     description="Transport Flow APIs"
	* )
	* @OA\Tag(
	*     name="Order",
	*     description="Order Flow APIs",
	* )
	* @OA\Tag(
	*     name="Service",
	*     description="Service Flow APIs"
	* )
	*/
}
