<?php

namespace App\Http\Controllers\Api;

use App\Banner;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class BannerController extends Controller
{
    protected $banners;

    public function __construct(Banner $banner)
    {
        $this->banners = $banner;
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

        try {
            $banners = $this->banners->where('status', 1);
            
            if($request->position) {
                $banners = $banners->where('position', $request->position);
            } 

            $banners = $banners->get();

            return response()->json([
                'success' => true,
                'message' => 'Get banners successfully',
                'data'    => $banners
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get banners failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
