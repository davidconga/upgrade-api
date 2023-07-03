<?php 

namespace App\Services;

use Illuminate\Mail\TransportManager;
use App\Models\Common\Setting;

class CustomTransportManager extends TransportManager {

	/**
	 * Create a new manager instance.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;

		$settings = json_decode(json_encode(Setting::first()->settings_data));
		$siteConfig = $settings->site;

		if( $siteConfig->send_email == 1 ){
			
			$this->app['config']['mail'] = [
				'driver'        => $siteConfig->mail_driver,
				'host'          => $siteConfig->mail_host,
				'port'          => $siteConfig->mail_port,
				'from'          => [ 'address' => $siteConfig->mail_from_address, 'name' => $siteConfig->mail_from_name ],
				'encryption'    => $siteConfig->mail_encryption,
				'username'      => $siteConfig->mail_username,
				'password'      => $siteConfig->mail_password,
				'sendmail'      => '/usr/sbin/sendmail -bs',
				'pretend'       => false,
			];

			if($siteConfig->mail_driver == 'MAILGUN'){
			   $this->app['config']['services'] = [
					'mailgun' => [
						'domain' => $siteConfig->mail_domain,
						'secret' => $siteConfig->mail_secret,
					]
				];
			}     
	   }

	}
}