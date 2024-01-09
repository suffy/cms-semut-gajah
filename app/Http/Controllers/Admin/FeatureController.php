<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Feature;
use App\Menu;
use Intervention\Image\ImageManagerStatic as InterImage;

class FeatureController extends Controller
{
    protected $category;
    protected $menu;

       
    protected $features;

    public function __construct(Feature $features, Menu $menu, Category $category) {
        $this->features = $features;
        $this->category = $category;
        $this->menu = $menu;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $category = $this->category->all();
        $features = $this->features->paginate(10);
        return view('admin.pages.features', compact('features', 'category'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $category = $this->category->all();
        return view('admin.pages.feature-new', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $user = $this->features->create([
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'content' => $request->input('content'),
            'content_en' => $request->input('content_en'),
            'category_id' => $request->input('category_id'),
            'slug' => strtolower(str_replace(" ", "-", $request->input('name'))),
            'status' => 1,
        ]);

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $ext = $file->getClientOriginalExtension();

            $newName = "feature" . date('YmdHis') . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->save(('images/' . $newName));
            $user->icon = $newName;
            $user->save();

        } 

        if ($user) {
            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
        }

        return redirect($request->input('url'))
        ->with('status', 2)
        ->with('message', "Error while save data!");
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feature = $this->features->find($id);
        if (isset($feature)) {
            
            $category = $this->category->all();
            return view('admin.pages.feature-detail', compact('feature', 'category'));

        } else {
            return redirect(url('admin/features'))
            ->with('status', 2)
            ->with('message', "Data not found!");
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
        $feature = $this->features->find($id);
        if (isset($feature)) {
            
            $category = $this->category->all();
            return view('admin.pages.feature-detail', compact('feature', 'category'));

        } else {
            return redirect(url('admin/features'))
            ->with('status', 2)
            ->with('message', "Data not found!");
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
        $article = $this->features->find($request->input('id'));

        if (isset($article)) {

            $article->name   = $request->input('name');
            $article->name_en   = $request->input('name_en');
            $article->content = $request->input('content');
            $article->content_en = $request->input('content_en');
            $article->category_id = $request->input('category_id');
            $article->slug    = strtolower(str_replace(" ", "-", $request->input('name')));

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $ext = $file->getClientOriginalExtension();

                $newName = "feature" . date('YmdHis') . "." . $ext;

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/' . $newName));
                $article->icon = $newName;
                $article->save();

            } 

            $article->save();

            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");

        } else {

            $article->name   = $request->input('name');
            $article->name_en   = $request->input('name_en');
            $article->content = $request->input('content');
            $article->content_en = $request->input('content_en');
            $article->slug    = strtolower(str_replace(" ", "-", $request->input('name')));
            $article->save();

            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");

        }
        return redirect($request->input('url'))
        ->with('status', 2)
        ->with('message', "Unable to Saved!");
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $features = Feature::find($id);
       $features->delete();
       return redirect('admin/features')
       ->with('status', 2)
       ->with('message', "Data Deleted!");
    }

    public function changeStatus(Request $request, $id){
        $feature = $this->features->find($id);

        if($feature){
            $feature->status = $request->input('status');
            $feature->save();
            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
            ->with('status', 2)
            ->with('message', "Error while save data!");
        }

    }


}
