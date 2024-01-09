<?php

namespace App\Http\Controllers\Admin;

use App\Post;
use App\PostCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class PostController extends Controller
{
    protected $post, $postCategory;

    public function __construct(Post $post, PostCategory $postCategory) {
        $this->post = $post;
        $this->postCategory = $postCategory;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');

        if($type==''||$type==null){
            $type='news';
            return redirect(url('admin/posts?type=news'));
        }
        $post = $this->post->where('post_type', $type)->orderBy('id', 'desc')
        ->paginate(10);
        $category = $this->postCategory->all();
        return view('admin.pages.posts', compact('post', 'category', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $type = $request->input('type');

        if($type=='news'||$type=='events'||$type=='careers'||$type=='pages'||$type=='educations'){
            $type = $request->input('type');
        }elseif($type==''||$type==null){
            $type='news';
        }else{
            return redirect(url('admin/posts/create?type=news'));
        }

        $category = $this->postCategory->all();
        return view('admin.pages.post-new', compact('category', 'type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $post = $this->post->create([
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'title_en' => $request->input('title_en'),
            'featured_video' => $request->input('featured_video'),
            'content' => $request->input('content'),
            'content_en' => $request->input('content_en'),
            'post_type' => $request->input('type'),
            'excerpt' => $request->input('excerpt'),
            'excerpt_en' => $request->input('excerpt_en'),
            'keyword' => $request->input('keyword'),
            'keyword_en' => $request->input('keyword_en'),
            'slug' => strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u','', strip_tags($request->input('title'))))),
            'post_category_id' => $request->input('category_id'),
            'tags' => $request->input('tags'),
            'tags_en' => $request->input('tags_en'),
            'status' => $request->input('status')
        ]);

        if ($post) {

            if ($request->hasFile('featured_image')) {

                $file = $request->file('featured_image');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "images-" . date('Ymd-His') . "." . $ext;
                //----
                $image_resize = Image::make($file->getRealPath());
                $image_resize->resize(1366, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_resize->save(('images/' . $newName));
                //----
                
                $post->featured_image = 'images/' . $newName;
                $post->save();
    
            } 

            if($request->hasFile('video')){

                $file = $request->file('video');
                $filename = $file->getClientOriginalName();
                $path = public_path().'/video/';
                $file->move($path, $filename);

                $post->video = $filename;
                $post->save();

            }

            return redirect(url('admin/posts?type='.$request->input('type')))
            ->with('status', 1)
            ->with('message', "Data Saved!");

        } else {

            return redirect($request->input('url'))
            ->with('status', 2)
            ->with('message', "Error while save data!");
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
        $post = $this->post->find($id);
        if (isset($post)) {
            $category = $this->postCategory->all();
            $type = $post->post_type;
            return view('admin.pages.post-edit', compact('category', 'post', 'type'));
        }else{
            return redirect(url('admin/posts'));
        }
    }

    public function changeStatus(Request $request, $id){
        $post = $this->post->find($id);

        if($post){
            $post->status = $request->input('status');
            $post->save();
            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
            ->with('status', 2)
            ->with('message', "Error while save data!");
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
        $post = $this->post->find($id);

        if (isset($post)) {

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $ext = $file->getClientOriginalExtension();

                $newName = "images" . date('YmdHis') . "." . $ext;

                $image_resize = Image::make($file->getRealPath());
                $image_resize->resize(1366, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_resize->save(('images/' . $newName));

                if ($post->featured_images !== null) {
                    unlink($post->featured_images); //menghapus file lama
                }

                $post->featured_image = 'images/' . $newName;

            }

            if($request->hasFile('video')){

                $file = $request->file('video');
                $filename = $file->getClientOriginalName();
                $path = public_path().'/video/';
                $file->move($path, $filename);

                $post->video = $filename;

            }

            $post->user_id = Auth::user()->id;
            $post->title = $request->input('title');
            $post->title_en = $request->input('title_en');
            $post->content = $request->input('content');
            $post->content_en = $request->input('content_en');
            $post->featured_video = $request->input('featured_video');
            $post->slug = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u','', strip_tags($request->input('title')))));
            $post->post_category_id = $request->input('category_id');
            $post->excerpt = $request->input('excerpt');
            $post->excerpt_en = $request->input('excerpt_en');
            $post->keyword = $request->input('keyword');
            $post->keyword_en = $request->input('keyword_en');
            $post->tags = $request->input('tags');
            $post->tags_en = $request->input('tags_en');
            $post->status = $request->input('status');
            $post->save();

            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");

        } else {

            return redirect($request->input('url'))
            ->with('status', 2)
            ->with('message', "Unable to Saved!");

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
        $post = $this->post->find($id);
        if (isset($post)) {

            if (file_exists($post->featured_image)) {
                unlink($post->featured_image); //menghapus file lama
            }
            $post->delete();
        }

        return redirect('admin/posts')
        ->with('status', 2)
        ->with('message', "Data Deleted!");
    }
}
