<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Helper;
use App\Models\Common\Setting;
use Auth;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $setting = Setting::where('company_id',  Auth::guard(strtolower( Helper::getGuard() ) )->user()->company_id)->first();

        if($setting->demo_mode == 1) {
            return Helper::getResponse(['status' => 403, 'message' => trans('admin.demomode')]);
        }
        return $next($request);
    }
}
