<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\DataOption;
use App\Helpers\StoreImage;
use App\Menu;

class OptionController extends Controller
{

    protected $option;
    protected $menu;
    public function __construct(DataOption $option, Menu $menu)
    {
        $this->option = $option;
        $this->menu = $menu;
    }
    public function index(Request $request)
    {
        $valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));

        if ($valsearch == "" || $valsearch == "0") {
            $q_search = "";
        } else {
            $q_search = " AND option_name like '%" . $valsearch . "%'";
        }
        $option = DataOption::whereRaw('1 ' . $q_search)
            ->orderBy('slug', 'asc')
            ->get();
            $menu = $this->menu->all();
        return view('admin/pages/option', compact('option', 'menu'));
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $option = new $this->option;
        $option->option_type      = $request->option_type;
        $option->slug             = $request->slug;
        $option->option_name      = $request->option_name;
        $option->option_name_en   = $request->option_name_en;
        $option->option_value     = $request->option_value;
        $option->option_value_en  = $request->option_value_en;
        $option->position         = $request->position;
        $option->editable         = 0;
        $option->status           = 1;

        if ($request->hasFile('icon')) {
            // image's folder
            $folder = 'option';
            // image's filename
            $newName = "option-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $option->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $option->save();

        return redirect('admin/options')
            ->with('status', 1)
            ->with('message', "Data Created!");
    }

    public function show(Option $option)
    {
        return view('admin.pages.option-detail', [

            'option' => $option
        ]);
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $option = $this->option->find($id);

        if ($option) {
            $option->option_type       = $request->input('option_type');
            $option->slug         = $request->input('slug');
            $option->option_name       = $request->input('option_name');
            $option->option_name_en       = $request->input('option_name_en');
            $option->option_value      = $request->input('option_value');
            $option->option_value_en      = $request->input('option_value_en');
            $option->position      = $request->input('position');

            if ($request->hasFile('icon')) {
                $folder     = 'option'; // image's folder
                $newName    = "option-" . date('Ymd-His'); // image's filename
                $form_name  = 'icon'; // image's form field name

                if (file_exists($option->icon)) {
                    unlink($option->icon); //delete old file
                }
                $option->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);
            }

            $option->save();

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }
    }


    public function destroy($id)
    {
        $option = $this->option->find($id);
        if (isset($option)) {
            if(isset($option->icon)){
                StoreImage::deleteImage($option->icon);
            
            }

            $option->delete();
            
            return redirect('admin/options')
            ->with('status', 1)
            ->with('message', "Data Deleted!"); 

        }else{
            return redirect('admin/options')
            ->with('status', 2)
            ->with('message', "Failed to delete!");
        }

        
    }
}
