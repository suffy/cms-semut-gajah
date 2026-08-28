<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    public function test(Request $request, FirebaseService $firebase)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token required'
            ], 400);
        }

        try {

            $result = $firebase->send(
                $token,
                'Status Orderan',
                'Order Anda telah diproses',
                [
                    'order_id' => '12345',
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
