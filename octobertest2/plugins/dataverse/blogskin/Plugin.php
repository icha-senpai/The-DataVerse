<?php namespace Dataverse\BlogSkin;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    
    public $require = ['Dataverse.Core'];

    public function pluginDetails()
    {
        return [
            'name'        => 'BlogSkin',
            'description' => 'Custom front-end skin for RainLab Blog',
            'author'      => 'Dataverse',
            'icon'        => 'icon-fire'
        ];
    }


    public function registerComponents()
    {
        return [
            'Dataverse\BlogSkin\Components\Posts'    => 'Posts',
            'Dataverse\BlogSkin\Components\PostPage' => 'PostPage',
        ];
    }
}
