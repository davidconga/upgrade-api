<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class ProviderDocument extends BaseModel
{
    protected $connection = 'common';

    protected $fillable = [
        'provider_id', 'document_id', 'company_id', 'url', 'status', 'expires_at'
    ];

    protected $hidden = [
        'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function document()
    {
        return $this->belongsTo('App\Models\Common\Document', 'document_id', 'id');
    }
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id', 'id');
    }
    public function getUrlAttribute() {
        return ($this->attributes['url'] != null) ? json_decode($this->attributes['url']) : $this->attributes['url'];
 
    }
}
