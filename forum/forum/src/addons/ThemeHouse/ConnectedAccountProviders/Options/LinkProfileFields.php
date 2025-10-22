<?php
/**
 * Created by PhpStorm.
 * User: KitCat
 * Date: 31/03/2020
 * Time: 11:35
 */

namespace ThemeHouse\ConnectedAccountProviders\Options;


use XF;
use XF\Entity\Option;
use XF\Option\AbstractOption;
use XF\Repository\ConnectedAccount;

class LinkProfileFields extends AbstractOption
{

    public static function verifyOption(&$choices, \XF\Entity\Option $option)
    {
        $finalChoices = [];
        $max = max(count($choices['profile_fields'] ?? []), count($choices['provider_ids'] ?? []), count($choices['provider_keys'] ?? []));
        for ($i = 0; $i <= $max; $i++) {
            $finalChoices[] = [
                'profile_field' => $choices['profile_fields'][$i] ?? null,
                'provider_id' => $choices['provider_ids'][$i] ?? null,
                'provider_key' => $choices['provider_keys'][$i] ?? null,
            ];
        }

        $choices = array_filter($finalChoices, function (array $choice) {
            return $choice['profile_field'] && $choice['provider_id'] && $choice['provider_key'];
        });
        return true;
    }

    public static function render(Option $option, array $htmlParams)
    {
        $userFields = \XF::app()->finder('XF:UserField')->fetch();

        /** @var ConnectedAccount $conAccRepo */
        $conAccRepo = XF::repository('XF:ConnectedAccount');
        $providers = $conAccRepo->findProvidersForList()->fetch();

        return self::getTemplater()->renderTemplate('admin:th_cap_option_linkProfileFields', [
            'option' => $option,
            'userFields' => $userFields,
            'providers' => $providers
        ]);
    }
}