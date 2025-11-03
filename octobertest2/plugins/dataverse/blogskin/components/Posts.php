<?php namespace Dataverse\BlogSkin\Components;

use RainLab\Blog\Models\Post;

class Posts extends \Cms\Classes\ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Neon Blog List',
            'description' => 'Dataverse styled list of blog posts'
        ];
    }

    public function posts()
    {
        return Post::isPublished()
            ->orderBy('published_at', 'desc')
            ->with('categories')
            ->take($this->property('limit'))
            ->get();
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title' => 'Posts Limit',
                'default' => 10
            ],
        ];
    }
}
