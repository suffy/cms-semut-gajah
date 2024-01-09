<?php

namespace App\Http\Controllers\Api\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Testimonial;

class TestimonialController extends Controller
{
    protected $testimonial;
    public function __construct(Testimonial $testimonial)
    {
        $this->testimonial = $testimonial;
    }

    public function get()
    {
        try {
            $top        = $this->testimonial
                                        ->orderBy('id', 'DESC')
                                        ->where('accept', 1)
                                        ->limit(10)
                                        ->get();

            $bottom     = $this->testimonial    
                                        ->orderBy('id', 'DESC')
                                        ->where('accept', 1)
                                        ->limit(4)
                                        ->get();

            return response()->json(
                                    [
                                        'success'   => true, 
                                        'data'      => 
                                                        [
                                                            'top'       => $top, 
                                                            'bottom'    => $bottom
                                                        ]
                                    ], 201);
        } catch(\Exception $e) {
            return response()->json(['success' => false, 'data' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request) 
    {
        try {
            $this->validate($request, [
                'name'        => 'required',
                'description' => 'required',
                'shop_name'   => 'required',
                'city'        => 'required',
            ]);

            $testi = $this->testimonial->create([
                'name'          => $request->name,
                'description'   => $request->description,
                'shop_name'     => $request->shop_name,
                'city'          => $request->city
            ]);
    
            return response()->json(['success' => true, 'data' => $testi], 201);
        } catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()], 400);
        }
    }
}
