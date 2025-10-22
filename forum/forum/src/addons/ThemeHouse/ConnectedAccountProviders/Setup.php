<?php

namespace ThemeHouse\ConnectedAccountProviders;

use XF;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Exception;

/**
 * Class Setup
 * @package ThemeHouse\ConnectedAccountProviders
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    protected $connectedAccountProviders = [
        'BattleNet',
        'DeviantArt',
        'Dropbox',
        'Discord',
        'Amazon',
        'Reddit',
        'Pinterest',
        'Instagram',
        'Twitch',
        'VK',
        'PayPal',
        'Vimeo',
        'Spotify',
        'Steam',
        'Imgur',
        'Keycloak',
//        'Line',
//        'WooCommerce',
       'StackExchange',
//        'XING',
//        'GitLab',
        'EpicGames',
        'Apple',
        'AmazonCognito',
        'Miniorange'
    ];

    /**
     * @throws Exception
     */
    protected function updateProviderRecords()
    {
        $displayOrder = 70;
        foreach ($this->connectedAccountProviders as $connectedAccountProvider) {
            $id = 'th_cap_' . strtolower($connectedAccountProvider);
            $class = 'ThemeHouse\\ConnectedAccountProviders:Provider\\' . $connectedAccountProvider;
            $displayOrder += 10;

            XF::db()->query("
                INSERT INTO
                  xf_connected_account_provider (provider_id, provider_class, display_order, options)
                VALUES (?, ?, ?, '')
                ON DUPLICATE KEY UPDATE
                  provider_class = ?
            ", [$id, $class, $displayOrder, $class]);
        }
    }

    /**
     * @param array $stateChanges
     * @throws Exception
     */
    public function postInstall(array &$stateChanges)
    {
        $this->updateProviderRecords();
    }

    /**
     * @param $previousVersion
     * @param array $stateChanges
     * @throws Exception
     */
    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->updateProviderRecords();
    }

    /**
     *
     */
    public function uninstallStep1()
    {
        XF::db()->delete('xf_connected_account_provider', "provider_id LIKE 'th_cap_%'");
    }
}