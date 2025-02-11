<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function listProvince()
    {
        $provinces = Province::all();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $provinces
            ]
        ]);
    }

    public function listDistrict(Request $request)
    {
        $provinceId = $request->province_id;
        $districts = District::where('province_id', $provinceId)->get();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $districts
            ]
        ]);
    }

    public function listWard(Request $request)
    {
        $districtId = $request->district_id;
        $wards = Ward::where('district_id', $districtId)->get();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $wards
            ]
        ]);
    }
}
