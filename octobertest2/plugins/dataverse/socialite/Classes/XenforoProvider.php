<?php namespace Dataverse\Socialite\Classes;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Support\Arr;

class XenforoProvider extends AbstractProvider
{
    protected $scopes = [];
    protected $baseUrl;

    public function __construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle = [], $baseUrl = null)
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);
        $this->baseUrl = $baseUrl ?: 'https://forum.test'; // fallback for local dev
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(rtrim($this->baseUrl, '/') . '/oauth/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return rtrim($this->baseUrl, '/') . '/oauth/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(rtrim($this->baseUrl, '/') . '/api/me', [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return $data ?: [];
    }

    protected function mapUserToObject(array $user)
    {
        return (new SocialiteUser)->setRaw($user)->map([
            'id'       => Arr::get($user, 'user_id'),
            'nickname' => Arr::get($user, 'username'),
            'name'     => Arr::get($user, 'username'),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => Arr::get($user, 'avatar_urls.l'),
        ]);
    }

    protected function getTokenFields($code)
    {
        return [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'code'          => $code,
        ];
    }

    protected function getConfig($key, $default = null)
    {
        $config = $this->getConfigFromProvider();
        return $config[$key] ?? $default;
    }

    protected function getConfigFromProvider(): array
    {
        return [
            'client_id'     => $this->clientId ?? null,
            'client_secret' => $this->clientSecret ?? null,
            'redirect'      => $this->redirectUrl ?? null,
            'base_url'      => $this->baseUrl ?? null,
        ];
    }
}
