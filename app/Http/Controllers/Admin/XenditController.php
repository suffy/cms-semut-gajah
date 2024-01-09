<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Log;
use App\MappingSite;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class XenditController extends Controller
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
        $usersXendit = $this->user
                            ->with('site_name')
                            ->where('account_type', '1')
                            ->where('account_role', 'distributor')
                            ->whereNotNull('xendit_id')
                            ->paginate(10);

        return view('admin.pages.xendit', compact('usersXendit'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'xendit_site' => 'required|string|max:255',
                'xendit_business_name' => 'required|string|max:255',
                'xendit_email' => 'required|string|email|max:255'
            ]
        );

        if ($validator->fails()) {
            return redirect(url('manager/xendit'))
                ->with('status', 2)
                ->with('message', $validator->errors()->first())
                ->withInput()
                ->with('errors', $validator->errors());
        }

        $xenditData = "{\"email\":\"" . $request->xendit_email . "\",\"type\":\"OWNED\",\"public_profile\":{\"business_name\":\"" . $request->xendit_business_name . "\"}}";

        // dd(json_encode($xenditData));

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.xendit.co/v2/accounts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $xenditData,
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic eG5kX2RldmVsb3BtZW50X1M3Vjh1YUtwV2NwSWI4TkRRRWxnN1VEaFNtZG9EUDlmTGgwdFB1c2J4RzZMazBLYmZzVnM5R2lsNUhreTY6",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        // $data = '{"id":"62a69ca06750c837b884ede3","created":"2022-06-13T02:10:40.852Z","updated":"2022-06-13T02:10:42.766Z","email":"testing9diy98@semutgajah.com","type":"OWNED","public_profile":{"business_name":"DIY TESTING"},"country":"ID","status":"REGISTERED"}';

        // dd($data);
        $response = json_decode($response, true);
        // create users
        $user = $this->user->where('account_type', '1')->where('account_role', 'distributor')->where('site_code', $request->xendit_site)->whereNull('xendit_id')->first();
        $user->xendit_id = $response['id'];
        $user->xendit_business_name = $request->xendit_business_name;
        $user->xendit_created = $response['created'];
        $user->save();

        if ($user) {
            return redirect(url('manager/xendit'))
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        } else {
            return redirect(url('manager/xendit'))
                ->with('status', 1)
                ->with('message', "Error saat menyimpan!");
        }
    }
}
