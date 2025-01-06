<?php

namespace App\Telegram\Conversations;

use App\Models\Transaction;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class RefundConversation extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage('Отправьте ID транзакции');
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        try {
            $transactionId = $bot->message()->text;
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            if (!$transaction || $transaction->user_id != $bot->userId()) {
                throw new \Exception('Транзакция не найдена');
            }
            $user = $transaction->user;
            if ($user->balance < $transaction->amount) {
                throw new \Exception("Для возврата необходимо {$transaction->amount} монет на балансе бота");
            }
            $bot->refundStarPayment($bot->message()->text);
            $user->balance -= $transaction->amount;
            $user->save();
            $bot->sendMessage('Средства успешно возвращены');
        } catch (\Exception $e) {
            $bot->sendMessage($e->getMessage());
        }
        $this->end();
    }
}
