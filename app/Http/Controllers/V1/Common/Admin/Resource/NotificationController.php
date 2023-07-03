<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\Actions;
use App\Models\Common\Notifications;
use App\Helpers\Helper;
use Auth;

class NotificationController extends Controller
{
  

    use Actions;

    private $model;
    private $request;

    public function __construct(Notifications $model)
    {
        $this->model = $model;
    }

  

     public function index(Request $request)
    {
        $datum = Notifications::where('company_id', Auth::user()->company_id);

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
            'notify_type' => 'required', 
            'service' => 'required',          
            'image' => 'required|mimes:jpeg,jpg,png|max:5242880',           
        ]);
        try{
            $Notifications = new Notifications;
            $Notifications->notify_type = $request->notify_type;
            if($request->hasFile('image')) {
                $Notifications->image = Helper::upload_file($request->file('image'), 'Notification/image');
            }
            $Notifications->company_id = Auth::user()->company_id;  
            $Notifications->service = $request->service;  
            $Notifications->descriptions = $request->descriptions;                                      
            $Notifications->title = $request->title;                                      
            $Notifications->expiry_date = date('Y-m-d H:i:s', strtotime($request->expiry_date));
            $Notifications->status = $request->status;                    
            $Notifications->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function show($id)
    {
        try {
            $notification = Notifications::findOrFail($id);
            $expiry_date=date('d/m/Y',strtotime($notification->expiry_date));
            // $data = array();
            // foreach ($notification as $key => $value) {
            //     if($key == "expiry_date"){
            //      $data[$key] = date('d/m/Y',strtotime($value));
            //     }else{
            //     $data[$key] = $value;
            // }
            // }
           // $notification->save();
            // $notification->toarray();
            
                return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'notify_type' => 'required',
            'service' => 'required',   
        ]);
        try {
            $Notifications = Notifications::findOrFail($id);
            $Notifications->notify_type = $request->notify_type;            
            if($request->hasFile('image')) {
                $Notifications->image = Helper::upload_file($request->file('image'), 'Notification/image');
            }
            $Notifications->service = $request->service;  
            $Notifications->descriptions = $request->descriptions; 
            $Notifications->title = $request->title; 
            $Notifications->expiry_date = date('Y-m-d H:i:s',strtotime($request->expiry_date));
            $Notifications->status = $request->status;                    
            $Notifications->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
           
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }
    public function destroy($id)
    {
        return $this->removeModel($id);
    }


}
