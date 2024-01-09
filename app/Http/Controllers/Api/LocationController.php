<?php

namespace App\Http\Controllers\Api;

use App\City;
use App\Http\Controllers\Controller;
use App\District;
use App\Province;
use App\Subdistrict;
use Exception;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $provinces, $cities, $districts, $subdistricts;

    public function __construct(Province $province, City $city, District $district, Subdistrict $subdistrict)
    {
        $this->provinces = $province;
        $this->cities = $city;
        $this->districts = $district;
        $this->subdistricts = $subdistrict;
    }

    public function get(Request $request)
    {
        // get provinces
        if ($request->type == 'province') {
            try {
                $locations = $this->provinces->cursor();

                return response()->json([
                    'success' => true,
                    'message' => 'Get locations successfully',
                    'data'    => $locations
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get location failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }

        // get cities
        if ($request->type == 'city') {
            try {
                $cities = $this->cities->where('province_id', $request->province_id)->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Get locations successfully',
                    'data'    => $cities
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get location failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }

        // get districts
        if ($request->type == 'district') {
            try {
                $districts = $this->districts->where('city_id', $request->city_id)->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Get locations successfully',
                    'data'    => $districts
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get location failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }

        // get subdistricts
        if ($request->type == 'subdistrict') {
            try {
                $subdistricts = $this->subdistricts->where('district_id', $request->district_id)->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Get locations successfully',
                    'data'    => $subdistricts
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get location failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }

        // get postal code
        if ($request->type == 'postal_code') {
            try {
                $postalCodes    = $this->subdistricts
                                ->where('province_id', $request->province_id)
                                ->where('city_id', $request->city_id)
                                ->where('district_id', $request->district_id)
                                ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Get locations successfully',
                    'data'    => $postalCodes
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get location failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }
    }
}
