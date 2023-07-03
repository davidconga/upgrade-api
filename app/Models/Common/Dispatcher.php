<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Dispatcher extends BaseModel
{
	protected $connection = 'common';
	
    protected $fillable = [
        'company_id','name','email', 'password',  'mobile',
     ];
}
