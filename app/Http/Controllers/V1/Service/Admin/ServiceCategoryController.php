<?php

namespace App\Http\Controllers\V1\Service\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Service\ServiceCategory;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Auth;
use Validator;
use DB;
use App\Models\Common\AdminService;
use App\Models\Common\Menu;

class ServiceCategoryController extends Controller
{
    use Actions;
    private $model;
    private $request;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(ServiceCategory $model)
    {
        $this->model = $model;
    }
    public function index(Request $request)
    {
        $datum = ServiceCategory::where('company_id', Auth::user()->company_id);
        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }
        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }
        $data = $datum->paginate(10);
        return Helper::getResponse(['data' => $data]);
    }
    public function indexlist(Request $request)
    {
        $datum = ServiceCategory::where('company_id', Auth::user()->company_id)->get();
        // if($request->has('search_text') && $request->search_text != null) {
        //     $datum->Search($request->search_text);
        // }
        // if($request->has('order_by')) {
        //     $datum->orderby($request->order_by, $request->order_direction);
        // }
        // $data = $datum->paginate(10);
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
            'service_category_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',
            'service_category_alias_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',

            // 'service_category_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{u00C0}-\x{u00ff}\s]+$/',
            // 'service_category_alias_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{u00C0}-\x{u00ff}\s]+$/',

            
            // 'service_category_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            // 'service_category_alias_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            /*'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880', u0041-\u007A /^[\u0041-\u007A]+$/
            'service_category_order' => 'required|integer|between:0,10',*/ 
            'service_category_status' => 'required',
        ]);
        try {
            $ServiceCategory = new ServiceCategory;
            $ServiceCategory->company_id = Auth::user()->company_id; 
            $ServiceCategory->service_category_name = $request->service_category_name;
            $ServiceCategory->alias_name = $request->service_category_alias_name;            
            //$ServiceCategory->service_category_order = $request->service_category_order;
            $ServiceCategory->service_category_status = $request->service_category_status;
            $ServiceCategory->price_choose = $request->price_choose;
            /*if($request->hasFile('picture')) {
                $ServiceCategory->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'cat-'.time().'.png');
            }*/
            $ServiceCategory->save();
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
            $ServiceCategory = ServiceCategory::findOrFail($id);
            return Helper::getResponse(['data' => $ServiceCategory]);
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
            'service_category_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',
            'service_category_alias_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',

            // 'service_category_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            // 'service_category_alias_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            /*'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'service_category_order' => 'required|integer|between:0,10',*/
            'service_category_status' => 'required',
        ]);
        try{
            $ServiceCategory = ServiceCategory::findOrFail($id);
            if($ServiceCategory){
                $ServiceCategory->service_category_name = $request->service_category_name;
                $ServiceCategory->alias_name = $request->service_category_alias_name;           
                //$ServiceCategory->service_category_order = $request->service_category_order;
                $ServiceCategory->service_category_status = $request->service_category_status;
                $ServiceCategory->price_choose = $request->price_choose;
                /*if($request->hasFile('picture')) {
                    $ServiceCategory->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'cat-'.time().'.png');
                }*/
                $ServiceCategory->save();
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
        $ServiceCategory = ServiceCategory::findOrFail($id);
        if($ServiceCategory){
            $ServiceCategory->service_category_status = 2;
            $ServiceCategory->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } else{
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = ServiceCategory::findOrFail($id);
            
            if($request->has('status') && $request->status == 1){
                
                $datum->service_category_status = 0;
            }else{
                $datum->service_category_status = 1;
            }
            $datum->save();

            $menu = Menu::where('menu_type_id',$id)->where('admin_service','SERVICE')->where('company_id',Auth::user()->company_id)->first();
            if(!empty($menu)){

                $menu->status = $datum->service_category_status;
                $menu->save();
            }
           
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
