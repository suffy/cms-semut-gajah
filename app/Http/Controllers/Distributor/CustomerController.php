<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Exports\UserExport;
use App\Log;
use App\MappingSite;
use App\MetaUser;
use App\Salesman;
use App\User;
use App\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected $users, $userAddress, $mappingSites, $logs, $salesmen, $userMeta;

    public function __construct(User $user, UserAddress $userAddress, MappingSite $mappingSite, Log $log, Salesman $salesman, MetaUser $userMeta)
    {
        $this->users = $user;
        $this->userAddress = $userAddress;
        $this->mappingSites = $mappingSite;
        $this->logs = $log;
        $this->salesmen     = $salesman;
        $this->userMeta     = $userMeta;
        ini_set('memory_limit', '-1');
    }

    public function index(Request $request)
    {
        // get mapping site
        $mappingSites = $this->mappingSites->get();

        // get salesmen
        $salesmen = $this->salesmen->get();

        // count users
        $usersToday     = $this->users
                                    ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                                    ->count();
        $usersLastWeek  = $this->users
                                    ->whereBetween('created_at', [Carbon::today()->subDays(7), Carbon::today()->subDays(6)])
                                    ->count();
        $usersLastMonth = $this->users
                                    ->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)
                                    ->count();
        $usersLastYear  = $this->users
                                    ->whereDate('created_at', '=', Carbon::now()->subYear())
                                    ->count();
        $usersThisYear  = $this->users
                                    ->whereYear('created_at', date('Y'))
                                    ->count();

        if(Auth::user()->account_role == 'distributor') {
            $site_code = auth()->user()->site_code;
    
            // check distributor login
            $distributor = $this->users->where('id', Auth::user()->id)->first();
    
            // get id from table mapping_site
            $mapping_site_id    = $this->mappingSites
                                ->where('kode', $distributor->site_code)
                                ->value('id');
            
            $users = $this->users->query();
    
            if ($request->has('search')) {
                $users = $users
                                ->where('site_code', $site_code)
                                ->where('name', 'like', '%'. $request->search .'%');
            }
            
            if ($request->platform == 'app') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->where('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->where('platform', 'app')
                        ->paginate(10);
            } elseif ($request->platform == 'erp') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->where('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->where('platform', 'erp')
                        ->paginate(10);
            } elseif ($request->site_id == 'false') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->where('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->whereHas('user_default_address', function($query)
                        {
                            $query->where('mapping_site_id', null);
                        })
                        ->paginate(10);
            } else {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->where('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->whereHas('user_default_address', function($query)
                        {
                            $query->where('mapping_site_id', '!=', null);
                        })
                        ->paginate(10);
            }
        } else if(Auth::user()->account_role == 'distributor_ho') {
            $sites = $this->mappingSites->where('kode', Auth::user()->site_code)->with(['ho_child' => function($q) {
                $q->select('kode', 'sub');
            }])->first();
            
            $site_code = [];
            foreach($sites->ho_child as $child) {
                array_push($site_code, $child->kode);
            }
    
            // check distributor login
            $distributor = $this->users->where('id', Auth::user()->id)->first();
    
            // get id from table mapping_site
            $mapping_site_id    = $this->mappingSites
                                ->where('kode', $distributor->site_code)
                                ->value('id');
            
            $users = $this->users->query();
    
            if ($request->has('search')) {
                $users = $users
                                ->whereIn('site_code', $site_code)
                                ->where('name', 'like', '%'. $request->search .'%');
            }
            
            if ($request->platform == 'app') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->whereIn('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->where('platform', 'app')
                        ->paginate(10);
            } elseif ($request->platform == 'erp') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->whereIn('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->where('platform', 'erp')
                        ->paginate(10);
            } elseif ($request->site_id == 'false') {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->whereIn('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->whereHas('user_default_address', function($query)
                        {
                            $query->where('mapping_site_id', null);
                        })
                        ->paginate(10);
            } else {
                $users  = $users
                        ->with('user_default_address.site_name')
                        ->with('meta_user.salesman')
                        ->whereIn('site_code', $site_code)
                        ->where('account_role', 'user')
                        ->whereHas('user_default_address', function($query)
                        {
                            $query->where('mapping_site_id', '!=', null);
                        })
                        ->paginate(10);
            }
        }

        $today = Carbon::now()->subDays(7)->format('Y-m-d H:i:s');
        return view('admin.pages.customers', compact('usersToday', 'usersLastWeek', 'usersLastMonth', 'usersLastYear', 'usersThisYear', 'users', 'mappingSites', 'salesmen', 'today'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'              => 'required|string|max:255',
                'email'             => 'required|string|email|max:255|unique:users',
                'phone'             => 'required|string|max:255|unique:users',
                'password'          => 'required|string|min:6'
            ]
        );

        if ($validator->fails()) {
            if (auth()->user()->account_role == 'manager') {
                return redirect(url('manager/customers'))
                    ->with('status', 2)
                    ->with('message', $validator->errors()->first());
            } elseif (auth()->user()->account_role == 'superadmin') {
                return redirect(url('superadmin/customers'))
                    ->with('status', 2)
                    ->with('message', $validator->errors()->first());
            } elseif (auth()->user()->account_role == 'distributor') {
                return redirect(url('distributor/customers'))
                    ->with('status', 2)
                    ->with('message', $validator->errors()->first());
            }
        }

        $customer               = $this->users;

        $customer->name         = $request->name;
        $customer->phone        = $request->phone;
        $customer->email        = $request->email;
        $customer->account_type = '4';
        $customer->account_role = 'user';
        $customer->password     = bcrypt($request->password);
        $customer->credit_limit = $request->credit_limit;

        $customer->save();

        $user = $this->users->where('email', $request->email)->first();

        // insert to user_address table
        $this->userAddress->create([
            'user_id'                   => $user->id,
            'mapping_site_id'           => $request->site_id,
            'name'                      => $user->name,
            'address'                   => $request->address,
            // 'default_address'           => 'true',
            'status'                    => '1',
        ]);

        // insert if register with other address not null
        if ($request->other_address != '') {
            $this->userAddress->create([
                'user_id'           => $user->id,
                'mapping_site_id'   => $user->site_id,
                'name'              => $user->name,
                'address'           => $request->other_address,
                'status'            => '1',
            ]);
        }

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new customer";
        $logs->data_content = "users(name : " . $request->name . ", phone : " . $request->phone . ", email : " . $request->email . ", account_type : 4, account_role : user, password : " . bcrypt($request->password) . ", credit_limit : " . $request->credit_limit . "), user_address(user_id : " . $user->id . ", mapping_site_id : " . $request->site_id . ", name : " . $request->name . ", address : " . $request->address . ", status : 1)";
        $logs->table_name   = 'users, user_address';
        $logs->column_name  = 'users.name, users.phone, users.email, users.account_type, users.account_role, users.password, users.credit_limit, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_address.status';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        
        if (auth()->user()->account_role == 'manager') {
            return redirect(url('manager/customers'))
                ->with('status', 1)
                ->with('message', "Data inserted!");
        } elseif (auth()->user()->account_role == 'superadmin') {
            return redirect(url('superadmin/customers'))
                ->with('status', 1)
                ->with('message', "Data inserted!");
        } elseif (auth()->user()->account_role == 'distributor') {
            return redirect(url('distributor/customers'))
                ->with('status', 1)
                ->with('message', "Data inserted!");
        }
    }

    public function update(Request $request)
    {
        // update users table
        $user = $this->users->find($request->id);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        
        if ($request->password != '') {
            $user->password = bcrypt($request->password);
        }

        $user->credit_limit = $request->credit_limit;

        $user->save();

        // update user_address table
        $userAddress = $this->userAddress->where('user_id', $request->id);

        $userAddress->update([
            'mapping_site_id' => $request->site_id
        ]);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update customer with id : " . $request->id;
        $logs->data_content = "users(name : " . $request->name . ", phone : " . $request->phone . ", email : " . $request->email . ", password : " . bcrypt($request->password) . ", credit_limit : " . $request->credit_limit . "), user_address(mapping_site_id : " . $request->site_id . ")";
        $logs->table_name   = 'users, user_address';
        $logs->column_name  = 'users.name, users.phone, users.email, users.password, users.credit_limit, user_address.mapping_site_id';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $request->id;
        $logs->platform     = 'web';

        $logs->save();

        return redirect($request->url)
                ->with('status', 1)
                ->with('message', "Data updated!");
    }

    public function destroy($id)
    {
        // delete
        $this->users->destroy($id);
        $this->userAddress->where('user_id', $id)->delete();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete customer with id : " . $id;
        $logs->table_name   = 'users, user_address';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $id;
        $logs->platform     = 'web';

        $logs->save();


        if (auth()->user()->account_role == 'manager') {
            return redirect(url('manager/customers'))
                ->with('status', 1)
                ->with('message', "Data deleted!");
        } elseif (auth()->user()->account_role == 'superadmin') {
            return redirect(url('superadmin/customers'))
                ->with('status', 1)
                ->with('message', "Data deleted!");
        } elseif (auth()->user()->account_role == 'distributor') {
            return redirect(url('distributor/customers'))
                ->with('status', 1)
                ->with('message', "Data deleted!");
        }
    }

    public function customerImport()
    {
        return view('admin.pages.customers-import');
    }
    
    public function uploadExcel()
    {
        Excel::import(new CustomerImport, request()->file('file'));
        
        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Import customers";
        $logs->table_name   = 'users';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        if (auth()->user()->account_role == 'manager') {
            return redirect(url('manager/customers'))
                ->with('status', 1)
                ->with('message', "Data imported!");
        } else if(auth()->user()->account_role == 'distributor') {
            return redirect(url('distributor/customers'))
                ->with('status', 1)
                ->with('message', "Data imported!");
        } else if(auth()->user()->account_role == 'superadmin') {
            return redirect(url('superadmin/customers'))
                ->with('status', 1)
                ->with('message', "Data imported!");
        }
    }

    public function updateMappingSite($id, Request $request)
    {
        $customers  = $this->userAddress->where('user_id', $id)
                    ->update([
                        'mapping_site_id' => $request->input('mapping_site')
                    ]);

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

        if ($customers) {
            $status = 1;
            $data = $customers;
            $msg = 'Update sukses ' . $request->input('price');
            return response()->json(compact('status', 'msg', 'data'), 200);

        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }

    }

    public function updateCreditLimit($id, Request $request)
    {
        $customers = $this->users->find($id);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update credit limit user with id : " . $id;
        $logs->table_name   = 'users';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $id;
        $logs->platform     = 'web';

        $logs->save();

        if ($customers) {

            $customers->credit_limit = $request->input('credit_limit');
            $customers->save();

            $status = 1;
            $data = $customers;
            $msg = 'Update sukses ' . $request->input('price');
            return response()->json(compact('status', 'msg', 'data'), 200);

        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function ajaxSalesman($id, Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            if($id != null) {
                $data = $this->salesmen->where('deleted_at', null)
                    ->where('kode', $id)
                    ->where('namasales', 'LIKE', "%".$search."%")
                    ->orderBy('namasales')
                    // ->limit(20)
                    ->get();
            } else {
                $data = $this->salesmen->where('deleted_at', null)
                    ->where('namasales', 'LIKE', "%".$search."%")
                    ->orderBy('namasales')
                    // ->limit(20)
                    ->get();
            }
        }
        return response()->json($data);
    }

    public function ajaxCode(Request $request)
    {
        // generate code approval
        $user = User::find($request->id);

        // random string
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $code_approval = '';

        for ($i = 0; $i < 6; $i++) {
            $code_approval .= $characters[rand(0, $charactersLength - 1)];
        }

        $user->code_approval = $code_approval;
        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Generate code approval users with id : " . $request->id;
        $logs->data_content = "code_approval : " . $code_approval;
        $logs->table_name   = 'users';
        $logs->column_name  = 'code_approval';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return response()->json(['success'=>'Status change successfully.', 'code_approval'=>$code_approval]);
    }

    public function ajaxMappingSite(Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data = $this->mappingSites->where('deleted_at', null)
                ->where('branch_name', 'LIKE', "%".$search."%")
                ->orderBy('branch_name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }
    
    public function exportExcel()
    {
        // $filename   = 'logs-'.$start_date.'-'.$end_date.'.xlsx';
        $filename   = auth()->user()->site_code . '-user-semut-gajah.xlsx';

        return Excel::download(new UserExport(), $filename);
    }
}
