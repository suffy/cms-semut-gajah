<?php

namespace App\Http\Controllers\Api;

use App\Help;
use App\HelpCategory;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class HelpController extends Controller
{

    protected $helpCategory, $help;

    public function __construct(HelpCategory $helpCategory, Help $help)
    {
        $this->helpCategory = $helpCategory;
        $this->help         = $help;
    }

    public function category()
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
            $helpCategories = $this->helpCategory->get();

            return response()->json([
                'success' => true,
                'message' => 'Get help categories successfully',
                'data'    => $helpCategories
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get help categories failed',
                'data'    => $e->getMessage()
            ], 500);
        }
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
            $helps = $this->help->query();

            if ($request->category_id) {
                // get helps by category_id
                $helps  = $helps
                        ->with('help_category')
                        ->where('help_category_id', $request->category_id);
            } else if ($request->search) {
                // get faq by name
                $helps  = $helps
                        ->with('help_category')
                        ->where('name', 'like', '%'. strtolower($request->search) .'%');
            } else {
                // get all helps
                $helps = $helps->with('help_category');
            }

            $helps = $helps->get();

            return response()->json([
                'success' => true,
                'message' => 'Get helps successfully',
                'data'    => $helps
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get helps failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
