<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use App\Telegram\Commands\SongDownloadCommand;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

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
    if (!User::find($bot->userId())) {
        User::create([
            'id' => $bot->userId(),
            'name' => $bot->user()->first_name,
            'username' => $bot->user()->username,
        ]);
    }
    $bot->sendMessage(
        text: 'Привет! Я бот для создания ремиксов! Отправь мне трек или нажми кнопку ниже!',
        reply_markup: InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make(
            text: 'Создать ремикс!',
            web_app: WebAppInfo::make(env('APP_URL')),
        ))
    );
})->description('start command');

$bot->onAudio([SongDownloadCommand::class, 'handle']);
