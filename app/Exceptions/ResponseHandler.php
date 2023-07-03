<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace App\Exceptions;

use Whoops\Handler\JsonResponseHandler;
use App\Jobs\SendEmailJob;
use Whoops\Exception\Formatter;
use App\Models\Common\Setting;
use Whoops\Handler\Handler;
use App\Helpers\Helper;
use App\Models\Common\Company;
use Carbon\Carbon;
use Auth;

/**
 * Catches an exception and converts it to a JSON
 * response. Additionally can also return exception
 * frames for consumption by an API.
 */
class ResponseHandler extends JsonResponseHandler
{
	public function handle()
	{
		$response = [
				'statusCode' => 500,
				'title' => 'Oops Something went wrong!', 
				'message' => 'Oops Something went wrong!',
				'responseData' => [],
				'error' => Formatter::formatExceptionAsDataArray(
					$this->getInspector(),
					$this->addTraceToOutput()
				),
			];

		echo json_encode($response, defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0);

  if( Auth::guard( strtolower( Helper::getGuard() ) )->user() !== null ) {
\Log::info(['push resposse' => Auth::guard( strtolower( Helper::getGuard() ) )->user()]);
		$setting = Setting::where('company_id', Auth::guard( strtolower( Helper::getGuard() ) )->user()->company_id)->first();
		if($setting != null && $setting->error_mode == 1) {
			$company = Company::find(Auth::guard( strtolower( Helper::getGuard() ) )->user()->company_id);
			$emails = explode(',', $setting->error_mail);
			$response['error']['time'] = Carbon::now()->format('d/m/Y h:m:s');
			dispatch(new SendEmailJob($response['error'], $company->company_name, $emails));
		}
}
		return Handler::QUIT;
	}
}
