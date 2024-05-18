<?php

namespace App\Http\Controllers;

use App\Services\VideoProcessingService;
use App\Services\VideoRetrievalService;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VideoController extends Controller
{
    use HelperTrait;

    protected $videoProcessingService;

    protected $videoRetrievalService;

    public function __construct(VideoProcessingService $videoProcessingService,
        VideoRetrievalService $videoRetrievalService)
    {
        $this->videoProcessingService = $videoProcessingService;
        $this->videoRetrievalService = $videoRetrievalService;
    }

    public function transcodeVideo(Request $request)
    {
        try {
            $response = $this->videoProcessingService->processVideo($request);

            return $this->successResponse($response, 'Video transcoded successfully', Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return $this->errorResponse('
            An error occurred while transcoding the video. Please try again later.
            ', $th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getVideosById($id)
    {
        try {
            $result = $this->videoRetrievalService->getVideosById($id);

            if (isset($result['error'])) {
                return $this->errorResponse('Record not found', $result['error'], Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse($result, 'Video fetched successfully', Response::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->errorResponse('
            An error occurred while fetching the video. Please try again later.
            ', $th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
