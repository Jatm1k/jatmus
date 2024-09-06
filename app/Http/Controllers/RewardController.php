<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function adsReward(Request $request)
    {
        $user = User::find($request->userid);
        $user->balance += 2;
        $user->save();

        return response()->noContent();
    }
}
