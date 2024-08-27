<?php
namespace App\Services;

class SongService
{
    public function makeCommand(string $effect, $inputPath, $outputPath): string
    {
        $input = storage_path('app/public/' . $inputPath);
        $output = storage_path('app/public/' . $outputPath);
        if ($effect === 'speed_up') {
            $filter = "rubberband=pitch=1.2,atempo=1.2";
            // $filter = "atempo=1.5,asetrate=48000";
            // $filter = "atempo=1.5, equalizer=f=1000:t=q:w=1:g=3";
            // $filter = "atempo=1.25, aecho=0.8:0.88:60:0.4";
            // $filter = "atempo=1.5, chorus=0.6:0.9:55:0.4:0.25:2";
            // $filter = "atempo=2.0, dynaudnorm";
        } elseif ($effect === 'slowed') {
            // $filter = "atempo=0.85,aecho=0.8:0.9:1000:0.3,aresample=44100";
            // $filter = "areverse, atempo=0.8, areverse, aecho=0.8:0.9:1000:0.3";
            // $filter = "asetrate=44100*0.8,aresample=44100,atempo=1/0.8,aecho=0.8:0.88:60:0.4";
            $filter = "rubberband=pitch=0.85,atempo=0.85";
            // $filter = "atempo=0.85,aecho=0.8:0.9:1000:0.3,aresample=44100";
            // $filter = "atempo=0.85,aecho=0.8:0.9:1000:0.3,aresample=44100";
        } elseif ($effect === '8d') {
            $filter = "[0:a]apulsator=hz=0.125:amount=1,stereowiden=delay=20:feedback=0.3:crossfeed=0.3:drymix=0.8";
        } elseif ($effect === 'bass') {
            $filter = "equalizer=f=60:t=q:w=1:g=10";
            // $filter = "bass=g=20,bass=g=15,bass=g=10,dynaudnorm";
        }
        $command = "ffmpeg -y -i \"{$input}\" -filter_complex \"{$filter}\" \"{$output}\"";
        return $command;
    }
}
