<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Commands\SongDownloadCommand;
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

$bot->onAudio([SongDownloadCommand::class, 'handle']);
