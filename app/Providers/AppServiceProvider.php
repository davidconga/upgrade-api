<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function asset(){
        if (!function_exists('urlGenerator')) {
            /**
             * @return \Laravel\Lumen\Routing\UrlGenerator
             */
            function urlGenerator() {
                return new \Laravel\Lumen\Routing\UrlGenerator(app());
            }
        }
        
        if (!function_exists('asset')) {
            /**
             * @param $path
             * @param bool $secured
             *
             * @return string
             */
            function asset($path, $secured = false) {
                return urlGenerator()->asset($path, $secured);
            }
        }
    }
}
