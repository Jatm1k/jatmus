<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyReward;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function checkAuth()
    {
        return response()->json(['auth' => Auth::check(), 'user' => Auth::user()]);
    }

    public function login(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'code' => 'auth_error',
                'message' => 'Ошибка авторизации, попробуйте перезагрузить приложение',
            ], 403);
        }

        $user = User::firstOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'name' => $request->first_name,
                'username' => $request->username,
            ]
        );
        if (!$user->id) {
            $user = User::find($request->id);
        }

        Auth::login($user);
        return response()->json(['user' => $user]);
    }

    public function getDailyReward()
    {
        $user = Auth::user();
        $reward = DailyReward::firstOrCreate(
            ['user_id' => $user->id],
            ['streak' => 0, 'last_claimed_at' => null]
        );

        $today = Carbon::now()->startOfDay();

        // Проверка на пропуск дня
        if ($reward->last_claimed_at) {
            $lastClaimedDay = Carbon::parse($reward->last_claimed_at)->startOfDay();
            if ($lastClaimedDay->diffInDays($today) > 1) {
                // Серия прерывается, сбрасываем счетчик
                $reward->streak = 0;
            } elseif ($lastClaimedDay->diffInDays($today) == 0) {
                // Награда уже была получена сегодня
                return response()->json(['status' => false]);
            }
        }
        $reward->streak = $reward->streak + 1;
        $reward->last_claimed_at = $today;
        $reward->save();

        $rewardAmount = $this->calculateRewardAmount($reward->streak);

        $user->balance += $rewardAmount;
        $user->save();

        return response()->json([
            'status' => true,
            'reward' => [
                'streak' => $reward->streak,
                'amount' => $rewardAmount,
            ],
        ]);
    }

    private function calculateRewardAmount(int $streak)
    {
        return $streak > 5 ? 5 : $streak;
    }
}
