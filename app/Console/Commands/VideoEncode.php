<?php

namespace App\Console\Commands;

use FFMpeg\Format\Video\X264;
use Illuminate\Console\Command;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoEncode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:video-encode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encode video for HLS streaming';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowBitrate = (new X264)->setKiloBitrate(250);
        $midBitrate = (new X264)->setKiloBitrate(500);
        $highBitrate = (new X264)->setKiloBitrate(1000);
        $superBitrate = (new X264)->setKiloBitrate(1500);

        FFMpeg::fromDisk('uploads')

        // daynamically set the video file name
            ->open('video.mp4')
            ->exportForHLS()
            ->addFormat($lowBitrate, function ($media) {
                $media->addFilter('scale=640:480');
            })
            ->addFormat($midBitrate, function ($media) {
                $media->scale(960, 720);
            })
            ->addFormat($highBitrate, function ($media) {
                $media->addFilter(function ($filters, $in, $out) {
                    $filters->custom($in, 'scale=1920:1200', $out); // $in, $parameters, $out
                });
            })
            ->addFormat($superBitrate, function ($media) {
                $media->addLegacyFilter(function ($filters) {
                    $filters->resize(new \FFMpeg\Coordinate\Dimension(2560, 1920));
                });
            })
            ->onProgress(function ($percentage) {
                $this->info("Progress: {$percentage}% transcoded");
            })
            ->toDisk('secrets')
            ->save('video.m3u8');

        $this->info('Video has been encoded for HLS streaming');
    }
}
