<?php

namespace App\Http\Controllers\Admin\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\StoreImage;
use App\Blog;
use App\Log;

class BlogController extends Controller
{
    protected $blogs, $logs;
    public function __construct(Blog $blogs, Log $logs)
    {
        $this->blogs = $blogs;
        $this->$logs = $logs;
    }

    public function index()
    {
        return view('admin.pages.blog');
    }

    public function fetch_data(Request $request)
    {
        $blogs = $this->blogs
            ->orderBy('id', 'DESC')
            ->paginate(10);

        if ($request->ajax()) {
            return view('admin.pages.pagination_data_blog', compact('blogs'))->render();
        }
    }

    public function updateStatus($id)
    {
        $blog = $this->blogs->find($id);
        if ($blog) {
            if ($blog->status_highlight == "1") {
                $blog->status_highlight = "0";
            } else {
                $blog->status_highlight = "1";
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "change status blogs to " . $blog->status;
            $logs->table_id     = $id;
            $logs->table_name   = 'blogs';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $blog->save();

            $status = 1;
            $msg = 'Update sukses ' . $blog->status_highlight;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function store(Request $request)
    {
        $image = null;
        if ($request->hasFile('image')) {
            // image's folder
            $folder = 'blogs';
            // image's filename
            $newName = "blog-" . date('Ymd-His');
            // image's form field name
            $form_name = 'image';

            $image = StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $blog = $this->blogs->create([
            'title'         => $request->title,
            'description'   => $request->description,
            'image'         => $image
        ]);

        $logs = $this->logs;
        $logs->log_time     = Carbon::now();
        $logs->activity     = "insert blogs with id " . $blog->id;
        $logs->table_id     = $blog->id;
        $logs->table_name   = 'blogs';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";
        $logs->save();

        return response()->json(['success' => true], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $blog = $this->blogs->find($id);

            $image = $blog->image;
            if ($request->hasFile('image')) {

                if ($blog->image) {
                    unlink($blog->image);
                }
                // image's folder
                $folder = 'blogs';
                // image's filename
                $newName = "blog-" . date('Ymd-His');
                // image's form field name
                $form_name = 'image';

                $image = StoreImage::saveImage($request, $folder, $newName, $form_name);
            }

            $blog->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'image'         => $image
            ]);

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "update blogs with id " . $id;
            $logs->table_id     = $id;
            $logs->table_name   = 'blogs';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage()], 400);
        }
    }

    public function delete($id)
    {
        $blog = $this->blogs->find($id);

        if ($blog->image) {
            if (file_Exists($blog->image)) {
                unlink($blog->image);
            }
        }

        $blog->delete();

        $logs = $this->logs;
        $logs->log_time     = Carbon::now();
        $logs->activity     = "delete blogs with id " . $id;
        $logs->table_id     = $id;
        $logs->table_name   = 'blogs';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";
        $logs->save();

        return response()->json(['success' => true], 200);
    }
}
