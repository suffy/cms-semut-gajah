<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected $user, $logs;

    public function __construct(User $user, Log $log)
    {
        $this->user = $user;
        $this->logs = $log;
    }

    public function index()
    {
        $user = $this->user->find(auth()->id());

        return view('admin.pages.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $this->user->find(auth()->id());

        if ($request->hasFile('photo')) {
    
            if($request->photo!==""){
                if (file_exists(public_path().$user->photo)) {
                    unlink(public_path().$user->photo); //menghapus file lama
                }
            }

            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension();

            $newName = "profile" . date('YmdHis') . "." . $ext;

            $image_resize = Image::make($file->getRealPath());
            $image_resize->save(public_path('images/profile/' . $newName));

            $user->photo = '/images/profile/' . $newName;

        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update profile user with id : " . auth()->id();
        $logs->data_content  = $user;
        $logs->table_name   = 'users';
        $logs->column_name  = 'name, email, phone';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        return redirect(url('admin/profile'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
        ]);

        $user = $this->user->find(auth()->id());

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('status', 2)
                ->with('message', $validator->errors()->first())
                ->with('errors', $validator->errors());
        } else {
            $user->password = Hash::make($request->password);

            $user->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update user password with id : " . auth()->id();
            $logs->data_content = $user;
            $logs->table_name   = 'users';
            $logs->column_name  = 'password';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            return redirect(url('admin/profile'))
                ->with('status', 1)
                ->with('message', "Password Changed!");
        }
    }
}
