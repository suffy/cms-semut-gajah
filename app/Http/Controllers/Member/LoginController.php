<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Session;

class LoginController extends Controller
{

    public function index()
    {
        //
        $user = Auth::user();
        if($user){

            if($user->account_type==4){
                return redirect(url('member/dashboard'));
            }else if($user->account_type==1){
                return redirect(url('admin/dashboard'));
            }
        }else{
            return view('public/member/member-login');
        }
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function auth(Request $request){

        $validate_admin = User::where('email', $request->input('email'))->first();

        if($validate_admin){

            if(Hash::check($request->input('password'), $validate_admin->password)){

                if($validate_admin->account_status==0){
                    return redirect('/member/login')
                    ->with('status', 2)
                    ->with('message', "Oppss, your account is inactive");
                }

                $user = User::find($validate_admin->id);
                $user->fcm_token = $request->input('token');
                $user->save();

                Auth::loginUsingId($validate_admin->id);
                Session::put('id', $validate_admin->id);


                if($validate_admin->account_type==4){
                    return redirect(url('member/dashboard'));
                }else if($validate_admin->account_type==1){
                    return redirect(url('admin/dashboard'));
                }

            }else{

                return redirect('/member/login')
                ->with('status', 2)
                ->with('message', "Oppss, Email atau Password salah");

            }


        }else{
            return redirect('/member/login')
                ->with('status', 2)
                ->with('message', "Oppss, Email atau Password salah");
        }

    }


    public function logout(Request $request) {
        Auth::logout();
        return redirect('/member/login');
    }
}
