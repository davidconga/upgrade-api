<?php
namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\Actions;
use App\Models\Common\PeakHour;
use App\Models\Common\CompanyCity;
use App\Models\Common\State;
use App\Helpers\Helper;
use Auth;
use Carbon\Carbon;

class PeakHourController extends Controller
{
  

    use Actions;

    private $model;
    private $request;

    public function __construct(PeakHour $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
     

     $datum = PeakHour::with('city')->where('company_id', Auth::user()->company_id);

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
            'city_id'=>'required',
            'start_time' => 'required',
            'end_time' => 'required',           
        ]);

        try{
            $PeakHour = new PeakHour;
            $state_id=CompanyCity::select('state_id')->where('city_id',$request->city_id)->where('company_id',Auth::user()->company_id)->first();
           
            $timezone=isset($state_id->state_id) ? State::find($state_id->state_id)->timezone : 'UTC';

            $PeakHour->start_time = (Carbon::createFromFormat('H:i:s', (Carbon::parse($request->start_time)->format('H:i:s')), $timezone))->setTimezone('UTC');  
            $PeakHour->end_time = (Carbon::createFromFormat('H:i:s', (Carbon::parse($request->end_time)->format('H:i:s')), $timezone))->setTimezone('UTC');
            $PeakHour->company_id=Auth::user()->company_id;
            $PeakHour->city_id=$request->city_id; 
            $PeakHour->timezone= $timezone;                 
            $PeakHour->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $PeakHour = PeakHour::findOrFail($id);
            $country_id=CompanyCity::select('country_id')->where('city_id',$PeakHour->city_id)->where('company_id',Auth::user()->company_id)->first();
            $PeakHour['city_data']=CompanyCity::where("country_id",$country_id->country_id)->with('city')->get();
            $PeakHour['country_id']=$country_id->country_id;
            $PeakHour['end_time']=$PeakHour['ended_time'];
            $PeakHour['start_time']=$PeakHour['started_time'];

                return Helper::getResponse(['data' => $PeakHour]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        
        $this->validate($request, [
            'city_id'=>'required',
            'start_time' => 'required',
            'end_time' => 'required',           
        ]);

        try{
            $PeakHour =  PeakHour::findOrFail($id);
            $state_id=CompanyCity::select('state_id')->where('city_id',$request->city_id)->where('company_id',Auth::user()->company_id)->first();
           
            $timezone=isset($state_id->state_id) ? State::find($state_id->state_id)->timezone : 'UTC';
            $PeakHour->start_time = (Carbon::createFromFormat('H:i:s', (Carbon::parse($request->start_time)->format('H:i:s')), $timezone))->setTimezone('UTC');  
            $PeakHour->end_time = (Carbon::createFromFormat('H:i:s', (Carbon::parse($request->end_time)->format('H:i:s')), $timezone))->setTimezone('UTC');
            $PeakHour->city_id=$request->city_id; 
            $PeakHour->timezone= $timezone;                       
            $PeakHour->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
           
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

    public function destroy($id)
    {
        try{
            \App\Models\Transport\RidePeakPrice::where('peak_hour_id',$id)->delete();
             return $this->removeModel($id);

        }catch(\Throwable $e) { 
         return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
       
        }  
        
    }


}
