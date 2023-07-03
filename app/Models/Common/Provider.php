<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Traits\Encryptable;

class Provider extends BaseModel implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    protected $connection = 'common';


    
    use Authenticatable, Authorizable;

    use Encryptable;

    protected $encryptable = [
        'email',
        'mobile'       
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'country_code',
        'country_id',
        'city_id',
        'mobile',
        'address',
        'picture',
        'gender',
        'jwt_token',
        'status',
        'is_online',
        'is_service',
        'is_document',
        'is_bankdetail',
        'latitude',
        'longitude',
        'status',
        'avatar',
        'social_unique_id',
        'fleet',
        'login_by',
        'company_id',
        'city_id',
        'referral_unique_id',
        'rating',
        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $appends = ['current_ride_vehicle','current_store'];

    protected $hidden = [
        'company_id', 'password', 'remember_token',  'email_verified_at',
        'jwt_token', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function getCurrentRideVehicleAttribute()
    {
        return (@$this->request()->first()->request_data != null) ? isset(json_decode($this->request()->first()->request_data)->provider_service_id) ? json_decode($this->request()->first()->request_data)->provider_service_id : null : null;
    }

    public function getCurrentStoreAttribute()
    {
        return (@$this->request()->first()->request_data != null) ? isset(json_decode($this->request()->first()->request_data)->store_id) ? json_decode($this->request()->first()->request_data)->store_id : null : null;
     }

    public function document($id)
    {
        return $this->hasOne('App\ProviderDocument')->where('document_id', $id)->first();
    }

    public function totaldocument()
    {
        return $this->hasmany('App\Models\Common\ProviderDocument','provider_id');
    }
   
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function request_filter()
    {
        return $this->hasMany('App\Models\Common\RequestFilter', 'provider_id');
    }
    public function providerservice()
    {
        return $this->hasMany('App\Models\Common\ProviderService', 'provider_id');
    }
    public function provider_vehicle()
    {
        return $this->hasMany('App\Models\Common\ProviderVehicle', 'provider_id');
    }
    public function service()
    {
        return $this->hasOne('App\Models\Common\ProviderService', 'provider_id');
    }
    public function country()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }
    public function state()
    {
        return $this->belongsTo('App\Models\Common\State', 'state_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo('App\Models\Common\Admin', 'admin_id', 'id');
    }
    public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }
    public function rating()
    {
        return $this->belongsTo('App\Models\Common\Rating', 'id', 'provider_id');
    }
    public function service_city()
    {
        return $this->hasOne('App\Models\Service\ServiceCityPrice', 'city_id','city_id')->with('city');
    }
    public function request()
    {
        return $this->hasOne('App\Models\Common\UserRequest', 'provider_id', 'id');
    }
    public function current_vehicle()
    {
        return $this->hasOne('App\Models\Common\ProviderService', 'provider_id', 'current_ride_vehicle');
    }
    public function current_store_detail()
    {
        return $this->hasOne('App\Models\Order\Store', 'id', 'current_store')->select('id','store_type_id','store_name');
    }

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('first_name', 'like', "%" . $searchText . "%")
            ->orWhere('last_name', 'like', "%" . $searchText . "%") 
            ->orWhere('email', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%")
            ->orWhere('mobile', 'like', "%" . $this->cusencrypt($searchText,env('DB_SECRET')) . "%")
            ->orWhere('wallet_balance', 'like', "%" . $searchText . "%")
            ->orWhere('rating', 'like', "%" . $searchText . "%");
    }

    public function scopeProviderSearch($query, $searchText='',$type) {
        
           
            return $query
            ->with(['service' => function($q) use ($searchText,$type) {
                if($type == "ORDER"){
                    $q->where('admin_service','ORDER');
                    $q->where('category_id',$searchText);
                }
                }]);
          
    }
   
}

    

  


