<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class CompanyCityAdminService extends BaseModel
{
	protected $connection = 'common';
	
    public function admin_service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }
}