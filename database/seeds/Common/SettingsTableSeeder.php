<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SettingsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run($company = null)
	{
		Schema::disableForeignKeyConstraints();

		$data = json_encode([
		   "site" => [
		      "site_title" => "GoX",
		      "contact_number" => [
		         [
		            "number" => "911"
		         ]
		      ],
		      "language" => [
		         [
		            "name" => "English",
		            "key" => "en"
		         ],
		         [
		            "name" => "Arabic",
		            "key" => "ar"
		         ]
		      ],
		      "contact_email" => "admin@gox.com",
		      "sos_number" => "911",
		      "site_copyright" => "&copy; Copyrights 2019 All Rights Reserved.",
		      "store_link_android_user" => "",
		      "store_link_android_provider" => "",
		      "store_link_ios_user" => "",
		      "store_link_ios_provider" => "",
		      "store_facebook_link" => "",
		      "store_twitter_link" => "",
		      "image" => "",
		      "site_logo" => url('/').'/images/common/logo.png',
		      "site_icon" => url('/').'/images/common/favicon.ico',
		      "browser_key" => "",
		      "server_key" => "",
		      "android_key" => "",
		      "ios_key" => "",
		      "social_login" => "0",
		      "facebook_app_version" => "",
		      "facebook_app_id" => "",
		      "facebook_app_secret" => "",
		      "google_client_id" => "",
		      "environment" => "development",
		      "ios_push_password" => "",
		      "IOS_USER_BUNDLE_ID" => "",
		      "provider_IOS_BUNDLE_ID" => "",
		      "android_push_key" => "",
		      "user_pem" => "",
		      "send_email" => "1",
		      "mail_driver" => "SMTP",
		      "mail_port" => "587",
		      "mail_host" => "smtp.gmail.com",
		      "mail_username" => "",
		      "mail_password" => "",
		      "mail_from_address" => "admin@gox.com",
		      "mail_from_name" => "GoX",
		      "mail_encryption" => "tls",
		      "mail_domain" => "",
		      "mail_secret" => "",
		      "send_sms" => "1",
		      "sms_driver" => "TWILIO",
		      "sms_provider" => "TWILIO",
		      "sms_account_sid" => "AC552499745e1c628a6dfb85dd9d268aa8",
		      "sms_auth_token" => "fc2f93892e957f8bac4ccbd5ee06006d",
		      "sms_from_number" => "+17577932902",
		      "referral" => "1",
		      "referral_count" => "5",
		      "referral_amount" => "50.00",
		      "distance" => "Kms",
		      "currency" => "$",
		      "round_decimal" => "2",
		      "cash" => "",
		      "card" => "",
		      "stripe_secret_key" => "",
		      "stripe_publishable_key" => "",
		      "stripe_currency" => "",
		      "page_privacy" => url()."/pages/page_privacy",
		      "help" => url()."/pages/help",
		      "terms" => url()."/pages/terms",
		      "cancel" => url()."/pages/cancel",
		      "about_us" => url()."/pages/about_us",
		      "legal" => url()."/pages/legal",
		      "faq" => url()."/pages/faq",
		      "provider_pem" => "",
		      "provider_negative_balance" => "0"
		   ],
		   "transport" => [
		      "ride_otp" => "0",
		      "manual_request" => "0",
		      "broadcast_request" => "1",
		      "provider_search_radius" => "200",
		      "user_select_timeout" => "180",
		      "provider_select_timeout" => "60",
		      "booking_prefix" => "TRNX",
		      "unit_measurement" => "Kms",
      		  "destination" => "1"
		   ],
		   "order" => [
		      "serve_otp" => "1",
		      "manual_request" => "0",
		      "broadcast_request" => "1",
		      "tax_percentage" => "",
		      "commission_percentage" => "",
		      "surge_trigger" => "",
		      "provider_search_radius" => "200",
		      "provider_select_timeout" => "60",
		      "time_left_to_respond" => 360,
		      "surge_percentage" => "",
		      "track_distance" => "1",
		      "booking_prefix" => "TRNXF",
		      "store_search_radius" => "200",
		      "store_response_time" => "60",
		      "store" => "1",
		      "order_otp" => "0",
		      "search_radius" => "200",
		      "response_time" => "60",
		      "max_items_in_order" => "2"
		   ],
		   "service" => [
		      "serve_otp" => "0",
		      "manual_request" => "0",
		      "broadcast_request" => "1",
		      "tax_percentage" => "",
		      "commission_percentage" => "",
		      "surge_trigger" => "",
		      "provider_search_radius" => "200",
		      "provider_select_timeout" => "60",
		      "time_left_to_respond" => 60,
		      "surge_percentage" => "",
		      "track_distance" => "1",
		      "booking_prefix" => "TRNXS",
		      "service" => "SER"
		   ],
		   "payment" => [
		      [
		         "name" => "cash",
		         "status" => "1",
		         "credentials" => [

		         ]
		      ],
		      [
		         "name" => "card",
		         "status" => 0,
		         "credentials" => [
		            [
		               "name" => "stripe_secret_key",
		               "value" => ""
		            ],
		            [
		               "name" => "stripe_publishable_key",
		               "value" => ""
		            ],
		            [
		               "name" => "stripe_currency",
		               "value" => "usd"
		            ]
		         ]
		      ]
		   ]
		]);


		DB::table('settings')->insert([
			[
				'settings_data' => $data,
				'company_id' => $company,
				'demo_mode' => 0,
				'error_mode' => 0,
				'encrypt'=>0,
				"banner"=>0,
                "chat"=>0,
			    'created_at' => Carbon::now(),
				'updated_at' => Carbon::now()
			]
		]);

		Schema::enableForeignKeyConstraints();
	}
}
