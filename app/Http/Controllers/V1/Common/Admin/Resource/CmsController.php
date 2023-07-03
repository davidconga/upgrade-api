<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Traits\Actions;
use App\Models\Common\CmsPage;
use DB;
use Auth;
class CmsController extends Controller
{
        use Actions;

        private $model;
        private $request;
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CmsPage $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cms_page = CmsPage::where('company_id',Auth::user()->company_id)->get();
        return Helper::getResponse(['data' => $cms_page]);
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
            'page_name' => 'required',
            'content' => 'required',  
            'status' => 'required',          
        ]);

        try{
            $cms_page = new CmsPage;
            $cms_page->company_id = Auth::user()->company_id;  
            $cms_page->page_name = $request->page_name;
            $cms_page->content = $request->content;  
            $cms_page->status = $request->status;                   
            $cms_page->save();
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
    public function show($page)
    {
        try {
            $cms_page = CmsPage::where('company_id',Auth::user()->company_id)->where('page_name',$page)->get();

            return Helper::getResponse(['data' => $cms_page]);
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
            'page_name' => 'required',
            'content' => 'required',
            'status' => 'required',       
        ]);

        try {

            $cms_page = CmsPage::findOrFail($id);
            $cms_page->page_name = $request->page_name;
            $cms_page->content = $request->content;   
            $cms_page->status = $request->status;                     
            $cms_page->save();

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

