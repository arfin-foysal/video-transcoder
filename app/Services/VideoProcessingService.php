<?php

namespace App\Services;

use App\Models\Transcode;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoProcessingService
{
    public function processVideo($request)
    {
        try {
            $uploadedVideo = $this->fileUpload($request, 'video', 'original');

            if ($uploadedVideo) {
                $compressedVideo = $this->compressVideo($uploadedVideo);

                if ($compressedVideo) {
                    $transcodedVideo = $this->transcodeVideo($compressedVideo);
                }
            }

            // Save transcode details to database
            $video = Transcode::create([
                'user_id' => auth()->id() ?? 1,
                'original' => $uploadedVideo ?? '',
                'compressed' => $compressedVideo,
                'transcoded' => $transcodedVideo,
                'status' => 1,
            ]);

            return [
                'id' => $video->id,
                'original_url' => $video->original,
                'compressed_url' => $video->compressed,
                'transcoded_url' => $video->transcoded,
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fileUpload($fullRequest, $fileName, $destination)
    {
        $file = null;
        $file_url = null;

        if ($fullRequest->hasFile($fileName)) {
            $image = $fullRequest->file($fileName);
            $time = time();
            $file = $fileName.'-'.str()->random(6).$time.'.'.$image->getClientOriginalExtension();
            $filePath = $destination.'/'.$file;

            // Choose the storage disk dynamically
            Storage::disk(config('filesystems.default'))->putFileAs($destination, $image, $file);
            $file_url = $filePath;
        }

        return $file_url;
    }

    public function compressVideo($uploadedVideo)
    {
        try {
            $fileName = pathinfo($uploadedVideo, PATHINFO_FILENAME); // Get the filename without extension

            $newFileName = $fileName.'_compressed.mp4'; // Create a new filename with the _compressed.mp4 extension
            $newFilePath = 'compressed/'.$newFileName; // Create a new file path

            FFMpeg::fromDisk(config('filesystems.default'))
                ->open($uploadedVideo)
                ->export()
                ->toDisk(config('filesystems.default'))
                ->inFormat(new X264('aac'))
                ->onProgress(function ($percentage) {
                    echo "Progress: {$percentage}% compressed\n";
                    Log::info("Progress: {$percentage}% compressed");
                })
                ->save($newFilePath); // Save the file to the new file path

            return $newFilePath; // Return the new file path

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function transcodeVideo($uploadedVideo)
    {
        try {
            $fileName = pathinfo($uploadedVideo, PATHINFO_FILENAME); // Get the filename without extension

            $newFileName = $fileName.'.m3u8'; // Create a new filename with the .m3u8 extension
            $newFilePath = 'secrets/'.$newFileName; // Create a new file path

            $lowBitrate = (new X264('aac'))->setKiloBitrate(250); // 144p
            $midBitrate = (new X264('aac'))->setKiloBitrate(500); // 240p
            $highBitrate = (new X264('aac'))->setKiloBitrate(1000); // 360p
            $superBitrate = (new X264('aac'))->setKiloBitrate(1500); // 480p
            $hdBitrate = (new X264('aac'))->setKiloBitrate(2500); // 720p
            $fullHdBitrate = (new X264('aac'))->setKiloBitrate(4000); // 1080p

            FFMpeg::fromDisk(config('filesystems.default'))
                ->open($uploadedVideo)
                ->exportForHLS()
                ->toDisk(config('filesystems.default'))
                ->addFormat($lowBitrate, function ($media) {
                    $media->addFilter('scale=256:144');
                })
                ->addFormat($midBitrate, function ($media) {
                    $media->addFilter('scale=426:240');
                })
                ->addFormat($highBitrate, function ($media) {
                    $media->addFilter('scale=640:360');
                })
                ->addFormat($superBitrate, function ($media) {
                    $media->addFilter('scale=854:480');
                })
                ->addFormat($hdBitrate, function ($media) {
                    $media->addFilter('scale=1280:720');
                })
                ->addFormat($fullHdBitrate, function ($media) {
                    $media->addFilter('scale=1920:1080');
                })
                ->onProgress(function ($percentage) {
                    echo "Progress: {$percentage}% transcoded\n";
                    Log::info("Progress: {$percentage}% transcoded");
                })
                ->save($newFilePath); // Save the file to the new file path

            return $newFilePath; // Return the new file path

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
