<?php

namespace App\Http\Controllers\Admin\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Testimonial;
use App\Log;

class TestimonialController extends Controller
{
    protected $testimonials, $logs;
    public function __construct(Testimonial $testimonials, Log $logs)
    {
        $this->testimonials = $testimonials;
        $this->$logs        = $logs;
    }

    public function index(Request $request)
    {
        return view('admin.pages.testimonial');
    }

    public function fetch_data(Request $request)
    {
        $testimonials = $this->testimonials->paginate(5);
        if($request->ajax()){
            return view('admin.pages.pagination_data_testi', compact('testimonials'))->render();
        }
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $this->validate($request, [
    //             'name'        => 'required',
    //             'description' => 'required',
    //             'shop_name'   => 'required',
    //             'city'        => 'required',
    //         ]);

    //         $this->testimonials->create([
    //             'name'  => $request->name,
    //             'description'   => $request->description,
    //             'shop_name' => $request->shop_name,
    //             'city'      => $request->city
    //         ]);
    
    //         return response()->json(['success' => true], 200);
    //     } catch(\Exception $e) {
    //         return response()->json(['data' => $e->getMessage()], 400);
    //     }
    // }

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $this->validate($request, [
    //             'name'        => 'required',
    //             'description' => 'required',
    //             'shop_name'   => 'required',
    //             'city'        => 'required',
    //         ]);

    //         $testi = $this->testimonials->find($id);

    //         $testi->update([
    //             'name'          => $request->name,
    //             'description'   => $request->description,
    //             'shop_name'     => $request->shop_name,
    //             'city'          => $request->city
    //         ]);
    
    //         return response()->json(['success' => true], 200);
    //     } catch(\Exception $e) {
    //         return response()->json(['data' => $e->getMessage()], 400);
    //     }
    // }

    public function accept($id)
    {
        $this->testimonials->find($id)->update([
            'accept'        => 1,
            'updated_at'    => Carbon::now()
        ]);

        $logs = $this->logs;
        $logs->log_time     = Carbon::now();
        $logs->activity     = "accept testimonials with id " . $id;
        $logs->table_id     = $id;
        $logs->table_name   = 'testimonials';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";
        $logs->save();

        return response()->json(['success' => true], 200);
    }

    public function delete($id)
    {
        $this->testimonials->find($id)->delete();

        $logs = $this->logs;
        $logs->log_time     = Carbon::now();
        $logs->activity     = "decline / delete testimonials with id " . $id;
        $logs->table_id     = $id;
        $logs->table_name   = 'testimonials';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";
        $logs->save();

        return response()->json(['success' => true], 200);
    }
}
