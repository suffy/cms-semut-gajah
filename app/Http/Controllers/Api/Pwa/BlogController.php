<?php

namespace App\Http\Controllers\Api\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Blog;

class BlogController extends Controller
{
    protected $blogs;
    public function __construct(Blog $blogs)
    {
        $this->blogs = $blogs;
    }

    // title, content, image
    public function get()
    {
        $highlights     = $this->blogs
                                    ->orderBy('id', 'DESC')
                                    ->where('status_highlight', 1)
                                    ->select('id', 'title', 'description', 'image', 'created_at')
                                    ->limit(2)
                                    ->get();

        $highlights_id  = $highlights->pluck('id');

        $newests = $this->blogs
                            ->whereNotIn('id', $highlights_id)
                            ->select('id', 'title', 'description')
                            ->limit(4)
                            ->orderBy('id', 'DESC')
                            ->get();

        return response()->json(
                                [
                                    'success'   => true, 
                                    'data'      => 
                                                    [
                                                        'left'      => $highlights, 
                                                        'right'    => $newests
                                                    ]
                                ], 201);
    }
}
