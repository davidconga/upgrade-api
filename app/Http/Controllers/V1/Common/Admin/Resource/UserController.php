<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Common\User;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCountry;
use App\Models\Common\Setting;
use App\Models\Common\AuthLog;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use App\Services\SendPushNotification;
use Auth;
use DB;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Services\ReferralResource;


class UserController extends Controller
{
    use Actions;
    use Encryptable;

    private $model;
    private $request;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {

        $datum = User::where('company_id', Auth::user()->company_id)->orderby('id', 'DESC');

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => $request->email != null ?'sometimes|required|email|max:255':'',
            'mobile' => $request->mobile != null ?'sometimes|required|digits_between:6,13':'',
            'gender' => 'required|in:MALE,FEMALE',
            'country_code' => 'required|max:25',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
            'country_id' => 'required',
            'city_id' => 'required',
        ]);

        $company_id=Auth::user()->company_id;
        if($request->has('email') && $request->has('mobile')) {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
            ]); 


            
            $email=$request->email;
            $mobile=$request->mobile;
            

            $this->validate($request, [          
                'email' =>[ Rule::unique('users')->where(function ($query) use($email,$company_id) {
                                return $query->where('email', $email)->where('company_id', $company_id);
                             }),
                           ],
                'mobile' =>[ Rule::unique('users')->where(function ($query) use($mobile,$company_id) {
                                return $query->where('mobile', $mobile)->where('company_id', $company_id);
                             }),
                           ],
            ]);
        }

        try{

            if($request->has('email') && $request->has('mobile')) {
                $request->merge([
                    'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                    'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
                ]);
            }

            $request->request->add(['company_id' => Auth::user()->company_id]);
            $user = $request->all();

            $user['payment_mode'] = 'CASH';
            $user['password'] = Hash::make($request->password);
           
            $user['referral_unique_id']=(new ReferralResource)->generateCode($company_id);

            $user = User::create($user);

            if($request->has('state_id')){
                $user->state_id = $request->state_id;
            }

            $user->qrcode_url = Helper::qrCode(json_encode(["country_code" => $request->country_code, 'phone_number' => $request->mobile]), $user->id.'.png', Auth::user()->company_id);
            if($request->hasFile('picture')) {
                $user->picture = Helper::upload_file($request->file('picture'), 'user/profile', $user->id.'.png');
            }

            $country = CompanyCountry::where('company_id',Auth::user()->company_id)->where('country_id', $request->country_id)->first();
            $user->currency_symbol = $country->currency;

            $user->save();

            $request->merge(["body" => "registered"]);
            if($request->has('email') && $request->has('mobile')) {
            $this->sendUserData($request->all());
            }
           
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
           $user['city_data']=CompanyCity::where("country_id",$user['country_id'])->with('city')->get();

            return Helper::getResponse(['data' => $user]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
      
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'country_code' => 'required|max:25',
            'email' => $request->email != null ?'sometimes|required|email|max:255':'',
            'mobile' => $request->mobile != null ?'sometimes|digits_between:6,13':'',
            'country_id' => 'required',
            'city_id' => 'required',
            // 'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);
        $company_id=Auth::user()->company_id;
        if($request->has('email') && $request->has('mobile')) {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
            ]);

            
            $email=$request->email;
            $mobile=$request->mobile;
            

            $this->validate($request, [          
                'email' =>[ Rule::unique('users')->where(function ($query) use($email,$company_id,$id) {
                                return $query->where('email', $email)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                             }),
                           ],
                'mobile' =>[ Rule::unique('users')->where(function ($query) use($mobile,$company_id,$id) {
                                return $query->where('mobile', $mobile)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                             }),
                           ],
            ]);
        }

        try {
            if($request->has('email') && $request->has('mobile')) {
                $request->merge([
                    'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                    'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
                ]);
            }
          
            $user = User::findOrFail($id);

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            if($request->has('email') && $request->has('mobile')) {
            $user->country_code = $request->country_code;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            }
            $user->country_id = $request->country_id;
            $user->city_id = $request->city_id;
            if($request->password != "" && $request->password != null){

                $user->password = Hash::make($request->password);
            }
            $user->qrcode_url = Helper::qrCode(json_encode(["country_code" => $request->country_code, 'phone_number' => $request->mobile]), $user->id.'.png', Auth::user()->company_id);
            if($request->hasFile('picture')) {
                $user->picture = Helper::upload_file($request->file('picture'), 'user/profile', $user->id.'.png');
            }

            $country = CompanyCountry::where('company_id', Auth::user()->company_id)->where('country_id', $request->country_id)->first();
            $user->currency_symbol = $country->currency;

            $user->save();

            $request->merge(["body"=>"updated"]);
            if($request->has('email') && $request->has('mobile')) {
            // $this->sendUserData($request->all());
            }
            
            app('redis')->publish('message', json_encode($request->all()));

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = User::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->status = 0;
                }else{
                    $datum->status = 1;
                }
            }
            $datum->save();

            if($request->status == 1){
                $status = "disabled";
                if($datum->jwt_token != null) {
                    Auth::guard('user')->setToken($datum->jwt_token);
                    try {
                        Auth::guard('user')->invalidate();
                    } catch (\Throwable $e) { }
                    
                    $datum->jwt_token = null;
                    $datum->save();
                }
            }else{
                $status = "enabled";
            }

            $datum['body'] = $status;
            
            $this->sendUserData($datum);

            (new SendPushNotification)->UserStatus($datum->id, 'provider', 'Account '.$status); 

            Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        $datum = User::findOrFail($id);
       
        $datum['body'] = "deleted";
        $this->sendUserData($datum);

        return $this->removeModel($id);
    }


    public function multidestroy(Request $request)
    {
        $this->request = $request;
        return $this->removeMultiple();
    }

    public function statusChange(Request $request)
    {
        $this->request = $request;
        return $this->changeStatus();
    }

    public function statusChangeMultiple(Request $request)
    {
        $this->request = $request;
        return $this->changeStatusAll();
    }
    public function companyuser(Request $request)
    {
        $role = new Role;
        $role->name = strtoupper($request->name);
        $role->guard_name = $request->guard_name;
        $role->company_id = $request->company_id;
        $role->save();
        return Helper::getResponse(['status' => 200, 'message' => trans('Roles with company details created successfully')]);

    }

}
