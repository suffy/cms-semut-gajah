<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;
use App\User;
use Session;
class UserController extends Controller
{
  protected $user;

  public function __construct(User $user){
    $this->user = $user;
  }

  public function index(){
    $id = Session::get('id');
    $user = User::findOrFail($id);
    return view('public.member.user', compact('user'));
  }


  public function edit(User $user){
    return view('public.member.edit-user', compact('user'));
  }

  public function update(Request $request, $id){

   $id = Session::get('id');
   $user = User::findOrFail($id);

    if($user) {


            $user->name   = $request->input('name');
            $user->phone  = $request->input('phone');
            $user->pin    = $request->input('pin');
            $user->email  = $request->input('email');

            if ($request->input('password') !== null) {
                $user->password = Hash::make($request->input('password'));
            };

            if ($request->hasFile('photo')) {

                $file = $request->file('photo');
                $ext = $file->getClientOriginalExtension();

                $newName = "photo" . date('YmdHis') . "." . $ext;
                $img = Image::make($file->getRealPath());
                $img->save(('images/user/' . $newName));

                $user->photo = 'images/user/' . $newName;

            }

            $user->save();


    }

    if ($user) {
        return redirect($request->input('url'))
        ->with('status', 1)
        ->with('message', "Data Saved!");
    }
    }

}
