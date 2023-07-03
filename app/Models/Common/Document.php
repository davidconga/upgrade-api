<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use Auth;

class Document extends BaseModel
{
	protected $connection = 'common';
	
    protected $fillable = [
        'name',
        'type',
        'company_id',
        'name_arabic',
        'name_portuguese'

    ];
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];
    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%")
            ->orWhere('type', 'like', "%" . $searchText . "%");
    }
    public function provider_document()
    {
        return $this->belongsTo('App\Models\Common\ProviderDocument', 'id', 'document_id')->where('provider_id',Auth::guard('provider')->user()->id);
    }
    public function service_categories()
    {
        return $this->hasOne('App\Models\Service\ServiceCategory', 'id','service_category_id');
    }
}
