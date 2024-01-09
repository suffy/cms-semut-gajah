<?php

namespace App\Http\Controllers\Admin;

use App\Help;
use App\HelpCategory;
use App\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;

class HelpController extends Controller
{
    protected $help, $helpCategory;
    protected $logs;

    public function __construct(Help $help, HelpCategory $helpCategory, Log $log)
    {
        $this->help = $help;
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
        $helps = $this->help->with('help_category')->paginate(10);
        $helpCategories = $this->helpCategory->all();
        // dd($helps);
        return view('admin.pages.helps', compact('helps', 'helpCategories'));
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
        $help                   = $this->help;

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

        $help->slug             = strtolower(str_replace(" ", "-", $request->name));
        $help->name             = $request->name;
        $help->description      = $request->description;
        $help->help_category_id = $request->category;
        $help->icon             = null;

        // $relPathImage = '/images/help/';
        // if (!file_exists(public_path($relPathImage))) {
        //     mkdir(public_path($relPathImage), 0755, true);
        // }

        // if ($request->hasFile('icon')) {
        //     $file = $request->file('icon');
        //     $ext = $file->getClientOriginalExtension();

        //     $newName = "help" . date('YmdHis') . "." . $ext;

        //     $image_resize = InterImage::make($file->getRealPath());
        //     $image_resize->save(('images/help/' . $newName));
        //     $help->icon = '/images/help/'.$newName;
        // }

        $help->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new help";
        $logs->table_id     = $help->id;
        $logs->data_content = $help;
        $logs->table_name   = 'helps';
        $logs->column_name  = 'icon, slug, name, description, help_category_id';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($help) {
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
        $help                   = $this->help->findOrFail($id);

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

        $help->slug             = strtolower(str_replace(" ", "-", $request->name));
        $help->name             = $request->name;
        $help->description      = $request->description;
        $help->help_category_id = $request->category;
        $help->icon             = null;

        // if ($request->hasFile('icon')) {
        //     $file = $request->file('icon');
        //     $ext = $file->getClientOriginalExtension();

        //     $newName = "help" . date('YmdHis') . "." . $ext;
        //     if($help->icon!==null){
        //         if (file_exists(substr($help->icon, 1))) {
        //             unlink(substr($help->icon, 1)); //menghapus file lama
        //         }
        //     }

        //     $image_resize = InterImage::make($file->getRealPath());
        //     $image_resize->save(('images/help/' . $newName));
        //     $help->icon = '/images/help/'.$newName;
        // }

        $help->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update help with id : " . $id;
        $logs->table_id     = $id;
        $logs->data_content = $help;
        $logs->table_name   = 'helps';
        $logs->column_name  = 'icon, slug, name, description, help_category_id';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();


        if ($help) {
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
        $help = $this->help->findOrFail($id);

        $help->delete();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete help with id : " . $id;
        $logs->table_id     = $id;
        $logs->table_name   = 'helps';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        // if (file_exists(substr($help->icon, 1))) {
        //     unlink(substr($help->icon, 1)); //menghapus file lama
        // }

        if ($help) {
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
