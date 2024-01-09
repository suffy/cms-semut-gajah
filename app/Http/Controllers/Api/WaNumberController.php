<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\MappingSite;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WaNumberController extends Controller
{
    protected $sites;

    public function __construct(MappingSite $sites)
    {
        $this->sites             = $sites;
    }

    public function login()
    {
        try {
            $number = $this->sites
                            ->select('id', 'kode', 'branch_name', 'provinsi', 'telp_wa')
                            ->where('status_ho', 1)
                            ->get();
    
            return response()->json([
                                    'success' => true,
                                    'message' => 'Get wa number successfully!',
                                    'data'    => $number
                                    ], 201);
        } catch(\Exceptio $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get wa number failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function home()
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

        try {
            $site_code = auth()->user()->site_code;

            $number = $this->sites
                                ->select('id', 'kode', 'branch_name', 'nama_comp', 'provinsi', 'telp_wa')
                                ->where('kode', $site_code)
                                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Get wa number successfully!',
                'data'    => $number
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get wa number failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
