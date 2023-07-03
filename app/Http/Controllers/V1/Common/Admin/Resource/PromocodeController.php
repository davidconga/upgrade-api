<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Models\Common\Promocode;
use DB;
use Auth;
use Carbon\Carbon;

class PromocodeController extends Controller
{
    use Actions;

    private $model;
    private $request;
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PromoCode $model)
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
        
      $datum = Promocode::where('company_id', Auth::user()->company_id);
        
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
        return view('admin.promocode.create');
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
            'promo_code' => 'required|max:100|unique:promocodes',
            'percentage' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'expiration' => 'required',
            'service' => 'required',
            'picture' => 'required|mimes:jpeg,jpg,bmp,png|dimensions:max_width=500,max_height=200', 
        ]);

        try{
            // Promocode::create($request->all());
            // $request->request->add(['company_id' => \Auth::user()->company_id]);
            $promo_code = new Promocode;
            $promo_code->company_id = Auth::user()->company_id;  
            $promo_code->service = $request->service; 
            

            if($request->hasFile('picture')) {
                $imagedetails = getimagesize($_FILES['picture']['tmp_name']);
                $height = $imagedetails[1];
                if($height < 190 || $height > 200){
                    return Helper::getResponse(['status' => 404,'message' => 'image Height must be 200px. this image height is '.$height.' px', 'error' => '']);
                }
                $promo_code['picture'] = Helper::upload_file($request->file('picture'), 'promocode');
            } 
            $promo_code->promo_code = $request->promo_code; 
            $promo_code->percentage = $request->percentage;  
            $promo_code->max_amount = $request->max_amount;                                      
            $promo_code->expiration = Carbon::parse($request->expiration)->format('Y-m-d');
            $promo_code->promo_description = $request->promo_description;               
            $promo_code->save();
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
            $promocode = Promocode::findOrFail($id);
            $expiration = $promocode['expiration'];
         $promocode['expiration']=date('d/m/Y',strtotime($promocode['expiration']));
         $promocode['expiration_date'] = date('m/d/Y',strtotime($expiration));
            return Helper::getResponse(['data' => $promocode]); 
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
            'promo_code' => 'required|max:100',
            'percentage' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'expiration' => 'required',
            'service' => 'required', 
        ]);

        try {

           $promo = Promocode::findOrFail($id);
            $promo->service = $request->service; 
            $promo->promo_code = $request->promo_code;
            if($request->hasFile('picture')) {
                $promo['picture'] = Helper::upload_file($request->file('picture'), 'provider/profile');
            } 
            $promo->percentage = $request->percentage;
            $promo->max_amount = $request->max_amount;
            $promo->expiration = Carbon::parse($request->expiration)->format('Y-m-d');
            $promo->promo_description = $request->promo_description;
            $promo->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);    
        } 

        catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
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
