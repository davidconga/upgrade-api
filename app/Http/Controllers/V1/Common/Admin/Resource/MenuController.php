<?php
namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\Actions;
use App\Models\Common\Menu;
use App\Models\Common\CompanyCity;
use App\Models\Common\MenuCity;
use App\Models\Common\City;
use App\Models\Transport\RideType;
use App\Models\Service\ServiceCategory;
use App\Helpers\Helper;
use Auth;
use App\Models\Order\StoreType;
use Illuminate\Validation\Rule;
class MenuController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(Menu $model)
    {
        $this->model = $model;
    }
    public function index(Request $request)
    {
        //$datum = Menu::with('adminservice','ridetype')->where('company_id', Auth::user()->company_id);
        $datum = Menu::with('adminservice')->where('company_id', Auth::user()->company_id);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        $datum->orderby('sort_order', 'asc');

        $datum = $datum->paginate(10);
        return Helper::getResponse(['data' => $datum]);

    }

    public function store(Request $request) 
    {
        $this->validate($request, [
            'bg_color' => 'required', 
            'icon' => 'required|mimes:jpeg,jpg,png|max:5242880',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
            'menu_type_id' => 'required',             
            'sort_order' => 'required',             
        ]);
        $company_id=Auth::user()->company_id;

        $this->validate($request, [          
            'menu_type_id' =>[ Rule::unique('menus')->where(function ($query) use($request,$company_id) {
                            return $query->where('menu_type_id', $request->menu_type_id)->where('company_id', $company_id)->where('admin_service',$request->admin_service);
                         }),
                       ],
         ],['menu_type_id.unique'=>"Already Service is Avaliable In Menu"]);

        if($request->has('is_featured') && $request->is_featured == 1) {
           
            $this->validate($request, [
                'featured_image' => 'required'
            ]);
        }
       
        try{
            $menu = new Menu;
            $menu->bg_color = $request->bg_color;
            if($request->hasFile('icon')) {
                $menu['icon'] = Helper::upload_file($request->file('icon'), 'menus');
            }

            if($request->hasFile('featured_image')) {

                $menu['is_featured'] = 1;
                $menu['featured_image'] = Helper::upload_file($request->file('featured_image'), 'menus/featuredimage');

            }

            $menu->company_id = Auth::user()->company_id;  
            $menu->title = $request->title;  
            $menu->admin_service = $request->admin_service;                                      
            $menu->menu_type_id = $request->menu_type_id;                    
            $menu->sort_order = $request->sort_order;                    
            $menu->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {

            $menu = Menu::findOrFail($id);

            if($menu->admin_service == 'SERVICE'){

                $menu['service_list'] = ServiceCategory::where('company_id', Auth::user()->company_id)->get();

            }elseif ($menu->admin_service == 'TRANSPORT') {
                $menu['service_list'] = RideType::where('company_id', Auth::user()->company_id)->where('status',1)->get();
            }

            return Helper::getResponse(['data' => $menu]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'bg_color' => 'required', 
            // 'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
            'menu_type_id' => 'required',
            'icon' => 'nullable|mimes:jpeg,jpg,png|max:5242880', 
        ]);
        $company_id=Auth::user()->company_id;

         $this->validate($request, [          
            'menu_type_id' =>[ Rule::unique('menus')->where(function ($query) use($request,$company_id, $id) {
                            return $query->where('menu_type_id', $request->menu_type_id)->where('company_id', $company_id)->where('admin_service',$request->admin_service)->whereNotIn('id', [$id]);
                         }),
                       ],
         ],['menu_type_id.unique'=>"Already Service is Avaliable In Menu"]);

        if($request->has('is_featured') && $request->is_featured == 1) {

            $menu = Menu::findOrFail($id);

            if($menu->featured_image == "" && $menu->featured_image == NULL){
           
                $this->validate($request, [
                    'featured_image' => 'required'
                ]);
            }
        }

        if($request->hasFile('icon'))
        {
            $this->validate($request, [
            'icon' => 'required|mimes:jpeg,jpg,png|max:5242880'
            ]); 
        }
        try {
            $menu = Menu::findOrFail($id);
            $pre_sort_id=$menu->sort_order;
            $sortmenu = Menu::where('sort_order',$pre_sort_id)->first();
            $menu->bg_color = $request->bg_color;
            if($request->hasFile('icon')) {
                $menu->icon = Helper::upload_file($request->file('icon'), 'menus');
            }

            if($request->hasFile('featured_image')) {
                $menu['featured_image'] = Helper::upload_file($request->file('featured_image'), 'menus/featuredimage');
            }
            if($request->is_featured != 1){
                $menu['featured_image'] = '';
                $menu['is_featured'] = '';

            }else{
                $menu['is_featured'] = 1;
            }
            $menu->title = $request->title;                                      
            $menu->menu_type_id = $request->menu_type_id;                    
            $menu->sort_order = $request->sort_order;

            //check sort order
            if($pre_sort_id!=$request->sort_order){
                $sortmenu = Menu::where('sort_order',$request->sort_order)->first();
                if(!empty($sortmenu)){                    
                    $sortmenu->sort_order = $pre_sort_id;                    
                    $sortmenu->save();
                }
            }

            $menu->save();

            

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
           
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        return $this->removeModel($id);
    }
    //Update the city in menu details
    public function menucity(Request $request,$id){
        $city_details = $request->city_id;
        $country_id = isset($request->country_id)?$request->country_id:'';
        try{
            if($country_id =='ALL' || $country_id ==''){
                $menucity = MenuCity::where('menu_id',$id)->delete();
            }else{
                $menucity = MenuCity::where('menu_id',$id)->where('country_id',$country_id)->delete();
            }
            if(count($city_details)>0){
                foreach($city_details as $city)
                {
                    $countryId = City::where('id',$city)->first();
                    $menu_city = new MenuCity;
                    $menu_city->menu_id = $id;
                    $menu_city->country_id = $countryId->country_id;
                    $menu_city->state_id = $countryId->state_id;
                    $menu_city->city_id = $city;
                    $menu_city->status = 1;
                    $menu_city->save();
                }
            }
            //sync want to implement..
            if($city_details!=''){
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
            }else{
                return Helper::getResponse(['status' => 404, 'message' => 'You have to check atleast one City.']);
            }
        }catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    //Ride type value
    public function ride_type()
    {
        $vehicle_type_list = RideType::where('company_id', Auth::user()->company_id)->where('status',1)->get();
        return Helper::getResponse(['data' => $vehicle_type_list]);
    }
    //Service Type
    public function service_type()
    {
        $service_list = ServiceCategory::where('company_id', Auth::user()->company_id)->get();
        return Helper::getResponse(['data' => $service_list]);
    }
    

    public function getCountryCity($serviceId, $countryId){
        $company_cities = CompanyCity::with('city')->where('country_id',$countryId)->where('company_id', Auth::user()->company_id)
        ->paginate(500);
        return Helper::getResponse(['data' => $company_cities]);
    }
    public function getmenucity($id)
    {
        $city_details = [];
        $menu_city = MenuCity::select('city_id')->where('menu_id',$id)->get();
        foreach($menu_city as $city)
        {
            $city_details[] = $city->city_id; 
        }
        return $city_details;
    }

     //Service Type
    public function order_type()
    {
        $service_list = StoreType::where('company_id', Auth::user()->company_id)->where('status',1)->get();
        return Helper::getResponse(['data' => $service_list]);
    }

}
