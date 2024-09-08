<?php
namespace App\Services;

class SongService
{
    public function makeCommand(string $effect, $inputPath, $outputPath, bool $ffmpeg = false): string
    {
        $input = storage_path('app/public/' . $inputPath);
        $output = storage_path('app/public/' . $outputPath);

        if ($effect === 'speed_up') {
            $filter = $ffmpeg ? "rubberband=pitch=1.2,atempo=1.2" : "speed 1.2 reverb";
        } elseif ($effect === 'slowed') {
            $filter = $ffmpeg ? "rubberband=pitch=0.85,atempo=0.8" : "speed 0.85 reverb";
        } elseif ($effect === '8d') {
            $filter = "[0:a]apulsator=hz=0.125:amount=1,stereowiden=delay=20:feedback=0.3:crossfeed=0.3:drymix=0.8";
            $ffmpeg = true;
        } elseif ($effect === 'bass') {
            $filter = $ffmpeg ? "equalizer=f=60:t=q:w=1:g=10" : "bass +12";
        }

        if ($ffmpeg) {
            $command = "ffmpeg -y -i \"{$input}\" -filter_complex \"{$filter}\" \"{$output}\"";
        } else {
            $command = "sox \"{$input}\" \"{$output}\" {$filter}";
        }

        return $command;
    }
}
