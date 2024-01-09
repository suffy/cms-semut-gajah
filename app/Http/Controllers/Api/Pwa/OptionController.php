<?php

namespace App\Http\Controllers\Api\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataOption;

class OptionController extends Controller
{
    protected $options;
    public function __construct(DataOption $options)
    {
        $this->options = $options;
    }

    public function get()
    {
        try {
            $options = $this->options
                                ->select('id', 'slug', 'icon', 'option_type', 'option_name', 'option_value')
                                ->where('option_type', 'pwa')
                                ->get();

            return response()->json(['success' => true, 'data' => $options], 201);
        } catch(\Exception $e) {
            return response()->json(['success' => false, 'data' => $e->getMessage()], 400);
        }
    }
}
