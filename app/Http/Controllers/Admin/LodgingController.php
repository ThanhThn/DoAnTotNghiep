<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lodging\CreateLodgingRequest;
use App\Http\Requests\Lodging\ListLodgingRequest;
use App\Http\Requests\Lodging\LodgingRequest;
use App\Http\Requests\Lodging\UpdateLodgingRequest;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LodgingController extends Controller
{
    public function __invoke()
    {

    }

    public function list(ListLodgingRequest $request)
    {
        $data = $request->all();
        $service = new LodgingService();
        $result = $service->list($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    function detail(LodgingRequest $request, $lodgingId)
    {
        $service = new LodgingService();
        $result = $service->detailLodging($lodgingId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    function create(CreateLodgingRequest $request)
    {
        $data = $request->all();

        if(!isset($data['user_id'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => "User ID is required",
                    'field' => 'user_id'
                ]]
            ]);
        }

        $service = new LodgingService();
        $result = $service->create($data, $data['user_id']);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result,
            ]
        ]);
    }

    function update(UpdateLodgingRequest $request)
    {
        $data = $request->all();

        $service = new LodgingService();
        $result = $service->updateLodging($data, true);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    function delete(LodgingRequest $request, $lodgingId)
    {
        $service = new LodgingService();
        $result = $service->hardDelete($lodgingId);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => "Xoá nơi cư trú thành công!"
            ]
        ]);
    }

}
