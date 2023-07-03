<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Common\Setting;
use App\Models\Common\AuthLog;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Auth;
use DB;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Models\Common\Zone;
use App\Models\Common\PayrollTemplate;
class PayrollTemplateController extends Controller
{
        use Actions;
    use Encryptable;

    private $model;
    private $request;

    public function __construct(Payrolltemplate $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
    	$datum = PayrollTemplate::with('zone')->where('company_id',Auth::user()->company_id);

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
            'template_name' => 'required|max:255',
            'zone_id' => 'required'
        ]);

        try{
            $request->request->add(['company_id' => Auth::user()->company_id]);
            $zone = $request->all();
            $returndata = PayrollTemplate::create($zone); 
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create'),'data'=>$returndata]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

    }
     public function show($id)
    {
        try {
            $zone = PayrollTemplate::findOrFail($id);
           //$zone['city_data']=CompanyCity::where("city_id",$zone['city_id'])->with('city')->get();
           //$zone['country_data']=CompanyCountry::where("company_id",$zone['company_id'])->with('country')->get();

            return Helper::getResponse(['data' => $zone]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'template_name' => 'required|max:255',
            'zone_id' => 'required',
            // 'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);
        try {
            $zone = PayrollTemplate::findOrFail($id);
            $zone->template_name = $request->template_name;
            $zone->zone_id = $request->zone_id;
            $zone->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = PayrollTemplate::findOrFail($id);
            if($request->status=='ACTIVE'){
            	$datum->status = 'INACTIVE';
            }else{
            	$datum->status = 'ACTIVE';
        	}
            
            $datum->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        $datum = PayrollTemplate::findOrFail($id);
        return $this->removeModel($id);
    }
    public function zonetemplates(){
        $zone_template = PayrollTemplate::where('company_id',Auth::user()->company_id)->get();
        return Helper::getResponse(['data' => $zone_template]);
    }
}
