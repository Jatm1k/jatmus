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

    public function process(SongProcessRequest $request)
    {
        $user = Auth::user();
        if ($user->balance <= 0) {
            return response()->json([
                'code' => 'balance_error',
                'message' => 'Пополните баланс для создания ремикса',
            ], 403);
        }

        $originalFile = $request->file('song');
        $originalFilename = $originalFile->getClientOriginalName();
        $originalPath = $originalFile->store('songs', 'public');

        $origName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $processedFilename = "{$origName} (JatMusicBot {$request->effect} remix).{$originalFile->extension()}";
        $outputPath = "processed/{$processedFilename}";

        $command = $this->songService->makeCommand($request->effect, $originalPath, $outputPath);
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var === 0) {
            $song = Song::create([
                'user_id' => $user->id,
                'original_filename' => $originalFilename,
                'processed_filename' => $processedFilename,
                'original_path' => Storage::url($originalPath),
                'processed_path' => Storage::url($outputPath),
                'effect' => $request->effect,
            ]);
            Cache::forget('feed');
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
            return Song::query()->whereDate('created_at', Carbon::today())->get();
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
            return Song::query()->where('user_id', $user->id)->get();
        });
        return Inertia::render('Profile', ['songs' => $songs]);
    }
}
