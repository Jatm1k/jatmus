<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Song;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage(
        text: 'Привет! Я бот для создания ремиксов! Отправь мне трек или нажми кнопку ниже!',
        reply_markup: InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make(
            text: 'Создать ремикс!',
            web_app: WebAppInfo::make(env('APP_URL')),
        ))
    );
})->description('start command');

$bot->onAudio(function (Nutgram $bot) {
    $bot->sendMessage('Начинаю загрузку вашего трека...');
    $audio = $bot->message()->audio;
    $file = $bot->getFile($audio->file_id);
    $file_path = "songs/{$audio->file_name}";
    $result = $bot->downloadFileToDisk($file, "/{$file_path}", 'public');
    Log::info('song:', [$result]);
    if($result) {
        $song = Song::create([
            'user_id' => $bot->user()->id,
            'original_filename' => $audio->file_name,
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
});
