<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Delivery;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
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
            $customer_code  = auth()->user()->customer_code;
            $delivery       = Delivery::where('customer_code', $customer_code)->first();
            if(!$delivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data customer code not found',
                    'data'    => null
                ], 200);
            }

            $data = Http::get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                                'latitude'  => $delivery->latitude,
                                'longitude' => $delivery->longitude,
                                'locality'  => 'en'
                            ])->json()
                            ['localityInfo']
                            ['administrative'];

            if(!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data map not found',
                    'data'    => null
                ], 200);
            }

            $response = [
                'nations'   => $data[0]['name'],
                'province'  => $data[1]['name'],
                'city'      => $data[2]['name'],
                'districts' => $data[3]['name'],
                'village'   => $data[4]['name']
            ];

            return response()->json([
                'success' => true,
                'message' => 'Get Delivery Data successfully',
                'data'    => $response
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get Delivery Data failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
