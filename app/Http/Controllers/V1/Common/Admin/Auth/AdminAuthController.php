<?php

namespace App\Http\Controllers\V1\Common\Admin\Auth;

use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Common\AuthLog;
use App\Models\Common\Admin;
use App\Models\Common\Setting;
use App\Traits\Encryptable;
use Spatie\Permission\Models\Role;
use Auth;
use DB;
use Illuminate\Validation\Rule;

class AdminAuthController extends Controller
{
    protected $jwt;
    use Encryptable;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
        ]);

        try {
            
            $company_id =  base64_decode($request->salt_key);

            $type = strtoupper($request->role);

            if($type == "ADMIN"){
                $user =  Admin::where('email',$request->email)->whereNotIn('type',['FLEET','DISPATCHER','DISPUTE','ACCOUNT'])->where('company_id',$company_id)->first();
            }else{
                $user = Admin::where('email',$request->email)->where('type', $type)->where('company_id',$company_id)->first();
            }

            if($user)
            {   
                if($user->status==1)
                {
                    if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                        return Helper::getResponse(['status' => 401, 'message' => 'Invalid Credentials']);
                    }
                }else{
                    return Helper::getResponse(['status' => 422, 'message' => 'Account Disabled']);
                }
            }else{
                return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return Helper::getResponse(['status' => 500, 'message' => 'token_expired']);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return Helper::getResponse(['status' => 500, 'message' => 'token_expired']);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return Helper::getResponse(['status' => 500, 'message' => $e->getMessage()]);

        }

        AuthLog::create(['user_type' => $user->type, 'user_id' => \Auth::id(), 'type' => 'login', 'data' => json_encode(
            ['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
            'host' => $request->getHost(), 
            'ip' => $request->getClientIp(), 
            'user_agent' => $request->userAgent(), 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
        )]);

        return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => Auth::user()]]);

    }

    public function refresh(Request $request) {

        $this->jwt->setToken($this->jwt->getToken());

        return Helper::getResponse(['data' => [
                "token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $this->jwt->refresh()
            ]]);
    }
    public function logout(Request $request) {
        try {

            $this->jwt->setToken($this->jwt->getToken());
            
            $this->jwt->invalidate();

            AuthLog::create(['user_type' => \Auth::user()->type, 'user_id' => \Auth::id(), 'type' => 'logout', 'data' => json_encode(
                ['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
                'host' => $request->getHost(), 
                'user_agent' => $request->userAgent(), 
                'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
            )]);

            return Helper::getResponse(['message' => 'Successfully logged out']);

        } catch (JWTException $e) {

            return Helper::getResponse(['status' => 403, 'message' => $e->getMessage()]);
        }
    }
    //Role Permission data taken in cache. 
    public function permission_list(Request $request)
    {
        $user = Admin::where('id',\Auth::id())->first();
        $model_has_permission =  DB::table('model_has_roles')->where('model_id',$user->id)->first();
        $role_details = Role::where('id',$model_has_permission->role_id)->first();

        $role_has_permission =  DB::table('role_has_permissions')
                                ->where('role_id',$role_details->id)
                                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')->select('name')
                                ->get();
        $permission =[];
        foreach($role_has_permission as $permission_name){
            $permission[]=$permission_name->name;
        }
        return $permission;
    }

    public function forgotPasswordOTP(Request $request){
        
        $response = $this->forgotPasswordEmail($request);
       
        return $response;
    }

    public function forgotPasswordEmail($request) {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'salt_key' => 'required',
            'account_type' => 'required',
        ]);
        $emaildata['username'] = $toEmail = isset($request->email)?$request->email:'';
        $emaildata['account_type'] = isset($request->account_type)?$request->account_type:'';
        $type = strtoupper($request->account_type);
        try {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
            ]);
            $company_id = base64_decode($request->salt_key);
            $request->request->add(['company_id' => base64_decode($request->salt_key)]);
            $request->request->remove('salt_key');
            $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
            $siteConfig = $settings->site;            
            $otp = mt_rand(100000, 999999);
            $userQuery = Admin::where('email' , $request->email)->where('company_id', $company_id)->where('type',$type)->first();
            //User Not Exists
            $validator  = Validator::make([],[],[]);
            if($userQuery == null) {
                $validator->errors()->add('mobile', 'User not found');
                throw new \Illuminate\Validation\ValidationException($validator); 
            }
            $userQuery->otp = $otp;
            $userQuery->save();
            $emaildata['otp'] = $otp;
            if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
                if( $siteConfig->mail_driver == 'SMTP') {
                //  SEND OTP TO MAIL
                    $subject='Forgot|OTP';
                    $templateFile='mails/forgotpassmail';
                    $data=['body'=>$otp,'username'=>$userQuery->name, 'salt_key' => $company_id];

                    $result= Helper::send_emails($templateFile,$toEmail,$subject, $data); 
                }else{
                    return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => '']);  
                }  
            }else{
                $errMessage = 'Mail configuration disabled';
            }
            return Helper::getResponse(['status' => 200, 'message'=>'success','data'=>$emaildata]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function resetPasswordOTP(Request $request) {
        
        $this->validate($request, [
            'username' => 'required',
            'type' => 'required',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed|max:36',
        ]);
        $responseData = $request->all();
        try {
            $username = isset($request->username)?$request->username:'';
            $newpassword = isset($request->password)?$request->password:'';
            $otp = isset($request->otp)?$request->otp:'';
            $request->merge([
                'loginUser' => $this->cusencrypt($username,env('DB_SECRET'))            
            ]);
            
            $where = ['email' => $request->loginUser, 'type' => $request->type];

            $userQuery = Admin::where($where)->first();
                //User Not Exists
            $validator  = Validator::make([],[],[]);
            if($userQuery == null) {         
                $validator->errors()->add('Result', 'User not found');
                throw new \Illuminate\Validation\ValidationException($validator); 
            }else{
                $dbOtpCode = $userQuery->otp;
                if($dbOtpCode != $otp){
                    $validator->errors()->add('Result', 'Invalid Credentials');
                    throw new \Illuminate\Validation\ValidationException($validator);
                }
                $enc_newpassword = Hash::make($newpassword);
                $input =['password' => $enc_newpassword];
                $userQuery->password = $enc_newpassword;
                $userQuery->otp = 0;
                $userQuery->save();
            }
            return Helper::getResponse(['status' => 200, 'message'=>'Password changed successfully','data'=>$responseData]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }            
    }
}
