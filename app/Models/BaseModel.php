<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;
use Auth;

class BaseModel extends Model
{
    
    public static function boot()
    {
        parent::boot();

        static::creating(function($page)
        {
            if(Helper::getGuard() != null) {
                $page->created_type = Helper::getGuard();
                $page->created_by = Auth::guard(strtolower(Helper::getGuard()))->user()->id;
                $page->modified_type = Helper::getGuard();
                $page->modified_by = Auth::guard(strtolower(Helper::getGuard()))->user()->id;
            }
        });

        static::updated(function($page)
        {
            if(Helper::getGuard() != null) {
                $page->modified_type = Helper::getGuard();
                $page->modified_by = Auth::guard(strtolower(Helper::getGuard()))->user()->id;
            }
        });

        static::deleted(function($page)
        {
            if(Helper::getGuard() != null) {
                $page->deleted_type = Helper::getGuard();
                $page->deleted_by = Auth::guard(strtolower(Helper::getGuard()))->user()->id;
            }
        });
    }
}
