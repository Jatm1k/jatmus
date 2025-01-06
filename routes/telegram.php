<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Transaction;
use App\Models\User;
use App\Telegram\Conversations\RefundConversation;
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
            'balance' => 3,
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

$bot->onPreCheckoutQuery(function (Nutgram $bot) {
    $bot->answerPreCheckoutQuery(true);
});

$bot->onSuccessfulPayment(function (Nutgram $bot) {
    $bot->sendMessage('Спасибо за покупку!');
    Transaction::create([
        'user_id' => $bot->userId(),
        'amount' => $bot->message()->successful_payment->invoice_payload,
        'transaction_id' => $bot->message()->successful_payment->telegram_payment_charge_id,
    ]);
});

$bot->onCommand('refund', RefundConversation::class);
