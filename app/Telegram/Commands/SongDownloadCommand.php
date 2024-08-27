<?php

namespace App\Telegram\Commands;

use App\Models\Song;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Media\Audio;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SongDownloadCommand extends Command
{
    public function handle(Nutgram $bot): void
    {
        try {
            $bot->sendMessage('Начинаю загрузку вашего трека...');
            $audio = $bot->message()->audio;
            $file = $bot->getFile($audio->file_id);
            [$unieque_name, $file_name] = $this->getFilename($audio);
            $file_path = "songs/{$unieque_name}";
            $result = $bot->downloadFileToDisk($file, "/{$file_path}", 'public');
            Log::info('song:', [$audio, $file, $file_path, $result]);
            if($result) {
                $song = Song::create([
                    'user_id' => $bot->user()->id,
                    'original_filename' => $file_name,
                    'original_path' => $file_path,
                    'original_url' => Storage::url($file_path),
                ]);
                $bot->sendMessage(
                    text: 'Трек загружен! Нажми на кнопку ниже, чтобы создать ремикс из этого трека!',
                    reply_markup: InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make(
                        text: 'Создать ремикс!',
                        web_app: WebAppInfo::make(env('APP_URL') . "/remix/{$song->id}"),
                    ))
                );
            } else {
                $bot->sendMessage('Произошла ошибка при загрузке трека');
            }
        } catch (\Exception $e) {
            $bot->sendMessage('Произошла ошибка при загрузке трека');
        }
    }

    private function getFilename(Audio $audio): array
    {
        $extension = '';
        switch ($audio->mime_type) {
            case 'audio/mpeg':
                $extension = 'mp3';
                break;
            case 'audio/ogg':
                $extension = 'ogg';
                break;
            case 'audio/wav':
                $extension = 'wav';
                break;
            default:
                throw new \Exception('Unsupported file type');
        }

        $file_name = $audio->file_name;
        $path_info = pathinfo($file_name);

        if (!isset($path_info['extension'])) {
            $file_name .= '.' . $extension;
        } else {
            $current_extension = $path_info['extension'];
            if ($current_extension !== $extension) {
                $file_name = $path_info['filename'] . '.' . $extension;
            }
        }

        return [$audio->file_unique_id . '.' . $extension, $file_name];
    }
}
