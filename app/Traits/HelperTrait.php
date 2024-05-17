<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

trait HelperTrait
{
    protected function successResponse($data, $message, $statusCode = 200): JsonResponse
    {
        $array = [
            'data' => $data,
            'message' => $message,
        ];

        return response()->json($array, $statusCode);
    }

    protected function successResponseWithCustomPagination($data, $statusCode): JsonResponse
    {

        return response()->json($data, $statusCode);
    }

    public function successResponseWithPagination($data, $message, $statusCode): JsonResponse
    {
        $array = [
            'data' => $data->items(), // get the items on the current page
            'links' => [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ], // get the pagination links
            'meta' => [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'path' => $data->path(),
                'per_page' => $data->perPage(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ],
            'message' => $message,
        ];

        return response()->json($array, $statusCode);
    }

    protected function errorResponse($error, $message, $statusCode): JsonResponse
    {
        $array = [
            'errors' => $error,
            'message' => $message,
        ];

        return response()->json($array, $statusCode);
    }

    /**
     * Create an Unauthorize JSON response.
     */
    protected function noAuthResponse(): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => 'Unauthorized.',
            'status' => false,
        ], 401);
    }

    /**
     * Create an Unauthorize JSON response.
     *
     * @param  $fullRequest  (provide full request Ex: $request)
     * @param  $fileName  (provide file name Ex: $request->image)
     * @param  $destination  (provide destination folder name Ex:'images')
     */
    protected function fileUpload($fullRequest, $fileName, $destination)
    {
        $file = null;
        $file_url = null;
        if ($fullRequest->hasFile($fileName)) {
            $image = $fullRequest->file($fileName);
            $time = time();
            $file = $fileName.'-'.Str::random(6).$time.'.'.$image->getClientOriginalExtension();
            $destinations = 'uploads/'.$destination;
            $image->move($destinations, $file);
            $file_url = $destination.'/'.$file;
        }

        return $file_url;
    }

    /**
     * Create an Unauthorize JSON response.
     *
     * @param  $fullRequest  (provide full request Ex: $request)
     * @param  $fileName  (provide file name Ex: $request->image)
     * @param  $destination  (provide destination folder name Ex:'images')
     * @param  string  $oldFile  (provide old file name if you want to delete old file Ex: $userData->old_image)
     */
    protected function fileUploadAndUpdate($fullRequest, $fileName, $destination, $oldFile = null)
    {
        $file = null;
        $file_url = null;
        if ($fullRequest->hasFile($fileName)) {
            if ($oldFile) {
                $old_image_path = public_path('uploads/'.$oldFile);
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $image = $fullRequest->file($fileName);
            $time = time();
            $file = $fileName.'-'.Str::random(6).$time.'.'.$image->getClientOriginalExtension();
            $destinations = 'uploads/'.$destination;
            $image->move($destinations, $file);
            $file_url = $destination.'/'.$file;
        }

        return $file_url;
    }

    /**
     * Create an Unauthorize JSON response.
     *
     * @param  $file  (provide file name Ex: $request->image)
     */
    protected function deleteFile($file)
    {
        $image_path = public_path('uploads/'.$file);
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        return true;
    }

    /**
     * Create an Unauthorize JSON response.
     *
     * @param  $fullRequest  (provide full request Ex: $request)
     * @param  $fileName  (provide file name Ex: $request->image)
     * @param  $destination  (provide destination folder name Ex:'images')
     */
    protected function ftpFileUpload($fullRequest, $fileName, $destination)
    {
        $file_url = null;
        if ($fullRequest->hasFile($fileName)) {
            $file_temp = $fullRequest->file($fileName);
            $destinations = 'uploads/' . $destination;
            $file_url = Storage::put($destinations, $file_temp);
        }
        return $file_url;
    }

    /**
     * Create an Unauthorize JSON response.
     *
     * @param  $fullRequest  (provide full request Ex: $request)
     * @param  $fileName  (provide file name Ex: $request->image)
     * @param  $destination  (provide destination folder name Ex:'images')
     * @param  string  $oldFile  (provide old file name if you want to delete old file Ex: $userData->old_image)
     */
    protected function ftpFileUploadAndUpdate($fullRequest, $fileName, $destination, $oldFile = null)
    {
        $file_url = null;
        if ($fullRequest->hasFile($fileName)) {

            if ($oldFile) {
                $old_image_path = public_path('uploads/'.$oldFile);
                if (file_exists($old_image_path)) {
                    Storage::delete($old_image_path);
                }
            }

            $file_temp = $fullRequest->file($fileName);
            $destinations = 'uploads/' . $destination;
            $file_url = Storage::put($destinations, $file_temp);
        }
        return $file_url;
    }

    // Delete File 
    protected function deleteFtpFile($file)
    {
            $file_path = public_path('uploads/'.$file);
            if (file_exists($file_path)) {
                Storage::delete($file_path);
            }

            return true;
    }

    protected function convertToClassName($input)
    {
        $parts = explode('_', $input);
        $classNameParts = array_map('ucfirst', $parts);

        return 'App\Models\\'.implode('', $classNameParts);
    }

    protected function modelToSnakeCase($input)
    {
        $input = str_replace('App\\Models\\', '', $input);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    private function getArrayById(array $array): mixed
    {
        return array_reduce($array, function ($result, $item) {
            $result[$item['id']] = $item;

            return $result;
        }, []);
    }

    protected function camelToSnakeCase($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
