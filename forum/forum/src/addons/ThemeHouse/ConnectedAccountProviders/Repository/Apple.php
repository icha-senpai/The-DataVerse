<?php

namespace ThemeHouse\ConnectedAccountProviders\Repository;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use XF\Entity\ConnectedAccountProvider;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class Apple extends Repository
{
    protected $algorithmManager;

    public function generatePrivateKey(ConnectedAccountProvider $provider)
    {
        $jsonConverter = new StandardConverter();
        $algorithmManager = $this->getAlgorithmManager();

        $jwsBuilder = new JWSBuilder($jsonConverter, $algorithmManager);
        $payload = $jsonConverter->encode([
            'iss' => $provider->options['team_id'],
            'iat' => \XF::$time,
            'exp' => \XF::$time + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $provider->options['client_id'],
        ]);
        $privateEcKey = $this->generatePrivateECKey($provider);

        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($privateEcKey, [
                'alg' => 'ES256',
                'kid' => $privateEcKey->get('kid'),
            ])
            ->build();

        $serializer = new CompactSerializer($jsonConverter);
        return $serializer->serialize($jws);
    }

    public function decodeIdToken($idToken)
    {
        $algorithmManager = $this->getAlgorithmManager();
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $serializerManager = JWSSerializerManager::create([
            new CompactSerializer(),
        ]);

        $jws = $serializerManager->unserialize($idToken);
        $payload = $jws->getPayload();

        return json_decode($payload, true);
    }

    /**
     * @return ConnectedAccountProvider|Entity
     */
    protected function getConnectedAccountProvider()
    {
        return $this->em->find('XF:ConnectedAccountProvider', 'th_cap_apple');
    }

    protected function getAlgorithmManager()
    {
        if (!$this->algorithmManager) {
            $this->algorithmManager = AlgorithmManager::create([
                new ES256(),
            ]);
        }

        return $this->algorithmManager;
    }

    protected function generatePrivateECKey(ConnectedAccountProvider $provider)
    {
        return JWKFactory::createFromKey($provider->options['private_key'], null, [
            'kid' => $provider->options['private_key_id'],
            'alg' => 'ES256',
        ]);
    }
}