<?php
namespace App\Services;

class SongService
{
    public function makeCommand(string $effect, string $effectType, $inputPath, $outputPath, bool $ffmpeg = false): string
    {
        $input = storage_path('app/public/' . $inputPath);
        $output = storage_path('app/public/' . $outputPath);

        $filters = [
            'speed_up' => [
                'low' => $ffmpeg ? "rubberband=pitch=1.2,atempo=1.2" : "speed 1.2 reverb",
                'medium' => $ffmpeg ? "rubberband=pitch=1.3,atempo=1.3" : "speed 1.3 reverb",
                'hard' => $ffmpeg ? "rubberband=pitch=1.4,atempo=1.4" : "speed 1.4 reverb",
            ],
            'slowed' => [
                'low' => $ffmpeg ? "rubberband=pitch=0.85,atempo=0.85" : "speed 0.85 reverb",
                'medium' => $ffmpeg ? "rubberband=pitch=0.78,atempo=0.78" : "speed 0.78 reverb",
                'hard' => $ffmpeg ? "rubberband=pitch=0.70,atempo=0.70" : "speed 0.70 reverb",
            ],
            '8d' => [
                'low' => "[0:a]apulsator=hz=0.1:amount=1,stereowiden=delay=15:feedback=0.2:crossfeed=0.2:drymix=0.7",
                'medium' => "[0:a]apulsator=hz=0.25:amount=1,stereowiden=delay=20:feedback=0.3:crossfeed=0.3:drymix=0.8",
                'hard' => "[0:a]apulsator=hz=0.5:amount=1,stereowiden=delay=25:feedback=0.4:crossfeed=0.4:drymix=0.9",
            ],
            'bass' => [
                'low' => $ffmpeg ? "equalizer=f=60:t=q:w=1:g=5" : "bass +6",
                'medium' => $ffmpeg ? "equalizer=f=60:t=q:w=1:g=10" : "bass +12",
                'hard' => $ffmpeg ? "equalizer=f=60:t=q:w=1:g=15" : "bass +18",
            ],
        ];

        if (!isset($filters[$effect][$effectType])) {
            throw new \InvalidArgumentException("Invalid effect type: $effectType for effect: $effect");
        }

        $filter = $filters[$effect][$effectType];
        $ffmpeg = in_array($effect, ['8d']) || $ffmpeg;

        if ($ffmpeg) {
            $command = "ffmpeg -y -i \"{$input}\" -filter_complex \"{$filter}\" \"{$output}\"";
        } else {
            $command = "sox \"{$input}\" \"{$output}\" {$filter}";
        }

        return $command;
    }
}
