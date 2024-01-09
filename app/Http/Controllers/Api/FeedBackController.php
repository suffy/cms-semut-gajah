<?php

namespace App\Http\Controllers\Api;

use App\Feedback;
use App\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeedBackController extends Controller
{
    protected $feedback, $logs;

    public function __construct(Feedback $feedback, Log $logs)
    {
        $this->feedback  = $feedback;
        $this->logs      = $logs;
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

        try {
            $user_id            = auth()->user()->id;
            $feedback           = $this->feedback;
            $feedback->user_id  = $user_id;
            $feedback->message  = $request->message;
            $feedback->save();

            // logs
            $logs   = $this->logs
                                ->create([
                                    'log_time'      => Carbon::now(),
                                    'activity'      => 'store feedback',
                                    'table_id'      => $feedback->id,
                                    'data_content'  => $feedback,
                                    'table_name'    => 'feedback',
                                    'column_name'   => 'feedback.user_id, feedback.message',
                                    'from_user'     => auth()->user()->id,
                                    'to_user'       => null,
                                    'platform'      => 'apps',
                                ]);

            return response()->json([
                'success' => true,
                'message' => 'store feedback successfully',
                'data'    => $feedback
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'store feedback failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
