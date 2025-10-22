<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Repository;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;
use XF\Entity\User;
use XF\Entity\UserConnectedAccount;
use XF\Mvc\Entity\Entity;
use XF\PrintableException;
use XF\Util\File;

/**
 * Class ConnectedAccount
 * @package ThemeHouse\ConnectedAccountProviders\XF\Repository
 */
class ConnectedAccount extends XFCP_ConnectedAccount
{
    public function associateConnectedAccountWithUser(\XF\Entity\User $user, AbstractProviderData $providerData)
    {
        parent::associateConnectedAccountWithUser($user, $providerData);

        $this->syncProfileFieldsForProvider($user, $providerData);
    }

    public function syncProfileFieldsForProvider(User $user, \XF\ConnectedAccount\ProviderData\AbstractProviderData $providerData): void
    {
        $options = \XF::options();

        if ($options->thconnectedaccountproviders_updateEmail && $providerData->email) {
            /** @noinspection PhpUndefinedFieldInspection */
            $user->email = $providerData->email;
        }

        if ($options->thconnectedaccountproviders_updateUsername && $providerData->username) {
            /** @noinspection PhpUndefinedFieldInspection */
            $user->username = $providerData->username;
        }

        foreach ($options->thconnectedaccountproviders_linkProfileFields ?? [] as $profileField) {
            if ($profileField['provider_id'] !== $providerData->getProviderId()) {
                continue;
            }

            $value = $providerData->{$profileField['provider_key']};
            if (!$value) {
                continue;
            }

            switch ($profileField['profile_field']) {
                case 'avatar_url':
                    /** @var XF\Service\User\Avatar $avatarService */
                    $avatarService = \XF::service('XF:User\Avatar', $user);
                    $tempFile = File::getTempFile();
                    file_put_contents($tempFile, file_get_contents($value));
                    $avatarService->setImage($tempFile);
                    $avatarService->updateAvatar();
                    break;

                case 'dob':
                    if (is_array($value)) {
                        $user->Profile->dob_day = $value['dob_day'] ?? 0;
                        $user->Profile->dob_month = $value['dob_month'] ?? 0;
                        $user->Profile->dob_year = $value['dob_year'] ?? 0;
                    } else {
                        if (!is_numeric($value)) {
                            $value = strtotime($value);
                        }
                        $user->Profile->dob_day = date('d', $value);
                        $user->Profile->dob_month = date('m', $value);
                        $user->Profile->dob_year = date('Y', $value);
                    }
                    break;

                case 'signature':
                case 'website':
                case 'location':
                    $user->Profile->{$profileField['profile_field']} = $value;
                    break;

                case 'receive_admin_email':
                case 'show_dob_year':
                case 'show_dob_date':
                    $user->Option->{$profileField['profile_field']} = $value;
                    break;

                default:
                    if (str_starts_with($profileField['profile_field'], 'uf_')) {
                        $userFieldId = substr($profileField['profile_field'], 3);
                        $userProfile = $user->Profile;
                        $fieldSet = $userProfile->custom_fields;
                        $fieldSet->set($userFieldId, $value);
                    }
                    break;
            }
        }

        $user->saveIfChanged();
        $user->Profile->saveIfChanged();
        $user->Option->saveIfChanged();
    }

    /**
     * @param User $user
     * @param $providerKey
     * @param $providerId
     * @param array $extraData
     * @return UserConnectedAccount|Entity|null
     * @throws PrintableException
     */
    public function associateThConnectedAccountWithUser(User $user, $providerKey, $providerId, $extraData = [])
    {
        // The provider+key combination is unique to a single user, so if we're trying to associate this
        // account with a user, we need to remove any other association first.
        /** @var UserConnectedAccount $connectedAccount */
        $connectedAccount = $this->em->findOne('XF:UserConnectedAccount', [
            'provider' => $providerId,
            'provider_key' => $providerKey
        ]);
        if ($connectedAccount && $connectedAccount->user_id != $user->user_id) {
            $connectedAccount->delete();
            $connectedAccount = null;
        }

        if (!$connectedAccount) {
            /** @var UserConnectedAccount $connectedAccount */
            $connectedAccount = $this->em->findOne('XF:UserConnectedAccount', [
                'user_id' => $user->user_id,
                'provider' => $providerId
            ]);
        }

        if (!$connectedAccount) {
            /** @var UserConnectedAccount $connectedAccount */
            $connectedAccount = $this->em->create('XF:UserConnectedAccount');
            $connectedAccount->user_id = $user->user_id;
            $connectedAccount->provider = $providerId;
        }

        $connectedAccount->provider_key = $providerKey;
        $connectedAccount->extra_data = $extraData;
        $connectedAccount->save();

        return $connectedAccount;
    }
}
