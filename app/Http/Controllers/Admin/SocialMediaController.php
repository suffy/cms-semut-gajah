<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\SocialMedia;
use App\Helpers\StoreImage;


class SocialMediaController extends Controller
{
    protected $social_media;

    public function __construct(SocialMedia $social_media) {
        $this->social_media = $social_media;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $valsearch = $request->input('search');

        if($valsearch==""||$valsearch=="0"){
           $q_search = "";
        }else{
            $q_search = " AND name like '%".$valsearch."%'";
        }

        $social_media = SocialMedia::whereRaw('1 '.$q_search)
                        ->orderBy('name','asc')
                        ->paginate(10);
        return view('admin.pages.social-medias', compact('social_media'));
    }


    public function changeStatus(Request $request)
    {
        $social_media = SocialMedia::find($request->social_media_id);
        $social_media->status = $request->input('status');
        $social_media->save();

        return response()->json(['success'=>'Status change successfully.']);
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
        $social_media = new $this->social_media;
        $social_media->name = $request->name;
        $social_media->url  = $request->link;
        $social_media->status = $request->status;

        if($request->hasFile('icon'))
        {
            // image's folder
            $folder = 'social_media';
            // image's filename
            $newName = "social_media-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $social_media->icon = \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $social_media->save();

        return redirect($request->input('url_redirect'))
        ->with('status', 1)
        ->with('message', "Data Saved!");
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
        $social_media = $this->social_media->find($id);

        if ($social_media) {
            $social_media->name = $request->input('name');
            $social_media->url  = $request->input('link');
            $social_media->status = $request->input('status');
            if($request->hasFile('icon'))
            {
                $folder     = 'social_media'; // image's folder
                $newName    = "social_media-" . date('Ymd-His'); // image's filename
                $form_name  = 'icon'; // image's form field name

                if (file_exists($social_media->icon)) {
                    unlink($social_media->icon); //delete old file
                }
                $social_media->icon = \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
            }

            $social_media->save();

            return redirect($request->input('url_redirect'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
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
        $social_media = $this->social_media->find($id);
        if (isset($social_media)) {
            if(isset($social_media->icon)){
                StoreImage::deleteImage($social_media->icon);
                $social_media->delete();
            }
        }

        return redirect('admin/social-medias')
        ->with('status', 2)
        ->with('message', "Data Deleted!");
    }
}
