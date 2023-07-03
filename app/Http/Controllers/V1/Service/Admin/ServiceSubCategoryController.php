<?php

namespace App\Http\Controllers\V1\Service\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Service\ServiceSubcategory;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Models\Service\ServiceCategory;

class ServiceSubCategoryController extends Controller
{
    use Actions;
    private $model;
    private $request;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(ServiceSubcategory $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = ServiceSubcategory::with('serviceCategory')->where('company_id', Auth::user()->company_id);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'service_subcategory_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',            
            // 'service_subcategory_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',            
            'service_category_id'=>'required',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'service_subcategory_order' => 'required|integer|between:0,10',
            'service_subcategory_status' => 'required',
        ]);
        try {
            $SubCategory = new ServiceSubcategory;
            $SubCategory->company_id = Auth::user()->company_id; 
            $SubCategory->service_subcategory_name = $request->service_subcategory_name; 
            $SubCategory->service_category_id = $request->service_category_id;            
            //$SubCategory->service_subcategory_order = $request->service_subcategory_order;
            $SubCategory->service_subcategory_status = $request->service_subcategory_status;
            if($request->hasFile('picture')) {
                $SubCategory->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'subcat-'.time().'.png');
            }
            $SubCategory->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        }catch (\Throwable $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $ServiceSubcategory = ServiceSubcategory::findOrFail($id);
            return Helper::getResponse(['data' => $ServiceSubcategory]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'service_subcategory_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',            
            // 'service_subcategory_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',            
            'service_category_id'=>'required',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'service_subcategory_order' => 'required|integer|between:0,10',
            'service_subcategory_status' => 'required',
        ]);
        try{
            $SubCategory = ServiceSubcategory::findOrFail($id);
            if($SubCategory){
                $SubCategory->service_subcategory_name = $request->service_subcategory_name; 
                $SubCategory->service_category_id = $request->service_category_id;            
                //$SubCategory->service_subcategory_order = $request->service_subcategory_order;
                $SubCategory->service_subcategory_status = $request->service_subcategory_status;
                if($request->hasFile('picture')) {
                    $SubCategory->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'subcat-'.time().'.png');
                }
                $SubCategory->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else{
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
            }
        }catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // ONLY STATUS UPDATE ADDED INSTEAD OF HARD DELETE // return $this->removeModel($id);
        $SubCategory = ServiceSubcategory::findOrFail($id);
        if($SubCategory){
            $SubCategory->service_subcategory_status = 2;
            $SubCategory->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } else{
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
        }
    }

    public function categoriesList()
    {
        $country = ServiceCategory::select('id','service_category_name','service_category_status')->where('service_category_status',1)->get();
        return Helper::getResponse(['data' => $country]);
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = ServiceSubcategory::findOrFail($id);
            
            if($request->has('status') && $request->status == 1){
                
                $datum->service_subcategory_status = 0;
            }else{
                $datum->service_subcategory_status = 1;
            }
            $datum->save();
           
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
