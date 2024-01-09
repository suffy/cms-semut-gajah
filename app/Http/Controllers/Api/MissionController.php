<?php

namespace App\Http\Controllers\Api;

use App\Mission;
use App\MissionUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MissionListResource;
use App\Http\Resources\MissionDetailResource;
use App\Http\Resources\MissionUserResource;
use Illuminate\Support\Facades\Validator;

class MissionController extends Controller
{
    public function list()
    {
        try {
            try {                                                                   // check token
                if (!JWTAuth::parseToken()->authenticate()) {
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

            $missions = Mission::where('status', 1)->with('user')->get();

            return response()->json([
                'success' => true,
                'message' => 'Get list mission successfully',
                'data'    => MissionListResource::collection($missions)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get list mission failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            try {                                                                   // check token
                if (!JWTAuth::parseToken()->authenticate()) {
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

            $mission = Mission::with(['tasks.user'])->find($id);

            // return new MissionDetailResource($mission);
            return response()->json([
                'success' => true,
                'message' => 'Get list mission successfully',
                'data'    => new MissionDetailResource($mission)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get list mission failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function startMission($id)
    {
        try {
            try {                                                                   // check token
                if (!JWTAuth::parseToken()->authenticate()) {
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

            $mission_user   = Mission::findorfail($id);
            $user_id        = auth()->user()->id;
            $cek            = MissionUser::where('mission_id', $id)->where('user_id', $user_id)->first();

            if (!$cek) {
                $mission_user->user()->attach(Auth::user()->id);
            }

            $missions = Mission::where('status', 1)->with('user')->get();

            return response()->json([
                'success' => true,
                'message' => 'Start mission successfully',
                'data'    => MissionListResource::collection($missions)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Start mission failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
