<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Country extends BaseModel
{
	protected $connection = 'common';
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function states() {
    	return $this->hasMany('App\Models\Common\State');
    }

    public function city() {
    	return $this->hasMany('App\Models\Common\City', 'country_id', 'id');
    }

    public function company_city() {
        return $this->hasMany('App\Models\Common\CompanyCity', 'country_id', 'id');
    }

    public function bank_form() {
        return $this->hasMany('App\Models\Common\CountryBankForm', 'country_id', 'id');
    }
}
