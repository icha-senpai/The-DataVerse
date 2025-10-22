<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Dataverse Socialite Routes
|--------------------------------------------------------------------------
| These routes handle the OAuth flow between OctoberCMS and XenForo.
| They are loaded automatically when the plugin boots.
*/

Route::group(['middleware' => ['web']], function () {

    Route::get('auth/xenforo', function () {
        return Socialite::driver('xenforo')->redirect();
    });

    Route::get('auth/callback', function () {
        $xUser = Socialite::driver('xenforo')->user();

        $user = \RainLab\User\Models\User::firstOrCreate(
            ['email' => $xUser->getEmail()],
            [
                'name'     => $xUser->getName(),
                'username' => $xUser->getNickname() ?: $xUser->getName(),
            ]
        );

        Auth::login($user);

        return redirect('/');
    });

});
