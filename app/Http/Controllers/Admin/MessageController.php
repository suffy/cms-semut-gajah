<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Message;

class MessageController extends Controller
{
    protected $messages;

    public function __construct(Message $message)
    {
        $this->messages = $message;
    }

    public function index(Request $request)
    {
        $messages = $this->messages->query();

        if ($request->search) {
            $messages = $messages->where('phone', 'like', '%' . $request->search . '%')->paginate(10);
        }else if ($request->status == '1') {
            $messages = $messages->where('status', '1')->paginate(10);
        } else {
            $messages = $messages->where('status', null)->paginate(10);
        }

        return view('admin.pages.message', compact('messages'));
    }

    public function update(Request $request, $id)
    {
        $message = $this->messages->find($id);

        $message->status = '1';

        $message->save();

        return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Message Readed!");
    }

    public function destroy(Request $request, $id)
    {
        $this->messages->destroy($id);

        return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Message Deleted!");
    }

    function pageContact(){
        $contact = Message::orderBy('id', 'desc')
                    ->paginate(20);

        return view('admin/pages/contact')
                ->with('contact', $contact);
    }

    function pageComplaint(){
        $contact = Message::orderBy('id', 'desc')
        ->paginate(20);

        return view('admin/pages/complaint')
            ->with('contact', $contact);
    }
}
