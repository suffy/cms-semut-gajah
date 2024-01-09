<?php

namespace App\Http\Controllers\Admin;

use App\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;

class BannerController extends Controller
{
    protected $banner, $logs;

    public function __construct(Banner $banner, Log $log)
    {
        $this->banner = $banner;
        $this->logs = $log;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banner = $this->banner->paginate(10);
        return view('admin.pages.banner', compact('banner'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id)
    {
        $banner = $this->banner->find($id);

        if ($banner) {
            // change status banner
            $banner->status = $request->input('status');
            $banner->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update status banner with id " . $id;
            $logs->table_name   = 'users';
            $logs->table_id     = $id;
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;

            $logs->save();

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 2)
                ->with('message', "Error while save data!");
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
        //
        $validator = Validator::make(
            $request->all(),
            [
                'title'           => 'required|string',
                'banner_desc'     => 'required|string'
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

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension();

            $newName = "banner" . date('YmdHis') . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->save(('images/' . $newName));

            $user = Banner::create([
                'page'          => $request->input('page'),
                'images'        => '/images/' . $newName,
                'link'          => $request->input('link'),
                'title'         => $request->input('title'),
                'title_en'      => $request->input('title_en'),
                'banner_desc'   => $request->input('banner_desc'),
                'banner_desc_en' => $request->input('banner_desc_en'),
                'menu_order'    => '0',
                'position'      => 'middle',
                'status'        => '1',
            ]);

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new banner";
            $logs->data_content = "page : " . $request->input('page') . ", image : /images/" . $newName . ", link : " . $request->input("link") . ", title : " . $request->input('title') . ", title_en : " . $request->input('title_en') . ", banner_desc : " . $request->input('banner_desc') . ", banner_desc_en : " . $request->input("banner_desc_en") . ", menu_order : 0, status : 1";
            $logs->table_name   = 'banners';
            $logs->table_id     = $user->id;
            $logs->column_name  = 'page, images, link, title, title_en, banner_desc, banner_desc_en, menu_order, status';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();

            if ($user) {
                return redirect($request->input('url'))
                    ->with('status', 1)
                    ->with('message', "Data Saved!");
            }
        } else {
            return redirect($request->input('url'))
                ->with('status', 2)
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
        //
        $banner = $this->banner->find($request->input('id'));

        $validator = Validator::make(
            $request->all(),
            [
                'title'           => 'required|string',
                'banner_desc'     => 'required|string'
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

        if ($banner) {

            if ($request->hasFile('photo')) {

                $file = $request->file('photo');
                $ext = $file->getClientOriginalExtension();

                $newName = "banner-" . date('Y-m-d-His') . "." . $ext;

                if (file_exists(public_path() . $banner->images)) {
                    unlink(public_path() . $banner->images); //menghapus file lama
                }

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/' . $newName));

                $banner->page           = $request->input('page');
                $banner->link           = $request->input('link');
                $banner->title          = $request->input('title');
                $banner->title_en       = $request->input('title_en');
                $banner->banner_desc    = $request->input('banner_desc');
                $banner->banner_desc_en = $request->input('banner_desc_en');
                $banner->images         = '/images/' . $newName;
                $banner->position       = 'middle';

                $banner->save();

                // logs
                $logs = $this->logs;

                $logs->log_time     = Carbon::now();
                $logs->activity     = "Update banner with id : " . $id;
                $logs->data_content = "page : " . $request->input('page') . ", link : " . $request->input('link') . ", title : " . $request->input('title') . ", title_en : " . $request->title_en . ", banner_desc : " . $request->input('banner_desc') . ", banner_desc_en : " . $request->input('banner_desc_en') . ", images : /images/" . $newName;
                $logs->table_name   = 'users';
                $logs->table_id     = $banner->id;
                $logs->column_name  = 'page, link, title, title_en, banner_desc, banner_desc_en, images';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = 'web';

                $logs->save();
            } else {

                $banner->page = $request->input('page');
                $banner->link = $request->input('link');
                $banner->title = $request->input('title');
                $banner->title_en = $request->input('title_en');
                $banner->banner_desc = $request->input('banner_desc');
                $banner->banner_desc_en = $request->input('banner_desc_en');
                $banner->position = 'middle';

                $banner->save();

                // logs
                $logs = $this->logs;

                $logs->log_time     = Carbon::now();
                $logs->activity     = "Update banner with id : " . $id;
                $logs->data_content = "page : " . $request->input('page') . ", link : " . $request->input('link') . ", title : " . $request->input('title') . ", title_en : " . $request->title_en . ", banner_desc : " . $request->input('banner_desc') . ", banner_desc_en : " . $request->input('banner_desc_en');
                $logs->table_name   = 'users';
                $logs->table_id     = $banner->id;
                $logs->column_name  = 'page, link, title, title_en, banner_desc, banner_desc_en';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = 'web';

                $logs->save();
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //delete banner
        $banner = $this->banner->find($id);
        if (isset($banner)) {

            if (file_exists(public_path() . $banner->images)) {
                unlink(public_path() . $banner->images); //menghapus file lama
            }

            $banner->delete();
        }

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete banner with id : " . $id;
        $logs->table_name   = 'users';
        $logs->table_id     = $id;
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return redirect()
            ->back()
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }

    public function priority()
    {
        $banners = $this->banner->select('id', 'title', 'images', 'priority', 'priority_position')->where('status', 1)->orderBy('id', 'desc')->get();

        return view('admin/pages/banner-priority', compact('banners'));
    }

    public function priorityStore(Request $request)
    {
        try {
            // $banners
            $banners = $this->banner->where('status', 1)->get();
            $pos = array_filter($request->pos);
            $posArray = [];
            foreach ($pos as $p) {
                array_push($posArray, $p);
            }
            foreach ($banners as $key => $banner) {
                if (in_array($banner->id, $request->banner)) {
                    $banner->priority = '1';
                    $banner->priority_position = $posArray[array_search($banner->id, $request->banner)];
                } else {
                    $banner->priority = '0';
                    $banner->priority_position = null;
                }
                $banner->save();
            }

            $role = auth()->user()->account_role;

            return redirect(url($role . '/banners'))
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }
}
