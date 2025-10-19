<?php

namespace AH\GamerProfiles\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Member extends XFCP_Member
{
    public function actionGamerProfiles(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params->user_id);

        $visitor = \XF::visitor();

        if (!$visitor->canViewGamerCards($error))
        {
            throw $this->exception($this->noPermission($error));
        }

        $viewParams = [
            'user' => $user,
        ];

        return $this->view('AH\GamerProfiles:Member\Gamerprofiles', 'ah_gamerprofiles_member_view_content', $viewParams);
    }
}