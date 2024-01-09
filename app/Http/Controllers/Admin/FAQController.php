<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\FAQ;
use App\Helpers\StoreImage;
use App\Menu;

class FAQController extends Controller
{

    protected $faq;
    protected $menu;
    public function __construct(FAQ $faq, Menu $menu)
    {
        $this->faq = $faq;
        $this->menu = $menu;
    }
    public function index(Request $request)
    {
        $valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));

        if ($valsearch == "" || $valsearch == "0") {
            $q_search = "";
        } else {
            $q_search = " AND question like '%" . $valsearch . "%'";
        }
        $faq = FAQ::whereRaw('1 ' . $q_search)
            ->orderBy('slug', 'asc')
            ->get();
            $menu = $this->menu->all();
        return view('admin/pages/faq', compact('faq', 'menu'));
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $faq = new $this->faq;
        $faq->type      = $request->type;
        $faq->slug             = $request->slug;
        $faq->question      = $request->question;
        $faq->question_en   = $request->question_en;
        $faq->answer     = $request->answer;
        $faq->answer_en  = $request->answer_en;
        $faq->position         = $request->position;
        $faq->editable         = 0;
        $faq->status           = 1;

        if ($request->hasFile('icon')) {
            // image's folder
            $folder = 'faq';
            // image's filename
            $newName = "faq-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $faq->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $faq->save();

        return redirect('admin/faqs')
            ->with('status', 1)
            ->with('message', "Data Created!");
    }

    public function show(FAQ $faq)
    {
        return view('admin.pages.faq-detail', [

            'faq' => $faq
        ]);
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $faq = $this->faq->find($id);

        if ($faq) {
            $faq->type       = $request->input('type');
            $faq->slug         = $request->input('slug');
            $faq->question       = $request->input('question');
            $faq->question_en       = $request->input('question_en');
            $faq->answer      = $request->input('answer');
            $faq->answer_en      = $request->input('answer_en');
            $faq->position      = $request->input('position');

            if ($request->hasFile('icon')) {
                $folder     = 'faq'; // image's folder
                $newName    = "faq-" . date('Ymd-His'); // image's filename
                $form_name  = 'icon'; // image's form field name

                if (file_exists($faq->icon)) {
                    unlink($faq->icon); //delete old file
                }
                $faq->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);
            }

            $faq->save();

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }
    }


    public function destroy($id)
    {
        $faq = $this->faq->find($id);
        if (isset($faq)) {
            if(isset($faq->icon)){
                StoreImage::deleteImage($faq->icon);
            
            }

            $faq->delete();
            
            return redirect('admin/faqs')
            ->with('status', 1)
            ->with('message', "Data Deleted!"); 

        }else{
            return redirect('admin/faqs')
            ->with('status', 2)
            ->with('message', "Failed to delete!");
        }

        
    }
}
