<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Company extends BaseModel
{
	protected $connection = 'common';

	protected $fillable = [
        'company_name','domain','base_url', 'socket_url', 'access_key', 'expiry_date'
    ];
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];
}
