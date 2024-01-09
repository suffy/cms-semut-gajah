<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UserImport;
use App\Log;
use App\MappingSite;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AccessController extends Controller
{
    protected $user, $logs, $mappingSites;

    public function __construct(User $user, Log $log, MappingSite $mappingSite)
    {
        $this->user = $user;
        $this->logs = $log;
        $this->mappingSites = $mappingSite;
    }

    public function index()
    {
        $usersAccess = $this->user->where('account_type', '1')->get();
        $users = $this->user->where('account_type', '1')->paginate(10);
        // get mapping site
        $mappingSites = $this->mappingSites->get();

        return view('admin.pages.access', compact('usersAccess', 'users', 'mappingSites'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'              => 'required|string|max:255',
                'email'             => 'required|string|email|max:255|unique:users',
                'password'          => 'required|string|min:6'
            ]
        );

        if ($validator->fails()) {
            return redirect(url('manager/access'))
                ->with('status', 2)
                ->with('message', $validator->errors()->first())
                ->withInput()
                ->with('errors', $validator->errors());
        }

        // create users
        $user = $this->user;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->account_type = '1';
        $user->account_role = $request->account_role;
        $user->password = bcrypt($request->password);
        $user->platform = "web";

        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new user with name " . $request->name . " and email " . $request->email;
        $logs->table_name   = 'users';
        $logs->table_id     = $user->id;
        $logs->from_user    = auth()->user()->id;
        $logs->platform     = 'web';
        $logs->to_user      = null;

        $logs->save();

        return redirect(url('manager/access'))
                ->with('status', 1)
                ->with('message', "Data Inserted!");
    }

    function show($id){
        $user = $this->user->find($id);

        if($user){
            return view('admin.pages.access-detail', compact('user'));
        }else{
            return redirect(url('admin/access'));
        }

    }

    public function update(Request $request)
    {
        // update user
        $user = $this->user->find($request->user_id);

        $user->account_role = $request->account_role;

        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update role user with name " . $request->name . " and email " . $request->email;
        $logs->data_content = 'account_role : ' . $request->account_role;
        $logs->table_name   = 'users';
        $logs->table_id     = $user->id;
        $logs->column_name  = 'account_role';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $request->user_id;
        $logs->platform     = 'web';

        $logs->save();

        return redirect(url('manager/access'))
                ->with('status', 1)
                ->with('message', "Data Updated!");
    }

    public function updateUser(Request $request)
    {
        $user = $this->user->find($request->id);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        
        if ($request->password != '') {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update user information with name " . $request->name . " and email " . $request->email;
        $logs->data_content = 'name : ' . $request->name . ', phone : ' . $request->phone . ', email : ' . $request->email;
        $logs->table_name   = 'users';
        $logs->table_id     = $user->id;
        $logs->column_name  = 'name, phone, email';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $request->id;

        $logs->save();

        return redirect($request->url)
                ->with('status', 1)
                ->with('message', "Data updated!");
    }

    public function destroy(Request $request, $id)
    {
        // delete user
        $this->user->destroy($id);
        
        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete user with name " . $request->name . " and email " . $request->email;
        $logs->table_name   = 'users';
        $logs->table_id     = $id;
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $id;
        $logs->platform     = 'web';

        $logs->save();

        return redirect(url('manager/access'))
                ->with('status', 1)
                ->with('message', "Data deleted!");
    }

    public function accessImport()
    {
        return view('admin.pages.access-import');
    }
    
    public function uploadExcel()
    {
        try {
            Excel::import(new UserImport, request()->file('file'));
            
            return redirect(url('manager/access'))           
                ->with('status', 1)
                ->with('message', "Data Imported!");
        } catch (\Illuminate\Database\QueryException $e) {
            
            return redirect(url('manager/access-import'))           
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }

    public function updateMappingSite(Request $request)
    {
        $id = $request->user_id;
        $distributor  = $this->user->find($id);
        $site = $this->mappingSites->where('kode',$request->mapping_site)->first();
        $distributor->site_code = $site->kode;

        $distributor->save();

        $response = [
            'distributor'   => $distributor,
            'site'          => $site
        ];
        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Change site name the user_id " . $id . ' to ' . $request->input('mapping_site');
        $logs->table_name   = 'user_address';
        $logs->column_name  = 'mapping_site_id';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $id;
        $logs->platform     = 'web';

        $logs->save();

        if ($distributor) {
            $status = 1;
            $data = $response;
            $msg = 'Update sukses ' . $request->input('price');
            return response()->json(compact('status', 'msg', 'data'), 200);

        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }

    }
}
