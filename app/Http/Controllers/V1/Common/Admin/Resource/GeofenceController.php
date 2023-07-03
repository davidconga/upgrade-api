<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Models\Common\GeoFence;
use App\Models\Common\CompanyCity;
use DB;
use Auth;
use Carbon\Carbon;

class GeofenceController extends Controller
{
    use Actions;

    private $model;
    private $request;
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GeoFence $model)
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
        
      $datum = GeoFence::with('city')->where('company_id', Auth::user()->company_id);
        
        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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
            'city_id' => 'required|numeric',
            'location_name' => 'required',
            'ranges' => 'required',
        ]);

        try{
            $geofence = new GeoFence;
            $geofence->company_id = Auth::user()->company_id;  
            $geofence->city_id = $request->city_id;   
            $geofence->location_name = $request->location_name; 
            $geofence->ranges = $request->ranges;              
            $geofence->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $geofence = GeoFence::findOrFail($id);
            $country = CompanyCity::where('city_id', $geofence->city_id)->first();
            $geofence->country_id = $country->country_id;
            return Helper::getResponse(['data' => $geofence]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'city_id' => 'required|numeric',
            'location_name' => 'required',
            'ranges' => 'required',
        ]);

        try {

            $geofence = GeoFence::findOrFail($id);
            $geofence->company_id = Auth::user()->company_id;  
            $geofence->city_id = $request->city_id;   
            $geofence->location_name = $request->location_name; 
            $geofence->ranges = $request->ranges;             
            $geofence->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);    
        } 

        catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = GeoFence::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->status = 0;
                }else{
                    $datum->status = 1;
                }
            }
            $datum->save();


            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->removeModel($id);
    }
}
