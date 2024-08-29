<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Cache;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\SongService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\SongProcessRequest;
use Illuminate\Support\Facades\Auth;
use Log;
use SergiX44\Nutgram\Nutgram;
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
        $user = Auth::user();
        if ($user->balance <= 0) {
            return response()->json([
                'code' => 'balance_error',
                'message' => 'Пополните баланс для создания ремикса',
            ], 403);
        }

        if($request->hasFile('song')) {
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
        $processedFilename = "{$origName} (JatMusicBot {$request->effect} remix).{$origExtension}";
        $outputPath = "processed/{$processedFilename}";

        $command = $this->songService->makeCommand($request->effect, $originalPath, $outputPath);
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var === 0) {
            $encodedPath = 'processed/' . rawurlencode($processedFilename);
            if(isset($song)) {
                $song->update([
                    'processed_filename' => $processedFilename,
                    'processed_path' => $outputPath,
                    'processed_url' => Storage::url($encodedPath),
                    'effect' => $request->effect,
                ]);
            } else {
                $song = Song::create([
                    'user_id' => $user->id,
                    'original_filename' => $originalFilename,
                    'processed_filename' => $processedFilename,
                    'original_path' => $originalPath,
                    'processed_path' => $outputPath,
                    'original_url' => Storage::url($originalPath),
                    'processed_url' => Storage::url($encodedPath),
                    'effect' => $request->effect,
                ]);
            }
            Cache::forget('feed');
            Cache::forget("profile_{$user->id}");
            $user->decrement('balance');

            return response()->json(['song' => $song]);
        } else {
            Log::error('Ошибка обработки трека', [$command, $output, $return_var]);
            return response()->json(['error' => 'Произошла ошибка при обработке трека'], 500);
        }
    }
    public function feed()
    {
        $songs = Cache::remember('feed', Carbon::now()->addDay(), function () {
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
        $songs = Cache::remember("profile_{$user->id}", Carbon::now()->addDay(), function () use ($user) {
            return Song::query()->where('user_id', $user->id)->whereNotNull('processed_url')->get();
        });
        return Inertia::render('Profile', ['songs' => $songs]);
    }

    public function sendAudio(Request $request, Nutgram $bot)
    {
        if (!$request->url) {
            return response()->json([
                'code' => 'send_error',
                'message' => 'Ошибка отправки трека',
            ], 400);
        }
        if (!Storage::disk('public')->exists($request->url)) {
            return response()->json([
                'code' => 'send_error',
                'message' => 'Файл не найден',
            ], 404);
        }
        if (!auth()->check()) {
            return response()->json([
                'code' => 'send_error',
                'message' => 'Ошибка авторизации',
            ], 403);
        }

        $bot->sendAudio(InputFile::make(storage_path('app/public/' .$request->url)), auth()->user()->id);

        return response()->json(['message' => 'Трек отправлен']);
    }
}
