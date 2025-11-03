<?php namespace Dataverse\BlogSkin\Components;

use Cms\Classes\ComponentBase;
use RainLab\Blog\Models\Post;
use Redirect;

class PostPage extends ComponentBase
{
    public $post;

    public function componentDetails()
    {
        return [
            'name'        => 'Neon Single Post',
            'description' => 'Displays a single RainLab Blog post in Dataverse front-end theme'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Post Slug Param',
                'description' => 'URL parameter for blog post slug',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],

            'notFoundRedirect' => [
                'title'       => 'Redirect if not found',
                'description' => 'Page to redirect to if the post does not exist',
                'default'     => '/',
                'type'        => 'string',
            ]
        ];
    }

    public function onRun()
    {
        $this->post = $this->loadPost();

        if (!$this->post) {
            return Redirect::to($this->property('notFoundRedirect'));
        }

        $this->page['post'] = $this->post;

        // Safely set meta description
        $this->page->title = $this->post->title;
        $this->page->meta_description = $this->post->summary ?: $this->post->excerpt;
    }

    protected function loadPost()
    {
        $slug = $this->property('slug');

        if (!$slug) {
            return null;
        }

        return Post::isPublished()
            ->where('slug', $slug)
            ->with('categories')
            ->first();
    }
}
