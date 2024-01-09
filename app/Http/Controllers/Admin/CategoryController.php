<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use App\Menu;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as InterImage;

class CategoryController extends Controller
{
    protected $category;
    protected $menu;
    protected $logs;

    public function __construct(Category $category, Menu $menu, Log $log) {
        $this->category = $category;
        $this->menu = $menu;
        $this->logs = $log;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = $this->category->paginate(10);
        $menu = $this->menu->all();
        return view('admin.pages.category', compact('category', 'menu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request)
    {
        // change status
        $category = Category::find($request->category_id);
        $category->status = $request->input('status');
        $category->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Change categories status with id : " . $request->category_id;
        $logs->data_content = "status : " . $request->status;
        $logs->table_name   = 'categories';
        $logs->column_name  = 'status';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return response()->json(['success'=>'Status change successfully.']);
    }

    public function updateStatus($id)
    {
        $category = Category::find($id);
        
        if($category){
            
            // update status
            if($category->status=="1"){
                $category->status="0";
            }else{
                $category->status="1";
            }
            $category->save();


            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Change categories status with id : " . $id;
            $logs->table_name   = 'categories';
            $logs->column_name  = 'status';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();

            $status = 1;
            $msg = 'Update sukses '.$category->status;
            return response()->json(compact('status', 'msg'), 200);
        }else{
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
            
    }

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
        // create category
        $category = Category::create([
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'slug' => strtolower(str_replace(" ", "-", $request->input('name'))),
            'menu_order' => '0',
            'description' => $request->input('description'),
            'description_en' => $request->input('description_en'),
            'status' => '1',
        ]);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new category";
        $logs->data_content = "name : " . $request->input('name') . ", name_en : " . $request->input('name_en') . ", slug : " . strtolower(str_replace(" ", "-", $request->input('name'))) . ", menu_order : 0, description : " . $request->input('description') . ", description_en : " . $request->input('description_en') . ', status : 1';
        $logs->table_name   = 'categories';
        $logs->table_id     = $category->id;
        $logs->column_name  = 'name, name_en, slug, menu_order, description, description_en, status';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        if ($category) {

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $ext = $file->getClientOriginalExtension();

                $newName = "category" . date('YmdHis') . "." . $ext;

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/' . $newName));
                $category->icon = '/images/'.$newName;
                $category->save();

            }

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
        $category = $this->category->find($id);

        if ($category) {
            $menu = $this->menu->all();
            return view('admin.pages.category-detail', compact('category', 'menu'));
        }else{
            return redirect(url('admin/categories'));
        }
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
        //
        $category = $this->category->find($request->input('id'));

        if ($category) {

                if ($request->hasFile('icon')) {

                    $file = $request->file('icon');
                    $ext = $file->getClientOriginalExtension();

                    $newName = "category-" . date('Y-m-d-His') . "." . $ext;
                    if($category->icon != null){
                        if (file_exists(public_path().$category->icon)) {
                            unlink(public_path().$category->icon); //menghapus file lama
                        }
                    }

                    $image_resize = InterImage::make($file->getRealPath());
                    $image_resize->save(('images/' . $newName));
                    $category->icon = '/images/' . $newName;

                }

                $category->name = $request->input('name');
                $category->name_en = $request->input('name_en');
                $category->status = $request->input('status');
                $category->description = $request->input('description');
                $category->description_en = $request->input('description_en');
                $category->slug = strtolower(str_replace(" ", "-", $request->input('name')));

                $category->save();

                // logs
                $logs = $this->logs;

                $logs->log_time     = Carbon::now();
                $logs->activity     = "Update categories status with id : " . $id;
                $logs->data_content  = "name : " . $request->input('name') . ", name_en : " . $request->input('name_en') . ", status : " . $request->input('status') . ", description : " . $request->input('description') . ", description_en : " . $request->input('description_en') . ", slug : " . strtolower(str_replace(" ", "-", $request->input('name')));
                $logs->table_name   = 'categories';
                $logs->table_id     = $category->id;
                $logs->column_name  = 'name, name_en, status, description, description_en, slug';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = 'web';

                $logs->save();

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
    public function destroy($id)
    {
        //delete category
        $category = $this->category->find($id);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete categories status with id : " . $id;
        $logs->table_name   = 'categories';
        $logs->table_id     = $id;
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        if (isset($category)) {

            if($category->icon != null){
                if (file_exists(public_path().$category->icon)) {
                    unlink(public_path().$category->icon); //menghapus file lama
                }
            }

            $category->delete();

        }

        return redirect('admin/categories')
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }
}
