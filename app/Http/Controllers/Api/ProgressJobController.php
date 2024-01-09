<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ProgressJobController extends Controller
{
    //progress
    public function getSettings($key)
    {
        $result = setting($key, '100%');

        return response()->json([
            'success' => true,
            'message' => 'Get Settings successfully',
            'data'    => $result
        ], 200);
    }
}
