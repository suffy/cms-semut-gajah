<?php

namespace App\Http\Controllers\Admin;

use App\Post;
use App\PostCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\StoreImage;

class PostCategoryController extends Controller
{
    protected $category, $post;

    public function __construct(PostCategory $category, Post $post) {
        $this->category = $category;
        $this->post = $post;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = $this->category->paginate(10);
        return view('admin.pages.post-category', compact('category'));
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
        $slug = strtolower(str_replace(" ", "-", $request->input('name')));

        $parent = $this->category->find($request->input('category_parent'));

        if($parent){
            $service = $this->category->create([
                'category_parent' => $request->input('category_parent'),
                'type' => $parent->slug,
                'name' => $request->input('name'),
                'name_en' => $request->input('name_en'),
                'status' => $request->input('status'),
                'slug' => $slug
            ]);
        }else{
            $service = $this->category->create([
                'category_parent' => $request->input('category_parent'),
                'name' => $request->input('name'),
                'name_en' => $request->input('name_en'),
                'status' => $request->input('status'),
                'slug' => $slug
            ]);
        }
        

        if ($request->hasFile('icon')) {
            // image's folder
            $folder = 'category';
            // image's filename
            $newName = "category-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $service->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);

            $service->save();
        }


        if ($service) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
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
        $data = $this->post->find($id);
        if (isset($data)) {
            return $data;
        } else {
            $data = array(
                'id' => '0',
                'content' => null
            );

            return $data;
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
        $slug = strtolower(str_replace(" ", "-", $request->input('name')));

        $cat = $this->category->find($request->input('id'));

        $cat->category_parent = $request->input('category_parent');
        $cat->name = $request->input('name');
        $cat->name_en = $request->input('name_en');
        $cat->status = $request->input('status');
        $cat->slug = $slug;

        $parent = $this->category->find($request->input('category_parent'));

        if($parent){
            $cat->type = $parent->slug;
            $cat->save();
        }

        if ($request->hasFile('icon')) {
            // image's folder
            $folder = 'category';
            // image's filename
            $newName = "category-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $cat->icon = StoreImage::saveImage($request, $folder, $newName, $form_name);

            $cat->save();
        }


        if ($cat->save()) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }else{
            return redirect($request->input('url'))
                ->with('status', 2)
                ->with('message', "Error update data!");
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
        $trans = $this->post->where('post_category_id', $id)->get();

        if(count($trans)>0){
            return redirect('admin/post-categories')
                ->with('status', 2)
                ->with('message', "Tidak dapat menghapus category karena pernah dipakai!");
        }else{
            $farm = $this->category->find($id);
            if(isset($farm)){
                $farm->delete();
            }

            return redirect('admin/post-categories')
                ->with('status', 1)
                ->with('message', "Data Deleted!");
        }
    }
}
