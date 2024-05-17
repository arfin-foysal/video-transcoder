<?php

namespace App\Services;

use App\Models\Transcode;
use App\Traits\HelperTrait;
use FFMpeg\Format\Video\X264;
use Log;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class TranscodedService
{
    use HelperTrait;

    public function show($id)
    {
        return Transcode::findOrFail($id);
    }

    public function fileStore($request)
    {
        try {
            $uploadedVideo = $this->fileUpload($request, 'video', 'original');

            if ($uploadedVideo) {
                $compressedVideo = $this->compressVideo($uploadedVideo);

                if ($compressedVideo) {
                    $transcodedVideo = $this->transcodedVideo($compressedVideo);
                }
            }

            // if ($transcodedVideo && $compressedVideo) {
            //     $this->deleteFile($uploadedVideo);
            // }

            $transcoded = Transcode::create([
                'user_id' => auth()->id() ?? 1,
                'original' => $uploadedVideo ?? '',
                'compressed' => $compressedVideo,
                'transcoded' => $transcodedVideo,
                'status' => 1,
            ]);

            return [
                'original' => $transcoded->original,
                'compressed' => $transcoded->compressed,
                'transcoded' => $transcoded->transcoded,
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function compressVideo($uploadedVideo)
    {
        try {
            $fileName = pathinfo($uploadedVideo, PATHINFO_FILENAME); // Get the filename without extension

            $newFileName = $fileName.'_compressed.mp4'; // Create a new filename with the _compressed.mp4 extension
            $newFilePath = 'secrets/'.$newFileName; // Create a new file path

            FFMpeg::fromDisk('uploads')
                ->open($uploadedVideo)
                ->export()
                ->inFormat(new X264('aac'))
                ->save($newFilePath); // Save the file to the new file path

            return 'secrets/'.$newFileName; // Return the new file path

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function transcodedVideo($uploadedVideo)
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

            FFMpeg::fromDisk('uploads')
                ->open($uploadedVideo)
                ->exportForHLS()
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
                // ->toDisk('secrets')
                ->save($newFilePath); // Save the file to the new file path

            return 'secrets/'.$newFileName; // Return the new file path

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
