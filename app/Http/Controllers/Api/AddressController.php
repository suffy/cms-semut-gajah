<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use App\UserAddress;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AddressController extends Controller
{
    protected $users, $userAddress, $logs;

    public function __construct(User $user, UserAddress $userAddress, Log $log)
    {
        $this->users        = $user;
        $this->userAddress  = $userAddress;
        $this->logs         = $log;
    }

    public function get(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        $id = Auth::user()->id;

        $user = $this->users->query();

        if ($request->search) {
            $user   = $user
                    ->with('user_address')
                    ->whereHas('user_address', function ($query) use ($request)
                    {
                        return $query
                            ->where('name', 'like', '%'. $request->search .'%')
                            ->orWhere('shop_name', 'like', '%'. $request->search .'%')
                            ->orWhere('address', 'like', '%'. $request->search .'%')
                            ->orWhere('kelurahan', 'like', '%'. $request->search .'%')
                            ->orWhere('kecamatan', 'like', '%'. $request->search .'%')
                            ->orWhere('kota', 'like', '%'. $request->search .'%')
                            ->orWhere('provinsi', 'like', '%'. $request->search .'%')
                            ->orWhere('kode_pos', 'like', '%'. $request->search .'%');
                    })
                    ->orWhere('phone', $request->search);
        } else {
            $user = $user->with('user_address');
        }

        // get user address
        $user   = $user->find($id);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get user address successfully',
                'data'    => $user
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get user address failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'province'      => 'required',
                'city'          => 'required',
                'district'      => 'required',
                'postal_code'   => 'required',
                'address'       => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        // check user login
        $user = Auth::user();

        try {
            // insert
            $userAddress = $this->userAddress;

            $userAddress->user_id   = $user->id;
            $userAddress->name      = $user->name;
            $userAddress->provinsi  = $request->province;
            $userAddress->kota      = $request->city;
            $userAddress->kecamatan = $request->district;
            $userAddress->kode_pos  = $request->postal_code;
            $userAddress->address   = $request->address;
            $userAddress->type      = $request->type;

            $userAddress->save();
            
            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new address user with id : " . $user->id;
            $logs->data_content = $userAddress;
            $logs->table_name   = 'user_address';
            $logs->column_name  = 'user_id, name, provinsi, kota, kecamatan, kode_pos, address';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            // insert user_address into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users_address', [
                'X-API-KEY'         => config('erp.x_api_key'),
                'token'             => config('erp.token_api'),
                'id_user'           => $user->id,
                'mapping_site_id'   => $userAddress->mapping_site_id,
                'name'              => $user->name,
                'shop_name'         => $userAddress->shop_name,
                'address_name'      => $userAddress->address_name,
                'address_phone'     => $userAddress->address_phone,
                'address'           => $request->get('address'),
                'id_provinsi'       => null,
                'provinsi'          => $request->get('province'),
                'id_kota'           => null,
                'kota'              => $request->get('city'),
                'id_kelurahan'      => null,
                'kelurahan'         => $request->get('subdistrict'),
                'id_kecamatan'      => null,
                'kecamatan'         => $request->get('district'),
                'kode_pos'          => $request->get('postal_code'),
                'latitude'          => $userAddress->latitude,
                'longitude'         => $userAddress->longitude,
                'default_address'   => $userAddress->default_address,
                'status'            => '1',
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                'deleted_at'        => $userAddress->deleted_at
            ]);

            // for response
            $response   = $this->users
                        ->where('id', $userAddress->user_id)
                        ->with('user_address')
                        ->first();

            return response()->json([
                'success' => true,
                'message' => 'Update default address successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update default address failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // check user login
        $user = Auth::user();

        try {
            // update address
            $address = $this->userAddress->find($id);

            $address->provinsi  = $request->province;
            $address->kota      = $request->city;
            $address->kecamatan = $request->district;
            $address->kelurahan = $request->subdistrict;
            $address->kode_pos  = $request->postal_code;
            $address->address   = $request->address;
            $address->type      = $request->type;

            $address->save();

            // update user_address into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users_address', [
                'X-API-KEY'         => config('erp.x_api_key'),
                'token'             => config('erp.token_api'),
                'id_user'           => $user->id,
                'mapping_site_id'   => $address->mapping_site_id,
                'name'              => $user->name,
                'shop_name'         => $address->shop_name,
                'address_name'      => $user->password,
                'address_phone'     => $address->address_phone,
                'address'           => $request->get('address'),
                'id_provinsi'       => null,
                'provinsi'          => $request->get('province'),
                'id_kota'           => null,
                'kota'              => $request->get('city'),
                'id_kelurahan'      => null,
                'kelurahan'         => $request->get('subdistrict'),
                'id_kecamatan'      => null,
                'kecamatan'         => $request->get('district'),
                'kode_pos'          => $request->get('postal_code'),
                'latitude'          => $address->latitude,
                'longitude'         => $address->longitude,
                'default_address'   => $address->default_address,
                'status'            => '1',
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                'deleted_at'        => $address->deleted_at
            ]);

            // for response
            $response   = $this->users
                        ->where('id', $address->user_id)
                        ->with('user_address')
                        ->first();

            return response()->json([
                'success' => true,
                'message' => 'Update user address successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update user address failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function updateDefaultAddress($id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // check user login
        $user = Auth::user();

        try {
            // update previous default address
            $userAddressOthers  = $this->userAddress
                            ->where('id', '!=', $id)
                            ->where('user_id', $user->id)
                            ->get();

            foreach ($userAddressOthers as $userAddressOther) {
                $userAddress = $this->userAddress->find($userAddressOther->id);

                $userAddress->default_address = null;

                $userAddress->save();
            }
            
            // update default address
            $userAddress = $this->userAddress->find($id);

            $userAddress->default_address = '1';

            $userAddress->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update address user with id : " . $user->id;
            $logs->data_content = $userAddress;
            $logs->table_name   = 'user_address';
            $logs->column_name  = 'default_address';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            // update default address into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/users_address', [
                'X-API-KEY'         => config('erp.x_api_key'),
                'token'             => config('erp.token_api'),
                'id_user'           => $user->id,
                'mapping_site_id'   => $userAddress->mapping_site_id,
                'name'              => $user->name,
                'shop_name'         => $userAddress->shop_name,
                'address_name'      => $user->password,
                'address_phone'     => $userAddress->address_phone,
                'address'           => $userAddress->address,
                'id_provinsi'       => null,
                'provinsi'          => $userAddress->province,
                'id_kota'           => null,
                'kota'              => $userAddress->city,
                'id_kelurahan'      => null,
                'kelurahan'         => $userAddress->subdistrict,
                'id_kecamatan'      => null,
                'kecamatan'         => $userAddress->district,
                'kode_pos'          => $userAddress->postal_code,
                'latitude'          => $userAddress->latitude,
                'longitude'         => $userAddress->longitude,
                'default_address'   => $userAddress->default_address,
                'status'            => '1',
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                'deleted_at'        => $userAddress->deleted_at
            ]);

            // for response
            $response   = $this->users
                        ->where('id', $user->id)
                        ->with('user_address')
                        ->first();

            return response()->json([
                'success' => true,
                'message' => 'Update default address successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update default address failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
