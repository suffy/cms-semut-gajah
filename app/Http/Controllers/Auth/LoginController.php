<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    // protected $redirectTo = '/admin';
    protected $redirectTo = '/admin';

    public function showLoginForm()
    {
        return view('auth/login');
    }

    function authenticated(Request $request, $user)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password, 'account_role' => ['superadmin', 'manager', 'admin', 'distributor', 'distributor_ho']])) {
            $usr = User::find($user->id);
            $usr->last_login = date('Y-m-d H:i:s');
            $usr->fcm_token = $request->input('token');
            $usr->save();
    
            Auth::loginUsingId($user->id);
            Session::put('id', $user->id);
    
            // if ( $usr->account_role ) {// do your magic here
            //     return redirect()->route('dashboard');
            // }
            // return redirect('/' . $usr->account_role);
            return redirect('/admin');
        } else {
            Auth::logout();
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }

    public function saveToken() {
        auth()->user()->update(['fcm_token'=>$request->token]);
        return response()->json(['token saved successfully. '.$request->token.'']);
    }
}
