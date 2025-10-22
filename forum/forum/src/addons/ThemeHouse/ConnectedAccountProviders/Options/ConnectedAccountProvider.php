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

class ConnectedAccountProvider extends AbstractOption
{

    public static function renderSelectMultiple(Option $option, array $htmlParams)
    {
        $data = self::getSelectData($option, $htmlParams);
        $data['controlOptions']['multiple'] = true;
        $data['controlOptions']['size'] = 5;

        return self::getTemplater()->formSelectRow(
            $data['controlOptions'], $data['choices'], $data['rowOptions']
        );
    }

    protected static function getSelectData(Option $option, array $htmlParams)
    {
        /** @var ConnectedAccount $conAccRepo */
        $conAccRepo = XF::repository('XF:ConnectedAccount');
        $providers = $conAccRepo->findProvidersForList()->fetch();

        $choices = [];
        foreach ($providers as $providerId => $provider) {
            $choices[] = [
                'label' => $provider->title,
                'value' => $providerId,
                'disabled' => empty($provider->options) || !$provider->isValidForRegistration()
            ];
        }

        return [
            'choices' => $choices,
            'controlOptions' => self::getControlOptions($option, $htmlParams),
            'rowOptions' => self::getRowOptions($option, $htmlParams)
        ];
    }
}