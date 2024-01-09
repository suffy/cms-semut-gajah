<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class FCMController extends Controller
{
    public function index(Request $req){
        $input = $req->all();
        $fcm_token = $input['fcm_token'];
        $user_id = $input['user_id'];
     
     
         $user = User::findOrFail($user_id);
     
        $user->fcm_token = $fcm_token;
        $user->save();
        return response()->json([
            'success'=>true,
            'message'=>'User token updated successfully.'
        ]);
     }
}
