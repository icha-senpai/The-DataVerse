<?php namespace Dataverse\Socialite;

use System\Classes\PluginBase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Dataverse\Socialite\Classes\XenforoProvider;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Dataverse Socialite (XenForo)',
            'description' => 'Login with XenForo (OAuth2) and auto-provision RainLab.User accounts.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-sign-in'
        ];
    }

    public function register(): void
    {
        // Register Laravel’s Socialite bindings
        $this->app->register(SocialiteServiceProvider::class);

        // Extend Socialite with our XenForo driver
        Socialite::extend('xenforo', function ($app) {
            $cfg = $this->xfConfig();

            return Socialite::buildProvider(
                XenforoProvider::class,
                [
                    'client_id'     => $cfg['client_id'],
                    'client_secret' => $cfg['client_secret'],
                    'redirect'      => $cfg['redirect'],
                    'base_url'      => rtrim($cfg['base_url'], '/'),
                ]
            );
        });
    }

    protected function xfConfig(): array
    {
        return [
            'base_url'      => env('XF_OAUTH_BASE', 'https://forum.test'),
            'client_id'     => env('XF_OAUTH_CLIENT_ID', ''),
            'client_secret' => env('XF_OAUTH_CLIENT_SECRET', ''),
            'redirect'      => env('XF_OAUTH_REDIRECT', url('/auth/callback')),
        ];
    }
}
