<?php namespace Dataverse\Socialite\Classes;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Arr;

class XenforoProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * Scopes to request from XenForo (keep empty unless you use the XenForo API add-on scopes)
     */
    protected $scopes = [];

    /**
     * Base URL for the XenForo installation.
     */
    protected function getBaseUrl()
    {
        return rtrim(config('services.xenforo.base_url'), '/');
    }

    /**
     * URL to redirect to for authorization.
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl() . '/oauth/authorize', $state);
    }

    /**
     * URL to get an access token from.
     */
    protected function getTokenUrl()
    {
        return $this->getBaseUrl() . '/oauth/token';
    }

    /**
     * Exchange the authorization code for an access token.
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * Fetch the authenticated user from XenForo’s API.
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->getBaseUrl() . '/api/users/me',
            ['headers' => ['Authorization' => 'Bearer ' . $token]]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw XenForo user data to a Socialite user.
     */
    protected function mapUserToObject(array $user)
    {
        // XenForo’s “me” endpoint structure example:
        // {
        //   "user_id": 123,
        //   "username": "Icha",
        //   "email": "icha@example.com",
        //   "user_title": "Pilot",
        //   "avatar_urls": { ... }
        // }

        return (new User())->setRaw($user)->map([
            'id'       => Arr::get($user, 'user_id'),
            'nickname' => Arr::get($user, 'username'),
            'name'     => Arr::get($user, 'username'),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => Arr::get($user, 'avatar_urls.o'),
        ]);
    }
}
