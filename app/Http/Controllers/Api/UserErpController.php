<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use App\UserAddress;
use App\NotificationVerification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserErpController extends Controller
{

    protected $users, $userAddress, $logs;

    public function __construct(User $user, UserAddress $userAddress, Log $log, NotificationVerification $notification)
    {
        $this->users        = $user;
        $this->userAddress  = $userAddress;
        $this->notification = $notification;
        $this->logs         = $log;
    }

    public function check(Request $request)
    {
        try {
            $user   = $this->users
                            ->where('customer_code', $request->customer_code)
                            ->where('code_approval', $request->code_approval)
                            ->where('account_type', '!=', '1')
                            ->whereNotNull('code_approval')
                            ->with('user_address')
                            ->first();

            if($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Check customer code successfully',
                    'data'    => $user
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer code not found or code approval not match',
                    'data'    => $user
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check customer code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                // 'shop_name'         => 'required|string',
                // 'email'             => 'nullable|string|email|max:255|unique:users',
                'phone'             => 'required|string|max:255|unique:users',
                'password'          => 'required|string|min:6|confirmed',
                // 'province'          => 'required|string',
                // 'city'              => 'required|string',
                // 'district'          => 'required|string',
                // 'subdistrict'       => 'required|string',
                // 'postal_code'       => 'required',
                'fcm_token'         => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 200);
        }

        DB::beginTransaction();
        try {
            // update customer at users table
            $user   = $this->users
                    ->where('customer_code', $request->customer_code)
                    ->first();

            if($user->status_blacklist == '1') {
                DB::rollBack();
                return response()->json([
                    'success'   => false,
                    'message'   => 'User ' . $request->customer_code . ' Masuk Dalam Daftar Blacklist',
                    'data'      => null
                ], 200);
            }

            $user->update([
                        'phone'             => $request->phone,
                        'password'          => bcrypt($request->password),
                        'fcm_token'         => $request->fcm_token,
                        'otp_verified_at'   => Carbon::now()
                    ]);
            
            // for response
            $user = $this->users
                            ->with('user_address')
                            ->where('customer_code', $request->customer_code)
                            ->first();
                            
            // generate
            $token = JWTAuth::fromUser($user);

            // insert to user_address table
            $this->userAddress->where('user_id', $user->id)
            ->update([
                // 'shop_name'         => $request->get('shop_name'),
                // 'kelurahan'         => $request->get('subdistrict'),
                // 'kecamatan'         => $request->get('district'),
                // 'kota'              => $request->get('city'),
                // 'provinsi'          => $request->get('province'),
                // 'kode_pos'          => $request->get('postal_code'),
                'default_address'   => '1',
                'status'            => '1',
            ]);

            // insert to notification verification table
            $this->notification->updateOrCreate(
                ['user_id' => $user['id']], 
                ['user_id' => $user['id'],
                'status'     => '1',
                'checked_at' => Carbon::now()]
            );

            if(!is_null($user->phone)) {
                // send notification if verification
                $userkey = config('zenziva.USER_KEY_ZENZIVA');
                $passkey = config('zenziva.API_KEY_ZENZIVA');
                $telepon = $user->phone;
                $message = 'Akun anda dengan code ' . $user['customer_code'] . ' sudah terverifikasi oleh admin, silahkan login kedalam sistem Semut Gajah.';
                $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
                $curlHandle = curl_init();
                curl_setopt($curlHandle, CURLOPT_URL, $url);
                curl_setopt($curlHandle, CURLOPT_HEADER, 0);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($curlHandle, CURLOPT_TIMEOUT,500);
                curl_setopt($curlHandle, CURLOPT_POST, 1);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                    'userkey' => $userkey,
                    'passkey' => $passkey,
                    'to' => $telepon,
                    'message' => $message
                ));
                json_decode(curl_exec($curlHandle), true);
                curl_close($curlHandle); 
            }

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Erp user has been registered with id " . $user->id;
            $logs->table_name   = 'users, user_address';
            $logs->table_id     = $user->id;
            $logs->from_user    = $user->id;
            $logs->to_user      = null;
            $logs->platform     = "erp";

            $logs->save();

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Erp user registered successfully',
                'data'      => [
                    'user'  => $user,
                    'token' => $token
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erp user registered failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
