<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use App\Models\Common\Admin;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use App\Traits\Actions;
use Exception;
use Setting;
use Auth;

class AccountManagerController extends Controller
{
    use Actions;

    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Admin $model)
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
        $datum = Admin::where('id','!=' , 1);

         $column_name = $datum->first()->toArray();

        $columns = (count($column_name) > 0) ? array_keys($column_name) : [];

        if($request->has('search_text') && $request->search_text != null) {
            $datum->where(function ($query) use($columns, $request) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'LIKE', "%".$request->search_text."%");
                }
            });
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);
        return Helper::getResponse(['data' =>  $data]); 
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
            'name' => 'required|max:255',
            'email' => $request->email != null ?'sometimes|required|unique:accounts,email|email|max:255':'',
            
            'password' => 'required|min:6|confirmed',
        ]);

        try{
            $request->request->add(['company_id' => \Auth::user()->company_id]);
            $Account = $request->all();
            $Account['password'] = Hash::make($request->password);

            $Account = Admin::create($Account);

            $role = Role::where('name', 'ACCOUNT')->first();

            if($role != null) $Account->assignRole($role->id);

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $account
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $account = Admin::findOrFail($id);
            return Helper::getResponse(['data' => $account]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|unique:dispatchers,email|email|max:255',
           
        ]);

        try {

            $Account = Admin::findOrFail($id);
            $Account->name = $request->name;
            $Account->email = $request->email;
            if($request->has('password')) $Account->password = $request->password;
            $Account->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        return $this->removeModel($id);
    }

}
