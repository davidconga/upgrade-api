<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use App\Traits\Encryptable;
class Fleet extends BaseModel
{
	protected $connection = 'common';

     use Encryptable;

    protected $encryptable = [
        'email',
        'mobile'       
    ];
	
    protected $fillable = [
       'company_id','name','email', 'password', 'company', 'mobile', 'logo','remeber_token','commission','country_code','country_id','city_id','currency_symbol',
    ];

    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

         public function scopeSearch($query, $searchText='') {
            return $query
            ->where('name', 'like', "%" . $searchText . "%")
            ->orWhere('email', 'like', "%" . $this->cusencrypt($searchText,env('DB_SECRET')) . "%") 
            ->orWhere('mobile', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')) . "%");
           
        }
}
