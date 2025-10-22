<?php

namespace ThemeHouse\ConnectedAccountProviders\Import\Importer;

use XF;
use XF\Entity\User;
use XF\Entity\UserConnectedAccount;
use XF\Import\Importer\AbstractImporter;
use XF\Import\StepState;
use XF\Mvc\Entity\Repository;
use XF\PrintableException;
use XF\Repository\ConnectedAccount;

/**
 * Class ConnectedAccountProvider
 * @package ThemeHouse\ConnectedAccountProviders\Import\Importer
 */
class ConnectedAccountProvider extends AbstractImporter
{
    /**
     * @return array
     */
    public static function getListInfo()
    {
        return [
            'target' => '[XF2] Connected Account Provider',
            'source' => '[XF2] Connected Account Provider'
        ];
    }

    /**
     * @param array $vars
     * @return string
     */
    public function renderBaseConfigOptions(array $vars)
    {
        $vars['providers'] = $this->getProviderRepo()->getUsableProviders();
        return $this->app->templater()->renderTemplate('admin:th_cap_connected_account_import_config', $vars);
    }

    /**
     * @return Repository|ConnectedAccount
     */
    protected function getProviderRepo()
    {
        return XF::repository('XF:ConnectedAccount');
    }

    /**
     * @param array $baseConfig
     * @param array $errors
     * @return bool
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    public function validateBaseConfig(array &$baseConfig, array &$errors)
    {
        if ($baseConfig['from_provider'] === $baseConfig['to_provider']) {
            $errors[] = XF::phrase('th_cap_import_error_providers_cannot_be_the_same');
            return false;
        }

        $providers = $this->getProviderRepo()->getUsableProviders();
        if (!isset($baseConfig['from_provider']) || !$baseConfig['from_provider'] || !$providers->offsetExists($baseConfig['from_provider'])) {
            $errors[] = XF::phrase('th_cap_import_error_no_valid_import_source_selected');
            return false;
        }

        $providers = $this->getProviderRepo()->getUsableProviders();
        if (!isset($baseConfig['to_provider']) || !$baseConfig['to_provider'] || !$providers->offsetExists($baseConfig['to_provider'])) {
            $errors[] = XF::phrase('th_cap_import_error_no_valid_import_target_selected');
            return false;
        }

        return true;
    }

    /**
     * @param array $vars
     * @return null
     */
    public function renderStepConfigOptions(array $vars)
    {
        return null;
    }

    /**
     * @param array $steps
     * @param array $stepConfig
     * @param array $errors
     * @return bool
     */
    public function validateStepConfig(array $steps, array &$stepConfig, array &$errors)
    {
        return true;
    }

    /**
     *
     */
    public function canRetainIds()
    {
        return false;
    }

    /**
     *
     */
    public function resetDataForRetainIds()
    {
        return false;
    }

    /**
     *
     */
    public function getSteps()
    {
        return [
            'userData' => ['title' => XF::phrase('th_cap_user_data')]
        ];
    }

    /**
     * @return bool|null
     */
    public function getStepEndUserData()
    {
        return $this->db()->fetchOne('SELECT max(user_id) FROM xf_user');
    }

    /**
     * @param StepState $state
     * @param array $stepConfig
     * @param $maxTime
     * @return StepState
     * @throws PrintableException
     */
    public function stepUserData(StepState $state, array $stepConfig, $maxTime)
    {
        $userIds = $this->db()->fetchAllColumn('
            SELECT
              user_id
            FROM
              xf_user
            WHERE
              user_id > ?
              AND user_id < ?
            LIMIT
              15
        ', [
            $state->startAfter,
            $state->end
        ]);

        if (empty($userIds)) {
            return $state->complete();
        }

        $repo = $this->getProviderRepo();
        $config = $this->getBaseConfig();
        foreach ($userIds as $userId) {
            $state->startAfter = $userId;
            $state->imported++;

            /** @var User $user */
            $user = $this->em()->find('XF:User', $userId);
            if (!$user) {
                continue;
            }

            /** @var UserConnectedAccount $fromProvider */
            $fromProvider = $this->em()->find('XF:UserConnectedAccount', [$user->user_id, $config['from_provider']]);
            if ($fromProvider) {

                /** @var UserConnectedAccount $toProvider */
                $toProvider = $this->em()->find('XF:UserConnectedAccount', [$user->user_id, $config['to_provider']]);
                if (!$toProvider) {
                    $toProvider = $this->em()->create('XF:UserConnectedAccount');
                    $toProvider->user_id = $user->user_id;
                    $toProvider->provider = $config['to_provider'];
                }

                $toProvider->provider_key = $fromProvider->provider_key;
                $toProvider->extra_data = $fromProvider->extra_data;
                $toProvider->save();
            }

            $repo->rebuildUserConnectedAccountCache($user);
        }

        if ($state->startAfter === $state->end) {
            return $state->complete();
        }

        return $state;
    }

    /**
     * @param array $stepsRun
     * @return array
     */
    public function getFinalizeJobs(array $stepsRun)
    {
        return [];
    }

    /**
     *
     */
    protected function getBaseConfigDefault()
    {
        return [
            'from_provider' => '',
            'to_provider' => ''
        ];
    }

    /**
     *
     */
    protected function getStepConfigDefault()
    {
        return [];
    }

    /**
     *
     */
    protected function doInitializeSource()
    {
    }
}