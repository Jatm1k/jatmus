<?php

use App\Http\Controllers\RewardController;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', function (Nutgram $bot) {
    $bot->run();
});

Route::get('/adsreward', [RewardController::class, 'adsReward']);
