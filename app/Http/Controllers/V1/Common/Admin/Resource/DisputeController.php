<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use Spatie\Permission\Models\Role;
use App\Models\Common\Dispute;
use DB;
use Auth;

class DisputeController extends Controller
{
    use Actions;

        private $model;
        private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Dispute $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datum = Dispute::where('company_id', Auth::user()->company_id);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $datum = $datum->paginate(10);

        return Helper::getResponse(['data' => $datum]);
        
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'service' => 'required',
            'dispute_type' => 'required',
            'dispute_name' => 'required',  
            'status' => 'required',             
        ]);

        try{
            //PeakHour::create($request->all());
            $request->request->add(['company_id' => \Auth::user()->company_id]);
            $Dispute = new Dispute;
            $Dispute->company_id = Auth::user()->company_id;  
            $Dispute->service = $request->service;
            $Dispute->admin_services = $request->service;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->dispute_name = $request->dispute_name;
            $Dispute->status = $request->status;                    
            $Dispute->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $dispute = Dispute::findOrFail($id);
            return Helper::getResponse(['data' => $dispute]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'service' => 'required',
            'dispute_type' => 'required',
            'dispute_name' => 'required', 
            'status' => 'required',  
        ]);

        try {

            $Dispute = Dispute::findOrFail($id);
            $Dispute->service = $request->service;
            $Dispute->admin_services = $request->service;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->dispute_name = $request->dispute_name;                    
            $Dispute->status = $request->status;                    
            $Dispute->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);   
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->removeModel($id);
    }

}
