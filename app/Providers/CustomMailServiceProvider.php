<?php

namespace App\Providers;

use Illuminate\Mail\MailServiceProvider;
use App\Services\CustomTransportManager;


class CustomMailServiceProvider extends MailServiceProvider
{
    protected function registerSwiftTransport(){
        $this->app->singleton('swift.transport', function ($app) {
	        return new CustomTransportManager($app);
	    });
    }
}
