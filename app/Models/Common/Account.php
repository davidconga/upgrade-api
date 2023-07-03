<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Account extends BaseModel
{
	protected $connection = 'common';
	
    protected $fillable = [
        'company_id','name','email', 'password', 'company', 'mobile', 'logo','remeber_token'
     ];

     protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];
}
