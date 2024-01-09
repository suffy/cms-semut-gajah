<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CustomerImport;
use App\Exports\UserExport;
use App\Log;
use App\MappingSite;
use App\MetaUser;
use App\Salesman;
use App\User;
use App\UserAddress;
use App\PointHistory;
use App\Order;
use App\Otp;
use App\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as InterImage;


class CustomerController extends Controller
{
    protected $users, $userAddress, $mappingSites, $logs, $salesmen, $userMeta, $pointHistories, $orders, $products, $otp;

    public function __construct(User $user, UserAddress $userAddress, MappingSite $mappingSite, Log $log, Salesman $salesman, MetaUser $userMeta, PointHistory $pointHistory, Order $order, Product $products, Otp $otp)
    {
        $this->users            = $user;
        $this->userAddress      = $userAddress;
        $this->mappingSites     = $mappingSite;
        $this->logs             = $log;
        $this->salesmen         = $salesman;
        $this->userMeta         = $userMeta;
        $this->pointHistories   = $pointHistory;
        $this->orders           = $order;
        $this->products         = $products;
        $this->otp              = $otp;
        ini_set('memory_limit', '-1');
    }

    public function index(Request $request)
    {
        // get mapping site
        $mappingSites   = $this->mappingSites->get();

        // get salesmen
        $salesmen       = $this->salesmen->get();

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

        $users          = $this->users->query();

        if ($request->has('search')) {
            $users = $users->where('name', 'like', '%' . $request->search . '%');
        }

        // $users = $users->where('account_role', 'user')->paginate(10);
        if ($request->platform == 'app') {
            $users  = $users
                ->with('user_default_address.site_name')
                ->with('meta_user.salesman')
                ->where('account_role', 'user')
                ->where('platform', 'app')
                ->paginate(10);
        } elseif ($request->platform == 'erp') {
            $users  = $users
                ->with('user_default_address.site_name')
                ->with('meta_user.salesman')
                ->where('account_role', 'user')
                ->where('platform', 'erp')
                ->paginate(10);
        } elseif ($request->site_id == 'false') {
            $users  = $users
                ->with('user_default_address.site_name')
                ->with('meta_user.salesman')
                ->where('account_role', 'user')
                ->whereHas('user_default_address', function ($query) {
                    $query->where('mapping_site_id', null);
                })
                ->paginate(10);
        } else {
            $users  = $users
                ->with('user_default_address.site_name')
                ->with('meta_user.salesman')
                ->where('account_role', 'user')
                ->whereHas('user_default_address', function ($query) {
                    $query->where('mapping_site_id', '!=', null);
                })
                ->paginate(10);
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
            return redirect()
                ->back()
                ->with('status', 2)
                ->with('message', $validator->errors()->first())
                ->withInput()
                ->with('errors', $validator->errors());
        }

        // get site_code
        $mappingSite = $this->mappingSites->find($request->site_id);

        // insert customer
        $customer               = $this->users;

        $customer->name         = $request->name;
        $customer->phone        = $request->phone;
        $customer->email        = $request->email;
        $customer->account_type = '4';
        $customer->account_role = 'user';
        $customer->password     = bcrypt($request->password);
        $customer->credit_limit = $request->credit_limit;
        $customer->site_code    = $mappingSite->kode;

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
        $logs->table_id     = $customer->id;
        $logs->column_name  = 'users.name, users.phone, users.email, users.account_type, users.account_role, users.password, users.credit_limit, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_address.status';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return redirect(url('manager/customers'))
            ->with('status', 1)
            ->with('message', "Data inserted!");
    }

    public function update(Request $request)
    {
        // get site_code
        $mappingSite = $this->mappingSites->find($request->site_id);

        // update users table
        $user = $this->users->find($request->id);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;

        if ($request->password != '') {
            $user->password = bcrypt($request->password);
        }

        $user->credit_limit = $request->credit_limit;
        $user->site_code    = $mappingSite->kode;

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
        $logs->table_name   = $user->id;
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
        $logs->table_name   = $id;
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
        try {
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
            } elseif (auth()->user()->account_role == 'superadmin') {
                return redirect(url('superadmin/customers'))
                    ->with('status', 1)
                    ->with('message', "Data imported!");
            } elseif (auth()->user()->account_role == 'distributor') {
                return redirect(url('distributor/customers'))
                    ->with('status', 1)
                    ->with('message', "Data imported!");
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect(url('manager/customers-import'))
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }

    public function updateMappingSite(Request $request)
    {
        $id = $request->user_id;
        // get mapping site
        $mappingSite = $this->mappingSites->find($request->input('mapping_site'));

        // update users table
        $customer = $this->users->find($id);

        $customer->site_code = $mappingSite->kode;

        $customer->save();

        // update user_address table
        $customerAddress  = $this->userAddress->where('user_id', $id)
            ->update([
                'mapping_site_id' => $request->input('mapping_site')
            ]);

        // for response
        $response   = $this->userAddress
            ->with('site_name')
            ->where('user_id', $id)
            ->where('mapping_site_id', $request->input('mapping_site'))
            ->first();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Change site name the user_id " . $id . ' to ' . $request->input('mapping_site');
        $logs->table_name   = 'user_address';
        $logs->table_name   = $customer->id;
        $logs->column_name  = 'mapping_site_id';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $id;
        $logs->platform     = 'web';

        $logs->save();

        if ($customerAddress) {
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

    public function updateSalesman(Request $request)
    {
        $id = $request->user_id;
        // update salesman
        $user = $this->users->find($id);

        $user->salesman_code = $request->salesman;

        $user->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Change salesman the user_id " . $user->id . ' to ' . $request->input('salesman');
        $logs->table_name   = 'users';
        $logs->table_id     = $user->id;
        $logs->column_name  = 'salesman_code';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $user->id;
        $logs->platform     = 'web';

        $logs->save();

        // response
        $response = $this->users->with('salesman')->where('id', $id)->first();

        if ($user) {
            $status = 1;
            $data = $response;
            $msg = 'Update sukses ' . $request->input('salesman');
            return response()->json(compact('status', 'msg', 'data'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function updateSalesmanErp(Request $request)
    {
        $customerCode = $request->customer;
        // check user erp or mobile
        $userCheck = $this->users->where('customer_code', $customerCode)->where('platform', 'erp')->get();
        // dd($userCheck);
        // check have salesman or not
        $checkSalesman  = $this->userMeta
            ->where('customer_code', $customerCode)
            ->get();

        // get data salesman
        $salesman   = $this->salesmen
            ->where('kodesales', $request->salesman)
            ->first();

        // check user platform and update or create
        if ($userCheck->count() > 0) {
            if ($checkSalesman->count() > 0) {
                $userMeta   = $this->userMeta
                    ->where('customer_code', $customerCode)
                    ->update([
                        'salesman_code' => $request->salesman
                    ]);
            } else {
                $userMeta   = $this->userMeta;

                $userMeta->site_code            = $userCheck[0]->site_code;
                $userMeta->customer_code        = $userCheck[0]->customer_code;
                $userMeta->salesman_code        = $request->salesman;
                $userMeta->salesman_erp_code    = $salesman->kodesales_erp;

                $userMeta->save();
            }
        }

        // response
        $response   = $this->userMeta
            ->with('salesman')
            ->where('customer_code', $customerCode)
            ->where('salesman_code', $request->salesman)
            ->first();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Change user_meta the user_id " . $userCheck[0]->id;
        $logs->table_name   = 'user_meta';
        $logs->table_id     = $userMeta->id;
        $logs->column_name  = 'site_code, customer_code, salesman_code, salesman_erp_code';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $userCheck[0]->id;
        $logs->platform     = 'web';

        $logs->save();

        if ($userMeta) {
            $status = 1;
            $data = $response;
            $msg = 'Update sukses ' . $request->input('salesman');
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
        $logs->table_id     = $id;
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

        if ($request->has('q')) {
            $search = $request->q;
            if ($id != null) {
                $data = $this->salesmen->where('deleted_at', null)
                    ->where('kode', $id)
                    ->where('namasales', 'LIKE', "%" . $search . "%")
                    ->orderBy('namasales')
                    // ->limit(20)
                    ->get();
            } else {
                $data = $this->salesmen->where('deleted_at', null)
                    ->where('namasales', 'LIKE', "%" . $search . "%")
                    ->orderBy('namasales')
                    // ->limit(20)
                    ->get();
            }
        }
        return response()->json($data);
    }

    public function ajaxMappingSite(Request $request)
    {
        $data = [];

        if ($request->has('q')) {
            $search = $request->q;
            $data = $this->mappingSites->where('deleted_at', null)
                ->where('branch_name', 'LIKE', "%" . $search . "%")
                ->orderBy('branch_name')
                // ->limit(20)
                ->get();
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

        return response()->json(['success' => 'Status change successfully.', 'code_approval' => $code_approval]);
    }

    public function exportExcel()
    {
        // $filename   = 'logs-'.$start_date.'-'.$end_date.'.xlsx';
        $filename   = 'user-semut-gajah.xlsx';

        return Excel::download(new UserExport(), $filename);
    }

    public function pointHistory($id)
    {
        $data = [];

        $data = $this->pointHistories->where('customer_id', $id)
            ->with('order')
            // ->limit(20)
            ->get();

        return response()->json($data);
    }

    public function registerApproval(Request $request)
    {

        $users          = $this->users->query();

        if ($request->has('search')) {
            $users = $users->where('name', 'like', '%' . $request->search . '%');
        }

        $cekotp = $this->otp->whereNotNull('verified_at')->pluck('email_phone');
        $users  = $users
            ->whereIn('phone', $cekotp)
            ->where('account_role', 'user')
            ->whereNull('customer_code')
            ->orderBy('created_at')
            ->paginate(10);

        $today = Carbon::now()->subDays(7)->format('Y-m-d H:i:s');
        return view('admin.pages.approval-list', compact('users', 'today'));
    }

    public function detailApproval($id)
    {
        $mappingSites   = $this->mappingSites->get();
        $user = $this->users->find($id);
        return view('admin.pages.approval-detail', compact('user', 'mappingSites'));
    }

    public function approvalPhotoEdit(Request $request)
    {
        if ($request->file('photo')) {
            $photo = $request->file('photo');
            $ext = $photo->getClientOriginalExtension();
            $new_name = $request->edit_name . "-" . date('Ymd-His') . "." . $ext;
            $image_resize = InterImage::make($photo->getRealPath());
            if (file_exists(public_path() . $request->edit_old)) {
                unlink(public_path() . $request->edit_old); //menghapus file lama
            }
            $image_resize->save(("$request->edit_directory/" . $new_name));

            $update = $this->users->whereId($request->edit_id)->update([
                $request->edit_tipe => "/$request->edit_directory/" . $new_name,
            ]);

            if ($update) {
                return redirect()
                    ->back()
                    ->with('status', 1)
                    ->with('message', "Data Saved!");
            } else {
                return redirect()
                    ->back()
                    ->with('status', 2)
                    ->with('message', "Error while Saved!");
            }
        }
    }
}
