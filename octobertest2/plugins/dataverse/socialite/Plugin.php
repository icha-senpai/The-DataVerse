<?php namespace Dataverse\Socialite;

use System\Classes\PluginBase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Dataverse\Socialite\Classes\XenforoProvider;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Dataverse Socialite',
            'description' => 'OAuth login bridge via Laravel Socialite',
            'author' => 'Dataverse',
            'icon' => 'icon-share-alt'
        ];
    }
    public function boot()
    {
           Socialite::extend('xenforo', function ($app) {
        $config = $app['config']['services.xenforo'];

        return Socialite::buildProvider(XenforoProvider::class, [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect'      => $config['redirect'],
            'base_url'      => $config['base_url'],
        ]);
    });
    }

}
