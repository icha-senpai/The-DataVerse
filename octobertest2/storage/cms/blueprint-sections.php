<?php return array (
  '6947ff28-b660-47d7-9240-24ca6d58aeae' => 
  array (
    'uuid' => '6947ff28-b660-47d7-9240-24ca6d58aeae',
    'handle' => 'Blog\\Author',
    'type' => 'entry',
    'name' => 'Author',
    'drafts' => false,
    'defaultSort' => 
    array (
      'column' => 'title',
      'direction' => 'desc',
    ),
    'customMessages' => 
    array (
      'buttonCreate' => 'New Author',
    ),
    'navigation' => 
    array (
      'label' => 'Authors',
      'parent' => 'Blog\\Post',
      'icon' => 'octo-icon-user',
      'order' => 200,
    ),
    'fields' => 
    array (
      'avatar' => 
      array (
        'label' => 'Avatar',
        'type' => 'mediafinder',
        'mode' => 'image',
      ),
      'email' => 
      array (
        'label' => 'Email',
        'type' => 'email',
        'column' => 
        array (
          'searchable' => true,
        ),
      ),
      'role' => 
      array (
        'label' => 'Role',
        'type' => 'text',
      ),
      'about' => 
      array (
        'label' => 'About',
        'type' => 'textarea',
      ),
      '_social_links' => 
      array (
        'type' => 'mixin',
        'label' => 'Social Links',
        'source' => 'Fields\\SocialLinks',
        'tab' => 'Social',
      ),
    ),
    'handleSlug' => 'blog_author',
    '_theme' => 'demo',
  ),
  'b022a74b-15e6-4c6b-9eb9-17efc5103543' => 
  array (
    'uuid' => 'b022a74b-15e6-4c6b-9eb9-17efc5103543',
    'type' => 'structure',
    'handle' => 'Blog\\Category',
    'name' => 'Category',
    'drafts' => false,
    'customMessages' => 
    array (
      'buttonCreate' => 'New Category',
    ),
    'structure' => 
    array (
      'maxDepth' => 1,
    ),
    'navigation' => 
    array (
      'label' => 'Categories',
      'parent' => 'Blog\\Post',
      'icon' => 'octo-icon-list-ul',
      'order' => 150,
    ),
    'fields' => 
    array (
      'description' => 
      array (
        'label' => 'Description',
      ),
    ),
    'handleSlug' => 'blog_category',
    '_theme' => 'demo',
  ),
  'edcd102e-0525-4e4d-b07e-633ae6c18db6' => 
  array (
    'uuid' => 'edcd102e-0525-4e4d-b07e-633ae6c18db6',
    'handle' => 'Blog\\Post',
    'type' => 'stream',
    'name' => 'Post',
    'drafts' => true,
    'customMessages' => 
    array (
      'buttonCreate' => 'New Post',
    ),
    'primaryNavigation' => 
    array (
      'label' => 'Blog',
      'icon' => 'octo-icon-file',
      'iconSvg' => 'modules/tailor/assets/images/blog-icon.svg',
      'order' => 95,
    ),
    'navigation' => 
    array (
      'label' => 'Posts',
      'icon' => 'octo-icon-pencil',
      'order' => 100,
    ),
    'groups' => 
    array (
      'regular_post' => 
      array (
        'name' => 'Regular Post',
        'fields' => 
        array (
          'content' => 
          array (
            'tab' => 'Edit',
            'label' => 'Content',
            'type' => 'richeditor',
            'span' => 'adaptive',
          ),
          '_blog_post_content' => 
          array (
            'type' => 'mixin',
            'source' => 'Fields\\BlogContent',
          ),
        ),
      ),
      'markdown_post' => 
      array (
        'name' => 'Markdown Post',
        'fields' => 
        array (
          'content' => 
          array (
            'tab' => 'Edit',
            'label' => 'Content',
            'type' => 'markdown',
            'span' => 'adaptive',
          ),
          '_blog_post_content' => 
          array (
            'type' => 'mixin',
            'source' => 'Fields\\BlogContent',
          ),
        ),
      ),
    ),
    'handleSlug' => 'blog_post',
    '_theme' => 'demo',
  ),
  'a63fabaf-7c0b-4c74-b36f-7abf1a3ad1c1' => 
  array (
    'uuid' => 'a63fabaf-7c0b-4c74-b36f-7abf1a3ad1c1',
    'handle' => 'Page\\About',
    'type' => 'single',
    'name' => 'About Page',
    'drafts' => true,
    'navigation' => 
    array (
      'icon' => 'icon-rocket',
      'order' => 200,
    ),
    'fields' => 
    array (
      '_block_builder' => 
      array (
        'type' => 'mixin',
        'source' => 'Fields\\Blocks',
      ),
    ),
    'handleSlug' => 'page_about',
    '_theme' => 'demo',
  ),
  '339b11b7-69ad-43c4-9be1-6953e7738827' => 
  array (
    'uuid' => '339b11b7-69ad-43c4-9be1-6953e7738827',
    'handle' => 'Page\\Article',
    'type' => 'structure',
    'name' => 'Article',
    'drafts' => true,
    'customMessages' => 
    array (
      'buttonCreate' => 'New Article',
    ),
    'structure' => 
    array (
      'maxDepth' => 3,
    ),
    'navigation' => 
    array (
      'label' => 'All Articles',
      'icon' => 'icon-wikipedia-w',
      'order' => 100,
    ),
    'fields' => 
    array (
      'content' => 
      array (
        'label' => 'Content',
        'tab' => 'Edit',
        'type' => 'richeditor',
        'span' => 'adaptive',
      ),
      'banner' => 
      array (
        'label' => 'Banner',
        'type' => 'fileupload',
        'mode' => 'image',
        'maxFiles' => 1,
      ),
      'show_in_toc' => 
      array (
        'label' => 'Show in TOC',
        'comment' => 'Include this article in the table of contents',
        'type' => 'checkbox',
      ),
      'summary_text' => 
      array (
        'label' => 'Summary Text',
        'type' => 'textarea',
        'size' => 'small',
      ),
      'gallery' => 
      array (
        'label' => 'Gallery',
        'type' => 'fileupload',
        'mode' => 'image',
      ),
      'external_links' => 
      array (
        'label' => 'External Links',
        'tab' => 'Links',
        'type' => 'repeater',
        'form' => 
        array (
          'fields' => 
          array (
            'link_text' => 
            array (
              'label' => 'Link Text',
              'span' => 'auto',
            ),
            'link_url' => 
            array (
              'label' => 'Link URL',
              'span' => 'auto',
            ),
          ),
        ),
      ),
    ),
    'handleSlug' => 'page_article',
    '_theme' => 'demo',
  ),
  '85e471d2-09b9-4f3d-a63b-1ae9d92d2879' => 
  array (
    'uuid' => '85e471d2-09b9-4f3d-a63b-1ae9d92d2879',
    'handle' => 'Site\\Menus',
    'type' => 'entry',
    'name' => 'Menu',
    'drafts' => false,
    'pagefinder' => false,
    'customMessages' => 
    array (
      'buttonCreate' => 'New Menu',
    ),
    'navigation' => 
    array (
      'label' => 'Menus',
      'icon' => 'icon-sitemap',
      'order' => 300,
    ),
    'columns' => 
    array (
      'title' => 
      array (
        'label' => 'Title',
      ),
      'entry_type_name' => 
      array (
        'label' => 'Menu Type',
        'invisible' => false,
      ),
      'slug' => 
      array (
        'label' => 'Code',
        'invisible' => false,
      ),
    ),
    'fields' => 
    array (
      'slug' => 
      array (
        'label' => 'Code',
        'validation' => 
        array (
          0 => 'required',
        ),
      ),
    ),
    'groups' => 
    array (
      'regular_menu' => 
      array (
        'name' => 'Regular Menu',
        'fields' => 
        array (
          'items' => 
          array (
            'label' => 'Menu Items',
            'type' => 'nesteditems',
            'span' => 'adaptive',
            'maxDepth' => 0,
            'customMessages' => 
            array (
              'buttonCreate' => 'Add Item',
              'titleCreateForm' => 'Create Item',
              'titleUpdateForm' => 'Edit Item',
            ),
            'form' => 
            array (
              'fields' => 
              array (
                'title' => 
                array (
                  'label' => 'Title',
                  'tab' => 'Reference',
                  'default' => 'New Menu Item',
                  'span' => 'full',
                  'type' => 'text',
                  'autoFocus' => true,
                  'validation' => 
                  array (
                    0 => 'required',
                  ),
                ),
                'reference' => 
                array (
                  'label' => 'Reference',
                  'type' => 'pagefinder',
                  'tab' => 'Reference',
                ),
              ),
              'tabs' => 
              array (
                'fields' => 
                array (
                  '_menu_item' => 
                  array (
                    'type' => 'mixin',
                    'source' => 'Site\\Menus\\MenuItem',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'sitemap_menu' => 
      array (
        'name' => 'Sitemap Menu',
        'fields' => 
        array (
          'sitemap_items' => 
          array (
            'label' => 'Sitemap Items',
            'type' => 'nesteditems',
            'span' => 'adaptive',
            'maxDepth' => 1,
            'customMessages' => 
            array (
              'buttonCreate' => 'Add Sitemap Item',
              'titleCreateForm' => 'Create Sitemap Item',
              'titleUpdateForm' => 'Edit Sitemap Item',
            ),
            'form' => 
            array (
              'fields' => 
              array (
                'title' => 
                array (
                  'label' => 'Title',
                  'tab' => 'Reference',
                  'default' => 'New Menu Item',
                  'span' => 'full',
                  'type' => 'text',
                  'autoFocus' => true,
                  'validation' => 
                  array (
                    0 => 'required',
                  ),
                ),
                'reference' => 
                array (
                  'label' => 'Reference',
                  'type' => 'pagefinder',
                  'tab' => 'Reference',
                ),
              ),
              'tabs' => 
              array (
                'fields' => 
                array (
                  '_menu_item' => 
                  array (
                    'type' => 'mixin',
                    'source' => 'Site\\Menus\\SitemapItem',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'handleSlug' => 'site_menus',
    '_theme' => 'demo',
  ),
  '3d05b6ae-9e27-41b1-8a7b-dcc7f76a153b' => 
  array (
    'uuid' => '3d05b6ae-9e27-41b1-8a7b-dcc7f76a153b',
    'handle' => 'Wiki\\Category',
    'name' => 'Wiki Category',
    'type' => 'structure',
    'navigation' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Categories',
      'icon' => 'icon-folder',
      'order' => 20,
      'mode' => 'secondary',
    ),
    'structure' => 
    array (
      'maxDepth' => 2,
      'reorder' => true,
      'showTree' => true,
    ),
    'fields' => 
    array (
      'name' => 
      array (
        'label' => 'Category Name',
        'type' => 'text',
        'span' => 'left',
      ),
      'slug' => 
      array (
        'label' => 'Slug',
        'type' => 'text',
        'preset' => 'name',
        'span' => 'right',
      ),
      'description' => 
      array (
        'label' => 'Description',
        'type' => 'textarea',
        'size' => 'small',
        'span' => 'full',
      ),
    ),
    'handleSlug' => 'wiki_category',
  ),
  'a9dc302f-3ab9-47ee-b37a-21b4420b5a82' => 
  array (
    'uuid' => 'a9dc302f-3ab9-47ee-b37a-21b4420b5a82',
    'handle' => 'Wiki\\Entry',
    'name' => 'Wiki Entry',
    'type' => 'entry',
    'multisite' => false,
    'navigation' => 
    array (
      'primaryNavigation' => 
      array (
        'label' => 'Wiki',
        'icon' => 'icon-book',
        'order' => 150,
      ),
      'label' => 'Entries',
      'icon' => 'icon-file-text',
      'order' => 10,
      'mode' => 'primary',
    ),
    'relations' => 
    array (
      'category' => 
      array (
        'label' => 'Category',
        'type' => 'belongsTo',
        'source' => 'Wiki\\Category',
      ),
      'tags' => 
      array (
        'label' => 'Tags',
        'type' => 'belongsToMany',
        'source' => 'Wiki\\Tag',
      ),
      'related_entries' => 
      array (
        'label' => 'Related Articles',
        'type' => 'belongsToMany',
        'source' => 'Wiki\\Entry',
      ),
      'sources' => 
      array (
        'label' => 'References / Citations',
        'type' => 'hasMany',
        'source' => 'Wiki\\Source',
      ),
      'revision_log' => 
      array (
        'label' => 'Revision History',
        'type' => 'hasMany',
        'source' => 'Wiki\\Revision',
      ),
    ),
    'fields' => 
    array (
      'title' => 
      array (
        'label' => 'Title',
        'type' => 'text',
        'span' => 'left',
      ),
      'slug' => 
      array (
        'label' => 'Slug',
        'type' => 'text',
        'preset' => 'title',
        'span' => 'right',
      ),
      'summary' => 
      array (
        'label' => 'Summary',
        'type' => 'textarea',
        'size' => 'small',
        'span' => 'full',
      ),
      'featured_image' => 
      array (
        'label' => 'Featured Image',
        'type' => 'mediafinder',
        'mode' => 'image',
        'span' => 'full',
      ),
      'infobox' => 
      array (
        'label' => 'Infobox',
        'type' => 'repeater',
        'comment' => 'Structured facts or attributes for the entry.',
        'form' => 
        array (
          'fields' => 
          array (
            'label' => 
            array (
              'label' => 'Label',
              'type' => 'text',
            ),
            'value' => 
            array (
              'label' => 'Value',
              'type' => 'text',
            ),
          ),
        ),
      ),
      'content' => 
      array (
        'label' => 'Main Content',
        'type' => 'markdown',
        'span' => 'full',
      ),
      'allow_comments' => 
      array (
        'label' => 'Allow Comments',
        'type' => 'switch',
        'default' => true,
      ),
    ),
    'use' => 
    array (
      'list' => 'app/contenttypes/tailor/wiki/entry/columns.yaml',
    ),
    'handleSlug' => 'wiki_entry',
  ),
  '1c68bb14-f81e-41e9-b9e7-8b9379d8e2ef' => 
  array (
    'uuid' => '1c68bb14-f81e-41e9-b9e7-8b9379d8e2ef',
    'handle' => 'Wiki\\Revision',
    'name' => 'Wiki Revision',
    'type' => 'entry',
    'navigation' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Revisions',
      'icon' => 'icon-history',
      'order' => 50,
      'code' => 'wiki-revisions',
      'mode' => 'secondary',
    ),
    'relations' => 
    array (
      'entry' => 
      array (
        'label' => 'Entry',
        'type' => 'belongsTo',
        'source' => 'Wiki\\Entry',
      ),
    ),
    'fields' => 
    array (
      'author' => 
      array (
        'label' => 'Author',
        'type' => 'text',
        'span' => 'left',
      ),
      'change_summary' => 
      array (
        'label' => 'Change Summary',
        'type' => 'textarea',
        'span' => 'right',
      ),
      'revision_date' => 
      array (
        'label' => 'Revision Date',
        'type' => 'datepicker',
        'mode' => 'datetime',
        'span' => 'left',
      ),
      'content_snapshot' => 
      array (
        'label' => 'Content Snapshot',
        'type' => 'markdown',
        'size' => 'large',
        'span' => 'full',
      ),
    ),
    'handleSlug' => 'wiki_revision',
  ),
  '7c7f19d1-45b2-4cb5-84b2-c5dbecc82fd8' => 
  array (
    'uuid' => '7c7f19d1-45b2-4cb5-84b2-c5dbecc82fd8',
    'handle' => 'Wiki\\Source',
    'name' => 'Wiki Source',
    'type' => 'entry',
    'navigation' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Sources',
      'icon' => 'icon-link',
      'order' => 40,
      'code' => 'wiki-sources',
      'mode' => 'secondary',
    ),
    'fields' => 
    array (
      'title' => 
      array (
        'label' => 'Title',
        'type' => 'text',
      ),
      'url' => 
      array (
        'label' => 'URL',
        'type' => 'text',
      ),
      'description' => 
      array (
        'label' => 'Description',
        'type' => 'textarea',
      ),
      'source_type' => 
      array (
        'label' => 'Type',
        'type' => 'dropdown',
        'options' => 
        array (
          'book' => 'Book',
          'article' => 'Article',
          'website' => 'Website',
          'video' => 'Video',
          'other' => 'Other',
        ),
      ),
    ),
    'handleSlug' => 'wiki_source',
  ),
  '14dfe87c-6db0-4f88-bd42-272c2d8740cf' => 
  array (
    'uuid' => '14dfe87c-6db0-4f88-bd42-272c2d8740cf',
    'handle' => 'Wiki\\Tag',
    'name' => 'Wiki Tag',
    'type' => 'structure',
    'navigation' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Tags',
      'icon' => 'icon-tags',
      'order' => 30,
      'mode' => 'secondary',
    ),
    'structure' => 
    array (
      'showTree' => false,
    ),
    'fields' => 
    array (
      'name' => 
      array (
        'label' => 'Tag Name',
        'type' => 'text',
      ),
      'slug' => 
      array (
        'label' => 'Slug',
        'type' => 'text',
        'preset' => 'name',
      ),
    ),
    'handleSlug' => 'wiki_tag',
  ),
);