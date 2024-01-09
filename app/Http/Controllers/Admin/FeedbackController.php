<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Feedback;

class FeedbackController extends Controller
{
    protected $feedbacks;

    public function __construct(Feedback $feedback)
    {
        $this->feedbacks = $feedback;
    }

    public function index(Request $request)
    {
        $feedbacks = $this->feedbacks->query();
        $feedbacks = $feedbacks->orderBy('id', 'desc');

        if ($request->search) {
            $feedbacks = $feedbacks->where('message', 'like', '%' . $request->search . '%')->with('user')->paginate(10);
        }else if ($request->start != null && $request->end != null) {
            $feedbacks = $feedbacks->whereBetween('created_at', [$request->start, $request->end])->with('user')->paginate(10);
        } else {
            $feedbacks = $feedbacks->with('user')->paginate(10);
        }

        return view('admin.pages.feedback', compact('feedbacks'));
    }
}
