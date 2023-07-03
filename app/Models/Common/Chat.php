<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Chat extends BaseModel
{
    protected $connection = 'common';
	
    protected $hidden = [
     	'company_id', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function getDataAttribute() {
        return json_decode($this->attributes['data']) ;
        
    }
}
