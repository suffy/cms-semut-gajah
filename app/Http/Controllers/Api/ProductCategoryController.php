<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductCategoryController extends Controller
{
    protected $categories;

    public function __construct(Category $category)
    {
        $this->categories = $category;
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
            $categories = $this->categories->get();

            return response()->json([
                'success' => true,
                'message' => 'Get product category successfully',
                'data'    => $categories
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get product category failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
