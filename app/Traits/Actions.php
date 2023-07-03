<?php

namespace App\Traits;

use App\Helpers\Helper;
use App\Models\Common\Setting;
use Auth;

trait Actions
{

    public $settings;
    public $user;
    public $company_id;

    public function __construct() {
        $this->settings = Helper::setting();
        $this->user = Auth::guard(strtolower(Helper::getGuard()))->user();
        $this->company_id = $this->user->company_id;
    }

	public function removeModel($id)
    {
        try{
            $model = $this->model->find($id);

            $model->delete();
            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.user_msgs.user_not_found'), 'error' => $e->getMessage()]);
        }
    }

    public function removeMultiple()
    {
    	
        try{
            $request = $this->request;
            $items = explode(',', $request->id);

            $this->model->destroy($items);

            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.user_msgs.user_not_found'), 'error' => $e->getMessage()]);
        }

    }

    public function changeStatus()
    {
    	$request = $this->request;

        try{
            $this->model->where('id', $request->id)->update(['status' => $request->status]);

            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.user_msgs.user_not_found'), 'error' => $e->getMessage()]);
        }
        
    }

    public function changeStatusAll()
    {
        try{
    	    $request = $this->request;
            $items = explode(',', $request->id);

            $this->model->whereIn('id', $items)->update(['status' => $request->status]);

            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.user_msgs.user_not_found'), 'error' => $e->getMessage()]);
        }
    }

    public function sendUserData($maildata)
    {
        try{
            $settings = Setting::where('company_id', \Auth::user()->company_id)->first()->settings_data->site;

            
            if( !empty($settings->send_email) && $settings->send_email == 1) {
               $toEmail = isset($maildata['email'])?$maildata['email']:'';

                if(isset($maildata['first_name'])){
                    $name = $maildata['first_name'];
                }else{
                    $name = $maildata['name'];
                }
                     
            //  SEND MAIL TO USER, PROVIDER, FLEET
                $subject = "Notification";
                $data=['body'=> $maildata['body'],'username'=> $name,'contact_mail' => $settings->contact_email, 'contact_number' => $settings->contact_number[0]->number];
                

                $templateFile='mails/notification_mail';


                Helper::send_emails($templateFile,$toEmail,$subject, $data);

            } 
                          
            return true;
        } 
        catch (\Throwable $e) {           
            throw new \Exception($e->getMessage());
        } 

    }
}