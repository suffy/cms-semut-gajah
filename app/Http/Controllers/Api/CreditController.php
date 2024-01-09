<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CreditHistory;
use App\Credit;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreditController extends Controller
{
    protected $creditHistory, $credit;
    
    public function __construct(CreditHistory $creditHistory, Credit $credit)
    {
        $this->creditHistory    = $creditHistory;
        $this->credit           = $credit;
    }

    public function index()
    {
        try {                                                                   // check token
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
            $id = Auth::user()->id;
            $credit = $this->credit->query();

            $credit = $credit->where('customer_id', $id)->with(['histories.order' => function($query) {
                $query->select(['id','invoice']);
            }])->first();

            return response()->json([
                'success' => true,
                'message' => 'Get credit successfully',
                'data'    => $credit
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get credit failed',
                'data'    => $e->getMessage()
            ], 500);            
        }
    }


    public function history(Request $request, $id)
    {
        try {                                                                   // check token
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
            $credit_histories = $this->creditHistory->query();
            
            $credit_histories = $credit_histories
                                        ->where('credit_id', $id)
                                        ->with(['order' => function($query) {
                                                    $query->select(['id','invoice']);
                                                }]);
                                                
            if ($request->order == 'asc') {
                $credit_histories   = $credit_histories->orderBy('created_at', 'asc');
            } else if ($request->order == 'desc') {
                $credit_histories = $credit_histories->orderBy('created_at', 'desc');
            } else {
                $credit_histories = $credit_histories->orderBy('created_at', 'desc');
            }
            
            $credit_histories = $credit_histories->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get credit history successfully',
                'data'    => $credit_histories
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get credit history failed',
                'data'    => $e->getMessage()
            ], 500);   
        }
    }
}
