<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function adsReward(Request $request)
    {
        $user = User::find($request->userid);
        if ($user->ad_check_count >= 9) {
            $user->ad_check_count = 0;
            $user->is_premium = true;
            $user->premium_until = now()->addDays(1);
        } else {
            $user->ad_check_count += 1;
        }
        $user->balance += 2;
        $user->next_ad_view = now()->addMinutes(5);
        $user->save();

        return response()->noContent();
    }
}
