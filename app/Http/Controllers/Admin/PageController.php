<?php

namespace App\Http\Controllers\Admin;

use App\Page;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class PageController extends Controller
{
    protected $page;

    public function __construct(Page $page) {
        $this->page = $page;
    }

    public function pageDashboard()
    {
        return view('admin/pages/dashboard');
    }

    public function pageUsers(Request $request)
    {
        $data = User::where('account_type','1')
                ->paginate(10);
        return view('admin/pages/users')
            ->with('user', $data);
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $this->page->paginate(10);
        return view('admin.pages.pages', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin.pages.page-new');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $user = $this->page->create([
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'title_en' => $request->input('title_en'),
            'content' => $request->input('content'),
            'content_en' => $request->input('content_en'),
            'post_type' => $request->input('type'),
            'position' => $request->input('position'),
            'slug' => strtolower(str_replace(" ", "-", $request->input('title'))),
            'tags' => $request->input('tags'),
            'status' => $request->input('status'),
        ]);

        if ($user) {

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
                
                $user->featured_image = 'images/' . $newName;
                $user->save();
    
            } 

            return redirect(url('admin/pages'))
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
        $data = $this->page->find($id);
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
        $page = $this->page->find($id);
        if (isset($page)) {
            return view('admin.pages.page-edit', compact('page'));
        }else{
            return redirect(url('admin/pages'));
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
        $article = $this->page->find($id);

        if (isset($article)) {

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $ext = $file->getClientOriginalExtension();

                $newName = "images" . date('YmdHis') . "." . $ext;

                $image_resize = Image::make($file->getRealPath());
                $image_resize->resize(1366, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_resize->save(('images/' . $newName));

                if ($article->featured_images !== null) {
                    unlink($article->featured_images); //menghapus file lama
                }

                $article->featured_image = 'images/' . $newName;

            }

            $article->user_id = Auth::user()->id;
            $article->title = $request->input('title');
            $article->title_en = $request->input('title_en');
            $article->content = $request->input('content');
            $article->content_en = $request->input('content_en');
            $article->slug = strtolower(str_replace(" ", "-", $request->input('title')));
            $article->position = $request->input('position');
            $article->tags = $request->input('tags');
            $article->status = $request->input('status');
            $article->save();

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
        $page = $this->page->find($id);
        if (isset($page)) {

            if (file_exists($page->featured_image)) {
                unlink($page->featured_image); //menghapus file lama
            }
            $page->delete();
        }

        return redirect('admin/pages')
        ->with('status', 2)
        ->with('message', "Data Deleted!");
    }
}
