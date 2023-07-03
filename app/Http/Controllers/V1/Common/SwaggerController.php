<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
use SwaggerLume\Http\Controllers\SwaggerLumeController;
use App\Models\Common\Setting;
use App\Models\Common\AdminService;
use App\Models\Common\CompanyCountry;
use App\Services\SendPushNotification;
use App\Models\Common\CompanyCity;
use App\Models\Common\Company;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\City;
use App\Models\Common\Menu;
use App\Models\Common\CmsPage;
use App\Models\Common\Rating;
use App\Models\Common\AuthLog;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderWallet;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use App\Models\Common\FleetWallet;
use App\Models\Common\Chat;
use App\Helpers\Helper;
use Carbon\Carbon;
use Auth;

class SwaggerController extends SwaggerLumeController
{
    /**
     * Dump api-docs.json content endpoint.
     *
     * @param null $jsonFile
     *
     * @return \Illuminate\Http\Response
     */
    public function docs($jsonFile = null)
    {
        $filePath = config('swagger-lume.paths.docs').'/'.
            (! is_null($jsonFile) ? $jsonFile : 'api-v1-docs.json');

        if (! File::exists($filePath)) {
            abort(404, 'Cannot find '.$filePath);
        }

        $content = File::get($filePath);

        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Display Swagger API page.
     *
     * @return \Illuminate\Http\Response
     */
    public function api()
    {
        if (config('swagger-lume.generate_always')) {
            Generator::generateDocs();
        }
        
        //need the / at the end to avoid CORS errors on Homestead systems.
        $response = new Response(
            view('swagger-lume::index', [
                'secure' => Request::secure(),
                'urlToDocs' => route('swagger-v1-lume.docs'),
                'operationsSorter' => config('swagger-lume.operations_sort'),
                'configUrl' => config('swagger-lume.additional_config_url'),
                'validatorUrl' => config('swagger-lume.validator_url'),
            ]),
            200,
            ['Content-Type' => 'text/html']
        );

        return $response;
    }

}
