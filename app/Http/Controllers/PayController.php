<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SergiX44\Nutgram\Nutgram;

class PayController extends Controller
{
    public function buy(Request $request, Nutgram $bot)
    {
        $invoiceLink = $bot->createInvoiceLink(
            "{$request->input('amount')} монет",
            'Покупка монет',
            $request->input('amount'),
            '',
            'XTR',
            [
                ['label' => 'XTR', 'amount' => $request->input('price')],
            ]
        );

        return response()->json([
            'link' => $invoiceLink,
        ]);
    }

    public function addBalance(Request $request)
    {
        $user = auth()->user();
        $user->balance += $request->input('amount');
        $user->save();

        return response()->json([
            'balance' => $user->balance,
        ]);
    }

    public function buyPremium(Request $request)
    {
        $user = auth()->user();
        if ($user->is_premium) {
            return response()->json([
                'message' => 'Вы уже имеете премиум',
            ], 403);
        } elseif ($user->balance >= $request->input('price')) {
            $user->balance -= $request->input('price');
            $user->is_premium = true;
            if ($request->input('duration') == 'month') {
                $user->premium_until = now()->addMonth();
            } elseif ($request->input('duration') == 'year') {
                $user->premium_until = now()->addYear();
            }
            $user->save();
        } else {
            return response()->json([
                'message' => 'Недостаточно монет',
            ], 403);
        }

        return response()->json([
            'user' => $user,
        ]);
    }
}
