<?php

namespace AH\GamerProfiles\XF\Entity;

class User extends XFCP_User
{
    public function canViewIcons(&$error = null)
    {
        return $this->hasPermission('ahGamerProfiles', 'canViewIcons');
    }

    public function canViewGamerCards(&$error = null)
    {
        return $this->hasPermission('ahGamerProfiles', 'canViewGamerCards');
    }
}