<?php

namespace App\Http\Controllers;

use Log;
use Cache;
use FFMpeg\FFMpeg;
use App\Models\Song;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\SongService;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\SongProcessRequest;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;

class MainController extends Controller
{
    public function __construct(
        private SongService $songService
    ) {
    }
    public function index()
    {
        return Inertia::render('Home');
    }

    public function remixBySong(Song $song)
    {
        return Inertia::render('Home', ['song' => $song]);
    }

    public function process(SongProcessRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user->balance <= 0) {
                return response()->json([
                    'code' => 'balance_error',
                    'message' => 'Пополните баланс для создания ремикса',
                ], 403);
            }

            if ($request->hasFile('song')) {
                $originalFile = $request->file('song');
                $originalFilename = $originalFile->getClientOriginalName();
                $originalPath = $originalFile->store('songs', 'public');
            } else {
                $song = Song::find($request->song_id);
                $originalFilename = $song->original_filename;
                $originalPath = $song->original_path;
            }

            $origName = pathinfo($originalFilename, PATHINFO_FILENAME);
            $origExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $processedFilename = "{$origName} (JatMusicBot {$request->effect_type} {$request->effect} remix).{$origExtension}";
            $outputPath = "processed/{$processedFilename}";

            $command = $this->songService->makeCommand($request->effect, $request->effect_type, $originalPath, $outputPath);
            exec($command . ' 2>&1', $output, $return_var);
            $duration = $this->getAudioDuration(storage_path('app/public/' . $outputPath));
            if ($duration < 5) {
                $command = $this->songService->makeCommand($request->effect, $request->effect_type, $originalPath, $outputPath, true);
                exec($command . ' 2>&1', $output, $return_var);
            }
            if ($return_var === 0) {
                $encodedPath = 'processed/' . rawurlencode($processedFilename);
                $song = Song::create([
                    'user_id' => $user->id,
                    'original_filename' => $originalFilename,
                    'processed_filename' => $processedFilename,
                    'original_path' => $originalPath,
                    'processed_path' => $outputPath,
                    'original_url' => Storage::url($originalPath),
                    'processed_url' => Storage::url($encodedPath),
                    'effect' => $request->effect,
                    'effect_type' => $request->effect_type,
                ]);
                $song->load('user');
                Cache::forget('feed');
                Cache::forget("profile_{$user->id}");
                $user->decrement('balance');

                return response()->json(['song' => $song]);
            } else {
                Log::error('Ошибка обработки трека', [$command, $output, $return_var]);
                return response()->json(['message' => 'Произошла ошибка при обработке трека'], 500);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Произошла ошибка при обработке трека'], 500);
        }
    }

    private function getAudioDuration($filePath)
    {
        // Создаем экземпляр FFMpeg
        $ffmpeg = FFMpeg::create();

        // Загружаем аудиофайл
        $audio = $ffmpeg->open($filePath);

        // Получаем информацию о формате
        $format = $audio->getFormat();

        // Извлекаем длительность в секундах
        $duration = $format->get('duration');

        return $duration;
    }

    public function feed()
    {
        $songs = Cache::remember('feed', Carbon::now()->addHour(), function () {
            return Song::query()->whereNotNull('processed_url')->whereDate('created_at', Carbon::today())->get();
        });
        return Inertia::render('Feed', ['songs' => $songs]);
    }
    public function result(Request $request)
    {
        return Inertia::render('Result', ['url' => Storage::url($request->url)]);
    }

    public function profile()
    {
        $user = Auth::user();
        $songs = Cache::remember("profile_{$user->id}", Carbon::now()->addHour(), function () use ($user) {
            return Song::query()->where('user_id', $user->id)->whereNotNull('processed_url')->get();
        });
        return Inertia::render('Profile', ['songs' => $songs]);
    }

    public function sendAudio(Request $request, Nutgram $bot)
    {
        if (!auth()->check()) {
            return response()->json([
                'code' => 'send_error',
                'message' => 'Ошибка авторизации',
            ], 403);
        }

        if ($request->url) {
            $song = Song::where('processed_path', $request->url)->first();
            $audio = InputFile::make(
                storage_path('app/public/' .$request->url),
                $song->processed_filename ?? $song->original_filename
            );
        } elseif ($request->audio) {
            $audio = InputFile::make($request->audio->getRealPath(), $request->filename);
        }
        $link = env('TELEGRAM_BOT_LINK');

        $bot->sendAudio(
            audio: $audio,
            chat_id: auth()->user()->id,
            caption: "[Создать ремикс песни 🎧]({$link})",
            parse_mode: 'MarkdownV2'
        );
        return response()->json(['message' => 'Трек отправлен']);
    }
}
