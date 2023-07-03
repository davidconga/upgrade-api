<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\Helper;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		HttpException::class,
		ModelNotFoundException::class,
		ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		
		$whoops = new \Whoops\Run;

		$whoops->register();

		//if ($request->ajax()) {
			$whoops->pushHandler(new ResponseHandler);
		//} else {
			//$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		//}

		if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
			return response()->json(['error' => $exception->getMessage()], 403);
		}

		if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
			return response()->json(['error' => $exception->getMessage()], 403);
		}

		if ($exception instanceof \Illuminate\Validation\ValidationException) {
			

			$errors = [];
			foreach ($exception->errors() as $key => $error) {
			   $errors[] = $error[0];
			}

			return Helper::getResponse(['status' => $exception->status, 'title' => $exception->getMessage(), 'message' => !empty($errors[0]) ? $errors[0] : "" ]);
		}

		return response($whoops->handleException($exception), $e->getStatusCode(), $e->getHeaders() );
		
		//return parent::render($request, $exception);
	}
}
