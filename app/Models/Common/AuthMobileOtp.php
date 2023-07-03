<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class AuthMobileOtp extends Model
{
	protected $connection = 'common';
	
	protected $fillable = [
		'company_id', 'country_code', 'mobile', 'otp'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];
}
