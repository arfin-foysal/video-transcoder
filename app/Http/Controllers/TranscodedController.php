<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Services\TranscodedService;
use App\Traits\HelperTrait;
use Illuminate\Http\Response;

class TranscodedController extends Controller
{
    use HelperTrait;

    protected $transcodedService;

    public function __construct(
        TranscodedService $transcodedService
    ) {
        $this->transcodedService = $transcodedService;
    }

    public function show($id)
    {

        try {
            $transcoded = $this->transcodedService->show($id);

            return $this->successResponse($transcoded, 'Video transcoded successfully', Response::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 'An error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function Store(StoreVideoRequest $request)
    {
        try {

            $transcoded = $this->transcodedService->fileStore($request);

            return $this->successResponse($transcoded, 'Video transcoded successfully', Response::HTTP_CREATED);

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 'An error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
