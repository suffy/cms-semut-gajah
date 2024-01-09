<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('public/member/member-register');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Save data to database
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        if ($validator->fails()) {
            $status = 404;
            $msg = "Registrasi gagal!";
            $data = $validator->errors();
            return redirect(url('member/register'))->withErrors($data);
        };

        $user = new $this->user;
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->pin = $request->input('pin');
        $user->email = $request->input('email');
        $user->account_type = 4;
        $user->account_role = "member";
        $user->password = Hash::make($request->input('password'));
        
        if($request->input('renter')=='on'){
            $user->verified_as_renter = 0;
        }
        
        if($request->input('owner')=='on'){
            $user->verified_as_owner = 0;
        }
        
        $user->account_status = 1;

        if($request->hasFile('photo'))
        {
            // image's folder
            $folder = 'user';
            // image's filename
            $newName = "user-" . date('Ymd-His');
            // image's form field name
            $form_name = 'photo';

            $user->photo = \App\Helpers\AboutImage::saveImage($request, $folder, $newName, $form_name);
        }

        $user->save();

        return redirect(url('member/login'))
        ->with('status', 1)
        ->with('message', "Register sukses, silahkan login!");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
