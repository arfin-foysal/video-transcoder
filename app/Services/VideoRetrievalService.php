<?php

namespace App\Services;

use App\Models\Transcode;
use Illuminate\Support\Facades\Storage;

class VideoRetrievalService
{
    public function getVideosById($id)
    {
        $video = Transcode::find($id);

        if (! $video) {
            return ['error' => 'Record not found'];
        }

        $originalPath = $video->original ? Storage::disk(config('filesystems.default'))->url($video->original) : null;
        $compressedPath = $video->compressed ? Storage::disk(config('filesystems.default'))->url($video->compressed) : null;
        $transcodedPath = $video->transcoded ? Storage::disk(config('filesystems.default'))->url($video->transcoded) : null;

        return [
            'original_url' => $originalPath,
            'compressed_url' => $compressedPath,
            'transcoded_url' => $transcodedPath,
        ];
    }
}
