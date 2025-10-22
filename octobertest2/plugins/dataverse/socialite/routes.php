<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use RainLab\User\Models\User;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Dataverse Socialite Routes (web middleware for sessions/CSRF)
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web']], function () {
    // Kick off the OAuth flow
    Route::get('/auth/xenforo', function () {
        return Socialite::driver('xenforo')->redirect();
    })->name('auth.xenforo');

    // Callback from XenForo
    Route::get('/auth/callback', function () {
        // Will throw if state invalid or code missing; let October exception page show
        $xfUser = Socialite::driver('xenforo')->user();

        // Prefer email as unique key; if missing, synthesize a stable placeholder
        $email = $xfUser->getEmail();
        if (!$email) {
            // You can refine this policy later (e.g., prompt to add email)
            $email = sprintf('user-%s@forum.local', $xfUser->getId() ?: Str::uuid());
        }

        // Find or create RainLab user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User();

            $user->name     = $xfUser->getName() ?: ($xfUser->user['username'] ?? 'Forum User');
            $user->username = $xfUser->getNickname() ?: ($xfUser->user['username'] ?? ('user_' . Str::lower(Str::random(8))));
            $user->email    = $email;

            // Random password since auth comes from XenForo
            $password = Str::random(32);
            $user->password              = $password;
            $user->password_confirmation = $password;

            // Activate immediately (bypasses email verification for SSO)
            $user->is_activated = true;

            $user->save();
        }

        // Log in to October
        Auth::login($user, true);

        // Optional: stash a link to the XenForo user id on the model for future use
        if (!isset($user->xf_user_id) && isset($xfUser->user['user_id'])) {
            // Only if your users table has xf_user_id. If not, skip or store in a pivot/profile table.
            try {
                $user->xf_user_id = (int) $xfUser->user['user_id'];
                $user->save();
            } catch (\Throwable $e) {
                // swallow silently – field might not exist
            }
        }

        return redirect('/'); // or a dashboard page
    })->name('auth.xenforo.callback');

    // Optional: logout helper that also sends users to the forum logout (comment if undesired)
    Route::get('/logout', function () {
        Auth::logout();
        return redirect('/'); // consider redirecting to forum logout URL if you want single-logout
    })->name('logout');
});
