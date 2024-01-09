<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\Otp;
use App\Enums\OtpTypeEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OtpController extends Controller
{
    protected $users;

    public function __construct(User $user)
    {
        $this->users = $user;
    }

    //otp register via sms
    public function store(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();
                
        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->whereNull('verified_at')
        //         ->latest()->first();

        try {
            // generate otp code
            // if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            // } else {
                // $otpCode = $otp->otp_code;
            // }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
            $url = 'https://console.zenziva.net/reguler/api/sendsms/';
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
                'userkey'   => $userkey,
                'passkey'   => $passkey,
                'to'        => $telepon,
                'message'   => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            // if (is_null($otp)) {
                // insert otp code
                Otp::updateOrCreate(
                    ['email_phone' => $request->phone],
                    ['type'         => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);

                // Otp::updateOrCreate(
                //     ['customer_code' => $request->customer_code],
                //     ['email_phone' => $request->phone,
                //     'type' => OtpTypeEnum::REGISTER,
                //     'otp_code' => $otpCode,
                //     'verified_at' => null,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Send OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    //otp register via whatsapp
    public function storeWa(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();

        try {
            // generate otp code
            // if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            // } else {
                // $otpCode = $otp->otp_code;
            // }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
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
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            // if (is_null($otp)) {
                // insert otp code
                Otp::updateOrCreate(
                    ['email_phone'  => $request->phone],
                    ['type'         => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);
                
                // Otp::updateOrCreate(
                //     ['customer_code' => $request->customer_code],
                //     ['email_phone' => $request->phone,
                //     'type' => OtpTypeEnum::REGISTER,
                //     'otp_code' => $otpCode,
                //     'verified_at' => null,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Send OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->where('otp_code', $request->otp_code)
                ->whereNull('verified_at')
                ->latest()->first();
                
        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->where('otp_code', $request->otp_code)
        //         ->whereNull('verified_at')
        //         ->latest()->first();


        if (is_null($otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Verify OTP Code failed',
            ], 500);
        }

        $otp->verified_at = Carbon::now();

        $otp->save();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Verify OTP Code successfully',
                'data'    => $otp
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verify OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    //otp for using forgot password via sms
    public function not_authenticated_otp_sms(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();
                
        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->whereNull('verified_at')
        //         ->latest()->first();

        try {
            // generate otp code
            if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            } else {
                $otpCode = $otp->otp_code;
            }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
            $url = 'https://console.zenziva.net/reguler/api/sendsms/';
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                'userkey'   => $userkey,
                'passkey'   => $passkey,
                'to'        => $telepon,
                'message'   => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            if (is_null($otp)) {
                // insert otp code
                Otp::create([
                    'email_phone'   => $request->phone,
                    'type'          => OtpTypeEnum::FORGOT,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);

                // Otp::create([
                //     'email_phone' => $request->phone,
                //     'customer_code' => $request->customer_code,
                //     'type' => OtpTypeEnum::FORGOT,
                //     'otp_code' => $otpCode,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Send Forgot OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send Forgot OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    //otp for using forgot password via whatsapp
    public function not_authenticated_otp_wa(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();
                
        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->whereNull('verified_at')
        //         ->latest()->first();

        try {
            // generate otp code
            if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            } else {
                $otpCode = $otp->otp_code;
            }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
            $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                'userkey' => $userkey,
                'passkey' => $passkey,
                'to' => $telepon,
                'message' => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            if (is_null($otp)) {
                // insert otp code
                Otp::create([
                    'email_phone'   => $request->phone,
                    'type'          => OtpTypeEnum::FORGOT,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);
                
                // Otp::create([
                //     'email_phone' => $request->phone,
                //     'customer_code' => $request->customer_code,
                //     'type' => OtpTypeEnum::FORGOT,
                //     'otp_code' => $otpCode,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Send Forgot OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send Forgot OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    //otp register via sms
    public function update_phone_sms(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();

        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->whereNull('verified_at')
        //         ->latest()->first();

        try {
            // generate otp code
            if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            } else {
                $otpCode = $otp->otp_code;
            }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
            $url = 'https://console.zenziva.net/reguler/api/sendsms/';
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                'userkey'   => $userkey,
                'passkey'   => $passkey,
                'to'        => $telepon,
                'message'   => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            if (is_null($otp)) {
                // insert otp code
                Otp::create([
                    'email_phone'   => $request->phone,
                    'type'          => OtpTypeEnum::PHONE,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);

                // Otp::create([
                //     'email_phone' => $request->phone,
                //     'customer_code' => $request->customer_code,
                //     'type' => OtpTypeEnum::PHONE,
                //     'otp_code' => $otpCode,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Send OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    //otp register via whatsapp
    public function update_phone_wa(Request $request)
    {
        // check user login
        $otp = Otp::where('email_phone', '=', $request->phone)
                ->where('valid_until', '>=', Carbon::now())
                ->whereNull('verified_at')
                ->latest()->first();
                
        // $otp = Otp::where('customer_code', '=', $request->customer_code)
        //         ->where('valid_until', '>=', Carbon::now())
        //         ->whereNull('verified_at')
        //         ->latest()->first();

        try {
            // generate otp code
            if (is_null($otp)) {
                $otpCode = random_int(100000, 999999);
            } else {
                $otpCode = $otp->otp_code;
            }
            $otpCodeMsg = implode(' ',str_split($otpCode));

            // send otp code
            
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $request->phone;
            $message = 'Akses Masuk Semut Gajah - ' . $otpCodeMsg. '. JANGAN BERI angka ini ke siapa pun';
            $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                'userkey' => $userkey,
                'passkey' => $passkey,
                'to' => $telepon,
                'message' => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);

            // insert otp code
            if (is_null($otp)) {
                // insert otp code
                Otp::create([
                    'email_phone'   => $request->phone,
                    'type'          => OtpTypeEnum::PHONE,
                    'otp_code'      => $otpCode,
                    'customer_code' => $request->customer_code,
                    'valid_until'   => Carbon::now()->addMinutes(30)
                ]);

                // insert otp into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/otp', [
                    'X-API-KEY'     => config('erp.x_api_key'),
                    'token'         => config('erp.token_api'),
                    'email_phone'   => $request->phone,
                    'customer_code' => $request->customer_code,
                    'type'          => OtpTypeEnum::REGISTER,
                    'otp_code'      => $otpCode,
                    'verified_at'   => null,
                    'valid_until'   => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'),
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'server'        => config('server.server')
                ]);

                // Otp::create([
                //     'email_phone' => $request->phone,
                //     'customer_code' => $request->customer_code,
                //     'type' => OtpTypeEnum::PHONE,
                //     'otp_code' => $otpCode,
                //     'valid_until' => Carbon::now()->addMinutes(30)
                // ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Send OTP Code successfully',
                'data'    => $results
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send OTP Code failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
