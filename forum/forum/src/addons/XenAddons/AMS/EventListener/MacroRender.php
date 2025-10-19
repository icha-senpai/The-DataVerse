<?php

namespace XenAddons\AMS\EventListener;

class MacroRender
{
    public static function preRender(\XF\Template\Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
    {
        if ($arguments['group']->group_id == 'xaAms')
        {
            $template = 'xa_ams_option_macros';
        }
    }
}