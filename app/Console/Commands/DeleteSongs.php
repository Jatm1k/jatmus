<?php

namespace App\Console\Commands;

use App\Models\Song;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Log;

class DeleteSongs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'songs:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет треки из бд и привязанные файлы';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneWeekAgo = Carbon::now()->subWeek();

        $songsToDelete = Song::where('created_at', '<', $oneWeekAgo)->get();

        foreach ($songsToDelete as $song) {
            if ($song->processed_path &&Storage::disk('public')->exists($song->processed_path)) {
                Storage::disk('public')->delete($song->processed_path);
            }

            if ($song->original_path && Storage::disk('public')->exists($song->original_path)) {
                Storage::disk('public')->delete($song->original_path);
            }

            $song->delete();
        }
        Log::info("Удалено {$songsToDelete->count()} треков");
        $this->info('Треки удалены');
    }
}
