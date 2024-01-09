<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Image;
use Intervention\Image\ImageManagerStatic as InterImage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $images = Image::orderBy('id','desc')
                ->paginate(15);

                return view('admin.pages.image')
                        ->with('images', $images);
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
        if ($request->hasFile('images')) {
            $file = $request->file('images');
            $ext = $file->getClientOriginalExtension();

            $newName = "img-".date('Y-m-d-His')."-".strtolower(str_replace(" ","-",$request->input('name'))) . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->resize(1366, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image_resize->save(('images/' .$newName));

            $user = Image::create([
                'description' => $request->input('img_desc'),
                'name' => $request->input('name'),
                'keyword' => $request->input('keyword'),
                'path' => 'images/'.$newName,
            ]);

            if($user){

                if($request->input('url')=='0'){
                    $resp = array(
                        'status' => '1',
                        'message' => 'success'
                    );

                    return $resp;
                }else{
                    return redirect($request->input('url'))
                        ->with('status', 1)
                        ->with('message', "Data Saved!");
                }

            }

        }
    }

    public function storeImageAjax(Request $request)
    {
        if ($request->hasFile('images')) {
            $file = $request->file('images');
            $ext = $file->getClientOriginalExtension();

            $newName = "img-".date('Y-m-d-His')."-".strtolower(str_replace(" ","-",$request->input('name'))) . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->resize(1366, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image_resize->save(('images/' .$newName));

            $user = Image::create([
                'description' => $request->input('img_desc'),
                'name' => $request->input('name'),
                'keyword' => $request->input('keyword'),
                'path' => 'images/'.$newName,
            ]);

            if($user){

                if($request->input('url')=='0'){
                    $resp = array(
                        'status' => '1',
                        'message' => 'success'
                    );

                    return $resp;
                }else{
                    return redirect($request->input('url'))
                        ->with('status', 1)
                        ->with('message', "Data Saved!");
                }

            }

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
        $post_images = Image::find($id);
        if (isset($post_images)) {
            return view('admin.pages.image-detail')
            ->with('image', $post_images);
        }else{
            return redirect(url('admin/images'))
            ->with('status', 1)
            ->with('message', "Image not found!");
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
        $post_images = Image::find($id);
        if (isset($post_images)) {

            $post_images->name = $request->input('name');
            $post_images->keyword = $request->input('keyword');
            $post_images->description = $request->input('img_desc');
            $post_images->save();

        }

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Updated!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $post_images = Image::find($id);
        if (isset($post_images)) {

            if (file_exists($post_images->path)) {
                unlink($post_images->path); //menghapus file lama
            }

            $post_images->forceDelete();

        }

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }



    public function getImages()
    {
        //
        $images = Image::orderBy('id','desc')
                ->paginate(15);

                return view('admin.includes.image-list')
                        ->with('images', $images);
    }
}
