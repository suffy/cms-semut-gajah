<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Voucher;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VoucherController extends Controller
{
    protected $vouchers;

    public function __construct(Voucher $voucher)
    {
        $this->vouchers = $voucher;
    }

    public function get()
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
            $vouchers = $this->vouchers->where('status', '1')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Get vouchers successfully',
                'data'    => $vouchers
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get vouchers failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
