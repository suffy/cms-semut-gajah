<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\MappingSite;
use App\Message;
use App\TbKota;
use App\User;
use App\UserAddress;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    protected $user, $userAddress, $logs, $messages, $mappingSite, $city;

    protected $statusRegister = [
        'checkUser' => 1,
        'registerUser' => 2,
    ];

    public function __construct(User $user, UserAddress $userAddress, Log $log, Message $message, MappingSite $mappingSite, TbKota $city)
    {
        $this->user = $user;
        $this->userAddress = $userAddress;
        $this->logs = $log;
        $this->messages = $message;
        $this->mappingSite = $mappingSite;
        $this->city = $city;
    }

    // method untuk login
    public function authenticate(Request $request)
    {
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $credentials = $request->only('email', 'password');
        } else {
            $credentials = $request->only('phone', 'password');
        }

        // validation
        $validator = Validator::make(
            $request->all(),
            [
                'fcm_token' => 'required|string',
                'app_version' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 200);
        }

        // auth check
        try {
            $login_type = filter_var($request->email_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $credentials = [$login_type => $request->email_phone, 'password' => $request->password];
            $userValidate = User::where($login_type, '=', $request->email_phone)->first();

            // if($userValidate->status_blacklist == '1') {
            //     return response()->json([
            //         'success'   => false,
            //         'message'   => 'Anda Masuk Dalam Daftar Blacklist',
            //         'data'      => null
            //     ], 200);
            // }

            // if (! $token = JWTAuth::attempt($credentials)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Invalid Credentials',
            //         'data'    => null
            //     ], 201);
            // }
            $token = JWTAuth::attempt($credentials);
            // $token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp]);
            if (is_null($userValidate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 201);
            } else if (!$userToken = JWTAuth::fromUser($userValidate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email / phone number not match',
                    'data' => null,
                ], 201);
            } else if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password not match',
                    'data' => null,
                ], 201);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'data' => null,
            ], 500);
        }

        // update fcm_token
        $user = $this->user->find(Auth::user()->id);

        $user->last_login = date('Y-m-d H:i:s');
        $user->fcm_token = $request->fcm_token;
        $user->app_version = $request->app_version;

        $user->save();

        $appVersion = auth()->user()->app_version;

        // for response
        // kondisi untuk bug aplikasi 1.1.3
        if ($appVersion == '1.1.3') {
            $user = $this->user->select(['users.*', DB::raw("IFNULL(users.photo, '/images/icon-semut-gajah.png') as photo")])->with(['user_address', 'credit_limits', 'credits'])->find(Auth::id());
        } else {
            $user = $this->user->with(['user_address', 'credit_limits', 'credits'])->find(Auth::id());
        }
        // $user = $this->user->with(['user_address', 'credit_limits'])->find(Auth::id());

        if ($user->otp_verified_at == null) {
            return response()->json([
                'success' => false,
                'message' => 'User not verified',
                'data' => null,
            ], 200);
        }

        if ($user->site_code == null) {
            return response()->json([
                'success' => false,
                'message' => 'User not have site code',
                'data' => null,
            ], 200);
        }

        if ($user->customer_code == null) {
            return response()->json([
                'success' => false,
                'message' => 'User not approved',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'User login successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_created_at' => Carbon::now(),
            ],
        ], 201);
    }

    public function register(Request $request)
    {
        $dataUser = $this->user::where('phone', $request->get('phone'))->with(['user_address', 'credit_limits']);
        if (!is_null($request->get('email')) && ($request->get('email') ?? "") != "") {
            $dataUser = $dataUser->where('email', $request->get('email'));
        }
        $dataUser = $dataUser->first(); //get data by phone or email

        // cek apabila data user sudah ada
        if ($dataUser) {
            return response()->json([
                'success' => false,
                'message' => 'Account has been registered',
                'data' => null,
            ], 200);
        }

        $coverage = Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/coverage', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token' => config('erp.token_api'),
            "kodepos" => $request->get('postal_code'),
            "latitude" => $request->get('latitude'),
            "longitude" => $request->get('longitude'),
        ])->json();

        if (isset($coverage['code']) && $coverage['code'] != '200') {
            return response()->json([
                'success' => false,
                'message' => 'Mapping code undefined',
                'data' => null,
            ], 200);
        }

        // jika status == 1 ()
        if ($request->status == $this->statusRegister['checkUser']) {
            return response()->json([
                'success' => true,
                'message' => 'User registered cek successfully',
            ], 201);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'shop_name' => 'required|string',
                'email' => 'nullable|string|email|max:255|unique:users',
                'phone' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'province' => 'required|string',
                'city' => 'required|string',
                'district' => 'required|string',
                'subdistrict' => 'required|string',
                'address' => 'required|string',
                'fcm_token' => 'required|string',
                'postal_code' => 'required',
                'photo_ktp' => 'mimes:jpg,png,jpeg,gif,svg',
                'photo_npwp' => 'mimes:jpg,png,jpeg,gif,svg',
                'photo_toko' => 'required|mimes:jpg,png,jpeg,gif,svg',
                'selfie_ktp' => 'required|mimes:jpg,png,jpeg,gif,svg',
                'shareloc' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 200);
        }

        if ($request->status == $this->statusRegister['registerUser']) {
            try {
                DB::beginTransaction();
                $ktp_newname = null;
                if ($request->file('photo_ktp')) {
                    // KTP
                    $photo_ktp = $request->file('photo_ktp');
                    $ext_ktp = $photo_ktp->getClientOriginalExtension();
                    $ktp = "KTP-" . str_replace(' ', '_', $request->shop_name) . "-" . $request->phone . "-" . date('Ymd-His') . "." . $ext_ktp;
                    $image_resize = InterImage::make($photo_ktp->getRealPath());
                    $relPath = '/images/register/ktp/';
                    if (!file_exists(public_path($relPath))) {
                        mkdir(public_path($relPath), 0755, true);
                    }
                    $image_resize->save(('images/register/ktp/' . $ktp));
                    $ktp_newname = '/images/register/ktp/' . $ktp;
                }

                $npwp_newname = null;
                if ($request->file('photo_npwp')) {
                    // NPWP
                    $photo_npwp = $request->file('photo_npwp');
                    $ext_npwp = $photo_npwp->getClientOriginalExtension();
                    $npwp = "NPWP-" . str_replace(' ', '_', $request->shop_name) . "-" . $request->phone . "-" . date('Ymd-His') . "." . $ext_npwp;
                    $image_resize = InterImage::make($photo_npwp->getRealPath());
                    $relPath = 'images/register/npwp/';
                    if (!file_exists(public_path($relPath))) {
                        mkdir(public_path($relPath), 0755, true);
                    }
                    $image_resize->save(('images/register/npwp/' . $npwp));
                    $npwp_newname = '/images/register/npwp/' . $npwp;
                }

                // Toko
                $photo_toko = $request->file('photo_toko');
                $ext_toko = $photo_toko->getClientOriginalExtension();
                $toko = "Toko-" . str_replace(' ', '_', $request->shop_name) . "-" . $request->phone . "-" . date('Ymd-His') . "." . $ext_toko;
                $image_resize = InterImage::make($photo_toko->getRealPath());
                $relPath = 'images/register/toko/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }
                $image_resize->save(('images/register/toko/' . $toko));
                $toko_newname = '/images/register/toko/' . $toko;

                // Selfie
                $selfie_ktp = $request->file('selfie_ktp');
                $ext_selfie = $selfie_ktp->getClientOriginalExtension();
                $selfie = "Selfie-" . str_replace(' ', '_', $request->shop_name) . "-" . $request->phone . "-" . date('Ymd-His') . "." . $ext_selfie;
                $image_resize = InterImage::make($selfie_ktp->getRealPath());
                $relPath = 'images/register/selfie/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }
                $image_resize->save(('images/register/selfie/' . $selfie));
                $selfie_newname = '/images/register/selfie/' . $selfie;

                // insert to users table
                $user = $this->user->create([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'phone' => $request->get('phone'),
                    'password' => Hash::make($request->get('password')),
                    'account_type' => '4',
                    'account_role' => 'user',
                    'platform' => 'app',
                    'fcm_token' => $request->get('fcm_token'),
                    'otp_verified_at' => Carbon::now(),
                    'photo_ktp' => config('app.url') . $ktp_newname,
                    'photo_npwp' => config('app.url') . $npwp_newname,
                    'photo_toko' => config('app.url') . $toko_newname,
                    'selfie_ktp' => config('app.url') . $selfie_newname,
                    'shareloc' => $request->shareloc,
                    'site_code' => Arr::first($coverage['data']),
                ]);

                $token = JWTAuth::fromUser($user);

                // insert to user_address table
                $this->userAddress->create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'shop_name' => $request->get('shop_name'),
                    'address' => $request->get('address'),
                    'kelurahan' => $request->get('subdistrict'),
                    'kecamatan' => $request->get('district'),
                    'kota' => $request->get('city'),
                    'provinsi' => $request->get('province'),
                    'kode_pos' => $request->get('postal_code'),
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                    'default_address' => '1',
                    'status' => '1',
                ]);

                // insert if register with other address not null
                // if ($request->get('other_address') != '') {
                //     $this->userAddress->create([
                //         'user_id'       => $user->id,
                //         'name'          => $user->name,
                //         'shop_name'     => $request->get('shop_name'),
                //         'address'       => $request->get('other_address'),
                //         'kelurahan'     => $request->get('other_subdistrict'),
                //         'kecamatan'     => $request->get('other_district'),
                //         'kota'          => $request->get('other_city'),
                //         'provinsi'      => $request->get('other_province'),
                //         'kode_pos'      => $request->get('other_postal_code'),
                //         'status'        => '1',
                //     ]);
                // }

                // logs
                $logs = $this->logs;

                $logs->log_time = Carbon::now();
                $logs->activity = "New user has been registered with id " . $user->id;
                $logs->table_name = 'users, user_address';
                $logs->table_id = $user->id;
                $logs->from_user = $user->id;
                $logs->to_user = null;
                $logs->platform = "apps";

                $logs->save();

                // insert users register into erp
                return Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/registrasi', [
                    'id_user'           => $user->id,
                    'name'              => $request->get('name'),
                    'email'             => $request->get('email'),
                    'phone'             => $request->get('phone'),
                    'password'          => Hash::make($request->get('password')),
                    'account_type'      => 4,
                    'account_role'      => 'user',
                    'photo'             => null,
                    'fcm_token'         => $request->get('fcm_token'),
                    'otp_verified_at'   => Carbon::now(),
                    'customer_code'     => null,
                    'kode_type'         => null,
                    'class'             => null,
                    'type_payment'      => null,
                    'site_code'         => $user->site_code,
                    'photo_ktp'         => $ktp_newname,
                    'photo_npwp'        => $npwp_newname,
                    'photo_toko'        => $toko_newname,
                    'selfie_ktp'        => $selfie_newname,
                    'shareloc'          => $request->shareloc,
                    'shop_name'         => $request->get('shop_name'),
                    'address'           => $request->get('address'),
                    'kelurahan'         => $request->get('subdistrict'),
                    'kecamatan'         => $request->get('district'),
                    'kota'              => $request->get('city'),
                    'provinsi'          => $request->get('province'),
                    'kode_pos'          => $request->get('postal_code'),
                    'latitude'          => $request->get('latitude'),
                    'longitude'         => $request->get('longitude'),
                    'X-API-KEY'         => config('erp.x_api_key'),
                    'token'             => config('erp.token_api'),
                ]);

                // insert users into erp
                // Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users', [
                //     'X-API-KEY'         => config('erp.x_api_key'),
                //     'token'             => config('erp.token_api'),
                //     'id_user'           => $user->id,
                //     'name'              => $request->get('name'),
                //     'email'             => $request->get('email'),
                //     'email_verified_at' => null,
                //     'password'          => Hash::make($request->get('password')),
                //     'pin'               => null,
                //     'phone'             => $request->get('phone'),
                //     'phone_verified_at' => null,
                //     'account_type'      => '4',
                //     'account_role'      => 'user',
                //     'photo'             => null,
                //     'credit_limit'      => null,
                //     'last_login'        => null,
                //     'account_status'    => null,
                //     'fcm_token'         => $request->get('fcm_token'),
                //     'platform'          => 'app',
                //     'site_code'         => null,
                //     'customer_code'     => null,
                //     'salesman_code'     => null,
                //     'salesman_erp_code' => null,
                //     'point'             => null,
                //     'badge'             => null,
                //     'remember_token'    => null,
                //     'created_at'        => Carbon::now(),
                //     'updated_at'        => Carbon::now(),
                //     'deleted_at'        => null,
                //     'server'            => config('server.server')
                // ]);

                // insert user_address into erp
                // Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users_address', [
                //     'X-API-KEY'         => config('erp.x_api_key'),
                //     'token'             => config('erp.token_api'),
                //     'id_user'           => $user->id,
                //     'mapping_site_id'   => null,
                //     'name'              => $request->get('name'),
                //     'shop_name'         => $request->get('shop_name'),
                //     // 'address_name'      => Hash::make($request->get('password')),
                //     'address_phone'     => null,
                //     // 'address'           => $request->get('other_address'),
                //     'id_provinsi'       => null,
                //     // 'provinsi'          => $request->get('other_province'),
                //     'id_kota'           => null,
                //     // 'kota'              => $request->get('other_city'),
                //     'id_kelurahan'      => null,
                //     // 'kelurahan'         => $request->get('other_subdistrict'),
                //     'id_kecamatan'      => null,
                //     // 'kecamatan'         => $request->get('other_district'),
                //     // 'kode_pos'          => $request->get('other_postal_code'),
                //     'shop_name'         => $request->get('shop_name'),
                //     'address'           => $request->get('address'),
                //     'kelurahan'         => $request->get('subdistrict'),
                //     'kecamatan'         => $request->get('district'),
                //     'kota'              => $request->get('city'),
                //     'provinsi'          => $request->get('province'),
                //     'kode_pos'          => $request->get('postal_code'),
                //     'latitude'          => null,
                //     'longitude'         => null,
                //     'default_address'   => null,
                //     'status'            => '1',
                //     'created_at'        => Carbon::now(),
                //     'updated_at'        => Carbon::now(),
                //     'deleted_at'        => null,
                //     'server'            => config('server.server')
                // ]);

                // insert other user_address into erp
                // if ($request->get('other_address') != '') {
                //     Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users_address', [
                //         'X-API-KEY'         => config('erp.x_api_key'),
                //         'token'             => config('erp.token_api'),
                //         'id_user'           => $user->id,
                //         'mapping_site_id'   => null,
                //         'name'              => $request->get('name'),
                //         'shop_name'         => $request->get('shop_name'),
                //         'address_name'      => Hash::make($request->get('password')),
                //         'address_phone'     => null,
                //         'address'           => $request->get('address'),
                //         'id_provinsi'       => null,
                //         'provinsi'          => $request->get('province'),
                //         'id_kota'           => null,
                //         'kota'              => $request->get('city'),
                //         'id_kelurahan'      => null,
                //         'kelurahan'         => $request->get('subdistrict'),
                //         'id_kecamatan'      => null,
                //         'kecamatan'         => $request->get('district'),
                //         'kode_pos'          => $request->get('postal_code'),
                //         'latitude'          => null,
                //         'longitude'         => null,
                //         'default_address'   => '1',
                //         'status'            => '1',
                //         'created_at'        => Carbon::now(),
                //         'updated_at'        => Carbon::now(),
                //         'deleted_at'        => null
                //     ]);
                // }

                // for response
                $user = $this->user->with(['user_address', 'credit_limits'])->find($user->id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                        'token_created_at' => Carbon::now(),
                    ],
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
                    'data' => null,
                ], 500);
            }
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            Auth::logout();
            Cache::flush();

            $token = $request->header('Authorization');
            JWTAuth::parseToken()->invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'User successfully sign out',
                'data' => null,
            ], 201);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        // concat(7,right(substr(a.kode_lang,4,6) * 1234,5));

        $appVersion = auth()->user()->app_version;
        // for response
        if ($appVersion == '1.1.3') {
            $user = $this->user->select(['users.*', DB::raw("IFNULL(users.photo, '/images/icon-semut-gajah.png') as photo")])->with(['user_address', 'credit_limits', 'credits'])->find(Auth::id());
        } else {
            $user = $this->user->with(['user_address', 'credit_limits', 'credits'])->find(Auth::id());
        }

        return response()->json([
            'success' => true,
            'lifetime' => config('session.lifetime'),
            'message' => 'Get user successfully',
            'data' => $user,
        ], 201);
    }

    public function getUserById($id)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        $user = $this->user->with(['user_address', 'credit_limits', 'credits'])->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Get user successfully',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    public function updateAuthenticatedUser(Request $request, $id)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'nullable',
                'phone' => 'required',
                'photo' => 'mimes:jpeg,jpg,png,gif|max:1024',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 201);
        }

        try {
            // update user profile
            $user = $this->user->findOrFail($id);

            if ($request->name) {
                $user->name = $request->name;
            }

            if ($request->email) {
                $user->email = $request->email;
            }

            if ($request->phone) {
                $user->phone = $request->phone;
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $ext = $file->getClientOriginalExtension();
                $relPath = '/images/profile/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                $newName = "profile" . date('YmdHis') . "." . $ext;

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/profile/' . $newName));
                $user->photo = '/images/profile/' . $newName;
            }

            $user->save();

            // update users into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token' => config('erp.token_api'),
                'id_user' => $user->id,
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'pin' => $user->pin,
                'phone' => $request->get('phone'),
                'phone_verified_at' => $user->phone_verified_at,
                'account_type' => '4',
                'account_role' => 'user',
                'photo' => $user->photo,
                'credit_limit' => $user->credit_limit,
                'last_login' => $user->last_login,
                'account_status' => $user->account_status,
                'fcm_token' => $request->get('fcm_token'),
                'platform' => 'app',
                'site_code' => $user->site_code,
                'customer_code' => $user->customer_code,
                'salesman_code' => $user->salesman_code,
                'salesman_erp_code' => $user->salesman_erp_code,
                'point' => $user->point,
                'badge' => $user->badge,
                'remember_token' => $user->remember_token,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => $user->deleted_at,
            ]);

            // for response
            $user = $this->user->with(['user_address', 'credit_limits'])->find(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Update user profile successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update user profile failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateQrcode()
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        $id = Auth::user()->id;

        // $qrcode = QrCode::generate($id, '../public/images/qrcode/' . $id . '_user.svg');
        // $data = '/images/qrcode/' . $id . '_user.svg';
        $qrcode = (string) $id;

        // logs
        $logs = $this->logs;

        $logs->log_time = Carbon::now();
        $logs->activity = "User has been generated qrcode with id " . $id;
        $logs->table_name = 'users, user_address';
        $logs->from_user = $id;
        $logs->to_user = null;
        $logs->platform = "apps";

        $logs->save();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get qrcode successfully',
                'data' => $qrcode,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get qrcode failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $isMatch = $request->password == $request->confirmation_password;
        $login_type = filter_var($request->email_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $validator = Validator::make(
            $request->all(),
            [
                'email_phone' => 'required',
                'password' => 'required',
                'confirmation_password' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        try {
            $credentials = [$login_type => $request->email_phone, 'password' => $request->password];
            $userValidate = User::where($login_type, '=', $request->email_phone)->first();

            if (is_null($userValidate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 201);
            } else if (!$isMatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password not match',
                    'data' => null,
                ], 201);
            }

            $userValidate->password = Hash::make($request->get('password'));
            $userValidate->save();

            //update users to erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token' => config('erp.token_api'),
                'id_user' => $userValidate->id,
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'email_verified_at' => $userValidate->email_verified_at,
                'password' => $userValidate->password,
                'pin' => $userValidate->pin,
                'phone' => $request->email_phone,
                'phone_verified_at' => $userValidate->phone_verified_at,
                'account_type' => '4',
                'account_role' => 'user',
                'photo' => $userValidate->photo,
                'credit_limit' => $userValidate->credit_limit,
                'last_login' => $userValidate->last_login,
                'account_status' => $userValidate->account_status,
                'fcm_token' => $request->get('fcm_token'),
                'platform' => 'app',
                'site_code' => $userValidate->site_code,
                'customer_code' => $userValidate->customer_code,
                'salesman_code' => $userValidate->salesman_code,
                'salesman_erp_code' => $userValidate->salesman_erp_code,
                'point' => $userValidate->point,
                'badge' => $userValidate->badge,
                'remember_token' => $userValidate->remember_token,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => $userValidate->deleted_at,
            ]);

            // create message
            $forgotPassword = $this->messages;
            $forgotPassword->name = $userValidate->name;
            $forgotPassword->email = $userValidate->email;
            $forgotPassword->phone = $request->email_phone;
            $forgotPassword->subject = "Forgot Password";
            $forgotPassword->message = "New reset password request from user with phone : " . $request->email_phone;
            $forgotPassword->status = null;

            $forgotPassword->save();

            return response()->json([
                'success' => true,
                'message' => 'Send forgot password successfully',
                'data' => [
                    'user' => $userValidate->name,
                    'phone' => $request->email_phone,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Send forgot password failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkServer()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Server is running',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server is running but not responding',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function sites()
    {
        try {
            $sites = $this->mappingSite->get(['id', 'kode', 'nama_comp']);
            return response()->json([
                'success' => true,
                'message' => 'Get sites successfully',
                'data' => $sites,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get sites failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function city()
    {
        try {
            $city = $this->city->get(['id', 'name']);
            return response()->json([
                'success' => true,
                'message' => 'Get city successfully',
                'data' => $city,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get city failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }
}
