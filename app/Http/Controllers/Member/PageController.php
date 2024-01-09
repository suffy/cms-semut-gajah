<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;
use Session;

class PageController extends Controller
{
    //
    public function dashboard(){

    	$id = Session::get('id');
        $user = User::findOrFail($id);

        return view('public/member/dashboard',compact(['user']));
    }
}
