<?php

namespace App\Http\Controllers\Admin;

use App\HelpCategory;
use App\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;

class HelpCategoryController extends Controller
{

    protected $helpCategory;
    protected $logs;

    public function __construct(HelpCategory $helpCategory, Log $log)
    {
        $this->helpCategory = $helpCategory;
        $this->logs = $log;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $helpCategories = $this->helpCategory->paginate();

        return view('admin.pages.help-category', compact('helpCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $helpCategory               = $this->helpCategory;

        $validator = Validator::make(
            $request->all(),
            [
                'name'          => 'required|string',
                'description'   => 'required|string'
            ]
        );

        if ($validator->fails()) {
            return redirect()
                        ->back()
                        ->with('status', 2)
                        ->with('message', $validator->errors()->first())
                        ->withInput()
                        ->with('errors', $validator->errors());
        }

        $helpCategory->slug         = strtolower(str_replace(" ", "-", $request->name));
        $helpCategory->name         = $request->name;
        $helpCategory->description  = $request->description;

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $ext = $file->getClientOriginalExtension();
            $relPathImage = '/images/help-category/';
            if (!file_exists(public_path($relPathImage))) {
                mkdir(public_path($relPathImage), 0755, true);
            }

            $newName = "help-category" . date('YmdHis') . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->save(('images/help-category/' . $newName));
            $helpCategory->icon = '/images/help-category/'.$newName;
        }

        $helpCategory->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new help category";
        $logs->table_id     = $helpCategory->id;
        $logs->data_content = $helpCategory;
        $logs->table_name   = 'help_categories';
        $logs->column_name  = 'slug, name, description, icon';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($helpCategory) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $helpCategory = $this->helpCategory->findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'name'          => 'required|string',
                'description'   => 'required|string'
            ]
        );

        if ($validator->fails()) {
            return redirect()
                        ->back()
                        ->with('status', 2)
                        ->with('message', $validator->errors()->first())
                        ->withInput()
                        ->with('errors', $validator->errors());
        }

        $helpCategory->slug         = strtolower(str_replace(" ", "-", $request->name));
        $helpCategory->name         = $request->name;
        $helpCategory->description  = $request->description;

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $ext = $file->getClientOriginalExtension();

            $newName = "help-category" . date('YmdHis') . "." . $ext;
            // if($helpCategory->icon!==null){
            //     if (file_exists(substr($helpCategory->icon, 1))) {
            //         unlink(substr($helpCategory->icon, 1)); //menghapus file lama
            //     }
            // }

            if($helpCategory->icon != null){
                if (file_exists(public_path().$helpCategory->icon)) {
                    unlink(public_path().$helpCategory->icon); //menghapus file lama
                }
            }
                
            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->save(('images/help-category/' . $newName));
            $helpCategory->icon = '/images/help-category/'.$newName;
        }

        $helpCategory->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update help category with id : " . $id;
        $logs->data_content = $helpCategory;
        $logs->table_name   = 'help_categories';
        $logs->column_name  = 'slug, name, description, icon';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($helpCategory) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $helpCategory = $this->helpCategory->findOrFail($id);
        $helpCategory->delete();

        // if (file_exists(substr($helpCategory->icon, 1))) {
        //     unlink(substr($helpCategory->icon, 1)); //menghapus file lama
        // } 

        if (file_exists(public_path().$helpCategory->icon)) {
            unlink(public_path().$helpCategory->icon); //menghapus file lama
        } 
        
        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete help category with id : " . $id;
        $logs->table_name   = 'help_categories';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($helpCategory) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
        }
    }
}
