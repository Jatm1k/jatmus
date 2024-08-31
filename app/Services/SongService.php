<?php
namespace App\Services;

class SongService
{
    public function makeCommand(string $effect, $inputPath, $outputPath): string
    {
        $input = storage_path('app/public/' . $inputPath);
        $output = storage_path('app/public/' . $outputPath);
        if ($effect === 'speed_up') {
            // ffmpeg
            // $filter = "rubberband=pitch=1.2,atempo=1.2";
            // sox
            $filter = "speed 1.2 reverb";
            $command = "sox \"{$input}\" \"{$output}\" {$filter}";
        } elseif ($effect === 'slowed') {
            // sox
            $filter = "speed 0.85 reverb";
            $command = "sox \"{$input}\" \"{$output}\" {$filter}";
            // ffmpeg
            // $filter = "rubberband=pitch=0.85,atempo=0.8,aecho=1.0:0.7:20:0.5";
        } elseif ($effect === '8d') {
            // ffmpeg
            $filter = "[0:a]apulsator=hz=0.125:amount=1,stereowiden=delay=20:feedback=0.3:crossfeed=0.3:drymix=0.8";
            $command = "ffmpeg -y -i \"{$input}\" -filter_complex \"{$filter}\" \"{$output}\"";
            // sox
            // $filter = "tremolo 0.125 1 delay 0.02 0.02 allpass 1000 0.7 phaser 0.8 0.2 0.01 0.9 0.5";

        } elseif ($effect === 'bass') {
            // $filter = "equalizer=f=60:t=q:w=1:g=10";
            $filter = "bass +12";
            $command = "sox \"{$input}\" \"{$output}\" {$filter}";
        }
        // $command = "ffmpeg -y -i \"{$input}\" -filter_complex \"{$filter}\" \"{$output}\"";
        // $command = "sox \"{$input}\" \"{$output}\" {$filter}";
        return $command;
    }
}
