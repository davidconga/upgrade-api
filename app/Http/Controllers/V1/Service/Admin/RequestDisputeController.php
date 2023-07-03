<?php

namespace App\Http\Controllers\V1\Service\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Service\Service;
use App\Models\Service\ServiceRequestDispute;
use App\Models\Common\Dispute;
use App\Models\Service\ServiceRequest;
use App\Services\Transactions;




use Illuminate\Support\Facades\Storage;
use Auth;

class RequestDisputeController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(ServiceRequestDispute $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = ServiceRequestDispute::where('company_id', Auth::user()->company_id)
                                    ->with('user','provider','request')
                                    ->orderBy('created_at' , 'desc');

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
            'request_id' => 'required',
            'dispute_type' => 'required', 
            'dispute_name' => 'required',        
        ]);

        try{
            $Dispute = new ServiceRequestDispute();
            $Dispute->company_id = Auth::user()->company_id; 
            $Dispute->service_request_id = $request->request_id;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->user_id = $request->user_id;
            $Dispute->provider_id = $request->provider_id;
            $Dispute->dispute_name = $request->dispute_name;
            if(!empty($request->dispute_other))
                $Dispute->dispute_name = $request->dispute_other;
            $Dispute->comments = $request->comments;                    
            $Dispute->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
     }


    public function show($id)
    {
        try {
            $RequestDispute = ServiceRequestDispute::with('user','provider','request')->findOrFail($id);
            $serviceQuery = Service::where('id',$RequestDispute->request->service_id)->first();
            $RequestDispute->service = $serviceQuery;
            return Helper::getResponse(['data' => $RequestDispute]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
      
        $this->validate($request, [            
            'comments' => 'required', 
            'status' => 'required',        
        ]);

        try{

            $Dispute = ServiceRequestDispute::findOrFail($id);
            $Dispute->comments = $request->comments;                    
            $Dispute->refund_amount = $request->refund_amount;
            $Dispute->status ='closed';                    
            $Dispute->save();
            if($request->refund_amount>0){
                $transaction['message']='Service amount refund';
                $transaction['amount']=$request->refund_amount;
                $transaction['company_id']=$Dispute->company_id;
                    if($Dispute->dispute_type=='user'){
                       $transaction['id']=$Dispute->user_id;
                       (new Transactions)->disputeCreditDebit($transaction);
                    } 
                    else
                    {
                        $transaction['id']=$Dispute->provider_id;
                        (new Transactions)->disputeCreditDebit($transaction,0);
                    }
            }

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        // ONLY STATUS UPDATE ADDED INSTEAD OF HARD DELETE // return $this->removeModel($id);
        $Service = ServiceRequestDispute::findOrFail($id);
        if($Service){
            $Service->active_status = 2;
            $Service->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } else{
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
        }
    }
  

    public function dispute_list(Request $request)
    {
        $this->validate($request, [
            'dispute_type' => 'required'         
        ]);

        $dispute = Dispute::select('dispute_name')->where('dispute_type' , $request->dispute_type)->where('status' , 'active')->get();

        return $dispute;
    }
    
}

